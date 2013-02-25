package cz.vse.myevents.activity;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.animation.Animator;
import android.animation.AnimatorListenerAdapter;
import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.content.Context;
import android.content.DialogInterface;
import android.content.DialogInterface.OnClickListener;
import android.content.DialogInterface.OnDismissListener;
import android.content.pm.ActivityInfo;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.AsyncTask;
import android.os.Build;
import android.os.Bundle;
import android.view.MenuItem;
import android.view.View;
import android.widget.EditText;
import cz.vse.myevents.R;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.misc.Constants;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.xml.RegistrationWebParser;
import cz.webcomplete.data.types.Password;
import cz.webcomplete.tools.Hasher;

public class RegistrationActivity extends Activity {

    private EditText emailView;
    private EditText passwordView;
    private EditText passwordCheckView;
    private EditText nameView;

    private Activity activity = this;
    private RegistrationTask registrationTask;

    private Account[] googleAccounts;

    @TargetApi(Build.VERSION_CODES.HONEYCOMB)
    @Override
    protected void onCreate(Bundle savedInstanceState) {
	super.onCreate(savedInstanceState);
	setContentView(R.layout.activity_registration);

	if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB) {
	    getActionBar().setDisplayHomeAsUpEnabled(true);
	}

	// Work out views
	initViews();
	initGoogleAccounts();
	prefillFields();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
	switch (item.getItemId()) {
	case android.R.id.home:
	    Helper.goToLogin(this);
	    return true;
	default:
	    return super.onOptionsItemSelected(item);
	}
    }

    public void sendRegistration(View view) {
	ConnectivityManager connMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
	NetworkInfo networkInfo = connMgr.getActiveNetworkInfo();
	if (networkInfo == null || !networkInfo.isConnected()) {
	    Helper.showWarning(this, R.string.login_error,
		    R.string.no_internet_connection);
	    return;
	}

	clearErrors();
	if (!checkForm()) {
	    return;
	}

	showProgress(true);
	registrationTask = new RegistrationTask();
	registrationTask.execute((Void) null);
    }

    private void clearErrors() {
	emailView.setError(null);
	passwordView.setError(null);
	passwordCheckView.setError(null);
	nameView.setError(null);
    }

    private boolean checkForm() {
	// Not empty values
	EditText invalidField = null;
	String message = null;

	try {
	    if (emailView.getText().toString().equals("")) {
		invalidField = emailView;
		message = getString(R.string.error_field_required);
		return false;
	    } else if (passwordView.getText().toString().equals("")) {
		invalidField = passwordView;
		message = getString(R.string.error_field_required);
		return false;
	    } else if (passwordCheckView.getText().toString().equals("")) {
		invalidField = passwordCheckView;
		message = getString(R.string.error_field_required);
		return false;
	    } else if (nameView.getText().toString().equals("")) {
		invalidField = nameView;
		message = getString(R.string.error_field_required);
		return false;
	    }

	    // Email
	    if (!Helper.checkEmail(emailView.getText().toString())) {
		invalidField = emailView;
		message = getString(R.string.error_invalid_email);
		return false;
	    }

	    // Password match
	    if (!passwordView.getText().toString()
		    .equals(passwordCheckView.getText().toString())) {
		invalidField = passwordCheckView;
		message = getString(R.string.error_password_missmatch);
	    }

	    // Password minimal length
	    if (passwordView.getText().length() < Password.MIN_LENGTH) {
		invalidField = passwordView;
		message = getString(R.string.error_invalid_password);
	    }

	} finally {
	    if (invalidField != null) {
		invalidField.setError(message);
		invalidField.requestFocus();
	    }
	}
	return true;
    }

    private void initViews() {
	emailView = (EditText) findViewById(R.id.registration_email);
	passwordView = (EditText) findViewById(R.id.registration_password);
	passwordCheckView = (EditText) findViewById(R.id.registration_password_check);
	nameView = (EditText) findViewById(R.id.registration_name);
    }

    private void prefillFields() {
	if (googleAccounts.length > 0) {
	    String googleEmail = googleAccounts[0].name;
	    emailView.setText(googleEmail);
	}
    }

    private void initGoogleAccounts() {
	googleAccounts = AccountManager.get(this).getAccountsByType(
		"com.google");
    }

    /**
     * Shows the progress UI and hides the login form.
     */
    @TargetApi(Build.VERSION_CODES.HONEYCOMB_MR2)
    private void showProgress(final boolean show) {
	// Orientation possibility
	int orientationType = show ? ActivityInfo.SCREEN_ORIENTATION_NOSENSOR
		: ActivityInfo.SCREEN_ORIENTATION_SENSOR;
	setRequestedOrientation(orientationType);

	final View registrationStatusView = findViewById(R.id.registration_status);
	final View registrationFormView = findViewById(R.id.registration_form);

	if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.HONEYCOMB_MR2) {
	    int shortAnimTime = getResources().getInteger(
		    android.R.integer.config_shortAnimTime);

	    registrationStatusView.setVisibility(View.VISIBLE);
	    registrationStatusView.animate().setDuration(shortAnimTime)
		    .alpha(show ? 1 : 0)
		    .setListener(new AnimatorListenerAdapter() {
			@Override
			public void onAnimationEnd(Animator animation) {
			    registrationStatusView
				    .setVisibility(show ? View.VISIBLE
					    : View.GONE);
			}
		    });

	    registrationFormView.setVisibility(View.VISIBLE);
	    registrationFormView.animate().setDuration(shortAnimTime)
		    .alpha(show ? 0 : 1)
		    .setListener(new AnimatorListenerAdapter() {
			@Override
			public void onAnimationEnd(Animator animation) {
			    registrationFormView.setVisibility(show ? View.GONE
				    : View.VISIBLE);
			}
		    });
	} else {
	    // The ViewPropertyAnimator APIs are not available, so simply show
	    // and hide the relevant UI components.
	    registrationStatusView.setVisibility(show ? View.VISIBLE
		    : View.GONE);
	    registrationFormView.setVisibility(show ? View.GONE : View.VISIBLE);
	}
    }

    public class RegistrationTask extends AsyncTask<Void, Void, Boolean>
	    implements OnDismissListener, OnClickListener {

	private int status;
	private boolean activationRequired = true;

	@Override
	protected Boolean doInBackground(Void... params) {
	    // Create password
	    Password password = new Password(passwordView.getText().toString());

	    // Create auth token
	    String[] emailSplitted = emailView.getText().toString().split("@");
	    String authToken = Hasher.sha256(emailSplitted[0]
		    + Constants.USER_REGISTRATION_AUTH_SALT + "@"
		    + emailSplitted[1]);

	    // Is activation required?
	    for (Account account : googleAccounts) {
		if (emailView.getText().toString().equals(account.name)) {
		    activationRequired = false;
		    break;
		}
	    }

	    // Create URL
	    StringBuilder urlBuilder = new StringBuilder();
	    try {
		urlBuilder
			.append("http://")
			.append(Constants.SERVER_URL)
			.append("/xml/newUserRegistration/")
			// Base
			.append(authToken)
			.append("/")
			// Auth token
			.append(URLEncoder.encode(
				emailView.getText().toString(), "UTF-8")
				.replaceAll("/", "&#47;")).append("/")
			// Email
			.append(password.toString())
			.append("/")
			// Password hash
			.append(URLEncoder.encode(
				nameView.getText().toString(), "UTF-8")
				.replaceAll("/", "&#47;")).append("/") // Name
			.append(String.valueOf(activationRequired)); // Activation
								     // required?
	    } catch (UnsupportedEncodingException e1) {
		status = RegistrationWebParser.STATUS_FAILED;
		return false;
	    }

	    // Send data and get status info
	    try {
		String url = urlBuilder.toString();
		RegistrationWebParser registrationWebParser = new RegistrationWebParser(
			Helper.loadWebStream(url));
		status = registrationWebParser.getStatus();

		return status == RegistrationWebParser.STATUS_OK;
	    } catch (WebContentNotReachedException e) {
		status = RegistrationWebParser.STATUS_FAILED;
		return false;
	    }
	}

	@TargetApi(Build.VERSION_CODES.JELLY_BEAN_MR1)
	@Override
	protected void onPostExecute(final Boolean success) {
	    showProgress(false);
	    registrationTask = null;

	    if (success) {
		int message = activationRequired ? R.string.registration_done_activation_required
			: R.string.registration_done;

		Builder dialogBuilder = new AlertDialog.Builder(activity);
		dialogBuilder.setTitle(R.string.title_activity_registration);
		dialogBuilder.setMessage(message);

		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR1) {
		    dialogBuilder.setOnDismissListener(this);
		    dialogBuilder.setNegativeButton("OK", null);
		} else {
		    dialogBuilder.setCancelable(false);
		    dialogBuilder.setNegativeButton("OK", this);
		}

		dialogBuilder.show();
	    } else {
		switch (status) {
		case RegistrationWebParser.STATUS_ALREADY_EXISTS:
		    Helper.showWarning(activity, R.string.error,
			    R.string.registration_acc_already_exists);
		    break;
		case RegistrationWebParser.STATUS_FAILED:
		    Helper.showWarning(activity, R.string.error,
			    R.string.registration_failed);
		    break;
		}
	    }
	}

	@Override
	protected void onCancelled() {
	    showProgress(false);
	}

	@Override
	public void onDismiss(DialogInterface dialog) {
	    dismissDialog();
	}

	@Override
	public void onClick(DialogInterface dialog, int which) {
	    dismissDialog();
	}

	private void dismissDialog() {
	    Intent intent = new Intent(activity, LoginActivity.class);
	    intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
	    startActivity(intent);
	    finish();
	}
    }
}
