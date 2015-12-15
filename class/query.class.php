<?php

class TQuery extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'query');
        parent::add_champs('sql_fields,sql_from,sql_where,sql_afterwhere',array('type'=>'text'));
		parent::add_champs('TField,TTable,TOrder,TTitle,TTotal,TLink,THide,TTranslate,TMode,TOperator,TGroup,TFunction,TValue,TJoin',array('type'=>'array'));
		parent::add_champs('expert',array('type'=>'int'));
		
        parent::_init_vars('title,type,xaxis');
        parent::start();    
		
		$this->TType = array(
			'LIST'=>$langs->trans('List')
			,'SIMPLELIST'=>$langs->trans('SimpleList')		
			,'CHART'=>$langs->trans('Columns')
			,'LINE'=>$langs->trans('Lines')
			,'PIE'=>$langs->trans('Pie')
			,'AREA'=>$langs->trans('Area')
		);
		
		$this->show_details = true;
		$this->preview = false;
    }
	
	static function getQueries(&$PDOdb) {
		
		$Tab = array();
		
		$Tab = TRequeteCore::_get_id_by_sql($PDOdb, "SELECT rowid, title FROM ".MAIN_DB_PREFIX."query WHERE 1 ORDER BY title", 'title', 'rowid');
		
		return $Tab;
	}
	
	static function getTables(&$PDOdb) {
		
		$PDOdb->Execute("SHOW TABLES");
		
		$Tab = array();
		while($obj = $PDOdb->Get_line()) {
			
			
			$t = array_values((array)$obj);
			
			$Tab[] = $t[0];
			
		}
		
		return $Tab;
		
	}
	
	static function getFields(&$PDOdb, $table) {
		
		$PDOdb->Execute("DESCRIBE ".$table);
		
		$Tab = array();
		while($obj = $PDOdb->Get_line()) {
			
			$Tab[] = $obj;
			
		}
		
		return $Tab;
		
		
	}
	
	function run(&$PDOdb, $show_details = true, $height=0, $table_element='', $objectid=0, $preview = false) {
		
		$this->show_details = $show_details;
		
		if(!empty($height)) $this->height = $height;
		
		if($this->type == 'CHART') {
			return $this->runChart($PDOdb,'ColumnChart',$table_element,$objectid);
		}
		else if($this->type == 'LINE') {
			return $this->runChart($PDOdb,'LineChart',$table_element,$objectid);
		}else if($this->type == 'PIE') {
			return $this->runChart($PDOdb,'PieChart',$table_element,$objectid);
		}
		else if($this->type == 'AREA') {
			return $this->runChart($PDOdb,'AreaChart',$table_element,$objectid);
		}
		else if($this->type == 'SIMPLELIST' || $this->preview) {
			return load_fiche_titre($this->title).$this->runList($PDOdb,dol_buildpath('/query/tpl/html.simplelist.tbs.html'),$table_element,$objectid);
		}
		else {
			
			return load_fiche_titre($this->title).$this->runList($PDOdb,'',$table_element,$objectid);	
		}
		
		
	}
	
	function runChart(&$PDOdb, $type = 'LineChart',$table_element='',$objectid=0) {
		//TODO déplacer rendu graphique dans listviewTBS Abricot
		
		global $conf;
		
		$sql=$this->getSQL($table_element,$objectid);
		$TBind = $this->getBind();
		$TSearch = $this->getSearch();
		$THide = $this->getHide();
		$TTitle = $this->getTitle();
		$TTranslate = $this->getTranslate();
		
		list($xTable, $xaxis) = explode('.',$this->xaxis);
		
		$html = '';
		
		$form=new TFormCore();
		$html.= $form->begin_form('auto','formQuery'. $this->getId(),'get');
		
		$html.=  $form->hidden('action', 'run');
		$html.=  $form->hidden('id', GETPOST('id') ? GETPOST('id') : $this->getId());
		
		if($this->show_details) $html.= '<div class="query">'.$sql.'</div>';
		
		$r=new TListviewTBS('chart'.$this->getId());
		$html .= $r->render($PDOdb, $sql,array(
			'type'=>'chart'
			,'chartType'=>$type
			,'translate'=>$TTranslate
			,'liste'=>array(
				'titre'=>$this->title
			)
			,'title'=>$TTitle
			,'xaxis'=>$xaxis
			,'hide'=>$THide
			,'search'=>$TSearch
			,'height'=>$this->height
		),$TBind);
		
		if($this->show_details) {
				$html.=  '<div class="query">';
				$Tab=array();
				foreach($r->TBind as $f=>$v) {
					$Tab[] = $f.' : '.$v;
				}
				$html.=  implode(', ', $Tab);
				$html.=  '</div>';
				
		}
			
		$html.=$form->end_form();
		
		return $html;
	}
	
	function getSQL($table_element='',$objectid=0) {
			
		if(!empty($this->TFunction)) {
			$this->sql_fields = '';
			foreach($this->TField as $field) {
				
				if(!empty($this->sql_fields))$this->sql_fields.=',';
				
				if(!empty($this->TFunction[$field])) {
					list($t, $fname) = explode('.', $field);
					$this->sql_fields.=strtr($this->TFunction[$field], array('@field@'=> $field)).' as "'.$fname.'"';
				}
				else{
					$this->sql_fields.=$field;
				}
				
			}
		}
		
		$sql="SELECT ".($this->sql_fields ? $this->sql_fields : '*') ."
			FROM ".$this->sql_from."
			WHERE (".($this->sql_where ? $this->sql_where : 1 ).")
			".$this->sql_afterwhere;
		
		if(!empty($table_element) && strpos($sql, $table_element)!==false) {
			$sql.=' AND '.MAIN_DB_PREFIX.$table_element.'.rowid='.$objectid;
		}
		
		if(!empty($this->TGroup)) {
			$sql.=" GROUP BY ".implode(',', $this->TGroup);	
		}
		
		
		if($this->preview && stripos($sql,'LIMIT ') === false) $sql.=" LIMIT 5";
		
		return $sql;			
	}

	function getBind() {
		$TBind = array();
		if(!empty($this->TMode)) {
			
			foreach($this->TMode as $f=>$m) {
				
				if(empty($this->TOperator[$f])) continue;
				
				$fBind  = strtr($f, '.', '_');
				list($tbl, $fSearch) = explode('.', $f);
				
				if($m == 'function') {
					null;
				}
				else if($m == 'var') {
					$TBind[$fBind] = '%';
				}
				else if(!empty($this->TValue[$f])) {
					$TBind[$fBind] = $this->TValue[$f];	
				}
				else {
					$TBind[$fBind] = NULL; // mauvaise définition de la valeur à chercher
				}	
				
				
			}
			
		}
		
		return $TBind ;
		
	}
	function getTitle() {
		
		
		$Tab = array();
		if(!empty($this->TTitle)) {
			
			foreach($this->TTitle as $f=>$v) {
				if($v) {
					list($tbl, $fSearch) = explode('.', $f);
					$Tab[$fSearch]= $v;
				}
				
			}
			
		}
		return $Tab;
	}
	function getHide() {
		
		$THide = array();
		if(!empty($this->THide)) {
			
			foreach($this->THide as $f=>$v) {
				if($v) {
					list($tbl, $fSearch) = explode('.', $f);
					$THide[]= $fSearch;
				}
				
			}
			
		}
		return $THide;
	}
	function getTranslate() {
		
		$Tab = array();
		if(!empty($this->TTranslate)) {
			
			foreach($this->TTranslate as $f=>$v) {
				list($tbl, $field) = explode('.', $f);
				$Tab[$field]=array();
				
				$TPair = str_getcsv($v, ',','"');
				
				foreach($TPair as $pair) {
					
					$pos = strpos($pair,':');
					if($pos!==false) {
						$from = substr($pair, 0, $pos );
						$to = substr($pair, $pos+1 );
						
						$Tab[$field]["$from"] = $to; 	
					}
					
					
				}
				
			}
			
		}
		
		return $Tab;
	}
	function getTotal() {
		
		
		$Tab = array();
		if(!empty($this->TTotal)) {
			
			foreach($this->TTotal as $f=>$v) {
				list($tbl, $field) = explode('.', $f);
				$Tab[$field]=$v;
			}
			
		}
		
		return $Tab;
	}
	function getSearch() {
		
		
		$TSearch = array();
		
		if(!empty($this->TMode)) {
			
			foreach($this->TMode as $f=>$m) {
				
				if(empty($this->TOperator[$f]) || $m!='var') continue;
				
				list($tbl, $fSearch) = explode('.', $f);
				
				$TSearch[$fSearch] = array(
					'recherche'=>TRUE
					,'table'=>$tbl
				);
				
			}
			
		}
		
		return $TSearch;
	}
	
	function runList(&$PDOdb, $template = '',$table_element='',$objectid=0) {
		
		$html = '';
		
			$sql=$this->getSQL($table_element,$objectid);
			$TBind = $this->getBind();
			$TSearch = $this->getSearch();
			$THide = $this->getHide();
			$TTranslate = $this->getTranslate();
			$TTotal = $this->getTotal();
			
			$form=new TFormCore();
			$html.= $form->begin_form('auto','formQuery'. $this->getId(),'get');
			
			$html.=  $form->hidden('action', 'run');
			$html.=  $form->hidden('id', GETPOST('id') ? GETPOST('id') : $this->getId());
			
			if($this->show_details) $html.= '<div class="query">'.$sql.'</div>';
			
			$TTitle=array();
			if(!empty($this->TTitle)) {
				$TTitle = $this->TTitle;
				foreach($this->TTitle as $tableField=>$label) {
					
					list($t,$f) = explode('.', $tableField);
					
					$TTitle[$f] = $label;
					
				}
					
			}
			
			$r=new TListviewTBS('lRunQuery'. $this->getId(), $template);
			$html.=  $r->render($PDOdb, $sql,array(
				'link'=>$this->TLink
				,'hide'=>$THide
				,'title'=>$TTitle
				,'liste'=>array(
					'titre'=>''
				)
				,'orderBy'=>$this->TOrder
				,'translate'=>$TTranslate
				,'search'=>$TSearch
				,'export'=>array(
					'CSV','TXT'
				)
				,'math'=>$TTotal
			)
			,$TBind);
			
			if($this->show_details) {
				$html.=  '<div class="query">';
				$Tab=array();
				foreach($r->TBind as $f=>$v) {
					$Tab[] = $f.' : '.$v;
				}
				$html.=  implode(', ', $Tab);
				$html.=  '</div>';
				
			}
			
			
			$html.= $form->end_form();
			
			return $html;
	}
	
}
