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
		case 'delete':
			
			$dashboard->load($PDOdb, GETPOST('id'));
			$dashboard->delete($PDOdb);
			setEventMessage($langs->trans('DeleteSuccess'));
			header('Location:dashboard.php');
			exit;
		
		
			break;
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


function liste()
{
	global $langs, $conf,$user, $db;

	llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js'), array('/query/css/jquery.gridster.min.css', '/query/css/dashboard.css'));

	dol_fiche_head(array(), 0, '', -1);
	
	$sql = '
			SELECT qd.rowid as "Id", qd.title, "" as action
			FROM ' . MAIN_DB_PREFIX . 'qdashboard qd
			WHERE TRUE';

	$sql.= TQDashBoard::getUserRightsSQLFilter($user);


	$r=new Listview($db, 'lDash');
	echo $r->render($sql,array(
		'link'=>array(
			'title'=>'<a href="?action=run&id=@Id@">'.img_picto('Run', 'object_cron.png').' @val@</a>'
			,'action'=>'<a href="?action=view&id=@Id@">'.img_picto('Edit', 'edit.png').'</a> <a href="?action=delete&id=@Id@" onclick="return(confirm(\''.$langs->trans('ConfirmDeleteMessage').'\'));">'.img_picto('Delete', 'delete.png').'</a>'
		
		)
		,'title'=>array(
			'title'=>$langs->trans('Title')
			,'action'=>''
		)
        ,'position' => array(
            'text-align' => array(
                'action' => 'right'
            )
        )
	
	));
	
	dol_fiche_end(-1);
	
	llxFooter();
}

function fiche(&$dashboard, $action = 'edit', $withHeader=true) {
	global $langs, $conf,$user, $db;
	
	$PDOdb=new TPDOdb;
	
	$form=new TFormCore;
	
	$cell_height = 200;
	
	$tab_object = GETPOST('tab_object');
	$table_element = GETPOST('table_element');
	$fk_object = GETPOST('fk_object');
	
	if(empty($table_element)) {
		if($tab_object == 'thirdparty') $table_element = 'societe';
		else if($tab_object == 'project') $table_element = 'projet';
		else $table_element = $tab_object;
	}
	
	
	if($withHeader)
	{
		llxHeader('', 'Query DashBoard', '', '', 0, 0, array('/query/js/dashboard.js', '/query/js/jquery.gridster.min.js', '/query/js/query-resize.js') , array('/query/css/jquery.gridster.min.css', '/query/css/dashboard.css'));

		if($dashboard->getId() > 0)
		{
			$head = TQueryMenu::getHeadForObject($tab_object, $fk_object);
			echo dol_get_fiche_head($head, 'tabQuery' . GETPOST('menuId'), 'Query');
		}

		$title = ! empty($dashboard->title) ? $langs->trans('DashboardTitle', $dashboard->title) : $langs->trans('NewDashboard');
		$morehtmlright = '<a href="' . $_SERVER['PHP_SELF'] . '">' . $langs->trans('BackToList') . '</a>';
		print load_fiche_titre($title, $morehtmlright, '');

		if($dashboard->getId() <= 0)
		{
			echo dol_get_fiche_head('');
		}
	}
	else if(GETPOST('for_incusion')>0) {
		?>
		<div class="querydashboard">
			<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/jquery.gridster.min.css', 1); ?>" />
			<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/dashboard.css', 1); ?>" />
			<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/dashboard.js', 1); ?>"></script>
			<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/jquery.gridster.min.js', 1); ?>"></script>
			<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/query-resize.js', 1); ?>"></script>

		<?php
	}
	else {
		?><!doctype html>
		<html>
			<head>
				<meta charset="UTF-8">
				<link rel="stylesheet" type="text/css" href="<?php echo dol_buildpath('/theme/eldy/style.css.php?lang=fr_FR&theme=eldy', 1); ?>" />
				<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/jquery.gridster.min.css', 1); ?>" />
				<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/query/css/dashboard.css', 1); ?>" />

				<script type="text/javascript" src="<?php echo dol_buildpath('/includes/jquery/js/jquery.min.js', 1); ?>"></script>
				<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/dashboard.js', 1); ?>"></script>
				<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/jquery.gridster.min.js', 1); ?>"></script>
				<script type="text/javascript" src="<?php echo dol_buildpath('/query/js/query-resize.js', 1); ?>"></script>
				<style type="text/css">
					.pagination { display : none; }
					<?php if((int)GETPOST('allow_gen')!=1) echo '.notInGeneration { display : none; }'; ?>

					table.liste tr.impair,table.liste tr.pair,table.liste tr.liste_titre,div.titre {
						font-size: 12px;
					}
				</style>
			</head>
		<body>
		<?php	
	}
	
	?>
	<script type="text/javascript">
        function calculateGridsterWidth()
        {
            // 4 => nombre de colonnes, 10 => CSS margin de chaque cellule, 8 => nombre de marges ( = nombre de colonnes * 2)
            let gridster_width = Math.floor(($('.gridster').innerWidth() - 8 * 10) / 4);

            // On ne va pas en dessous d'une certaine largeur
            if(gridster_width < 100) gridster_width = 100;

            return gridster_width;
        }

		var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
		var cellHeight = <?= $cell_height ?>;

		$(document).ready(function() //DOM Ready
		{
			// jQuery().gridster() retourne un objet jQuery, on chaîne un .data('gridster') pour récupérer l'instance de Gridster.js
		    gridster = $(".gridster ul").gridster({
		        widget_margins: [10, 10]
		        ,widget_base_dimensions: [calculateGridsterWidth(), cellHeight]
		        ,min_cols:4
                ,max_cols:4
		        ,min_rows:1
		        ,serialize_params: function($w, wgd) { 
		        	return { posx: wgd.col, posy: wgd.row, width: wgd.size_x, height: wgd.size_y, k : $w.attr('data-k') } 
		        }
		        <?php
		        if($action == 'edit') {
		        
		        ?>,resize: {
		            enabled: true
		            ,max_size: [4, 4]
		            ,min_size: [1, 1]
				    ,stop: function()
				    {
                        handleResizing();
                    }
		        }

		       <?php
		       }
		       ?>

		    }).data('gridster');

			<?php
				if($action == 'view')
				{
					echo 'gridster.disable();';
				}
			
			?>


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

				return false;
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
						,use_as_landing_page:$('select[name=use_as_landing_page]').val()
						,refresh_dashboard:$('input[name=refresh_dashboard]').val()
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

            $(window).on('resize', handleResizing);
            handleResizing();
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
	if($action == 'edit')
	{
		$formDoli = new Form($db);
		?>

		<table class="border centpercent">
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('Title') ?></td>
				<td><?= $form->texte('', 'title', $dashboard->title, 50,255) ?></td>
			</tr>
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('UseAsLandingPage') ?></td>
				<td><?= $formDoli->selectarray('use_as_landing_page', array($langs->trans('No'),$langs->trans('Yes')),$dashboard->use_as_landing_page) ?></td>
			</tr>
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('LimitAccessToThisGroup') ?></td>
				<td><?= $formDoli->select_dolgroups($dashboard->fk_usergroup, 'fk_usergroup', 1) ?></td>
			</tr>
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('SendByMailToThisGroup') ?></td>
				<td><?= $form->combo('', 'send_by_mail', $dashboard->TSendByMail, $dashboard->send_by_mail) ?></td>
			</tr>
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('ShowThisInCard') ?></td>
				<td><?= $form->combo('','hook', $dashboard->THook, $dashboard->hook) ?></td>
			</tr>
			<tr>
				<td class="titlefieldcreate"><?= $langs->trans('RefreshDashboard') ?></td>
				<td><?= $form->number('','refresh_dashboard', $dashboard->refresh_dashboard, 20, 1, 0) ?></td>
			</tr>
		</table>

		<div class="center"><a href="#" class="butAction" id="saveDashboard"><?php echo $langs->trans('SaveDashboard'); ?></a></div>
		<?php

	}
	else {
		if(true || ! empty($conf->global->QUERY_SHOW_PDF_TRANSFORM))	echo '<div style="text-align:right" class="notInGeneration"><a class="butAction" style=";z-index:999;" href="download-dashboard.php?uid='.$dashboard->uid.'">'.$langs->trans('Download').'</a></div>';
		
	}

	if($withHeader && $action == 'edit')
	{
		dol_fiche_end(-1);

		if ($dashboard->getId() > 0)
		{
			$TQueries = TQuery::getQueries($PDOdb);

			$morehtmlright = $form->combo('', 'fk_query', $TQueries, 0);
			$morehtmlright .= '&nbsp;<a href="#" class="butAction" id="addQuery">' . $langs->trans('AddThisQuery') . '</a>';

			print load_fiche_titre($langs->trans('Queries'), $morehtmlright, '');
		}
	}
	?>		
	
	<div class="gridster">
	    <ul>
	    	<?php
	    	foreach($dashboard->TQDashBoardQuery as $k=>&$cell) {
	    		echo '<li tile-id="'.$cell->getId().'" data-k="'.$k.'" data-row="'.$cell->posy.'" data-col="'.$cell->posx.'" data-sizex="'.$cell->width.'" data-sizey="'.$cell->height.'">';
		    		if($action == 'edit') {
		    			echo '<a style="position:absolute; top:3px; right:3px; z-index:999;" href="javascript:delTile('.$cell->getId().')">'.img_delete('DeleteThisTile').'</a>';	
		    		}
					else {
						$actionToDo = 'run-in';
						$target = '';

						if(! empty($conf->global->QUERY_DASHBOARD_OPEN_IN_NEW_TAB))
						{
							$actionToDo = 'run';
							$target = ' target="_blank"';
						}
						
						echo '<a style="position:absolute; top:3px; right:3px; z-index:999;" href="'.dol_buildpath('/query/query.php?action='.$actionToDo.'&id='.$cell->query->getId(),1).'"'.$target.'>'.img_picto($langs->trans('Run'),'object_cron.png').'</a>';
					}
					
					if($cell->query->type=='LIST')$cell->query->type='SIMPLELIST';

					$trueHeight = $cell->height * $cell_height;

					if($cell->height > 1)
					{
						$trueHeight += ($cell->height - 1) * 20;
					}

					if(!empty($cell->query)) {
						if(!$withHeader) {
							echo $cell->query->run(false, $trueHeight, $table_element, $fk_object, 0, false, true);
						}
						else{
							echo $cell->query->run(false, $trueHeight, $table_element, $fk_object, -1, false, true);
						}
						
					}
					
					
					
				
	    		echo '</li>';
				
				
	    	}
	    	
	    	?>
	        
	
	    </ul>
	</div>
	
	<div style="clear:both"></div>

<?php
	if(! $withHeader && $dashboard->refresh_dashboard > 0)
	{
		echo '
			<script type="text/javascript">
				// Automatically refresh
				$(document).ready(function()
				{
					setTimeout("window.location.reload()", ' . (60000 * $dashboard->refresh_dashboard) . ');
				});
			</script>';
	}

	
	if($withHeader)
	{
		if($dashboard->getId() > 0)
		{
			print dol_buildpath('/query/dashboard.php?action=run&uid=' . $dashboard->uid, 2);
		}

		if($action != 'edit')
		{
			dol_fiche_end(-1);
		}

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
	


