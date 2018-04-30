<?php

namespace sheadawson\DynamicLists;

use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\CsvBulkLoader;

class DynamicListCsvLoader extends CsvBulkLoader
{
    public function __construct($objectClass)
    {
        parent::__construct($objectClass);

        $this->relationCallbacks = array(
            'AgencyTitle' => array(
                'relationname' => 'Items',
                'callback' => 'getItemByTitle'
            ),
        );
    }

    protected function processRecord($record, $columnMap, &$results, $preview = false)
    {
        $class = $this->objectClass;
        
        $title = trim($record['Title']);
        $item = trim($record['ListItem']);

        $existingList = DynamicList::get_dynamic_list($title);
        if (!$existingList) {
            $existingList = new DynamicList;
            $existingList->Title = $title;
            $existingList->write();
        }

        // now add the item to that list
        $existingItem = DataObject::get_one(DynamicListItem::class, '"Title"=\''.Convert::raw2sql($item).'\' AND "ListID" = '.((int) $existingList->ID));
        if (!$existingItem) {
            $existingItem = new DynamicListItem;
            $existingItem->Title = $item;
            $existingItem->ListID = $existingList->ID;
            $existingItem->write();
        }
    }
}
