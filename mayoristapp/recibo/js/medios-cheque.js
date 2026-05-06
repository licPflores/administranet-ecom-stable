// # CHEQUES
    //=========================================================================





    // alta de cheque de tercero.
    function mostrar_panel_cheques() {

        trae_lista_cheques();
        trae_totales_recibo();
        // $('#aCubrirPanelCheques').text(numerito.format(varTotalSaldo));
        // $('#totalReciboPanelCheques').text(numerito.format(varTotalRecibo));
        // $('#totalPanelCheques').text(numerito.format(varTotalCheque));
        $.mobile.go('#panelCuerpoCheque', 'slide', 'right');

    }
    /*
        $('#chImporte').numberbox('textbox').bind('keydown',function(e){
            if(e.keyCode==13){
                //$('#app_charge2').numberbox.focus();
                console.info('que tecla soy',event.which);
            }

        });*/
    
        function trae_caja_cheque() {
            // solo los presento y si hay movimientos 
            // var selCheque = $('#listaCajaCheque');
    
            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    traeCajaCheque: 1
                },
                dataType: 'json',
                success: function(data) {
                    console.warn('caja cheque', 
                        data.caja[0]
                    );
    
    
                    if (data.msg === "ok") {
                        // var opt = data.caja;
                        // selCheque.combobox({
                        //     data: opt
                        // });
                        varCajaCheque = data.caja[0].id;
    
                    } else {
                        // error 
                    }
    
                }
            });
        }


    function mostrar_panel_alta_cheque() {
        let saldoCh = varTotalSaldo;
        let numeroCheque = $('#chNumero');
        let importeCheque = $('#chImporte');
        let listaBancos = $('#listaBancos');

        //$('#saldoAltaCheque').html('<strong>$' + numerito.format(saldoCh) + '</strong>');
        importeCheque.textbox('textbox').css('font-size', 'x-large');
        importeCheque.textbox('textbox').css('color', '#f75d69');
        importeCheque.textbox('textbox').css('border', '1px solid #f75d69');
        importeCheque.textbox('textbox').css('height', '50px');
        importeCheque.textbox('setValue', varTotalSaldo);

        $.mobile.go('#panelAltaCheque', 'slide', 'left');
        // panel de estado
        $('#totalChequeAlta').text(numerito.format(varTotalCheque));
        $('#totalReciboChequeAlta').text(numerito.format(varTotalRecibo));
        $('#totalAcubrirChequeAlta').text(numerito.format(varTotalSaldo));
        // numeroCheque.numberbox('clear').numberbox('textbox').focus();
        listaBancos.combobox('textbox').focus();

    }



    // traer los Bancos 
    function trae_bancos() {
        // solo los presento y si hay movimientos 
        var bancos = $('#listaBancos');

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeBancos: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    var opt = data.banco;
                    bancos.combobox({
                        data: opt
                    });

                } else {
                    // error 
                }

            }
        });
    }

    // bancos    
    $('#listaBancos').combobox({
        onSelect: function(row) {
            console.log("dentro del banco");
            console.log(row);
            var cuitBanco = $('#chBancoCuit');
            cuitBanco.maskedbox('setValue', row.cuit);
            var librador = $('#chLibrador');
            //                librador.textbox('textbox').focus();
            $('#chFechaEmision').datebox('textbox').focus();
            // obtener el campo del monto y total del recibo.
        }
    });
    // tipo de cheque normal o electronico.

    // alta de cheque
    function alta_cheque() {
        var boton = $('#botonGuardarCheque');

        var banco, codbanco, cuitbanco,
            librador, cuitlibrador, importe, numero,
            emision, vencimiento, cobro, tipoCheque;

        var limiteValor = varTotalSaldo;
        var mensaje = "debe completar todos los datos del cheque.";
        console.log('-----q opciones de tipo de cheque');
        // tipo chque vacio.
        tipoCheque = '';
        // tipo de cheque normal o elecronico
        $('a.easyui-linkbutton').each(function() {
            var opts = $(this).linkbutton('options');


            if (opts.selected && opts.group === "gCheque") {


                console.log(opts.id + ' selected');

                // veo que fue seleccionado. si normal o electronico.
                if (opts.id == "Normal") {
                    tipoCheque = "Normal";
                }
                if (opts.id == "Electronico") {
                    tipoCheque = "Electronico";
                }
            }

        });
        console.log({
            tipoCheque
        });

        //console.log([$('.easyui-linkbutton')]);
        // return false;
        // desabilito el boton
        boton.linkbutton('disable');
        boton.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> ProcesandoEspere...'
        });
        //boton.unbind('click');
        //        boton.linkbutton('disabled');
        //$('#spinner').show();


        banco = $('#listaBancos').combobox('getText');
        codbanco = $('#listaBancos').combobox('getValue');
        cuitbanco = $('#chBancoCuit').maskedbox('getText');

        librador = $('#chLibrador');
        cuitlibrador = $('#chCuitLibrador');
        importe = $('#chImporte');
        numero = $('#chNumero');
        emision = fechaReves($('#chFechaEmision').datebox('getValue'));
        //console.log("fecha cobro;"+$('#chFechaCobro').datebox('getValue'));

        //vencimiento=fechaReves();
        cobro = fechaReves($('#chFechaCobro').datebox('getValue'));
        // controlar los vacios y los que son requeridos.

        console.log(cuitlibrador.maskedbox('getText'));
        console.log(cuitlibrador.maskedbox('getValue'));
        var errores = 0;
        if (codbanco === "") {
            errores++;
        }
        if (librador.textbox('getValue') === "") {
            errores++;
        }
        if (cuitlibrador.maskedbox('getText') === "") {
            errores++;
        }
        if (numero.numberbox('getValue') === "") {
            errores++;
        }
        if (importe.numberbox('getValue') === "") {
            errores++;
        }
        if (emision === "") {
            errores++;
        }

        if (cobro === "") {
            errores++;
        }
        if (tipoCheque == '') {
            errores++;
        }
        //        

        if (errores > 0) {

            Swal.fire("advertencia!", "Debe completar todos los campos", "warning");
        } else {
            // mandar el ajax

            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    altaCheque: 1,
                    codbanco: codbanco,
                    banco: banco,
                    cuitbanco: cuitbanco,
                    librador: librador.textbox('getValue'),
                    cuitlibrador: cuitlibrador.maskedbox('getText'),
                    numero: numero.numberbox('getValue'),
                    importe: importe.numberbox('getValue'),
                    emison: emision,
                    cobro: cobro,
                    tipo: tipoCheque

                },
                dataType: 'json',
                success: function(data) {

                    console.log('resultado operacion alta chque=>', {
                        data
                    });
                    if (data.msg === "ok") {
                        boton.linkbutton({
                            'text': '<i class="fas fa-check fa-fw fa-lg"></i> Aceptar'
                        });
                        boton.linkbutton('enable');

                        mostrar_panel_cheques();
                        Swal.fire({
                            title: 'Muy bien',
                            position: 'top',
                            html: 'Se agrego el cheque Nro: <strong>' + numero.numberbox('getValue') + '</strong><br> por <strong>$' + importe.numberbox('getValue') + '</strong>',
                            icon: "success"
                        });

                        $('#listaBancos').combobox('clear');
                        $('#chBancoCuit').maskedbox('clear');

                        //                            $('#chImporte').numberbox('clear');
                        //                            $('#chNumero').numberbox('clear');
                        $('#chImporte').textbox('clear');
                        $('#chNumero').textbox('clear');
                        $('#chFechaEmision').datebox('clear');

                        $('#chFechaCobro').datebox('clear');

                        //$('#spinner').hide();
                    }
                }
            });
        }
    }

    // funcion para eliminar un cheque
    function borrar_cheque() {
        // abrir la dialog que se lo asegura.
        //$('#spinner').show();

        var select = $('#tblCheques').datagrid('getSelected');
        if (select === null) {
            Swal.fire("Advertencia", "Debe seleccionar un cheque de la lista para borrar", "warning");
            return false;
        }

        Swal.fire({
            title: 'Advertencia',
            html: '¿Esta seguro de eliminar el <br>cheque <strong>#' + select.numero + '</strong> <br> por <strong>$' + select.importe + '</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Aceptar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log("dentr del ok sweet alert.");

                $.ajax({
                    type: 'GET',
                    url: 'ajax/json_recibo.php',
                    data: {
                        borraCheque: 1,
                        cod: select.cod,
                        numero: select.numero,
                        importe: select.importe
                    },
                    dataType: 'json',
                    success: function(data) {

                        if (data.msg === "ok") {
                            trae_totales_recibo();
                            trae_lista_cheques();
                        }
                    },
                });
            }
        });



    }


    function trae_lista_cheques() {
        var tabla = $('#tblCheques');
        var cajaCheque = $('#listaCajaCheque');
        var cajaCheque = varCajaCheque;

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                listaCheques: 1,
                idCaja: cajaCheque
            },
            dataType: 'json',

            success: function(data) {
                if (data.msg === "ok") {
                    var opt = data.cheques;
                    // console.log(opt);
                    tabla.datagrid({
                        singleSelect: true,
                        fit: false,
                        fitColumns: true,
                        border: true,
                        scrollbarSize: 0,

                        columns: [
                            [{
                                    field: 'cod',
                                    title: 'Cod',
                                    width: 120,
                                    hidden: true
                                },
                                {
                                    field: 'banco',
                                    title: 'Banco',
                                    width: 100
                                },
                                {
                                    field: 'numero',
                                    title: 'Número',
                                    width: 100
                                },
                                {
                                    field: 'importe',
                                    title: 'Importe',
                                    width: 80
                                }

                            ]
                        ],
                        // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
                        data: opt

                    });
                }
                if (data.msg === "vacio") {
                    if (tabla.data('datagrid')) {
                        // no initialization
                        tabla.datagrid('loadData', []);
                    }
                }

            }
        });


    }
