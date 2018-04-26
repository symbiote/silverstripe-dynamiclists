<?php

namespace sheadawson\DynamicLists;

use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * A data list is a user specified list of data items that can be used
 * for a variety of areas in the site where a predefined list is used
 * using the DynamicListField form control.
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license
 */
class DynamicList extends DataObject {
    private static $db = array(
		'Title' => 'Varchar(128)',
		'CachedItems'	=> 'Text',
	);

	private static $has_many = array(
		'Items' => DynamicListItem::class,
	);
	
	/**
	 * Should list items be cached?
	 *
	 * @var boolean
	 */
	private static $cache_lists = false;

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->removeByName('CachedItems');
		
		if ($this->ID) {
			$orderableComponent = new GridFieldOrderableRows('Sort');
			$conf=GridFieldConfig_RelationEditor::create(20);
			$conf->addComponent($orderableComponent);
			$fields->addFieldToTab('Root.Items', new GridField('Items', 'Dynamic List Items', $this->Items(), $conf));
		}

		// Allow extension.
		
		$this->extend('updateDynamicListCMSFields', $fields);
		return $fields;
	}

	public function onBeforeDelete() {
		parent::onBeforeDelete();
		// delete all items that were attached
		$items = $this->Items();
		foreach ($items as $item) {
			$item->delete();
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

	/**
	 * Convenience method for getting a data list
	 *
	 * @param String $title
	 * @return DataObject
	 */
	public static function get_dynamic_list($title) {
		$list = DynamicList::get()->filter('Title', $title)->first();
		return $list;
	}

	public function getItemByTitle($title) {
		$SQL_title = Convert::raw2sql($title);
		$item = DataObject::get_one('DynamicListItem', "\"ListID\" = $this->ID AND \"Title\" = '{$SQL_title}'");
		if (!$item || !$item->exists()) {
			// create item
			$item = new DynamicListItem();
			$item->ListID = $this->ID;
			$item->Title = $title;
			$item->write();
		}

		return $item;
	}

	public function cacheListData() {
		$items = $this->Items();
		if ($items) {
			$mapped = array();
			foreach ($items as $i) {
				$mapped[$i->ID] = $i->Title;
			}
		}
	}
	
	/**
	 * Get a map of ID => Title for the contained items in this list
	 */
	public function itemArray() {
		if ($this->config()->cache_lists) {
			$str = $this->CachedItems;
			if (strlen($str) && $items = @unserialize($str)) {
				return $items;
			}
		}

		$mapped = $this->Items()->map()->toArray();
		return $mapped;
	}
}
