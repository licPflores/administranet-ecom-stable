//  *#TRANSFERENCIA BANCARIA 
    // =====================================================================================================
    function mostrar_alta_transferencia() {
        // $('#panelMediosCobro').hide('fast');
        // $('#panelCuerpoEfectivo').hide('fast');
        // $('#panelCuerpoDolar').hide('fast');
        // $('#panelCuerpoCheque').hide('fast');
        // $('#panelCuerpoTarjetasCredito').hide('fast');
        trae_cuentas_bancarias();
        let importeTransferencia = $('#transfImporteAlta');
        importeTransferencia.textbox('textbox').css('font-size', 'x-large');
        importeTransferencia.textbox('textbox').css('color', '#ff7c43');
        importeTransferencia.textbox('textbox').css('border', '1px solid #ff7c43');
        importeTransferencia.textbox('textbox').css('height', '50px');

        // panel de estado
       // $('#totalPanelTransferencia').text(numerito.format(varTotalTransferencia));
        //$('#totalReciboTransferencia').text(numerito.format(varTotalRecibo));
        $('#totalAcubrirTransferencia').text(numerito.format(varTotalSaldo));
        // $('#panelCuerpoTransferenciaBancaria').show('fast');
        importeTransferencia.numberbox('setValue', varTotalSaldo);
        $.mobile.go('#panelCuerpoTransferenciaBancaria', 'slide', 'left');

        // coloco la fecha actual 

        var hoy = new Date();
        var y = hoy.getFullYear();
        var m = hoy.getMonth() + 1;
        var d = hoy.getDate();
        //return m+'/'+d+'/'+y;
        $('#transfFecha').datebox('setValue', d + '/' + m + '/' + y);
        importeTransferencia.numberbox('textbox').focus().select();
    }

    // Código antiguo para llenar el select de cuentas bancarias:
    // var listaCuentas = $('#listaCuentaBancariaAlta');
    // listaCuentas.combobox({
    //     valueField: 'id',
    //     textField: 'text',
    //     prompt: 'Cuenta Bancaria...',
    //     panelMaxHeight: '50px',
    //     data: opt
    // });
    // Nuevo método: renderizar linkbuttons para cuentas bancarias
    function renderCuentasBancariasBotones(cuentas) {
        var html = '<label style="display:block;margin-bottom:5px;font-weight:bold;">Cuentas Bancarias:</label>';
        cuentas.forEach(function(cuenta, idx) {
            html += '<a href="javascript:void(0)" '
                + 'class="easyui-linkbutton cuenta-bancaria-btn" '
                + 'data-id="' + cuenta.id + '" '
                + 'data-options="toggle:true,group:\'cuentasBancarias\'" '
                + (idx === 0 ? 'data-selected="true"' : '')
                + ' style="margin-right:10px;">' + cuenta.text + '</a>';
        });
        $('#grupoCuentasBancarias').html(html);
        // $.parser.parse('#grupoCuentasBancarias'); // Inicializa EasyUI en los nuevos botones
        //     var html = '<div style="display:flex;align-items:center;gap:15px;">';
        //     html += '<label style="font-weight:bold;white-space:nowrap;">Cuentas Bancarias disponibles:</label>';
        //     html += '<div style="display:flex;gap:10px;">';
        //     cuentas.forEach(function(cuenta, idx) {
        //         html += '<a href="javascript:void(0)" '
        //             + 'class="easyui-linkbutton cuenta-bancaria-btn" '
        //             + 'data-id="' + cuenta.id + '" '
        //             + 'data-options="toggle:true,group:\'cuentasBancarias\'" '
        //             + (idx === 0 ? 'data-selected="true"' : '')
        //             + ' style="margin-right:0;">' + cuenta.text + '</a>';
        //     });
        //     html += '</div></div>';
        //     $('#grupoCuentasBancarias').html(html);
         $.parser.parse('#grupoCuentasBancarias'); // Inicializa EasyUI en los nuevos botones
    }

    function trae_cuentas_bancarias() {
        // var listaCuentas = $('#listaCuentaBancariaAlta'); // <-- comentado
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeCuentasBancarias: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log('traeCuentasBancarias::', data);
                if (data.msg === "ok") {
                    var opt = data.cuentasBcarias;
                    renderCuentasBancariasBotones(opt);
                } else {
                    // error 
                }
            }
        });
    }

    // Para obtener la cuenta seleccionada:
    function getCuentaBancariaSeleccionada() {
        var btn = $('.cuenta-bancaria-btn.easyui-linkbutton-selected');
        return btn.length ? btn.data('id') : null;
    }
    /** 
     * # controla que todos los campos esten bien para enviar a la transferencia 
     * retorna un array con errores tipo.
     */
    function control_alta_transferencia() {
        let fecha, numeroTransferencia, idCuentaBancaria, nombreCuentaBancaria, importe, detalle;

        fecha = fechaReves($('#transfFechaAlta').datebox('getValue'));
        numeroTransferencia = $('#transfNumeroAlta').numberbox('getValue');
        // Obtener el botón seleccionado
        var btnSeleccionado = $('#grupoCuentasBancarias .cuenta-bancaria-btn.l-btn-selected');
        idCuentaBancaria = btnSeleccionado.length ? btnSeleccionado.data('id') : '';
        nombreCuentaBancaria = btnSeleccionado.length ? btnSeleccionado.find('.l-btn-text').text().trim() : '';
        console.log('id de cuenta bancaria=>', idCuentaBancaria);
        console.log('nombreCuentaBancaria=>',nombreCuentaBancaria);    
        importe = $('#transfImporteAlta').numberbox('getValue');
        detalle = $('#transfDetalleAlta').textbox('getValue');

        console.info(fecha, numeroTransferencia, nombreCuentaBancaria, idCuentaBancaria, importe, detalle);

        let estado = false;
        let vacio = 0;
        // controlo valores vacios.
        if (fecha === '') {
            $('#transfFechaAlta').textbox('textbox').focus();
            Swal.fire("Atención!", "Debe completar la fecha", "warning");
            vacio++;
        }
        if (numeroTransferencia == '') {
            $('#transfNumeroAlta').textbox('textbox').focus();
            Swal.fire("Atención!", "Debe completar el Número de transferencia del cliente", "warning");
            vacio++;
        }
        if (nombreCuentaBancaria == '') {
            Swal.fire("Atención!", "Debe seleccionar una Cuenta Bancaria destino", "warning");
            vacio++;
        }
        if (detalle == '') {
            $('#transfDetalleAlta').textbox('textbox').focus();
            Swal.fire("Atención!", "Debe completar el detalle / titular de la transferencia", "warning");
            vacio++;
        }
        if (idCuentaBancaria == '') {
            Swal.fire("Atención!", "No seleccionó una Cuenta Bancaria destino", "warning");
            vacio++;
        }
        if (importe == '') {
            $('#transfImporteAlta').textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar un importe:", "warning");
            vacio++;
        }

        if (vacio == 0) {
            estado = true;
        }

        return estado;
    }
    /**
     * *# genero el alta, guardo los datos de la transferencias en recibo temporal.
     */
    function alta_transferencia_bancaria() {
        let control = false;
        let botonAceptar, botonCancelar;
        let fecha, numeroTransferencia, idCuentaBancaria, nombreCuentaBancaria, importe,detalle;
        botonAceptar = $('#botonGuardarTransferencia');
        botonCancelar = $('#botonCancelarTransferencia');
        // Inicializar linkbutton si no está
        if (!botonAceptar.hasClass('easyui-linkbutton')) {
            botonAceptar.linkbutton();
        }
        if (!botonCancelar.hasClass('easyui-linkbutton')) {
            botonCancelar.linkbutton();
        }
        // colocando el espere..
        botonAceptar.linkbutton('disable');
        botonCancelar.linkbutton('disable');
        botonAceptar.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere...'
        });

        // controlando que no hayan vacios.
        control = control_alta_transferencia();
        if (control == false) {
            // boton a su estado anterior.
            botonAceptar.linkbutton({
                'text': '<i class="fas fa-check fa-fw fa-lg"></i> Aceptar'
            });
            botonAceptar.linkbutton('enable');
            botonCancelar.linkbutton('enable');

            return false;
        }

        if (control == true) {
            // pase el control capturo los valores nuevamente


            fecha = fechaReves($('#transfFechaAlta').datebox('getValue'));
            numeroTransferencia = $('#transfNumeroAlta').numberbox('getValue');
            var btnSeleccionado = $('#grupoCuentasBancarias .cuenta-bancaria-btn.l-btn-selected');
            idCuentaBancaria = btnSeleccionado.length ? btnSeleccionado.data('id') : '';
            nombreCuentaBancaria = btnSeleccionado.length ? btnSeleccionado.find('.l-btn-text').text().trim() : '';
            console.log('nombre de la cuenta bancaria', nombreCuentaBancaria);
            console.log('id de cuenta bancaria', idCuentaBancaria);
            importe = $('#transfImporteAlta').numberbox('getValue');
            detalle = $('#transfDetalleAlta').textbox('getValue');
            // mandando los datos a guardar
            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    altaTransferenciaBancaria: 1,
                    fecha: fecha,
                    nroTransferencia: numeroTransferencia,
                    idCuentaBancaria: idCuentaBancaria,
                    numeroCuenta: nombreCuentaBancaria,
                    importe: importe,
                    detalle: detalle
                },
                dataType: 'json',

                success: function(data) {

                    if (data.msg === "ok") {
                        // la transferencia se guardo exitosamente.
                        trae_totales_recibo();
                        $('#spinner').hide('fast');
                        botonAceptar.linkbutton({
                            'text': '<i class="fas fa-check fa-fw fa-lg"></i> Aceptar'
                        });
                        botonAceptar.linkbutton('enable');
                        botonCancelar.linkbutton('disable');
                        $.mobile.go('#panelMediosCobro', 'slide', 'left');
                        Swal.fire('Muy Bien', 'La transferencia se generó exitosamente', 'success');
                        // vaciar los campos para una nueva transferencia
                        
                        $('#transfFechaAlta').datebox('clear');
                        $('#transfNumeroAlta').numberbox('clear');
                       // $('#listaCuentaBancariaAlta').combobox('clear');
                        $('#transfImporteAlta').numberbox('clear');
                        $('#transfDetalleAlta').textbox('clear');

                    } else {
                        console.error({
                            data
                        });
                        Swal.fire('error', 'Ocurrio un inconveniente con la transferencia ' + data.detalle, 'error');

                    }

                }
            });
        }

    }
    // Muestra el panel de transferencias y actualiza totales
    function mostrar_panel_transferencias() {
        trae_lista_transferencias();
        trae_totales_recibo();
        $.mobile.go('#panelCuerpoTransferenciaBancaria', 'slide', 'left');
    }

    // Muestra el panel de alta de transferencia y prepara campos
    function mostrar_panel_alta_transferencia() {

        trae_cuentas_bancarias();
        
        // coloco la fecha actual 

        var hoy = new Date();
        var y = hoy.getFullYear();
        var m = hoy.getMonth() + 1;
        var d = hoy.getDate();
        //return m+'/'+d+'/'+y;
        $('#transfFechaAlta').datebox('setValue', d + '/' + m + '/' + y);
        // importeTransferencia.numberbox('textbox').focus().select();

        let saldo = varTotalSaldo || 0;
        $('#transfImporteAlta').textbox('setValue', saldo);
        $('#totalTransferenciaAlta').text(numerito.format(varTotalTransferencia || 0));
        $('#totalReciboTransferenciaAlta').text(numerito.format(varTotalRecibo || 0));
        $('#totalAcubrirTransferenciaAlta').text(numerito.format(saldo));
       // $('#listaCuentaBancariaAlta').combobox('textbox').focus();
        $.mobile.go('#panelAltaTransferencia', 'slide', 'left');
    }

    // Elimina la transferencia seleccionada
    function borrar_transferencia() {
        
        var select = $('#tblTransferencias').datagrid('getSelected');
        console.log('linea de la tabla transferencias',select);
        if (!select) {
            Swal.fire('Advertencia', 'Debe seleccionar una transferencia para borrar', 'warning');
            return;
        }
        Swal.fire({
            title: 'Advertencia',
            html: '¿Está seguro de eliminar la transferencia <br><strong>#' + select.numeroTransferencia + ' de ' + select.detalle + '</strong> <br> por <strong>$' + select.monto + '</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Aceptar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'GET',
                    url: 'ajax/json_recibo.php',
                    data: {
                        borraTransferencia: 1,
                        id: select.id,
                        numero: select.numeroTransferencia , 
                        importe: select.monto
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.msg === 'ok') {
                            trae_totales_recibo();
                            trae_lista_transferencias();
                        }
                    }
                });
            }
        });
    }

    // Trae el listado de transferencias y lo carga en la tabla
    function trae_lista_transferencias() {
        var tabla = $('#tblTransferencias');
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: { listaTransferencias: 1 },
            dataType: 'json',
            success: function(data) {
                if (data.msg === 'ok') {
                    var opt = data.transferencias;
                    tabla.datagrid({
                        singleSelect: true,
                        fit: false,
                        fitColumns: true,
                        border: true,
                        scrollbarSize: 0,
                        columns: [[
                            { field: 'id', title: 'ID', width: 80, hidden: true },
                            // { field: 'cuenta', title: 'Cuenta', width: 120 },
                            { field: 'numeroTransferencia', title: 'Número', width: 100 },
                            { field: 'detalle', title: 'Detalle', width: 120 },
                            { field: 'monto', title: 'Importe', width: 80 },
                        ]],
                        data: opt
                    });
                }
                if (data.msg === 'vacio') {
                    if (tabla.data('datagrid')) {
                        tabla.datagrid('loadData', []);
                    }
                }
            }
        });
    }