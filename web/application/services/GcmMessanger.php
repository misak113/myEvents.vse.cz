<?php

namespace app\services;

use app\models\authentication\GcmRegistrationTable;

/**
 * Slouží k odesílání GCM zpráv na Android klienty
 *
 * @author Jakub Macoun
 */
class GcmMessanger {

	/** @var GcmRegistrationTable */
    protected $gcmRegistrationTable;

    const API_KEY = "AIzaSyBH0LuBGLRP7r8tsJCBcYh1pN74rI9q6zU";
    const MSG_TYPE_SYNC_EVENTS = 1;
    const MSG_TYPE_SYNC_DATA = 2;
    const MSG_TYPE_SYNC_ALL = 3;

    public function sendSyncEventsMessage($forced = false) {
        $this->sendMessage(self::MSG_TYPE_SYNC_EVENTS, $forced);
    }

    public function sendSyncDataMessage($forced = false) {
        $this->sendMessage(self::MSG_TYPE_SYNC_DATA, $forced);
    }

    public function sendSyncAllMessage($forced = false) {
        $this->sendMessage(self::MSG_TYPE_SYNC_ALL, $forced);
    }

    protected function sendMessage($type, $forced) {
        // Message to be sent
        switch ($type) {
            case self::MSG_TYPE_SYNC_EVENTS:
                $syncEvents = true;
                $syncData = false;
                break;
            case self::MSG_TYPE_SYNC_DATA:
                $syncEvents = false;
                $syncData = true;
                break;
            case self::MSG_TYPE_SYNC_ALL:
                $syncEvents = true;
                $syncData = true;
                break;
			default:
				$syncEvents = false;
				$syncData = false;
		}

        // Registrations
        $select = $this->gcmRegistrationTable->select();
        $registrations = $this->gcmRegistrationTable->fetchAll($select);

        // Create message
        $message = new \Zend_Mobile_Push_Message_Gcm();

        foreach ($registrations as $registration) {
            $message->addToken($registration->reg_id);
        }

        $message->setData(array(
            "syncEvents" => $syncEvents,
            "syncData" => $syncData,
            "forced" => $forced
        ));

        // Send
        $gcm = new \Zend_Mobile_Push_Gcm();
        $gcm->setApiKey(self::API_KEY);

        $response = false;
        try {
            $response = $gcm->send($message);
        } catch (\Zend_Mobile_Push_Exception $e) {
            return;
        }

        foreach ($response->getResults() as $regId => $value) {
            if (isset($value["error"]) && ($value["error"] == "NotRegistered" || $value["error"] == "InvalidRegistration")) {
                $this->gcmRegistrationTable->getByRegId($regId)->delete();
            }
        }
    }

    public function injectGcmRegistrationTable(GcmRegistrationTable $gcmRegistrationTable) {
        $this->gcmRegistrationTable = $gcmRegistrationTable;
    }

}

?>
