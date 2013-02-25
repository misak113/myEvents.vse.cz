package cz.vse.myevents.activity;

import android.app.Activity;
import android.os.Bundle;
import android.webkit.WebView;
import android.widget.LinearLayout.LayoutParams;
import cz.vse.myevents.R;

public class HelpChapterActivityLegacy extends Activity {

	public static final String ACTION_LOGIN = "cz.vse.myevents.help.login";
	public static final String ACTION_EVENTS = "cz.vse.myevents.help.eventsSubscribtion";
	public static final String ACTION_SETTINGS = "cz.vse.myevents.help.settings";

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		
		// Find out chapter
		String action = getIntent().getAction();
		String chapter = null;
		int title = 0;
		
		if (action.equals(ACTION_LOGIN)) {
			chapter = "login";
			title = R.string.help_chapter_login;
		} else if (action.equals(ACTION_EVENTS)) {
			chapter = "events";
			title = R.string.help_chapter_events;
		} else if (action.equals(ACTION_SETTINGS)) {
			chapter = "settings";
			title = R.string.help_chapter_settings;
		}
		
		setTitle(title);
		
		// Create web view
		WebView webView = new WebView(this);
		webView.setLayoutParams(new LayoutParams(LayoutParams.MATCH_PARENT, LayoutParams.MATCH_PARENT));
		webView.loadUrl("file:///android_asset/help-" + getString(R.string.lang_code) + "/" + chapter + ".html");
		setContentView(webView);
	}
}
