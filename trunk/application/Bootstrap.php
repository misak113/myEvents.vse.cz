<?phpuse Zette\Diagnostics\TimerPanel;use Zette\Application\Route;use Zette\Config\ZetteBootstrap;/** * Uvodni inicializace aplikace * */require_once __DIR__ . '/../library/Zette/Config/ZetteBootstrap.php';class Bootstrap extends ZetteBootstrap {    /**     * Nastaveni helperu     * @deprecated     */    protected function _initHelpers() {        $view = $this->getResource('view');        $prefix = 'My_View_Helper';        $dir = APPLICATION_PATH . '/../library/My/View/Helper';        $view->addHelperPath($dir, $prefix);        TimerPanel::traceTime();    }    /**     * Nastaveni prepisu URL     *     * @param array $options     */    protected function _initRequest(array $options = array()) {        $router = $this->router;        // Statics        $router->addRoute('aboutUs', new Route('o-nas', array(                    'module' => 'default',                    'controller' => 'index',                    'action' => 'about',                ))        );        $router->addRoute('contact', new Route('kontakt', array(                    'module' => 'default',                    'controller' => 'index',                    'action' => 'contact',                ))        );        // Events        $router->addRoute('eventList', new Route('udalosti/:categoryId', array(                    'module' => 'default',                    'controller' => 'event',                    'action' => 'list',                    'categoryId' => '',                ))        );		$router->addRoute('eventList', new Route('', array(				'module' => 'default',				'controller' => 'event',				'action' => 'list',			))		);        $router->addRoute('event', new Route('udalost/:id/:title', array(                    'module' => 'default',                    'controller' => 'event',                    'action' => 'detail',                        ), array(                    'id' => '\d+',                ))        );        // User accounts        $router->addRoute('userRegister', new Route('registrace', array(                    'module' => 'default',                    'controller' => 'user',                    'action' => 'register',                ))        );        $router->addRoute('userLogin', new Route('login', array(                    'module' => 'default',                    'controller' => 'user',                    'action' => 'login',                ))        );		$router->addRoute('userFbLogin', new Route('fb-login', array(				'module' => 'default',				'controller' => 'user',				'action' => 'fb-login',			))		);        $router->addRoute('userLogout', new Route('logout', array(                    'module' => 'default',                    'controller' => 'user',                    'action' => 'logout',                ))        );        $router->addRoute('userActivation', new Route('aktivace/:id/:password', array(                    'module' => 'default',                    'controller' => 'user',                    'action' => 'activate',                        ), array(                    'id' => '\d+',                    'password' => '[a-z0-9]{10}'                ))        );        // Admin        $router->addRoute('adminIndex', new Route('administrace', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'index',                ))        );        $router->addRoute('adminEvents', new Route('administrace/udalosti', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'index',                ))        );        $router->addRoute('newEvent', new Route('administrace/nova-udalost', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'edit',                ))        );        $router->addRoute('editEvent', new Route('administrace/editovat-udalost/:id', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'edit',                        ), array(                    'id' => '\d+',                ))        );        $router->addRoute('getClassrooms', new Route('administrace/ziskat-ucebny', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'autocompleteclassrooms'                ))        );        $router->addRoute('uploadPicture', new Route('administrace/upload-logo', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'handleupload'                ))        );        $router->addRoute('editOrganization', new Route('administrace/editovat-organizaci', array(                    'module' => 'admin',                    'controller' => 'organization',                    'action' => 'edit',                ))        );        $router->addRoute('adminSystem', new Route('administrace/system', array(                    'module' => 'admin',                    'controller' => 'system',                    'action' => 'index',                ))        );        $router->addRoute('organizationAdmins', new Route('administrace/system/admini', array(                    'module' => 'admin',                    'controller' => 'system',                    'action' => 'admins',                ))        );        // Organizace        $router->addRoute('organization', new Route('organizace/:id/:title', array(                    'module' => 'default',                    'controller' => 'organization',                    'action' => 'detail',                        ), array(                    'id' => '\d+',                ))        );        $router->addRoute('organizationList', new Route('organizace', array(                    'module' => 'default',                    'controller' => 'organization',                    'action' => 'list',                ))        );        $router->addRoute('eventFbImport', new Route('administrace/facebook-import-udalosti', array(                    'module' => 'admin',                    'controller' => 'event',                    'action' => 'fb-import',                ))        );		$router->addRoute('eventFbExport', new Route('administrace/facebook-export-udalosti', array(				'module' => 'admin',				'controller' => 'event',				'action' => 'fb-export',			))		);        // Landing page @todo smazat pro real pages        /*$router->addRoute('landing', new Route('', array(                    'module' => 'landing',                    'controller' => 'index',                    'action' => 'index',                ))        );*/        TimerPanel::traceTime();    }}