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
		global $user, $conf;

		if(($parameters['currentcontext'] == 'projectcard'
		|| $parameters['currentcontext'] == 'productcard'
		|| $parameters['currentcontext'] == 'thirdpartycard'
		|| $parameters['currentcontext'] == 'usercard')
		&& $action == ''
		&& empty($conf->global->QUERY_NO_DASHBOARD_ON_CARDS)
		&& !empty($user->rights->query->dashboard->viewin)) {

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

    	/**
	 * Overloading the addOpenElementsDashboardLine function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function addOpenElementsDashboardLine($parameters, &$object, &$action, $hookmanager) {
        global $conf;
		// we use this hook starting from 14.0 instead of addStatisticLine
        if(in_array('index', explode(':', $parameters['context'])) && ! empty($conf->global->QUERY_HOME_SELECTOR)) {
            if(version_compare(DOL_VERSION, '14.0', '>=')) {
                define('INC_FROM_DOLIBARR', true);
                dol_include_once('/query/config.php');
                dol_include_once('/query/class/dashboard.class.php');
                dol_include_once('/query/class/query.class.php');
                dol_include_once('/query/class/dashboard.class.php');
                TQDashBoard::injectIndexQueryDashbord();
            }
        }

		return 0;
	}

	function addStatisticLine($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$db, $user, $conf,$query_just_after_login;

		if (in_array('index', explode(':',$parameters['context'])) && ! empty($conf->global->QUERY_HOME_SELECTOR)) {
            if(version_compare(DOL_VERSION, '14.0', '<')) {
                define('INC_FROM_DOLIBARR', true);
                dol_include_once('/query/config.php');
                dol_include_once('/query/class/dashboard.class.php');
                dol_include_once('/query/class/query.class.php');
                dol_include_once('/query/class/dashboard.class.php');
                TQDashBoard::injectIndexQueryDashbord();
            }
		}

		return 0;
	}

}
