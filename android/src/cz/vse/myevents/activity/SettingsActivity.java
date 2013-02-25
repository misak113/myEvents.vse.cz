package cz.vse.myevents.activity;

import java.util.Date;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.annotation.TargetApi;
import android.app.Activity;
import android.app.NotificationManager;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.media.Ringtone;
import android.media.RingtoneManager;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.preference.CheckBoxPreference;
import android.preference.Preference;
import android.preference.Preference.OnPreferenceChangeListener;
import android.preference.PreferenceActivity;
import android.preference.PreferenceCategory;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;
import android.preference.PreferenceScreen;
import android.provider.CalendarContract.Calendars;
import android.provider.CalendarContract.Events;
import android.provider.CalendarContract.Reminders;
import android.view.MenuItem;
import cz.vse.myevents.R;
import cz.vse.myevents.account.sync.CalendarSyncAdapter;
import cz.vse.myevents.database.data.DataContract.EventTypes;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;

public class SettingsActivity extends PreferenceActivity {

	public static final String PREF_REMINDER_KEY = "pref_reminder";
	public static final String PREF_NOTIFICATIONS_KEY = "pref_notifications";

	public static final String PREF_ORGANIZATIONS_KEY = "pref_organizations_screen";
	public static final String PREF_ETYPES_KEY = "pref_event_types_screen";

	public static final String PREF_ORG_SETTINGS_KEY = "pref_cat_organizations";
	public static final String PREF_ETYPES_SETTINGS_KEY = "pref_cat_event_types";

	public static final String PREF_ORG_LIST_KEY = "pref_organizations_category";
	public static final String PREF_ETYPES_LIST_KEY = "pref_event_types_category";

	public static final String PREF_ORGS_SUBSCRIBE_TO_ALL_KEY = "pref_orgs_subscribe_to_all";
	public static final String PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY = "pref_event_types_subscribe_to_all";

	private static final String ORG_CHECKBOX_KEY = "pref_subscription_org";
	private static final String ETYPES_CHECKBOX_KEY = "pref_subscription_event_types";

	private static final String MAIN_ORGS_SUBSCRIBTIONS_KEY = "pref_main_organizations";
	private static final String MAIN_ETYPES_SUBSCRIBTIONS_KEY = "pref_main_event_types";

	private static final String PREF_ACTION_ORGANIZATIONS = "cz.vse.myevents.pref.organizations";
	private static final String PREF_ACTION_ETYPES = "cz.vse.myevents.pref.eventtypes";

	// UI
	private static Preference subscribeAllOrgsPreference;
	private static Preference subscribeAllETypesPreference;
	private static Preference notificationSoundPreference;

	private static int orgCount;
	private static Set<CheckBoxPreference> orgPreferences = new HashSet<CheckBoxPreference>();

	private static int eTypesCount;
	private static Set<CheckBoxPreference> eTypesPreferences = new HashSet<CheckBoxPreference>();

	private static boolean syncNecessary = false;
	private static SQLiteDatabase dataDb;

	private String action;
	private static SharedPreferences sharedPreferences;

	@SuppressWarnings("deprecation")
	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		sharedPreferences = PreferenceManager.getDefaultSharedPreferences(this);

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
			getActionBar().setDisplayHomeAsUpEnabled(true);
		}

		// Load preferences for older versions
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB) {
			action = getIntent().getAction();
			initDb(this);

			if (action == null) {
				// Load the preferences from an XML resources
				addPreferencesFromResource(R.xml.preferences_events);
				addPreferencesFromResource(R.xml.preference_filters);

				notificationSoundPreference = findPreference("notification_sound");
				initEventsPreferences(this);
			}

			// Handle intents

			// Organizations intent
			if (action != null && action.equals(PREF_ACTION_ORGANIZATIONS)) {
				addPreferencesFromResource(R.xml.preferences_organizations);

				// UI
				subscribeAllOrgsPreference = findPreference(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY);
				subscribeAllOrgsPreference.setOnPreferenceChangeListener(new OrgSubscribtionListener(this, 0));

				PreferenceScreen orgPrefScreen = (PreferenceScreen) findPreference(PREF_ORGANIZATIONS_KEY);
				attachOrgPreferences(this, orgPrefScreen);

				// Event types intent
			} else if (action != null && action.equals(PREF_ACTION_ETYPES)) {
				addPreferencesFromResource(R.xml.preferences_event_types);

				// UI
				subscribeAllETypesPreference = findPreference(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY);
				subscribeAllETypesPreference.setOnPreferenceChangeListener(new EventTypeSubscribtionListener(this, 0));

				PreferenceScreen eTypesPrefScreen = (PreferenceScreen) findPreference(PREF_ETYPES_KEY);
				attachETypesPreferences(this, eTypesPrefScreen);

				// Add intents
			} else {
				Intent organizationsIntent = new Intent(this, getClass());
				organizationsIntent.setAction(PREF_ACTION_ORGANIZATIONS);
				findPreference(MAIN_ORGS_SUBSCRIBTIONS_KEY).setIntent(organizationsIntent);

				Intent eventTypesIntent = new Intent(this, getClass());
				eventTypesIntent.setAction(PREF_ACTION_ETYPES);
				findPreference(MAIN_ETYPES_SUBSCRIBTIONS_KEY).setIntent(eventTypesIntent);
			}

			// Load preferences for newer non-tablet Androids
		} else if (!Helper.isXLargeTablet(this)) {
			getFragmentManager().beginTransaction().replace(android.R.id.content, new NonTabletFragment()).commit();
		}
	}
	
	@SuppressWarnings("deprecation")
	@Override
	public void onResume() {
		super.onResume();
		
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB) {
			initDb(this);
			
			if (getIntent().getAction() == null || getIntent().getAction().equals(PREF_ACTION_ORGANIZATIONS)) {
				createNonAllOrgsSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY, true), this, findPreference(MAIN_ORGS_SUBSCRIBTIONS_KEY));
			}
			
			if (getIntent().getAction() == null || getIntent().getAction().equals(PREF_ACTION_ETYPES)) {
				createNonAllETypesSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY, true), this, findPreference(MAIN_ETYPES_SUBSCRIBTIONS_KEY));
			}
		}
	}

    @Override
	public void onPause() {
		super.onPause();

		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB && action != null && action.equals(PREF_ACTION_ORGANIZATIONS)) {
			dataDb.close();

			// Sync after organizations subscribtion change
			if (syncNecessary) {
				resyncCalendar(this);
			}
		}
	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	@Override
	public void onBuildHeaders(List<Header> target) {
		super.onBuildHeaders(target);

		if (Helper.isXLargeTablet(this)) {
			loadHeadersFromResource(R.xml.preference_headers, target);
		}
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
			case android.R.id.home :
				Helper.goHome(this);
				return true;
			default :
				return super.onOptionsItemSelected(item);
		}
	}

	@Override
	public void onBackPressed() {
		super.onBackPressed();

		if (syncNecessary) {
			resyncCalendar(this);
		}
	}

	private static void attachOrgPreferences(Activity activity, PreferenceScreen orgPrefScreen) {
		// All subscribed?
		boolean allSubscribed = sharedPreferences.getBoolean(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY, true);

		// Create preferences
		PreferenceCategory organizationsCat = new PreferenceCategory(activity);
		organizationsCat.setKey(PREF_ORG_LIST_KEY);
		organizationsCat.setTitle(R.string.org_subscribtions);

		orgPrefScreen.addPreference(organizationsCat);

		orgPreferences.clear();

		Cursor orgCursor = dataDb.query(Organizations.TABLE_NAME, new String[]{Organizations._ID, Organizations.NAME, Organizations.SUBSCRIBED},
		        null, null, null, null, Organizations.NAME);

		if (orgCursor != null && orgCursor.moveToFirst()) {
			orgCount = orgCursor.getCount();
			do {
				Integer id = orgCursor.getInt(orgCursor.getColumnIndex(Organizations._ID));
				String name = orgCursor.getString(orgCursor.getColumnIndex(Organizations.NAME));

				sharedPreferences
				        .edit()
				        .putBoolean(ORG_CHECKBOX_KEY + "_" + String.valueOf(id),
				                orgCursor.getInt(orgCursor.getColumnIndex(Organizations.SUBSCRIBED)) == 1).commit();

				CheckBoxPreference orgPreference = new CheckBoxPreference(activity);

				orgPreference.setKey(ORG_CHECKBOX_KEY + "_" + String.valueOf(id));
				orgPreference.setTitle(name);
				orgPreference.setEnabled(!allSubscribed);
				orgPreference.setOnPreferenceChangeListener(new OrgSubscribtionListener(activity, id));

				organizationsCat.addPreference(orgPreference);
				orgPreferences.add(orgPreference);
			} while (orgCursor.moveToNext());
		}
		if (orgCursor != null) {
			orgCursor.close();
		}
	}

	private static void attachETypesPreferences(Activity activity, PreferenceScreen eTypesPrefScreen) {
		// All subscribed?
		boolean allSubscribed = sharedPreferences.getBoolean(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY, true);

		// Create preferences
		PreferenceCategory eTypesCatategory = new PreferenceCategory(activity);
		eTypesCatategory.setKey(PREF_ETYPES_LIST_KEY);
		eTypesCatategory.setTitle(R.string.event_types_subscribtions);

		eTypesPrefScreen.addPreference(eTypesCatategory);

		eTypesPreferences.clear();

		Cursor eTypesCursor = dataDb.query(EventTypes.TABLE_NAME, new String[]{EventTypes._ID, EventTypes.NAME, EventTypes.SUBSCRIBED}, null, null,
		        null, null, EventTypes.NAME);

		if (eTypesCursor != null && eTypesCursor.moveToFirst()) {
			eTypesCount = eTypesCursor.getCount();
			do {
				Integer id = eTypesCursor.getInt(eTypesCursor.getColumnIndex(EventTypes._ID));
				String name = eTypesCursor.getString(eTypesCursor.getColumnIndex(EventTypes.NAME));

				sharedPreferences
				        .edit()
				        .putBoolean(ETYPES_CHECKBOX_KEY + "_" + String.valueOf(id),
				                eTypesCursor.getInt(eTypesCursor.getColumnIndex(EventTypes.SUBSCRIBED)) == 1).commit();

				CheckBoxPreference eTypePreference = new CheckBoxPreference(activity);

				eTypePreference.setKey(ETYPES_CHECKBOX_KEY + "_" + String.valueOf(id));
				eTypePreference.setTitle(name);
				eTypePreference.setEnabled(!allSubscribed);
				eTypePreference.setOnPreferenceChangeListener(new EventTypeSubscribtionListener(activity, id));

				eTypesCatategory.addPreference(eTypePreference);
				eTypesPreferences.add(eTypePreference);
			} while (eTypesCursor.moveToNext());
		}
		if (eTypesCursor != null) {
			eTypesCursor.close();
		}
	}

	private static void initDb(Activity activity) {
		if (dataDb == null || !dataDb.isOpen()) {
			dataDb = DataDbHelper.getWritableDatabase(activity);
		}
	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	private static void initEventsPreferences(PreferenceFragment fragment) {
		Preference reminderPreference = fragment.findPreference(PREF_REMINDER_KEY);
		reminderPreference.setOnPreferenceChangeListener(new ReminderChangeListener(fragment.getActivity(), true));

		Preference notificationsPreference = fragment.findPreference(PREF_NOTIFICATIONS_KEY);
		notificationsPreference.setOnPreferenceChangeListener(new NotificationsChangeListener(fragment.getActivity()));

		(new ReminderChangeListener(fragment.getActivity(), false)).onPreferenceChange(reminderPreference, fragment.getPreferenceManager()
		        .getSharedPreferences().getString(PREF_REMINDER_KEY, "-1"));

		notificationSoundPreference.setOnPreferenceChangeListener(new NotificationSoundChangeListener(fragment.getActivity()));
		new NotificationSoundChangeListener(fragment.getActivity()).onPreferenceChange(notificationSoundPreference,
		        sharedPreferences.getString(notificationSoundPreference.getKey(), "content://settings/system/notification_sound"));
	}

	@SuppressWarnings("deprecation")
	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	private static void initEventsPreferences(PreferenceActivity activity) {
		Preference reminderPreference = activity.findPreference(PREF_REMINDER_KEY);
		reminderPreference.setOnPreferenceChangeListener(new ReminderChangeListener(activity, true));

		Preference notificationsPreference = activity.findPreference(PREF_NOTIFICATIONS_KEY);
		notificationsPreference.setOnPreferenceChangeListener(new NotificationsChangeListener(activity));

		(new ReminderChangeListener(activity, false)).onPreferenceChange(reminderPreference, activity.getPreferenceManager().getSharedPreferences()
		        .getString(PREF_REMINDER_KEY, "-1"));

		notificationSoundPreference.setOnPreferenceChangeListener(new NotificationSoundChangeListener(activity));
		new NotificationSoundChangeListener(activity).onPreferenceChange(notificationSoundPreference,
		        sharedPreferences.getString(notificationSoundPreference.getKey(), "content://settings/system/notification_sound"));
	}

	private static void createNonAllOrgsSummary(boolean isAllChecked, Context context, Preference mainPreference) {
		int subscribtionsCount = 0;

		// Get count of selected organizations
		if (!isAllChecked) {
			Cursor cursor = dataDb.query(Organizations.TABLE_NAME, new String[]{Organizations._ID}, Organizations.SUBSCRIBED + "=1", null, null,
			        null, null);

			if (cursor != null) {
				subscribtionsCount = cursor.getCount();
				cursor.close();
			}
		}

		String resourceString;
		if (isAllChecked || orgCount == subscribtionsCount) {
			resourceString = context.getResources().getString(R.string.organizations_summary_all);
		} else if (subscribtionsCount == 0) {
			resourceString = context.getResources().getString(R.string.subscribtion_summary_none);
		} else if (subscribtionsCount == 1) {
			resourceString = context.getResources().getString(R.string.organizations_summary_count_1);
		} else if (subscribtionsCount >= 2 && subscribtionsCount <= 4) {
			resourceString = context.getResources().getString(R.string.organizations_summary_count_24)
			        .replaceAll(":count:", String.valueOf(subscribtionsCount));
		} else {
			resourceString = context.getResources().getString(R.string.organizations_summary_count)
			        .replaceAll(":count:", String.valueOf(subscribtionsCount));
		}

		// Set summary
		if (subscribeAllOrgsPreference != null) {
			subscribeAllOrgsPreference.setSummary(resourceString);
		}
		if (mainPreference != null) {
			mainPreference.setSummary(resourceString);
		}
	}

	private static void createNonAllETypesSummary(boolean isAllChecked, Context context, Preference mainPreference) {
		int subscribtionsCount = 0;

		// Get count of selected organizations
		if (!isAllChecked) {
			Cursor cursor = dataDb.query(EventTypes.TABLE_NAME, new String[]{EventTypes._ID}, EventTypes.SUBSCRIBED + "=1", null, null, null, null);

			subscribtionsCount = 0;
			if (cursor != null) {
				subscribtionsCount = cursor.getCount();
				cursor.close();
			}
		}

		String resourceString;
		if (isAllChecked || eTypesCount == subscribtionsCount) {
			resourceString = context.getResources().getString(R.string.event_types_summary_all);
		} else if (subscribtionsCount == 0) {
			resourceString = context.getResources().getString(R.string.subscribtion_summary_none);
		} else if (subscribtionsCount == 1) {
			resourceString = context.getResources().getString(R.string.event_types_summary_count_1);
		} else if (subscribtionsCount >= 2 && subscribtionsCount <= 4) {
			resourceString = context.getResources().getString(R.string.event_types_summary_count_24)
			        .replaceAll(":count:", String.valueOf(subscribtionsCount));
		} else {
			resourceString = context.getResources().getString(R.string.event_types_summary_count)
			        .replaceAll(":count:", String.valueOf(subscribtionsCount));
		}

		// Set summary
		if (subscribeAllETypesPreference != null) {
			subscribeAllETypesPreference.setSummary(resourceString);
		}
		if (mainPreference != null) {
			mainPreference.setSummary(resourceString);
		}
	}

	private static class ReminderChangeListener implements OnPreferenceChangeListener {

		private boolean updateEvents;
		private Preference preference;
		private String newValue;
		private Context context;

		public ReminderChangeListener(Context context, boolean updateEvents) {
			this.context = context;
			this.updateEvents = updateEvents;
		}

		@Override
		public boolean onPreferenceChange(Preference preference, Object newValue) {
			this.preference = preference;
			this.newValue = (String) newValue;

			handleReminderSummary();
			if (updateEvents) {
				updateEventReminders();
			}
			return true;
		}

		private void handleReminderSummary() {
			int preferenceValue = Integer.valueOf(newValue);
			int resource = -1;

			if (preferenceValue == -1) {
				resource = R.string.reminder_summary_none;
			} else if (preferenceValue == 1) {
				resource = R.string.reminder_summary_1;
			} else if (preferenceValue == 5) {
				resource = R.string.reminder_summary_5;
			} else if (preferenceValue == 10) {
				resource = R.string.reminder_summary_10;
			} else if (preferenceValue == 15) {
				resource = R.string.reminder_summary_15;
			} else if (preferenceValue == 30) {
				resource = R.string.reminder_summary_30;
			} else if (preferenceValue == 45) {
				resource = R.string.reminder_summary_45;
			} else if (preferenceValue == 60) {
				resource = R.string.reminder_summary_60;
			} else if (preferenceValue == 120) {
				resource = R.string.reminder_summary_120;
			} else if (preferenceValue == 360) {
				resource = R.string.reminder_summary_360;
			} else if (preferenceValue == 1440) {
				resource = R.string.reminder_summary_1440;
			} else if (preferenceValue == 7200) {
				resource = R.string.reminder_summary_7200;
			} else {
				resource = R.string.reminder_summary_none;
			}

			preference.setSummary(resource);
		}

		private void updateEventReminders() {
			Account[] accounts = AccountManager.get(context).getAccountsByType(Constants.ACCOUNT_TYPE);

			int calendarId = CalendarSyncAdapter.loadCalendarId(context);
			Uri eventsUri = Helper.eventsUri().buildUpon().appendQueryParameter(android.provider.CalendarContract.CALLER_IS_SYNCADAPTER, "true")
			        .appendQueryParameter(Calendars.ACCOUNT_NAME, accounts[0].name)
			        .appendQueryParameter(Calendars.ACCOUNT_TYPE, Constants.ACCOUNT_TYPE).build();

			// Update events HAS_ALARM value
			if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
				ContentValues values = new ContentValues();
				values.put(Events.HAS_ALARM, !newValue.equals("-1"));

				context.getContentResolver().update(eventsUri, values, Events.CALENDAR_ID + "=? AND " + Events.DTSTART + ">?",
				        new String[]{String.valueOf(calendarId), String.valueOf((new Date()).getTime())});
			}

			// Get events list
			Cursor cursor = context.getContentResolver().query(eventsUri, new String[]{Events._ID},
			        Events.CALENDAR_ID + "=? AND " + Events.DTSTART + ">?",
			        new String[]{String.valueOf(calendarId), String.valueOf((new Date()).getTime())}, null);

			// Append / delete reminders
			if (cursor != null && cursor.moveToFirst()) {
				do {
					int eventId = cursor.getInt(cursor.getColumnIndex(Events._ID));

					if (newValue.equals("-1")) {
						context.getContentResolver().delete(Helper.remindersUri(), Reminders.EVENT_ID + "=?", new String[]{String.valueOf(eventId)});
					} else {
						CalendarSyncAdapter.appendReminder(context, eventId, Integer.parseInt(newValue));
					}
				} while (cursor.moveToNext());
			}

			if (cursor != null) {
				cursor.close();
			}
		}
	}

	private static class NotificationSoundChangeListener implements OnPreferenceChangeListener {

		private Context context;

		public NotificationSoundChangeListener(Context context) {
			this.context = context;
		}

		@Override
		public boolean onPreferenceChange(Preference preference, Object newValue) {
			Uri ringtoneUri = Uri.parse((String) newValue);
			Ringtone ringtone = RingtoneManager.getRingtone(context, ringtoneUri);
			String name;

			try {
				name = ringtone.getTitle(context);
			} catch (NullPointerException ex) {
				name = "";
			}

			if (RingtoneManager.isDefault(ringtoneUri)) {
				name = context.getString(R.string.default_one);
			}

			if (newValue.equals("")) {
				name = context.getString(R.string.silent);
			}

			preference.setSummary(name);
			return true;
		}

	}

	private static class NotificationsChangeListener implements OnPreferenceChangeListener {

		private Context context;

		public NotificationsChangeListener(Context context) {
			this.context = context;
		}

		@Override
		public boolean onPreferenceChange(Preference preference, Object newValue) {
			boolean showNotifications = (Boolean) newValue;
			if (!showNotifications) {
				NotificationManager notificationManager = (NotificationManager) context.getSystemService(NOTIFICATION_SERVICE);
				notificationManager.cancel(Constants.NOTIFICATION_NEW_EVENT_ID);
				CalendarSyncAdapter.NEW_EVENTS.clear();
			}
			return true;
		}

	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public static class EventsFragment extends PreferenceFragment {

		@Override
		public void onCreate(Bundle savedInstanceState) {
			super.onCreate(savedInstanceState);

			// Load the preferences from an XML resource
			addPreferencesFromResource(R.xml.preferences_events);
			notificationSoundPreference = findPreference("notification_sound");

			initEventsPreferences(this);
		}
	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public static class OrganizationsFragment extends PreferenceFragment {

		@Override
		public void onCreate(Bundle savedInstanceState) {
			super.onCreate(savedInstanceState);
			initDb(getActivity());

			// Load the preferences from an XML resource
			addPreferencesFromResource(R.xml.preferences_organizations);

			// UI
			subscribeAllOrgsPreference = findPreference(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY);
			subscribeAllOrgsPreference.setOnPreferenceChangeListener(new OrgSubscribtionListener(getActivity(), 0));

			PreferenceScreen orgPrefScreen = (PreferenceScreen) findPreference(PREF_ORGANIZATIONS_KEY);
			attachOrgPreferences(getActivity(), orgPrefScreen);

			createNonAllOrgsSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY, true), getActivity(), findPreference(MAIN_ORGS_SUBSCRIBTIONS_KEY));
		}

		@Override
		public void onPause() {
			super.onPause();
			dataDb.close();

			// Sync after organizations subscribtion change
			if (syncNecessary) {
				resyncCalendar(getActivity());
			}
		}

		@Override
		public void onResume() {
			super.onResume();
			initDb(getActivity());
		}
	}

	private static class OrgSubscribtionListener implements OnPreferenceChangeListener {

		private int orgId;
		private Activity activity;

		public OrgSubscribtionListener(Activity activity, int orgId) {
			this.activity = activity;
			this.orgId = orgId;
		}

		@Override
		public boolean onPreferenceChange(Preference preference, Object newValue) {
			syncNecessary = true;
			boolean value = (Boolean) newValue;

			// Subscribe all
			if (orgId == 0) {
				for (CheckBoxPreference checkBoxPreference : orgPreferences) {
					checkBoxPreference.setEnabled(!value);
				}
				createNonAllOrgsSummary(value, activity, null);

				return true;

				// Subscribe particular
			} else {
				ContentValues values = new ContentValues();
				values.put(Organizations.SUBSCRIBED, value);

				int updatedRows = dataDb.update(Organizations.TABLE_NAME, values, Organizations._ID + "=?", new String[]{String.valueOf(orgId)});

				// Summary
				createNonAllOrgsSummary(false, activity, null);

				return updatedRows > 0;
			}
		}
	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public static class EventTypesFragment extends PreferenceFragment {

		@Override
		public void onCreate(Bundle savedInstanceState) {
			super.onCreate(savedInstanceState);
			initDb(getActivity());

			// Load the preferences from an XML resource
			addPreferencesFromResource(R.xml.preferences_event_types);

			// UI
			subscribeAllETypesPreference = findPreference(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY);
			subscribeAllETypesPreference.setOnPreferenceChangeListener(new EventTypeSubscribtionListener(getActivity(), 0));

			PreferenceScreen eTypesPrefScreen = (PreferenceScreen) findPreference(PREF_ETYPES_KEY);
			attachETypesPreferences(getActivity(), eTypesPrefScreen);

			createNonAllETypesSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY, true), getActivity(), findPreference(MAIN_ETYPES_SUBSCRIBTIONS_KEY));
		}

		@Override
		public void onPause() {
			super.onPause();
			dataDb.close();

			// Sync after subscribtion change
			if (syncNecessary) {
				resyncCalendar(getActivity());
			}
		}

		@Override
		public void onResume() {
			super.onResume();
			initDb(getActivity());
		}
	}

	private static class EventTypeSubscribtionListener implements OnPreferenceChangeListener {

		private int eventTypeId;
		private Activity activity;

		public EventTypeSubscribtionListener(Activity activity, int eventTypeId) {
			this.activity = activity;
			this.eventTypeId = eventTypeId;
		}

		@Override
		public boolean onPreferenceChange(Preference preference, Object newValue) {
			syncNecessary = true;
			boolean value = (Boolean) newValue;

			// Subscribe all
			if (eventTypeId == 0) {
				for (CheckBoxPreference checkBoxPreference : eTypesPreferences) {
					checkBoxPreference.setEnabled(!value);
				}
				createNonAllETypesSummary(value, activity, null);

				return true;

				// Subscribe particular
			} else {
				ContentValues values = new ContentValues();
				values.put(EventTypes.SUBSCRIBED, value);

				int updatedRows = dataDb.update(EventTypes.TABLE_NAME, values, EventTypes._ID + "=?", new String[]{String.valueOf(eventTypeId)});

				// Summary
				createNonAllETypesSummary(false, activity, null);

				return updatedRows > 0;
			}
		}
	}

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public static class NonTabletFragment extends PreferenceFragment {

		@Override
		public void onCreate(Bundle savedInstanceState) {
			super.onCreate(savedInstanceState);
			initDb(getActivity());

			// Load the preferences from an XML resources
			addPreferencesFromResource(R.xml.preferences_events);
			addPreferencesFromResource(R.xml.preference_filters);

			notificationSoundPreference = findPreference("notification_sound");
			initEventsPreferences(this);

			// Handle intents
			String intentAction = getActivity().getIntent().getAction();

			// Organizations intent
			if (intentAction != null && intentAction.equals(PREF_ACTION_ORGANIZATIONS)) {
				getFragmentManager().beginTransaction().replace(android.R.id.content, new OrganizationsFragment()).commit();

				// Event types intent
			} else if (intentAction != null && intentAction.equals(PREF_ACTION_ETYPES)) {
				getFragmentManager().beginTransaction().replace(android.R.id.content, new EventTypesFragment()).commit();

				// Add intents
			} else {
				Intent organizationsIntent = new Intent(getActivity(), getActivity().getClass());
				organizationsIntent.setAction(PREF_ACTION_ORGANIZATIONS);
				findPreference(MAIN_ORGS_SUBSCRIBTIONS_KEY).setIntent(organizationsIntent);

				Intent eventTypesIntent = new Intent(getActivity(), getActivity().getClass());
				eventTypesIntent.setAction(PREF_ACTION_ETYPES);
				findPreference(MAIN_ETYPES_SUBSCRIBTIONS_KEY).setIntent(eventTypesIntent);
			}
		}

		@Override
		public void onResume() {
			super.onResume();
			initDb(getActivity());

			String intentAction = getActivity().getIntent().getAction();
			
			if (intentAction == null || intentAction.equals(PREF_ACTION_ORGANIZATIONS)) {
				createNonAllOrgsSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ORGS_SUBSCRIBE_TO_ALL_KEY, true), getActivity(), findPreference(MAIN_ORGS_SUBSCRIBTIONS_KEY));
			}
			
			if (intentAction == null || intentAction.equals(PREF_ACTION_ETYPES)) {
				createNonAllETypesSummary(getPreferenceManager().getSharedPreferences().getBoolean(PREF_ETYPES_SUBSCRIBE_TO_ALL_KEY, true), getActivity(), findPreference(MAIN_ETYPES_SUBSCRIBTIONS_KEY));
			}
		}

		@Override
		public void onPause() {
			super.onPause();
			dataDb.close();

			// Sync after subscribtion change
			if (syncNecessary) {
				resyncCalendar(getActivity());
			}
		}
	}

	private static void resyncCalendar(Context context) {
		Bundle extras = new Bundle();
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);
		extras.putBoolean(CalendarSyncAdapter.NO_NOTIFICATIONS_EXTRA, true);

		Account[] accounts = AccountManager.get(context).getAccountsByType(Constants.ACCOUNT_TYPE);
		ContentResolver.requestSync(accounts[0], "com.android.calendar", extras);

		syncNecessary = false;
	}
}
