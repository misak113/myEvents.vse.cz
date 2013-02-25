package cz.vse.myevents.serverdata;

import java.io.File;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import android.annotation.SuppressLint;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.graphics.drawable.Drawable;
import android.provider.CalendarContract.Events;
import cz.vse.myevents.R;
import cz.vse.myevents.database.data.DataContract.EventInfo;
import cz.vse.myevents.database.data.DataContract.EventOrganizators;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.database.data.DataDbHelper;
import cz.vse.myevents.filefilter.EventImageFileFilter;

public class Event implements Comparable<Event> {

	private int id;
	private int serverId;
	private String name;
	private String location;
	private Date startDate;
	private Date endDate;
	private String description;
	private List<Organization> organizators = new ArrayList<Organization>();
	private String serverImageUrl;
	private String serverImageCrc;
	private long crc;

	private boolean notified = false;

	public static Event createFromDomNode(Context context, Node node) {
		if (!node.getNodeName().equals("event")) {
			throw new IllegalArgumentException("No event node passed");
		}

		NodeList dataNodes = node.getChildNodes();
		Event event = new Event();

		String capacity = null;
		String url = null;
		String fbLink = null;

		event.serverId = Integer.parseInt(((Element) node).getAttribute("id"));

		for (int i = 0; i < dataNodes.getLength(); i++) {
			Node dataNode = dataNodes.item(i);
			String dataNodeName = dataNode.getNodeName();
			String dataNodeValue = dataNode.getTextContent();

			// Name
			if (dataNodeName.equals("name")) {
				event.name = dataNodeValue;

				// CRC
			} else if (dataNodeName.equals("crc")) {
				event.crc = Long.parseLong(dataNodeValue);

				// Location
			} else if (dataNodeName.equals("location")) {
				event.location = dataNodeValue;

				// Start date
			} else if (dataNodeName.equals("start")) {
				// CalendarSyncAdapter.test = "Start date";
				try {
					event.startDate = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.ENGLISH).parse(dataNodeValue);
				} catch (ParseException e) {
					return null;
				}

				// End date
			} else if (dataNodeName.equals("end")) {
				try {
					event.endDate = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.ENGLISH).parse(dataNodeValue);
				} catch (ParseException e) {
					return null;
				}

				// Description
			} else if (dataNodeName.equals("description")) {
				event.description = dataNodeValue;

				// Capacity
			} else if (dataNodeName.equals("capacity")) {
				capacity = dataNodeValue;

				// URL
			} else if (dataNodeName.equals("url")) {
				url = dataNodeValue;

				// Facebook link
			} else if (dataNodeName.equals("fbLink")) {
				fbLink = dataNodeValue;

				// Organizators
			} else if (dataNodeName.equals("organizators")) {
				NodeList organizatorsNodes = dataNode.getChildNodes();
				SQLiteDatabase db = DataDbHelper.getReadableDatabase(context);
				for (int ii = 0; ii < organizatorsNodes.getLength(); ii++) {
					Node orgNode = organizatorsNodes.item(ii);

					if (!orgNode.getNodeName().equals("organizator")) {
						continue;
					}

					int orgId = Integer.parseInt(((Element) orgNode).getAttribute("id"));

					// Find out name
					Cursor orgCursor = db.query(Organizations.TABLE_NAME, new String[]{Organizations.NAME}, Organizations._ID + "=?",
					        new String[]{String.valueOf(orgId)}, null, null, null);

					String orgName;
					try {
						if (!orgCursor.moveToFirst()) {
							continue;
						}
						orgName = orgCursor.getString(orgCursor.getColumnIndex(Organizations.NAME));
					} finally {
						if (orgCursor != null) {
							orgCursor.close();
						}
					}

					Organization organization = new Organization(orgId, orgName);
					event.organizators.add(organization);
				}
				db.close();
			}

			// Picture
			else if (dataNodeName.equals("image")) {
				NodeList imageNodes = dataNodes.item(i).getChildNodes();
				for (int ii = 0; ii < imageNodes.getLength(); ii++) {
					if (imageNodes.item(ii).getNodeName().equals("url")) {
						event.serverImageUrl = imageNodes.item(ii).getTextContent();
					} else if (imageNodes.item(ii).getNodeName().equals("crc")) {
						event.serverImageCrc = imageNodes.item(ii).getTextContent();
					}
				}
			}
		}

		// Finish description

		// Organizators
		if (!event.organizators.isEmpty()) {
			StringBuilder organizatorsBuilder = new StringBuilder();
			organizatorsBuilder.append(context.getString(R.string.event_arranged_by));

			int i = 1;
			int listSize = event.organizators.size();
			for (Organization organization : event.organizators) {
				String divider;
				if (i == 1) { // First
					divider = " ";
				} else if (i == listSize) { // Last
					divider = " " + context.getString(R.string.and) + " ";
				} else { // All others
					divider = ", ";
				}

				organizatorsBuilder.append(divider).append(organization.getName());

				i++;
			}

			event.description = organizatorsBuilder.append(".\n\n").toString() + event.description;
		}

		// Appendix
		if (context != null) {
			String descriptionAddon = "";

			// Capacity
			if (capacity != null && capacity != "") {
				descriptionAddon += "\n" + context.getResources().getString(R.string.capacity) + ": " + capacity;
			}

			// URL
			if (url != null && url != "") {
				descriptionAddon += "\n" + context.getResources().getString(R.string.event_url) + ": " + url;
			}

			// Facebook link
			if (fbLink != null && fbLink != "") {
				descriptionAddon += "\n" + context.getResources().getString(R.string.fb_link) + ": " + fbLink;
			}

			// Add new line to the begining
			if (descriptionAddon != "") {
				event.description += "\n" + descriptionAddon;
			}
		}

		return event;
	}

	public static Event createFromCursor(Cursor cursor, SQLiteDatabase dataDb) {
		Event event = new Event();

		try {
			event.id = cursor.getInt(cursor.getColumnIndex(Events._ID));
		} catch (IllegalStateException ex) {
		}

		try {
			event.name = cursor.getString(cursor.getColumnIndex(Events.TITLE));
		} catch (IllegalStateException ex) {
		}

		try {
			event.location = cursor.getString(cursor.getColumnIndex(Events.EVENT_LOCATION));
		} catch (IllegalStateException ex) {
		}

		try {
			event.startDate = new Date(cursor.getLong(cursor.getColumnIndex(Events.DTSTART)));
		} catch (IllegalStateException ex) {
		}

		try {
			event.endDate = new Date(cursor.getLong(cursor.getColumnIndex(Events.DTEND)));
		} catch (IllegalStateException ex) {
		}

		try {
			event.description = cursor.getString(cursor.getColumnIndex(Events.DESCRIPTION));
		} catch (IllegalStateException ex) {
		}

		// Database related
		if (dataDb != null && dataDb.isOpen()) {
			Cursor infoCursor = dataDb.query(EventInfo.TABLE_NAME, new String[]{EventInfo._ID, EventInfo.CRC}, EventInfo.EVENT_ID + "=?",
			        new String[]{String.valueOf(event.id)}, null, null, null);

			if (infoCursor != null && infoCursor.moveToFirst()) {
				// CRC32
				event.crc = infoCursor.getLong(infoCursor.getColumnIndex(EventInfo.CRC));

				// Server ID
				event.serverId = infoCursor.getInt(infoCursor.getColumnIndex(EventInfo._ID));
			}

			if (infoCursor != null) {
				infoCursor.close();
			}

			// Organizators
			Cursor organizatorsCursor = dataDb.query(EventOrganizators.TABLE_NAME, new String[]{EventOrganizators.ORGANIZATION_ID},
			        EventOrganizators.EVENT_ID + "=?", new String[]{String.valueOf(event.id)}, null, null, null);

			if (organizatorsCursor != null && organizatorsCursor.moveToFirst()) {
				do {
					Organization organization = new Organization(organizatorsCursor.getInt(organizatorsCursor
					        .getColumnIndex(EventOrganizators.ORGANIZATION_ID)), null);
					event.organizators.add(organization);
				} while (organizatorsCursor.moveToNext());
				if (organizatorsCursor != null) {
					organizatorsCursor.close();
				}
			}
		}

		return event;
	}

	@Override
	public int hashCode() {
		int hash = 3;
		hash = 67 * hash + (int) (this.crc ^ (this.crc >>> 32));
		return hash;
	}

	@Override
	public boolean equals(Object obj) {
		if (obj == null) {
			return false;
		}
		if (getClass() != obj.getClass()) {
			return false;
		}
		final Event other = (Event) obj;
		if (this.crc != other.crc) {
			return false;
		}
		return true;
	}

	@Override
	public String toString() {
		return name;
	}

	@Override
	public int compareTo(Event another) {
		if (this.startDate.equals(another.startDate))
			return 0;
		else if (this.startDate.after(another.startDate))
			return 1;
		else
			return -1;
	}

	@SuppressLint({"DefaultLocale", "SimpleDateFormat"})
	public String getFormattedStartDate(Context context) {
		// Format date
		String date;
		if (Locale.getDefault().getCountry().equals("CZ")) { // Make czech date
			                                                 // nicer
			date = new SimpleDateFormat("d. M. yyyy HH:mm").format(startDate);
		} else {
			date = SimpleDateFormat.getInstance().format(startDate);
		}

		// Replace today and tomorrow days
		Date todayDate = new Date();
		String[] todaySplitted = SimpleDateFormat.getInstance().format(todayDate).split(" ");
		date = date.replace(todaySplitted[0], context.getString(R.string.today) + " " + context.getString(R.string.at));

		Date tomorrowDate = new Date((new Date()).getTime() + 60L * 60L * 24L * 1000L);
		String[] tomorrowSplitted = SimpleDateFormat.getInstance().format(tomorrowDate).split(" ");
		date = date.replace(tomorrowSplitted[0], context.getString(R.string.tomorrow) + " " + context.getString(R.string.at));

		return date.toLowerCase();
	}

	public String getEventImagePath(Context context) {
		String path = null;
		File[] files = context.getFilesDir().listFiles(new EventImageFileFilter(id));

		try {
			path = files[0].getAbsolutePath();
		} catch (ArrayIndexOutOfBoundsException ex) {
			path = getOrganizationLogo(context);
		} catch (NullPointerException ex) {
			path = getOrganizationLogo(context);
		}

		return path;
	}

	public boolean hasDefaultImage(Context context) {
		File[] files = context.getFilesDir().listFiles(new EventImageFileFilter(id));
		
		try {
			files[0].getAbsolutePath();
		} catch (ArrayIndexOutOfBoundsException ex) {
			return true;
		} catch (NullPointerException ex) {
			return true;
		}
		
		return false;
	}

	public String getOrganizationLogo(Context context) {
		for (Organization org : organizators) {
			String logoPath = org.getLogoPath(context);
			if (logoPath != null) {
				return logoPath;
			}
		}

		return null;
	}

	public static Drawable getDefaultImage(Context context) {
		return context.getResources().getDrawable(R.drawable.event_default_image);
	}

	/**
	 * @return the name
	 */
	public String getName() {
		return name;
	}

	/**
	 * @param name
	 *            the name to set
	 */
	public void setName(String name) {
		this.name = name;
	}

	/**
	 * @return the location
	 */
	public String getLocation() {
		return location;
	}

	/**
	 * @param location
	 *            the location to set
	 */
	public void setLocation(String location) {
		this.location = location;
	}

	/**
	 * @return the startDate
	 */
	public Date getStartDate() {
		return startDate;
	}

	/**
	 * @param startDate
	 *            the startDate to set
	 */
	public void setStartDate(Date startDate) {
		this.startDate = startDate;
	}

	/**
	 * @return the endDate
	 */
	public Date getEndDate() {
		return endDate;
	}

	/**
	 * @param endDate
	 *            the endDate to set
	 */
	public void setEndDate(Date endDate) {
		this.endDate = endDate;
	}

	/**
	 * @return the description
	 */
	public String getDescription() {
		return description;
	}

	/**
	 * @param description
	 *            the description to set
	 */
	public void setDescription(String description) {
		this.description = description;
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getServerImageUrl() {
		return serverImageUrl;
	}

	public void setServerImageUrl(String serverImageUrl) {
		this.serverImageUrl = serverImageUrl;
	}

	public String getServerImageCrc() {
		return serverImageCrc;
	}

	public void setServerImageCrc(String serverImageCrc) {
		this.serverImageCrc = serverImageCrc;
	}

	public boolean isNotified() {
		return notified;
	}

	public void setNotified(boolean notified) {
		this.notified = notified;
	}

	public long getCrc() {
		return crc;
	}

	public void setCrc(int crc) {
		this.crc = crc;
	}

	public List<Organization> getOrganizators() {
		return organizators;
	}

	public int getServerId() {
		return serverId;
	}

	public void setServerId(int serverId) {
		this.serverId = serverId;
	}
}
