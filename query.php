<?php

require('config.php');

dol_include_once('/query/class/query.class.php');




$action = GETPOST('action');

$query=new TQuery;
$PDOdb=new TPDOdb;


switch ($action) {
	case 'view':
		
		$query->load($PDOdb, GETPOST('id'));
		fiche($query);
		
		break;
	case 'add':
		fiche($query);
		
		break;
		
	case 'run':
		$query->load($PDOdb, GETPOST('id'));
		run($PDOdb, $query);
		
		break;		

	default:
		
		liste();
		
		break;
}




function run(&$PDOdb, &$query) {
	
	llxHeader('', 'Query', '', '', 0, 0, array() , array('/query/css/query.css') );
	dol_fiche_head();
	
	print_fiche_titre($query->title);
	
	if(empty($query->sql_from)) die('InvalidQuery');
	
	$sql="SELECT ".($query->sql_fields ? $query->sql_fields : '*') ."
	FROM ".$query->sql_from."
	WHERE ".($query->sql_where ? $query->sql_where : 1 )."
	";
	
	$TBind = array();
	$TSearch = array();
	
	foreach($query->TMode as $f=>$m) {
		
		if(empty($query->TOperator[$f])) continue;
		
		$fBind  = strtr($f, '.', '_');
		list($tbl, $fSearch) = explode('.', $f);
		
		if($m == 'var') {
			$TBind[$fBind] = '%';
			$TSearch[$fSearch] = true;
		}
		else{
			if(!empty($query->TValue[$f])) {
				$TBind[$fBind] = $query->TValue[$f];	
			}
			
		}
		
	}
	var_dump($TBind);
	
	print '<div class="query">'.$sql.'</div>';
	
	$r=new TListviewTBS('lRunQuery');
	echo $r->render($PDOdb, $sql,array(
		'link'=>$query->TLink
		,'title'=>$query->TTitle
		,'liste'=>array(
			'titre'=>''
		)
		,'search'=>$TSearch
		
		
	)
	,$TBind);
	
	dol_fiche_end();
	
	llxFooter();
}


function liste() {
	
	global $langs, $conf,$user;
	
	$PDOdb=new TPDOdb;
	
	llxHeader('', 'Query', '', '', 0, 0, array() , array('/query/css/query.css') );
	dol_fiche_head();
	
	$sql="SELECT rowid as 'Id', title 
	FROM ".MAIN_DB_PREFIX."query
	WHERE 1
	 ";
	
	$r=new TListviewTBS('lQuery');
	echo $r->render($PDOdb, $sql,array(
		'link'=>array(
			'Id'=>'<a href="?action=view&id=@val@">'.img_picto('Edit', 'edit.png').' @val@</a>'
			,'title'=>'<a href="?action=run&id=@Id@">'.img_picto('Run', 'object_cron.png').' @val@</a>'
		)
		,'title'=>array(
			'title'=>$langs->trans('Title')
		)
	
	));
	
	dol_fiche_end();
	
	llxFooter();
}

function fiche(&$query) {
	global $langs, $conf,$user;
	
	llxHeader('', 'Query', '', '', 0, 0, array('/query/js/query.js') , array('/query/css/query.css') );
	dol_fiche_head();
	
	?>
	<script type="text/javascript">
		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		
		function _init_query() {
			
			<?php

			if($query->getId()>0) {
				
				foreach($query->TTable as $table) {
					
					echo 'addTable("'.$table.'"); ';
		
				}
			
				$TField = explode(',', $query->sql_fields );
				
				foreach($TField as $field) {
					
					echo ' checkField("'.$field.'"); ';
				
				}
				
				if(!empty($query->TMode)) {
					foreach($query->TMode as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'mode\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
					
				}
				
				if(!empty($query->TOrder)) {
					foreach($query->TOrder as $f=>$v) {
					
						echo ' $("#fields [sql-act=\'order\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
							
				if(!empty($query->TOperator)) {
					foreach($query->TOperator as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'operator\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
				
				if(!empty($query->TValue)) {
					foreach($query->TValue as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'value\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
				
			}
						
			?>
			
			refresh_sql();
		}
		
	</script>
	
	<form name="formQuery" id="formQuery">
		<input type="hidden" name="id" value="<?php echo $query->getId(); ?>" />
		
	<div>
		<?php echo $langs->trans('Title') ?> : <input type="text" name="title" size="80" value="<?php echo $query->title; ?>" />
		<select id="tables"></select>
		<input class="button" type="button" id="add_this_table" value="<?php echo $langs->trans('AddThisTable') ?>" />
		<input class="button" type="button" id="save_query" value="<?php echo $langs->trans('SaveQuery') ?>" />
		
		<div id="selected_tables">
			
		</div>
		
	</div>
	
	<div class="selected_fields">
		<div class="border" id="fields"><div class="liste_titre"><?php echo $langs->trans('FieldsOrder'); ?></div></div>
	</div>
	
	<div id="results">
		<div>
		<?php echo $langs->trans('Fields'); ?><br />
		<textarea id="sql_query_fields" name="sql_fields">
		</textarea>
		</div>
		
		<div>
		<?php echo $langs->trans('From'); ?><br />
		<textarea id="sql_query_from" name="sql_from">
		</textarea>
		</div>
		
		<div>
		<?php echo $langs->trans('Where'); ?><br />
		<textarea id="sql_query_where" name="sql_where">
		</textarea>
		</div>
	</div>
	
	</form>
	
	<div style="clear:both"></div>
	
	<?php
	dol_fiche_end();
	
	llxFooter();
}
	



