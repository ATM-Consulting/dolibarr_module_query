<?php

class TQDashBoard extends TObjetStd {

	function __construct() {
        global $langs;

        parent::set_table(MAIN_DB_PREFIX.'qdashboard');
        parent::add_champs('fk_user,fk_user_author,fk_usergroup',array('type'=>'integer','index'=>true));
		parent::add_champs('uid,send_by_mail,hook',array('index'=>true));

		parent::add_champs('refresh_dashboard,use_as_landing_page',array('type'=>'integer'));

        parent::_init_vars('title');
        parent::start();

		$this->setChild('TQDashBoardQuery', 'fk_qdashboard');

		$this->TSendByMail=array(
			''=>$langs->trans('No')
			,'DAY'=>$langs->trans('EveryDay')
			,'WEEK'=>$langs->trans('EveryWeek')
			,'MONTH'=>$langs->trans('EveryMonth')
		);

		$this->THook = array(
			''=>$langs->trans('No')
			,'projectcard'=>$langs->trans('Project')
			,'productcard'=>$langs->trans('Product')
			,'thirdpartycard'=>$langs->trans('Thirdparty')
			,'usercard'=>$langs->trans('User')
		);

    }

    public static function injectIndexQueryDashbord() {
        global $langs, $user, $db, $conf, $query_just_after_login;
        $langs->load('query@query');

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

			$defaultDashboard = getDolGlobalString('QUERY_HOME_DEFAULT_DASHBOARD_USER_'.$user->id);

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

	function save(&$PDOdb) {
		global $user;

		if(empty($this->fk_user_author)) $this->fk_user_author = $user->id; // creator
		$this->fk_user = $user->id; //  last updater
		if(empty($this->uid))$this->uid = md5( time().$this->title.rand(1000,999999) );

		return parent::save($PDOdb);
	}

	static function getDashboard(&$PDOdb, $hook = '', $user = null, $withTitle = false)
	{
		$Tab = array();

		$sql = '
				SELECT rowid, uid, title, refresh_dashboard
				FROM ' . MAIN_DB_PREFIX . 'qdashboard qd
				WHERE TRUE';

		if(! empty($hook))
		{
			$sql .= '
				AND qd.hook = ' . $PDOdb->quote($hook);
		}

		$sql.= static::getUserRightsSQLFilter($user);

		$sql.= '
				ORDER BY title';

		$Tab = TRequeteCore::_get_id_by_sql($PDOdb, $sql, ($withTitle ? 'title' : 'uid'), 'rowid');

		return $Tab;
	}

	public static function getUserRightsSQLFilter($user)
	{
		if(empty($user) || ! empty($user->admin) || ! empty($user->rights->dashboard->readall))
		{
			return '';
		}

		return '
			AND (
				qd.fk_user_author = ' . intval($user->id) . '
				OR
				COALESCE(qd.fk_usergroup, 0) <= 0
				OR
				qd.fk_usergroup IN (
					SELECT fk_usergroup
					FROM ' . MAIN_DB_PREFIX . 'usergroup_user
					WHERE fk_user = '  . intval($user->id) .'
				)
			)';
	}
}

class TQDashBoardQuery extends TObjetStd {
	function __construct() {
        global $langs;

        parent::set_table(MAIN_DB_PREFIX.'qdashboard_query');
        parent::add_champs('fk_qdashboard,fk_query',array('type'=>'integer','index'=>true));
		parent::add_champs('width,height,posx,posy',array('type'=>'integer'));
        parent::_init_vars('title');
        parent::start();

    	$this->query=null;
		$this->width=$this->height=$this->posx=$this->posy = 1;
    }

	function load(&$PDOdb, $id, $loadChild = true) {

		parent::load($PDOdb, $id);

		if($this->fk_query>0) {

			$this->query = new TQuery;
			$this->query->load($PDOdb, $this->fk_query);

		}

	}


}
