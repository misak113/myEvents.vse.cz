package cz.vse.myevents.account.sync;

import android.annotation.TargetApi;
import android.content.AbstractThreadedSyncAdapter;
import android.content.Context;
import android.os.Build;

public abstract class TalkingSyncAdapter extends AbstractThreadedSyncAdapter {

	public TalkingSyncAdapter(Context context, boolean autoInitialize) {
		super(context, autoInitialize);
	}
	
	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public TalkingSyncAdapter(Context context, boolean autoInitialize,
			boolean allowParallelSyncs) {
		super(context, autoInitialize, allowParallelSyncs);
	}

	public void tellSyncStart(SyncListener listener) {
		if (listener == null) {
			return;
		}
		
		listener.onSyncStart(this);
	}

	public void tellSyncEnd(SyncListener listener) {
		if (listener == null) {
			return;
		}
		
		listener.onSyncEnd(this);
	}

}
