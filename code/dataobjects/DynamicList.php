<?php
/**
 * A data list is a user specified list of data items that can be used
 * for a variety of areas in the site where a predefined list is used
 * using the DynamicListField form control.
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license
 */
class DynamicList extends DataObject {
    public static $db = array(
		'Title' => 'Varchar(128)',
	);

	public static $has_many = array(
		'Items' => 'DynamicListItem',
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$conf=GridFieldConfig_RelationEditor::create(10);
		$conf->addComponent(new GridFieldSortableRows('Sort'));	
		$fields->addFieldToTab('Root.Items', new GridField('Items', 'Dynamic List Items', $this->Items(), $conf));
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

	/**
	 * Convenience method for getting a data list
	 *
	 * @param String $title
	 * @return DataObject
	 */
	public static function get_dynamic_list($title) {
		$list = DataObject::get_one('DynamicList', '"Title" = \''.Convert::raw2sql($title).'\'');
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
}