<?php

namespace app\models\authorization;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi akcemi a sponzory
 *
 */
class UserHasRoleTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'user_has_role';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'User' => array(
           'columns' => array ('user_id'), 
           'refTableClass' => 'app\models\authentication\UserTable', 
           'refColumns' => array ('user_id')
        ), 
        'Role' => array(
           'columns' => array ('role_id'), 
           'refTableClass' => 'app\models\authorization\RoleTable', 
           'refColumns' => array ('role_id')
        ), 
    );

}
	
