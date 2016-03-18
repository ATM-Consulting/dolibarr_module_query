<?php

	require 'config.php';
	
	dol_include_once('/query/class/query.class.php');
	
	$PDOdb = new TPDOdb;
	
	$langs->load('query@query');
	
	$action = GETPOST('action');
	
	$object=new TQueryMenu;
	
	switch ($action) {
		case 'save':
			$object->load($PDOdb, GETPOST('id'));
			
			if(GETPOST('bt_delete')!='') {
				$object->delete($PDOdb);
				setEventMessage('MenuDeleted');
			}
			else{
				$object->set_values($_POST);
				$object->save($PDOdb);
				setEventMessage('MenuSaved');				
			}
			
			
			_list($PDOdb);
			
			break;
		case 'edit':
			$object->load($PDOdb, GETPOST('id'));
			_card($PDOdb, $object);
			
			break;
		case 'add':	
			_card($PDOdb, $object);
			break;
		default:
		
			_list($PDOdb);
			
			break;
	}	

function _card(&$PDOdb, &$object) {
	
	global $langs, $conf,$user,$db;
	
	llxHeader();
	dol_fiche_head(array(),'menu','Menu');

	$formCore=new TFormCore('auto','formMenu','post');
	echo $formCore->hidden('action', 'save');
	echo $formCore->hidden('id', $object->getId());
	
	$tbs=new TTemplateTBS;
	echo $tbs->render('tpl/menu.html',array(),array(
		'menu'=>array(
			'mainmenu'=>$formCore->combo('', 'mainmenu', TQueryMenu::getMenu($PDOdb, 'main'), $object->mainmenu)
			,'leftmenu'=>$formCore->combo('', 'leftmenu', TQueryMenu::getMenu($PDOdb, 'left'), $object->leftmenu)
			,'fk_query'=>$formCore->combo('', 'fk_query', TQuery::getQueries($PDOdb), $object->fk_query,0," $('#title').val( $(this).find(':selected').text() ) ")
			,'title'=>$formCore->texte('','title', $object->title,80,255)
			,'perms'=>$formCore->texte('','perms', $object->perms,80,255)
		)
		,'view'=>array(
			'langs'=>$langs
			,'buttons'=>$formCore->btsubmit($langs->trans('Delete'), 'bt_delete','','butActionDelete').' &nbsp; '. $formCore->btsubmit($langs->trans('Save'), 'bt_save')
		)
	));
	
	$formCore->end();
	
	dol_fiche_end();
	llxFooter();
	
}

function _list(&$PDOdb) {
	global $langs, $conf,$user,$db;
	
	llxHeader();
	dol_fiche_head(array(),'menu','Menu');
	
	
	$l=new TListviewTBS('lMenu');
	$sql = "SELECT rowid, mainmenu,leftmenu,title,date_cre 
	FROM ".MAIN_DB_PREFIX."query_menu 
	WHERE entity IN (0,".$conf->entity.")";
	
	
	
	echo $l->render($PDOdb, $sql,array(
	
		'title'=>array(
			'title'=>$langs->trans('Title')
			,'leftmenu'=>$langs->trans('LeftMenu')
			,'mainmenu'=>$langs->trans('MainMenu')
			,'date_cre'=>$langs->trans('Date')
		)
		,'link'=>array(
			'title'=>'<a href="?id=@rowid@&action=edit">@val@</a>'
		)
		,'hide'=>array('rowid')
		,'type'=>array(
			'date_cre'=>'date'
		)
		
	));
	
	
	/*$kiwi = new TKiwi;
	$kiwi->fk_soc = $object->id;
	$kiwi->fk_product = 1;
	$kiwi->save($PDOdb);
	*/
	// pied de page 
	dol_fiche_end();
	llxFooter();
	
}
