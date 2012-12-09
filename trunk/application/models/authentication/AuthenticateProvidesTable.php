<?php
namespace app\models\authentication;

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
	protected $_rowClass = 'app\models\authentication\AuthenticateProvides';


	/**
	 * Vrátí provides podle názvu a pokud neexistuje tak ji vytvoří
	 * @param string $name název provides
	 * @param string $description popis
	 * @return AuthenticateProvides
	 */
	public function getOrCreateProvides($name, $description) {
		$provides = $this->fetchRow(array('name = ?' => $name));
		if (!$provides) {
			$data = array(
				'name' => $name,
				'description' => $description,
				'active' => 1,
			);
			$provides = $this->createRow($data);
		}
		$provides->save();

		return $provides;
	}

}

?>