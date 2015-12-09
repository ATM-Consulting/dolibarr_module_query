<?php

require('config.php');

dol_include_once('/query/class/query.class.php');

$langs->load('query@query');


$action = GETPOST('action');

$query=new TQuery;
$PDOdb=new TPDOdb;


switch ($action) {
	case 'set-expert':
		$query->load($PDOdb, GETPOST('id'));
		$query->expert = 1;
		$query->save($PDOdb);
		fiche($query);
	
		break;
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
				<link rel="stylesheet" type="text/css" href="<?php echo dol_buildpath('/theme/eldy/style.css.php',1) ?>">
				<link rel="stylesheet" type="text/css" href="<?php echo dol_buildpath('/query/css/query.css',1) ?>">
				<script type="text/javascript" src="<?php echo dol_buildpath('/includes/jquery/js/jquery.min.js',1) ?>"></script>
			</head>
		<body style="margin:0 0 0 0;padding:0 0 0 0;"><?php
		
	}
	
	if(empty($query->sql_from)) die('InvalidQuery');
	
	$show_details = true;
	
	if($preview) {
		$query->preview = true;
		
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
	
	$sql="SELECT rowid as 'Id', title,expert
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
			,'expert'=>$langs->trans('Expert')
		)
		,'translate'=>array(
			'expert'=>array( 0=>$langs->trans('No'), 1=>$langs->trans('Yes') )
		)
	
	));
	
	dol_fiche_end();
	
	llxFooter();
}

function fiche(&$query) {
	global $langs, $conf,$user;
	
	llxHeader('', 'Query', '', '', 0, 0, array('/query/js/query.js'/*,'/query/js/jquery.base64.min.js'*/) , array('/query/css/query.css') );
	dol_fiche_head();
	
	?>
	<script type="text/javascript">
		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		var MODQUERY_QUERYID = <?php echo $query->getId(); ?>;
		
		function _init_query() {
			
			<?php

			if($query->getId()>0) {
				
				if($query->expert) {
				
					echo 'showQueryPreview('.$query->getId().');';
						
				
				}
				else {
					
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
							
							echo ' $("#fieldsview [sql-act=\'title\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
							
						}
					}
					
					if(!empty($query->TTranslate)) {
						foreach($query->TTranslate as $f=>$v) {
							
							echo ' $("#fieldsview [sql-act=\'translate\'][field=\''.$f.'\']").val("'. addslashes($v) .'"); ';
							
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
							
					?>
					refresh_sql();
					<?php
					
				}
			}
						
			?>
		}
		
	</script>
	
	<form name="formQuery" id="formQuery">
		<input type="hidden" name="id" value="<?php echo $query->getId(); ?>" />
		
	<div>
		<?php
			if($query->getId()>0 && !$query->expert && !empty($user->rights->query->all->expert) ) {
				
				?>
				<div style="float:right;">
					<a class="butAction" href="?action=set-expert&id=<?php echo $query->getId() ?>"><?php echo $langs->trans('setExpertMode') ?></a>
				</div>
				<?php
				
			}
		?>
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
		if($query->getId()>0 && !$query->expert) {
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
		if($query->getId()>0 && !$query->expert) {
	?>
	<div class="selected_fields">
		<div class="border" id="fields"><div class="liste_titre"><?php echo $langs->trans('FieldsOrder'); ?></div></div>
	</div>
	<?php
		}
		
		if($query->getId()>0) {
	?>
	<div id="results" style="display:<?php echo !$query->expert ? 'none':'block'; ?>;">
		<div>
		<?php echo $langs->trans('Fields'); ?><br />
		<textarea id="sql_query_fields" name="sql_fields"><?php echo $query->sql_fields ?></textarea>
		</div>
		
		<div>
		<?php echo $langs->trans('From'); ?><br />
		<textarea id="sql_query_from" name="sql_from"><?php echo $query->sql_from ?></textarea>
		</div>
		
		<div>
		<?php echo $langs->trans('Where'); ?><br />
		<textarea id="sql_query_where" name="sql_where"><?php echo $query->sql_where ?></textarea>
		
		<?php
			if($query->expert) {
				echo $langs->trans('AfterWhere'); ?><br /><textarea id="sql_query_afterwhere" name="sql_afterwhere"><?php echo $query->sql_afterwhere ?></textarea><?php
			}
			else {
				?><input type="hidden" id="sql_query_afterwhere" name="sql_afterwhere" value="" /><?php
			}
		
		?>
		
		</div>
	</div>
	
	<div style="clear:both; border-top:1px solid #000;"></div>
	<?php
		if($query->getId()>0 && !$query->expert) {
	?>
	<div class="selected_fields_view">
		<div class="border" id="fieldsview"><div class="liste_titre"><?php echo $langs->trans('FieldsView'); ?></div></div>
	</div>
	<?php
		}
	?>
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
	



