// # EFECTIVO 
    // =========================================================================
    function mostrar_panel_efectivo() {
        let importePesos = $('#efectivoCobro');
        // console.log('click en efectivo',importePesos);
        importePesos.textbox('setValue', varTotalSaldo);

        // formateo el input  importe de la tarjetas
        importePesos.textbox('textbox').css('font-size', 'x-large');
        importePesos.textbox('textbox').css('color', '#9d5092');
        importePesos.textbox('textbox').css('border', '1px solid #9d5092');
        importePesos.textbox('textbox').css('height', '50px');
        // $('#totalEfectivoPesosAlta').text(numerito.format(varTotalEfectivo));
        // $('#totalReciboPesos').text(numerito.format(varTotalRecibo));
        // $('#totalAcubrirPesos').text(numerito.format(varTotalSaldo));


        $.mobile.go('#panelEfectivo', 'slide', 'right');
        importePesos.textbox('textbox').focus().select();


    }

    function mostrar_panel_dolar() {
        // $('#panelMediosCobro').hide('fast');
        // $('#panelCuerpoCheque').hide('fast');
        // $('#panelCuerpoEfectivo').hide('fast');
        // $('#panelCuerpoTransferenciaBancaria').hide('fast');
        // $('#panelCuerpoTarjetasCredito').hide('fast');
        // $('#panelCuerpoDolar').show('fast');
        // $.mobile.go('#panelEfectivoDolar', 'slide', 'right');
        let importeDolar = $('#dolarCobro');
        let cotizacion = $('#cotiDolarCobro').text(numerito.format(varCotizacion));
        // let cotizacion = varCotizacion;
        // console.log('click en efectivo',importePesos);
        importeDolar.textbox('setValue', (Math.ceil(varTotalSaldo / varCotizacion)));

        // formateo el input  importe de la tarjetas
        importeDolar.textbox('textbox').css('font-size', 'x-large');
        importeDolar.textbox('textbox').css('color', '#9d5092');
        importeDolar.textbox('textbox').css('border', '1px solid #9d5092');
        importeDolar.textbox('textbox').css('height', '50px');
        // $('#totalEfectivoDolarAlta').text(numerito.format(varTotalEfectivo));
        // $('#totalReciboDolar').text(numerito.format(varTotalRecibo));
        // $('#totalAcubrirDolar').text(numerito.format(varTotalSaldo));


        $.mobile.go('#panelEfectivoDolar', 'fade', 'left');
        importeDolar.textbox('textbox').focus().select();



    }



    // * guarda el efectivo pesos o dolar
    function acepta_efectivo(quien, moneda) {
        let botonOk, pesos, dolar, dolarPeso,
        subtotal, subtotalPesos, subtotalDolar, 
        coti, caja,controlImporte,
        pesosVal,dolarVal;
        // instanciando
        pesos = $('#efectivoCobro');
        dolar = $('#dolarCobro');
        dolarPeso = $('#dolarApeso');       
        coti = varCotizacion;
        controlImporte=0;


        // controlar que no meta valor en cero.
        

        // subtotalPesos = $('#efectivoTotalCobro');
        // subtotalDolar = $('#dolarTotalCobro');
        //caja = $('#listaCajaEfectivo');
        caja = varCajaEfectivo;
        console.info('acepta_efectivo',quien,moneda);
        if (moneda == 'pesos') {
            if (!isNaN(pesos.numberbox('getValue'))) {
                pesosVal = pesos.numberbox('getValue');
            }

            botonOk = $('#efectivo-ok');
            subtotal = parseFloat(varTotalEfectivo) + parseFloat(pesos.numberbox('getValue'));
            controlImporte = pesosVal;
        }

        if (moneda == 'dolar') {
            botonOk = $('#dolar-ok');
        
            // valor del dolar a pesos
        
            if (dolar.numberbox('getValue') !== "") {
                dolarVal = dolar.numberbox('getValue');
            }
            subtotal = parseFloat(varTotalEfectivo) + parseFloat(dolarVal);
            controlImporte= dolarVal;
        }
        console.info('control importe',controlImporte);
        if(parseFloat(controlImporte)==0){
            return false;
            Swal.fire("advertencia", "No ha ingresado Efectivo", "warning");
        }
        
        botonOk.linkbutton('disable');
        botonOk.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-fw fa-lg fa-spin"></i> Espere...'
        });
        
            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    altaEfectivo: 1,
                    idcaja: caja,
                    pesos: pesosVal,
                    dolar: dolarVal,
                    coti: coti,
                    moneda: moneda,
                    subtotal: subtotal
                },
                dataType: 'json',

                success: function(data) {
                   

                    if (data.msg === "ok") {
                        trae_totales_recibo();
                        $.mobile.go('#panelMediosCobro', 'slide', 'left');
                        // recalcular 
                        if (moneda === "pesos") {

                            Swal.fire("Hecho!", "Efectivo confirmado", "success");

                            $('#efectivo-cancel').linkbutton('enable');
                            $('#efectivo-ok').linkbutton('disable');
                            pesos.textbox('setValue',0);
                            // $('#boton-ok-efectivo').linkbutton('disable');
                            //pesos.numberbox('disable');
                            //quien.linkbutton('disable');
                            // subtotalPesos.numberbox('disable');
                        }

                        if (moneda === "dolar") {
                            Swal.fire("Hecho!", "Efectivo Moneda extranjera confirmado", "success");
                            $('#dolar-cancel').linkbutton('enable');
                            $('#dolar-ok').linkbutton('disable');
                            $('#boton-calcula-dolar').linkbutton('disable');
                            // $('#boton-ok-dolar').linkbutton('disable');
                            dolar.numberbox('disable');
                            dolar.textbox('setValue',0);
                            dolarPeso.numberbox('setValue',0);
                            // coti.numberbox('disable');
                            // subtotalDolar.numberbox('disable');
                        }


                        botonOk.linkbutton({
                            'text': '<i class="fas fa-check fa-lg fa-fw"></i> Confirmar'
                        });
                        //botonOk.attr('disabled',false);
                        botonOk.linkbutton('disable');


                    } 
                    if (data.msg !== "ok") {
                        // error 
                        Swal.fire("Oops!", "Hubo un inconveniente con el efectivo , vuelva a intentar", "error");
                        botonOk.linkbutton({
                            'text': '<i class="fas fa-check fa-lg fa-fw"></i> Confirmar'
                        });
                        botonOk.linkbutton('enable');
                    }

                }
            });
        
    }

     // consulta anttes de borrar
     function borrar_efectivo(subtotalN, tipo) {
        var subtotal = $(subtotalN);
        Swal.fire({
            title:'Está seguro?',
            text:'Se vaciará el medio de pago',
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText:'No',
            confirmButtonText:'Si!'
        }).then((resultado )=>{
            
            if(resultado.isConfirmed){
                console.log('llamando a confirmar que se borra');
                borrar_efectivo_confirmado(subtotalN,tipo);
            }
        });
    }
    // borrar el efectivo.
    function borrar_efectivo_confirmado(subtotalN, tipo) {
        console.log('me confirmaron que borre');
        var subtotal = $(subtotalN);
        //       console.log("a quien voy a bloquear=?");
        //       console.log(quien);
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                bajaEfectivo: 1,
                tipo: tipo
            },
            dataType: 'json',

            beforeSend: function() {
                $('#spinner').show('fast');
            },
            success: function(data) {
                console.log('borrar_efectivo=>',tipo);
                console.log('borrar_efectivo_resultado',data);
                if (data.msg === "ok") {
                    // recalcular 
                    if (tipo === 'dolar') {
                        console.log("pongo total dolares en cero")
                        // $('#dolarTotalCCobro').numberbox('setValue', 0);
                        $('#dolar-cancel').linkbutton('disable');
                        // $('#boton-ok-dolar').linkbutton('disable');
                        $('#boton-calcula-dolar').linkbutton('enable');
                    } 
                    if(tipo=='pesos'){
                        console.log("pongo pesos en cero ");
                        // $('#efectivoTotalCobro').numberbox('setValue', 0);
                         $('#efectivo-cancel').linkbutton('disable');
                         $('#efectivo-ok').linkbutton('enable');
                    }
                    //console.log("bloqueando el boton del quien");

                    trae_totales_recibo();

                    subtotal.numberbox('enable');
                    //subtotal.textbox({'prompt':varTotalSaldo});
                    subtotal.numberbox('textbox').focus();
                    $.mobile.go('#panelMediosCobro', 'slide', 'left');
                } else {
                    // error 
                    //                                 $('#mensajeDialog').text("hubo un inconveniente eliminacion del efectivo, vuelva a intentar.");
                    //                                 $('#dlgMensaje').dialog('open').dialog('center');
                    Swal.fire("Oops!", "hubo un inconveniente eliminacion del efectivo, vuelva a intentar.", "error");
                    //$('#listaDescuento').combobox('focus');
                }

            },
            complete: function() {
                $('#spinner').hide('fast');
            }
        });

    }








    function trae_caja_efectivo() {
        // solo los presento y si hay movimientos 
        // var selEfectivo = $('#listaCajaEfectivo');

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeCajaEfectivo: 1
            },
            dataType: 'json',
            success: function(data) {
                console.warn('caja Efectivo', 
                    data.caja[0].id
                );

                if (data.msg === "ok") {
                    // var opt = data.caja;
                    // selEfectivo.combobox({
                    //     data: opt
                    // });
                    // var opts = selEfectivo.combobox('options');
                    // console.log(opts);
                    //selEfectivo.combobox('select', items[0][opts.valueField]);
                    varCajaEfectivo = data.caja[0].id;

                } else {
                    // error 
                }

            }
        });
    }