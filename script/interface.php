<?php
if (!defined("NOCSRFCHECK")) define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
	require '../config.php';
	dol_include_once('/query/class/query.class.php');
	dol_include_once('/query/class/dashboard.class.php');

	$PDOdb=new TPDOdb;

	$get = GETPOST('get','alpha');
	$put = GETPOST('put','alpha');

	switch ($get) {
		case 'tables':
			$query=new TQuery;
			$query->load($PDOdb, GETPOST('id','int'));
			__out( TQuery::getTables($query->pdodb) , 'json' );

			break;
		case 'fields':
			$query=new TQuery;
			$query->load($PDOdb, GETPOST('id','int'));
			__out( TQuery::getFields($query->pdodb, GETPOST('table','alpha')) , 'json' );

			break;

		default:

			break;
	}


	switch ($put) {
		case 'query':

			$query = new TQuery;
			$query->load($PDOdb, GETPOST('id','int'));

			$query->set_values($_REQUEST);

			$query->THide = GETPOST('THide','array');
			$query->TOperator = GETPOST('TOperator','array');
			$query->TGroup = GETPOST('TGroup','array');
			$query->TFunction = GETPOST('TFunction','array');
			$query->TValue = GETPOST('TValue','array');
			$query->TNull = GETPOST('TNull','array');
			$query->TOrder = GETPOST('TOrder','array');
			$query->TTranslate = GETPOST('TTranslate','array');
			$query->TFilter = GETPOST('TFilter','array');
			$query->TType = GETPOST('TType','array');
			$query->TClass = GETPOST('TClass','array');
			$query->TMethod = GETPOST('TMethod','array');

			$query->sql_fields = base64_decode(GETPOST('sql_fields','alpha'));
			$query->sql_from = base64_decode(GETPOST('sql_from','alpha'));
			$query->sql_where = base64_decode(GETPOST('sql_where','alpha'));
			$query->sql_afterwhere = base64_decode(GETPOST('sql_afterwhere','alpha'));
//			$PDOdb->debug=true;
			$query->save($PDOdb);

			print $query->getId();

			break;

		case 'dashboard':
			$dash=new TQDashBoard;
			$dash->load($PDOdb, GETPOST('id','int'));

			$dash->set_values($_REQUEST);

			$dash->save($PDOdb);

			print $dash->getId();

			break;

		case 'dashboard-query-link':
			$dash=new TQDashBoard;
			if($dash->load($PDOdb, GETPOST('fk_qdashboard','int'))) {

				$TCoord = $_REQUEST['TCoord'];

				foreach($TCoord as &$coord) {
					$dash->TQDashBoardQuery[(int)$coord['k']]->set_values($coord);
				}

				$dash->save($PDOdb);

				print 1;

			}
			else {
				print 0;
			}

			break;

		case 'dashboard-query-remove':
			$tile = new TQDashBoardQuery;
			$tile->load($PDOdb, GETPOST('id','int'));
			$tile->delete($PDOdb);

			echo 1;

			break;
		case 'dashboard-query':
			$dash=new TQDashBoard;
			if($dash->load($PDOdb, GETPOST('fk_qdashboard','int'))) {
				$k = $dash->addChild($PDOdb, 'TQDashBoardQuery');

				$dash->TQDashBoardQuery[$k]->fk_query = GETPOST('fk_query','int');

				$dash->save($PDOdb);

				print $k;

			}
			else {
				print 0;
			}

			break;
	}
