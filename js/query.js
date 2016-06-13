var TTable = [];
var TField = [];
var TFieldForCombo = [];
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
	
	if(MODQUERY_EXPERT!=1) {
		getTables();
		
		$('#fieldsview').sortable({
			 items: "> div.field"
		  	 ,stop: function( event, ui ) { 
		  	 	refresh_sql();  
		  	 }
		});
		
	}
	
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
		$('#fieldsview [sql-act="hide"]').each(function(i,item) {
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

		var TTotal = {};
		$('#fieldsview [sql-act="total"]').each(function(i,item) {
			if($(item).val()) {
				var f = $(item).attr('field');
				if($(item).val() == 'groupsum') {
					TTotal[f] = [ 'groupsum' , $('#fieldsview [field="'+f+'"][sql-act="field-total-group"]').val()];
				}
				else{
					TTotal[f] = $(item).val();	
				}
				
			}
		});
		
		var TFunction = {}; 
		$('#fields [sql-act="function"]').each(function(i,item) {
			if($(item).val()) {
				
				TFunction[$(item).attr('field')] = $(item).val();
				
			}
		});
		
		var TTitle = {};
		$('#fieldsview [sql-act="title"]').each(function(i,item) {
			if($(item).val()) {
				TTitle[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TSelectedField = [];
		$('[sql-act=title]').each(function(i,item) {
				TSelectedField.push( $(item).attr('field') );
		});
		
		var TTranslate = {};
		$('#fieldsview [sql-act="translate"]').each(function(i,item) {
			if($(item).val()) {
				TTranslate[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TFilter = {};
		$('#fields [sql-act="filter"]').each(function(i,item) {
			if($(item).val()) {
				TFilter[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TType = {};
		$('#fieldsview [sql-act="type"]').each(function(i,item) {
			if($(item).val()) {
				TType[$(item).attr('field')] = $(item).val();
			}
		});
		
		var TClass = {};
		$('#fieldsview [sql-act="class"]').each(function(i,item) {
			if($(item).val()) {
				TClass[$(item).attr('field')] = $(item).val();
			}
		});
		
		if(MODQUERY_QUERYID == 0) {
			var TData= {
				'put':'query'
				,'id' : $('form#formQuery input[name=id]').val()
				,'title' : $('form#formQuery input[name=title]').val()
				,'type' : $('form#formQuery select[name=type]').val()
	
	
				,'xaxis' : $('form#formQuery select[name=xaxis]').val()
			};
			
		}
		else {
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
				,'TTranslate' : TTranslate
				,'TGroup' : TGroup
				,'TTotal' : TTotal
				,'TFunction' : TFunction
				,'TFilter' : TFilter
				,'TType' : TType
				,'TClass' : TClass
				,'sql_fields' :  btoa( $('textarea[name=sql_fields]').val() )
				,'sql_from' : btoa( $('textarea[name=sql_from]').val() )
				,'sql_where' : btoa( $('textarea[name=sql_where]').val())
				,'sql_afterwhere': btoa( $('[name=sql_afterwhere]').val() )
			};
			
		}

		$.post(MODQUERY_INTERFACE, TData, function (idQuery) {
			if(idQuery>0) {
				if(MODQUERY_QUERYID == 0) document.location.href = "?action=view&id="+idQuery;
				else showQueryPreview(idQuery);
				
				$.jnotify('Saved');
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

function refresh_field_param(field, table) {
	
	var $fields = $('#fields');
	var $fieldsView = $('#fieldsview');
	
	
			$('select[name=xaxis]').append('<option value="'+field+'" field="'+field+'" table="'+table+'">'+field+'</option>');
			
			var search = '<span table="'+table+'" field="'+field+'" class="selector"><div class="tagtd">'+select_equal+select_mode+select_filter+'</div><div class="tagtd">'+select_order+select_function+select_group+'</div></span>';

			$li = $('<div class="field table-border-row" table="'+table+'" field="'+field+'" ><div class="fieldName">'+field+'</div></div>');
			
			$li.append($(search).find('input,select').attr('field', field));
			$fields.append($li);
			
			$('select[field="'+field+'"][sql-act=function-select]').change(function() {
				$("input[field='"+field+"'][sql-act='function']").val(  $(this).val() );
				
			});
				
			$liView = $('<div class="field" table="'+table+'" field="'+field+'" ><div class="fieldName">'+field+'</div> <input tytpe="text" placeholder="Title" sql-act="title" field='+field+' value="" /></div>');
			$liView.append('<input type="text" placeholder="Translation (value:translation, ...)" sql-act="translate" field='+field+' value="" />');
			$liView.append(select_type+select_hide+select_total+select_total_group_field+select_class);
			$liView.find('input,select').attr('field', field);
			
			$fieldsView.append($liView);
			
			$fieldsView.find('select[sql-act="class-select"]').unbind().change( function () {
				var field = $(this).attr('field');
				var $input = $fieldsView.find('input[field="'+field+'"][sql-act="class"]');
				var value = $(this).val();
				
				$input.val(value);
			});
			
			$fields.find('select[sql-act=mode]').unbind().change( function () {
			
				var field = $(this).attr('field');
				
				var $input = $fields.find('input[field="'+field+'"][sql-act="value"]');
				var $filter= $fields.find('select[field="'+field+'"][sql-act="filter"]');
				//console.log($input, field, $(this).val());
				if($(this).val() == 'var') {
					if(MODQUERY_EXPERT != 1) { $input.hide(); }
					$filter.show();
				}
				else{
					if(MODQUERY_EXPERT != 1) { $input.show();}
					$filter.hide();
				}
				
				refresh_sql();
				
			}).change();
			
			$fieldsView.find('select[sql-act=total]').unbind().change(function() {
				var field = $(this).attr('field');
				
				TFieldForCombo.push(field);
				
				var $input = $fieldsView.find('select[field="'+field+'"][sql-act="field-total-group"]');
				
				if($(this).val() == 'groupsum') {
					$input.show();
				}
				else{
					$input.hide();
				}
				
			}).change();
			
			if(MODQUERY_EXPERT == 1) {
				$fields.find('select[sql-act=operator],input[sql-act=value]').hide();
			}
			
}

function refresh_field_array(table) {
	TField[table] = [];
	
	//console.log('refresh_field_array:'+table);
	var $fields = $('#fields');
	var $fieldsView = $('#fieldsview');
	//$fields.find('li[table='+table+']').remove();
	
	$('tr[table='+table+'] input').not(':checked').each(function(i,item) {
		$fields.find('div[table="'+table+'"][field="'+$(item).val()+'"]').remove();
		$fieldsView.find('div[table="'+table+'"][field="'+$(item).val()+'"]').remove();
		$('select[name=xaxis] option[table="'+table+'"][field="'+$(item).val()+'"]').remove();
	});
	
	$('tr[table='+table+'] input:checked ').each(function(i,item) {
		var field = $(item).val();
		TField[table].push( field );
	
		if($fields.find('div[table="'+table+'"][field="'+field+'"]').length == 0) {
			refresh_field_param(field, table);
		}	});
	
	if(MODQUERY_EXPERT == 1) {
		null;
	}
	else {
		
			
		if($fields.find('select[sql-act=mode]').val() == 'var') { $input.hide(); }
	
		$fields.find('select[sql-act=operator], input[sql-act=title], input[sql-act=value]').unbind().change( function () {
			refresh_sql();
		});
		
		
	
		refresh_sql();
		
	}

}

function refresh_sql() {
	
	
	if(MODQUERY_EXPERT == 1) return false;
	
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
			else if(operator=='=') {
				null;
			}
			else{
				where+= field+' '+operator+' ( :'+field.replace(".", "_")+' ) '; 	
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
		
		var l = MODQUERY_PREFIX.length;
		
		$tables = $("#tables");
		
		$tables.empty();
		
		for (x in data) {
			//console.log(data[x], jQuery.inArray(data[x], TTable));
			if(jQuery.inArray(data[x], TTable) == -1 ) {
				$tables.append('<option value="'+data[x]+'">'+data[x].substring(l)+'</option>');	
			}
			
			
		}
		
		
	});
	
}
