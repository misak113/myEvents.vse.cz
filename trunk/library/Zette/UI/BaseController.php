<?php
/**
 * @author: Misak113
 * @date-created: 2.10.12
 */

class Zette_UI_BaseController extends Zend_Controller_Action {
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
		parent::__construct($request, $response, $invokeArgs);
		$this->viewSuffix = 'latte';
	}

	public function render($action = null, $name = null, $noController = false)
	{
		return parent::render($action, $name, $noController);
		if (!$this->getInvokeArg('noViewRenderer') && $this->_helper->hasHelper('viewRenderer')) {
			return $this->_helper->viewRenderer->render($action, $name, $noController);
		}

		$template   = new \Nette\Templating\Template();
		$script = $this->getViewScript($action, $noController);
		$template->setSource($script);


		$this->getResponse()->appendBody(
			(string)$template,
			$name
		);
	}
}