<?php

require 'config.php';

llxHeader();

if(empty($user->rights->query->bdd->write)) {
	accessforbidden();
}

		$url = 'lib/adminer.php?';
		
		$url.='&server='.$dolibarr_main_db_host;
		$url.='&db='.$dolibarr_main_db_name;
		$url.='&username='.$dolibarr_main_db_user;
		$url.='&password='.$dolibarr_main_db_pass;
		$url.='&driver='.$dolibarr_main_db_type;

?>

	<a href="<?php echo $url; ?>" class="butAction" target="_blank">Accès à la base de données</a>
	
<?php


llxFooter();
