package cz.vse.myevents.activity;

import java.util.ArrayList;
import java.util.List;

import android.annotation.TargetApi;
import android.app.ListActivity;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.ListView;
import cz.vse.myevents.R;
import cz.vse.myevents.adapter.IconAdapter;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.misc.IconizedText;

public class HelpContentActivity extends ListActivity {
	
	private IconAdapter adapter;

	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		
		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
			getActionBar().setDisplayHomeAsUpEnabled(true);
		}
		
		initContent();
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
	
	public void onListItemClick(ListView l, View v, int position, long id) {
		super.onListItemClick(l, v, position, id);

		String action = null;
		switch (position) {
		case 0:
			action = HelpChapterActivityLegacy.ACTION_LOGIN;
			break;
		case 1:
			action = HelpChapterActivityLegacy.ACTION_EVENTS;
			break;
		case 2:
			action = HelpChapterActivityLegacy.ACTION_SETTINGS;
			break;
		}
		
		if (action != null) {
			Class<?> activityClass;
			if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
				activityClass = HelpChapterActivity.class;
			} else {
				activityClass = HelpChapterActivityLegacy.class;
			}
			
			Intent intent = new Intent(action, null, this, activityClass);
			startActivity(intent);
		}
	}
	
	private void initContent() {
		adapter = new IconAdapter(this);
		
		List<IconizedText> texts = new ArrayList<IconizedText>();
		texts.add(new IconizedText(R.string.help_chapter_login, android.R.drawable.ic_menu_my_calendar));
		texts.add(new IconizedText(R.string.help_chapter_events, android.R.drawable.ic_menu_month));
		texts.add(new IconizedText(R.string.help_chapter_settings, android.R.drawable.ic_menu_manage));
		
		for (IconizedText text : texts) {
			adapter.add(text);
		}
		
		setListAdapter(adapter);
	}
}
