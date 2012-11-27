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
           'refTableClass' => 'RoleTable', 
           'refColumns' => array ('role_id')
        ), 
        'Privilege' => array(
           'columns' => array ('privilege_id'), 
           'refTableClass' => 'PrivilegeTable', 
           'refColumns' => array ('privilige_id')
        ), 
    	'Resource' => array(
    		'columns' => array ('resource_id'),
    		'refTableClass' => 'ResourceTable',
    		'refColumns' => array ('resource_id')
    	),
    );

}
	
