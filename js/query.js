var TTable = [];
var TField = [];
var TFieldInTable = [];
var TJoin = [];
var TFieldRank = [];

$(document).ready(function() {
	
	_init_query();
	
	/* for test ----
		TTable.push( 'llx_user' );
		TField['llx_user'] = [];
		
		drawFieldTables( 'llx_user' );
		
		TTable.push('llx_usergroup_user' );
		TField['llx_usergroup_user'] = [];
		drawFieldTables( 'llx_usergroup_user' );
	
		TTable.push('llx_usergroup' );
		TField['llx_usergroup'] = [];
		drawFieldTables( 'llx_usergroup' );
	
		
	
	---- for test */
	
	getTables();
	
	$('#fields').sortable({
		 items: "> div.field"
	  	 ,stop: function( event, ui ) { refresh_sql();  }
	});
	
	$('#add_this_table').click(function() {
		
		t = $("#tables").val();
		
		TTable.push( t );
		TField[t] = [];
		
		drawFieldTables( t );
		
		getTables();
		
	});
	
	$('#save_query').click(function() {
		
		var TOperator = {};
		$('#fields [sql-act="operator"]').each(function(i,item) {
			if($(item).val()) {
				TOperator[$(item).attr('field')] = $(item).val();
				
			}
		});
		
		var TMode = {};
		$('#fields [sql-act="mode"]').each(function(i,item) {
			if($(item).val()) {
				
				TMode[$(item).attr('field')] = $(item).val();
				
			}
		});
		
		var TOrder = {};
		$('#fields [sql-act="order"]').each(function(i,item) {
			if($(item).val()) {
				
				TOrder[$(item).attr('field')] = $(item).val();
				
			}
		});
		
		var TValue = {};
		$('#fields [sql-act="value"]').each(function(i,item) {
			if($(item).val()) {
				TValue[$(item).attr('field')] = $(item).val();
			}
		});
		
		var THide = {};
		$('#fields [sql-act="hide"]').each(function(i,item) {
			if($(item).val()) {
				THide[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TData= {
			'put':'query'
			,'id' : $('form#formQuery input[name=id]').val()
			,'title' : $('form#formQuery input[name=title]').val()
			,'TOperator' : TOperator
			,'TValue' : TValue
			,'TJoin' : TJoin
			,'TTable': TTable
			,'TOrder' : TOrder
			,'TMode' : TMode
			,'THide' : THide
			,'sql_fields' : $('textarea[name=sql_fields]').val()
			,'sql_from' : $('textarea[name=sql_from]').val()
			,'sql_where' : $('textarea[name=sql_where]').val()
		
		};
		console.log( TData);
		$.ajax({
			url: MODQUERY_INTERFACE
			,data:TData
			,method:'post'
			,dataType:'html'
			
		}).done(function (data) {
			$('form#formQuery input[name=id]').val(data);
		});
	});		
	
});

function addTable( table ){
	
	TTable.push( table );
	TField[table] = [];
		
	drawFieldTables( table );
}
function drawFieldTables( table ){
	
	$.ajax({
		url: MODQUERY_INTERFACE
		,data:{
			get:'fields'
			,table : table
		}
		,async:false
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



										
		}
		
		addJointure($ul, table);
		
		$ul.find('input[type=checkbox]').click( function () {
			refresh_field_array($(this).attr('table'));
		});
		
		
		$('#selected_tables').append($fields);
		
	});
	
}

function checkField( field ) {
	$input = $("input[type=checkbox][name='"+field+"']");
	
	$input.prop("checked",true);
	refresh_field_array($input.attr('table'));
	
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
	var $fields = $('#fields');
	//$fields.find('li[table='+table+']').remove();
	
	$('tr[table='+table+'] input').not(':checked').each(function(i,item) {
		$fields.find('div[table="'+table+'"][field="'+$(item).val()+'"]').remove();
	});
	
	$('tr[table='+table+'] input:checked ').each(function(i,item) {
		var field = $(item).val();
		TField[table].push( field );
		
		if($fields.find('div[table="'+table+'"][field="'+field+'"]').length == 0) {
			
			
			var select_equal = '<select field='+field+' sql-act="operator"> '
						+ '<option value=""> </option>'
						
						+ '<option value="LIKE">LIKE</option>'
						+ '<option value="=">=</option>'
						+ '<option value="!=">!=</option>'
						+ '<option value="&lt;">&lt;</option>'
						+ '<option value="&lt;">&gt;</option>'
						+ '<option value="IN">IN</option>'
						+ '</select>';
						
			var select_mode	= '<select field='+field+' sql-act="mode"> '
						+ '<option value="value">valeur</option>'
						+ '<option value="var">variable</option>'
						+ '</select> <input field='+field+' type="text" value="" sql-act="value" />';
				
			var select_order	= '<select field='+field+' sql-act="order"> '
						+ '<option value=""> </option>'
						+ '<option value="ASC">Ascendant</option>'
						+ '<option value="DESC">Descendant</option>'
						+ '</select>';
				
			var select_hide	= '<select field='+field+' sql-act="hide"> '
						+ '<option value=""> </option>'
						+ '<option value="1">Caché</option>'
						+ '</select>';
				
			
			var search = '<span table="'+table+'" field="'+f+'" class="selector"><div class="tagtd">'+select_equal+select_mode+'</div><div class="tagtd">'+select_order+select_hide+'</div></span>';

			$li = $('<div class="field table-border-row" table="'+table+'" field="'+field+'" ><div class="fieldName">'+field+'</div></div>');
				
			$li.append(search);
			$fields.append($li);
			
		}
			});


	$fields.find('select[sql-act=operator]').unbind().change( function () {
		refresh_sql();
	});
	
	$fields.find('select[sql-act=mode]').unbind().change( function () {
		
		var field = $(this).attr('field');
		
		var $input = $fields.find('input[field="'+field+'"][sql-act="value"]');
		//console.log($input, field, $(this).val());
		if($(this).val() == 'var') {
			$input.hide();
		}
		else{
			$input.show();
			
		}
		
		refresh_sql();
		
	});
		

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
		
	}

	$('#fields div.field').each(function(i,item) {
		if(fields!='') fields+=',';
	 	fields+=$(item).attr('field');	
		
	});
	
	$('#sql_query_fields').val(fields);
	
	$('#sql_query_from').val(tables);
	
	where='';
	order='';
	
	$('#fields div.field').each(function(i, item) {
		
		field = $(this).attr('field');
		operator = $(this).find('select[sql-act=operator]').val();
		sens = $(this).find('select[sql-act=order]').val();
		
		if(operator!='') {
			
			if(where!='') where+=' AND ';
			
			where+= field+' '+operator+' :'+field.replace(".", "_"); ;
			
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
		,async:false
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
