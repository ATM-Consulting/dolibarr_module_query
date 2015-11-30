<?php

	require '../config.php';
	dol_include_once('/query/class/query.class.php');
	dol_include_once('/query/class/dashboard.class.php');
	
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
		
		case 'dashboard':
			$dash=new TQDashBoard;
			$dash->load($PDOdb, GETPOST('id'));
			
			$dash->set_values($_REQUEST);
			
			$dash->save($PDOdb);
			
			print $dash->getId();
			
			break;
		
		case 'dashboard-query-link':
			$dash=new TQDashBoard;
			if($dash->load($PDOdb, GETPOST('fk_qdashboard'))) {
				$dash->TQDashBoardQuery[GETPOST('k')]->set_values($_REQUEST);
				
				$dash->save($PDOdb);
				
				print 1;
				
			}
			else {
				print 0;
			}
			
			break;
		
		case 'dashboard-query':
			$dash=new TQDashBoard;
			if($dash->load($PDOdb, GETPOST('fk_qdashboard'))) {
				$k = $dash->addChild($PDOdb, 'TQDashBoardQuery');
				
				$dash->TQDashBoardQuery[$k]->fk_query = GETPOST('fk_query');
				
				$dash->save($PDOdb);
				
				print $k;
				
			}
			else {
				print 0;
			}
			
			break;
	}