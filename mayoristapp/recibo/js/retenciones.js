/**
     * * # RETENCIONES
     * * ==============================================
     */


 function trae_retenciones() {
    var tabla = $('#tblRetenciones');

    $.ajax({
        type: 'GET',
        url: 'ajax/json_recibo.php',
        data: {
            listaRetencion: 1
        },
        dataType: 'json',
        success: function(data) {
            //console.log(data);

            if (data.msg === "ok") {
                var opt = data.retencion;
                // console.log(opt);
                tabla.datagrid({
                    header: '#hh',
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
                                field: 'retencion',
                                title: 'Retencion',
                                width: 120
                            },
                            {
                                field: 'certificado',
                                title: 'Cert',
                                width: 80
                            },
                            {
                                field: 'porcentaje',
                                title: '%',
                                width: 50,
                                align: 'right'
                            },
                            {
                                field: 'monto',
                                title: 'Monto',
                                width: 100,
                                align: 'right'
                            }
                        ]
                    ],
                    // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
                    data: opt
                });

            }

        }
    });
}