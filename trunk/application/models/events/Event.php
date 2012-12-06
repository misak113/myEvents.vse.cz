<?phpnamespace app\models\events;use My\Db\Table\Row,        \app\models\organizations;use Zend_Date;/** * Trida reprezentujici akci * */class Event extends Row{           /**     *      * @return Zend_Db_Table_Rowset_Abstract     */    public function getSponsors() {        return $this->findManyToManyRowset(new SponsorTable, new EventHasSponsorTable);    }        /**     *      * @return Zend_Db_Table_Rowset_Abstract     */    public function getOrganizations() {        return $this->findManyToManyRowset(new organizations\OrganizationTable, new organizations\OrganizationOwnEventTable);    }    public function getTags() {
    	return $this->findManyToManyRowset(new TagTable, new EventHasTagTable);
    }        public function updateFromArray(array $values) {            // Převedení data a časů do dvou datetime sloupců (začátek a konec)            $date = new Zend_Date($values["date"]);            $date = $date->toString('YYYY-MM-dd');            $values["timestart"] = $date . " " . $values["timestart"] . ":00";            $values["timeend"] =  $date . " " . $values["timeend"] . ":00";            $values["category_id"] = $values["category"];            $this->setFromArray($values);            $this->save();                        // Nastavíme nové vlastního akce            $this->setOwner($values["organization_id"]);            // Nastavíme tagy akce            $this->setTags($values["tags1"]);                                    // pokud jsou předáni sponzoři, nastavíme je            // if (isset($values['sponsors'])) {            //	$this->setFolders($values['sponsors']);            // }            return $this;    }    public function setTags($tags) {
    	$service = new EventHasTagTable;
    
    	// smažeme existující tagy
    	$service->delete(array('event_id = ?' => $this->event_id));
    
    	// nastavime nové
    	foreach ($tags as $tag) {	    		    		$service->insert(array(
    				'event_id' => $this->event_id,
    				'tag_id' => $tag,
    		));    		
    	}    	
    	return $this;
    }            public function toArray($options = array()) {
    	$values = parent::toArray();
    
    	if (isset($options['tags'])) {    		$values['tags'] = array();
    		foreach ($this->getTags() as $tag) {
    			$values['tags1'][] = $tag->tag_id;
    		}
    	}
    
    	return $values;
    }        public function setOwner($ownerid) {
    	$service = new organizations\OrganizationOwnEventTable;
    
    	// smažeme existujícího vlastníka
    	$service->delete(array('event_id = ?' => $this->event_id));
    
    	// nastavime nového
    	$service->insert(array(
    				'event_id' => $this->event_id,
    				'organization_id' => $ownerid,
    		));
    	
    	return $this;
    }                public function setSponsors($sponsors) {		        $service = My_Model::getInstance()->getService('EventHasSponsorTable');        // vymazání (případně) nastavených sponzorů (pro editaci)        $service->delete(array('sponsor_id = ?' => $this->getId()));                // nastavíme nové sponzory        foreach ($sponsors as $sponsor) {            $service->insert(array(                'event_id' => $this->getId(),                'sponsor_id' => $sponsor,            ));        }                return $this;            }}