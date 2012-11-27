<?php
namespace app\models\authentication;

use My\Db\Table;

class AuthenticateTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'authenticate';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\Authenticate';


}

?>