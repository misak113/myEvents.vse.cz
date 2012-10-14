<?php

/**
 * Trida reprezentujici seznam produktu
 *
 */
class Products implements Iterator {
	
	/**
	 * Seznam produktu
	 *
	 * @var array
	 */
	protected $products = array();
	
	
	
	/**
	 * Konstruktor tridy
	 *
	 */
	public function __construct() {
		
	}
	
	
	/**
	 * Vrati seznam produktu
	 *
	 * @return array Seznam produktu
	 */
	public function getProducts() {
		
		return $this->products;
		
	}
	
	/**
	 * Vrati produkt ze seznamu
	 *
	 * @param int $productId ID produktu
	 * @return Product Produkt
	 */
	public function getProduct($productId) {
		
		if (key_exists($productId, $this->products)) {
			return $this->products [$productId];
		}
		
		return null;
		
	}
	
	/**
	 * Prida dalsi produkt do seznamu
	 *
	 * @param Product $product Produkt
	 */
	public function addProduct($product) {
		
		if (! key_exists($product->getId(), $this->products)) {
			
			$this->products [$product->getId()] = $product;
			reset($this->products);
			
		}
		
	}
	
	/**
	 * Odstrani produkt ze seznamu
	 *
	 * @param int $productId ID produktu
	 */
	public function removeProduct($productId) {
		
		if (key_exists($productId, $this->products)) {
			unset($this->products [$productId]);
		}
		
	}
	
	
	/**
	 * Vrati produkt na aktualni pozici v seznamu
	 *
	 * @return Product Produkt
	 */
	public function current() {
		
		return current($this->products);
		
	}
	
	/**
	 * Vrati dalsi produkt ze seznamu
	 *
	 * @return Product Produkt
	 */
	public function next() {
		
		return next($this->products);
		
	}
	
	/**
	 * Aktualizuje seznam produktu na zacatek
	 *
	 */
	public function rewind() {
		
		reset($this->products);
		
	}
	
	/**
	 * Vrati pozici aktualniho produktu v seznamu
	 *
	 * @return integer Klic v seznamu produktu (ID produktu)
	 */
	public function key() {
		
		return key($this->products);
		
	}
	
	/**
	 * Vrati, zda seznam produktu obsahuje dalsi produkt
	 *
	 * @return bool Zda seznam produktu obsahuje dalsi produkt
	 */
	public function valid() {
		
		return $this->current() !== false;
		
	}
	
	/**
	 * Zda je seznam prazdny nebo ne
	 *
	 * @return bool 'true' pokud je seznam prazdny, 'false' pokud neni
	 */
	public function isEmpty() {
		
		if (count($this->products) > 0) {
			return false;
		}
		
		return true;
		
	}
	
}
