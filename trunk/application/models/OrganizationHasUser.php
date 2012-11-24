<?php

namespace app\models;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi organizacemi a uzivateli
 *
 */
class OrganizationHasUser extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'organization_has_user';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'Organization' => array(
           'columns' => array ('organization_id'), 
           'refTableClass' => 'OrganizationTable', 
           'refColumns' => array ('organization_id')
        ), 
        'User' => array(
           'columns' => array ('user_id'), 
           'refTableClass' => 'UserTable', 
           'refColumns' => array ('user_id')
        ), 
    );

}
	
