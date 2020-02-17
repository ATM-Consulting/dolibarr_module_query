<?php
class ActionsQuery
{
	/** Overloading the doActions function : replacing the parent's function with the one below
	 *  @param      parameters  meta datas of the hook (context, etc...)
	 *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 *  @param      action             current action (if set). Generally create or edit or null
	 *  @return       void
	 */

	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {
		global $user;

		if(($parameters['currentcontext'] == 'projectcard'
		|| $parameters['currentcontext'] == 'productcard'
		|| $parameters['currentcontext'] == 'thirdpartycard'
		|| $parameters['currentcontext'] == 'usercard')
		&& $action == '' && !empty($user->rights->query->dashboard->viewin)) {

			define('INC_FROM_DOLIBARR',true);
			dol_include_once('/query/config.php');
			dol_include_once('/query/class/dashboard.class.php');
			dol_include_once('/query/class/query.class.php');

			$PDOdb=new TPDOdb;
			$TDash = TQDashBoard::getDashboard($PDOdb, $parameters['currentcontext'], $user);

			foreach($TDash as $uid) {
				$url = dol_buildpath('/query/dashboard.php?action=run&allow_gen=1&uid='.$uid.'&table_element='.$object->table_element.'&fk_object='.$object->id,1);

				?>
				<tr>
					<td colspan="2">
						<iframe src="<?php echo $url ?>" id="queryIframe" width="100%" frameborder="0" style="overflow: hidden"></iframe>
					</td>
				</tr>
				<?php

			}

		}
	}

	function addStatisticLine($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$db, $user, $conf,$query_just_after_login;

		if (in_array('index', explode(':',$parameters['context'])) && ! empty($conf->global->QUERY_HOME_SELECTOR))
		{
			$langs->load('query@query');

			define('INC_FROM_DOLIBARR',true);
			dol_include_once('/query/config.php');
			dol_include_once('/query/class/dashboard.class.php');
			dol_include_once('/query/class/query.class.php');

			$sql = '
					SELECT qd.uid as uid, qd.title, qd.use_as_landing_page, qd.rowid
					FROM ' . MAIN_DB_PREFIX . 'qdashboard qd
					WHERE CHAR_LENGTH(uid) > 0';

			$sql .= TQDashBoard::getUserRightsSQLFilter($user);

			$TDashboard = array();
			$res = $db->query($sql);
			if (!$res) {
				setEventMessage($db->lasterror, 'errors');
			} else {
				while($obj = $db->fetch_object($res))
				{
					if($obj->use_as_landing_page == 1 && ! empty($query_just_after_login))
					{
						?>
						<script type="text/javascript">
						document.location.href="<?php echo dol_buildpath('/query/dashboard.php?action=run&id='.$obj->rowid,1) ?>";
						</script>
						<?php
						exit;
					}

					$TDashboard[] = $obj;
				}
			}

			$defaultDashboard = $conf->global->{ 'QUERY_HOME_DEFAULT_DASHBOARD_USER_'.$user->id };

			$select = '';
			$messageForEmptyBox = '<p style="text-align: center">' . $langs->trans('NoQueryDashBoard') . '</p>';

			if(! empty($TDashboard))
			{
				$messageForEmptyBox = '<p style="text-align: center">' . $langs->trans('QueryDashBoard') . '</p>';

				$selected = empty($defaultDashboard) ? ' selected' : '';
				$select = '
					<select name="qdashboardList">
			        	<option value=""' . $selected . '>&nbsp;</option>';

				foreach($TDashboard as &$obj)
				{
					$selected = $defaultDashboard == $obj->uid ? ' selected' : '';
					$select .= '
						<option value="' . $obj->uid  . '"' . $selected . '>' . $obj->title . '</option>';
				}

				$select .= '
					</select>';
			}

			$boxHTML = '
				<div class="box">
			        <table class="noborder boxtable" width="100%" summary="Query">
			            <tr class="liste_titre">
			                <th class="liste_titre">' . $langs->trans('Module104778Name') . '</th>
			                <th class="liste_titre" align="right">' . $select . '</th>
						</tr>
						<tr>
							<td colspan="2" class="impair">
								<div id="queryDashboardview">' . $messageForEmptyBox . '</div>
							</td>
						</tr>
					</table>
				</div>';


			?>
			<script type="text/javascript">
				$(document).ready(function() {

				    let $box = $('<?= dol_escape_js($boxHTML, 1) ?>');

					<?php
					if(DOL_VERSION >= 6.0) {
						?> $('.fichehalfleft .box').first().after($box);<?php
					}
					elseif(DOL_VERSION>=4.0) {
						?> $('#left').before($box);<?php
					}
					else{
						?> $('table#otherboxes').before($box); <?php
					}
					?>

                    let $select = $box.find('select[name=qdashboardList]').first();

                    if($select)
                    {
                        $select.on('change', function ()
                        {
                            let uid = $(this).find(":selected").val();

                            $('#queryDashboardview').empty();

                            if (uid && uid.length > 0)
                            {
                                var url = "<?= dol_buildpath('/query/dashboard.php', 1) ?>?action=run&allow_gen=1&storechoice=1&fk_user=<?= $user->id ?>&uid=" + uid;
                                $('#queryDashboardview').html('<iframe id="queryIframe" src="' + url + '" width="100%" frameborder="0" style="overflow:hidden"></iframe>');
                            }
                            else
                            {
                                $('#queryDashboardview').html('<?= dol_escape_js($messageForEmptyBox, 1) ?>');
                            }
                        }).trigger('change');
                    }
				});

			</script>
			<?php
		}

		return 0;
	}

}
