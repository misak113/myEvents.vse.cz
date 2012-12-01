<?phpnamespace app\models\events;use My\Db\Table\Row;use Zend_Date;/** * Trida reprezentujici akci * */class Event extends Row{	public function updateFromArray(array $values) {				// Převedení data a časů do dvou datetime sloupců (začátek a konec)		$date = new Zend_Date($values["date"]);		$date = $date->toString('YYYY-MM-dd');		$values["timestart"] = $date . " " . $values["timestart"] . ":00";		$values["timeend"] =  $date . " " . $values["timeend"] . ":00";				$this->setFromArray($values);
		$this->save();
				// pokud jsou předáni sponzoři, nastavíme je
		// if (isset($values['sponsors'])) {
		//	$this->setFolders($values['sponsors']);
		// }
	
		return $this;
	}			public function setSponsors($sponsors) {
				$service = My_Model::getInstance()->getService('EventHasSponsorTable');		// vymazání (případně) nastavených sponzorů (pro editaci)        $service->delete(array('sponsor_id = ?' => $this->getId()));                // nastavíme nové sponzory        foreach ($sponsors as $sponsor) {            $service->insert(array(                'event_id' => $this->getId(),                'sponsor_id' => $sponsor,            ));        }                return $this;        
	}}