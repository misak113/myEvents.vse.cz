<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use \app\models\organizations\OrganizationTable;

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

    /** @var OrganizationTable */
    protected $organizationTable;

    /** @var \app\models\organizations\OrganizationHasUserTable */
    protected $organizationHasUserTable;

    /** @var \app\models\authorization\RoleTable */
    protected $roleTable;
    
    protected $gcmMessanger;

    public function init() {
        $this->_helper->layout->setLayout('admin_sys');
    }

    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader) {
        $this->titleLoader = $titleLoader;
    }

    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
    }

    public function adminsAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
        $this->template->organizations = $this->organizationTable->getOrganizations();

        $role = $this->roleTable->getOrCreateRole($roleName = 'orgAdmin');
        $this->template->users = $role->getUsers();

        $this->template->actionUrl = $this->_helper->url->url();


        if ($this->_request->isPost()) {
            $values = $this->_request->getPost();

            $this->organizationHasUserTable->saveAdmins($values);

//            if( $form->isValid($this->_request->getPost()) ) {
//                $formValues = $form->getValues();
//                $this->template->formvalues = $formValues;
            // $record->updateFromArray($formValues);
            //TODO flashmessage zmeny ulozeny


            $this->_helper->redirector->gotoRoute(
                    array(
                'module' => 'admin',
                'controller' => 'system',
                'action' => 'admins'
                    ), 'default', true
            );
        } else {
            $orgUsers = $this->organizationHasUserTable->getAdmins();
            if ($orgUsers) {
                $adminArray = array();
                foreach ($orgUsers as $orgUser) {
                    $adminArray[$orgUser->organization_id][] = $orgUser->user_id;
                }
                $this->template->adminArray = $adminArray;
            }
        }
    }
    
    public function androidAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:System:android');
        
        if (isset($_POST["forceAndroidSync"]) && !empty($_POST["forceAndroidSync"])) {
            $this->gcmMessanger->sendSyncAllMessage(true);
            $this->flashMessage("Příkaz k vynucené synchronizaci byl rozeslán");
        }
    }

    public function injectTables(
            OrganizationTable $organizationTable,
            \app\models\organizations\OrganizationHasUserTable $organizationHasUserTable,
            app\models\authorization\RoleTable $roleTable,
            app\services\GcmMessanger $gcmMessanger) {
        $this->organizationTable = $organizationTable;
        $this->organizationHasUserTable = $organizationHasUserTable;
        $this->roleTable = $roleTable;
        $this->gcmMessanger = $gcmMessanger;
    }

}
