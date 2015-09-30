<?php

class TQuery extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'query');
        parent::add_champs('sql_fields,sql_from,sql_where',array('type'=>'text'));
		parent::add_champs('TField,TTable,TOrder',array('type'=>'array'));
		
        parent::_init_vars();
        parent::start();    
         
    }
	
	static function getTables(&$db) {
		
		$res = $db->query("SHOW TABLES");
		
		$Tab = array();
		while($obj = $db->fetch_object($res)) {
			
			
			$t = array_values((array)$obj);
			
			$Tab[] = $t[0];
			
		}
		
		return $Tab;
		
	}
	
	static function getFields(&$db, $table) {
		
		$res = $db->query("DESCRIBE ".$table);
		
		$Tab = array();
		while($obj = $db->fetch_object($res)) {
			
			$Tab[] = $obj;
			
		}
		
		return $Tab;
		
		
	}
	
}
