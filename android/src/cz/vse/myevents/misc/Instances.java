package cz.vse.myevents.misc;

import cz.vse.myevents.activity.OverviewActivity;

public class Instances {
	
	private static OverviewActivity overviewActivity;

	private Instances() {
	}

	public static OverviewActivity getOverviewActivity() {
		return overviewActivity;
	}

	public static void setOverviewActivity(OverviewActivity overviewActivity) {
		Instances.overviewActivity = overviewActivity;
	}
}
