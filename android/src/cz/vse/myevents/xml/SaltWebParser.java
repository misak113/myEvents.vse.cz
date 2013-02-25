package cz.vse.myevents.xml;

import java.io.InputStream;

import org.w3c.dom.Document;

import cz.vse.myevents.exception.UserNotFoundException;

public class SaltWebParser extends WebParser {
	
	private String salt;

	public SaltWebParser(InputStream inputStream) throws UserNotFoundException {
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
		
		salt = doc.getElementsByTagName("salt").item(0).getTextContent();
	}

	public String getSalt() {
		return salt;
	}
}
