<?php
namespace app\models\authorization;

use My\Db\Table\Row;

class Role extends Row
{


	public function getResources() {
		return $this->findManyToManyRowset(new ResourceTable(), new PermissionTable());
	}

	public function getPrivileges() {
		return $this->findManyToManyRowset(new PrivilegeTable(), new PermissionTable());
	}

}

?>