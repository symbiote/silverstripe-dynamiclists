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
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class DependentDynamicListDropdownField extends DynamicListField {
    /**
	 * The lists that should be used to populate the dynamic list
	 *
	 * @var array
	 */
	protected $dependentLists;

	/**
	 * The Name of the other form control that we're dependent upon
	 *
	 * @var String
	 */
	protected $dependentOn;

  protected $extraClasses = array('dropdown');

	public function  __construct($name, $title = null, $dynamicLists, $dependentOn = '', $value = "", $form = null, $emptyString = null) {
		$this->dependentLists = $dynamicLists;
		$this->dependentOn = $dependentOn;

		parent::__construct($name, $title, array(), $value, $form, $emptyString);
	}


	public function Field($properties = array()) {

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(DYNAMICLIST_MODULE . '/javascript/DependentDynamicListDropdownField.js');

		$listItems = array();

		if (is_string($this->dependentLists)) {
			$list = DynamicList::get_dynamic_list($this->dependentLists);
			if ($list) {

				$this->dependentLists = $list->Items()->map('Title', 'Title')->toArray();
			}
		}

		if(!is_array($this->dependentLists)){
			$this->dependentLists = array();
		}

		foreach ($this->dependentLists as $k => $v) {
			$list = DynamicList::get_dynamic_list($k);
			if ($list) {
				$listItems[$k] = $list->Items()->map('Title', 'Title')->toArray();
			}
		}
		$this->setAttribute('data-listoptions', Convert::raw2json($listItems));
		$this->setAttribute('data-dependentOn', $this->dependentOn);

		if($this->value){
			$this->setAttribute('data-initialvalue', $this->value);
		}

		return parent::Field();
	}

	/**
	 * Override method for validation to use dynamic list based off the
	 * parent's value. Overridden due to null source.
	 * @param type $validator 
	 * @return bool
	 */
	public function validate($validator) {
		// Source isn't pulled in correctly and we're going to rectify this
		// later on, so this can be an empty array for now.
		$source = array();
		$disabled = $this->getDisabledItems();

		// Grab the parent list we're trying to validate against first so we can refer to it.
		$parentListName = $this->getForm()->Fields()->fieldByName($this->dependentOn)->value;

		// Use the items from the Dynamic list as the "source" for validation purposes
		$parentList = DynamicList::get_dynamic_list($parentListName);
		if($parentList) {
			$source = $parentList->Items()->map('Title', 'Title')->toArray();
		}

		// Carry on as normal validating against our new source!
		// Since there's no data if the list doesn't exist, then of course it will fail
		if (!array_key_exists($this->value, $source) || in_array($this->value, $disabled)) {
			if ($this->getHasEmptyDefault() && !$this->value) {
				return true;
			}
			$validator->validationError(
				$this->name,
				_t(
					'DropdownField.SOURCE_VALIDATION',
					"Please select a value within the list provided. {value} is not a valid option",
					array('value' => $this->value)
				),
				"validation"
			);
			return false;
		}
		return true;
	}

	/**
	 * Returns a readonly version of this field
	 */
	function performReadonlyTransformation() {
		$field = new ReadonlyField($this->name, $this->title, $this->value);
		$field->addExtraClass($this->extraClass());
		$field->setForm($this->form);
		return $field;
	}
}
