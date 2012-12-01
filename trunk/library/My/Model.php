<?php
/**
 * My_Model
 * implementuje singleton
 * slouzi jako centralni pristupove misto k modelovym a domenovym objektum
 */


class My_Model {
    protected static $_instance = null;
    protected $_services = array();
    
    
    private function __construct() {        
    }
    
    /**
     * Navrati instanci servisni tridy dle zadaneho nazvu
     *
     * @param string $name
     * @return My_Db_Table
     */
    public function getService($name) {
        if (!isset($this->_services[$name])) {
            $service = new $name();  
            if (!$service instanceof Zend_Db_Table_Abstract) {
                $type = gettype($service);
                if ($type == 'object') {
                    $type = get_class($service);
                }
                //require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception("Class must be a Zend_Db_Table_Abstract, but it is $type");
            }
            $this->_services[$name] = $service;
        }
        return $this->_services[$name];
    }
    
    
    /**
     * Vrati instanci modelu
     *
     * @return My_Model
     */
    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Navrati instanci servisni tridy dle zadaneho nazvu
     *
     * @param string $name
     * @return My_Db_Table
     */
     public static function get($name) {
         return self::getInstance()->getService($name);
     } 
    
}

