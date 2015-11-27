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
         
        parent::set_table(MAIN_DB_PREFIX.'qdashboard');
        parent::add_champs('fk_qdashboard,fk_query',array('type'=>'integer','index'=>true));
		parent::add_champs('width,height,posx,posy',array('type'=>'integer'));
        parent::_init_vars('title');
        parent::start();    
		
        
    }
}
	