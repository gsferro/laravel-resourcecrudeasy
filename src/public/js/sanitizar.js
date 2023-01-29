/**
 * Created by Guilherme Ferro on 20/04/2018.
 */

$.btn = "";
function sanitizar()
{
	// open menudropdown process datatable
	$('body .dropdown-toggle').dropdown();

	// colocando btn-flat e icon em todos que tiverem .btn
	$('.btn:not(._run)').each(function ()
	{
		let elem = $(this);
		elem.addClass( '_run' );
		elem.addClass( 'btn-flat' );
		if( !elem.find( 'i' ).length ) {
			elem.text(elem.text().trim());
			let text = elem.text();
			// melhorando perfomace usando return true = continue
			// atenção a ordem: do mais usado para o menos
			if (text.match( /entrar/i )) 					{ elem.append( '<i class="fa fa-sign-in fa-padding-left fa-lg">' ); return; }
			if (text.match( /salvar/i )) 					{ elem.prepend( '<i class="fa fa-save fa-padding-right">' ); return; }
			if (text.match( /(incluir|adicionar)/i )) 		{ elem.prepend( '<i class="fa fa-plus fa-padding-right">' ); return; }
			if (text.match( /(pesquisar|filtrar|buscar)/i )){ elem.prepend( '<i class="fa fa-filter fa-padding-right">' ); return; }
			if (text.match( /fechar/i ) ) 					{ elem.prepend( '<i class="fa fa-reply fa-padding-right">' ); return; }
			if (text.match( /voltar/i )) 					{ elem.prepend( '<i class="fa fa-angle-double-left fa-padding-right">' ); return; }
			if (text.match( /avançar/i )) 					{ elem.append( '<i class="fa fa-angle-double-right fa-padding-left">' ); return; }
			if (text.match( /novo/i )) 						{ elem.prepend( '<i class="fa fa-file fa-padding-right">' ); return; }
			if (text.match( /registrar/i )) 				{ elem.prepend( '<i class="fa fa-user-plus fa-padding-right">' ); return; }
			// if (text.match( /(historico|histórico)/i ))		{ elem.prepend( '<i class="fa fa-book fa-padding-right">' ); return; }
			// if (text.match( /(relatorio|relatório)/i ))		{ elem.prepend( '<i class="fa fa-list-alt fa-padding-right">' ); return; }
			// if (text.match( /(vincular|associar)/i ))		{ elem.prepend( '<i class="fa fa-compress fa-padding-right">' ); return; }
			// if (text.match( /(frequencia|frequência)/i ))	{ elem.prepend( '<i class="fa fa-address-book fa-padding-right">' ); return; }
			// if (text.match( /imprimir/i )) 					{ elem.prepend( '<i class="fa fa-print fa-padding-right">' ); return; }
			if (text.match( /alterar|atualizar/i ))			{ elem.prepend( '<i class="fa fa-recycle fa-padding-right">' ); return; }
			// if (text.match( /informar/i ))					{ elem.prepend( '<i class="fa fa-pencil-square-o fa-padding-right">' ); return; }
			if (text.match( /limpar/i )) 					{ elem.prepend( '<i class="fa fa-eraser fa-padding-right">' ); return; }
			if (text.match( /cancelar/i )) 					{ elem.prepend( '<i class="fa fa-close fa-padding-right">' ); return; }
			// if (text.match( /verificar/i )) 				{ elem.prepend( '<i class="fa fa-eye fa-padding-right">' ); return; }
			// if (text.match( /prosseguir/i )) 				{ elem.prepend( '<i class="fa fa-arrow-circle-o-right fa-padding-right">' ); return; }
			// if (text.match( /escolher/i )) 					{ elem.prepend( '<i class="fa fa-code-fork fa-padding-right">' ); return; }
		}
	});

	// coloca a classe na label e acerta o width
	// $('label').each(function ()
	// {
	// 	!$(this).hasClass('control-label') && $(this).addClass('control-label');
	// 	!$(this).parents('form').hasClass('form-horizontal') && $(this).css('width', '100%');
	// });

	// colocando o maxlength como "2000" em todos os textarea que não tiverem setado
	$('textarea').each(function ()
	{
		!$(this)[0].hasAttribute('maxlength') && $(this).attr('maxlength', 2000);
	});

	// colocando contador de lenght em textareas
	$( 'textarea.form-control:not(._run)' ).filter( function( id , el ) {
		$( el ).addClass( '_run' );
		var name      = $( el ).attr( 'name' );
		var maxLength = parseInt( $( el ).attr( 'maxlength' ) || 2000); // assegurando que seja 2000
		var div       = $( el ).parents( 'div.form-group' );

		let ready = maxLength - $( el ).val().length;
		if( !div.find( 'p.text-muted' ).length )
			div.append( '<p class="text-muted"><small><span data-name="' + name + '">' + ready + '</span></small> caracteres restantes</p>' )

		$( el ).on( 'keyup trigger blur' , function() {
			$( 'span[data-name="' + name + '"]' ).text( maxLength - $( this ).val().length );
		} );
	} );

	// processo de colocar o datatable em todas as tables do sistema, msm na chamada ajax
	$( '.datatable:not("_run")' ).each( function( id , table ) {
		$( table ).addClass( '_run' );

		$( table ).addClass( 'table' );
		$( table ).addClass( 'table-bordered' );
		$( table ).addClass( 'table-condensed' );
		$( table ).removeClass( 'table-striped' );
		$( table ).removeClass( 'table-hover' );
		$( table ).addClass( 'tablesorter' );
		$( table ).addClass( 'tablesorter-hover' );

		// add css para exibir coluna ordenada
		$( table ).addClass( 'display' );

		// $( table ).DataTableBR();
		//
		// // coloca o pesquisar no canto, como o padrão do plugin
		// !$('.dataTables_filter').hasClass('pull-right') && $('.dataTables_filter').addClass('pull-right');
	} );
	/////////////////////////// colocando o placeholder no pesquisar do datatable
	$( 'input[type="search"]' ).each( function( id , el ) {
		!$(el).prop( 'placeholder' ) && $(el).attr( 'placeholder' , 'Pesquise aqui...' );
	} );

	// ativa o plugin validador em todos os forms
	// TODO
	$('form:not(".form-validator")').each(function (id, form)
	{
		$( form ).attr( 'autocomplete' , 'off' );
		$( form ).addClass( 'form-validator' );
		$( form ).setHtmlValidator();
		$( form ).validator( { html : true , delay : 1 } );

		/*$( form ).find( 'input:not(".input-clear")' ).each( function( id , input ) {
			$( input ).addClass( "input-clear" ).btnClear();
		} );
		*/
		/////////////////////////////////////////////
		// bloqueia o btn para evitar multiplos clicks ao salvar
		$( form ).find( 'button[type="submit"]' ).on( 'click' , function() {
			// verifica se esta habilitado e trava o button com msg de loading
			$.btn = $( this );
			// se n tiver bloqueado, libera o click e bloqueia
			if( !$.btn.hasClass( 'disabled' ) )
			{
				// ativa o load do btn
				$.btn.attr( "data-loading-text" , "<i class='fa fa-spinner fa-pulse fa-fw'></i> Processando <span id='data-loading-text'>...</span>" ).button( 'loading' );
				// reativar caso seja uma busca
				$( document ).ajaxComplete( function() {
					$.btn.button( 'reset' );
				} );
				$( 'body' ).on('click', 'button.swal2-cancel.swal2-styled', function() {
					$.btn.button( 'reset' );
				} );
			}
		} );
		/////////////////////////////////////////////
		// acertando css pos plugin validator
		$('.list-unstyled').find('li').each(function () {
			!$(this).hasClass('text-bold') && $(this).addClass('text-bold')
		});

		// TODO entrar já informando os campos obrigatórios?!
		// $( form ).validator( 'validate' );
	});
	// ajusta pesquisar datatable no canto, como o padrão do plugin
	$( '.dataTables_filter' ).addClass( 'pull-right' );
	// campos date maxlength
	$('[type="date"]').mask('0000-00-00');

	$( '.form-search' ).on( 'focus' , 'input.form-control' , function() {
		$( this ).select();
	} );

	$('.form-search').find( 'input:not(".input-clear")' ).each( function( id , input ) {
		$( input ).addClass( "input-clear" ).btnClear();
	} );

	// simulação de disabled em select
	$( 'select[readonly]' ).on( 'mousedown' , function( e ) {
		e.preventDefault();
		this.blur();
		window.focus();
		return false
	} );

	//////////// plugin jquery_alphanumeric
	// So numeros
	$('.numeric').numeric();
	// // So letras
	$('.alpha').alpha();
	// // So numeros e letras
	$('.alphanumeric').alphanumeric();

	// ajuste de CSS caso coloquei btn no head do panel
	$( '.panel-heading.clearfix:not("._run")' ).each( function( id , el ) {
		$( el ).addClass( '_run' );
		let margin = ($( el ).find( 'div.btn-group' ).hasClass( 'btn-group-sm' ) ? 4 : 6 ) + "px";
		$( el ).find( 'h4' ).css( 'margin' , margin );
	} );

	//////////////////////////////////////
	// 				select2				//
	//////////////////////////////////////
	$( 'div.content-wrapper select:not(".select2-run"):not(".selectAjax"):not("[aria-controls]")' ).each( function( id , select )
	{
		$( select ).addClass( 'select2-run' );
		// sem debugbar
		// $( select ).parents( 'div.phpdebugbar' ).length == 0 && preperSelect2( select );
	});

	// $('select:not(".select2-run"):not(".selectAjax"):not("[aria-controls]")').each( function( id , select ){
	// 	preperSelect2( select )
	// });

	// ajuste btn clear select2
	$( 'span.select2-selection__clear' )
		.empty()
		.addClass( 'fa fa-close fa-lg fa-fw' )
		.attr( 'data-toggle' , 'tooltip' )
		.attr( 'title' , 'Limpar busca' )
		.css('margin-top', '4px');

	///////////////////////////////////////
	// bloqueia o click no li disabled em qualquer aba
	$( '.nav.nav-tabs' ).find( 'li.disabled' ).on( 'click' , function( e ) {
		e.preventDefault();
		return false;
	} );

	// colocar o tooltip onde tiver titlepac
	$( '[title]' ).each( function()
	{
		$( this ).attr( 'data-toggle' ) !== "tooltip" && $( this ).attr( 'data-toggle' , 'tooltip' );
	} );
	$('[data-toggle="tooltip"]').tooltip();

};

////////////////////////////////////////////////////////
$(document).on('blur', 'input[type="text"]', function ()
{
	$(this).val($(this).val().trim()).trigger('change');
});

$(document).on('blur', 'input[type="email"]', function ()
{
	let val = $(this).val().trim();
	$(this).val('').val(val).trigger('change');
});

$(document).on('blur', 'input[type="date"]', function ()
{
	!$(this).attr('min') && $(this).attr('min', '1900-01-01');
	!$(this).attr('max') && $(this).attr('max', '2100-01-01');

	$( this ).trigger( 'change' );
});

$(document).on('blur', 'textarea', function ()
{
	$(this).val($(this).val().trim()).trigger('change');
});

// rodando o sanitizar somente no carragamento da tela ou qdo completar um ajax
$( document ).ajaxStop(function() {
	sanitizar();
});

// fechar modal
// confirmar fechar modal
$( document ).on( 'click' , '.close-modal-confirm' , function() {
	$.mySwal( 'Fechar modal?' , function() {
		modalGlobalClose()
	} );
} ).on( 'keyup' , '#modalGlobal:visible' , function( e ) {
	e.keyCode == 27 &&
	$.mySwal( 'Fechar modal?' , function() {
		modalGlobalClose()
	} );
} );

// ajax para valores isolados
function ajaxDataPost( url , args , done, fail )
{
	if( !$.isFunction( done ) ) done = function() {};
	if( !$.isFunction( fail ) ) fail = function() {};

	if( !$.isPlainObject( args ) ) args = {};
	if( !$.isPlainObject( args ) ) args = {};

	$.ajax( {
		url      : url ,
		method   : 'POST' ,
		data     : args ,
		// complete : () => $.loader()
	} ).done( () => {
		done();
	} ).fail( () => {
		fail();
	} );
}

//////////////////////////////////////// adapter swal
$.mySwal = function( title , functionYes ) {
	let text = title || "Execute a ação!";

	swal( {
		title             : 'Você tem certeza?' ,
		text              : text ,
		type              : 'warning' ,
		showCancelButton  : true ,
		allowEscapeKey    : false ,
		allowOutsideClick : false ,
		confirmButtonText : 'Sim, por favor!' ,
		cancelButtonText  : 'Não, obrigado!'
	} ).then( ( r ) => {
		// console.log( r );
		r.value && functionYes();
		r.dismiss == "cancel" && $.btn.button( 'reset' );
	} );
};

//////////////////////////////////////////////////////////////
$(function ()
{
	sanitizar();
	/* Guilherme Ferro - DE209 */
	// coloca o title onde não tiver pelo tipo de icone
	!$( '.glyphicon-pencil' ).prop( 'title' ) 		&& $( '.glyphicon-pencil' ).prop( 'title' , 'Editar' );
	!$( '.glyphicon-off' ).prop( 'title' ) 			&& $( '.glyphicon-off' ).prop( 'title' , 'Desativar' );
	!$( '.glyphicon-user' ).prop( 'title' ) 		&& $( '.glyphicon-user' ).prop( 'title' , 'Pessoa' );
	!$( '.glyphicon-download-alt' ).prop( 'title') 	&& $( '.glyphicon-download-alt' ).prop( 'title' , 'Download' );
	!$( '.glyphicon-eye-open' ).prop( 'title') 		&& $( '.glyphicon-eye-open' ).prop( 'title' , 'Visualizar dados' );
	!$( '.glyphicon-search' ).prop( 'title' ) 		&& $( '.glyphicon-search' ).prop( 'title' , 'Pesquisar dados' );

	// coloca o atritubo para receber o tooltip
	$('.glyphicon').attr("data-toggle", "tooltip");

	// TODO - Analisar impacto no sistema
	/*	$( '[type="checkbox"]' ).iCheck( {
	 checkboxClass : 'icheckbox_square-blue' ,
	 radioClass    : 'iradio_square-blue' ,
	 increaseArea  : '20%' // optional
	 } );*/
});

//////////////////////////// Comparação de datas
const checkDateInitVsEnd = (i, f) => (new Date(i) > new Date(f));

$(document).on('blur', '.dataFim', function () {
	if ($(this).val().length == 0)
		return false;
	// if( $( '.dataIni' ).val().length == 0 )

	if (checkDateInitVsEnd($('.dataIni').val(), $(this).val()))
	{
		$.toastInfo( "Data inicial maior que a data final" );
		// bootbox.alert();
		$(this).val('').trigger('change');
	}
});
//////////////////////////// TODO aba link url
/*$(function(  ) {
 var $tabs = $('a[data-toggle="tab"]');

 $tabs.on('click', function(  ) {
 // this.hash => pega o id



 let aba = this.hash.replace( '#tab_' , '' );
 // console.log( abaId );

 let pathname = window.location.pathname;
 let url      = pathname.substring( 0 , (pathname.length - 1) );

 window.location.replace(url + aba);
 console.log( url );
 })
 });*/
//////////////////////////// funções para fazer dar reload apos change jquery

function reloadDataTableAppend($table, append)
{
	// destroy
	$($table).DataTable().destroy();
	// insert
	$($table).find('tbody').append(append);
	// create
	$($table).removeClass('runDataTable');
	$($table).DataTableBR();
}

function reloadDataTableHtml($table, html)
{
	// destroy
	$($table).DataTable().destroy();
	// insert
	$($table).find('tbody').html(html);
	// create
	$($table).removeClass('runDataTable');
	$($table).DataTableBR();
}
////////////////////////////

function confereData(dataFimClass,dataIniClass)
{
	if( $( dataFimClass ).val().length == 0 )
		return false;
	// if( $( '.dataIni' ).val().length == 0 )

	if( checkDateInitVsEnd( $( dataIniClass ).val() , $( dataFimClass ).val() ) )
	{
		bootbox.alert( "Data inicial maior que a data final." );
		$( dataFimClass ).val( '' ).trigger( 'blur' );
	}
}

/**
 * Guilherme ferro
 *
 * Validar com o digito verificador todos os campos com a classe (e mask) cpf
 * */
function validarCPF(cpf)
{
	cpf = cpf.replace( /[^\d]+/g , '' );
	if( cpf.length != 11 ) return false;

	// Elimina CPFs invalidos conhecidos
	if(
		cpf == "00000000000" ||
		cpf == "11111111111" ||
		cpf == "22222222222" ||
		cpf == "33333333333" ||
		cpf == "44444444444" ||
		cpf == "55555555555" ||
		cpf == "66666666666" ||
		cpf == "77777777777" ||
		cpf == "88888888888" ||
		cpf == "99999999999" )
		return false;

	// Valida 1o digito
	let add = 0;
	for( let i = 0 ; i < 9 ; i++ )
		add += parseInt( cpf.charAt( i ) ) * (10 - i);
	let rev = 11 - (add % 11);
	if( rev == 10 || rev == 11 )
		rev = 0;
	if( rev != parseInt( cpf.charAt( 9 ) ) )
		return false;
	// Valida 2o digito
	add = 0;
	for( let i = 0 ; i < 10 ; i++ )
		add += parseInt( cpf.charAt( i ) ) * (11 - i);
	rev = 11 - (add % 11);
	if( rev == 10 || rev == 11 )
		rev = 0;
	if( rev != parseInt( cpf.charAt( 10 ) ) )
		return false;
	return true;
}

$( document ).on( 'keyup blur change' , '.cpf' , function()
{
	let $cpf = $( this );
	let cpf  = $cpf.val().replace( /[^\d]+/g , '' );

	if ( cpf.length != 11) return false;

	if( !validarCPF( cpf ) )
		$.toast( {
			heading    : 'Ops, erro de digitação!' ,
			text       : 'CPF: <strong>' + $cpf.val() +'</strong> inválido' ,
			icon       : 'error' ,
			// hideAfter  : false ,
			beforeShow : function() {
				// caso tenha o required coloca a msg de error
				$cpf.prop( 'required' ) && $cpf.attr( 'data-error' , 'CPF Inválido!' );
				$cpf.val( '' ).trigger( 'change' );
			} ,
		} );
} );
