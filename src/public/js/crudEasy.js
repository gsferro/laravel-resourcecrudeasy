// qq form que tiver a classe sera transformado em ajax
$(document).on('submit', 'form.formInModal', function(e) {
    // não avança caso não tenha validado todos os itens
    if (e.isDefaultPrevented()) return;
    // captura o form html
    e.preventDefault();

    // chama o adapter
    $(this).crudEasy({
        // test
        // confirmation  : true ,
        // reload        : true ,
        // loaderMessage : 'dados' ,
        // swal_question : 'dados' ,
        // method        : 'dados' , 
        // redirect      : 'dados'
    });
});

// chama em cima do form que esta sendo postada
$.fn.crudEasy = function(_dados) {
    /////////////////////////////////////////////////////////////////
    // encapsulando o form
    var form = $(this);
    // opcional enviar os dados
    let dados = _dados || {};
    // encapsula os dados
    let data = form.objectize();
    // console.log( data );

    /////////////////////////////////////////////////////////////////
    // upload de arquivos
    let upload = {};
    if (form.attr('enctype') === "multipart/form-data") {
        upload = {
            cache: false,
            enctype: "multipart/form-data",
            contentType: false,
            processData: false,
        };

        let name = form.find('input[type="file"]').attr('name');
        var file = form.find('input[type="file"]')[0].files || null;
        var fd = new FormData(); // [name="arquivo"]

        // encapsulado callback caso exista
        data["_callback"] && $.extend(dados, { callback: data["_callback"] });

        // recoloca todos os campos dentro do fd
        $.each(data, function(id, val) {
            if (id === name && file[0] !== undefined)
                fd.append(name, file[0]);
            else
                fd.append(id, val);
        });

        // coloca em data
        data = fd;
        // console.log( "after each " );
        // console.log( data );
    }

    /////////////////////////////////////////////////////////////////
    // encapuslando msg do loader via data- | dados | data
    let loaderMessage = !!dados.loaderMessage ? dados.loaderMessage :
        (!!data["loader-message"] ? data["loader-message"] :
            (form.attr('data-loader-message') || ""));

    // console.log( "loaderMessage " + loaderMessage );
    /////////////////////////////////////////////////////////////////
    // encapuslando reload via data- | dados | data
    let reload = !!dados.reload ? true :
        (!!data["reload"] ? true :
            !!form.attr('data-reload'));

    // console.log( "reload " + reload );
    /////////////////////////////////////////////////////////////////
    // encapuslando reload via data- | dados | data
    let method = !!dados.method ? dados.method :
        (!!data["_method"] ? data["_method"] :
            (form.attr('method') || "GET"));

    // console.log( "method " + method );
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    // encapuslando reload via data- | dados | data
    let callback = !!dados.callback ? dados.callback :
        (!!data["_callback"] ? data["_callback"] :
            (form.attr('data-callback') || false));

    // console.log( "callback " + dados.callback );
    // console.log( "callback " + data[ "_callback" ] );
    // console.log( "callback " + form.attr( 'data-callback' ) );
    // console.log( "callback " + callback );
    /////////////////////////////////////////////////////////////////

    // encapuslando redirect via data- | dados | data
    let redirect = !!dados.redirect ? dados.redirect :
        (!!data["_redirect"] ? data["_redirect"] :
            (form.attr('data-redirect') || false));

    if (redirect == false) {
        var url = new URL(window.location);
        let pathname = url.pathname.split('/');

        redirect = '/' + pathname[1];
    }

    // console.log( "callback " + dados.callback );
    // console.log( "callback " + data[ "_callback" ] );
    // console.log( "callback " + form.attr( 'data-callback' ) );
    // console.log( "callback " + callback );
    /////////////////////////////////////////////////////////////////


    // montando o ajax
    let ajax = $.extend({
        url: form.attr('action'),
        method: method,
        data: data,
        // beforeSend : () => $.loader( loaderMessage ) ,
        // complete   : () => $.loader() ,
    }, dados, upload);

    // console.log( ajax );

    /////////////////////////////////////////////////////////////////
    // encapuslando a chamada ajax
    let ajaxRun = () => {
        $.ajax(ajax)
            .done((r) => {
                setTimeout(function() {
                    if (!callback) {
                        function myaction() {
                            // reloadAbaAtiva();
                            location.replace(redirect);
                        }
                        $.toastSuccess(r.message, myaction());
                    } else
                        $.toastSuccess(r.message, window[callback](r));

                    reload && location.reload();
                }, 100);

            }).fail((res) => {
                let r = res.responseJSON;
                // TODO perdeu a sessão
                r.code == 401 && location.replace('/sessionExpire');

                if (r.code == 422) // erro validação
                    $.toastValidate(r.data);
                else
                    $.toastErro(r.message);
            });
    };

    /////////////////////////////////////////////////////////////////
    // confirmação swal - default false
    let confirmation = !!dados.confirmation ? true :
        (!!data["confirmation"] ? true :
            !!form.attr('data-confirmation'));
    //console.log( "confirmation " + confirmation );
    /////////////////////////////////////////////////////////////////
    // run
    if (!confirmation) {
        // executa direto
        ajaxRun();
    } else {
        /////////////////////////////////////////////////////////////////
        // encapsulando a pergunta vinda via data- | dados | data
        let swalQuestion = !!dados.swal_question ? dados.swal_question :
            (!!data["swal-question"] ? data["swal-question"] :
                (form.attr('data-swal-question') || ""));
        //console.log( "swalQuestion " + swalQuestion );

        // pergunta e executa
        $.mySwal(swalQuestion, ajaxRun);
    }
};

//////////////////////////////////////// serealização para envio ajax
$.fn.objectize = function() {
    let selector = "select, textarea, input:not([type='reset']):not([type='submit']):not([type='button']):not([type='radio']), input[type='radio']:checked";
    let result = {};
    let _fetchval = function(obj) {
        if (obj.is("input[type='checkbox']"))
            return !!obj.is(":checked");
        else {
            if (obj.is("input.money"))
                return sanitizeMoney(obj.val());
            else
                return sanitizeStr(obj.val());
        }
    };

    let sanitizeStr = function(str) {
        if (typeof str != "string") str = String(str);
        return str.replace(/\u0009/g, "");
    };
    let sanitizeMoney = function(valor) {
        if (valor === "") {
            valor = 0;
        } else {
            valor = valor.replace(".", "");
            valor = valor.replace(",", ".");
            valor = parseFloat(valor);
        }
        return valor;
    };

    let extend = function(i, _element) {
        let element = $(_element);
        let node = result[element.attr("name")];

        let myval = _fetchval(element);
        if (myval === false) return;

        if ('undefined' !== typeof node && node !== null) {
            if ($.isArray(node))
                node.push(myval);
            else
                result[element.attr("name")] = [node, myval];
        } else {
            result[element.attr("name")] = myval;
        }
    };
    let targets = $(this).filter(selector);
    targets = targets.add($(this).find(selector));
    $.each(targets, extend);
    return result;
};