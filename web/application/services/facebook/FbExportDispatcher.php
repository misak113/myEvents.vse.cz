<?php

namespace app\services\facebook;

use app\models\organizations\Organization;
use app\services\facebook\Facebook;
use app\models\organizations\OrganizationTable;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.12.12
 * Time: 12:49
 * To change this template use File | Settings | File Templates.
 */
class FbExportDispatcher
{
	const SOURCE_TYPE_FACEBOOK = 'facebook';

	/** @var Facebook @inject */
	protected $facebook;
	/** @var \app\models\organizations\OrganizationTable @inject */
	protected $organizationTable;


	/**
	 * @param \app\models\organizations\Organization|int $organization
	 * @throws \Nette\InvalidArgumentException
	 */
	public function exportEventsToOrganization($organization) {
		if (is_int($organization)) {
			$organization = $this->organizationTable->getById($organization);
		}
		if (! $organization instanceof Organization) {
			throw new \Nette\InvalidArgumentException('Organizace musí být organization_id nebo Organization objekt. Zadal jste "'.get_class($organization).'".');
		}

		$fbEvents = $this->getFbEvents($organization);
		$events = $this->getEvents($organization);

		$fbEvents2 = array();
		foreach ($fbEvents as $event) {
			$fbEvents2[$event['id']] = $event;
		}
		$events2 = array();
		foreach ($events as $event) {
			$events2[$event['external_id']] = $event;
		}
		foreach ($fbEvents2 as $fbId => $event) {
			if (isset($events2[$fbId]) && $events2[$fbId]['source_type'] == self::SOURCE_TYPE_FACEBOOK) {
				unset($events2[$fbId]);
			}
		}
		$exportEvents = $events2;
		_dBar($exportEvents);

		foreach ($exportEvents as $event) {
			$this->facebook->api("/$organizationFbId/events");
		}

		die();

	}

	/**
	 * @param \app\models\organizations\Organization $organization
	 * @return bool
	 */
	public function getFbEvents(Organization $organization) {
		$fbId = $organization->getFacebookId();

		$since = date('U');
		// get events
		$url = '/'.$fbId.'/events?since='.$since;
		$result = $this->facebook->api($url);
		if (!isset($result['data'])) {
			return false;
		}

		$events = $result['data'];

		return $events;
	}

	/**
	 * @param \app\models\organizations\Organization $organization
	 * @return array
	 */
	public function getEvents(Organization $organization) {
		$events = $organization->getEvents();
		return $events->toArray();
	}





	public function injectFacebook(Facebook $facebook) {
		$this->facebook = $facebook;
		$this->facebook->login();
	}
	public function injectOrganizationTable(OrganizationTable $organizationTable) {
		$this->organizationTable = $organizationTable;
	}
}
