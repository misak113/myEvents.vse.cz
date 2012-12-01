<?phpnamespace app\models\events;use My\Db\Table;/** * Trida reprezentujici seznam akci * */class EventTable extends Table {		/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	
	protected $_name = 'event';
	
	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\events\Event';		}