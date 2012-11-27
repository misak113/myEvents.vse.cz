<?php

namespace app\models\events;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi akcemi a sponzory
 *
 */
class EventHasSponsorTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'event_has_sponsor';
    	
    
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
        'Sponsor' => array(
           'columns' => array ('sponsor_id'), 
           'refTableClass' => 'app\models\events\SponsorTable', 
           'refColumns' => array ('sponsor_id')
        ), 
    );

}
	
