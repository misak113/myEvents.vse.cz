package cz.vse.myevents.activity;

import java.util.Date;

import android.annotation.TargetApi;
import android.app.ListActivity;
import android.app.SearchManager;
import android.content.ContentUris;
import android.content.Intent;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.provider.CalendarContract.Events;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import cz.vse.myevents.account.sync.CalendarSyncAdapter;
import cz.vse.myevents.adapter.EventAdapter;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.serverdata.Event;

public class SearchActivity extends ListActivity {

	public static final String RESULT_NOT_FOUND_KEY = "resultNotFound";

	private EventAdapter adapter;
	private String lastQuery;

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		handleIntent(getIntent());

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
			getActionBar().setDisplayHomeAsUpEnabled(true);
		}
	}

	@Override
	public void onResume() {
		super.onResume();
		doSearch(lastQuery);
	}

	@Override
	public void onNewIntent(Intent intent) {
		super.onNewIntent(intent);
		setIntent(intent);
		handleIntent(intent);
	}

	@Override
	public void onListItemClick(ListView l, View v, int position, long id) {
		super.onListItemClick(l, v, position, id);

		Event event = adapter.getItem(position);
		Uri uri = ContentUris.withAppendedId(Helper.eventsUri(), event.getId());

		Intent intent = new Intent(Intent.ACTION_VIEW).setData(uri);
		intent.putExtra("beginTime", event.getStartDate().getTime());
		intent.putExtra("endTime", event.getEndDate().getTime());

		startActivity(intent);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		case android.R.id.home:
			Helper.goHome(this);
			return true;
		default:
			return super.onOptionsItemSelected(item);
		}
	}

	private void handleIntent(Intent intent) {
		if (Intent.ACTION_SEARCH.equals(intent.getAction())) {
			lastQuery = intent.getStringExtra(SearchManager.QUERY);
			doSearch(lastQuery);
		}
	}

	private void doSearch(String queryStr) {
		adapter = new EventAdapter(this);
		queryStr = queryStr.replaceAll(" ", "%");
		SQLiteDatabase dataDb = DataDbHelper.getReadableDatabase(this);

		// Query database
		int calendarId = CalendarSyncAdapter.loadCalendarId(this);
		Cursor cursor = getContentResolver().query(
				Helper.eventsUri(),
				new String[] { Events._ID, Events.TITLE, Events.EVENT_LOCATION,
						Events.DESCRIPTION, Events.DTSTART, Events.DTEND },
				"(" + Events.TITLE + " LIKE ? OR " + Events.EVENT_LOCATION
						+ " LIKE ?) AND " + Events.CALENDAR_ID + "=? AND "
						+ Events.DTSTART + ">?",
				new String[] { "%" + queryStr + "%", "%" + queryStr + "%",
						String.valueOf(calendarId),
						String.valueOf((new Date()).getTime()) },
				Events.DTSTART);

		if (cursor.moveToFirst()) { // Results found
			do {
				Event event = Event.createFromCursor(cursor, dataDb);
				adapter.add(event);
			} while (cursor.moveToNext());
		} else { // No results found, go back to overview
			Intent intent = new Intent(this, OverviewActivity.class);
			intent.putExtra(RESULT_NOT_FOUND_KEY, true);
			intent.setFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);

			startActivity(intent);
		}

		if (cursor != null) {
			cursor.close();
		}
		
		if (dataDb != null && dataDb.isOpen()) {
			dataDb.close();
		}

		setListAdapter(adapter);
	}
}