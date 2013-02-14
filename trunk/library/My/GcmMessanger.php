<?php

/**
 * Slouží k odesílání GCM zpráv na Android klienty
 *
 * @author Jakub Macoun
 */
class My_GcmMessanger {

    protected $gcmRegistrationTable;

    public function __construct(app\models\authentication\GcmRegistrationTable $gcmRegistrationTable) {
        $this->gcmRegistrationTable = $gcmRegistrationTable;
    }

    public function sendDataSyncMessage() {
        
    }

    public function sendEventsSyncMessage() {
        
    }

    public function sendMessage() {
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
        $message = "testMessage";

        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';

        $fields = array(
            'registration_ids' => $registrationIDs,
            'data' => array("message" => $message),
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
            $partExploded = explode(":", $part);
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

}

?>
