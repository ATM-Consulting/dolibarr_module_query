<?php

require('config.php');

dol_include_once('/query/class/query.class.php');

llxHeader('', 'Query', '', '', 0, 0, array('/query/js/query.js') , array('/query/css/query.css') );

dol_fiche_head();

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
	default:
		
		liste();
		
		break;
}


dol_fiche_end();


function liste() {
	
	global $langs, $conf,$user;
	
	$PDOdb=new TPDOdb;
	
	
	$sql="SELECT rowid as 'Id', title 
	FROM ".MAIN_DB_PREFIX."query
	WHERE 1
	ORDER BY date_cre ASC ";
	
	$r=new TListviewTBS('lQuery');
	echo $r->render($PDOdb, $sql,array(
		'link'=>array(
			'Id'=>'<a href="?action=view&id=@val@">@val@</a>'
		)
	
	));
	
	
}

function fiche(&$query) {
	global $langs, $conf,$user;
	
	
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
				
			}
						
			?>
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

}
	


llxFooter();
