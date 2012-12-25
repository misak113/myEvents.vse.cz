<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\authentication\UserTable;

/*
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */

class Admin_OrganizationController extends BaseController {

    /** @var TitleLoader */
    protected $titleLoader;

    /** @var \app\models\authentication\UserTable */
    protected $userTable;

    public function init() {
        $this->_helper->layout->setLayout('admin_org');
    }

    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader, UserTable $userTable
    ) {

        $this->titleLoader = $titleLoader;
        $this->userTable = $userTable;
    }

    public function editAction() {
        $userId = $this->user->getId();
        $user = $this->userTable->getById($userId);
        $organizations = $user->getOrganizations();
        if (count($organizations) > 0)
            $record = $organizations[0];
        else
            throw new Zend_Auth_Exception;

        $this->template->title = $this->titleLoader->getTitle('Admin:Organization:edit');

        $form = new \OrganizationForm;

        $form->setAction($this->_helper->url->url());

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $formValues = $form->getValues();
                $this->template->formvalues = $formValues;

                $record->updateFromArray($formValues);


                //TODO flashmessage zmeny ulozeny


                $this->_helper->redirector->gotoRoute(
                        array(
                    'module' => 'admin',
                    'controller' => 'event',
                    'action' => 'index'
                        ), 'default', true
                );
            }
        } else {
            if ($record !== null) {

                $data = $record->toArray();
                $form->populate($data);
            }
        }


        $this->template->form = $form;
    }

}
