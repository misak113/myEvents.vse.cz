<?php
namespace app\models\organizations;

use My\Db\Table\Row;

class Organization extends Row
{

	public function getEvents() {
	
		return $this->findManyToManyRowset('app\models\events\EventTable', 'app\models\organizations\OrganizationOwnEventTable');
	
	}
	
}

?>