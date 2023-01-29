/**
 * Created by Guilherme on
 * @autor Guilherme Ferro
 * @created 21/12/2017
 * @version 1.7.1
 * @requerid jQuery 2.2.1
 * @requerid Bootstrap 3.3.6
 * @requerid Bootstrap-validator 0.11.9
 * @description Function para colocar no form o html necessário para o plugin validator
 * @this form
 ----------
 * PADRÃO JS HTML5
 *  caso queria colocar uma msg padrão para cada elemento
 *  coloca o atributo oninvalid com a mensagem personalizada
 *  oninvalid = "setCustomValidity('teste custom')"
 *  e o atributo onchange para retirar a msg quando for preenchido o campo
 *  onchange = "try{setCustomValidity('')}catch(e){}"
 * URL: http://www.bufaloinfo.com.br/dicas.aspx?cod=1127
 * ----------
 * VIA PLUGIN VALIDATOR
 * coloque o elemento os seguintes itens
 * data-pattern-error=""
 * data-required-error=""
 * data-match-error=""
 *
 * Ou simplesmente
 * data-error=""
 *
 * URL: http://1000hz.github.io/bootstrap-validator/#validator-options
 *
 * ----------
 * @releases
 * 1.4.0 - Somente elementos :not("[hidden]")
 * 1.4.1 - caso o feedback não esteja na posição correta, o css precisa ser corrigido
 * 1.4.2 - qdo for radio ou checkbox não colocar icone feedback
 * 1.4.3 - qdo for radio ou checkbox não colocar msg de error
 * 1.4.4 - estava inserindo o html nos campos hidden
 * 1.5.0 - inputsDefaultMsg password
 * 1.5.1 - quando tiver mais de uma div no form horizontal ele so coloca o html na div que tenha o filho com required
 * 1.6.0 - ao inves se usar data-error (geral) usando data-required-error, para tratar as msgs mais genericamente
 * 	  	 - inputsDefaultMsg obsoleto
 * 	  	 - ao setar data-minlength, coloca na msg amigavel usando data-minlength-error para todos
 * 1.7.0 - Caso não tenha label colocar como form-horizontal, ou seja, embaixo do input
 * 1.7.1 - Acertando css das div container div[role="form"][data-toggle="validator"]
 * 1.8.0 - Buscando paramentros padrão via data-* | criação do plugin hasAttr para validar a existencia do attr
 */

// plugin para verificar se existe o atributo
// v1.0
$.fn.hasAttr = function( nome ) {
	return $( this ).attr( nome ) !== undefined;
};

jQuery.fn.setHtmlValidator = function( params )
{
	var params     = params || {};
	var padrao     = {
		hasFeedback     : true ,
		iconeFeedback   : true ,
		msgNavegador    : false ,
		//inputsDefaultMsg : [ 'email' , 'date', 'password' ] ,
		inputsNotMsg     : [ 'radio' , 'checkbox' ] ,
		msgDataError    : '*Obrigatório!' ,
		msgErrorInLabel : true
	};

	// 1.8 - buscando paramentros padrão via data-*
	if( $( this ).hasAttr( 'data-hasFeedback' ) && $( this ).attr( 'data-hasFeedback' ) == "false" )
		padrao.hasFeedback = false;

	if( $( this ).hasAttr( 'data-msgNavegador' ) && $( this ).attr( 'data-msgNavegador' ) == "true" )
		padrao.msgNavegador = true;

	if( $( this ).hasAttr( 'data-iconeFeedback' ) && $( this ).attr( 'data-iconeFeedback' ) == "false" )
		padrao.iconeFeedback = false;

	if( $( this ).hasAttr( 'data-msgDataError' ) )
		padrao.msgDataError = $( this ).attr( 'data-msgDataError' );

	if( $( this ).hasAttr( 'data-msgErrorInLabel' ) && $( this ).attr( 'data-msgErrorInLabel' ) == "false" )
		padrao.msgErrorInLabel = false;

	// set params default
	$.extend( padrao , params );

	// atributos necessários no form
	$( this ).attr( 'role' , 'form' ).attr( 'data-toggle' , 'validator' );

	// verificando tipo de exibição do form
	var horizontal = $( this ).hasClass( 'form-horizontal' );
	//console.log( horizontal + ' id: ' + $( this ).attr('id') );

	// varendo elementos
	$( this ).find( '[required]:not("[type=hidden]")' ).each( function( id , elem )
	{
		// elemento required obrigatório
		var input = $( elem );

		// verifica se exibe msg
		var typesNotMsg = (padrao.inputsNotMsg.indexOf( $( input ).attr( 'type' )) < 0);

		// div container form-group
		var div = $( input ).closest( 'div.form-group' );

		// coloca a div role form validator
		$( div ).wrap( '<div role="form" data-toggle="validator"></div>' );

		// coloca a class feedback
		if( padrao.hasFeedback )
			$( div ).addClass( 'has-feedback' );

		// icone de feedback
		if( padrao.iconeFeedback && typesNotMsg )
		{
			if( horizontal )
				$( div ).find( 'div:has([required])' ).append( '<span class="iconeFeedback glyphicon form-control-feedback" aria-hidden="true" style="line-height: 33px; text-align: center"></span>' );
			else
				$( div ).append( '<span class="iconeFeedback glyphicon form-control-feedback" aria-hidden="true"></span>' );
		}

		// coloca na label a div que terá a msg de erro
		if( typesNotMsg )
		{
			if( horizontal )
			{
				$( div ).find( 'div:has([required])' ).append( '<div class="help-block with-errors pull-right no-margin"></div>' );
				// 1.7.1
				$( 'div[role="form"][data-toggle="validator"]' ).css( 'display' , 'flow-root' );
			}
			else
			{
				// 1.7.0
				if( $( div ).find( 'label' ).length && padrao.msgErrorInLabel ) // existe label ou msgInLabel
				{
					$( div ).find( 'label.control-label' ).append( '<div class="help-block with-errors pull-right no-margin"></div>' );
				}
				else
				{
					$( div ).append( '<div class="help-block with-errors pull-right no-margin"></div>' );
					// 1.7.1
					$( div ).parent( 'div[role="form"][data-toggle="validator"]' ).css( 'display' , 'flow-root' );
				}
			}
		}
		// msg padrão para todos os elementos - substituindo a msg padrão
		if( !padrao.msgNavegador )
		{
			if ( $(input).attr('type') === "password")
			{
				// se tiver verificação
				if( !!$( input ).attr( 'data-match' ) )
					$( input ).attr( 'data-match-error' , "Ops... senhas não conferem!" );

				// if( !!$( input ).data( 'minlength' ) )
				// 	$( input ).attr( 'data-minlength-error' , 'Mínimo de ' + $( input ).attr( 'data-minlength' ) + ' caracteres' );
			}
                        if ( $(input).attr('type') === "email")
			{
				// se tiver verificação
				if( !!$( input ).attr( 'data-match' ) )
					$( input ).attr( 'data-match-error' , "Ops... e-mails não conferem!" );

				// if( !!$( input ).data( 'minlength' ) )
				// 	$( input ).attr( 'data-minlength-error' , 'Mínimo de ' + $( input ).attr( 'data-minlength' ) + ' caracteres' );
			}

			// v1.6.0 - trazendo para todos que tiverem o data-minlength
			// se tiver qtd minimo
			if( !!$( input ).attr( 'data-minlength' ) )
				$( input ).attr( 'data-minlength-error' , 'Mínimo de ' + $( input ).attr( 'data-minlength' ) + ' caracteres' );

			// obsoleto v1.6.0
			// var exists = padrao.inputsDefaultMsg.indexOf( $( input ).attr( 'type' ) );
			// if( exists < 0)
			//$( input ).attr( 'data-error' , padrao.msgDataError );

			// v1.6.0
			$( input ).attr( 'data-required-error' , padrao.msgDataError );
		}

		// TODO via label
		// var label = $( div ).find( 'label' ).text().replace(':','');
		// form.attr('data-error',' [ ' + label + ' ] é Obrigatório!');
	} );


	//@reliase 1.4.1
	//var iconeFeedback = $( this ).find( 'span.iconeFeedback' );
	//iconeFeedback.css('right') == "0px" && iconeFeedback.css('right','15px');
};