setInterval( function()
{
    $('.date').mask('00/00/0000');
    $('.time').mask('00:00:00');
    $('.date_time').mask('00/00/0000 00:00:00');
    $('.cep').mask('00000-000');
    $('.phone').mask('0000-00000');

    var SPMaskBehavior = function( val )
        {
            return val.replace( /\D/g , '' ).length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        } ,
        spOptions      = {
            onKeyPress : function( val , e , field , options )
            {
                field.mask( SPMaskBehavior.apply( {} , arguments ) , options );
            }
        };
    $( '.phone_with_ddd' ).mask( SPMaskBehavior , spOptions );

    $('.phone_us').mask('(000) 000-0000');
    $('.mixed').mask('AAA 000-S0S');
    $('.cpf').mask('000.000.000-00', {
        reverse: true
    });
    $('.cnpj').mask('00.000.000/0000-00', {
        reverse: true
    });
    $('.money').mask('000.000.000.000.000,00', {
        reverse: true
    });
    $('.money2').mask("#.##0,00", {
        reverse: true
    });
    $('.ip_address').mask('0ZZ.0ZZ.0ZZ.0ZZ', {
        translation: {
            'Z': {
                pattern: /[0-9]/,
                optional: true
            }
        }
    });
    $('.ip_address').mask('099.099.099.099');
    $('.percent').mask('##0,00%', {
        reverse: true
    });
    $('.clear-if-not-match').mask("00/00/0000", {
        clearIfNotMatch: true
    });
    $('.placeholder').mask("00/00/0000", {
        placeholder: "__/__/____"
    });
    $('.fallback').mask("00r00r0000", {
        translation: {
            'r': {
                pattern: /[\/]/,
                fallback: '/'
            },
            placeholder: "__/__/____"
        }
    });
    $('.selectonfocus').mask("00/00/0000", {
        selectOnFocus: true
    });

    // campo de ano - Guilherme Ferro
	$( '.ano' ).mask( "0000" );

	/*
	* [Sanitizar]
	* Para usar somente numeros em um campo coloque a classe: numeric
	* Para usar somente Letras em um campo coloque a classe: alpha
	* Para usar somente numeros e letras em um campo coloque a classe: alphanumeric
	*  */
},100);
