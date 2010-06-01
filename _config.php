<?php

define('DATALIST_MODULE', 'datalists');

if (basename(dirname(__FILE__)) != DATALIST_MODULE) {
	throw new Exception(DATALIST_MODULE . ' module not installed in correct directory');
}