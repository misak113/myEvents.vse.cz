<?php
/**
 * 
 * view helper pro ziskani zakladniho url
 *
 */
class My_View_Helper_BaseUrl extends Zend_View_Helper_Abstract 
{
    protected $_baseUrl;
    
    public function __construct() 
    {
        $fc = Zend_Controller_Front::getInstance();
        $this->_baseUrl =  $fc->getBaseUrl();
    }
    
    public function baseUrl($value = null)
    {
        if ($value !== null) {
            $this->_baseUrl = $value;
        }
        return $this->_baseUrl;
    }
}