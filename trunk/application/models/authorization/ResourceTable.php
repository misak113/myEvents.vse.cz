<?php
namespace app\models\authorization;

use My\Db\Table;

class ResourceTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'resource';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authorization\Resource';

	/**
	 * @param string $name
	 * @return \app\models\authorization\Resource
	 */
	public function getOrCreateResource($name) {
		$resource = $this->fetchRow(array('uri_code = ?' => $name));
		if (!$resource) {
			$data = array(
				'name' => $name,
				'uri_code' => $name,
				'description' => null,
			);
			$resource = $this->createRow($data);
		}
		$resource->save();

		return $resource;
	}

}

?>