package cz.vse.myevents.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;
import cz.vse.myevents.R;
import cz.vse.myevents.misc.IconizedText;

public class IconAdapter extends ArrayAdapter<IconizedText> {
	
	public IconAdapter(Context context) {
		super(context, R.layout.icon_adapter_row);
	}
	
	@Override
	public View getView(int position, View convertView, ViewGroup parent) {
		View view = convertView;
		if (view == null) {
			LayoutInflater viewInflater = (LayoutInflater) getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
			view = viewInflater.inflate(R.layout.icon_adapter_row, null);
		}

		IconizedText iconText = getItem(position);
		if (iconText != null) {
			TextView textView = (TextView) view.findViewById(R.id.text);

			if (textView != null) {
				textView.setCompoundDrawablesWithIntrinsicBounds(iconText.getDrawableResourceId(), 0, 0, 0);
				textView.setCompoundDrawablePadding(10);
				
				if (iconText.getText() == null) {
					textView.setText(iconText.getTextResourceId());
				} else {
					textView.setText(iconText.getText());
				}
			}
		}
		return view;
	}
}