//////////////////////////////// toasts reuso
$.toastSuccess = (( text , callback ) => {
	// executará a callback no evento afterShown
	let call = {};
	if( $.isFunction( callback ) ) ;
	call = {
		afterShown : () => callback()
	};

	$.toast( $.extend( {
		heading  : 'Sucesso!' ,
		text     : text || "Ação executada com sucesso!" ,
		icon     : 'success' ,
		position : 'top-right' ,
	} , {
		call
	} ) );
});

$.toastErro = (( text, callback ) => {
	// executará a callback no evento afterShown
	let call = {};
	if( $.isFunction( callback ) ) ;
	call = {
		beforeShow : () => callback()
	};
	$.toast( $.extend( {
		heading   : 'Oops, falhou!' ,
		text      : text || "Ação não foi realizada" ,
		icon      : 'error' ,
		hideAfter : false ,
		position  : 'top-right' ,
	} , {
		call
	} ) );
});

$.toastInfo = (( text ) => {
	$.toast( {
		heading   : 'Informação importante!' ,
		text      : text || "Atenção" ,
		icon      : 'info' ,
		hideAfter : false ,
		position  : 'top-right' ,
	} );
});

$.toastWarning = (( text ) => {
	$.toast( {
		heading   : 'Atenção!' ,
		text      : text || "Atenção" ,
		icon      : 'warning' ,
		hideAfter : false ,
		position  : 'top-right' ,
	} );
});

$.toastNotify = (( text ) => {
	$.toast( {
		text       : text || "Atenção" ,
		position   : 'bottom-right' ,
		textAlign  : 'center' ,
		stack      : 5 ,
		hideAfter  : 2000 ,
		beforeHide : () => $.toast().reset( 'all' )
	} );
});

$.toastValidate = (( errors ) => {
	console.log( errors );
	let text = [];
	$.each( errors , function( campo , msg ) {
		text.push( msg );
	} );

	$.toast( {
		heading   : 'Atenção no preenchimento:' ,
		text      : text ,
		icon      : 'error' ,
		hideAfter : false ,
		position  : 'top-right' ,
	} )
});

