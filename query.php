<?php

require('config.php');

dol_include_once('/query/class/query.class.php');

$langs->load('query@query');


$action = GETPOST('action');

$query=new TQuery;
$PDOdb=new TPDOdb;


switch ($action) {
	case 'view':
		
		$query->load($PDOdb, GETPOST('id'));
		fiche($query);
		
		break;
	case 'add':
		
		if(empty($user->rights->query->all->create)) accessforbidden();
		
		fiche($query);
		
		break;
		
	case 'run':
		$query->load($PDOdb, GETPOST('id'));
		run($PDOdb, $query);
		
		break;		

	case 'preview':
		$query->load($PDOdb, GETPOST('id'));
		run($PDOdb, $query, true);
		
		break;

	default:
		
		liste();
		
		break;
}




function run(&$PDOdb, &$query, $preview = false) {
	global $conf;
	
	if(!$preview) {
		llxHeader('', 'Query', '', '', 0, 0, array() , array('/query/css/query.css') );
		dol_fiche_head();
	}
	else{
		
		?><html>
			<head>
				<link rel="stylesheet" type="text/css" href="/dolibarr/3.9/htdocs/theme/eldy/style.css.php">
				<script type="text/javascript" src="/dolibarr/3.9/htdocs/includes/jquery/js/jquery.min.js"></script>
			</head>
		<body style="margin:0 0 0 0;padding:0 0 0 0;"><?php
		
	}
	
	if(empty($query->sql_from)) die('InvalidQuery');
	
	$show_details = true;
	
	if($preview) {
		$query->preview = true;
		$show_details = false;	
	}
	
	echo $query->run($PDOdb, $show_details);
	
	if(!$preview) {
		dol_fiche_end();
		llxFooter();
		
	}
	else{
		?></body></html><?php
	}
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
	
	llxHeader('', 'Query', '', '', 0, 0, array('/query/js/query.js','/query/js/jquery.base64.min.js') , array('/query/css/query.css') );
	dol_fiche_head();
	
	?>
	<script type="text/javascript">
		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		var MODQUERY_QUERYID = <?php echo $query->getId(); ?>;
		
		function _init_query() {
			
			<?php

			if($query->getId()>0) {
				
				foreach($query->TTable as $table) {
					
					echo 'addTable("'.$table.'"); ';
		
				}
			
				if(empty($query->TField) && !empty($query->sql_fields)) {
					$query->TField = explode(',', $query->sql_fields );
				}
				//$TField = 
				if(!empty($query->TField )) {
					foreach($query->TField as $field) {
						
						echo ' checkField("'.$field.'"); ';
					
					}
					
					echo 'showQueryPreview('.$query->getId().');';
					
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
				
				if(!empty($query->THide)) {
					foreach($query->THide as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'hide\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
				
				if(!empty($query->TTitle)) {
					foreach($query->TTitle as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'title\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
				
				if(!empty($query->TFunction)) {
					foreach($query->TFunction as $f=>$v) {
						
						echo ' $("#fields [sql-act=\'function\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
						
					}
				}
				
				if(!empty($query->TGroup)) {
					foreach($query->TGroup as $f) {
						
						echo ' $("#fields [sql-act=\'group\'][field=\''.$f.'\']").val(1); ';
						
					}
				}
			
				if(!empty($query->TJoin)) {
					foreach($query->TJoin as $t=>$join) {
						
						?>
						$("td[rel=from] select[jointure='<?php echo $t; ?>']").val("<?php echo $join[0]; ?>");
						$("td[rel=to] select[jointure-to='<?php echo $t; ?>']").val("<?php echo $join[1]; ?>");
						
						TJoin['<?php echo $t; ?>'] = ["<?php echo $join[0]; ?>", "<?php echo $join[1]; ?>"]; 
						<?php
						
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
		<div>
			<?php echo $langs->trans('Title') ?> : 
			<input type="text" name="title" size="80" value="<?php echo $query->title; ?>" />
			<?php
				$form=new TFormCore;
				echo $form->combo('- '.$langs->trans('Type').' : ', 'type', $query->TType, $query->type);
				echo '- '.$langs->trans('XAxis').' : <select name="xaxis" initValue="'.$query->xaxis.'"></select>';
			?>
			<input class="button" type="button" id="save_query" value="<?php echo $langs->trans('SaveQuery') ?>" />
		</div>
		<?php
		if($query->getId()>0) {
		?>
		<div>
			<?php echo $langs->trans('AddOneOfThisTables') ?> : <select id="tables"></select>
			<input class="button" type="button" id="add_this_table" value="<?php echo $langs->trans('AddThisTable') ?>" />
		</div>
		
		<div id="selected_tables">
			
		</div>
		<?php
		}
		?>
	</div>
	<?php
		if($query->getId()>0) {
	?>
	<div class="selected_fields">
		<div class="border" id="fields"><div class="liste_titre"><?php echo $langs->trans('FieldsOrder'); ?></div></div>
	</div>
	<div style="clear:both; border-top:1px solid #000;"></div>
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
	
	<div style="clear:both; border-top:1px solid #000;"></div>
	<div id="previewRequete" style="display: none;">
		<iframe src="#" width="100%" frameborder="0" onload="this.height = this.contentWindow.document.body.scrollHeight + 'px'"></iframe>
	</div>
	
	<?php
		}
	?>
	</form>
	
	
	
	<?php
	dol_fiche_end();
	
	llxFooter();
}
	



