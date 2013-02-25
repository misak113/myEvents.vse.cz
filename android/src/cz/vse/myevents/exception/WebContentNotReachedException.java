package cz.vse.myevents.exception;

public class WebContentNotReachedException extends Exception {
	
	private static final long serialVersionUID = 1L;
	private int responseCode;
	private Exception exception;

	public WebContentNotReachedException(int responseCode) {
		this.responseCode = responseCode;
	}
	
	public WebContentNotReachedException(Exception exception) {
		this.exception = exception;
	}

	public Exception getException() {
		return exception;
	}

	public int getResponseCode() {
		return responseCode;
	}

}
