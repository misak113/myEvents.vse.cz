package cz.vse.myevents.activity;

import java.util.Collections;

import android.annotation.TargetApi;
import android.app.ListActivity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import cz.vse.myevents.account.sync.CalendarSyncAdapter;
import cz.vse.myevents.adapter.EventAdapter;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.serverdata.Event;

public class EventsListActivity extends ListActivity {

	public static final String NEW_EVENTS_ACTION = "cz.vse.myevents.newevents";
	
	private EventAdapter adapter;

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		Intent intent = getIntent();

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
			getActionBar().setDisplayHomeAsUpEnabled(true);
		}
		
		// Show list
		adapter = new EventAdapter(this);
		
		if (intent.getAction().equals(NEW_EVENTS_ACTION)) {
			Collections.sort(CalendarSyncAdapter.NEW_EVENTS);
			
			if (Build.VERSION.SDK_INT < Build.VERSION_CODES.HONEYCOMB) {
				for (Event event : CalendarSyncAdapter.NEW_EVENTS) {
					adapter.add(event);
				}
			} else {
				adapter.addAll(CalendarSyncAdapter.NEW_EVENTS);
			}
			setListAdapter(adapter);
		}
	}

	@Override
	public void onResume() {
		super.onResume();
	}

	@Override
	public void onListItemClick(ListView l, View v, int position, long id) {
		super.onListItemClick(l, v, position, id);

		Event event = adapter.getItem(position);
		Intent intent = Helper.getCalendarEventIntent(event);

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
}