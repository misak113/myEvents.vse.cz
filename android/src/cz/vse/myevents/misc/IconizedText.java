package cz.vse.myevents.misc;


public class IconizedText {

	private String text;
	private int textResourceId;
	private int drawableResourceId;

	public IconizedText(String text, int drawableResourceId) {
		this.text = text;
		this.drawableResourceId = drawableResourceId;
	}
	
	public IconizedText(int textResourceId, int drawableResourceId) {
		this.textResourceId = textResourceId;
		this.drawableResourceId = drawableResourceId;
	}

	public String getText() {
		return text;
	}

	public int getTextResourceId() {
		return textResourceId;
	}

	public int getDrawableResourceId() {
		return drawableResourceId;
	}
}
