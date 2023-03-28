/**
 * Created by Guilherme on 01/03/2019.
 * Adapter para uso do plugin Datatable proccess server side
 *
 * dependencia DataTableBR
 *
 * @requires ajax.url
 * @requires columns
 */

jQuery.fn.DataTableProccess = function( param ) {

	var columnDefs = {
		"columnDefs" : [
			{
				"render"  : function( data , type , row ) {
					if ( row[ "DT_RowData" ].actions )
						return row[ "DT_RowData" ].actions;

					return false;
				} ,
				"targets" : -1
			} ,
		]
	}
	//console.log( row );

	var option = {
		"processing"     : true ,
		"serverSide"     : true ,
		"FixedColumns"   : true ,
		"iDisplayLength" : 10 ,
		"searching"      : false ,
	};

	var myParam = param || {};

	// caso n tenha btns de ação
	// ex https://stackoverflow.com/questions/2281633/javascript-isset-equivalent
	if( !myParam.hasOwnProperty('columnDefs')) // TODO
	{
		option = $.extend( option , columnDefs );
	}

	var options = $.extend( option , myParam );

	return $( this ).DataTableBR( options);
};