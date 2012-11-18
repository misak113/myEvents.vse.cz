<?phpuse Zette\UI\BaseController;/** * Controller pro odchytavani a vypis chybovych stavu * */class ErrorController extends BaseController {		/**	 * Odchytavani a vypis chybovych stavu	 *	 */	public function errorAction() {		if (APPLICATION_ENV != 'development' && strstr(APPLICATION_ENV, 'localhost') === false) {			$this->_helper->redirector->gotoRoute(array(),				'default',			true);		}		$errors = $this->getParam('error_handler');		switch ($errors->type) {			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:				// stranka nebyla nalezena - HTTP chybova hlaska 404				$this->getResponse()->setHttpResponseCode(404);				$this->template->message = 'Stránka nenalezena';				break;			default:				// chyba v aplikaci - HTTP chybova hlaska 500				$this->getResponse()->setHttpResponseCode(500);				$this->template->message = 'Chyba v aplikaci';				break;		}		$this->template->env = APPLICATION_ENV;		$this->template->exception = $errors->exception;		$this->template->request   = $errors->request;		$this->template->assign('title', 'Objevila se chyba');		$this->setLayout('error');	}}