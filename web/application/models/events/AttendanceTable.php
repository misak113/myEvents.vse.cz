<?php

namespace app\models\events;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi akcemi a uzivateli
 *
 */
class AttendanceTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'attendance';
    	
    
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
        'User' => array(
           'columns' => array ('user_id'), 
           'refTableClass' => 'app\models\authentication\UserTable', 
           'refColumns' => array ('user_id')
        ), 
    );

}
	
