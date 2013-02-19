<?php

namespace app\models\events;

use My\Db\Table\Row;
use app\services\GcmMessanger;

class Category extends Row {

    protected $gcmMessanger;
    
    public function save() {
        parent::save();

        // GCM
        $this->gcmMessanger->sendSyncEventsMessage();
    }

    public function delete() {
        parent::delete();

        // GCM
        $this->gcmMessanger->sendSyncEventsMessage();
    }

    public function injectGcmMessanger(GcmMessanger $gcmMessanger) {
        $this->gcmMessanger = $gcmMessanger;
    }

}

?>