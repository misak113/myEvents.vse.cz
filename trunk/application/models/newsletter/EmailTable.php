<?php
namespace app\models\newsletter;

use My\Db\Table;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 16.11.12
 * Time: 19:48
 * To change this template use File | Settings | File Templates.
 */
class EmailTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'email';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\newsletter\Email';


}
