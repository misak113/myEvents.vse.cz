<?phpuse Zette\UI\BaseController;use app\services\TitleLoader;/** * Controller pro uvodni a informaci stranky * */class IndexController extends BaseController {        const ANDROID_BUG_REPORT_SALT = "f6eWRuwr";    /** @var TitleLoader */    protected $titleLoader;    protected $userTable;    protected $authenticateTable;    /**     * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba     * Mezi služby se řadí také modely a DB modely     * Je třeba nadefinovat modely v config.neon     * @param app\services\TitleLoader $titleLoader     */    public function setContext(    TitleLoader $titleLoader, app\models\authentication\AuthenticateTable $authenticateTable, app\models\authentication\UserTable $userTable) {        $this->titleLoader = $titleLoader;        $this->authenticateTable = $authenticateTable;        $this->userTable = $userTable;    }    /**     * O nás     */    public function aboutAction() {        $this->template->title = $this->t($this->titleLoader->getTitle('Index:about'));    }    /**     * Kontakt     */    public function contactAction() {        $this->template->title = $this->t($this->titleLoader->getTitle('Index:contact'));    }        public function androidbugreportAction() {        $this->_helper->layout->disableLayout();        Nette\Diagnostics\Debugger::$bar = FALSE;        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');                $authToken = $this->_getParam("authToken");        $version = $this->_getParam("version");        $sender = $this->_getParam("senderEmail");        $text = str_replace("&#47;", "/", $this->_getParam("text"));                // Check auth token        $versionExploded = explode("-", $version);        $checkAuthToken = hash("sha256", $versionExploded[0] . "-" . self::ANDROID_BUG_REPORT_SALT . $versionExploded[1]);        if ($authToken != $checkAuthToken) {            $status = 0;        } else {            try {                $text .= "\n\nVerze: " . $version;                $text .= "\nDatum: " . date("j.n.Y G:i:s");                $mail = new Zend_Mail("utf-8");                $mail->addTo("james.macoun@gmail.com", "Jakub Macoun");                $mail->setSubject("myEvents android bug report");                $mail->setFrom($sender, $sender);                $mail->setBodyText($text);                $mail->send();                $status = 1;            } catch (Exception $ex) {                $status = 0;            }        }                $this->template->status = $status;    }}