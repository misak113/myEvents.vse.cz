<?php

/**
 * Trida reprezentujici produkt
 *
 */
class Product {
	
	/**
	 * ID produktu
	 *
	 * @var int
	 */
	protected $id;
	
	/**
	 * Cena produktu
	 *
	 * @var float
	 */
	protected $price;
	
	/**
	 * Mena ceny produktu
	 *
	 * @var string
	 */
	protected $currency;
	
	/**
	 * Nazev produktu
	 *
	 * @var string
	 */
	protected $title;
	
	/**
	 * Popis produktu
	 *
	 * @var string
	 */
	protected $description;
	
	
	
	/**
	 * Konstruktor tridy
	 *
	 * @param id $id ID produktu
	 * @param float $price Cena produktu
	 * @param string $currency Mena ceny produktu
	 * @param string $title Nazev produktu
	 * @param string $description Popis produktu
	 */
	public function __construct($id, $price = 0, $currency = null, $title = null, $description = null) {
		
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
			throw new InvalidArgumentException('ID není číslo.');
		}
		
		if (is_numeric($price)) {
			$this->price = $price;
		}
		else {
			throw new InvalidArgumentException('Cena není číslo.');
		}
		
		$this->currency = $currency;
		$this->title = $title;
		$this->description = $description;
		
	}
	
	
	/**
	 * Vrati ID produktu
	 *
	 * @return int ID produktu
	 */
	public function getId() {
		
		return $this->id;
		
	}
	
	/**
	 * Vrati cenu produktu
	 *
	 * @return float Cena produktu
	 */
	public function getPrice() {
		
		return $this->price;
		
	}
	
	/**
	 * Vrati menu ceny produktu
	 *
	 * @return string Mena ceny produktu
	 */
	public function getCurrency() {
		
		return $this->currency;
		
	}
	
	/**
	 * Vrati nazev produktu
	 *
	 * @return string Nazev produktu
	 */
	public function getTitle() {
		
		return $this->title;
		
	}
	
	/**
	 * Vrati popis produktu
	 *
	 * @return string Popis produktu
	 */
	public function getDescription() {
		
		return $this->description;
		
	}
	
	
	/**
	 * Nastavi cenu produktu
	 *
	 * @param float $price Nova cena produktu
	 */
	public function setPrice($price) {
		
		if (is_numeric($price)) {
			$this->price = $price;
		}
		else {
			throw new InvalidArgumentException('Cena není číslo.');
		}
		
	}
	
	/**
	 * Nastavi menu ceny produktu
	 *
	 * @param string $currency Nova mena ceny produktu
	 */
	public function setCurrency($currency) {
		
		$this->currency = $currency;
		
	}
	
	/**
	 * Nastavi nazev produktu
	 *
	 * @param string $price Novy nazev produktu
	 */
	public function setTitle($title) {
		
		$this->title = $title;
		
	}
	
	/**
	 * Nastavi popis produktu
	 *
	 * @param string $description Novy popis produktu
	 */
	public function setDescription($description) {
		
		$this->description = $description;
		
	}
	
	/**
	 * Vypis ceny produktu
	 *
	 * @return string Cena produktu
	 */
	public function printPrice() {
		
		return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
		
	}
	
}