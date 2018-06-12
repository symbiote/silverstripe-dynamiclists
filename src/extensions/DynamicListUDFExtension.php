<?php

namespace Symbiote\DynamicLists;

use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\UserDefinedForm;

/**
 *  This extension is to help identify dynamic lists a little better.
 *  @author Nathan Glasl <nathan@symbiote.com.au>
 */

class DynamicListUDFExtension extends DataExtension
{

    private static $default_sort = array(
        'Title'
    );

    public function updateDynamicListCMSFields($fields)
    {

        // Make sure the draft records are being looked at.

        $stage = Versioned::get_stage();
        Versioned::set_stage('Stage');
        $used = EditableFormField::get()->filter(array(
            'ClassName:PartialMatch' => DynamicList::class
        ));

        // Determine whether this dynamic list is being used anywhere.

        $found = array();
        foreach ($used as $field) {
            // This information is stored using a serialised list, therefore we need to iterate through.

            if ($field->ListTitle === $this->owner->Title) {
                // Make sure there are no duplicates recorded.

                if (!isset($found[$field->ParentID]) && ($form = UserDefinedForm::get()->byID($field->ParentID))) {
                    $found[$field->ParentID] = "<a href='{$form->CMSEditLink()}'>{$form->Title}</a>";
                }
            }
        }

        // Display whether there were any dynamic lists found on user defined forms.

        if (count($found)) {
            $fields->removeByName('UsedOnHeader');
            $fields->addFieldToTab('Root.Main', HeaderField::create('UsedOnHeader', 'Used On', 5));
        }
        $display = count($found) ? implode('<br>', $found) : 'This dynamic list is <strong>not</strong> used.';
        $fields->removeByName('UsedOn');
        $fields->addFieldToTab('Root.Main', LiteralField::create('UsedOn', '<div>' . $display . '</div>'));
        Versioned::set_stage($stage);
    }
}
