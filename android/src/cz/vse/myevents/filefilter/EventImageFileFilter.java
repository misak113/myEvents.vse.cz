package cz.vse.myevents.filefilter;
import java.io.File;
import java.io.FilenameFilter;

public class EventImageFileFilter implements FilenameFilter {

	public static final String FILENAME_PREFIX = "eventImg_";

	private int eventId;

	public EventImageFileFilter() {
	}

	public EventImageFileFilter(int eventId) {
		this.eventId = eventId;
	}

	@Override
	public boolean accept(File dir, String filename) {
		String pattern = eventId == 0 ? "^" + FILENAME_PREFIX + "_.*$" : "^"
				+ FILENAME_PREFIX + eventId + ".*$";
		return filename.matches(pattern);
	}

}