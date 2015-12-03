<?php
class ActionsQuery
{ 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */
      
    function addStatisticLine($parameters, &$object, &$action, $hookmanager) 
    {  
      	global $langs,$db, $user;
		
		if (in_array('index',explode(':',$parameters['context']))) 
        {
        	
			$sql="SELECT qd.uid as 'uid', qd.title 
				FROM ".MAIN_DB_PREFIX."qdashboard qd
				WHERE uid!='' ";
			
			if($user->admin) {
				null;
			}
			else {
				$sql.=" AND (qd.fk_user_author=".$user->id." OR  qd.fk_usergroup IN (SELECT fk_usergroup FROM ".MAIN_DB_PREFIX."usergroup_user WHERE fk_user=".$user->id." ) )";
			}
			
        	?>
        	<script type="text/javascript">
        		$(document).ready(function() {
        			
        			$select = $('<div class="titre"><?php echo $langs->trans('QueryDashBoard'); ?></div><select name="qdashboardList"><option value=""> </option><?php
						$res = $db->query($sql);
						while($obj = $db->fetch_object($res)) {
							echo '<option value="'.$obj->uid.'">'.$obj->title.'</option>';
						}
        			?></select><div id="queryDashboardview"></div>');
        			
        			
        			$select.change(function() {
        				
        				var uid = $(this).val();
        				$('#queryDashboardview').empty();
        				
        				if(uid!='') {
        					var url="<?php echo dol_buildpath('/query/dashboard.php',1) ?>?action=run&uid="+uid;
        					$('#queryDashboardview').html('<iframe src="'+url+'" width="100%" frameborder="0" onload="this.height = this.contentWindow.document.body.scrollHeight + \'px\'"></iframe>');
        				}
        			});
        			
        			$('table#otherboxes').before($select);
        			
        			
        			
        		});
        		
        	</script>
        	<?php
		}
		
		return 0;
	}
     
}