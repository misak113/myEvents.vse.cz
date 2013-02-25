package cz.vse.myevents.exception;

public class UserNotFoundException extends Exception {

	private static final long serialVersionUID = 1L;

	public UserNotFoundException() {
	}

	public UserNotFoundException(String detailMessage) {
		super(detailMessage);
	}

	public UserNotFoundException(Throwable throwable) {
		super(throwable);
	}

	public UserNotFoundException(String detailMessage, Throwable throwable) {
		super(detailMessage, throwable);
	}

}
