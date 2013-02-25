package cz.vse.myevents.activity;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.Calendar;
import java.util.Date;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.animation.Animator;
import android.animation.AnimatorListenerAdapter;
import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.app.SearchManager;
import android.content.ContentResolver;
import android.content.ContentUris;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager.NameNotFoundException;
import android.content.res.Configuration;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.provider.CalendarContract;
import android.provider.CalendarContract.Events;
import android.text.TextUtils;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.WindowManager;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.Button;
import android.widget.EditText;
import android.widget.GridView;
import android.widget.SearchView;
import android.widget.Toast;

import com.google.ads.AdView;
import com.google.android.gcm.GCMRegistrar;

import cz.vse.myevents.R;
import cz.vse.myevents.account.sync.CalendarSyncAdapter;
import cz.vse.myevents.account.sync.SyncListener;
import cz.vse.myevents.account.sync.TalkingSyncAdapter;
import cz.vse.myevents.adapter.OverviewImageAdapter;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.misc.Instances;
import cz.vse.myevents.serverdata.Event;
import cz.vse.myevents.xml.BugReportWebParser;
import cz.webcomplete.tools.Hasher;

public class OverviewActivity extends Activity implements SyncListener {

	public static final int EVENTS_SHOWN = 6;
	
	private static final String NOTIF_QUESTION_ASKED_PREFKEY = "notifQuestionAsked";
	
	private Activity activity = this;

	private Account[] accounts;
	private boolean nothingShown;

	private AlertDialog bugReportDialog;
	private BugReportTask bugReportTask;

	private static final int PROGRESS_SYNC = 1;
	private static final int PROGRESS_BUG_REPORT = 2;

	private AdView adView;
	private GridView mainView;

	private boolean syncInProgress = false;
	private boolean firstProgressShown = false;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		Instances.setOverviewActivity(this);
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_overview);

		adView = (AdView) findViewById(R.id.ad_overview);
		mainView = (GridView) findViewById(R.id.overview_screen);
		mainView.setOnItemClickListener(new EventInfoRequestListener());

		manageOrientation();
		handleIntentToasts();
		registerGcm();
	}

	@Override
	public void onResume() {
		super.onResume();

		checkForLogin();

		if (!syncInProgress) {
			initGridAdapter();
		}

		if (getResources().getConfiguration().orientation == Configuration.ORIENTATION_PORTRAIT) {
			Helper.loadAd(adView);
		}
	}
	
	
	@TargetApi(Build.VERSION_CODES.HONEYCOMB)
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_overview, menu);

		// Search widget
		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
			SearchManager searchManager = (SearchManager) getSystemService(Context.SEARCH_SERVICE);
			SearchView searchView = (SearchView) menu.findItem(R.id.menu_search).getActionView();
			searchView.setSearchableInfo(searchManager.getSearchableInfo(getComponentName()));
		}

		return super.onCreateOptionsMenu(menu);
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		// Handle item selection
		switch (item.getItemId()) {
			case R.id.menu_settings :
				showSettings();
				return true;
			case R.id.menu_search :
				onSearchRequested();
				return true;
			case R.id.menu_open_calendar :
				openCalendar();
				return true;
			case R.id.menu_refresh :
				refreshData();
				return true;
			case R.id.menu_logout :
				logout();
				return true;
			case R.id.menu_report_bug :
				showBugReportDialog();
				return true;
			case R.id.menu_about :
				showAboutInfo();
				return true;
			case R.id.menu_help :
				Helper.goToHelp(this);
				return true;
			default :
				return super.onOptionsItemSelected(item);
		}
	}

	@Override
	public void onSyncStart(final TalkingSyncAdapter syncAdapter) {
		syncInProgress = true;
		runOnUiThread(new Runnable() {

			@Override
			public void run() {
				if ((nothingShown && syncAdapter instanceof CalendarSyncAdapter)
				        || (getIntent().getBooleanExtra(LoginActivity.IS_AFTER_LOGIN_KEY, false) && !firstProgressShown)) {
					showProgress(true, PROGRESS_SYNC);
					firstProgressShown = true;
				}
			}

		});
	}

	@Override
	public void onSyncEnd(final TalkingSyncAdapter syncAdapter) {
		syncInProgress = false;
		final Activity activity = this;

		runOnUiThread(new Runnable() {

			@Override
			public void run() {
				if (syncAdapter instanceof CalendarSyncAdapter) {
					initGridAdapter();
					showProgress(false, PROGRESS_SYNC);
				}
				
				// Show notifications dialog after first login
				if (	
						syncAdapter instanceof CalendarSyncAdapter
						&& getIntent().getBooleanExtra(LoginActivity.IS_AFTER_LOGIN_KEY, false)
				        && !getPreferences(Context.MODE_PRIVATE).getBoolean(NOTIF_QUESTION_ASKED_PREFKEY, false)
				    ) {
					Builder dialogBuilder = new AlertDialog.Builder(activity);
					dialogBuilder.setIcon(R.drawable.ic_question);
					dialogBuilder.setTitle(R.string.notifications);
					dialogBuilder.setMessage(R.string.notifications_question);
					dialogBuilder.setPositiveButton(android.R.string.yes, new NotificationsAgreedListener());
					dialogBuilder.setNegativeButton(android.R.string.no, null);
					dialogBuilder.show();

					getPreferences(Context.MODE_PRIVATE).edit().putBoolean(NOTIF_QUESTION_ASKED_PREFKEY, true).commit();
				}
			}

		});
	}

	private void registerGcm() {
		try {
			GCMRegistrar.checkDevice(this);
			GCMRegistrar.checkManifest(this);
		} catch (UnsupportedOperationException ex) {
			return;
		}
		if (GCMRegistrar.getRegistrationId(this).equals("")) {
			GCMRegistrar.register(this, Constants.GCM_SENDER_ID);
		}
	}

	private void refreshData() {
		showProgress(true, PROGRESS_SYNC);

		Bundle extras = new Bundle();
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

		ContentResolver.requestSync(accounts[0], Constants.APP_DATA_AUTHORITY, extras);
		ContentResolver.requestSync(accounts[0], "com.android.calendar", extras);
	}

	private void initGridAdapter() {
		int calendarId = CalendarSyncAdapter.loadCalendarId(this);

		if (calendarId != 0) {
			SQLiteDatabase dataDb = DataDbHelper.getReadableDatabase(this);
			OverviewImageAdapter adapter = new OverviewImageAdapter(this);

			Cursor cursor = getContentResolver().query(Helper.eventsUri(), null, Events.CALENDAR_ID + "=?", new String[]{String.valueOf(calendarId)},
			        Events.DTSTART + " LIMIT " + EVENTS_SHOWN);

			// Add events
			if (cursor != null && cursor.moveToFirst()) {
				do {
					adapter.add(Event.createFromCursor(cursor, dataDb));
				} while (cursor.moveToNext());
			}

			// No events
			if (cursor == null || cursor.getCount() <= 0) {
				handleEmptyEvents(false);
			} else {
				nothingShown = false;
			}
			
			if (cursor != null) {
				cursor.close();
			}

			mainView.setAdapter(adapter);
			if (dataDb != null && dataDb.isOpen()) {
				dataDb.close();
			}
		} else {
			handleEmptyEvents(true);
		}
	}

	private void manageOrientation() {
		int orientation = getResources().getConfiguration().orientation;
		mainView.setNumColumns(orientation == Configuration.ORIENTATION_PORTRAIT ? 2 : 3);
	}

	private void handleIntentToasts() {
		// Multi-accounting restricted
		if (getIntent().getBooleanExtra(LoginActivity.MULTIACCOUNTING_RESTRICTED_KEY, false)) {
			Toast.makeText(this, R.string.multiaccounting_restricted, Toast.LENGTH_LONG).show();
		}

		// Search result
		if (getIntent().getBooleanExtra(SearchActivity.RESULT_NOT_FOUND_KEY, false)) {
			Toast.makeText(this, R.string.search_nothing_found, Toast.LENGTH_LONG).show();
		}
	}

	private void showSettings() {
		Intent intent = new Intent(this, SettingsActivity.class);
		startActivity(intent);
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	private void openCalendar() {
		Uri baseUri;
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
			baseUri = Uri.parse("content://com.android.calendar");
		} else {
			baseUri = CalendarContract.CONTENT_URI;
		}

		Uri.Builder uriBuilder = baseUri.buildUpon().appendPath("time");
		ContentUris.appendId(uriBuilder, (new Date()).getTime());

		Intent intent = new Intent(Intent.ACTION_VIEW).setData(uriBuilder.build());
		startActivity(intent);
	}

	private void logout() {
		for (Account account : accounts) {
			AccountManager.get(this).removeAccount(account, null, null);
		}

		Intent intent = new Intent(this, LoginActivity.class);
		intent.putExtra(LoginActivity.MULTIACCOUNTING_CHECK_SKIP_KEY, true);
		startActivity(intent);
		finish();
	}

	private void showBugReportDialog() {
		ConnectivityManager connMgr = (ConnectivityManager) activity.getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			Helper.showWarning(this, R.string.connection_error, R.string.no_internet_connection);
			return;
		}

		Builder dialogBuilder = new AlertDialog.Builder(this);
		bugReportDialog = dialogBuilder
			.setView(getLayoutInflater().inflate(R.layout.dialog_bug_report, null))
		    .setPositiveButton(R.string.send, null)
		    .setNeutralButton(R.string.cancel, null)
		    .create();
		
		bugReportDialog.setOnShowListener(new BugReportShowListener());
		bugReportDialog.show();
		
		// Focus input field
		EditText detail = (EditText) bugReportDialog.findViewById(R.id.bug_report_detail);
		detail.setOnFocusChangeListener(new View.OnFocusChangeListener() {
		    @Override
		    public void onFocusChange(View v, boolean hasFocus) {
		        if (hasFocus) {
		        	bugReportDialog.getWindow().setSoftInputMode(WindowManager.LayoutParams.SOFT_INPUT_STATE_ALWAYS_VISIBLE);
		        }
		    }
		});
		detail.requestFocus();
	}

	private void showAboutInfo() {
		String year = String.valueOf(Calendar.getInstance().get(Calendar.YEAR));
		String version;
		try {
			version = getPackageManager().getPackageInfo(getPackageName(), 0).versionName;
		} catch (NameNotFoundException e) {
			version = "";
		}

		String message = getResources().getString(R.string.about_info).replaceAll(":lastYear:", year).replaceAll(":version:", version);

		Helper.showDialog(this, getResources().getString(R.string.menu_about), message);
	}

	private void handleEmptyEvents(boolean noSyncDone) {
		nothingShown = true;
		findViewById(R.id.overview_screen).setVisibility(View.GONE);
		if (noSyncDone) {
			showProgress(true, PROGRESS_SYNC);
		} else {
			findViewById(R.id.no_events_message).setVisibility(View.VISIBLE);
		}
	}

	private void checkForLogin() {
		accounts = AccountManager.get(this).getAccountsByType(Constants.ACCOUNT_TYPE);
		if (accounts.length == 0) {
			Helper.goToLogin(this);
			return;
		}
		
		// Check for data clear
		if (PreferenceManager.getDefaultSharedPreferences(this).getLong(LoginActivity.LOGIN_DATE_PREFKEY, 0) == 0) {
			logout();
		}
	}

	/**
	 * Shows the progress UI and hides the login form.
	 */
	@TargetApi(Build.VERSION_CODES.HONEYCOMB_MR2)
	private void showProgress(final boolean show, int type) {
		// Orientation possibility
		int orientationType = show ? ActivityInfo.SCREEN_ORIENTATION_NOSENSOR : ActivityInfo.SCREEN_ORIENTATION_SENSOR;
		setRequestedOrientation(orientationType);

		final View statusView;
		final View mainView = nothingShown ? findViewById(R.id.no_events_message) : findViewById(R.id.overview_screen);

		// Find status view
		switch (type) {
			case PROGRESS_BUG_REPORT :
				statusView = findViewById(R.id.bug_report_status);
				break;
			case PROGRESS_SYNC :
				statusView = findViewById(R.id.sync_status);
				break;
			default :
				statusView = findViewById(R.id.bug_report_status);
		}

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB_MR2) {
			int shortAnimTime = getResources().getInteger(android.R.integer.config_shortAnimTime);

			statusView.setVisibility(View.VISIBLE);
			statusView.animate().setDuration(shortAnimTime).alpha(show ? 1 : 0).setListener(new AnimatorListenerAdapter() {
				@Override
				public void onAnimationEnd(Animator animation) {
					statusView.setVisibility(show ? View.VISIBLE : View.GONE);
				}
			});

			mainView.animate().setDuration(shortAnimTime).alpha(show ? 0 : 1).setListener(new AnimatorListenerAdapter() {
				@Override
				public void onAnimationEnd(Animator animation) {
					mainView.setVisibility(show ? View.GONE : View.VISIBLE);
				}
			});
		} else {
			statusView.setVisibility(show ? View.VISIBLE : View.GONE);
			mainView.setVisibility(show ? View.GONE : View.VISIBLE);
		}
	}

	private class EventInfoRequestListener implements OnItemClickListener {

		@Override
		public void onItemClick(AdapterView<?> parent, View v, int position, long id) {
			Event event = (Event) parent.getItemAtPosition(position);

			Uri uri = ContentUris.withAppendedId(Helper.eventsUri(), event.getId());
			Intent intent = new Intent(Intent.ACTION_VIEW).setData(uri);
			intent.putExtra("beginTime", event.getStartDate().getTime());
			intent.putExtra("endTime", event.getEndDate().getTime());
			startActivity(intent);
		}

	}
	
	private class BugReportShowListener implements DialogInterface.OnShowListener {

	    @Override
	    public void onShow(final DialogInterface dialog) {

	        Button b = ((AlertDialog) dialog).getButton(AlertDialog.BUTTON_POSITIVE);
	        b.setOnClickListener(new View.OnClickListener() {

	            @Override
	            public void onClick(View view) {
	            	EditText text = (EditText) ((AlertDialog) dialog).findViewById(R.id.bug_report_detail);
	    			if (TextUtils.isEmpty(text.getText().toString().trim())) {
	    				text.setError(getString(R.string.error_field_required));
	    				return;
	    			}

	    			showProgress(true, PROGRESS_BUG_REPORT);
	    			bugReportTask = new BugReportTask();
	    			bugReportTask.execute((Void) null);

	                //Dismiss once everything is OK.
	            	((AlertDialog) dialog).dismiss();
	            }
	        });
	    }
	}

	public class BugReportTask extends AsyncTask<Void, Void, Boolean> {

		private int status;

		@Override
		protected Boolean doInBackground(Void... params) {
			// Version
			PackageInfo pInfo;
			String version;
			try {
				pInfo = getPackageManager().getPackageInfo(getPackageName(), 0);
				version = Build.VERSION.RELEASE + " - " + pInfo.versionName;
			} catch (NameNotFoundException e1) {
				version = Build.VERSION.RELEASE + " - ?";
			}

			// Create auth token
			String[] versionSplitted = version.split("-");
			String authToken = Hasher.sha256(versionSplitted[0] + "-" + Constants.BUG_REPORT_AUTH_SALT + versionSplitted[1]);

			// Get text
			EditText detailField = (EditText) bugReportDialog.findViewById(R.id.bug_report_detail);
			String text = detailField.getText().toString().replaceAll("/", "&#47;").trim();

			// Build URL
			StringBuilder urlBuilder = new StringBuilder();
			try {
				urlBuilder.append("http://").append(Constants.SERVER_URL).append("/xml/androidBugReport/")
				// Base
				        .append(authToken).append("/")
				        // Auth token
				        .append(URLEncoder.encode(version, "UTF-8")).append("/")
				        // Version
				        .append(URLEncoder.encode(accounts[0].name, "UTF-8")).append("/") // Email
				        .append(URLEncoder.encode(text, "UTF-8")); // Text

			} catch (UnsupportedEncodingException e) {
				status = BugReportWebParser.STATUS_FAILED;
				return false;
			}

			BugReportWebParser bugReportWebParser;
			try {
				bugReportWebParser = new BugReportWebParser(Helper.loadWebStream(urlBuilder.toString()));
			} catch (WebContentNotReachedException e) {
				status = BugReportWebParser.STATUS_FAILED;
				return false;
			}

			status = bugReportWebParser.getStatus();
			if (status == BugReportWebParser.STATUS_OK) {
				return true;
			} else {
				return false;
			}
		}

		@Override
		protected void onPostExecute(final Boolean success) {
			showProgress(false, PROGRESS_BUG_REPORT);
			bugReportTask = null;

			if (success) {
				Toast.makeText(activity, R.string.bug_report_sent, Toast.LENGTH_LONG).show();
			} else {
				Toast.makeText(activity, R.string.bug_report_failed, Toast.LENGTH_LONG).show();
			}
		}

		@Override
		protected void onCancelled() {
			showProgress(false, PROGRESS_BUG_REPORT);
		}
	}

	private class NotificationsAgreedListener implements DialogInterface.OnClickListener {

		@Override
		public void onClick(DialogInterface dialog, int which) {
			PreferenceManager.getDefaultSharedPreferences(activity).edit().putBoolean(SettingsActivity.PREF_NOTIFICATIONS_KEY, true).commit();
		}

	}
}
