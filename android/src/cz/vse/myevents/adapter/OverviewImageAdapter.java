package cz.vse.myevents.adapter;

import android.app.Activity;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;
import cz.vse.myevents.R;
import cz.vse.myevents.misc.BitmapLoader;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.serverdata.Event;

public class OverviewImageAdapter extends ArrayAdapter<Event> {
	private final Activity activity;

	public OverviewImageAdapter(Activity activity) {
		super(activity, R.layout.overview_grid_element);
		this.activity = activity;
	}

	@Override
	public long getItemId(int position) {
		return position;
	}

	@Override
	public View getView(int position, View convertView, ViewGroup container) {
		Event event = getItem(position);
        convertView = activity.getLayoutInflater().inflate(R.layout.overview_grid_element, null);
        
        ImageView image = (ImageView) convertView.findViewById(R.id.grid_event_image);
        TextView label = (TextView) convertView.findViewById(R.id.grid_event_label);
        
        // Create image
        String imagePath = event.getEventImagePath(getContext());
        if (imagePath == null) {
        	image.setImageResource(R.drawable.event_default_image);
        } else {
        	int[] pixels = Helper.countEventImagePixels(activity);
        	image.setImageBitmap(BitmapLoader.loadBitmap(imagePath, pixels[0], pixels[1]));
        }
        
        
        label.setText(event.getName());
 
        return convertView;
	}
}