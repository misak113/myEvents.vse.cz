package cz.vse.myevents.account.sync;

import android.app.Service;
import android.content.Intent;
import android.os.IBinder;

public class DataSyncService extends Service {

    private static DataSyncAdapter syncAdapter = null;

	public DataSyncService() {
		super();
	}
	
	@Override
	public void onCreate() {
		super.onCreate();
		if (syncAdapter == null) {
            syncAdapter = new DataSyncAdapter(getApplicationContext(), true);
        }
		
		
	}

	@Override
	public IBinder onBind(Intent intent) {
		return syncAdapter.getSyncAdapterBinder();
	}
}
