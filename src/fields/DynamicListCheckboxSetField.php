<?php

namespace Symbiote\DynamicLists;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\CheckboxSetField;

/*
 * A CheckboxSetField field that takes its inputs from a DynamicList
 * @author Shea Dawson <shea@symbiote.com.au>
 */
class DynamicListCheckboxSetField extends CheckboxSetField
{
    public function __construct($name, $title = null, $source = null, $value = "", $form = null, $emptyString = null)
    {
        if (!$source) {
            $source = [];
        }

        if (is_string($source)) {
            // it should be the name of a list, lets get all its contents
            $dynamicList = DynamicList::get_dynamic_list($source);
            $source = [];
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
