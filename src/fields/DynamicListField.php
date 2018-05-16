<?php

namespace Symbiote\DynamicLists;

use SilverStripe\Forms\DropdownField;

/**
 * A select field that takes its inputs from a data list
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class DynamicListField extends DropdownField
{

    protected $extraClasses = array('dropdown');

    public function __construct($name, $title = null, $source = null, $value = "", $form = null, $emptyString = null)
    {
        if (!$source) {
            $source = array();
        }

        if (is_string($source)) {
            // it should be the name of a list, lets get all its contents
            $dynamicList = DynamicList::get_dynamic_list($source);
            $source = array();
            if ($dynamicList) {
                $items = $dynamicList->Items();
                foreach ($items as $item) {
                    $source[$item->Title] = $item->Title;
                }
            }
        }

        $this->addExtraClass('dropdown');

        parent::__construct($name, $title, $source, $value, $form, $emptyString);
    }
}
