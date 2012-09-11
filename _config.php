<?php

define('DYNAMICLIST_MODULE', 'dynamiclists');

if (basename(dirname(__FILE__)) != DYNAMICLIST_MODULE) {
	throw new Exception(DYNAMICLIST_MODULE . ' module not installed in correct directory');
}

if (!class_exists('GridFieldSortableRows')) {
	throw new Exception('The Dynamic Lists module requires the Sortable Grid Field Module module.');
}