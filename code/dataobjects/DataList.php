<?php
/*

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * A data list is a user specified list of data items that can be used
 * for a variety of areas in the site where a predefined list is used
 * using the DataListField form control.
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
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
		$item = DataObject::get_one('DataListItem', "\"Title\" = '{$SQL_title}'");
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