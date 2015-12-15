<?php
	
	if(!empty($_GET['uid'])) {
		define('INC_FROM_CRON_SCRIPT',true);
	}
	
	require 'config.php';
	
	dol_include_once('/query/class/query.class.php');
	dol_include_once('/query/class/dashboard.class.php');
	
	$langs->load('query@query');
	
	
	$action = GETPOST('action');
	
	$dashboard=new TQDashBoard;
	$PDOdb=new TPDOdb;

	$fk_user_to_use = GETPOST('fk_user');	
	if(empty($user->id) && !empty($fk_user_to_use)) {
		$user->fetch($fk_user_to_use);
	}
	
	switch ($action) {
		case 'view':
			
			$dashboard->load($PDOdb, GETPOST('id'));
			fiche($dashboard);
			
			break;
		case 'add':
			
			if(empty($user->rights->query->dashboard->create)) accessforbidden();
			
			fiche($dashboard);
			
			break;
			
		case 'run':
			
			if(GETPOST('uid')) {
				
				if(GETPOST('storechoice')>0 && $user->id > 0) {
					dol_include_once('/core/lib/admin.lib.php');
					$res = dolibarr_set_const($db, 'QUERY_HOME_DEFAULT_DASHBOARD_USER_'.$user->id, GETPOST('uid'));
				}
				
				$dashboard->loadBy($PDOdb, GETPOST('uid'),'uid',true);
				run($PDOdb, $dashboard, false);
				
			}
			else {
				$dashboard->load($PDOdb, GETPOST('id'));
				run($PDOdb, $dashboard);
			 
			}
			
			break;		
	
		default:
			
			liste();
			
			break;
	}
	



function run(&$PDOdb, &$dashboard, $withHeader = true) {
	
	echo fiche($dashboard, 'view', $withHeader);

}


function liste() {
	
	global $langs, $conf,$user;
	
	$PDOdb=new TPDOdb;
	
	llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js') , array('/query/css/dashboard.css','/query/css/jquery.gridster.min.css') );

	dol_fiche_head();
	
	$sql="SELECT qd.rowid as 'Id', qd.title 
	FROM ".MAIN_DB_PREFIX."qdashboard qd
	WHERE 1
	";
	
	if($user->admin) {
		null;
	}
	else {
		$sql.=" AND (qd.fk_user_author=".$user->id." OR  qd.fk_usergroup IN (SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user=".$user->id." ) )";
	}
	
	
	$r=new TListviewTBS('lDash');
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

function fiche(&$dashboard, $action = 'edit', $withHeader=true) {
	global $langs, $conf,$user, $db;
	
	$PDOdb=new TPDOdb;
	
	$form=new TFormCore;
	
	$cell_height = 200;
	
	if($withHeader) {
	
		llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js') , array('/query/css/dashboard.css','/query/css/jquery.gridster.min.css') );
	
		dol_fiche_head();
		print_fiche_titre($dashboard->title);
	}
	else if(GETPOST('for_incusion')>0) {
		?>
		<div class="querydashboard">
			<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/dashboard.css',1); ?>">
			<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/jquery.gridster.min.css',1); ?>">
			<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/dashboard.js',1); ?>"></script>
			<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/jquery.gridster.min.js',1); ?>"></script>

		<?php
	}
	else {
		?><html>
			<head>
				<meta charset="UTF-8">
				<link rel="stylesheet" type="text/css" href="<?php echo dol_buildpath('/theme/eldy/style.css.php?lang=fr_FR&theme=eldy',1); ?>">
				<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/dashboard.css',1); ?>">
				<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/jquery.gridster.min.css',1); ?>">

				<script type="text/javascript" src="<?php echo dol_buildpath('/includes/jquery/js/jquery.min.js',1); ?>"></script>
				<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/dashboard.js',1); ?>"></script>
				<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/jquery.gridster.min.js',1); ?>"></script>
				<style type="text/css">
					.pagination,.notInGeneration { display : none; }
				</style>
			</head>
		<body style="min-width: 1300px;">
		<?php	
	}
	
	?>
	<script type="text/javascript">
		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		
		$(document).ready(function(){ //DOM Ready

		    $(".gridster ul").gridster({
		        widget_margins: [10, 10]
		        ,widget_base_dimensions: [300, <?php echo $cell_height ?>]
		        ,min_cols:3
		        ,min_rows:5
		        ,serialize_params: function($w, wgd) { 
		        	return { posx: wgd.col, posy: wgd.row, width: wgd.size_x, height: wgd.size_y, k : $w.attr('data-k') } 
		        }
		        <?php
		        if($action == 'edit') {
		        
		        ?>,resize: {
		            enabled: true
		            ,max_size: [4, 4]
		            ,min_size: [1, 1]
		        }
		        
		       
		       <?php
		       }
		       ?>
		    })<?php 
				if($action == 'view') {
					
					echo '.data(\'gridster\').disable()';
					
				}
			
			?>;
		
			var gridster = $(".gridster ul").gridster().data('gridster');
		
			$('#addQuery').click(function() {
				
				var fk_query = $('select[name=fk_query]').val();
				
				$.ajax({
					url: MODQUERY_INTERFACE
					,data: {
						put:'dashboard-query'
						,fk_query : fk_query
						,fk_qdashboard:<?php echo $dashboard->getId() ?>
					}
					,dataType:'json'
					
				}).done(function(data) {
					
					var title = $('select[name=fk_query] option:selected').text();
					
					gridster.add_widget('<li data-k="'+data+'">'+title+'</li>',1,1,1,1);	
				});
				
			});
			
			$('#saveDashboard').click(function() {
				
				var $button = $(this);
				
				$button.hide();
				
				$.ajax({
					url: MODQUERY_INTERFACE
					,data: {
						put:'dashboard'
						,id:<?php echo $dashboard->getId() ?>
						,title:$('input[name=title]').val()
						,fk_usergroup:$('select[name=fk_usergroup]').val()
						,send_by_mail:$('select[name=send_by_mail]').val()
						,hook:$('select[name=hook]').val()
					}
					
				}).done(function(data) {
				   <?php
					if($dashboard->getId()> 0) {
						?>$.ajax({
							url: MODQUERY_INTERFACE
							,data: {
								put:'dashboard-query-link'
								,TCoord:gridster.serialize( )
								,fk_qdashboard:<?php echo $dashboard->getId() ?>
							}
							,dataType:'json'
							
					  }).done(function(data) {
					  	$button.show();
					  });
						
						<?php
					}					  	
					else {
						echo 'document.location.href="?action=view&id="+data;';
					}
				  ?>
					  	
		              
				});
				
			});
		
		});
		
		function delTile(idTile) {
			$('li[tile-id='+idTile+']').css('opacity',.5);
			
			$.ajax({
				url: MODQUERY_INTERFACE
				,data: {
					put:'dashboard-query-remove'
					,id : idTile
				}
				,dataType:'json'
				
			}).done(function(data) {
				$('li[tile-id='+idTile+']').toggle();
				//document.location.hre
			});
			
		}
			
	</script>
	<?php
	if($action == 'edit') {
		?><div><?php 
			$TQuery = TQuery::getQueries($PDOdb);
			echo $form->texte($langs->trans('Title'), 'title', $dashboard->title, 50,255);
			
			$formDoli=new Form($db);
			echo ' - '.$langs->trans('LimitAccessToThisGroup').' : '.$formDoli->select_dolgroups($dashboard->fk_usergroup, 'fk_usergroup', 1);
			
			echo $form->combo(' - '.$langs->trans('SendByMailToThisGroup'),'send_by_mail', $dashboard->TSendByMail, $dashboard->send_by_mail);
			echo $form->combo(' - '.$langs->trans('ShowThisInCard'),'hook', $dashboard->THook, $dashboard->hook);
			
			?>
			<a href="#" class="butAction" id="saveDashboard"><?php echo $langs->trans('SaveDashboard'); ?></a>
		</div>
		<?php
		if($dashboard->getId()>0) {
		?>
		<div>
			<?php
				$TQuery = TQuery::getQueries($PDOdb);
				echo $form->combo('', 'fk_query', $TQuery, 0);
			?>
			<a href="#" class="butAction" id="addQuery"><?php echo $langs->trans('AddThisQuery'); ?></a>
		</div>
		
		<?php
		}
	}
	else {
		if(!empty($conf->global->QUERY_SHOW_PDF_TRANSFORM))	echo '<div style="text-align:right"><a class="butAction" style=";z-index:999;" href="download-dashboard.php?uid='.$dashboard->uid.'">'.$langs->trans('Download').'</a></div>';
		
	}
	?>		
	
	<div class="gridster">
	    <ul>
	    	<?php
	    	foreach($dashboard->TQDashBoardQuery as $k=>&$cell) {
	    		echo '<li tile-id="'.$cell->getId().'" data-k="'.$k.'" data-row="'.$cell->posy.'" data-col="'.$cell->posx.'" data-sizex="'.$cell->width.'" data-sizey="'.$cell->height.'" '.($withHeader ? '' : 'style="overflow:hidden;"').'>';
		    		if($action == 'edit') {
		    			echo '<a style="position:absolute; top:3px; right:3px; z-index:999;" href="javascript:delTile('.$cell->getId().')">'.img_delete('DeleteThisTile').'</a>';	
		    		}
					if(!$withHeader && $cell->query->type == 'LIST')$cell->query->type = 'SIMPLELIST';
					echo $cell->query->run($PDOdb, false, $cell->height * $cell_height, GETPOST('table_element'), GETPOST('objectid'));
				
	    		echo '</li>';
				
				
	    	}
	    	
	    	?>
	        
	
	    </ul>
	</div>
	
	<div style="clear:both"></div>
	
	<?php
	
	if($withHeader) {
		
		if($dashboard->getId()>0) print dol_buildpath('/query/dashboard.php?action=run&uid='.$dashboard->uid,2);
		dol_fiche_end();
		
		llxFooter();
		
	}
	else if(GETPOST('for_incusion')>0) {
		?></div><?php	
	}
	else {
		?>
		</body></html>
		<?php		
		
	}
	
}
	


