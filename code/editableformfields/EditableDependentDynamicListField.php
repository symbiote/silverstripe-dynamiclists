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
 * A dynamic list whose values are dependent on another list in the page.
 *
 * Relies on the DynamicList module for selecting which dynamic lists it is dependent
 * upon. 
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class EditableDependentDynamicListField extends EditableDropdown {
    static $singular_name = 'Dependent Dynamic List field';

	static $plural_name = 'Dependent Dynamic List fields';

	public function Icon() {
		return 'userforms/images/editabledropdown.png';
	}

	public function getHasAddableOptions() {
		return false;
	}

	function getFieldConfiguration() {
		$fields = parent::getFieldConfiguration();

		// eventually replace hard-coded "Fields"?
		$baseName = "Fields[$this->ID]";

		$listName = ($this->getSetting('SourceList')) ? $this->getSetting('SourceList') : '';

		// select another form field that has the titles of the lists to use for this list when displayed
		// The assumption being made here is that each entry in the source list has a corresponding dynamic list
		// defined for it, which we use later on. 
		$options = array();
		if ($this->Parent()) {
			$sourceList = $this->Parent()->Fields();
			if ($sourceList) {
				$options = $sourceList->map('Name', 'Title');
			}
		}
		
		$extraFields = new FieldList(
			new DropDownField($baseName . "[CustomSettings][SourceList]", _t('EditableDependentDynamicListField.SOURCE_LIST_TITLE', 'Source List'), $options, $listName)
		);

		$fields->merge($extraFields);
		return $fields;
	}

	function getFormField() {
		$sourceList = ($this->getSetting('SourceList')) ? $this->getSetting('SourceList') : null;
		// first off lets go and output all the options we need
		$fields = $this->Parent()->Fields();
		$source = null;
		foreach ($fields as $field) {
			if ($field->Name == $sourceList) {
				$source = $field;
				break;
			}
		}

		$optionLists = array();
		if ($source) {
			// all our potential lists come from the source list's dynamic list source, so we need to go load that
			// first, then iterate it and build all the additional required lists
			$sourceList = DynamicList::get_dynamic_list($source->getSetting('ListTitle'));
			if ($sourceList) {
				$items = $sourceList->Items();
				
				// now lets create a bunch of option fields
				foreach ($items as $sourceItem) {
					// now get the dynamic list that is represented by this one
					$list = DynamicList::get_dynamic_list($sourceItem->Title);
					if ($list) {
						$optionLists[$sourceItem->Title] = $sourceItem->Title;
					}
				}
			}

			if (count($optionLists)) {
				return new DependentDynamicListDropdownField($this->Name, $this->Title, $optionLists, $source->Name);
			}else{
				return new DropdownField($this->Name, $this->Title, array());
			}
		}


		// return a new list
		return new LiteralField($this->Name);
	}
}