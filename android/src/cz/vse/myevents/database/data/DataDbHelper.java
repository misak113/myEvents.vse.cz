package cz.vse.myevents.database.data;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.ContentResolver;
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.os.Bundle;
import cz.vse.myevents.misc.Constants;

public class DataDbHelper extends SQLiteOpenHelper {
	
	private Context context;

    public DataDbHelper(Context context) {
        super(context, DataContract.DATABASE_NAME, null, DataContract.DATABASE_VERSION);
        this.context = context;
    }
    
    @Override
    public void onCreate(SQLiteDatabase db) {
    	DataContract.createEntries(db);
    }
    
    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
    	DataContract.deleteEntries(db);
        onCreate(db);
        
        // Resync data
        Account[] accounts = AccountManager.get(context).getAccountsByType(Constants.ACCOUNT_TYPE);
        Bundle extras = new Bundle();
        extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);
        
        for (Account account : accounts) {
        	ContentResolver.requestSync(
        	        account,
        	        Constants.APP_DATA_AUTHORITY,
        	        extras);
        }
    }
    
    public static SQLiteDatabase getReadableDatabase(Context context) {
    	return (new DataDbHelper(context)).getReadableDatabase();
    }
    
    public static SQLiteDatabase getWritableDatabase(Context context) {
    	return (new DataDbHelper(context)).getWritableDatabase();
    }
}
