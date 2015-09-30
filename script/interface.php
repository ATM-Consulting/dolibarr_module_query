<?php

	require '../config.php';
	dol_include_once('/query/class/query.class.php');
	
	
	$get = GETPOST('get');
	
	switch ($get) {
		case 'tables':
			
			__out( TQuery::getTables($db) , 'json' );
			
			break;
		case 'fields':
			
			__out( TQuery::getFields($db, GETPOST('table')) , 'json' );
			
			break;
		
		default:
			
			break;
	}
