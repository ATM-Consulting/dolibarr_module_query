<?php
	
	require 'config.php';
	
	dol_include_once('/query/class/query.class.php');
	dol_include_once('/query/class/dashboard.class.php');
	
	$langs->load('query@query');
	
	
	$action = GETPOST('action');
	
	$dashboard=new TQDashBoard;
	$PDOdb=new TPDOdb;
	
	switch ($action) {
		case 'view':
			
			$dashboard->load($PDOdb, GETPOST('id'));
			fiche($query);
			
			break;
		case 'add':
			
			if(empty($user->rights->query->dashboard->create)) accessforbidden();
			
			fiche($dashboard);
			
			break;
			
		case 'run':
			$dashboard->load($PDOdb, GETPOST('id'));
			run($PDOdb, $dashboard);
			
			break;		
	
		default:
			
			liste();
			
			break;
	}
	



function run(&$PDOdb, &$query) {
	
	llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js') , array('/query/css/dashboard.css','/query/css/jquery.gridster.min.css') );
	dol_fiche_head();
	
	print_fiche_titre($dashboard->title);
	
	if(empty($dashboard->sql_from)) die('InvalidQuery');
	
	echo $dashboard->run($PDOdb);
	
	dol_fiche_end();
	
	llxFooter();
}


function liste() {
	
	global $langs, $conf,$user;
	
	$PDOdb=new TPDOdb;
	
	llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js') , array('/query/css/dashboard.css','/query/css/jquery.gridster.min.css') );

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
	
	llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js') , array('/query/css/dashboard.css','/query/css/jquery.gridster.min.css') );
	dol_fiche_head();
	
	?>
	<script type="text/javascript">
		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		
		$(document).ready(function(){ //DOM Ready

		    $(".gridster ul").gridster({
		        widget_margins: [10, 10]
		        ,widget_base_dimensions: [300, 200]
		        ,min_cols:3
		        ,min_rows:5
		        ,resize: {
		            enabled: true,
		            max_size: [4, 4],
		            min_size: [1, 1]
		          }
		    });
		
		});
			
	</script>
	<div class="gridster">
	    <ul>
	        <li data-row="1" data-col="1" data-sizex="1" data-sizey="1"></li>
	        <li data-row="2" data-col="1" data-sizex="1" data-sizey="1"></li>
	        <li data-row="3" data-col="1" data-sizex="1" data-sizey="1"></li>
	
	        <li data-row="1" data-col="2" data-sizex="2" data-sizey="1"></li>
	        <li data-row="2" data-col="2" data-sizex="2" data-sizey="2"></li>
	
	        <li data-row="1" data-col="4" data-sizex="1" data-sizey="1"></li>
	        <li data-row="2" data-col="4" data-sizex="2" data-sizey="1"></li>
	        <li data-row="3" data-col="4" data-sizex="1" data-sizey="1"></li>
	
	        <li data-row="1" data-col="5" data-sizex="1" data-sizey="1"></li>
	        <li data-row="3" data-col="5" data-sizex="1" data-sizey="1"></li>
	
	        <li data-row="1" data-col="6" data-sizex="1" data-sizey="1"></li>
	        <li data-row="2" data-col="6" data-sizex="1" data-sizey="2"></li>
	    </ul>
	</div>
	
	<div style="clear:both"></div>
	
	<?php
	dol_fiche_end();
	
	llxFooter();
}
	


