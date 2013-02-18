<?php

namespace app\models\organizations;

use My\Db\Table\Row;
use app\services\GcmMessanger;

class Organization extends Row {

    protected $gcmMessanger;
    
    /**
     * 
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getEvents() {
        $select = $this->select()->where('active = ?', 1);
        return $this->findManyToManyRowset('app\models\events\EventTable', 'app\models\organizations\OrganizationOwnEventTable', null, null, $select);
    }

    public function getContactUser() {
        $userTable = \My_Model::get("app\models\organizations\OrganizationHasUserTable");
        $select = $userTable->select()->where("i.member = 2");
        $rowset = $this->findManyToManyRowset('app\models\authentication\UserTable', "app\models\organizations\OrganizationHasUserTable", null, null, $select);
        return $rowset->current();
    }

    /**
     * Update Organization
     * @param array $values
     * @return \app\models\organizations\Organization
     */
    public function updateFromArray(array $values) {

        $this->setFromArray($values);
        $this->save();

        return $this;
    }

    public function save() {
        parent::save();

        // GCM
        $this->gcmMessanger->sendSyncEventsMessage();
    }

    public function delete() {
        parent::delete();

        // GCM
        $this->gcmMessanger->sendSyncEventsMessage();
    }

    public function injectGcmMessanger(GcmMessanger $gcmMessanger) {
        $this->gcmMessanger = $gcmMessanger;
    }

}

?>