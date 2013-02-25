package cz.vse.myevents;

import java.io.InputStream;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;

import com.google.android.gcm.GCMBaseIntentService;

import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.xml.GcmRegisterWebParser;
import cz.vse.myevents.xml.GcmUnregisterWebParser;
import cz.webcomplete.tools.Hasher;

public class GCMIntentService extends GCMBaseIntentService {

	public GCMIntentService() {
		super(Constants.GCM_SENDER_ID);
    }
	
	@Override
    protected void onError(Context arg0, String errorId) {
    }

	@Override
    protected void onMessage(Context context, Intent intent) {
		boolean syncEvents = intent.getStringExtra("syncEvents").equals("true");
		boolean syncData = intent.getStringExtra("syncData").equals("true");
		boolean forced = intent.getStringExtra("forced").equals("true");

		// Get account
		Account[] accounts = AccountManager.get(context).getAccountsByType(Constants.ACCOUNT_TYPE);
		
		// Create extras
		Bundle extras = new Bundle();
		
		// Forced sync
		if (forced) {
			extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
			extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);
		}
		
		// Sync events
		if (syncEvents) {
			ContentResolver.requestSync(accounts[0], "com.android.calendar", extras);
		}
		
		// Sync app data
		if (syncData) {
			ContentResolver.requestSync(accounts[0], Constants.APP_DATA_AUTHORITY, extras);
		}
    }

	@Override
    protected void onRegistered(Context context, String regId) {
	    // Create auth token
		String authToken = Hasher.sha256(Constants.GCM_AUTH_SALT + regId);
		
		// Build stream
		StringBuilder urlBuilder = new StringBuilder();
	    urlBuilder
	    	.append("http://").append(Constants.SERVER_URL).append("/xml/registerGcm/") // Base
	    	.append(authToken).append("/") // Auth token
	    	.append(regId); // Reg ID

	    InputStream inputStream;
	    try {
	        inputStream = Helper.loadWebStream(urlBuilder.toString());
        } catch (WebContentNotReachedException e) {
	        return;
        }
	    
	    new GcmRegisterWebParser(inputStream);
    }

	@Override
    protected void onUnregistered(Context context, String regId) {
	    // Create auth token
		String authToken = Hasher.sha256(Constants.GCM_AUTH_SALT + regId);
		
		// Build stream
		StringBuilder urlBuilder = new StringBuilder();
	    urlBuilder
	    	.append("http://").append(Constants.SERVER_URL).append("/xml/unregisterGcm/") // Base
	    	.append(authToken).append("/") // Auth token
	    	.append(regId); // Reg ID

	    InputStream inputStream;
	    try {
	        inputStream = Helper.loadWebStream(urlBuilder.toString());
        } catch (WebContentNotReachedException e) {
	        return;
        }
	    
	    new GcmUnregisterWebParser(inputStream);
    }
}
