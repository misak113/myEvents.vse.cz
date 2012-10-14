<?php
use Zette\UI\BaseController;
/**
 * Controlleru produktu
 *
 */
class ProductController extends BaseController {
    protected $_products;


	/**
	 * Uvodni konfigurace controlleru
	 *
	 */
	public function init() {
		
	    // nastaveni baze produktu
		$this->_products = new Products();
		
		// nastaveni meny
        $currency = 'Kč';
        
		$product = new Product(1, 750000, $currency, 'Škoda Superb', 'Luxusní vůz české výroby.');
		$this->_products->addProduct($product);
		
		$product = new Product(2, 600000, $currency, 'Ford Focus', 'Sportovní vůz americké výroby.');
		$this->_products->addProduct($product);
		
		$product = new Product(3, 1000, $currency, 'Moped', 'No comment.');
		$this->_products->addProduct($product);
		
	}
	
	/**
	 * Seznam produktu
	 *
	 */
	public function indexAction() {
		
		$this->view->title = 'Seznam produktů';
		$this->view->products = $this->_products;
		
	}
	
	/**
	 * Nahled produktu
	 *
	 */
	public function previewAction() {

		/*$template = new \Nette\Templating\Template();
		$template->setSource('Ahoj');
		die($template);*/

		// zjisteni ID produktu (z URL)
		$product = $this->_products->getProduct($this->_getParam('id'));
		
		if ($product) {
		
			$this->view->title = $product->getTitle();
			$this->view->product = $product;
			
		}
		// produkt neexistuje
		else {
			$this->_helper->redirector->gotoRoute(array(), 'productList', true);
		}
		
	}
	
}
