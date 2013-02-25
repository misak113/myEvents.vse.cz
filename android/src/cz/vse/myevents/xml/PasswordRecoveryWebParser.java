package cz.vse.myevents.xml;

import java.io.InputStream;

import org.w3c.dom.Document;
import org.w3c.dom.NodeList;

public class PasswordRecoveryWebParser extends WebParser {

	public static final int STATUS_OK = 1;
	public static final int STATUS_FAILED = 0;
	public static final int STATUS_NO_SUCH_ACCOUNT = 2;
	
	private int status;
	
	public PasswordRecoveryWebParser(InputStream inputStream) {
		super(inputStream);
		initData();
	}

	private void initData() {
		Document document = getDomElement();
		NodeList statusNodes = document.getElementsByTagName("status");
		status = Integer.parseInt(statusNodes.item(0).getTextContent());
	}

	public int getStatus() {
		return status;
	}
}
