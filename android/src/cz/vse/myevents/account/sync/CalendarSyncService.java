package cz.vse.myevents.account.sync;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

public class CalendarSyncService extends Service {
	
    private static CalendarSyncAdapter syncAdapter = null;

	public CalendarSyncService() {
		super();
	}
	
	@Override
	public void onCreate() {
		super.onCreate();
		if (syncAdapter == null) {
            syncAdapter = new CalendarSyncAdapter(getApplicationContext(), true);
        }
		
		
	}

	@Override
	public IBinder onBind(Intent intent) {
		return syncAdapter.getSyncAdapterBinder();
	}

}
