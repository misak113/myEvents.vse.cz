package cz.vse.myevents.notification;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import cz.vse.myevents.account.sync.CalendarSyncAdapter;

public class NotificationBroadcastReceiver extends BroadcastReceiver {

	@Override
	public void onReceive(Context context, Intent intent) {
		CalendarSyncAdapter.NEW_EVENTS.clear();
	}

}
