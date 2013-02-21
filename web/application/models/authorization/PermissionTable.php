<?php

namespace app\models\authorization;

use My\Db\Table;

class PermissionTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'permission';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'Role' => array(
           'columns' => array ('role_id'), 
           'refTableClass' => 'app\models\authorization\RoleTable', 
           'refColumns' => array ('role_id')
        ), 
        'Privilege' => array(
           'columns' => array ('privilege_id'), 
           'refTableClass' => 'app\models\authorization\PrivilegeTable', 
           'refColumns' => array ('privilege_id')
        ), 
    	'Resource' => array(
    		'columns' => array ('resource_id'),
    		'refTableClass' => 'app\models\authorization\ResourceTable',
    		'refColumns' => array ('resource_id')
    	),
    );

	public function updatePermissions(array $roles) {
		// radši v transakci
		$this->_db->beginTransaction();
		// Smazat všechno
		$this->delete(array());
		// přidat postupně
		foreach ($roles as $role_id => $resources) {
			foreach ($resources as $resource_id => $privileges) {
				foreach ($privileges as $privilege_id => $value) {
					if ($value) {
						$permission = $this->createRow(array(
							'role_id' => $role_id,
							'resource_id' => $resource_id,
							'privilege_id' => $privilege_id,
						));
						$permission->save();
					}
				}
			}
		}
		// commitnutí transakce
		$status = $this->_db->commit();
		return (bool)$status;
	}

}
	
