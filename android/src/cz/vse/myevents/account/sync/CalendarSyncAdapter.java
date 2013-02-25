package cz.vse.myevents.account.sync;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.TimeZone;

import android.accounts.Account;
import android.annotation.TargetApi;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.ContentProviderClient;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.PeriodicSync;
import android.content.SharedPreferences;
import android.content.SyncResult;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.Bitmap;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.provider.CalendarContract.Calendars;
import android.provider.CalendarContract.Events;
import android.provider.CalendarContract.Reminders;
import android.support.v4.app.NotificationCompat;
import android.support.v4.app.TaskStackBuilder;
import cz.vse.myevents.R;
import cz.vse.myevents.activity.EventsListActivity;
import cz.vse.myevents.activity.LoginActivity;
import cz.vse.myevents.activity.SettingsActivity;
import cz.vse.myevents.database.data.DataContract.EventInfo;
import cz.vse.myevents.database.data.DataContract.EventOrganizators;
import cz.vse.myevents.database.data.DataContract.EventTypes;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.filefilter.EventImageFileFilter;
import cz.vse.myevents.misc.BitmapLoader;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.misc.Instances;
import cz.vse.myevents.notification.NotificationBroadcastReceiver;
import cz.vse.myevents.serverdata.Event;
import cz.vse.myevents.serverdata.Organization;
import cz.vse.myevents.xml.EventsWebParser;

public class CalendarSyncAdapter extends TalkingSyncAdapter {

	public static final String CALENDAR_NAME = "cz.vse.myevents.calendar.v1";
	public static final String NO_NOTIFICATIONS_EXTRA = "noNotifications";

	private int calendarId;

	private Account account;
	private SharedPreferences sharedPreferences;
	private Bundle extras;

	private Set<Event> serverEvents = new HashSet<Event>();
	private Set<Event> dbEvents = new HashSet<Event>();

	public static final List<Event> NEW_EVENTS = new ArrayList<Event>();
	private NotificationCompat.Builder notificationBuilder;

	private SQLiteDatabase dataDb;

	public CalendarSyncAdapter(Context context, boolean autoInitialize) {
		super(context, autoInitialize);
		sharedPreferences = PreferenceManager.getDefaultSharedPreferences(context);
	}

	@Override
	public void onPerformSync(Account account, Bundle extras, String authority, ContentProviderClient provider, SyncResult syncResult) {
		this.extras = extras;

		ConnectivityManager connMgr = (ConnectivityManager) getContext().getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			return;
		}

		// Check if data sync was done
		openDb();
		Cursor cursor = dataDb.query(Organizations.TABLE_NAME, new String[]{Organizations._ID}, null, null, null, null, null);

		if (cursor == null || cursor.getCount() <= 0) {
			Bundle syncExtras = new Bundle();
			syncExtras.putBoolean(DataSyncAdapter.SYNC_EVENTS_AFTER_EXTRA, true);
			LoginActivity.setupDataSync(account, syncExtras);
			return;
		}

		try {
			tellSyncStart(Instances.getOverviewActivity());
			this.account = account;

			calendarId = createOrLoadCalendar();

			boolean dataLoaded = true;
			try {
				loadDbEvents();
				loadServerEvents();
			} catch (WebContentNotReachedException ex) {
				dataLoaded = false;
			}
			if (dataLoaded) {
				syncEvents();

				// Handle notifications
				handleNotifications();
			}
		} finally {
			tellSyncEnd(Instances.getOverviewActivity());

			if (dataDb != null) {
				dataDb.close();
			}
		}

		// Set periodic sync
		List<PeriodicSync> periodicSyncs = ContentResolver.getPeriodicSyncs(account, "com.android.calendar");
		if (periodicSyncs.size() != 1) {
			periodicSyncs.clear();
			ContentResolver.addPeriodicSync(account, "com.android.calendar", new Bundle(), Constants.SYNC_PERIODICITY_CALENDAR * 60L);
		}
	}

	private void openDb() {
		if (dataDb == null || !dataDb.isOpen()) {
			dataDb = DataDbHelper.getWritableDatabase(getContext());
		}
	}

	private void handleNotifications() {
		if (!sharedPreferences.getBoolean(SettingsActivity.PREF_NOTIFICATIONS_KEY, false) || extras.getBoolean(NO_NOTIFICATIONS_EXTRA)) {
			NEW_EVENTS.clear();
			return;
		}

		Set<Event> notNotifiedEvents = new HashSet<Event>();
		for (Event event : NEW_EVENTS) {
			if (!event.isNotified()) {
				notNotifiedEvents.add(event);
			}
		}

		if (notNotifiedEvents.size() == 0) {
			return;
		}

		// Set up builder
		if (notificationBuilder == null) {
			notificationBuilder = new NotificationCompat.Builder(getContext());
		}

		// Add intent
		Intent resultIntent;

		if (NEW_EVENTS.size() > 1) {
			resultIntent = new Intent(getContext(), EventsListActivity.class);
			resultIntent.setAction(EventsListActivity.NEW_EVENTS_ACTION);
		} else {
			resultIntent = Helper.getCalendarEventIntent(NEW_EVENTS.iterator().next());
		}

		TaskStackBuilder stackBuilder = TaskStackBuilder.create(getContext());

		stackBuilder.addParentStack(EventsListActivity.class);
		stackBuilder.addNextIntent(resultIntent);

		PendingIntent resultPendingIntent = stackBuilder.getPendingIntent(0, PendingIntent.FLAG_UPDATE_CURRENT);

		notificationBuilder.setContentIntent(resultPendingIntent);

		// Set info
		String ticker, title, text, contentInfo;
		if (NEW_EVENTS.size() > 1) {
			ticker = getContext().getString(R.string.notif_more_new_events_ticker);
			title = getContext().getString(R.string.notif_more_new_events_title);
			text = getContext().getString(R.string.notif_more_new_events_text);
			contentInfo = String.valueOf(NEW_EVENTS.size());
		} else {
			ticker = getContext().getString(R.string.notif_one_new_event_ticker);
			title = getContext().getString(R.string.notif_one_new_event_title);
			text = NEW_EVENTS.iterator().next().getName();
			contentInfo = null;
		}

		// Handle large icon
		Bitmap largeIcon = null;
		for (Event event : NEW_EVENTS) {
			if (!event.hasDefaultImage(getContext())) {
				if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
					largeIcon = BitmapLoader.loadBitmap(event.getEventImagePath(getContext()),
					        getContext().getResources().getDimensionPixelSize(android.R.dimen.notification_large_icon_width), getContext()
					                .getResources().getDimensionPixelSize(android.R.dimen.notification_large_icon_height));
				}
			}
		}

		notificationBuilder.setSmallIcon(R.drawable.ic_notification_small);
		notificationBuilder.setTicker(ticker);
		notificationBuilder.setContentTitle(title);
		notificationBuilder.setContentText(text);
		notificationBuilder.setContentInfo(contentInfo);
		notificationBuilder.setAutoCancel(true);

		if (largeIcon != null) {
			notificationBuilder.setLargeIcon(largeIcon);
		}

		if (sharedPreferences.getBoolean("pref_notifications_vibrations", true)) {
			notificationBuilder.setVibrate(new long[]{0, 350, 50, 150, 50, 150});
		}
		notificationBuilder.setSound(Uri.parse(sharedPreferences.getString("notification_sound", "content://settings/system/notification_sound")));

		// Build
		Notification notification = notificationBuilder.build();
		notification.flags = Notification.FLAG_SHOW_LIGHTS | Notification.FLAG_AUTO_CANCEL;

		// Delete intent
		Intent intent = new Intent(getContext(), NotificationBroadcastReceiver.class);
		intent.setAction("notification_cancelled");
		PendingIntent deleteIntent = PendingIntent.getBroadcast(getContext(), 0, intent, PendingIntent.FLAG_CANCEL_CURRENT);
		notification.deleteIntent = deleteIntent;

		// Show
		NotificationManager notificationManager = (NotificationManager) getContext().getSystemService(Context.NOTIFICATION_SERVICE);
		notificationManager.notify(Constants.NOTIFICATION_NEW_EVENT_ID, notification);

		// Finish
		for (Event event : NEW_EVENTS) {
			event.setNotified(true);
		}
	}

	private Set<Event> downloadServerData(boolean forceGetAll) throws WebContentNotReachedException {
		Set<Event> serverEvents = new HashSet<Event>();

		String subOrgIds = createSubOrgsIds(dataDb, forceGetAll);
		String subETypesIds = createSubETypesIds(dataDb, forceGetAll);

		// Get data
		if (subOrgIds != "" && subETypesIds != "") {
			StringBuilder urlBuilder = new StringBuilder();
			urlBuilder.append("http://" + Constants.SERVER_URL + "/xml/eventsData/") // Base
			        .append(subOrgIds).append("/") // Subscribed
			                                       // organizations IDs
			        .append(subETypesIds).append("/"); // Subscribed event
			                                           // types IDs

			InputStream stream = Helper.loadWebStream(urlBuilder.toString());
			EventsWebParser eventsWebParser = new EventsWebParser(getContext(), stream);
			serverEvents = eventsWebParser.getEvents();
		}

		return serverEvents;
	}

	private String createSubOrgsIds(SQLiteDatabase db, boolean forceGetAll) {
		SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(getContext());

		String subOrgIds;
		if (forceGetAll) {
			subOrgIds = "0";
		} else if (!sharedPreferences.getBoolean(SettingsActivity.PREF_ORGS_SUBSCRIBE_TO_ALL_KEY, true)) {
			Cursor orgSubsCursor = db.query(Organizations.TABLE_NAME, new String[]{Organizations._ID}, Organizations.SUBSCRIBED + "=1", null, null,
			        null, null, null);

			StringBuilder orgStringBuilder = new StringBuilder();
			if (orgSubsCursor != null && orgSubsCursor.moveToFirst()) {
				do {
					orgStringBuilder.append(orgSubsCursor.getInt(orgSubsCursor.getColumnIndex(Organizations._ID))).append(",");
				} while (orgSubsCursor.moveToNext());
			}

			if (orgSubsCursor != null) {
				orgSubsCursor.close();
			}

			int lastPointIndex = orgStringBuilder.lastIndexOf(",");
			if (lastPointIndex != -1) {
				subOrgIds = orgStringBuilder.deleteCharAt(orgStringBuilder.lastIndexOf(",")).toString();
			} else {
				subOrgIds = "";
			}
		} else {
			subOrgIds = "0";
		}

		return subOrgIds;
	}

	private String createSubETypesIds(SQLiteDatabase db, boolean forceGetAll) {
		SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(getContext());

		String subETypesIds;
		if (forceGetAll) {
			subETypesIds = "0";
		} else if (!sharedPreferences.getBoolean(SettingsActivity.PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY, true)) {
			Cursor eTypesSubsCursor = db.query(EventTypes.TABLE_NAME, new String[]{EventTypes._ID}, EventTypes.SUBSCRIBED + "=1", null, null, null,
			        null, null);

			StringBuilder eTypesStringBuilder = new StringBuilder();
			if (eTypesSubsCursor != null && eTypesSubsCursor.moveToFirst()) {
				do {
					eTypesStringBuilder.append(eTypesSubsCursor.getInt(eTypesSubsCursor.getColumnIndex(EventTypes._ID))).append(",");
				} while (eTypesSubsCursor.moveToNext());
			}

			if (eTypesSubsCursor != null) {
				eTypesSubsCursor.close();
			}

			int lastPointIndex = eTypesStringBuilder.lastIndexOf(",");
			if (lastPointIndex != -1) {
				subETypesIds = eTypesStringBuilder.deleteCharAt(eTypesStringBuilder.lastIndexOf(",")).toString();
			} else {
				subETypesIds = "";
			}
		} else {
			subETypesIds = "0";
		}

		return subETypesIds;
	}

	private int insertDataToDb(Event event) {
		SharedPreferences sharedPreferences = PreferenceManager.getDefaultSharedPreferences(getContext());

		ContentValues values = new ContentValues();

		values.put(Events.CALENDAR_ID, calendarId);
		values.put(Events.TITLE, event.getName());
		values.put(Events.EVENT_LOCATION, event.getLocation());
		values.put(Events.DTSTART, event.getStartDate().getTime());
		values.put(Events.DTEND, event.getEndDate().getTime());
		values.put(Events.DESCRIPTION, event.getDescription());
		values.put(Events.EVENT_TIMEZONE, TimeZone.getTimeZone("GMT+1").getID());
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
			values.put(Events.HAS_ALARM, !sharedPreferences.getString(SettingsActivity.PREF_REMINDER_KEY, "-1").equals("-1"));
		}

		Uri result = getContext().getContentResolver().insert(eventsUri(), values);

		event.setId(Integer.valueOf(result.getPathSegments().get(1)));

		// Insert additional info
		ContentValues eventInfoValues = new ContentValues();
		eventInfoValues.put(EventInfo._ID, event.getServerId());
		eventInfoValues.put(EventInfo.EVENT_ID, event.getId());
		eventInfoValues.put(EventInfo.CRC, event.getCrc());

		dataDb.insert(EventInfo.TABLE_NAME, EventInfo.CRC, eventInfoValues);

		// Insert organizators info
		for (Organization organizator : event.getOrganizators()) {
			ContentValues organizatorValues = new ContentValues();
			organizatorValues.put(EventOrganizators.EVENT_ID, event.getId());
			organizatorValues.put(EventOrganizators.ORGANIZATION_ID, organizator.getId());

			dataDb.insert(EventOrganizators.TABLE_NAME, EventOrganizators.EVENT_ID, organizatorValues);
		}

		return event.getId();
	}

	private void downloadEventImage(Event event) {
		// Find out event ID
		if (event.getId() == 0) {
			Cursor cursor = getContext().getContentResolver()
			        .query(eventsUri(),
			                new String[]{Events._ID},
			                Events.TITLE + "=? AND " + Events.DTSTART + "=? AND " + Events.DTEND + "=?",
			                new String[]{event.getName(), String.valueOf(event.getStartDate().getTime()),
			                        String.valueOf(event.getEndDate().getTime())}, null);

			try {
				if (cursor.moveToFirst()) {
					event.setId(cursor.getInt(cursor.getColumnIndex(Events._ID)));
				} else {
					return;
				}
			} finally {
				if (cursor != null) {
					cursor.close();
				}
			}
		}

		try {
			String fileName = null;
			try {
				fileName = buildEventPictureName(event);
			} catch (NullPointerException ex) {
			}

			// Delete old one
			File eventImagesDir = new File(getContext().getFilesDir().getAbsolutePath());
			File[] eventImages = eventImagesDir.listFiles(new EventImageFileFilter(event.getId()));

			for (File eventImage : eventImages) {
				String[] imagePathSplitted = eventImage.getPath().split(File.separator);
				String imageFileName = imagePathSplitted[imagePathSplitted.length - 1];

				if (!fileName.equals(imageFileName)) {
					eventImage.delete();
				}
			}

			// Download new one
			if (event.getServerImageUrl() != null && event.getServerImageUrl() != "") {
				File outFile = new File(getContext().getFilesDir(), fileName);

				if (!outFile.exists()) {
					BufferedInputStream inStream = new BufferedInputStream(Helper.loadWebStream(event.getServerImageUrl()));
					FileOutputStream fileStream = getContext().openFileOutput(fileName, Context.MODE_PRIVATE);
					BufferedOutputStream outStream = new BufferedOutputStream(fileStream, 1024);
					byte[] data = new byte[1024];

					int bytesRead = 0;
					while ((bytesRead = inStream.read(data, 0, data.length)) >= 0) {
						ConnectivityManager connMgr = (ConnectivityManager) getContext().getSystemService(Context.CONNECTIVITY_SERVICE);
						NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
						if (networkInfo == null || !networkInfo.isConnected()) {
							outFile.delete();
							break;
						}

						outStream.write(data, 0, bytesRead);
					}

					outStream.close();
					fileStream.close();
					inStream.close();
				}
			}
		} catch (WebContentNotReachedException e) {
		} catch (FileNotFoundException e) {
		} catch (IOException e) {
		}
	}

	public static String buildEventPictureName(Event event) {
		String[] urlSplitted = event.getServerImageUrl().split("\\.");
		String fileSuffix = (urlSplitted[urlSplitted.length - 1]).toLowerCase();

		StringBuilder nameBuilder = new StringBuilder();
		nameBuilder.append(EventImageFileFilter.FILENAME_PREFIX)
		// Base
		        .append(String.valueOf(event.getId())).append("_")
		        // ID
		        .append(String.valueOf(event.getEndDate().getTime())).append("_") // End
		                                                                          // date
		                                                                          // timestamp
		        .append(event.getServerImageCrc()) // CRC32
		        .append(".").append(fileSuffix); // Suffix

		return nameBuilder.toString();
	}

	public static int appendReminder(Context context, int eventId, int minutes) {
		if (minutes == -1) {
			return 0;
		}

		// Find existing reminder
		Uri remindersUri = Helper.remindersUri();
		Cursor cursor = context.getContentResolver().query(remindersUri, new String[]{Reminders._ID}, Reminders.EVENT_ID + "=?",
		        new String[]{String.valueOf(eventId)}, null);
		int reminderId;

		// Reminder exists, update only
		try {
			if (cursor.moveToFirst()) {
				ContentValues values = new ContentValues();
				values.put(Reminders.MINUTES, minutes);

				context.getContentResolver().update(remindersUri, values, Reminders.EVENT_ID + "=?", new String[]{String.valueOf(eventId)});

				return cursor.getInt(cursor.getColumnIndex(Reminders._ID));
			}

			// Reminder does not exist, create new one
			else {
				ContentValues values = new ContentValues();
				values.put(Reminders.MINUTES, minutes);
				values.put(Reminders.EVENT_ID, eventId);
				values.put(Reminders.METHOD, Reminders.METHOD_ALERT);

				Uri result = context.getContentResolver().insert(remindersUri, values);

				reminderId = Integer.valueOf(result.getPathSegments().get(1));
			}
		} finally {
			if (cursor != null) {
				cursor.close();
			}
		}

		return reminderId;
	}

	private int createOrLoadCalendar() {
		Cursor cursor = getContext().getContentResolver().query(calendarsUri(), new String[]{Calendars._ID, Calendars.NAME}, null, null, null);
		if (cursor != null) {
			cursor.moveToFirst();
		}

		// Get name of an existing calendar
		int calendarId = 0;
		String calendarName = null;
		if (cursor != null && cursor.getCount() == 1) {
			calendarName = cursor.getString(cursor.getColumnIndex(Calendars.NAME));
		}
		boolean newName = calendarName != null && !calendarName.equals(CALENDAR_NAME);

		if (cursor == null || cursor.getCount() <= 0 || newName) {
			ContentValues calendar = new ContentValues();
			Uri calendarUri = calendarsUri();

			// Create column names
			String accountName, accountType, name, calendarDisplayName, calendarColor, calendarAccessLevel, ownerAccount, syncEvents;
			if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
				accountName = "_sync_account";
				accountType = "_sync_account_type";
				name = "name";
				calendarDisplayName = "displayName";
				calendarColor = "color";
				calendarAccessLevel = "access_level";
				ownerAccount = "ownerAccount";
				syncEvents = "sync_events";
			} else {
				accountName = Calendars.ACCOUNT_NAME;
				accountType = Calendars.ACCOUNT_TYPE;
				name = Calendars.NAME;
				calendarDisplayName = Calendars.CALENDAR_DISPLAY_NAME;
				calendarColor = Calendars.CALENDAR_COLOR;
				calendarAccessLevel = Calendars.CALENDAR_ACCESS_LEVEL;
				ownerAccount = Calendars.OWNER_ACCOUNT;
				syncEvents = Calendars.SYNC_EVENTS;
			}

			calendar.put(accountName, account.name);
			calendar.put(accountType, Constants.ACCOUNT_TYPE);
			calendar.put(name, CALENDAR_NAME);
			calendar.put(calendarDisplayName, getContext().getResources().getString(R.string.app_name));
			calendar.put(calendarColor, 0xFF93AE23);
			calendar.put(calendarAccessLevel, 200);
			calendar.put(ownerAccount, account.name);
			calendar.put(syncEvents, 1);

			if (newName) {
				getContext().getContentResolver().update(calendarUri, calendar, accountType + "=?", new String[]{Constants.ACCOUNT_TYPE});
			} else {
				Uri result = getContext().getContentResolver().insert(calendarUri, calendar);
				calendarId = Integer.valueOf(result.getPathSegments().get(1));
			}

		}

		// Load calendar id
		if (calendarId == 0) {
			calendarId = cursor.getInt(cursor.getColumnIndex(Calendars._ID));
		}

		if (cursor != null) {
			cursor.close();
		}
		return calendarId;
	}

	public static int loadCalendarId(Context context) {
		int calendarId = 0;
		Uri calendarUri = Helper.calendarsUri();
		calendarUri.buildUpon().appendQueryParameter(Constants.CALENDAR_ACCOUNT_TYPE, Constants.ACCOUNT_TYPE).build();

		Cursor calendarsCursor = context.getContentResolver().query(calendarUri, new String[]{Calendars._ID}, Constants.CALENDAR_ACCOUNT_TYPE + "=?",
		        new String[]{Constants.ACCOUNT_TYPE}, null);

		if (calendarsCursor != null && calendarsCursor.moveToFirst()) {
			calendarId = calendarsCursor.getInt(calendarsCursor.getColumnIndex(Calendars._ID));
		}

		if (calendarsCursor != null) {
			calendarsCursor.close();
		}

		return calendarId;
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	private Uri calendarsUri() {
		Uri uri = Helper.calendarsUri();

		return uri.buildUpon().appendQueryParameter(android.provider.CalendarContract.CALLER_IS_SYNCADAPTER, "true")
		        .appendQueryParameter(Calendars.ACCOUNT_NAME, account.name)
		        .appendQueryParameter(Constants.CALENDAR_ACCOUNT_TYPE, Constants.ACCOUNT_TYPE).build();
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	private Uri eventsUri() {
		Uri uri = Helper.eventsUri();

		return uri.buildUpon().appendQueryParameter(android.provider.CalendarContract.CALLER_IS_SYNCADAPTER, "true")
		        .appendQueryParameter(Calendars.ACCOUNT_NAME, account.name)
		        .appendQueryParameter(Constants.CALENDAR_ACCOUNT_TYPE, Constants.ACCOUNT_TYPE).build();
	}

	private void syncEvents() {
		Uri uri = eventsUri();
		int reminderMinutes = Integer.parseInt(sharedPreferences.getString(SettingsActivity.PREF_REMINDER_KEY, "-1"));
		Set<Integer> removedServerIds = new HashSet<Integer>();

		// Remove old events
		for (Event dbEvent : dbEvents) {
			if (!serverEvents.contains(dbEvent)) {
				// Remove from DB
				getContext().getContentResolver().delete(uri, Events._ID + "=?", new String[]{String.valueOf(dbEvent.getId())});
				dataDb.delete(EventInfo.TABLE_NAME, EventInfo.EVENT_ID + "=?", new String[]{String.valueOf(dbEvent.getId())});
				dataDb.delete(EventOrganizators.TABLE_NAME, EventOrganizators.EVENT_ID + "=?", new String[]{String.valueOf(dbEvent.getId())});
				NEW_EVENTS.remove(dbEvent);

				removedServerIds.add(dbEvent.getServerId());
			}
		}

		deleteUselessImages();

		// Insert new events
		for (Event serverEvent : serverEvents) {
			if (!dbEvents.contains(serverEvent)) {
				int eventId = insertDataToDb(serverEvent);
				serverEvent.setId(eventId);

				// Is just updated?
				if (removedServerIds.contains(serverEvent.getServerId())) {
					serverEvent.setNotified(true);
				}

				appendReminder(getContext(), eventId, reminderMinutes);
				NEW_EVENTS.add(serverEvent);
			}

			downloadEventImage(serverEvent);
		}
	}

	private void deleteUselessImages() {
		// Delete old images
		File[] images = getContext().getFilesDir().listFiles(new EventImageFileFilter());
		for (File image : images) {
			String[] imagePathSplitted = image.getPath().split(File.separator);
			String imageFileName = imagePathSplitted[imagePathSplitted.length - 1];
			String[] imageNameSplitted = imageFileName.split("_");
			long timestamp = Long.parseLong(imageNameSplitted[2]);

			Date eventDate = new Date(timestamp);
			Date now = new Date();

			if (eventDate.before(now)) {
				image.delete();
			}
		}

		// Delete images of not existing events
		Uri uri = eventsUri();
		Cursor cursor = getContext().getContentResolver().query(uri, new String[]{Events._ID}, Events.CALENDAR_ID + "=?",
		        new String[]{String.valueOf(calendarId)}, null);

		Set<Integer> existingEventsIds = new HashSet<Integer>();
		if (cursor != null && cursor.moveToFirst()) {
			do {
				existingEventsIds.add(cursor.getInt(cursor.getColumnIndex(Events._ID)));
			} while (cursor.moveToNext());
		}

		if (cursor != null) {
			cursor.close();
		}

		File[] files = getContext().getFilesDir().listFiles(new EventImageFileFilter());
		for (File file : files) {
			String[] pathSplitted = file.getPath().split(File.separator);
			String fileName = pathSplitted[pathSplitted.length - 1];
			String[] fileNameSplitted = fileName.split("_");
			int fileEventId = Integer.parseInt(fileNameSplitted[1]);

			if (!existingEventsIds.contains(fileEventId)) {
				file.delete();
			}
		}
	}

	private void loadDbEvents() {
		if (dbEvents != null) {
			dbEvents.clear();
		}

		Uri uri = eventsUri();
		Cursor cursor = getContext().getContentResolver().query(uri,
		        new String[]{Events._ID, Events.TITLE, Events.EVENT_LOCATION, Events.DTSTART, Events.DTEND, Events.DESCRIPTION},
		        Events.CALENDAR_ID + "=?", new String[]{String.valueOf(calendarId)}, null);

		if (cursor != null && cursor.moveToFirst()) {
			do {
				dbEvents.add(Event.createFromCursor(cursor, dataDb));
			} while (cursor.moveToNext());
		}

		if (cursor != null) {
			cursor.close();
		}
	}

	private void loadServerEvents() throws WebContentNotReachedException {
		if (serverEvents != null) {
			serverEvents.clear();
		}

		serverEvents = downloadServerData(false);
	}
}
