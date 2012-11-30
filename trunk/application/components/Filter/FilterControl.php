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
	 * @param array $organizations
	 * @return FilterControl
	 */
	public function setOrganizations(array $organizations) {
		$this->organizations = $organizations;
		return $this;
	}


	public function render() {
		$this->template->setFile(__DIR__.'/filter.latte');

		$this->template->dates = $this->dates;
		$this->template->categories = $this->categories;
		$this->template->organizations = $this->organizations;

		$this->template->render();
	}

}
