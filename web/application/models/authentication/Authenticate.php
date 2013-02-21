<?php
namespace app\models\authentication;

use My\Db\Table\Row;

class Authenticate extends Row
{

	
	public function getUser() {
		return $this->findParentRow('app\models\authentication\UserTable');
	}
	
}

?>