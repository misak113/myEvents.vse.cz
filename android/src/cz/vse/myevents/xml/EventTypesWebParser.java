package cz.vse.myevents.xml;

import java.io.InputStream;
import java.util.HashSet;
import java.util.Set;

import org.w3c.dom.Document;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import cz.vse.myevents.serverdata.EventType;

public class EventTypesWebParser extends WebParser {
	
	private Set<EventType> eventTypes = new HashSet<EventType>();
	
	public EventTypesWebParser(InputStream inputStream) {
		super(inputStream);
		initData();
	}

	private void initData() {
		Document document = getDomElement();
		
		NodeList nodes = document.getElementsByTagName("type");
		for (int i = 0; i < nodes.getLength(); i++) {
			Node node = nodes.item(i);
			EventType eventType = EventType.createFromDomNode(node);
			eventTypes.add(eventType);
		}
	}

	public Set<EventType> getEventTypes() {
		return eventTypes;
	}
}
