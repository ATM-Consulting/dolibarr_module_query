var TTable = [];
var TField = [];
var TFieldInTable = [];
var TJoin = [];

$(document).ready(function() {
	
	/* for test */
		TTable.push( 'llx_user' );
		TField['llx_user'] = [];
		
		drawFieldTables( 'llx_user' );
		TTable.push('llx_usergroup_user' );
		TField['llx_usergroup_user'] = [];
		
		drawFieldTables( 'llx_usergroup_user' );
	
	getTables();
	
	/* for test */
	
	$('#add_this_table').click(function() {
		
		t = $("#tables").val();
		
		TTable.push( t );
		TField[t] = [];
		
		drawFieldTables( t );
		
		getTables();
		
	});
	
	
	
	
});


function drawFieldTables( table ){
	
	$.ajax({
		url: MODQUERY_INTERFACE
		,data:{
			get:'fields'
			,table : table
		}
		,dataType:'json'
	}).done(function(data) {
		
		var $fields = $('<div class="fields" />');
		$fields.append('<ul table="'+table+'">'+table+'</ul>');
		
		$ul = $fields.find('ul');
		
		TFieldInTable[table] = [];
		
		for (x in data) {
			
			f =  data[x].Field;
			
			TFieldInTable[table].push(table+'.'+f);
			
			$ul.append('<li table="'+table+'" field="'+f+'"><input table="'+table+'" id="'+table+'-'+f+'" type="checkbox" name="'+table+'.'+f+'" value="'+table+'.'+f+'" /><label for="'+table+'-'+f+'"> '+f+' </label></li>');	



			var select_equal = '<select field='+f+' sql-act="operator"> '
						+ '<option value="&lt;">&lt;</option>'
						+ '<option value="&lt;">&gt;</option>'
						+ '<option value="=">=</option>'
						+ '<option value="LIKE">LIKE</option>'
						+ '</select>';
						
			var select_mode	= '<select field='+f+' sql-act="mode"> '
						+ '<option value="value">valeur</option>'
						+ '<option value="var">variable</option>'
						+ '</select> <input field='+f+' type="text" value="" sql-act="value" />';
				
			
			var search = '<span class="selector">'+select_equal+select_mode+'</span>';

				
			$ul.find('li[field="'+f+'"]').append(search);
							
		}
		
		addJointure(table);
		
		$ul.find('input[type=checkbox]').click( function () {
			
			if($(this).is(':checked')) {
				console.log($(this).closest('li').find('span.selector').html());
				$(this).closest('li').find('span.selector').css('display','block');	
			}
			else{
				$(this).closest('li').find('span.selector').css('display','none');
			}
			
			
			
			refresh_field_array($(this).attr('table'));
		});
		
		$ul.find('select[sql-act=mode]').click( function () {
			
			field = $(this).attr('field');
			
			$input = $ul.find('input[field='+f+'][sql-act=value]');
			
			if($(this).val() == 'variable') {
				$input.val=':'+f;
				$input.attr('disabled','disabled');
			}
			else{
				$input.removeAttr('disabled');
			}
			
		});
		
		
		$('#selected_tables').append($fields);
		
	});
	
}

function addJointure(table) {
	
	if(TTable[0] == table) return false;
	
	for(x in TTable) {
		
		if(TTable[x] == table) {
			if(typeof TTable[x-1] != 'undefined') previous_table = TTable[x-1];
			else return false;
		} 
		
	}
	
	var $join = $('<div class="jointure" />');
	
	$select_t1 = $('<select name="jointure_'+table+'" jointure="'+table+'" />');
	$select_t2 = $('<select name="jointure_'+table+'_to" jointure-to="'+table+'" />');
	
	$select_t1.change(function() {
		TJoin[table] = [ $('select[jointure='+table+']').val() , $('select[jointure-to='+table+']').val() ];
	});
	$select_t2.change(function() {
		TJoin[table] = [ $('select[jointure='+table+']').val() , $('select[jointure-to='+table+']').val() ];
	});
	
	for(x in TFieldInTable[table]) {
		f = TFieldInTable[table][x];
		$select_t1.append('<option value="'+f+'">'+f+'</option>');
	}
	
	for(t in TFieldInTable) { 
		if(t!=table) {
			
			for(x in TFieldInTable[t]) {
				f = TFieldInTable[t][x];
				$select_t2.append('<option value="'+f+'">'+f+'</option>');
			}
			
		}	
	}
	
	
	TJoin[table] = [ TFieldInTable[table][0] , TFieldInTable[previous_table][0] ];
	
	$join.append($select_t1);
	$join.append($select_t2);
	
	$('#selected_tables').append($join);
	
	refresh_sql();
}

function refresh_field_array(table) {
	
	TField[table] = [];
	//console.log('refresh_field_array:'+table);
	$('ul li[table='+table+'] input:checked ').each(function(i,item) {
		TField[table].push( $(item).val() );	});

	refresh_sql();
}

function refresh_sql() {
	
	
	fields = '';
	tables = '';
	
	for(t in TField) {
		
		if(typeof TJoin[t] != 'undefined') {
			tables+=' LEFT JOIN ';
		}
		
		tables += t;
		
		if(typeof TJoin[t] != 'undefined') {
			tables+=' ON ('+TJoin[t][0]+'='+TJoin[t][1]+') ';	
		}
		
		if(TField[t].length>0) {
			if(fields!='') fields+=',';
			 fields+=TField[t].join(',') ;	
			
		}
	}
	
	$('#sql_query_fieds').val(fields);
	
	$('#sql_query_from').val(tables);
	
}

function getTables() {
	
	$.ajax({
		url:MODQUERY_INTERFACE
		,data:{
			get:'tables'
		}
		,dataType:'json'
	}).done(function(data) {
		
		$tables = $("#tables");
		
		$tables.empty();
		
		for (x in data) {
			//console.log(data[x], jQuery.inArray(data[x], TTable));
			if(jQuery.inArray(data[x], TTable) == -1 ) {
				$tables.append('<option>'+data[x]+'</option>');	
			}
			
			
		}
		
		
	});
	
}
