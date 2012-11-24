<?php
namespace app\models;

use My\Db\Table;

class AuthenticateProvidesTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'authenticate_provides';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\AuthenticateProvides';


}

?>