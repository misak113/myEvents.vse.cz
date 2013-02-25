package cz.vse.myevents.filefilter;
import java.io.File;
import java.io.FilenameFilter;

public class OrganizationLogoFileFilter implements FilenameFilter {

	public static final String FILENAME_PREFIX = "orgLogo_";

	private long orgId;

	public OrganizationLogoFileFilter() {
	}

	public OrganizationLogoFileFilter(long orgId) {
		this.orgId = orgId;
	}

	@Override
	public boolean accept(File dir, String filename) {
		String pattern = orgId == 0
				? "^" + FILENAME_PREFIX + ".*$"
				: "^" + FILENAME_PREFIX + orgId + "_.*$";
		return filename.matches(pattern);
	}

}