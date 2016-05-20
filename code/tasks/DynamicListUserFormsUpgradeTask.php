<?php

class DynamicListUserFormsUpgradeTask extends BuildTask {
	protected $title = "Dynamic List UserForms 3.0 Migration Tool";

	protected $description = "Upgrade tool for sites upgrading to userforms 3.0/4.0 from 2.x";

	public function run($request) {
		foreach (EditableDynamicListField::get() as $record)
		{
			if (!$record->getField('ListTitle'))
			{
				$record->setField('ListTitle', $record->getSetting('ListTitle'));
				if ($record->getChangedFields(true))
				{
					try
					{
						$record->write();
						DB::alteration_message('Modified #'.$record->ID . ' updated ListTitle', 'changed');
					}
					catch (Exception $e)
					{
						DB::alteration_message('Failed to write #'.$record->ID. ' -- '.$e->getMessage(), 'error');
					}
				}
				else
				{
					DB::alteration_message('No changes to #'.$record->ID);
				}
			}
			else
			{
				DB::alteration_message('ListTitle already set on #'.$record->ID. ' on parent #'.$record->ParentID);
			}
		}

		foreach (EditableDependentDynamicListField::get() as $record)
		{
			if (!$record->getField('SourceList'))
			{
				$record->setField('SourceList', $record->getSetting('SourceList'));
				if ($record->getChangedFields(true))
				{
					try
					{
						$record->write();
						DB::alteration_message('Modified #'.$record->ID . ' updated SourceList', 'changed');
					}
					catch (Exception $e)
					{
						DB::alteration_message('Failed to write #'.$record->ID. ' -- '.$e->getMessage(), 'error');
					}
				}
				else
				{
					DB::alteration_message('No changes to #'.$record->ID);
				}
			}
			else
			{
				DB::alteration_message('SourceList already set on #'.$record->ID . ' on parent #'.$record->ParentID);
			}
		}
	}

}