<?php

	require '../config.php';
	dol_include_once('/query/class/query.class.php');
	
	$PDOdb=new TPDOdb;
	
	$get = GETPOST('get');
	$put = GETPOST('put');
	
	switch ($get) {
		case 'tables':
			
			__out( TQuery::getTables($PDOdb) , 'json' );
			
			break;
		case 'fields':
			
			__out( TQuery::getFields($PDOdb, GETPOST('table')) , 'json' );
			
			break;
		
		default:
			
			break;
	}

	
	switch ($put) {
		case 'query':

			$query = new TQuery;
			$query->load($PDOdb, GETPOST('id'));
			
			$query->set_values($_REQUEST);
			
			$query->save($PDOdb);
			
			print $query->getId();
			
			break;
		


	}