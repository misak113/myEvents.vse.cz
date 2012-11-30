<?php
namespace app\components\Filter;

use app\models\events\CategoryTable;
use app\models\organizations\OrganizationTable;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */
class FilterDispatcher
{

	/** @var FilterControl */
	protected $filter;

	/** @var \app\models\events\CategoryTable @inject */
	protected $categoryTable;
	/** @var \app\models\organizations\OrganizationTable @inject */
	protected $organizationTable;

	protected $testDates = array(
		array(
			'id' => 1,
			'title' => '1. týden (24.9.-28.9.)',
		),
		array(
			'id' => 1,
			'title' => '2. týden (1.10.-5.10.)',
		),
		array(
			'id' => 1,
			'title' => '3. týden (8.10.-12.10.)',
		),
		array(
			'id' => 1,
			'title' => '4. týden (15.10.-19.10.)',
		),
	);


	/**
	 * Vrátí filter pro vykreslení (pokud neexistuje vytvoří, popřípadě cachuje)
	 * @return FilterControl
	 */
	public function createComponentFilter() {
		if ($this->filter === null) {
			$this->filter = $this->createFilter();
		}

		return $this->filter;
	}

	/**
	 * Vytvoří filter
	 * @return FilterControl
	 */
	protected function createFilter() {
		$categories = $this->categoryTable->getCategories();
		$organizations = $this->organizationTable->getOrganizations();

		$filter = new FilterControl();
		$filter->setDates($this->testDates);
		$filter->setCategories($categories);
		$filter->setOrganizations($organizations);

		return $filter;
	}





	/******************** Dependency Injection **********************/
	public function injectCategoryTable(CategoryTable $categoryTable) {
		$this->categoryTable = $categoryTable;
	}
	public function injectOrganizationTable(OrganizationTable $organizationTable) {
		$this->organizationTable = $organizationTable;
	}

}
