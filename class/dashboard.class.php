<?php

class TQDashBoard extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'qdashboard');
        parent::add_champs('fk_user,fk_user_author,fk_usergroup',array('type'=>'integer','index'=>true));
		parent::add_champs('uid,send_by_mail',array('index'=>true));
		
        parent::_init_vars('title');
        parent::start();    
		
		$this->setChild('TQDashBoardQuery', 'fk_qdashboard');
        
		$this->TSendByMail=array(
			''=>$langs->trans('No')
			,'DAY'=>$langs->trans('EveryDay')
			,'WEEK'=>$langs->trans('EveryWeek')
			,'MONTH'=>$langs->trans('EveryMonth')
		);
		
    }
	
	function save(&$PDOdb) {
		global $user;
		
		if(empty($this->fk_user_author)) $this->fk_user_author = $user->id; // creator
		$this->fk_user = $user->id; //  last updater
		if(empty($this->uid))$this->uid = md5( time().$this->title.rand(1000,999999) );
		
		parent::save($PDOdb);
		
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
	