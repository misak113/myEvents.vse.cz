<?php

namespace app\models;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi akcemi a sponzory
 *
 */
class EventHasSponsor extends Table {

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
           'refTableClass' => 'EventTable', 
           'refColumns' => array ('event_id')
        ), 
        'Sponsor' => array(
           'columns' => array ('sponsor_id'), 
           'refTableClass' => 'SponsorTable', 
           'refColumns' => array ('sponsor_id')
        ), 
    );

}
	
