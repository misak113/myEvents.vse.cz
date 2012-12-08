<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;


 
/*
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */
class Admin_SystemController extends BaseController {
    
    /** @var TitleLoader */
    protected $titleLoader;
  
    
    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader) {
        $this->titleLoader = $titleLoader;
    }
    
    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
    }
}
