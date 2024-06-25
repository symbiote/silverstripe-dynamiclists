<?php

namespace Symbiote\DynamicLists;

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\DropdownField;
use \SilverStripe\UserForms\Model\EditableFormField\EditableDropdown;

/*

  Copyright (c) 2009, Symbiote
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
 * An editable field that can use a data list for its
 * fields
 *
 * @author Marcus Nyeholt <marcus@symbiote.com.au>
 */

if (!class_exists(EditableDropdown::class)) {
    return;
}

class EditableDynamicListField extends EditableDropdown
{
    private static $db = [
        'ListTitle' => 'Varchar(512)'
    ];

    private static $table_name = 'EditableDynamicListField';

    private static $singular_name = 'Dynamic List field';
    private static $plural_name = 'Dynamic List fields';

    public function Icon()
    {
        return 'userforms/images/editabledropdown.png';
    }

    public function getHasAddableOptions()
    {
        return false;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Options']);

        // get a list of data lists to select from
        $allLists = DataObject::get(DynamicList::class);

        $options = [
            'Please create a DynamicList!' => '(No DynamicLists available)'
        ];

        if ($allLists) {
            /* @var $allLists DataObjectSet */
            $options = $allLists->map('Title', 'Title');
        }

        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'ListTitle',
                _t('EditableDataListField.DYNAMICLIST_TITLE', 'List Title'),
                $options
            )
        );
        return $fields;
    }

    public function getFormField()
    {
        $field = DynamicListField::create($this->Name, $this->Title, $this->ListTitle)
            ->setFieldHolderTemplate(EditableFormField::class . '_holder')
            ->setTemplate(self::class);
        if ($this->UseEmptyString) {
            $field->setEmptyString($this->EmptyString ?: '');
        }
        $this->doUpdateFormField($field);
        return $field;
    }
}
