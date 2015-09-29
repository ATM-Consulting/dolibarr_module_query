<?php

require('config.php');

llxHeader();

?>
<div>
	<select id="tables"></select>
	
</div>
<script type="text/javascript">
	$(document).ready(function() {
		
		$.ajax({
			url:'script/interface.php'
			,data:{
				get:'tables'
			}
			,dataType:'json'
		}).done(function(data) {
			
			$tables = $("#tables");
			
			$tables.empty();
			
			for (x in data) {
				
				$tables.append('<option>'+data[x]+'</option>');
				
			}
			
			
		});
		
	});
	
	
</script>

<?php

llxFooter();
