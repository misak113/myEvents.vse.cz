package cz.vse.myevents.xml;

import java.io.InputStream;
import java.util.HashSet;
import java.util.Set;

import org.w3c.dom.Document;
import org.w3c.dom.NodeList;

import cz.vse.myevents.serverdata.Organization;

public class OrganizationsWebParser extends WebParser {
	
	private Set<Organization> organizations = new HashSet<Organization>();

	public OrganizationsWebParser(InputStream inputStream) {
		super(inputStream);
		initData();
	}

	private void initData() {
		Document doc = getDomElement();
		NodeList orgNodes = doc.getElementsByTagName("organization");
		
		for (int i = 0; i < orgNodes.getLength(); i++) {
			getOrganizations().add(Organization.createFromDomNode(orgNodes.item(i)));
		}
	}

	public Set<Organization> getOrganizations() {
		return organizations;
	}
}
