package cz.vse.myevents.activity;

import java.io.InputStream;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.Date;

import android.accounts.Account;
import android.accounts.AccountAuthenticatorActivity;
import android.accounts.AccountManager;
import android.animation.Animator;
import android.animation.AnimatorListenerAdapter;
import android.annotation.TargetApi;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.content.ContentResolver;
import android.content.Context;
import android.content.DialogInterface;
import android.content.DialogInterface.OnClickListener;
import android.content.Intent;
import android.content.pm.ActivityInfo;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.text.TextUtils;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Toast;

import com.facebook.Request;
import com.facebook.Response;
import com.facebook.Session;
import com.facebook.SessionState;
import com.facebook.model.GraphUser;
import com.google.ads.AdView;

import cz.vse.myevents.R;
import cz.vse.myevents.exception.UserNotFoundException;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.xml.FbRegistrationWebParser;
import cz.vse.myevents.xml.LoginWebParser;
import cz.vse.myevents.xml.PasswordRecoveryWebParser;
import cz.vse.myevents.xml.SaltWebParser;
import cz.webcomplete.data.types.Password;
import cz.webcomplete.tools.Hasher;

/**
 * Activity which displays a login screen to the user, offering registration as
 * well.
 */
public class LoginActivity extends AccountAuthenticatorActivity {
	public static final String MULTIACCOUNTING_RESTRICTED_KEY = "multiAccountingRestricted";
	public static final String MULTIACCOUNTING_CHECK_SKIP_KEY = "skipMultiAccountingCheck";
	public static final String FACEBOOK_ID_KEY = "cz.vse.myevents.facebookId";
	public static final String IS_AFTER_LOGIN_KEY = "cz.vse.myevents.isAfterLogin";
	public static final String LOGIN_DATE_PREFKEY = "loginDate";
	private static final String IS_FB_LOGIN = "cz.vse.myevents.isFbLogin";

	private static final String TOKEN_SALT = "9HA7Ekef";

	private AccountAuthenticatorActivity activity = this;
	private AccountManager accountManager;

	private AlertDialog passwordRecoveryDialog;
	private PasswordRecoveryTask passwordRecoveryTask;

	/**
	 * Keep track of the login task to ensure we can cancel it if requested.
	 */
	private UserLoginTask authTask = null;

	// Values for email and password at the time of the login attempt.
	private String email;
	private String password;

	// UI references.
	private EditText emailView;
	private EditText passwordView;
	private View loginFormView;
	private View loginStatusView;
	private View passwordRecoveryStatusView;
	private View focusView = null;
	private AdView adView;

	private boolean facebookInUse;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		// Account manager
		accountManager = AccountManager.get(this);

		if (!isOneAccountOnly()) {
			return;
		}

		setContentView(R.layout.activity_login);

		// Adjust layout for tablets
		if (Helper.isXLargeTablet(this)) {
			findViewById(R.id.login_layout).setPadding(200, 200, 200, 200);

			View fbLoginButton = findViewById(R.id.sign_in_fb_button);
			LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) fbLoginButton.getLayoutParams();
			params.setMargins(0, 0, 50, 0);
			fbLoginButton.setLayoutParams(params);
		}

		// Set up the login form.
		emailView = (EditText) findViewById(R.id.login_email);
		emailView.setText(email);

		loginFormView = findViewById(R.id.login_form);
		loginStatusView = findViewById(R.id.login_status);

		passwordRecoveryStatusView = findViewById(R.id.password_recovery_status);

		passwordView = (EditText) findViewById(R.id.login_password);

		// Load ad
		adView = (AdView) findViewById(R.id.ad_login);
		Helper.loadAd(adView);
	}

	@Override
	public void onResume() {
		super.onResume();
		detectFacebook();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		super.onCreateOptionsMenu(menu);
		getMenuInflater().inflate(R.menu.activity_login, menu);
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		// Handle item selection
		switch (item.getItemId()) {
			case R.id.menu_register :
				showRegistryForm();
				return true;
			case R.id.menu_lost_password :
				startRecoveringPassword();
				return true;
			case R.id.menu_help :
				Helper.goToHelp(this);
				return true;
			default :
				return super.onOptionsItemSelected(item);
		}
	}

	@Override
	public void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		Session.getActiveSession().onActivityResult(this, requestCode, resultCode, data);
	}

	public void attemptFbLogin(View view) {
		ConnectivityManager connMgr = (ConnectivityManager) activity.getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			Helper.showWarning(this, R.string.login_error, R.string.no_internet_connection);
			return;
		}

		// start Facebook Login
		InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
		imm.hideSoftInputFromWindow(emailView.getWindowToken(), 0);
		imm.hideSoftInputFromWindow(passwordView.getWindowToken(), 0);

		showProgress(true, true);

		try {
			Session.openActiveSession(this, true, new Session.StatusCallback() {

				// callback when session changes state
				@Override
				public void call(Session session, SessionState state, Exception exception) {
					if (session.isOpened()) {
						// make request to the /me API
						Request.executeMeRequestAsync(session, new Request.GraphUserCallback() {

							// callback after Graph API response
							// with user
							// object
							@Override
							public void onCompleted(GraphUser user, Response response) {
								showProgress(false, true);

								if (user != null) {
									finishFbLogin(user);
								} else {
									Helper.showWarning(activity, R.string.error, R.string.fb_login_failed);
								}
							}
						});
					}
				}
			});
		} catch (Exception ex) {
			showProgress(false, true);
			Helper.showWarning(activity, R.string.error, R.string.fb_login_failed);
		}
	}

	private void finishFbLogin(GraphUser user) {
		Account[] fbAccounts = AccountManager.get(this).getAccountsByType(Constants.FACEBOOK_ACOCUNT_TYPE);
		if (fbAccounts.length < 1) {
			return;
		}

		Account fbAccount = fbAccounts[0];

		// Create auth token
		String[] emailSplitted = fbAccount.name.split("@");
		String authToken = Hasher.sha256(emailSplitted[0] + Constants.FB_REGISTER_AUTH_SALT + "@" + emailSplitted[1]);

		// Create URL
		StringBuilder urlBuilder = new StringBuilder();
		urlBuilder.append("http://" + Constants.SERVER_URL + "/xml/registerFbUser/") // Base
		        .append(authToken).append("/") // Auth token
		        .append(user.getId()).append("/") // Facebook ID
		        .append(fbAccount.name).append("/") // Email
		        .append(user.getName()).append("/"); // Name

		// Send data to server
		try {
			new FbRegistrationWebParser(Helper.loadWebStream(urlBuilder.toString()));
		} catch (WebContentNotReachedException e) {
		}

		// Create account
		createAccount(fbAccount.name, null, user.getId());

		// Store login date
		PreferenceManager.getDefaultSharedPreferences(this).edit().putLong(LOGIN_DATE_PREFKEY, (new Date()).getTime()).commit();
		goToHome();
	}

	/**
	 * Attempts to sign in or register the account specified by the login form.
	 * If there are form errors (invalid email, missing fields, etc.), the
	 * errors are presented and no actual login attempt is made.
	 */
	public void attemptLogin(View view) {
		if (authTask != null) {
			return;
		}

		ConnectivityManager connMgr = (ConnectivityManager) activity.getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			Helper.showWarning(this, R.string.login_error, R.string.no_internet_connection);
			return;
		}

		// Reset errors.
		emailView.setError(null);
		passwordView.setError(null);

		// Store values at the time of the login attempt.
		email = emailView.getText().toString();
		password = passwordView.getText().toString();

		boolean cancel = !checkData();

		if (cancel) {
			focusView.requestFocus();
		} else {
			InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
			imm.hideSoftInputFromWindow(emailView.getWindowToken(), 0);
			imm.hideSoftInputFromWindow(passwordView.getWindowToken(), 0);

			showProgress(true, true);
			authTask = new UserLoginTask();
			authTask.execute((Void) null);
		}
	}

	private void showRegistryForm() {
		Intent intent = new Intent(this, RegistrationActivity.class);
		startActivity(intent);
	}

	private void startRecoveringPassword() {
		ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
		if (networkInfo == null || !networkInfo.isConnected()) {
			Helper.showWarning(this, R.string.login_error, R.string.no_internet_connection);
			return;
		}

		Builder dialogBuilder = new AlertDialog.Builder(this);
		dialogBuilder.setView(getLayoutInflater().inflate(R.layout.dialog_recover_password, null))
		        .setPositiveButton(R.string.recover, new PasswordRecoveryListener()).setNeutralButton(R.string.cancel, null);
		passwordRecoveryDialog = dialogBuilder.show();
	}

	private boolean checkData() {
		// Check for a valid email address.
		if (TextUtils.isEmpty(email)) {
			emailView.setError(getString(R.string.error_field_required));
			focusView = emailView;
			return false;
		} else if (!Helper.checkEmail(email)) {
			emailView.setError(getString(R.string.error_invalid_email));
			focusView = emailView;
			return false;
		}

		// Check for a valid password.
		if (TextUtils.isEmpty(password)) {
			passwordView.setError(getString(R.string.error_field_required));
			focusView = passwordView;
			return false;
		} else if (password.length() < Password.MIN_LENGTH) {
			passwordView.setError(getString(R.string.error_invalid_password));
			focusView = passwordView;
			return false;
		}

		return true;
	}

	/**
	 * Shows the progress UI and hides the login form.
	 */
	@TargetApi(Build.VERSION_CODES.HONEYCOMB_MR2)
	private void showProgress(final boolean show, boolean login) {
		// Orientation possibility
		int orientationType = show ? ActivityInfo.SCREEN_ORIENTATION_NOSENSOR : ActivityInfo.SCREEN_ORIENTATION_SENSOR;
		setRequestedOrientation(orientationType);

		final View statusView = login ? loginStatusView : passwordRecoveryStatusView;

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB_MR2) {
			int shortAnimTime = getResources().getInteger(android.R.integer.config_shortAnimTime);

			statusView.setVisibility(View.VISIBLE);
			statusView.animate().setDuration(shortAnimTime).alpha(show ? 1 : 0).setListener(new AnimatorListenerAdapter() {
				@Override
				public void onAnimationEnd(Animator animation) {
					statusView.setVisibility(show ? View.VISIBLE : View.GONE);
				}
			});

			loginFormView.setVisibility(View.VISIBLE);
			loginFormView.animate().setDuration(shortAnimTime).alpha(show ? 0 : 1).setListener(new AnimatorListenerAdapter() {
				@Override
				public void onAnimationEnd(Animator animation) {
					loginFormView.setVisibility(show ? View.GONE : View.VISIBLE);
				}
			});
		} else {
			// The ViewPropertyAnimator APIs are not available, so simply show
			// and hide the relevant UI components.
			statusView.setVisibility(show ? View.VISIBLE : View.GONE);
			loginFormView.setVisibility(show ? View.GONE : View.VISIBLE);
		}
	}

	private boolean isOneAccountOnly() {
		// Check is supposed to be skipped
		if (getIntent().getBooleanExtra(MULTIACCOUNTING_CHECK_SKIP_KEY, false)) {
			return true;
		}

		Account[] accounts = accountManager.getAccountsByType(Constants.ACCOUNT_TYPE);

		if (accounts.length >= 1) {
			Intent intent = new Intent(this, OverviewActivity.class);
			intent.putExtra(MULTIACCOUNTING_RESTRICTED_KEY, true);
			intent.addFlags(Intent.FLAG_ACTIVITY_PREVIOUS_IS_TOP);

			startActivity(intent);
			finish();
			return false;
		}

		return true;
	}

	private void detectFacebook() {
		Account[] accounts = AccountManager.get(this).getAccountsByType(Constants.FACEBOOK_ACOCUNT_TYPE);
		facebookInUse = accounts.length > 0;

		if (facebookInUse) {
			findViewById(R.id.sign_in_fb_button).setVisibility(View.VISIBLE);
			findViewById(R.id.sign_in_type_divider).setVisibility(View.VISIBLE);
		}
	}

	private Account createAccount(String name, Password password, String fbId) {
		String stringPassword = password == null ? null : password.toString();

		// User data
		Bundle userData = new Bundle();
		if (fbId != null) {
			userData.putString(FACEBOOK_ID_KEY, fbId);
		}

		Account account = new Account(name, Constants.ACCOUNT_TYPE);
		accountManager.addAccountExplicitly(account, stringPassword, null);

		Intent authIntent = new Intent();
		authIntent.putExtra(AccountManager.KEY_ACCOUNT_NAME, name);
		authIntent.putExtra(AccountManager.KEY_ACCOUNT_TYPE, Constants.ACCOUNT_TYPE);
		authIntent.putExtra(AccountManager.KEY_AUTHTOKEN, Constants.ACCOUNT_TYPE);
		authIntent.putExtra(IS_FB_LOGIN, stringPassword == null);

		setAccountAuthenticatorResult(authIntent.getExtras());
		setResult(RESULT_OK, authIntent);

		// App data sync settings
		Bundle extras = new Bundle();
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

		setupCalendarSync(account, null);

		return account;
	}

	public static void setupCalendarSync(Account account, Bundle extras) {
		if (extras == null) {
			extras = new Bundle();
		}
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

		ContentResolver.setIsSyncable(account, "com.android.calendar", 1);
		ContentResolver.requestSync(account, "com.android.calendar", extras);
		ContentResolver.setSyncAutomatically(account, "com.android.calendar", true);
	}

	public static void setupDataSync(Account account, Bundle extras) {
		if (extras == null) {
			extras = new Bundle();
		}
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_EXPEDITED, true);
		extras.putBoolean(ContentResolver.SYNC_EXTRAS_MANUAL, true);

		ContentResolver.setIsSyncable(account, Constants.APP_DATA_AUTHORITY, 1);
		ContentResolver.requestSync(account, Constants.APP_DATA_AUTHORITY, extras);
		ContentResolver.setSyncAutomatically(account, Constants.APP_DATA_AUTHORITY, true);
		
	}

	private void goToHome() {
		Intent intent = new Intent(this, OverviewActivity.class);
		intent.putExtra(IS_AFTER_LOGIN_KEY, true);
		intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		startActivity(intent);
		finish();
	}

	private void showError(int result) {
		if (result == UserLoginTask.RESULT_USER_NF) {
			Helper.showWarning(activity, R.string.login_error, R.string.incorrect_login);
		} else if (result == UserLoginTask.RESULT_CONN_ERROR) {
			Helper.showWarning(activity, R.string.login_error, R.string.connection_failed);
		}
	}

	/**
	 * Represents an asynchronous login/registration task used to authenticate
	 * the user.
	 */
	public class UserLoginTask extends AsyncTask<Void, Void, Boolean> {
		private static final int RESULT_DATA_DOWNLOAD_FAILED = -1;
		private static final int RESULT_CONN_ERROR = 1;
		private static final int RESULT_USER_NF = 2;

		private int result;
		private Password finalPassword;

		@Override
		protected Boolean doInBackground(Void... params) {
			try {
				// Get user salt and create password
				String[] emailSplitted = email.split("@");
				StringBuilder tokenBuilder = new StringBuilder();
				tokenBuilder.append(emailSplitted[0]);
				tokenBuilder.append(TOKEN_SALT);
				tokenBuilder.append("@");
				tokenBuilder.append(emailSplitted[1]);

				InputStream saltStream = Helper.loadWebStream("http://" + Constants.SERVER_URL + "/xml/userSalt/" + URLEncoder.encode(email, "UTF-8")
				        + "/" + Hasher.sha256(tokenBuilder.toString()));

				SaltWebParser saltParser = new SaltWebParser(saltStream);
				finalPassword = new Password(password);
				finalPassword.setSalt(saltParser.getSalt());

				// Try to login
				InputStream loginStream = Helper.loadWebStream("http://" + Constants.SERVER_URL + "/xml/userData/"
				        + URLEncoder.encode(email, "UTF-8") + "/" + finalPassword.toString());
				new LoginWebParser(loginStream);

				// Create account
				createAccount(email, finalPassword, null);

				// Store login date
				PreferenceManager.getDefaultSharedPreferences(activity).edit().putLong(LOGIN_DATE_PREFKEY, (new Date()).getTime()).commit();
			} catch (WebContentNotReachedException e) {
				result = RESULT_CONN_ERROR;
				return false;
			} catch (UserNotFoundException e) {
				result = RESULT_USER_NF;
				return false;
			} catch (UnsupportedEncodingException e1) {
				result = RESULT_CONN_ERROR;
				return false;
			}

			return true;
		}

		@TargetApi(Build.VERSION_CODES.JELLY_BEAN_MR1)
		@Override
		protected void onPostExecute(final Boolean success) {
			showProgress(false, true);
			authTask = null;

			if (success) {
				// Sync data download failed
				if (result == RESULT_DATA_DOWNLOAD_FAILED) {
					Toast.makeText(activity, R.string.unable_to_dl_data, Toast.LENGTH_LONG).show();
				}

				goToHome();
			} else {
				showError(result);
			}
		}

		@Override
		protected void onCancelled() {
			authTask = null;
			showProgress(false, true);
		}
	}

	private class PasswordRecoveryListener implements OnClickListener {

		@Override
		public void onClick(DialogInterface dialog, int which) {
			// Check email
			EditText emailField = (EditText) passwordRecoveryDialog.findViewById(R.id.auth_recorvery_email);
			String email = emailField.getText().toString();

			if (!Helper.checkEmail(email)) {
				Toast.makeText(activity, R.string.error_invalid_email, Toast.LENGTH_LONG).show();
				return;
			}

			showProgress(true, false);
			passwordRecoveryTask = new PasswordRecoveryTask();
			passwordRecoveryTask.execute((Void) null);
		}

	}

	public class PasswordRecoveryTask extends AsyncTask<Void, Void, Boolean> {

		private int status;

		@Override
		protected Boolean doInBackground(Void... params) {
			String email = ((EditText) passwordRecoveryDialog.findViewById(R.id.auth_recorvery_email)).getText().toString().replaceAll("/", "&#47;");

			// Auth token
			String[] emailSplitted = email.split("@");
			String authToken = Hasher.sha256(emailSplitted[0] + Constants.PASSWORD_RECOVERY_AUTH_SALT + "@" + emailSplitted[1]);

			// Build URL
			StringBuilder urlBuilder = new StringBuilder();
			try {
				urlBuilder.append("http://").append(Constants.SERVER_URL).append("/xml/recoverPassword/") // Base
				        .append(authToken).append("/") // Auth token
				        .append(URLEncoder.encode(email, "UTF-8")); // Email
			} catch (UnsupportedEncodingException e) {
				status = PasswordRecoveryWebParser.STATUS_FAILED;
				return false;
			}

			// Data exchange
			try {
				PasswordRecoveryWebParser parser = new PasswordRecoveryWebParser(Helper.loadWebStream(urlBuilder.toString()));
				status = parser.getStatus();
			} catch (WebContentNotReachedException e) {
				status = PasswordRecoveryWebParser.STATUS_FAILED;
				return false;
			}

			return status == PasswordRecoveryWebParser.STATUS_OK;
		}

		@Override
		protected void onPostExecute(final Boolean success) {
			showProgress(false, false);
			passwordRecoveryTask = null;

			if (success) {
				Helper.showDialog(activity, R.string.password_recovered, R.string.password_recovery_done);
			} else {
				int message = 0;

				switch (status) {
					case PasswordRecoveryWebParser.STATUS_FAILED :
						message = R.string.password_recovery_failed;
						break;
					case PasswordRecoveryWebParser.STATUS_NO_SUCH_ACCOUNT :
						message = R.string.password_recovery_no_such_account;
						break;
				}

				if (message != 0) {
					Helper.showWarning(activity, R.string.error, message);
				}
			}
		}

		@Override
		protected void onCancelled() {
			showProgress(false, false);
		}
	}
}
