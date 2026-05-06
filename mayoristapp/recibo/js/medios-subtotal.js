 // * FIN RECIBO RESUMEN
    // * ========================================================================   

    // * resumen del medio cobro 
    function trae_resumen_recibo() {
        var tipoRec;
        //inicio el spinner
        // $('#spinner').show('fast');
        // traer el resumen del recibo

        tipoRec = "acuenta";

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeResumenRecibo: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log('traeResumenRecibo:>', data);

                if (data.msg === "ok") {
                    //tipoRec = data.tiporec;
                    //console.log("tiporec::", tipoRec);

                    // dibujar la tabla resumen
                    //activa_tabla_resumen_recibo(data.resumen);
                    // si es por imputacion traigo las facturas


                    $.ajax({
                        type: 'GET',
                        url: 'ajax/json_recibo.php',
                        data: {
                            traeResumenImputacion: 1
                        },
                        dataType: 'json',

                        success: function(data) {
                            console.log('trae Resumen imputacion=>', data);

                            if (data.msg === "ok") {
                                console.log("llamando a imputacion ");
                                activa_tabla_imputacion(data.imputacion);

                                // dibujar la tabla resumen


                            }
                        }
                    });
                }
            }
        });





        // si es a cuenta solo medios de cobro.

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeResumenMedios: 1
            },
            dataType: 'json',

            success: function(data) {
                console.log('Resumen de medios=>', {
                    data
                });

                if (data.msg === "ok") {
                    activa_tabla_medios(data.medios);
                    control_final_recibo();
                    // dibujar la tabla resumen


                }
            }
        });
        // como ya debi cargar todo ahora si muestro el resumen.

        //     ver si esta en condiciones de hacer el recibo o falta completar algo

        // apago el spinner
        // $('#spinner').hide('fast');


        $.mobile.go('#panelResumen', 'slide', 'left');
        //$('#spinner').hide();
    }

    function control_final_recibo() {
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                controlFinalRecibo: 1
            },
            dataType: 'json',
            beforeSend: function() {
                $('#spinner').show('fast');
            },
            success: function(data) {
                console.log('controlFinalRecibo:=>', data);

                if (data.msg === "error") {
                    var deuda = data.deuda;
                    $('#botonFinalizar').linkbutton('disable');
                    Swal.fire("Atención!", "No puede finalizar el recibo, debe cubrir:" + deuda, "warning");
                   
                }
            },
            complete: function() {
                $('#spinner').hide('fast');
            }
        });
    }

    // trae los datos del recibo resumen.

    function activa_tabla_resumen_recibo(data) {
        // var tablaResumen = $('#tblResumenRecibo');

        // tablaResumen.datagrid({
        //     singleSelect: false,
        //     fit: false,
        //     fitColumns: true,
        //     border: true,
        //     scrollbarSize: 0,
        //     striped: true,
        //     columns: [
        //         [{
        //                 field: 'campo',
        //                 title: '',
        //                 width: 100
        //             },
        //             {
        //                 field: 'valor',
        //                 title: '',
        //                 width: 120,
        //                 align: 'right'
        //             }
        //         ]
        //     ],
        //     // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
        //     data: data
        // });
        // var divResumen= $('#divResumenRecibo');
        // let titulo,dato;
        // console.log('datos de resumen del recibo',data);
        
        // data.forEach(function(linea){
        //     console.log('soy una linea',linea);
        //     dato ='<p>'+linea.campo+'<span>'+linea.valor+'</span></p>';
        //     divResumen.append(dato);    
        // });
        

    }

    // dibuja la tabla imputacion
    function activa_tabla_imputacion(data) {
        var tablaImputacion = $('#tblResumenImputacion');
        tablaImputacion.datagrid({
            singleSelect: false,
            fit: false,
            showHeader: false,
            fitColumns: true,
            border: true,
            scrollbarSize: 0,
            striped: true,
            columns: [
                [{
                        field: 'campo',
                        title: '',
                        width: 100
                    },
                    {
                        field: 'cantidad',
                        title: 'Cant',
                        width: 50,
                        align: 'center'
                    },
                    {
                        field: 'valor',
                        title: 'Monto',
                        width: 120,
                        align: 'right'
                    }
                ]
            ],
            // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
            data: data
        });
    }


    // dibuja la tabla medios de cobro.

    function activa_tabla_medios(data) {
        var tablaMedio = $('#tblResumenMedios');
        tablaMedio.datagrid({
            singleSelect: false,
            showHeader: false,
            fit: false,
            fitColumns: true,
            border: true,
            scrollbarSize: 0,
            striped: false,
            columns: [
                [{
                        field: 'campo',
                        title: '',
                        width: 100
                    },
                    {
                        field: 'cantidad',
                        title: 'Cant',
                        width: 50,
                        align: 'center'
                    },
                    {
                        field: 'valor',
                        title: 'Monto',
                        width: 120,
                        align: 'right'
                    }
                ]
            ],
            rowStyler: function(index, row) {

                if (row.campo === 'Total Recibo:') {
                    //      console.log(row);
                    return 'color: #f7fdff;background-color: #395aa2; border-color: #395aa2;font-weight:bolder;font-size: 1.5 em;';
                }
            },

            data: data
        });
    }
