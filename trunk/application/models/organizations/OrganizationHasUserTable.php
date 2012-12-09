<?php

namespace app\models\organizations;

use My\Db\Table;

/**
 * Trida reprezentujici vazbu mezi organizacemi a uzivateli
 *
 */
class OrganizationHasUserTable extends Table {

	/**
     * Nazev databazove tabulky
     *
     * @var string
     */
    protected $_name = 'organization_has_user';
    	
    
    /**
     * Reference
     * 
     * @var array
     */
    protected $_referenceMap = array (  
        'Organization' => array(
           'columns' => array ('organization_id'), 
           'refTableClass' => 'app\models\organizations\OrganizationTable', 
           'refColumns' => array ('organization_id')
        ), 
        'User' => array(
           'columns' => array ('user_id'), 
           'refTableClass' => 'app\models\authentication\UserTable', 
           'refColumns' => array ('user_id')
        ), 
    );
    
    public function getAdmins() {
        return $this->fetchAll();
    }
    
    public function saveAdmins($values) {
        if (count($values) > 0) {
            
            $tagsArray = array();
            
            foreach ($values as $key => $on) {
                $tag = explode('_', $key);
                $userId = $tag[0];
                $orgId = $tag[1];
                
                $tagsArray[] = $key;
                
                $where = $this->select()
                    ->where('user_id = ?',$userId)
                    ->where('organization_id = ?', $orgId);
                
                $existRow = $this->fetchRow($where);
                
                if (!$existRow){
                    $row = $this->insert(array(
                        'user_id' => $userId,
                        'organization_id' => $orgId,
                        'member' => 1
                    ));
                }
            }
            
            $members = $this->fetchAll();
            
            foreach ($members as $member) {
    
                $tag = $member->user_id . "_" . $member->organization_id;
                
                if (!in_array($tag, $tagsArray)) {
                    $member->delete();
                }
            }
            
            
        }
        else
            return false;
    }

}
	
