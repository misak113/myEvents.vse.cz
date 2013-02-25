package cz.vse.myevents.database.data;

import android.database.sqlite.SQLiteDatabase;
import android.provider.BaseColumns;

public class DataContract {

	public static final String DATABASE_NAME = "Data.db";
	public static final int DATABASE_VERSION = 2;

	private DataContract() {
	}
	
	public static void createEntries(SQLiteDatabase db) {
		db.execSQL(Organizations.SQL_CREATE_ENTRIES);
		db.execSQL(EventTypes.SQL_CREATE_ENTRIES);
		db.execSQL(EventOrganizators.SQL_CREATE_ENTRIES);
		db.execSQL(EventInfo.SQL_CREATE_ENTRIES);
	}
	
	public static void deleteEntries(SQLiteDatabase db) {
		db.execSQL(Organizations.SQL_DELETE_ENTRIES);
		db.execSQL(EventTypes.SQL_DELETE_ENTRIES);
		db.execSQL(EventOrganizators.SQL_DELETE_ENTRIES);
		db.execSQL(EventInfo.SQL_DELETE_ENTRIES);
	}

	public static abstract class Organizations implements BaseColumns {
		public static final String TABLE_NAME = "organization";
		public static final String NAME = "name";
		public static final String WEBSITE = "website";
		public static final String INFO = "info";
		public static final String EMAIL = "email";
		public static final String FB_LINK = "fbLink";
		public static final String CONTACT_PERSON = "contactPerson";
		public static final String SUBSCRIBED = "subscribed";

	    private static final String SQL_CREATE_ENTRIES =
	    	    "CREATE TABLE IF NOT EXISTS " + TABLE_NAME + " ( " +
	    	    _ID + " INTEGER PRIMARY KEY," +
	    	    NAME + " VARCHAR(255)," +
	    	    WEBSITE + " VARCHAR(255)," +
	    	    INFO + " TEXT," +
	    	    EMAIL + " VARCHAR(255)," +
	    	    FB_LINK + " VARCHAR(255)," +
	    	    CONTACT_PERSON + " VARCHAR(255)," +
	    	    SUBSCRIBED + " BOOLEAN" +
	    	    " )";
	    
		private static final String SQL_DELETE_ENTRIES =
			    "DROP TABLE IF EXISTS " + TABLE_NAME;
	}

	public static abstract class EventTypes implements BaseColumns {
		public static final String TABLE_NAME = "event_type";
		public static final String NAME = "name";
		public static final String SUBSCRIBED = "subscribed";
		
		private static final String SQL_CREATE_ENTRIES =
	    	    "CREATE TABLE IF NOT EXISTS " + TABLE_NAME + " ( " +
	    	    _ID + " INTEGER PRIMARY KEY," +
	    	    NAME + " VARCHAR(255)," +
	    	    SUBSCRIBED + " BOOLEAN" +
	    	    " )";
	    
		private static final String SQL_DELETE_ENTRIES =
			    "DROP TABLE IF EXISTS " + TABLE_NAME;
	}
	
	public static abstract class EventOrganizators {
		public static final String TABLE_NAME = "event_organizator";
		public static final String EVENT_ID = "event_id";
		public static final String ORGANIZATION_ID = "organization_id";

	    private static final String SQL_CREATE_ENTRIES =
	    	    "CREATE TABLE IF NOT EXISTS " + TABLE_NAME + " ( " +
	    	    EVENT_ID + " INTEGER," +
	    	    ORGANIZATION_ID + " INTEGER" +
	    	    " )";
	    
		private static final String SQL_DELETE_ENTRIES =
			    "DROP TABLE IF EXISTS " + TABLE_NAME;
	}
	
	public static abstract class EventInfo implements BaseColumns {
		public static final String TABLE_NAME = "event_info";
		public static final String EVENT_ID = "event_id";
		public static final String CRC = "crc32";

	    private static final String SQL_CREATE_ENTRIES =
	    	    "CREATE TABLE IF NOT EXISTS " + TABLE_NAME + " ( " +
	    	    _ID + " INTEGER PRIMARY KEY," +
	    	    EVENT_ID + " BIGINT," +
	    	    CRC + " INTEGER" +
	    	    " )";
	    
		private static final String SQL_DELETE_ENTRIES =
			    "DROP TABLE IF EXISTS " + TABLE_NAME;
	}
}
