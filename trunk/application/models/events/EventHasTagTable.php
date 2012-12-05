<?php

namespace app\models\events;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi akcemi a sponzory
 *
 */
class EventHasTagTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'event_has_tag';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'Event' => array(
           'columns' => array ('event_id'), 
           'refTableClass' => 'app\models\events\EventTable', 
           'refColumns' => array ('event_id')
        ), 
        'Tag' => array(
           'columns' => array ('tag_id'), 
           'refTableClass' => 'app\models\events\TagTable', 
           'refColumns' => array ('tag_id')
        ), 
    );

}
	
