<?php

class TQuery extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'query');
        parent::add_champs('sql_fields,sql_from,sql_where',array('type'=>'text'));
		parent::add_champs('TField,TTable,TOrder,TTitle,TLink,THide,TMode,TOperator,TGroup,TFunction,TValue',array('type'=>'array'));
		
        parent::_init_vars('title,type,xaxis');
        parent::start();    
		
		$this->TType = array(
			'LIST'=>$langs->trans('List')
			,'SIMPLELIST'=>$langs->trans('SimpleList')		
			,'CHART'=>$langs->trans('Chart')
			,'PIE'=>$langs->trans('Pie')
			,'AREA'=>$langs->trans('Area')
		);
		
        $this->show_details = true;
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
	
	function run(&$PDOdb, $show_details = true, $height=0) {
		
		$this->show_details = $show_details;
		
		if(!empty($height)) $this->height = $height;
		
		if($this->type == 'CHART') {
			return $this->runChart($PDOdb);
		}
		else if($this->type == 'PIE') {
			return $this->runChart($PDOdb,'PieChart');
		}
		else if($this->type == 'AREA') {
			return $this->runChart($PDOdb,'AreaChart');
		}
		else if($this->type == 'SIMPLELIST') {
			return load_fiche_titre($this->title).$this->runList($PDOdb,dol_buildpath('/query/tpl/html.simplelist.tbs.html'));
		}
		else {
			
			return load_fiche_titre($this->title).$this->runList($PDOdb);	
		}
		
		
	}
	
	function runChart(&$PDOdb, $type = 'LineChart') {
		//TODO déplacer rendu graphique dans listviewTBS Abricot
		
		list($tableXaxis,$fieldXaxis) = explode('.', $this->xaxis);
		
		$sql=$this->getSQL();
		$TBind = $this->getBind();
		$TSearch = $this->getSearch();
		$THide = $this->getHide();
		$TTitle = $this->getTitle();
		
		$Tab = $PDOdb->ExecuteAsArray($sql, $TBind);
		$TData = array();
		$header = '';
		$first = true;
		foreach($Tab as $row) {
			
			//var_dump($row);
			if($first) {
				
				$TValue=array();
				$key = null;
			
				foreach($row as $k=>$v) {
				
					if($k == $fieldXaxis) {
						$key = $k;
					}
					else if(!in_array($k, $THide)) {
						$TValue[] = !empty($TTitle[$k]) ? $TTitle[$k] : $k;
					}
					
				}
				if(!is_null($key)) {
					$header='["'.addslashes($key).'","'.implode('","', $TValue).'"]';
				}
				else{
					exit('QueryChart, where is Xaxis '.$fieldXaxis.' !?');
				}
				$first = false;
			}
			$TValue=array();
			$key = null;
			
			
			foreach($row as $k=>$v) {
				
				if($k == $fieldXaxis) {
					$key = $v;
				}
				else if(!in_array($k, $THide)) {
					$TValue[] = $v;
				}
				
			}

			if(!is_null($key)) {
				if(!isset($TData[$key])) $TData[$key] = $TValue;
				else {
					foreach($TData[$key] as $k=>$v) {
						$TData[$key][$k]+=(float)$TValue[$k];
					}
					
				}
			}
			
			
		}
		
		$data = $header;
		foreach($TData as $key=>$TValue) {
			
			$data .= ',[ "'.$key.'", ';
			foreach($TValue as $v) {
				$data.=(float)$v.',';
			}
			
			$data.=' ]';
		}
		if($this->show_details) print $data;
		$height = empty($this->height) ? 500 : $this->height;
		$curveType= empty($this->curveType) ? 'function' : $this->curveType; // none or function
		
		$html = '';
		
		$html.='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">

	      // Load the Visualization API and the piechart package.
	      google.load("visualization", "1", {"packages":["corechart"]});
	
	      // Set a callback to run when the Google Visualization API is loaded.
	      google.setOnLoadCallback(drawChart);
		
		  function drawChart() {
	        var data = google.visualization.arrayToDataTable([
	          '.$data.'
	        ]);

	        var options = {
	          title: "'.addslashes($this->title).'",
	          curveType: "'.$curveType.'"
	          ,legend: { position: "bottom" }
			  ,animation: { "startup": true }
			  ,height : '.$height.'
			  '.( $type == 'PieChart' ? ',pieHole: 0.2' : '').'
			  '.( $type == 'AreaChart' ? ',isStacked: \'percent\'' : '').'
	        };
	
	        var chart = new google.visualization.'.$type.'(document.getElementById("div_query_chart'.$this->getId().'"));
	
	        chart.draw(data, options);
	      }

				
		
		</script>
		<div id="div_query_chart'.$this->getId().'"></div>
		
		'; 
		
		return $html;
	}
	
	function getSQL() {
			
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
			WHERE ".($this->sql_where ? $this->sql_where : 1 )."
			";
		
		
		if(!empty($this->TGroup)) {
			$sql.=" GROUP BY ".implode(',', $this->TGroup);	
		}
		
		return $sql;			
	}

	function getBind() {
		$TBind = array();
		if(!empty($this->TMode)) {
			
			foreach($this->TMode as $f=>$m) {
				
				if(empty($this->TOperator[$f])) continue;
				
				$fBind  = strtr($f, '.', '_');
				list($tbl, $fSearch) = explode('.', $f);
				
				if($m == 'var') {
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
	
	function runList(&$PDOdb, $template = '') {
		
		$html = '';
		
			$sql=$this->getSQL();
			$TBind = $this->getBind();
			$TSearch = $this->getSearch();
			$THide = $this->getHide();
			
			$form=new TFormCore();
			$html.= $form->begin_form('auto','formQuery'. $this->getId(),'get');
			
			$html.=  $form->hidden('action', 'run');
			$html.=  $form->hidden('id',  $this->getId());
			
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
				,'search'=>$TSearch
				
				
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
