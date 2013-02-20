<?php

namespace app\models\events;

use My\Db\Table\Row;
use app\services\GcmMessanger;

class Category extends Row {

    protected $gcmMessanger;

    public function save() {
        parent::save();

        // GCM
        if ($this->changed) {
            $this->gcmMessanger->sendSyncDataMessage();
        }
    }

    public function delete() {
        parent::delete();

        // GCM
        if ($this->changed) {
            $this->gcmMessanger->sendSyncDataMessage();
        }
    }

    public function injectGcmMessanger(GcmMessanger $gcmMessanger) {
        $this->gcmMessanger = $gcmMessanger;
    }

}

?>