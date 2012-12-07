<?php
namespace app\models\organizations;

use My\Db\Table\Row;

class Organization extends Row
{
    /**
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getEvents() {

            return $this->findManyToManyRowset('app\models\events\EventTable', 'app\models\organizations\OrganizationOwnEventTable');

    }

}

?>