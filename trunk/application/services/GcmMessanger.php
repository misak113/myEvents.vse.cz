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

    private function sendMessage($type, $forced) {
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
            // all other exceptions only require action to be sent or implementation of exponential backoff.
            die($e->getMessage());
        }

        foreach ($response->getResults() as $k => $v) {
            if ($v['registration_id']) {
                printf("%s has a new registration id of: %s\r\n", $k, $v['registration_id']);
            }
            if ($v['error']) {
                printf("%s had an error of: %s\r\n", $k, $v['error']);
            }
            if ($v['message_id']) {
                printf("%s was successfully sent the message, message id is: %s", $k, $v['message_id']);
            }
        }

        /*
          $apiKey = "AIzaSyBH0LuBGLRP7r8tsJCBcYh1pN74rI9q6zU";

          $select = $this->gcmRegistrationTable->select();
          $registrations = $this->gcmRegistrationTable->fetchAll($select);

          $registrationIDs = array();
          $dbRegistratons = array();
          $i = 0;
          foreach ($registrations as $registration) {
          $registrationIDs[] = $registration->reg_id;
          $dbRegistratons[$i] = $registration->gcm_registration_id;

<<<<<<< .mine
          $i++;
          }
=======
        $replacedResultExploded = explode(",", $replacedResult);
        
        $results = array();
        $i = 0;
        foreach ($replacedResultExploded as $part) {
            $part = strtr($part, array(
                "{" => "",
                "}" => "",
                "\"" => ""
            ));
            $partExploded = explode(":", $part, 2);
            
            // Canoical ID created, skip
            if ($partExploded[0] == "registration_id") {
                continue;
            }

			if (!isset($dbRegistratons[$i])) {
				// Log and continue
				continue;
			}
            
            $partArray = array(
                "type" => $partExploded[0],
                "content" => isset($partExploded[1]) ?$partExploded[1] :'',
                "dbRegistrationId" => $dbRegistratons[$i]
            );
            $results[$i] = $partArray;
            
            $i++;
        } */
    }

    public function injectGcmRegistrationTable(GcmRegistrationTable $gcmRegistrationTable) {
        $this->gcmRegistrationTable = $gcmRegistrationTable;
    }

}

?>
