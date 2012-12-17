<?php

namespace app\models\organizations;

use My\Db\Table\Row;

class Organization extends Row {

    /**
     * 
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getEvents() {
        return $this->findManyToManyRowset('app\models\events\EventTable', 'app\models\organizations\OrganizationOwnEventTable');
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

}

?>