<?php

class TQDashBoard extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'qdashboard');
        parent::add_champs('fk_user,fk_user_author',array('type'=>'integer','index'=>true));
		
        parent::_init_vars('title');
        parent::start();    
		
		$this->setChild('TQDashBoardQuery', 'fk_qdashboard');
        
    }
	
}

class TQDashBoardQuery extends TObjetStd {
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'qdashboard_query');
        parent::add_champs('fk_qdashboard,fk_query',array('type'=>'integer','index'=>true));
		parent::add_champs('width,height,posx,posy',array('type'=>'integer'));
        parent::_init_vars('title');
        parent::start();    
		
    	$this->query=null;   
		$this->width=$this->height=$this->posx=$this->posy = 1; 
    }
	
	function load(&$PDOdb, $id) {
		
		parent::load($PDOdb, $id);
		
		if($this->fk_query>0) {
			
			$this->query = new TQuery;
			$this->query->load($PDOdb, $this->fk_query);
			
		}
		
	}
	
	
}
	