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
class DependentDataListDropdownField extends DataListField {
    /**
	 * The lists that should be used to populate the data list
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


	public function  __construct($name, $title = null, $dataLists, $dependentOn = '', $value = "", $form = null, $emptyString = null) {
		$this->dependentLists = $dataLists;
		$this->dependentOn = $dependentOn;

		parent::__construct($name, $title, array(), $value, $form, $emptyString);
	}

	public function Field() {

		$dependScript = '';
		// lets find out if we've got an existing selection in our dependon list
		if ($this->form) {
			$dependent = $this->form->Fields()->dataFieldByName($this->dependentOn);
			if ($dependent && $dependent->Value()) {
				$dependScript = "showList('".Convert::raw2js($dependent->Value())."', '".Convert::raw2js($this->value)."');";
			}
		}

		$dependentName = $this->dependentOn;
		
		if (strpos($dependentName, '.')) {
			$dependentName = substr($dependentName, strrpos($dependentName, '.') + 1);
		}

		$listItems = array();
		if (is_string($this->dependentLists)) {
			$list = DataList::get_data_list($this->dependentLists);
			if ($list) {
				$this->dependentLists = $list->Items()->map('Title', 'Title');
			}
		}

		foreach ($this->dependentLists as $listTitle) {
			$list = DataList::get_data_list($listTitle);
			if ($list) {
				$listItems[$listTitle] = $list->Items()->map('Title', 'Title');
			}
		}

		$jsonStruct = Convert::raw2json($listItems);
		$jscript = <<<JSCRIPT
(function ($) {
	$().ready(function () {
		var listOptions = $jsonStruct;
		var me = $('select[name=$this->name]');

		/**
		 * Shows the specified list when needed
		 */
		var showList = function(name, value) {
			// need to create all the options
			if (listOptions[name]) {
				for (var k in listOptions[name]) {
					var sel = '';
					if (k == value) {
						sel = ' selected="selected"';
					}
					me.append('<option val="' + k + '"' + sel + '>' + k + '</option>');
				}
			}
		}

		$('select[name=$dependentName]').change(function () {
			// when this list changes, make sure to update the contained list items
			var _this = $(this);
			me.empty();
			if (_this.val()) {
				showList(_this.val());
			}
		});

		$dependScript
	});
})(jQuery);
JSCRIPT;

		Requirements::customScript($jscript, $this->name.'dropdown');

		return parent::Field();
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
?>