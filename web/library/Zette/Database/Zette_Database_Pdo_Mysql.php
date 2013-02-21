<?php

use Zette\Database\Diagnostics\ConnectionPanel;
/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 4.12.12
 * Time: 20:58
 * To change this template use File | Settings | File Templates.
 */
class Zette_Database_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
	/** @var \Zette\Database\Diagnostics\ConnectionPanel */
	protected $connectionPanel;

	public function setConnectionPanel(ConnectionPanel $connectionPanel) {
		$this->connectionPanel = $connectionPanel;
	}

	/**
	 * @param string|Zend_Db_Select $sql
	 * @param array $params
	 * @return void|Zend_Db_Statement_Pdo
	 */
	public function query($sql, $bind = array()) {
		$stmt = parent::query($sql, $bind);
		$this->connectionPanel->logQuery($stmt);
		return $stmt;
	}

}
