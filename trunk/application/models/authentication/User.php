<?php
namespace app\models\authentication;

use My\Db\Table\Row;

class User extends Row
{
	public function getFullName() 
	{
		
		return $this->first_name . " " . $this->last_name; 	
		
	}
	
	public function getOrganizations() {

		return $this->findManyToManyRowset('app\models\organizations\OrganizationTable', 'app\models\organizations\OrganizationHasUserTable');
	
	}
        
        public function getRoles() {
            return $this->findManyToManyRowset('app\models\authorization\RoleTable', 'app\models\authorization\UserHasRoleTable');
        }
        
}
 
?>