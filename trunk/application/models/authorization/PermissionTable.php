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

}
	
