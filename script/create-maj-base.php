<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */


 
if(defined('INC_FROM_DOLIBARR')) {
	dol_include_once('/query/config.php');
	$PDOdb=new TPDOdb; 
}
else{
	define('INC_FROM_CRON_SCRIPT', true);
	
	require('../config.php');
	$PDOdb=new TPDOdb; 
	$PDOdb->db->debug=true;	
}

dol_include_once('/query/class/query.class.php');
dol_include_once('/query/class/dashboard.class.php');

$o=new TQuery($db);
$o->init_db_by_vars($PDOdb);

$o=new TQDashBoard($db);
$o->init_db_by_vars($PDOdb);

$o=new TQDashBoardQuery($db);
$o->init_db_by_vars($PDOdb);