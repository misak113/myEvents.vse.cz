<?php

/**
 * Třída pro saltované hashované heslo
 *
 * @author Jakub Macoun
 */
class My_Password {

    public static $MIN_LENGTH = 5; // Minimální délka hesla (nesmí být menší jak 3)
    private $nonHashForm;
    private $finalHash;
    private $salt;

    public function __construct() {
        $parameters = func_get_args();

        if (func_num_args() == 0) { // Generate random
            $this->salt = strtolower($this->generateRandom(7));
            $this->nonHashForm = $this->generateRandom(self::$MIN_LENGTH + 2);
        } else { // Create from non-hash password
            $nonHashForm = $parameters[0];

            if (strlen($nonHashForm) < self::$MIN_LENGTH) {
                throw new Zend_Exception("Password is too short");
            }

            $this->salt = strToLower($this->generateRandom(7));
            $this->nonHashForm = $nonHashForm;
        }

        $this->createFinalHash();
    }
    
    
    
    /**
     * Extracts salt from final hash
     * @param finalHash Complete final hash
     * @return Salt in final hash
     */
    public static function extractSalt($dHash) {
        if (strLen($dHash) != 71) {
            return null;
        }
        
        return subStr($dHash, 29, 7);
    }
    
    /**
     * Extracts SHA-256 hash from final hash
     * @param finalHash Complete final hash
     * @return SHA-256 hash
     */
    public static function extractHash($dHash) {
        if (strLen($dHash) != 71) {
            return null;
        }
        
        $string = subStr($dHash, 0, 29);
        $string .= subStr($dHash, 36);
        
        return $string;
    }

    private function generateRandom($length) {
        $string = "";

        for ($i = 0; $i < $length; $i++) {
            if (mt_rand(0, 1) == 1) { // Písmeno
                if (mt_rand(0, 1) == 1) { // Malé
                    $charCode = mt_rand(0, 24) + 97;
                } else { // Velké
                    $charCode = mt_rand(0, 24) + 65;
                }

                $string .= chr($charCode);
            } else { // Číslo
                $string .= (string) mt_rand(0, 9);
            }
        }

        return $string;
    }

    private function createFinalHash() {
        $string = subStr($this->nonHashForm, 0, self::$MIN_LENGTH - 3);
        $string .= $this->salt;
        $string .= subStr($this->nonHashForm, self::$MIN_LENGTH - 2);

        $basicHash = hash("sha256", $string);
        $this->finalHash = hash("sha256", subStr($basicHash, 0, 9) . $this->salt . subStr($basicHash, 10));

    }
    
    public function getDHash() {
        if ($this->salt == null) {
            return null;
        } else {
            $string = subStr($this->finalHash, 0, 29);
            $string .= $this->salt;
            $string .= subStr($this->finalHash, 29);

            return $string;
        }
    }


    /**
     * @return the finalHash
     */
    public function getFinalHash() {
        return $this->finalHash;
    }

    /**
     * @return the salt
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * @param salt the salt to set
     */
    public function setSalt($salt) {
        if ($this->nonHashForm == null || strLen($salt) != 7) {
            return;
        }
        
        $this->salt = $salt;
        $this->createFinalHash();
    }
}

?>
