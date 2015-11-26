<?php

class TQuery extends TObjetStd {
	
	function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'query');
        parent::add_champs('sql_fields,sql_from,sql_where',array('type'=>'text'));
		parent::add_champs('TField,TTable,TOrder,TTitle,TLink,THide,TMode,TOperator,TValue',array('type'=>'array'));
		
        parent::_init_vars('title,type');
        parent::start();    
		
		$this->TType = array(
			'LIST'=>$langs->trans('List')		
			,'CHART'=>$langs->trans('Chart')
			,'PIE'=>$langs->trans('Pie')
		);
		
        $this->show_details = true;
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
	
	function run(&$PDOdb) {
		
		if($this->type == 'CHART') {
			
			return $this->runChart($PDOdb);
			
		}
		else {
			return $this->runList($PDOdb);	
		}
		
		
	}
	
	function runChart(&$PDOdb) {
		
		$html = '';
		
		$html.='<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">

	      // Load the Visualization API and the piechart package.
	      google.load("visualization", "1", {"packages":["corechart"]});
	
	      // Set a callback to run when the Google Visualization API is loaded.
	      google.setOnLoadCallback(drawChart);
		
		  function drawChart() {
	        var data = google.visualization.arrayToDataTable([
	          ["Year", "Sales", "Expenses"],
	          ["2004",  1000,      400],
	          ["2005",  1170,      460],
	          ["2006",  660,       1120],
	          ["2007",  1030,      540]
	        ]);
	
	        var options = {
	          title: "'.addslashes($this->title).'",
	          curveType: "none" /* or function */
	          ,legend: { position: "bottom" }
			  ,animation: { }
	        };
	
	        var chart = new google.visualization.LineChart(document.getElementById("div_query_chart'.$this->getId().'"));
	
	        chart.draw(data, options);
	      }

				
		
		</script>
		<div id="div_query_chart'.$this->getId().'"></div>
		
		';
		
		return $html;
	}
	
	function runList(&$PDOdb) {
		
		$html = '';
		
		$sql="SELECT ".($this->sql_fields ? $this->sql_fields : '*') ."
			FROM ".$this->sql_from."
			WHERE ".($this->sql_where ? $this->sql_where : 1 )."
			";
			
			$TBind = array();
			$TSearch = array();
			$binds='';
			if(!empty($this->TMode)) {
				
				foreach($this->TMode as $f=>$m) {
					
					if(empty($this->TOperator[$f])) continue;
					
					$fBind  = strtr($f, '.', '_');
					list($tbl, $fSearch) = explode('.', $f);
					
					if($m == 'var') {
						$TBind[$fBind] = '%';
						$TSearch[$fSearch] = array(
							'recherche'=>TRUE
							,'table'=>$tbl
						);
					}
					else if(!empty($this->TValue[$f])) {
							$TBind[$fBind] = $this->TValue[$f];	
					}
					else {
						$TBind[$fBind] = NULL; // mauvaise définition de la valeur à chercher
					}	
					
					
				}
				
			}
			
			$THide = array();
			foreach($this->THide as $f=>$v) {
				if($v) {
					list($tbl, $fSearch) = explode('.', $f);
					$THide[$fSearch]= true;
					
				}
				
			}
			
			
			$form=new TFormCore();
			$html.= $form->begin_form('auto','formQuery'. $this->getId(),'get');
			
			$html.=  $form->hidden('action', 'run');
			$html.=  $form->hidden('id',  $this->getId());
			
			if($this->show_details) $html.= '<div class="query">'.$sql.'</div>';
			
			
			$r=new TListviewTBS('lRunQuery'. $this->getId());
			$html.=  $r->render($PDOdb, $sql,array(
				'link'=>$this->TLink
				,'hide'=>$THide
				,'title'=>$this->TTitle
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
