<?php
/**
 * A data list is a user specified list of data items that can be used
 * for a variety of areas in the site where a predefined list is used
 * using the DataListField form control.
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD License http://silverstripe.org/bsd-license
 */
class DataList extends DataObject {
    public static $db = array(
		'Title' => 'Varchar(128)',
	);

	public static $has_many = array(
		'Items' => 'DataListItem',
	);

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		if ($this->ID) {
			// now swap out to an orderable CTF field
			$fields->removeFieldFromTab('Root.Items', 'Items');

			foreach($this->has_many() as $relationship => $component) {
				$relationshipFields = singleton($component)->summaryFields();
				$foreignKey = $this->getRemoteJoinField($relationship);
				$ctf = new DataListOrderableComplexTableField(
					$this,
					$relationship,
					$component,
					$relationshipFields,
					"getCMSFields",
					"\"$foreignKey\" = " . $this->ID
				);
				$ctf->setPermissions(TableListField::permissions_for_object($component));
				$fields->addFieldToTab('Root.Items', $ctf);
			}
		}
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
	public static function get_data_list($title) {
		$dataList = DataObject::get_one('DataList', '"Title" = \''.Convert::raw2sql($title).'\'');
		return $dataList;
	}

	public function getItemByTitle($title) {
		$SQL_title = Convert::raw2sql($title);
		$item = DataObject::get_one('DataListItem', "\"ListID\" = $this->ID AND \"Title\" = '{$SQL_title}'");
		if (!$item || !$item->exists()) {
			// create item
			$item = new DataListItem();
			$item->ListID = $this->ID;
			$item->Title = $title;
			$item->write();
		}

		return $item;
	}
}
?>