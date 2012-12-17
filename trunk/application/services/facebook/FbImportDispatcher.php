<?php

namespace app\services\facebook;

use app\models\organizations\OrganizationTable;
use app\models\organizations\Organization;
use Nette\Utils\Strings;
use app\models\events\EventTable;
use app\models\organizations\OrganizationOwnEventTable;
use Zette\Diagnostics\TimerPanel;
use app\services\facebook\Facebook;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 9.12.12
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */
class FbImportDispatcher
{

	const SOURCE_TYPE_FACEBOOK = 'facebook';
	const DEFAULT_CATEGORY_ID = 1;

	/** @var Facebook @inject */
	protected $facebook;
	/** @var \app\models\organizations\OrganizationTable */
	protected $organizationTable;
	/** @var EventTable @inject */
	protected $eventTable;
	/** @var \app\models\organizations\OrganizationOwnEventTable @inject */
	protected $organizationOwnEventTable;

	/** @var Organization */
	protected $organization;

	/**
	 * @param int|\app\models\organizations\Organization $organization
	 */
	public function importEventsByOrganization($organization) {
		if (is_int($organization)) {
			$organization = $this->organizationTable->getById($organization);
		}
		if (! $organization instanceof Organization) {
			throw new \Nette\InvalidArgumentException('Organizace musí být organization_id nebo Organization objekt. Zadal jste "'.get_class($organization).'".');
		}
		$this->organization = $organization;
		$fbId = $organization->getFacebookId();

		$since = date('U');
		// get events
		$url = '/'.$fbId.'/events?since='.$since;
		$result = $this->facebook->api($url);
		if (!isset($result['data'])) {
			return false;
		}

		$events = $result['data'];
		$eventsData = array();
		$startTime = date('U');
		foreach ($events as $event) {
			$event = $this->importEvent($event);
			if ($event) $eventsData[] = $event;
			if (date('U') > $startTime+20) break; // Už to je moc času, 20s je limit přidávání
		}

		return $eventsData;
	}

	/**
	 * Vložíz FB akci do DB
	 * @param $event
	 * @return bool|\Zend_Db_Table_Row_Abstract	 */
	protected function importEvent($event) {
		// get event info
		TimerPanel::start('fb event get');
		$result = $this->facebook->api('/'.$event['id']);
		TimerPanel::stop('fb event get');
		TimerPanel::start('fb event insert');
		if (!isset($result['name'])) {
			return false;
		}
		if ($this->eventTable->fetchAll(array('external_id = ?' => $result['id'], 'source_type = ?' => self::SOURCE_TYPE_FACEBOOK))->count() > 0) {
			return false;
		}

		$start = isset($result['start_time']) ?\DateTime::createFromFormat('Y-m-d\TH:i:sO', $result['start_time']) :null;
		$end = isset($result['end_time']) ?\DateTime::createFromFormat('Y-m-d\TH:i:sO', $result['end_time']) :null;

		$data = array(
			'name' => Strings::substring($result['name'], 0, 44),
			'location' => isset($result['location']) ?Strings::substring($result['location'], 0, 44) :null,
			'timestart' => $start ?$start->format('Y-m-d H:i:s') :null,
			'timeend' => $end ?$end->format('Y-m-d H:i:s') :null, // @todo prý je povinný
			'shortinfo' => isset($result['description']) ?Strings::substring($result['description'], 0, 200) :null,
			'longinfo' => isset($result['description']) ?$result['description'] :null,
			'active' => 1,
			'public' => 1, // @todo až po odsouhlasení
			'url' => null, // @todo naparsovat z infa
			'fburl' => $this->createFbEventUrl($result['id']),
			'category_id' => self::DEFAULT_CATEGORY_ID, // @todo jakou budfe mít kategorii
			'capacity' => null, // @todo není kapacita
			'external_id' => $result['id'],
			'source_type' => self::SOURCE_TYPE_FACEBOOK,
		);

		$event = $this->eventTable->createRow($data);
		$event->save();

		$this->organizationOwnEventTable->createRow(array(
			'event_id' => $event->getEventId(),
			'organization_id' => $this->organization->getOrganizationId(),
		))->save();

		TimerPanel::stop('fb event insert');
		return $event;
	}

	/**
	 * Vytvoří URL fb akce
	 * @param $id
	 * @return string
	 */
	protected function createFbEventUrl($id) {
		return 'http://www.facebook.com/events/'.$id;
	}


	public function injectFacebook(Facebook $facebook) {
		$this->facebook = $facebook;
		$this->facebook->login();
	}
	public function injectOrganizationTable(OrganizationTable $organizationTable) {
		$this->organizationTable = $organizationTable;
	}
	public function injectEventTable(EventTable $eventTable) {
		$this->eventTable = $eventTable;
	}
	public function injectOrganizationOwnEventTable(OrganizationOwnEventTable $organizationOwnEventTable) {
		$this->organizationOwnEventTable = $organizationOwnEventTable;
	}
}
