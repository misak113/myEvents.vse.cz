<?php
namespace app\services;

/**
 * Slouží k odesílání GCM zpráv na Android klienty
 *
 * @author Jakub Macoun
 */
class GcmMessanger {

    protected $gcmRegistrationTable;
    
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
        $apiKey = "AIzaSyBH0LuBGLRP7r8tsJCBcYh1pN74rI9q6zU";

        $select = $this->gcmRegistrationTable->select();
        $registrations = $this->gcmRegistrationTable->fetchAll($select);
        
        $registrationIDs = array();
        $dbRegistratons = array();
        $i = 0;
        foreach ($registrations as $registration) {
            $registrationIDs[] = $registration->reg_id;
            $dbRegistratons[$i] = $registration->gcm_registration_id;
            
            $i++;
        }

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
        }

        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';

        $fields = array(
            'registration_ids' => $registrationIDs,
            'data' => array(
                "syncEvents" => $syncEvents,
                "syncData" => $syncData,
                "forced" => $forced
            ),
        );

        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);

        // Close connection
        curl_close($ch);

        // Parse result
        $replacedResult = preg_replace("/^.*\"results\":\[(.+)\].*$/i", "$1", $result);

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
            
            $partArray = array(
                "type" => $partExploded[0],
                "content" => $partExploded[1],
                "dbRegistrationId" => $dbRegistratons[$i]
            );
            $results[$i] = $partArray;
            
            $i++;
        }
print_r($results);
        // Work out results
        foreach ($results as $res) {
            if ($res["type"] == "error" && ($res["content"] == "NotRegistered" || $res["content"] == "InvalidRegistration")) {
                $this->gcmRegistrationTable->getById($res["dbRegistrationId"])->delete();
            }
        }
    }

    public function injectGcmRegistrationTable(GcmRegistrationTable $gcmRegistrationTable) {
        $this->gcmRegistrationTable = $gcmRegistrationTable;
    }
}

?>
