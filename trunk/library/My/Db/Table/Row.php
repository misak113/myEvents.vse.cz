<?php
namespace My\Db\Table;

use Zend_Filter_Inflector;

/**
 * Trida reprezentujici obecny radek v databazi
 *
 */
class Row extends \Zend_Db_Table_Row_Abstract {
	
	
   /**
     * Inflektor pro ziskani nazvu atributu
     * camelCase -> under_score
     *
     * @param string $columnName
     * @return string
     */
    protected function _transformColumn($columnName) {
        $inflector  = new Zend_Filter_Inflector(":string");
        $inflector->setRules(array(
           ':string' => array('Word_CamelCaseToUnderscore', 'StringToLower'))
        );
        
        $columnName = $inflector->filter(array('string' => $columnName));
        return $columnName;     
    }
        
    /**
     * Prekryti magicke metody, aby odchytavala gettery a settery
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, array $args) {
        $matches = array();
    
        if (preg_match('/^get(\w+?)$/', $method, $matches)) {
            $attribute = $matches[1];
           
            return $this->{$attribute};
        }
    
        if (preg_match('/^set(\w+?)$/', $method, $matches)) {
            $attribute = $matches[1];
            
            $this->{$attribute} = (count($args) == 1)? $args[0] : null;
            return;
        }
        
        return parent::__call($method, $args);
    }
    
	
}