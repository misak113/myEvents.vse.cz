package cz.vse.myevents.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;
import cz.vse.myevents.R;
import cz.vse.myevents.misc.BitmapLoader;
import cz.vse.myevents.misc.Helper;
import cz.vse.myevents.serverdata.Event;

public class EventAdapter extends ArrayAdapter<Event> {

		public EventAdapter(Context context) {
			super(context, R.layout.event_row);
		}

		@Override
		public View getView(int position, View convertView, ViewGroup parent) {
			View view = convertView;
			if (view == null) {
				LayoutInflater viewInflater = (LayoutInflater) getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
				view = viewInflater.inflate(R.layout.event_row, null);
			}

			Event event = getItem(position);
			if (event != null) {
				
				ImageView eventImage = (ImageView) view.findViewById(R.id.icon);
				TextView nameView = (TextView) view.findViewById(R.id.name);
				TextView timeView = (TextView) view.findViewById(R.id.date);

				String imagePath = event.getEventImagePath(getContext());
		        if (imagePath == null) {
		        	eventImage.setImageResource(R.drawable.event_default_image);
		        } else {
		        	int[] pixels = Helper.countEventImagePixels(getContext());
		        	eventImage.setImageBitmap(BitmapLoader.loadBitmap(imagePath, pixels[0], pixels[1]));
		        }

				if (nameView != null) {
					nameView.setText(event.getName());
				}
				if (timeView != null) {
					timeView.setText(getContext().getResources().getString(R.string.start)
							+ ": " + event.getFormattedStartDate(getContext()));
				}
			}
			return view;
		}
	}