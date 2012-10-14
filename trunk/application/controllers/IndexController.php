<?php
use Zette\UI\BaseController;
/**
 * Controller pro uvodni a informaci stranky
 *
 */
class IndexController extends BaseController {
	
	/**
	 * Uvodni konfigurace controlleru
	 *
	 */
	public function init() {
		
	}
	
	/**
	 * Uvodni stranka
	 *
	 */
	public function indexAction() {
		
		$this->view->title = 'Vítejte na e-shopu';

	}
	
	/**
	 * Stranka 'O nas'
	 *
	 */
	public function aboutAction() {
		
		$this->view->title = 'O nás';
		
	}
	
	/**
	 * Stranka 'Kontakt'
	 *
	 */
	public function contactAction() {
		
		$this->view->title = 'Kontakt';
		
	}
	
}
