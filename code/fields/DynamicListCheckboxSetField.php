<?php
/*
 * A CheckboxSetField field that takes its inputs from a DynamicList
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class DynamicListCheckboxSetField extends CheckboxSetField {
    function __construct($name, $title = null, $source = null, $value = "", $form = null, $emptyString = null) {
		if (!$source) {
			$source = array();
		}

		if (is_string($source)){
			// it should be the name of a list, lets get all its contents
			$dynamicList = DataObject::get_one('DynamicList', '"Title" = \''.Convert::raw2sql($source).'\'');
			$source = array();
			if ($dynamicList) {
				$items = $dynamicList->Items();
				foreach ($items as $item) {
					$source[$item->Title] = $item->Title;
				}
			}
		}

		parent::__construct($name, $title, $source, $value, $form, $emptyString);
	}
}