<?php

	define('INC_FROM_CRON_SCRIPT',true);
	require 'config.php';
	
	dol_include_once('/query/lib/Wkhtmltopdf.php');
	
	$url = dol_buildpath('/query/dashboard.php?action=run&uid='.GETPOST('uid'),2);
	//$temp_file = tempnam(sys_get_temp_dir(), 'QRY_');
	
	try {
        $wkhtmltopdf = new Wkhtmltopdf(array('path' => sys_get_temp_dir()));
        $wkhtmltopdf->setTitle("Query");
        $wkhtmltopdf->setUrl($url);
        $wkhtmltopdf->output(Wkhtmltopdf::MODE_SAVE, "file.pdf");
    } catch (Exception $e) {
        echo $e->getMessage();
    }
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