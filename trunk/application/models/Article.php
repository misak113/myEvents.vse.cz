<?php
/**
 * @author: Misak113
 * @date-created: 2.10.12
 */
class Article
{
	/** @var string */
	protected $name;
	protected $id;
	protected $text;

	protected static $nextId = 1;

	public function __construct($name, $text) {
		$this->name = $name;
		$this->id = ++self::$nextId;
		$this->text = $text;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	public function getTitle() {
		return $this->text;
	}

	public function getText() {
		return $this->text;
	}
}
