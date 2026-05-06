// *# TARJETAS CREDITO
    // ====================================================================================================
    // vamos a validar con el numero de tarjeta que tipo de tarjeta es si no lo encuentra , pedir que sea seleccionado. Se

    // * variables globales de tarjeta
    var importeTarjeta,
        tipoTarjeta,
        claseTarjeta,
        numeroTarjeta,
        planTarjeta,
        cuotasTarjeta,
        importeCuotaTarjeta,
        cuponTarjeta,
        totalaCubrirTarjeta,
        loteTarjeta;
    // * asginando objetos a variables de tarjeta.
    importeTarjeta = $('#tarjetaImporteTotal');
    tipoTarjeta = $('#tarjetaTipo');
    claseTarjeta = $('#tarjetaLista');
    numeroTarjeta = $('#tarjetaNumero');
    planTarjeta = $('#tarjetaListaPlan');
    cuotasTarjeta = $('#tarjetaCuotas');
    importeCuotaTarjeta = $('#tarjetaImporteCuota');
    cuponTarjeta = $('#tarjetaCupon');
    loteTarjeta = $('#tarjetaLote');
    aCubrirTarjeta = $('#aCubrirTarjeta');
    /**
     *  *muestra el panel con el total de las tarjetas 
     * */

    function mostrar_panel_tarjetas() {
        //$('#panelMediosCobro').hide('fast');
        //$('#panelCuerpoEfectivo').hide('fast');
        //$('#panelCuerpoDolar').hide('fast');
        //$('#panelCuerpoCheque').hide('fast');
        //$('#panelCuerpoTransferenciaBancaria').hide('fast');
        trae_totales_recibo();
        trae_lista_tarjetas();
        // llenar el estado. 
        $('#aCubrirPanelTarjeta').text(numerito.format(varTotalSaldo));
        $('#totalReciboPanelTarjeta').text(numerito.format(varTotalRecibo));
        $('#totalAltaTarjeta').text(numerito.format(varTotalTarjeta));

        //$('#panelCuerpoTarjetasCredito').show('fast');
        $.mobile.go('#panelCuerpoTarjetasCredito', 'fade', 'left');



    }
    /**
     * muestra la carga de una tarjeta de credito /debito
     */
    function mostrar_panel_alta_tarjeta() {
        var saldoRecibo = varTotalSaldo;
        aCubrirTarjeta.text(numerito.format(saldoRecibo));

        importeTarjeta.numberbox('setValue', saldoRecibo);

        // formateo el input  importe de la tarjetas
        importeTarjeta.textbox('textbox').css('font-size', 'x-large');
        importeTarjeta.textbox('textbox').css('color', '#c97900');
        importeTarjeta.textbox('textbox').css('border', '1px solid #ffa600');
        armar_selector_tipo_tarjeta();
        $.mobile.go('#panelAltaTarjeta', 'slide', 'left');
        importeTarjeta.numberbox('textbox').focus().select();

    }

    /**
     * Tipo de tarjeta switch comportamiento.
     */
    function armar_selector_tipo_tarjeta() {
        var tipoTarjeta = 'Credito';
        //labelWidth="120"
        $('#tarjetaTipo').switchbutton({
            onText: 'Credito',
            offText: 'Debito',
            label: 'Tipo:',
            labelWidth: '50px',
            labelAlign: 'right',
            width: '30%',
            checked: true,

            onChange: function(checked) {

                // checked = true Credito;
                // checked = false Debito;
                if (checked == true) {
                    // soy el Credito.
                    tipoTarjeta = 'Credito';

                }
                if (checked == false) {
                    // soy el Debito
                    tipoTarjeta = 'Debito';
                }
                // llenando las tarjetas de credito debito.
                listar_tarjetas(tipoTarjeta);

            }
        });
        // como pongo defecto credito ya debo traer las tarjetas
        listar_tarjetas(tipoTarjeta);
    }

    /**
     * *Buscar planes de tarjetas segun el id de tarjeta seleccionado.
     */
    function trae_planes_tarjeta(idTarjeta) {
        var cuotas = $('#tarjetaCuotas');
        let listaPlanesTarjetas = $('#tarjetaListaPlan');

        //segun el plan debo limitar la cantidad de cuotas y pasra el foco a cuotas
        // por defecto colocar una cuota y repetir el importe de la tarjeta a la cuota.
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeListaPlanTarjetas: 1,
                idTarjeta: idTarjeta
            },
            dataType: 'json',
            success: function(data) {
                console.log('Planes de tarjeta::', data);

                if (data.msg === "ok") {
                    var opt = data.listaPlanesTarjetas;

                    listaPlanesTarjetas.combobox({
                        valueField: 'id',
                        textField: 'text',
                        panelHeight: 'auto',
                        data: opt,
                        onSelect: function(row) {
                            // en el id de plan debo traer cantidad de cuotas
                            // armar el campo cuotas con sus minimos y maximos segun cuotas.
                            // poner el minimo en cuotas y si es uno, copiar el importe de la cuota.
                            console.log('soy el row de los planes', row);

                            //cuotas.numberbox('destroy');
                            cuotas.numberbox({
                                min: parseInt(row.min),
                                max: parseInt(row.max),
                                value: parseInt(row.min),
                                required: true

                            });
                            let lacuota = document.getElementById('tarjetaCuotas');
                            // const y = lacuota.getBoundingClientRect().top + window.scrollY;
                            // window.scroll({
                            //     top: y,
                            //     behavior: 'smooth'
                            // });


                            lacuota.scrollIntoView({
                                behavior: 'smooth',
                                block: 'end'
                            });
                            cuotas.textbox('textbox').focus();
                        }

                    });
                    //pasarle el foco 
                    listaPlanesTarjetas.textbox('textbox').focus();

                }

            }
        });
    }

    /** 
     * *# Buscar listado de tarjetas disponibles
     * filtra si son credito o debito
     */
    function listar_tarjetas(tipoTarjeta) {
        // formato visa | credito
        let listadoTarjetas;

        listadoTarjetas = $('#tarjetaLista');
        // el tipo de tarjeta vino con algun bug o basura
        console.info(tipoTarjeta);
        if (tipoTarjeta !== 'Credito' && tipoTarjeta !== 'Debito') {
            tipoTarjeta = 'Credito'; // por defecto credito.
        }
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeListaTarjetas: 1,
                tipoTarjeta: tipoTarjeta
            },
            dataType: 'json',
            success: function(data) {
                console.log('listado de tarjetas de credito');
                console.table({
                    data
                });

                if (data.msg === "ok") {
                    var opt = data.listaTarjetasCredito;

                    listadoTarjetas.combobox({
                        valueField: 'id',
                        textField: 'text',
                        panelMaxHeight: '20px',
                        data: opt,
                        onSelect: function(lista) {
                            // * poner el icono de la tarjeta cambiar imagen.
                            console.log('selecciona una tarjeta decredito', lista);
                            muestra_icono_tarjeta(lista.text.toLowerCase());
                            trae_planes_tarjeta(lista.id);

                        }

                    });
                    //pasarle el foco 
                    //listadoTarjetas.textbox('textbox').focus();


                }

            }
        });

    }
    /** 
     * * muestra iconico segun tipo tarjeta.
     * 
     */
    function muestra_icono_tarjeta(nombreTarjeta) {
        console.log('que icono tarjeta elegi[' + nombreTarjeta + ']');
        ccicon.style.display = "block";
        ccimg.style.display = "none";
        switch (nombreTarjeta) {
            case 'american express':
                ccicon.innerHTML = amex;
                // ccsingle.innerHTML = amex_single;
                // swapColor('green');
                break;
            case 'visa':
                ccicon.innerHTML = visa;
                // ccsingle.innerHTML = visa_single;
                // swapColor('lime');
                break;
            case 'diners':
                ccicon.innerHTML = diners;
                // ccsingle.innerHTML = diners_single;
                // swapColor('orange');
                break;
            case 'discover':
                ccicon.innerHTML = discover;
                // ccsingle.innerHTML = discover_single;
                // swapColor('purple');
                break;
            case ('jcb' || 'jcb15'):
                ccicon.innerHTML = jcb;
                // ccsingle.innerHTML = jcb_single;
                // swapColor('red');
                break;
            case 'maestro':
                ccicon.innerHTML = maestro;
                // ccsingle.innerHTML = maestro_single;
                // swapColor('yellow');
                break;
            case 'mastercard':
                ccicon.innerHTML = mastercard;
                // ccsingle.innerHTML = mastercard_single;
                // swapColor('lightblue');

                break;
            case 'unionpay':
                ccicon.innerHTML = unionpay;
                // ccsingle.innerHTML = unionpay_single;
                // swapColor('cyan');
                break;
            case ('mercadopago' || 'mercado pago'):
                console.warn('entre en mercado pago-->', mercadopago);
                ccicon.style.display = "none";
                ccimg.src = mercadopago;
                ccimg.style.display = "block";

                //ccicon.innerHTML = mercadopago;
                // ccicon.hide('fast');

                break;
            case 'mercado pago credito':
                console.warn('entre en mercado pago credito-->', mercadopago);
                ccicon.style.display = "none";
                ccimg.src = mercadopago;
                ccimg.style.display = "block";
                break;
            case 'mercado pago debito':
                console.warn('entre en mercado pago debito-->', mercadopago);
                ccicon.style.display = "none";
                ccimg.src = mercadopago;
                ccimg.style.display = "block";
                break;
            case ('yacaré' || 'yacare'):
                ccicon.style.display = "none";
                ccimg.src = yacare;
                ccimg.style.display = "block";
                break;
            case ('naranja' || 'naranja-x'):
                ccicon.style.display = "none";
                ccimg.src = naranja;
                ccimg.style.display = "block";
                break;

            case ('nevada'):
                ccicon.style.display = "none";
                ccimg.src = naranja;
                ccimg.style.display = "block";
                break;
            default:
                ccicon.innerHTML = '';
                // ccsingle.innerHTML = '';
                // swapColor('grey');
                break;
        }
    }

    /**
     * *# panel de tarjetas credito
     */
    function trae_lista_tarjetas() {
        var tabla = $('#tblTarjetasCredito');


        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                listadoTotalTarjetas: 1
            },
            dataType: 'json',

            success: function(data) {
                if (data.msg === "ok") {
                    var opt = data.tarjetas;
                    console.table(data.tarjetas);
                    tabla.datagrid({
                        singleSelect: true,
                        fit: false,
                        fitColumns: true,
                        border: true,
                        scrollbarSize: 0,

                        columns: [
                            [{
                                    field: 'numero',
                                    title: 'Numero',
                                    width: 80

                                },
                                {
                                    field: 'tipo',
                                    title: 'Tipo',
                                    width: 100
                                },
                                {
                                    field: 'importe',
                                    title: 'Importe',
                                    width: 80
                                },
                                {
                                    field: 'cuota',
                                    title: 'Cuota',
                                    width: 100
                                }

                            ]
                        ],

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

    /**
     * *# controlar que la tarjeta tenga todo bien.
     */

    function controlar_alta_tarjeta() {
        let errores = 0;

        let importe = parseFloat(importeTarjeta.numberbox('getValue'));
        let tipo = tipoTarjeta.switchbutton('options').checked;
        let clase = claseTarjeta.combobox('getValue');
        let nombreClase = claseTarjeta.textbox('getText');
        let numero = parseInt(numeroTarjeta.numberbox('getValue'));
        let plan = planTarjeta.combobox('getValue');
        let nombrePlan = planTarjeta.textbox('getText');
        let cuotas = parseInt(cuotasTarjeta.numberbox('getValue'));
        let importeCuota = parseFloat(importeCuotaTarjeta.numberbox('getValue'));
        let cupon = parseInt(cuponTarjeta.numberbox('getValue'));
        let lote = parseInt(loteTarjeta.numberbox('getValue'));

        console.table(importe, tipo, clase, nombreClase, numero, plan, nombrePlan, cuotas, cupon, importeCuota);

        if (importe == 0) {
            importeTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar un importe", "warning");

            errores++;
            return false;
        }
        if (clase == '') {
            claseTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe seleccionar un tipo de tarjeta", "warning");
            errores++;
            return false;
        }
        if (numero == '' || numero == 0) {
            numeroTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar ultimos 4 dígitos de tarjeta", "warning");
            errores++;
            return false;
        }
        if (plan == '') {
            planTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe seleccionar un plan:", "warning");
            errores++;
            return false;
        }
        if (cuotas == '' || cuotas == 0) {
            cuotasTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar una cantidad de cuotas", "warning");
            errores++;
            return false;
        }
        if (importeCuota == '' || importeCuota == 0) {
            importeCuotaTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar un importe de cuotas", "warning");
            errores++;
            return false;
        }
        if (cupon == '' || cupon == 0) {
            cuponTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar un número de cupon", "warning");
            errores++;
            return false;
        }
        if (lote == '' || lote == 0) {
            loteTarjeta.textbox('textbox').focus();
            Swal.fire("Atención!", "Debe colocar un número de lote", "warning");
            errores++;
            return false;
        }

        if (errores == 0) {
            return true;
        }


    }

    /**
     * *guardar el pago de la tarjeta en recibo.
     */
    function alta_tarjeta_credito() {
        // console.log('miTarjetita::>',miTarjetita);
        console.info('estoy en la alta tarjetita');


        let control = false;

        let botonAceptar, botonCancelar;

        let importe = parseFloat(importeTarjeta.numberbox('getValue'));
        let tipo = tipoTarjeta.switchbutton('options').checked;
        let tipoTexto;
        let clase = claseTarjeta.combobox('getValue');
        let nombreClase = claseTarjeta.textbox('getText');
        let numero = parseInt(numeroTarjeta.numberbox('getValue'));
        let plan = planTarjeta.combobox('getValue');
        let nombrePlan = planTarjeta.textbox('getText');
        let cuotas = parseInt(cuotasTarjeta.numberbox('getValue'));
        let importeCuota = parseFloat(importeCuotaTarjeta.numberbox('getValue'));
        let cupon = parseInt(cuponTarjeta.numberbox('getValue'));
        let lote = parseInt(loteTarjeta.numberbox('getValue'));

        // valor de la tarjeta de credito / debito del switch
        if (tipo == true) {
            tipoTexto = 'Credito';
        }
        if (tipo == false) {
            $tipoTexto = 'Debito';
        }

        botonAceptar = $('#botonGuardarTarjeta');
        botonCancelar = $('#botonCancelarTarjeta');
        // colocando el espere..
        botonAceptar.linkbutton('disable');
        botonCancelar.linkbutton('disable');
        botonAceptar.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere...'
        });

        // controlando que no hayan vacios.
        control = controlar_alta_tarjeta();
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

            // mandando los datos a guardar 
            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    altaTarjetaCredito: 1,
                    importe: importe,
                    tipo: tipoTexto,
                    clase: clase,
                    nombreClase: nombreClase,
                    numero: numero,
                    plan: plan,
                    nombrePlan: nombrePlan,
                    cuotas: cuotas,
                    importeCuota: importeCuota,
                    cupon: cupon,
                    lote: lote

                },
                dataType: 'json',
                beforeSend: function() {
                    $('#spinner').show('fast');
                },
                success: function(data) {

                    if (data.msg === "ok") {
                        // la transferencia se guardo exitosamente.


                        trae_lista_tarjetas();
                        $.mobile.go('#panelMediosCobro', 'slide', 'left');
                        mostrar_panel_tarjetas();

                        Swal.fire({
                            icon: 'success',
                            title: 'Muy Bien',
                            position: 'top',
                            text: 'Tarjeta ' + nombreClase + ' nro: ***' + numero + ' agregada!',

                        });

                    } else {
                        console.error({
                            data
                        });
                        Swal.fire('error', 'Ocurrio un inconveniente con la tarjeta ' + data.detalle, 'error');

                    }

                },
                complete: function(e) {
                    $('#spinner').hide('fast');
                    botonAceptar.linkbutton({
                        'text': '<i class="fas fa-check fa-fw fa-lg"></i> Aceptar'
                    });
                    botonAceptar.linkbutton('enable');
                    botonCancelar.linkbutton('disable');

                }
            });
        }


    }