package cz.vse.myevents.misc;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;

import android.annotation.TargetApi;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.AlertDialog.Builder;
import android.content.ContentUris;
import android.content.Context;
import android.content.Intent;
import android.content.res.Configuration;
import android.net.Uri;
import android.os.Build;
import android.provider.CalendarContract.Calendars;
import android.provider.CalendarContract.Events;
import android.provider.CalendarContract.Reminders;
import android.util.DisplayMetrics;

import com.google.ads.AdRequest;
import com.google.ads.AdView;

import cz.vse.myevents.R;
import cz.vse.myevents.activity.HelpContentActivity;
import cz.vse.myevents.activity.LoginActivity;
import cz.vse.myevents.activity.OverviewActivity;
import cz.vse.myevents.exception.WebContentNotReachedException;
import cz.vse.myevents.serverdata.Event;

public class Helper {
	private Helper() {
	}

	/**
	 * Reads web content
	 * 
	 * @param url
	 *            URL of the content
	 * @return Content as a String instance
	 * @throws WebContentNotReachedException
	 */
	public static String readWebContent(String url)
			throws WebContentNotReachedException {
		InputStream is = loadWebStream(url);
		try {
			// Convert the InputStream into a string
			BufferedReader buffer = new BufferedReader(new InputStreamReader(
					is, "UTF8"));
			StringBuilder builder = new StringBuilder();

			int byteRead;
			while ((byteRead = buffer.read()) != -1) {
				builder.append((char) byteRead);
			}

			buffer.close();

			return builder.toString();

			// Makes sure that the InputStream is closed after the app is
			// finished using it.
		} catch (Exception ex) {
			throw new WebContentNotReachedException(ex);
		} finally {
			if (is != null) {
				try {
					is.close();
				} catch (IOException e) {
				}
			}
		}
	}

	/**
	 * Loads input stream of remote URL
	 * 
	 * @param url
	 *            URL of the stream
	 * @return Input stream of the URL
	 * @throws WebContentNotReachedException
	 */
	public static InputStream loadWebStream(String url)
			throws WebContentNotReachedException {
		try {
			URL urlInst = new URL(url);
			HttpURLConnection conn = (HttpURLConnection) urlInst
					.openConnection();
			conn.setReadTimeout(10000);
			conn.setConnectTimeout(15000);
			conn.setRequestMethod("GET");
			conn.setDoInput(true);
			// Starts the query
			conn.connect();
			int response = conn.getResponseCode();

			// Check response
			if (response != HttpURLConnection.HTTP_OK) {
				throw new WebContentNotReachedException(response);
			}
			return conn.getInputStream();
		} catch (Exception ex) {
			throw new WebContentNotReachedException(ex);
		}
	}

	/**
	 * Shows dialog
	 * 
	 * @param activity
	 *            Activity of the alert
	 * @param text
	 *            Text to show as String
	 */
	public static void showDialog(Activity activity, String title, String text) {
		showDialog(activity, 0, 0, title, text, false);
	}

	/**
	 * Shows dialog
	 * 
	 * @param activity
	 *            Activity of the alert
	 * @param text
	 *            Text to show as int
	 */
	public static void showDialog(Activity activity, int title, int text) {
		showDialog(activity, title, text, null, null, false);
	}

	/**
	 * Shows warning
	 * 
	 * @param activity
	 *            Activity of the alert
	 * @param text
	 *            Text to show as String
	 */
	public static void showWarning(Activity activity, String title, String text) {
		showDialog(activity, 0, 0, title, text, true);
	}

	/**
	 * Shows warning
	 * 
	 * @param activity
	 *            Activity of the alert
	 * @param text
	 *            Text to show as int
	 */
	public static void showWarning(Activity activity, int title, int text) {
		showDialog(activity, title, text, null, null, true);
	}

	private static void showDialog(Activity activity, int intTitle,
			int intText, String stringTitle, String stringText, boolean warning) {
		Builder dialogBuilder = new AlertDialog.Builder(activity);
		if (intText == 0) {
			dialogBuilder.setTitle(stringTitle);
			dialogBuilder.setMessage(stringText);
		} else {
			dialogBuilder.setTitle(intTitle);
			dialogBuilder.setMessage(intText);
		}

		// Attach icon
		dialogBuilder.setIcon(warning ? R.drawable.ic_alert
				: R.drawable.ic_info);

		dialogBuilder.setNeutralButton(android.R.string.ok, null);
		dialogBuilder.show();
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	public static Uri calendarsUri() {
		Uri calendarUri;
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
			calendarUri = Uri.parse("content://com.android.calendar/calendars");
		} else {
			calendarUri = Calendars.CONTENT_URI;
		}

		return calendarUri;
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	public static Uri eventsUri() {
		Uri eventsUri;
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
			eventsUri = Uri.parse("content://com.android.calendar/events");
		} else {
			eventsUri = Events.CONTENT_URI;
		}

		return eventsUri;
	}

	@TargetApi(Build.VERSION_CODES.ICE_CREAM_SANDWICH)
	public static Uri remindersUri() {
		Uri remindersUri;
		if (Build.VERSION.SDK_INT < Build.VERSION_CODES.ICE_CREAM_SANDWICH) {
			remindersUri = Uri
					.parse("content://com.android.calendar/reminders");
		} else {
			remindersUri = Reminders.CONTENT_URI;
		}

		return remindersUri;
	}

	public static boolean checkEmail(String email) {
		return email.matches("^[_A-Za-z0-9-]+(\\."
				+ "[_A-Za-z0-9-]+)*@[A-Za-z0-9]+(\\.[A-Za-z0-9]+)*"
				+ "(\\.[A-Za-z]{2,})$");
	}

	public static void goHome(Activity activity) {
		Intent intent = new Intent(activity, OverviewActivity.class);
		intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		activity.startActivity(intent);
		activity.finish();
	}

	public static void goToLogin(Activity activity) {
		Intent intent = new Intent(activity, LoginActivity.class);
		intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
		activity.startActivity(intent);
		activity.finish();
	}
	
	public static void goToHelp(Activity activity) {
		Intent intent = new Intent(activity, HelpContentActivity.class);
		activity.startActivity(intent);
	}

	public static boolean isXLargeTablet(Context context) {
		return (context.getResources().getConfiguration().screenLayout & Configuration.SCREENLAYOUT_SIZE_MASK) >= Configuration.SCREENLAYOUT_SIZE_XLARGE;
	}
	
	public static Intent getCalendarEventIntent(Event event) {
		Uri uri = ContentUris.withAppendedId(Helper.eventsUri(), event.getId());

		Intent intent = new Intent(Intent.ACTION_VIEW).setData(uri);
		intent.putExtra("beginTime", event.getStartDate().getTime());
		intent.putExtra("endTime", event.getEndDate().getTime());
		
		return intent;
	}
	
	public static void loadAd(AdView adView) {
		AdRequest adRequest = new AdRequest();
		adView.loadAd(adRequest);
	}
	
	public static int[] countEventImagePixels(Context context) {
		int[] pixels = new int[2];
		int density = context.getResources().getDisplayMetrics().densityDpi;
		
		switch (density) {
			case DisplayMetrics.DENSITY_LOW :
				pixels[0] = 112;
				pixels[1] = 75;
				break;
			case DisplayMetrics.DENSITY_MEDIUM :
				pixels[0] = 120;
				pixels[1] = 80;
				break;
			case DisplayMetrics.DENSITY_HIGH :
				pixels[0] = 200;
				pixels[1] = 133;
				break;
			case DisplayMetrics.DENSITY_XHIGH :
			case DisplayMetrics.DENSITY_XXHIGH :
				pixels[0] = 300;
				pixels[1] = 200;
				break;
			default :
				pixels[0] = 150;
				pixels[1] = 100;
		}
		
		return pixels;
	}
}
