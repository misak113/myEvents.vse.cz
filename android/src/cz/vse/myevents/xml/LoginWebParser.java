package cz.vse.myevents.xml;

import java.io.InputStream;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import cz.vse.myevents.account.User;
import cz.vse.myevents.exception.UserNotFoundException;

public class LoginWebParser extends WebParser {
	private User user;
	private Set<Integer> orgSubIds = new HashSet<Integer>();

	public LoginWebParser(InputStream inputStream) throws UserNotFoundException {
		super(inputStream);
		initData();
	}
	
	private void initData() throws UserNotFoundException {
		Document doc = getDomElement();
		int status = Integer.valueOf(doc.getElementsByTagName("status").item(0).getTextContent());
		
		// User not found
		if (status == 0) {
			throw new UserNotFoundException();
		}
		
		HashMap<String, String> name = new HashMap<String, String>();
		
		Node userNode = doc.getElementsByTagName("user").item(0);
		NodeList userChildNodes = userNode.getChildNodes();
		
		for (int i = 0; i < userChildNodes.getLength(); i++) {
			Node node = userChildNodes.item(i);

			// Name
			if (node.getNodeName().equals("name")) {
				NodeList nameNodes = node.getChildNodes();
				for (int ii = 0; ii < nameNodes.getLength(); ii++) {
					String nodeName = nameNodes.item(ii).getNodeName();
					String nodeValue = nameNodes.item(ii).getTextContent();
					name.put(nodeName, nodeValue);
				}
			}
			
			// Organizations
			if (node.getNodeName().equals("organizations")) {
				NodeList orgNodes = node.getChildNodes();

				for (int ii = 0; ii < orgNodes.getLength(); ii++) {
					Node orgNode = orgNodes.item(ii);
					if (orgNode.getNodeName().equals("organization")) {
						Integer id = Integer.valueOf(((Element) orgNode).getAttribute("id"));
						orgSubIds.add(id);
					}
				}
			}
		}
		
		int userId = Integer.valueOf(((Element) userNode).getAttribute("id"));
		user = new User(userId, name.get("first"), name.get("last"));
	}

	public User getUser() {
		return user;
	}

	public Set<Integer> getOrgSubIds() {
		return orgSubIds;
	}
}
