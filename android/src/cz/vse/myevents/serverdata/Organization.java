package cz.vse.myevents.serverdata;

import java.io.File;
import java.net.MalformedURLException;
import java.net.URL;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import android.content.Context;
import android.database.Cursor;
import cz.vse.myevents.database.data.DataContract.Organizations;
import cz.vse.myevents.filefilter.OrganizationLogoFileFilter;

public class Organization {
	private int id;
	private String name;
	private URL website;
	private String info;
	private String email;
	private URL fbLink;
	private String contactPerson;
	private String serverLogoUrl;
	private long serverLogoCrc;
	
	public Organization(int id, String name) {
		this.id = id;
		this.name = name;
	}
	
	private Organization() {}
	
	public static Organization createFromDomNode(Node node)  {
		if (!node.getNodeName().equals("organization")) {
			throw new IllegalArgumentException("No organization node given");
		}

		Organization organization = new Organization();
		NodeList dataNodes = node.getChildNodes();
		
		// ID
		organization.id = Integer.valueOf(((Element) node).getAttribute("id"));
		
		for (int i = 0; i < dataNodes.getLength(); i++) {
			String nodeName = dataNodes.item(i).getNodeName();
			String nodeValue = dataNodes.item(i).getTextContent();
			
			
			// Name
			if (nodeName.equals("name")) {
				organization.name = nodeValue;
			
			// Website
			} else if (nodeName.equals("website")) {
				try {
					organization.website = new URL(nodeValue);
				} catch (MalformedURLException e) {
				}
				
			// Info
			} else if (nodeName.equals("info")) {
				organization.info = nodeValue;
				
			// Email
			} else if (nodeName.equals("email")) {
				organization.email = nodeValue;
				
			// Facebook link
			} else if (nodeName.equals("fbLink")) {
				try {
					organization.fbLink = new URL(nodeValue);
				} catch (MalformedURLException e) {
				}
				
			// Contact person
			} else if (nodeName.equals("contactPerson")) {
				organization.contactPerson = nodeValue;
			}

			// Logo
			else if (nodeName.equals("logo")) {
				NodeList imageNodes = dataNodes.item(i).getChildNodes();
				for (int ii = 0; ii < imageNodes.getLength(); ii++) {
					if (imageNodes.item(ii).getNodeName().equals("url")) {
						organization.serverLogoUrl = imageNodes.item(ii).getTextContent();
					} else if (imageNodes.item(ii).getNodeName().equals("crc")) {
						try {
							organization.serverLogoCrc = Long.parseLong(imageNodes.item(ii).getTextContent());
						} catch (NumberFormatException ex) {
						}
					}
				}
			}
		}
		
		return organization;
	}
	
	public static Organization createFromCursor(Cursor cursor) {
		Organization organization = new Organization();
		
		organization.id = cursor.getInt(cursor.getColumnIndex(Organizations._ID));
		organization.name = cursor.getString(cursor.getColumnIndex(Organizations.NAME));
		try {
			organization.website = new URL(cursor.getString(cursor.getColumnIndex(Organizations.WEBSITE)));
		} catch (MalformedURLException e) {
		}
		organization.info = cursor.getString(cursor.getColumnIndex(Organizations.INFO));
		organization.email = cursor.getString(cursor.getColumnIndex(Organizations.EMAIL));
		try {
			organization.fbLink = new URL(cursor.getString(cursor.getColumnIndex(Organizations.FB_LINK)));
		} catch (MalformedURLException e) {
		}
		organization.contactPerson = cursor.getString(cursor.getColumnIndex(Organizations.CONTACT_PERSON));
		
		return organization;
	}
	
    @Override
    public int hashCode() {
        int hash = 7;
        hash = 67 * hash + this.id;
        hash = 67 * hash + (this.name != null ? this.name.hashCode() : 0);
        hash = 67 * hash + (this.website != null ? this.website.hashCode() : 0);
        hash = 67 * hash + (this.info != null ? this.info.hashCode() : 0);
        hash = 67 * hash + (this.email != null ? this.email.hashCode() : 0);
        hash = 67 * hash + (this.fbLink != null ? this.fbLink.hashCode() : 0);
        hash = 67 * hash + (this.contactPerson != null ? this.contactPerson.hashCode() : 0);
        return hash;
    }

    @Override
    public boolean equals(Object obj) {
        if (obj == null) {
            return false;
        }
        if (getClass() != obj.getClass()) {
            return false;
        }
        final Organization other = (Organization) obj;
        if (this.id != other.id) {
            return false;
        }
        if ((this.name == null) ? (other.name != null) : !this.name.equals(other.name)) {
            return false;
        }
        if (this.website != other.website && (this.website == null || !this.website.equals(other.website))) {
            return false;
        }
        if ((this.info == null) ? (other.info != null) : !this.info.equals(other.info)) {
            return false;
        }
        if ((this.email == null) ? (other.email != null) : !this.email.equals(other.email)) {
            return false;
        }
        if (this.fbLink != other.fbLink && (this.fbLink == null || !this.fbLink.equals(other.fbLink))) {
            return false;
        }
        if ((this.contactPerson == null) ? (other.contactPerson != null) : !this.contactPerson.equals(other.contactPerson)) {
            return false;
        }
        return true;
    }
    
    public String getLogoPath(Context context) {
    	String path = null;
    	
    	File[] files = context.getFilesDir().listFiles(new OrganizationLogoFileFilter(id));
    	try {
	    	path = files[0].getAbsolutePath();
		} catch (ArrayIndexOutOfBoundsException ex) {
		} catch (NullPointerException ex) {
		}
    	
    	return path;
    }

	public long getId() {
		return id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public URL getWebsite() {
		return website;
	}

	public void setWebsite(URL website) {
		this.website = website;
	}

	public String getInfo() {
		return info;
	}

	public void setInfo(String info) {
		this.info = info;
	}

	public String getEmail() {
		return email;
	}

	public void setEmail(String email) {
		this.email = email;
	}

	public URL getFbLink() {
		return fbLink;
	}

	public void setFbLink(URL fbLink) {
		this.fbLink = fbLink;
	}

	public String getContactPerson() {
		return contactPerson;
	}

	public void setContactPerson(String contactPerson) {
		this.contactPerson = contactPerson;
	}

	public String getServerLogoUrl() {
	    return serverLogoUrl;
    }

	public void setServerLogoUrl(String serverLogoUrl) {
	    this.serverLogoUrl = serverLogoUrl;
    }

	public long getServerLogoCrc() {
	    return serverLogoCrc;
    }

	public void setServerLogoCrc(long serverLogoCrc) {
	    this.serverLogoCrc = serverLogoCrc;
    }
}
