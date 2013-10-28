<?php
/**
 * Moves the old DataList and DataListItem records to DynamList/DynamicListItem
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class DataListUpdgradeTask extends BuildTask {
	
	public function run($request){
		global $databaseConfig;
		$database = $databaseConfig['database'];

		DB::query("DROP TABLE `DynamicList`");
		DB::query("DROP TABLE `DynamicListItem`");
		DB::query("RENAME TABLE  `$database`.`DataList` TO  `$database`.`DynamicList`");
		DB::query("RENAME TABLE  `$database`.`DataListItem` TO  `$database`.`DynamicListItem`");
		DB::query("ALTER TABLE DynamicListItem CHANGE ClassName ClassName enum('DynamicListItem')");
		DB::query("ALTER TABLE DynamicList CHANGE ClassName ClassName enum('DynamicList')");
		DB::query("UPDATE DynamicListItem SET ClassName = 'DynamicListItem'");
		DB::query("UPDATE DynamicList SET ClassName = 'DynamicList'");

		if(class_exists('EditableFormField')){
			DB::query("UPDATE EditableFormField SET ClassName = 'EditableDynamicListField' WHERE ClassName = 'EditableDataListField'");
			DB::query("UPDATE EditableFormField_Live SET ClassName = 'EditableDynamicListField' WHERE ClassName = 'EditableDataListField'");
			DB::query("UPDATE EditableFormField_versions SET ClassName = 'EditableDynamicListField' WHERE ClassName = 'EditableDataListField'");
		}
	}
	

}