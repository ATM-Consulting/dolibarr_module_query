<?php

	require '../config.php';
	dol_include_once('/query/class/query.class.php');
	
	
	$get = GETPOST('get');
	
	switch ($get) {
		case 'tables':
			
			__out( TQuery::getTables($db) , 'json' );
			
			break;
		
		default:
			
			break;
	}
