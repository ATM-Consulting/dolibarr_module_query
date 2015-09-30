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
		
		$fields.append('<table class="border" width="100%" table="'+table+'"><tr class="liste_titre"><td>'+table+'</td></tr></table>');
		
		var $ul = $fields.find('table');
		
		TFieldInTable[table] = [];
		
		for (x in data) {
			
			f =  data[x].Field;
			
			TFieldInTable[table].push(table+'.'+f);
			
			$ul.append('<tr table="'+table+'" field="'+f+'"><td><input table="'+table+'" id="'+table+'-'+f+'" type="checkbox" name="'+table+'.'+f+'" value="'+table+'.'+f+'" /><label for="'+table+'-'+f+'"> '+f+' </label></td></tr>');	



			var select_equal = '<select field='+f+' sql-act="operator"> '
						+ '<option value=""> </option>'
						
						+ '<option value="LIKE">LIKE</option>'
						+ '<option value="=">=</option>'
						+ '<option value="&lt;">&lt;</option>'
						+ '<option value="&lt;">&gt;</option>'
						+ '</select>';
						
			var select_mode	= '<select field='+f+' sql-act="mode"> '
						+ '<option value="value">valeur</option>'
						+ '<option value="var">variable</option>'
						+ '</select> <input field='+f+' type="text" value="" sql-act="value" />';
				
			var select_order	= '<select field='+f+' sql-act="order"> '
						+ '<option value=""> </option>'
						+ '<option value="ASC">Ascendant</option>'
						+ '<option value="DESC">Descendant</option>'
						+ '</select>';
				
			
			var search = '<span table="'+table+'" field="'+f+'" class="selector"><br />'+select_equal+select_mode+'<br />'+select_order+'</span>';

				
			$ul.find('tr[field="'+f+'"] td').append(search);
							
		}
		
		addJointure($ul, table);
		
		$ul.find('input[type=checkbox]').click( function () {
			
			$condition = $(this).closest('tr').find('span.selector');
			
			if($(this).is(':checked')) {
			//	console.log($(this).closest('li').find('span.selector').html());
				$condition.css('display','block');
				$condition.attr('sql-where',1);	
			}
			else{
				$condition.css('display','none');
				$condition.attr('sql-where',0);
			}
			
			refresh_field_array($(this).attr('table'));
		});
		
		
		$ul.find('select[sql-act=operator]').change( function () {
			refresh_sql();
		});
		
		$ul.find('select[sql-act=mode]').change( function () {
			
			field = $(this).attr('field');
			
			var $input = $ul.find('input[field="'+field+'"][sql-act="value"]');
			//console.log($input, $(this).val());
			if($(this).val() == 'var') {
				$input.hide();
			}
			else{
				$input.show();
				
			}
			
			refresh_sql();
			
		});
		
		
		$('#selected_tables').append($fields);
		
	});
	
}

function addJointure($obj, table) {
	
	if(TTable[0] == table) return false;
	
	for(x in TTable) {
		
		if(TTable[x] == table) {
			if(typeof TTable[x-1] != 'undefined') previous_table = TTable[x-1];
			else return false;
		} 
		
	}
	
	var $join = $('<table class="border jointure" width="100%"><tr class="liste_titre"><td>Jointure</td></tr><tr><td rel="from"></td></tr><tr><td rel="to"></td></tr></table>');
	
	$select_t1 = $('<select name="jointure_'+table+'" jointure="'+table+'" />');
	$select_t2 = $('<select name="jointure_'+table+'_to" jointure-to="'+table+'" />');
	
	$select_t1.change(function() {
		TJoin[table] = [ $('select[jointure='+table+']').val() , $('select[jointure-to='+table+']').val() ];
		refresh_sql();
	});
	$select_t2.change(function() {
		TJoin[table] = [ $('select[jointure='+table+']').val() , $('select[jointure-to='+table+']').val() ];
		refresh_sql();
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
	
	$join.find('[rel=from]').append($select_t1);
	$join.find('[rel=to]').append($select_t2);
	
	//$('#selected_tables').append($join);
	
	$obj.before($join);
	
	refresh_sql();
}

function refresh_field_array(table) {
	
	TField[table] = [];
	//console.log('refresh_field_array:'+table);
	$('tr[table='+table+'] input:checked ').each(function(i,item) {
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
	
	where='';
	order='';
	
	$('span[sql-where=1]').each(function(i, item) {
		
		
		field = $(this).attr('field');
		operator = $(this).find('select[sql-act=operator]').val();
		sens = $(this).find('select[sql-act=order]').val();
		
		if(operator!='') {
			
			if(where!='') where+=' AND ';
			
			where+= field+' '+operator+' :'+field;
			
		}
		
/*		if(sens!='') {
			
			if(order!='')order+=',';
			
			order+=field +' '+sens
			
		}
*/		
		
	});
	
	$('#sql_query_where').val(where);
	
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
