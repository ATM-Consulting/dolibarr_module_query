var TTable = [];
var TField = [];
var TFieldInTable = [];
var TJoin = {};
var TFieldRank = [];
var tables = '';

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
	
	$('select[name=xaxis]').val( $('select[name=xaxis]').attr('initValue') );
	
	$('#add_this_table').click(function() {
		
		t = $("#tables").val();
		
		TTable.push( t );
		TField[t] = {};
		
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
		
		var TGroup = [];
		$('#fields [sql-act="group"]').each(function(i,item) {
			if($(item).val()) {
				TGroup.push($(item).attr('field'));
			}
		});
		
		var TFunction = {}; 
		$('#fields [sql-act="function"]').each(function(i,item) {
			if($(item).val()) {
				
				TFunction[$(item).attr('field')] = $(item).val();
				
			}
		});
		
		var TTitle = {};
		$('#fields [sql-act="title"]').each(function(i,item) {
			if($(item).val()) {
				TTitle[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TSelectedField = [];
		$('input[rel=selected-field]:checked').each(function(i,item) {
				TSelectedField.push( $(item).val() );
		});
		console.log(TJoin);
		var TData= {
			'put':'query'
			,'id' : $('form#formQuery input[name=id]').val()
			,'title' : $('form#formQuery input[name=title]').val()
			,'type' : $('form#formQuery select[name=type]').val()
			,'xaxis' : $('form#formQuery select[name=xaxis]').val()
			,'TOperator' : TOperator
			,'TValue' : TValue
			,'TJoin' : TJoin
			,'TTable': TTable
			,'TOrder' : TOrder
			,'TMode' : TMode
			,'THide' : THide
			,'TTitle' : TTitle
			,'TField' : TSelectedField
			,'TGroup' : TGroup
			,'TFunction' : TFunction
			,'sql_fields' : $.base64.encode( $('textarea[name=sql_fields]').val() )
			,'sql_from' : $.base64.encode( $('textarea[name=sql_from]').val() )
			,'sql_where' : $.base64.encode( $('textarea[name=sql_where]').val() )
			,'sql_afterwhere': $.base64.encode( $('[name=sql_afterwhere]').val() )
		
		};
		console.log( TData);
		$.ajax({
			url: MODQUERY_INTERFACE
			,data:TData
			,method:'post'
			,dataType:'html'
			
		}).done(function (idQuery) {
			if(idQuery>0) {
				if(MODQUERY_QUERYID == 0) document.location.href = "?action=view&id="+idQuery;
				else showQueryPreview(idQuery);
			}
			else{
				null;
			}
		});
	});		
	
});

function showQueryPreview(idQuery) {
	
	var url='?action=preview&id='+idQuery;
	
	$('#previewRequete iframe').attr('src', url);
	
	$('#previewRequete').show();
	
}

function delTable( table ) {
	
	TTable.splice( TTable.indexOf(table), 1 );
	
	$('[table='+table+']').parent('div').remove();
	
	getTables();
	
	refresh_sql();
	
}

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
		
		$fields.append('<table class="border" width="100%" table="'+table+'"><tr class="liste_titre"><td>'+table+'<a href="#" onclick="javascript:delTable(\''+table+'\');" style="float:right">x</a></td></tr></table>');
		
		var $ul = $fields.find('table');
		
		TFieldInTable[table] = [];
		
		for (x in data) {
			
			f =  data[x].Field;
			
			TFieldInTable[table].push(table+'.'+f);
			
			$ul.append('<tr table="'+table+'" field="'+f+'"><td><input table="'+table+'" id="'+table+'-'+f+'" type="checkbox" name="'+table+'.'+f+'" value="'+table+'.'+f+'" rel="selected-field" /><label for="'+table+'-'+f+'"> '+f+' </label></td></tr>');	



										
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
		$('select[name=xaxis] option[table="'+table+'"][field="'+$(item).val()+'"]').remove();
	});
	
	$('tr[table='+table+'] input:checked ').each(function(i,item) {
		var field = $(item).val();
		TField[table].push( field );
	
		if($fields.find('div[table="'+table+'"][field="'+field+'"]').length == 0) {
			$('select[name=xaxis]').append('<option value="'+field+'" field="'+field+'" table="'+table+'">'+field+'</option>');
			
			var select_equal = '<select field='+field+' sql-act="operator"> '
						+ '<option value=""> </option>'
						
						+ '<option value="LIKE">LIKE</option>'
						+ '<option value="=">=</option>'
						+ '<option value="!=">!=</option>'
						+ '<option value="&lt;">&lt;</option>'
						+ '<option value="&lt;=">&lt;=</option>'
						+ '<option value="&gt;">&gt;</option>'
						+ '<option value="&gt;=">&gt;=</option>'
						+ '<option value="IN">IN</option>'
						+ '</select>';
						
			var select_mode	= '<select field='+field+' sql-act="mode"> '
						+ '<option value="value">valeur</option>'
						+ '<option value="var">variable</option>'
						+ '<option value="function">fonction</option>'
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
				
			var select_group	= '<select field='+field+' sql-act="group"> '
						+ '<option value=""> </option>'
						+ '<option value="1">Groupé</option>'
						+ '</select>';
				
			var select_function	= '<input type="text" size="10" field='+field+' sql-act="function" value="" /><select field='+field+' sql-act="function-select"> '
						+ '<option value=""> </option>'
						+ '<option value="SUM(@field@)">Somme</option>'
						+ '<option value="COUNT(@field@)">Nombre de</option>'
						+ '<option value="MIN(@field@)">Minimum</option>'
						+ '<option value="MAX(@field@)">Maximum</option>'
						+ '<option value="MONTH(@field@)">Mois</option>'
						+ '<option value="YEAR">Année</option>'
						+ '<option value="DATE_FORMAT(@field@, \'%m/%Y\')">Année/Mois</option>'
						//+ '<option value="FROM_UNIXTIME(@field@,\'%H:%i\')">Timestamp</option>'
						+ '<option value="SEC_TO_TIME(@field@)">Timestamp</option>'
						//+ '<option value="(@field@ / 3600)">/ 3600</option>'
						+ '</select>';
				
			
			var search = '<span table="'+table+'" field="'+f+'" class="selector"><div class="tagtd">'+select_equal+select_mode+'</div><div class="tagtd">'+select_order+select_hide+select_function+select_group+'</div></span>';

			$li = $('<div class="field table-border-row" table="'+table+'" field="'+field+'" ><div class="fieldName">'+field+' <input tytpe="text" placeholder="Title" sql-act="title" field='+field+' value="" /></div></div>');
			
			$li.append(search);
			$fields.append($li);
			
			$("select[field='"+field+"'][sql-act=function-select]").change(function() {
				$("input[field='"+field+"'][sql-act='function']").val(  $(this).val() );
				
			});
				
			
		}
			});


	$fields.find('select[sql-act=operator], input[sql-act=title], input[sql-act=value]').unbind().change( function () {
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
		
	if($fields.find('select[sql-act=mode]').val() == 'var') { $input.hide(); }

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
	
	/*	var title = $(this).find('input[sql-act=title]').val();
		console.log(title, $.trim(title));
		if($.trim(title) != '') fields+=' as "'+title+'"';
		*/	
		
	});
	
	$('#sql_query_fields').val(fields);
	
	$('#sql_query_from').val(tables);
	
	where='';
	order='';
	
	$('#fields div.field').each(function(i, item) {
		
		field = $(this).attr('field');
		operator = $(this).find('select[sql-act=operator]').val();
		sens = $(this).find('select[sql-act=order]').val();
		value = $(this).find('input[sql-act=value]').val();
		mode = $(this).find('select[sql-act=mode]').val();
		
		if(operator!='') {
			
			if(where!='') where+=' AND ';
			
			if(mode == 'function') {
				where+= field+' '+operator+' ('+value+')';
			}
			else{
				where+= field+' '+operator+' :'+field.replace(".", "_"); 	
			}
			
			
		}

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
