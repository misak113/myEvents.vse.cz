<?php
namespace app\models\authorization;

use My\Db\Table\Row;

class Role extends Row
{
    
    /**
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUsers() {
        return $this->findManyToManyRowset(new \app\models\authentication\UserTable, new UserHasRoleTable);
    }
    

	public function getResources() {
		return $this->findManyToManyRowset(new ResourceTable(), new PermissionTable());
	}

	public function getPrivileges(Resource $resource = null) {
		if ($resource === null) {
			return $this->findManyToManyRowset(new PrivilegeTable(), new PermissionTable());
		} else {
			$select = $this->getTable()->select()->where('resource_id = ?', array($resource->resource_id));
			return $this->findManyToManyRowset(new PrivilegeTable(), new PermissionTable(), null, null, $select);
		}
	}

}

?>