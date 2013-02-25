package cz.vse.myevents.misc;

import android.os.Build;
import android.provider.CalendarContract.Calendars;

public class Constants {

    public static final String SERVER_URL = "myevents.vse.cz";
    public static final String ACCOUNT_TYPE = "cz.vse.myevents";
    public static final String APP_DATA_AUTHORITY = "cz.vse.myevents.appdata";
    public static final String FACEBOOK_ACOCUNT_TYPE = "com.facebook.auth.login";
    public static final int NOTIFICATION_NEW_EVENT_ID = 1;
    
    // Sync periodicity in minutes
    public static final long SYNC_PERIODICITY_CALENDAR = 60L * 24L * 7L;	
    public static final long SYNC_PERIODICITY_DATA = SYNC_PERIODICITY_CALENDAR;

    // Salts used to make auth tokens for server data exchange
    public static final String USER_REGISTRATION_AUTH_SALT = "CapHeC9a";
    public static final String BUG_REPORT_AUTH_SALT = "f6eWRuwr";
    public static final String PASSWORD_RECOVERY_AUTH_SALT = "bEb7QuCh";
    public static final String FB_REGISTER_AUTH_SALT = "Cefre9u8";
    public static final String GCM_AUTH_SALT = "s8atUbru";
    
    // GCM sender ID
    public static final String GCM_SENDER_ID = "217157633735";

    // Version compatibility constants
    public static final String CALENDAR_ACCOUNT_TYPE = Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH ? "_sync_account_type"
	    : Calendars.ACCOUNT_TYPE;

    private Constants() {
    }

}
