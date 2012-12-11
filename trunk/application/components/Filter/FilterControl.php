<?php
namespace app\components\Filter;

use Zette\UI\BaseControl;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */
class FilterControl extends BaseControl
{

	/** @var array */
	protected $dates;
	/** @var array */
	protected $categories;
	/** @var array */
	protected $tags;
	/** @var array */
	protected $organizations;

	/**
	 * @param array $dates
	 * @return FilterControl
	 */
	public function setDates(array $dates) {
		$this->dates = $dates;
		return $this;
	}

	/**
	 * @param array $categories
	 * @return FilterControl
	 */
	public function setCategories(array $categories) {
		$this->categories = $categories;
		return $this;
	}

	/**
	 * @param array $tags
	 * @return FilterControl
	 */
	public function setTags(array $tags) {
		$this->tags = $tags;
		return $this;
	}
	
	/**
	 * @param array $organizations
	 * @return FilterControl
	 */
	public function setOrganizations(array $organizations) {
		$this->organizations = $organizations;
		return $this;
	}

	public function getFilter() {
		$filter = $this->getRequest()->getParam('filter');
		return $filter;
	}

	public function render() {
		$this->template->setFile(__DIR__.'/filter.latte');

		$filter = $this->getFilter();

		$this->template->dateFilterActivated = isset($filter['date']);
		$this->template->categoryFilterActivated = isset($filter['category']);
		$this->template->tagFilterActivated = isset($filter['tag']);
		$this->template->organizationFilterActivated = isset($filter['organization']);
		$this->template->filter = $filter;

		$this->template->dates = $this->dates;
		$this->template->categories = $this->categories;
		$this->template->tags = $this->tags;
		$this->template->organizations = $this->organizations;

		$this->template->render();
	}

}
