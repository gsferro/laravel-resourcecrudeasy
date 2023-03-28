/**
 * Created by Guilherme on 31/10/2016.
 */
jQuery.fn.DataTableBR = function( param )
{
	if( $( this ).last().hasClass( "runDataTable" ) ) return;

	$( this ).last().addClass( "runDataTable" );

	var option = {
		// "pagingType" : "full_numbers" ,
		"lengthMenu": [[5,10, 25, 50, 100], [5,10, 25, 50, 100]],
		"language"   : {
			"url" : "/js/datatables/js/lang/Portuguese-Brasil.json"
		} ,
		"responsive" : true//,
		// "iDisplayLength" : 25//,
		//"dom": '<"top"ifl<"clear">>rt<"bottom"ip<"clear">>'
	};

	var myParam = param || {};
	var options = $.extend( option , myParam );
	//console.log( param );

	return  $( this ).DataTable( options );
};

