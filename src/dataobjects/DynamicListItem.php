<?php

namespace sheadawson\DynamicLists;

use SilverStripe\ORM\DB;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\DataObject;

/**
 * A dynamic list is a user specified list of data items that can be used
 * for a variety of areas in the site where a predefined list is used
 * using the DynamicListField form control.
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class DynamicListItem extends DataObject {
    private static $db = array(
		'Title' => 'Varchar(128)',
		'Sort' => 'Int',
	);

	private static $has_one = array(
		'List' => DynamicList::class
	);

	private static $summary_fields = array(
		'Title'
	);

	private static $default_sort = 'Sort, ID';
	

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('ListID');
		return $fields;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->Sort) {
			$parentID = ($this->ListID) ? $this->ListID : 0;
			$this->Sort = DB::query("SELECT MAX(\"Sort\") + 1 FROM \"DynamicListItem\" WHERE \"ListID\" = $parentID")->value();
		}
	}
	
	public function onAfterWrite() {
		if ($list = $this->List()) {
			if ($list->config()->cache_lists) {
				$list->cacheListData();
			}
		}
	}
	
	public function canView($member = null) {
		return true;
	}
	
	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canEdit($member = null) {
		return Permission::check('CMS_ACCESS_DynamicListAdmin', 'any', $member);
	}

	/**
	 * @param Member $member
	 * @return boolean
	 */
	public function canDelete($member = null) {
		return Permission::check('CMS_ACCESS_DynamicListAdmin', 'any', $member);
	}

	/**
	 * @todo Should canCreate be a static method?
	 *
	 * @param Member $member
	 * @return boolean
	 */
	public function canCreate($member = null, $context = array()) {
		return Permission::check('CMS_ACCESS_DynamicListAdmin', 'any', $member);
	}
}