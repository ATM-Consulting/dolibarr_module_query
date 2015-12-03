#!/usr/bin/php
<?php

	chdir(__DIR__);

	define('INC_FROM_CRON_SCRIPT',true);
	require 'config.php';
	
	dol_include_once('/user/class/usergroup.class.php');
	dol_include_once('/core/class/CMailFile.class.php');
	
	//dol_include_once('/query/lib/Wkhtmltopdf.php');
	$PDOdb=new TPDOdb;
	
	if(!is_dir('files')) mkdir('files',0777);
	
	$frequence = $argv[1];
	if(empty($frequence)) $frequence = 'DAY'; 
	$step = $argv[2];
	if(empty($step)) $step = 1;
	
	$sql="SELECT uid, title, fk_usergroup,fk_user_author FROM ".MAIN_DB_PREFIX."qdashboard WHERE send_by_mail = '".$frequence."' AND fk_usergroup>0";
	$Tab = $PDOdb->ExecuteAsArray($sql);
	$f1 = fopen('files/convert.sh','w');
	fputs($f1,"cd ".__DIR__."/files \n");
	
	foreach($Tab as $row) {
		
		$author = new User($db);
		$author->fetch($row->fk_user_author);
		
		if($step == 1) {
			$url = dol_buildpath('/query/dashboard.php?action=run&uid='.$row->uid,2);
			fputs($f1, "wkhtmltopdf --javascript-delay 1000 --orientation Landscape".escapeshellarg($url)." ".$row->uid.".pdf  \n");
		
		}
		elseif($step == 2) {
			$g=new UserGroup($db);
			if($g->fetch($row->fk_usergroup)>0) {
				$TUser = $g->listUsersForGroup();
				foreach($TUser as &$u) {
					if($u->statut == 1) {
						
						$mailto = $u->email;
						
						if(!empty($mailto)) {
							print "$mailto \n";	
							
							$m=new TReponseMail($author->email,$mailto,$langs->trans('Report').' : '.$row->title,$langs->trans('PleaseFindYourReportHasAttachement'));
							$m->add_piece_jointe($row->uid.'.pdf',  dol_buildpath('/query/files/'.$row->uid.'.pdf') );
							$m->send();
								
						}
						
					}
				}
				
			}
			
			unlink(dol_buildpath('/query/files/'.$row->uid.'.pdf')); // suppresion du pdf après envoi par mail
		}
	}
	fclose($f1);
	
	
	
	//$temp_file = tempnam(sys_get_temp_dir(), 'QRY_');
	/*
	try {
        $wkhtmltopdf = new Wkhtmltopdf(array('path' => sys_get_temp_dir()));
        $wkhtmltopdf->setTitle("Query");
        $wkhtmltopdf->setUrl($url);
        $wkhtmltopdf->output(Wkhtmltopdf::MODE_SAVE, "file.pdf");
    } catch (Exception $e) {
        echo $e->getMessage();
    }*/
/*
	echo (int)dol_include_once('/query/lib/vendor/autoload.php');
	

	use mikehaertl\wkhtmlto\Pdf;

	
	$pdf = new Pdf();
	
	// On some systems you may have to set the binary path.
	// $pdf->binary = 'C:\...';
	print $temp_file;
	if (!$pdf->saveAs($temp_file)) {
	    echo $pdf->getError();
	}
*/