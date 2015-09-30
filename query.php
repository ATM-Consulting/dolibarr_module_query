<?php

require('config.php');

llxHeader('', 'Query', '', '', 0, 0, array('/query/js/query.js') , array('/query/css/query.css') );

dol_fiche_head();

?>
<script type="text/javascript">
	var MODQUERY_INTERFACE = "<?php echo dol_buildpath('/query/script/interface.php',1); ?>";
</script>


<div>
	<select id="tables"></select>
	<input class="button" type="button" id="add_this_table" value="<?php echo $langs->trans('AddThisTable') ?>" />
	
	<div id="selected_tables">
		
	</div>
	
</div>

<div>
	<ul id="fields"><?php echo $langs->trans('FieldsOrder'); ?></ul>
</div>

<div id="results">
	<div>
	<?php echo $langs->trans('Fields'); ?><br />
	<textarea id="sql_query_fieds">
	</textarea>
	</div>
	
	<div>
	<?php echo $langs->trans('From'); ?><br />
	<textarea id="sql_query_from">
	</textarea>
	</div>
	
	<div>
	<?php echo $langs->trans('Where'); ?><br />
	<textarea id="sql_query_where">
	</textarea>
	</div>
</div>

<div style="clear:both"></div>

<?php

dol_fiche_end();

llxFooter();
