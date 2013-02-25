package cz.vse.myevents.account;

import android.accounts.AbstractAccountAuthenticator;
import android.accounts.Account;
import android.accounts.AccountAuthenticatorResponse;
import android.accounts.AccountManager;
import android.accounts.NetworkErrorException;
import android.content.Context;
import android.content.Intent;
import android.database.sqlite.SQLiteDatabase;
import android.os.Bundle;
import android.preference.PreferenceManager;
import cz.vse.myevents.activity.LoginActivity;
import cz.vse.myevents.activity.SettingsActivity;
import cz.vse.myevents.database.data.DataContract.EventInfo;
import cz.vse.myevents.database.data.DataContract.EventOrganizators;
import cz.vse.myevents.database.data.DataContract.EventTypes;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.misc.Constants;

public class Authenticator extends AbstractAccountAuthenticator {
	public static final String AUTHTOKEN_TYPE = "authToken_type";

	private Context context;

	public Authenticator(Context context) {
		super(context);
		this.context = context;
	}

	@Override
	public Bundle addAccount(AccountAuthenticatorResponse response, String accountType, String authTokenType, String[] requiredFeatures,
	        Bundle options) throws NetworkErrorException {

		Intent intent = new Intent(context, LoginActivity.class);
		intent.putExtra(AccountManager.KEY_ACCOUNT_AUTHENTICATOR_RESPONSE, response);

		Bundle result = new Bundle();
		result.putParcelable(AccountManager.KEY_INTENT, intent);

		// Check accounts
		Account[] existingAccounts = AccountManager.get(context).getAccountsByType(Constants.ACCOUNT_TYPE);
		if (existingAccounts.length >= 1) {
			result.putInt(AccountManager.KEY_ERROR_CODE, 1);
			result.putString(AccountManager.KEY_ERROR_MESSAGE, "UŽ JE");
		}

		return result;
	}

	@Override
	public Bundle confirmCredentials(AccountAuthenticatorResponse response, Account account, Bundle options) throws NetworkErrorException {

		return null;
	}

	@Override
	public Bundle editProperties(AccountAuthenticatorResponse response, String accountType) {

		throw new UnsupportedOperationException();
	}

	@Override
	public Bundle getAuthToken(AccountAuthenticatorResponse response, Account account, String authTokenType, Bundle options)
	        throws NetworkErrorException {
		return null;
	}

	@Override
	public String getAuthTokenLabel(String authTokenType) {
		return null;
	}

	@Override
	public Bundle hasFeatures(AccountAuthenticatorResponse response, Account account, String[] features) throws NetworkErrorException {

		final Bundle result = new Bundle();
		result.putBoolean(AccountManager.KEY_BOOLEAN_RESULT, false);
		return result;
	}

	@Override
	public Bundle updateCredentials(AccountAuthenticatorResponse response, Account account, String authTokenType, Bundle options)
	        throws NetworkErrorException {

		return null;
	}

	@Override
	public Bundle getAccountRemovalAllowed(AccountAuthenticatorResponse response, Account account) throws NetworkErrorException {
		Bundle ret = super.getAccountRemovalAllowed(response, account);

		if (ret.getBoolean(AccountManager.KEY_BOOLEAN_RESULT)) {
			// Do the cleaning
			SQLiteDatabase db = DataDbHelper.getWritableDatabase(context);
			db.delete(Organizations.TABLE_NAME, null, null);
			db.delete(EventTypes.TABLE_NAME, null, null);
			db.delete(EventInfo.TABLE_NAME, null, null);
			db.delete(EventOrganizators.TABLE_NAME, null, null);
			db.close();

			// Turn notifications off
			PreferenceManager.getDefaultSharedPreferences(context).edit().putBoolean(SettingsActivity.PREF_NOTIFICATIONS_KEY, false).commit();
		}

		return ret;

	}

}
