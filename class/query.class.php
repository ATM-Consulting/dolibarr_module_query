<?php

class TQuery {
	
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
