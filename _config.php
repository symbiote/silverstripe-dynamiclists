<?php

define('DYNAMICLIST_MODULE', 'dynamiclists');

if (basename(dirname(__FILE__)) != DYNAMICLIST_MODULE) {
	throw new Exception(DYNAMICLIST_MODULE . ' module not installed in correct directory');
}