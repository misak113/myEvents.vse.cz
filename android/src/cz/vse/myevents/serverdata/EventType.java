package cz.vse.myevents.serverdata;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import android.database.Cursor;
import cz.vse.myevents.database.data.DataContract.EventTypes;

public class EventType {
	private int id;
	private String name;

	public EventType() {
	}

	public EventType(int id, String name) {
		this.id = id;
		this.name = name;
	}

	public static EventType createFromDomNode(Node node) {
		if (!node.getNodeName().equals("type")) {
			throw new IllegalArgumentException("No event type node passed");
		}

		NodeList dataNodes = node.getChildNodes();
		EventType eventType = new EventType();

		// ID
		eventType.id = Integer.parseInt(((Element) node).getAttribute("id"));

		for (int i = 0; i < dataNodes.getLength(); i++) {
			Node dataNode = dataNodes.item(i);
			String dataNodeName = dataNode.getNodeName();
			String dataNodeValue = dataNode.getTextContent();

			// Name
			if (dataNodeName.equals("name")) {
				eventType.name = dataNodeValue;
			}
		}

		return eventType;
	}

	public static EventType createFromCursor(Cursor cursor) {
		EventType eventType = new EventType();

		eventType.id = cursor.getInt(cursor.getColumnIndex(EventTypes._ID));
		eventType.name = cursor.getString(cursor.getColumnIndex(EventTypes.NAME));

		return eventType;
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}
}
