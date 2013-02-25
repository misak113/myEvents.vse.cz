package cz.vse.myevents.xml;

import java.io.InputStream;
import java.util.HashSet;
import java.util.Set;

import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import android.content.Context;
import cz.vse.myevents.serverdata.Event;

public class EventsWebParser extends WebParser {

	private Context context;
	
	private Set<Event> events = new HashSet<Event>();
	
	public EventsWebParser(InputStream inputStream) {
		super(inputStream);
		initData();
	}
	
	public EventsWebParser(Context context, InputStream inputStream) {
		super(inputStream);
		this.context = context;
		initData();
	}

	private void initData() {
		Document document = getDomElement();
		
		NodeList nodes = document.getElementsByTagName("event");
		for (int i = 0; i < nodes.getLength(); i++) {
			Node node = nodes.item(i);
			Event event = Event.createFromDomNode(context, node);
			events.add(event);
		}
	}

	public Set<Event> getEvents() {
		return events;
	}
}
