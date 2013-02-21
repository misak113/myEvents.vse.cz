<?php

namespace app\models\organizations;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi organizacemi a udalostmi
 *
 */
class OrganizationOwnEventTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'organization_own_event';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'Organization' => array(
           'columns' => array ('organization_id'), 
           'refTableClass' => 'app\models\organizations\OrganizationTable', 
           'refColumns' => array ('organization_id')
        ), 
        'Event' => array(
           'columns' => array ('event_id'), 
           'refTableClass' => 'app\models\events\EventTable', 
           'refColumns' => array ('event_id')
        ), 
    );

}
	
