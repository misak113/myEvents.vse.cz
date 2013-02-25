package cz.vse.myevents.account.sync;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

import android.accounts.Account;
import android.annotation.TargetApi;
import android.content.ContentProviderClient;
import android.content.ContentResolver;
import android.content.ContentValues;
import android.content.Context;
import android.content.PeriodicSync;
import android.content.SyncResult;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.Build;
import android.os.Bundle;
import cz.vse.myevents.database.data.DataContract.EventTypes;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.filefilter.OrganizationLogoFileFilter;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.misc.Instances;
import cz.vse.myevents.serverdata.EventType;
import cz.vse.myevents.serverdata.Organization;
import cz.vse.myevents.xml.EventTypesWebParser;
import cz.vse.myevents.xml.OrganizationsWebParser;

public class DataSyncAdapter extends TalkingSyncAdapter {

	public static final String SYNC_EVENTS_AFTER_EXTRA = "syncEventsAfter";

	private SQLiteDatabase dataDb;

	private Set<Organization> dbOrganizations = new HashSet<Organization>();
	private Set<Organization> serverOrganizations = new HashSet<Organization>();

	private Set<EventType> dbEventTypes = new HashSet<EventType>();
	private Set<EventType> serverEventTypes = new HashSet<EventType>();

	public DataSyncAdapter(Context context, boolean autoInitialize) {
		super(context, autoInitialize);
	}

	@Override
	public void onPerformSync(Account account, Bundle extras, String authority, ContentProviderClient provider, SyncResult syncResult) {
		ConnectivityManager connMgr = (ConnectivityManager) getContext().getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			return;
		}

		tellSyncStart(Instances.getOverviewActivity());
		initDb();
		// Organizations
		boolean orgDataLoaded = true;
		try {
			loadOrgDbData();
			loadOrgServerData();
		} catch (WebContentNotReachedException ex) {
			orgDataLoaded = false;
		}
		if (orgDataLoaded) {
			syncOrgList();
		}

		// Event types
		boolean eTypesDataLoaded = true;
		try {
			loadETypesDbData();
			loadETypesServerData();
		} catch (WebContentNotReachedException ex) {
			eTypesDataLoaded = false;
		}
		if (eTypesDataLoaded) {
			syncEventTypesList();
		}

		closeDb();
		tellSyncEnd(Instances.getOverviewActivity());

		// Sync events if required
		if (extras.getBoolean(SYNC_EVENTS_AFTER_EXTRA)) {
			Bundle syncExtras = new Bundle();
			syncExtras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
			syncExtras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

			ContentResolver.requestSync(account, "com.android.calendar", syncExtras);
		}
		
		// Set periodic sync
		List<PeriodicSync> periodicSyncs = ContentResolver.getPeriodicSyncs(account, Constants.APP_DATA_AUTHORITY);
		if (periodicSyncs.size() != 1) {
			periodicSyncs.clear();
			ContentResolver.addPeriodicSync(account, Constants.APP_DATA_AUTHORITY, new Bundle(), Constants.SYNC_PERIODICITY_DATA * 60L);
		}
	}

	private Set<Organization> downloadOrgServerData() throws WebContentNotReachedException {
		InputStream inputStream;
		inputStream = Helper.loadWebStream("http://" + Constants.SERVER_URL + "/xml/organizations");

		OrganizationsWebParser organizationsWebParser = new OrganizationsWebParser(inputStream);
		return organizationsWebParser.getOrganizations();
	}

	private Set<EventType> downloadETypesServerData() throws WebContentNotReachedException {
		InputStream inputStream;
		inputStream = Helper.loadWebStream("http://" + Constants.SERVER_URL + "/xml/eventTypes");

		EventTypesWebParser eventTypesWebParser = new EventTypesWebParser(inputStream);
		return eventTypesWebParser.getEventTypes();
	}

	private void insertDataToOrgDb(SQLiteDatabase db, Organization organization) {
		ContentValues values = new ContentValues();

		values.put(Organizations._ID, organization.getId());
		values.put(Organizations.NAME, organization.getName());
		try {
			values.put(Organizations.WEBSITE, organization.getWebsite().toString());
		} catch (NullPointerException ex) {
		}
		values.put(Organizations.INFO, organization.getInfo());
		values.put(Organizations.EMAIL, organization.getEmail());
		try {
			values.put(Organizations.FB_LINK, organization.getFbLink().toString());
		} catch (NullPointerException ex) {
		}
		values.put(Organizations.CONTACT_PERSON, organization.getContactPerson());
		values.put(Organizations.SUBSCRIBED, false);

		db.insert(Organizations.TABLE_NAME, Organizations.NAME, values);
	}

	private void insertDataToETypesDb(SQLiteDatabase db, EventType type) {
		ContentValues values = new ContentValues();

		values.put(EventTypes._ID, type.getId());
		values.put(EventTypes.NAME, type.getName());

		db.insert(EventTypes.TABLE_NAME, EventTypes.NAME, values);
	}

	private void initDb() {
		if (dataDb == null || !dataDb.isOpen()) {
			dataDb = DataDbHelper.getWritableDatabase(getContext());
		}
	}

	private void closeDb() {
		dataDb.close();
	}

	private void syncOrgList() {
		// Remove old organizations
		for (Organization dbOrganization : dbOrganizations) {
			if (!serverOrganizations.contains(dbOrganization)) {
				dataDb.delete(Organizations.TABLE_NAME, Organizations._ID + "=?", new String[]{String.valueOf(dbOrganization.getId())});
			}
		}

		deleteUselessLogos();

		// Add new organizations
		for (Organization serverOrganization : serverOrganizations) {
			if (!dbOrganizations.contains(serverOrganization)) {
				insertDataToOrgDb(dataDb, serverOrganization);
			}

			downloadOrganizationLogo(serverOrganization);
		}
	}

	private void syncEventTypesList() {
		// Remove old event types
		for (EventType dbEventType : dbEventTypes) {
			if (!serverEventTypes.contains(dbEventType)) {
				dataDb.delete(EventTypes.TABLE_NAME, EventTypes._ID + "=?", new String[]{String.valueOf(dbEventType.getId())});
			}
		}

		// Add new event types
		for (EventType serverEventType : serverEventTypes) {
			if (!dbEventTypes.contains(serverEventType)) {
				insertDataToETypesDb(dataDb, serverEventType);
			}
		}
	}

	private void loadOrgDbData() {
		Cursor cursor = dataDb.query(Organizations.TABLE_NAME, null, null, null, null, null, null);

		dbOrganizations.clear();
		if (cursor != null && cursor.moveToFirst()) {
			do {
				dbOrganizations.add(Organization.createFromCursor(cursor));
			} while (cursor.moveToNext());
		}

		if (cursor != null) {
			cursor.close();
		}
	}

	private void loadETypesDbData() {
		Cursor cursor = dataDb.query(EventTypes.TABLE_NAME, null, null, null, null, null, null);

		dbEventTypes.clear();
		if (cursor != null && cursor.moveToFirst()) {
			do {
				dbEventTypes.add(EventType.createFromCursor(cursor));
			} while (cursor.moveToNext());
		}

		if (cursor != null) {
			cursor.close();
		}
	}

	private void loadOrgServerData() throws WebContentNotReachedException {
		serverOrganizations = downloadOrgServerData();
	}

	private void loadETypesServerData() throws WebContentNotReachedException {
		serverEventTypes = downloadETypesServerData();
	}

	private String buildOrgLogoName(Organization organziation) {
		if (organziation.getServerLogoUrl() == null) {
			return null;
		}

		String[] urlSplitted = organziation.getServerLogoUrl().split("\\.");
		String fileSuffix = (urlSplitted[urlSplitted.length - 1]).toLowerCase();

		StringBuilder nameBuilder = new StringBuilder();
		nameBuilder.append(OrganizationLogoFileFilter.FILENAME_PREFIX) // Base
		        .append(String.valueOf(organziation.getId())).append("_") // ID
		        .append(organziation.getServerLogoCrc()) // CRC32
		        .append(".").append(fileSuffix); // Suffix

		return nameBuilder.toString();
	}

	@TargetApi(Build.VERSION_CODES.GINGERBREAD)
	private void downloadOrganizationLogo(Organization organization) {
		if (organization == null || organization.getServerLogoUrl() == null) {
			return;
		}

		String fileName = buildOrgLogoName(organization);

		// Delete old one
		File orgLogosDir = new File(getContext().getFilesDir().getAbsolutePath());
		File[] orgLogos = orgLogosDir.listFiles(new OrganizationLogoFileFilter(organization.getId()));

		for (File orgLogo : orgLogos) {
			String[] imagePathSplitted = orgLogo.getPath().split(File.separator);
			String imageFileName = imagePathSplitted[imagePathSplitted.length - 1];

			if (!fileName.equals(imageFileName)) {
				orgLogo.delete();
			}
		}

		// Download new one
		if (organization.getServerLogoUrl() != null && organization.getServerLogoUrl() != "") {
			File outFile = new File(getContext().getFilesDir(), fileName);

			if (!outFile.exists()) {
				try {
					BufferedInputStream inStream = new BufferedInputStream(Helper.loadWebStream(organization.getServerLogoUrl()));
					FileOutputStream fileStream = getContext().openFileOutput(fileName, Context.MODE_PRIVATE);
					BufferedOutputStream outStream = new BufferedOutputStream(fileStream, 1024);
					byte[] data = new byte[1024];

					ConnectivityManager connMgr = (ConnectivityManager) getContext().getSystemService(Context.CONNECTIVITY_SERVICE);
					int bytesRead = 0;
					while ((bytesRead = inStream.read(data, 0, data.length)) >= 0) {
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
				} catch (WebContentNotReachedException ex) {
				} catch (FileNotFoundException ex) {
				} catch (IOException ex) {
				}
			}
		}
	}
	private void deleteUselessLogos() {
		// Delete images of not existing organizations
		Cursor cursor = dataDb.query(Organizations.TABLE_NAME, new String[]{Organizations._ID}, null, null, null, null, null);

		Set<Integer> existingOrgIds = new HashSet<Integer>();
		if (cursor != null && cursor.moveToFirst()) {
			do {
				existingOrgIds.add(cursor.getInt(cursor.getColumnIndex(Organizations._ID)));
			} while (cursor.moveToNext());
		}

		if (cursor != null) {
			cursor.close();
		}

		File[] files = getContext().getFilesDir().listFiles(new OrganizationLogoFileFilter());
		for (File file : files) {
			String[] pathSplitted = file.getPath().split(File.separator);
			String fileName = pathSplitted[pathSplitted.length - 1];
			String[] fileNameSplitted = fileName.split("_");
			int fileOrgId = Integer.parseInt(fileNameSplitted[1]);

			if (!existingOrgIds.contains(fileOrgId)) {
				file.delete();
			}
		}
	}
}
