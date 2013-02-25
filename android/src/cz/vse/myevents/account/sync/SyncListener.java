package cz.vse.myevents.account.sync;

public interface SyncListener {
	public void onSyncStart(TalkingSyncAdapter syncAdapter);
	public void onSyncEnd(TalkingSyncAdapter syncAdapter);
}
