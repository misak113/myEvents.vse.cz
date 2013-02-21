<?php
namespace My\Db;

use Zette\Database\Table as ZetteTable;

/**
 * Trida reprezentujici obecny tabulku v databazi
 *
 */
class Table extends ZetteTable {

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