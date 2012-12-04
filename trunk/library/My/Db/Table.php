<?php
namespace My\Db;

/**
 * Trida reprezentujici obecny tabulku v databazi
 *
 */
class Table extends \Zend_Db_Table_Abstract {

    /**
     * Navrati zaznam dle zadaneho primarniho klice
     *
     * @param string $id
     * @return \My\Db\Table\Row
     */
    public function getById($id) {
        return $this->find($id)->current(); 
    }

}