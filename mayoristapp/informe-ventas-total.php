<?php require_once 'sesion.inc.php'; ?>
<?php
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom = 0;
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>ventas estadisticas | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <?php require_once 'cabecera.php'; ?>
    <?php

    //        vamos a buscar los pedidos de acuerdo al cliente y al estado 
    $pedidos = array();

    ?>
    <script>
        google.load('visualization', '1', null); // No 'packages' section.

        function drawChart(dataG, optionsG, tipo, quien) {
            var wrap = new google.visualization.ChartWrapper();
            wrap.setChartType(tipo);
            wrap.setContainerId(quien);
            wrap.setDataTable(dataG);
            wrap.setOptions(optionsG);
            wrap.draw();
        }

        $.extend($.fn.dataTable.defaults, {
            "language": {
                "emptyTable": "No data available in table",
                "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",
                "thousands": "",
                "lengthMenu": "Ver _MENU_ entradas",
                "loadingRecords": "Loading...",
                "processing": "Processing...",
                "search": "Buscar:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "searching": true,
            "ordering": false,
            "paging": true,
            "responsive": true
        });

        $(document).ready(function() {
            // funcion para las comas



            // aca atacch a los eventos del spinner funcionando.
            $("#spinner").bind("ajaxSend", function() {
                $(this).show();
            }).bind("ajaxStop", function() {
                $(this).hide();
            }).bind("ajaxError", function() {
                $(this).hide();
            });

            // para que se borren lo que tienen adentro las fechas   
            $('#fechaDesde').val('dd/mm/aaaa');
            $('#fechaHasta').val('dd/mm/aaaa');
            $('#campoBusca').change(function() {
                var valor = $(this).val();
                // voy a corroborar que div mostrar

                $('#numeroComp').val('');
                switch (valor) {
                    case 'Fecha':
                        $('#buscaNumero').hide();
                        $('#buscaTipo').hide();
                        $('#buscaFecha').show(400);
                        break;
                    case 'NroComprobante':
                        $('#buscaFecha').hide();
                        $('#buscaTipo').hide();
                        $('#buscaNumero').show(400);
                        break;
                    case '':
                        $('#buscaTipo').hide();
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                    case 'TipoPedido':
                        $('#buscaTipo').show(400);
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                }


            });
            // boton para buscar coincidencias
            $('#botonBuscar').click(function() {
                function ReplaceNumberWithCommas(yourNumber) {
                    //Seperates the components of the number
                    var yourNumberF = parseFloat(yourNumber).toFixed(2);
                    var n = yourNumberF.toString().split(".");
                    //Comma-fies the first part
                    n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    //Combines the two sections
                    //        return n.join(",");
                    return yourNumberF;
                }

                var contienesVentas = $('#myTableVentas'),
                    nombreVentas = "#myTableVentas",
                    contienesVentasRubro = $('#myTableVentasRubro'),
                    nombreVentasRubro = "#myTableVentasRubro",
                    simbDer = "",
                    simbIzq = "",
                    contienesVentasRubroP = $('#myTableVentasRubroP'),
                    nombreVentasRubroP = "#myTableVentasRubroP",
                    //campoBusca = $('#campoBusca').val(),
                    fechaDesde = $('#fechaDesde').val(),
                    fechaHasta = $('#fechaHasta').val(),
                    fechaDesdeDos = $('#fechaDesdeDos').val(),
                    fechaHastaDos = $('#fechaHastaDos').val(),
                    rangoDoble = 0,
                    listarPor = $('#agrupoPor').val(),
                    filtrarPor = $('#filtroSelec').val(),
                    opRango = $('#tipoOperacion').val(),
                    datasetGrafico = "",
                    grafico = 0,
                    tipoResumen = $('#campoPeriodo').val(),
                    tipo = $('#verInforme').val(),
                    // solo montos
                    simbDer = "",
                    simbIzq = "$",
                    verGrafico = $('#aceptaGrafico');


                if (verGrafico.is(':checked') === true) {
                    grafico = 1;
                } else {
                    grafico = 0;
                }
                if (tipo == "") {
                    alert("Debe seleccionar tipo de información ");

                    return false;
                    $('#verInforme').focus();
                }
                if (listarPor == "") {
                    alert("Debe seleccionar un campo por el cual listar el informe");
                    return false;
                    $('#agrupoPor').focus();
                }
                if (tipoResumen == "") {
                    alert("Debe seleccionar un período de resumen");
                    return false;
                    $('#campoPeriodo').focus();
                }
                if (fechaDesde == "") {
                    alert("Debe seleccionar una fecha desde primaria");
                    return false;
                    $('#fechaDesde').focus();
                }
                if (fechaHasta == "") {
                    alert("Debe seleccionar una fecha hasta secundaria");
                    return false;
                    $('#fechaHasta').focus();
                }
                if (fechaDesdeDos !== "" && fechaHastaDos !== "") {
                    rangoDoble = 1;

                }
                switch (tipo) {
                    case "un":
                        simbDer = "";
                        simbIzq = "";
                        break;
                    case "monto":
                        simbDer = "";
                        simbIzq = "";
                        break;
                    case "peso":
                        simbDer = "";
                        simbIzq = "";
                        break;
                }
                // antes de empezar voy a ir generando de a un informe a la vez
                var errorInfo = 0;
                /*
                 * VENTAS NETAS
                 * ===========
                 */
                $.ajax({
                    type: 'GET',
                    url: 'relay-ventas-netas.php',
                    data: {
                        "ajax": "true",

                        "fechaDesde": fechaDesde,
                        "fechaHasta": fechaHasta,
                        "fechaDesdeDos": fechaDesdeDos,
                        "fechaHastaDos": fechaHastaDos,
                        "rangoDoble": rangoDoble,
                        "opRango": opRango,
                        "tipoResumen": tipoResumen,
                        "tipo": tipo,
                        "listarPor": listarPor,
                        "filtrarPor": filtrarPor,
                        "queInforme": "vt",
                        "queSalida": "html"

                    },
                    success: function(response) {
                        //                    console.log(response);
                        if (response === "vacio") {
                            var trCampos = "<tr><td>No se encontraron resultados</td></tr>";
                            contienesVentas.find("tbody").empty();
                            contienesVentas.find("tbody").append(trCampos);
                        } else {
                            //                        var response2 = '{"titulos":{"0":{"titulo":"Articulo","span":2,"rowspan":1},"1":{"titulo":"UNIDADES  x mes  \/   - Cliente: EICHLER GUILLERMO FEDERICO - CAFE (cod:27447)","span":2,"rowspan":1},"201604":{"titulo":"2016","span":1,"rowspan":1},"201606":{"titulo":"2016","span":1,"rowspan":1},"201607":{"titulo":"2016","span":1,"rowspan":1},"201605":{"titulo":"2016","span":1,"rowspan":1},"201608":{"titulo":"2016","span":1,"rowspan":1},"201603":{"titulo":"2016","span":1,"rowspan":1},"201609":{"titulo":"SubTotal","span":1,"rowspan":2}},"cabeceras":{"201604":"abr","201606":"jun","201607":"jul","201605":"may","201608":"ago","201603":"mar"},"data":{"1627":{"0":"AZUCAR ENSOBRADA Caja x 800 (6 ...","201604":2,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":2},"2339":{"0":"BANDEJA 103 *PP PROLIX* RECTANGULAR x 100 (unid) BOULEVARES","201604":0,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"2347":{"0":"BANDEJA 103 *PP* RECTANGULAR x 100 (unid) BOULEVARES","201604":0,"201606":0,"201607":1,"201605":0,"201608":0,"201603":0,"subt":1},"2302":{"0":"BANDEJA 105 *PP PROLIX* OVAL x 100 (unid) BOULEVARES","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"2349":{"0":"BANDEJA 105 *PP* RECTANGULAR  x 100 (unid) BOULEVARES","201604":1.5,"201606":0,"201607":0.5,"201605":0,"201608":0.5,"201603":0,"subt":2.5},"10051":{"0":"BANDEJA 105 PP *F\/C* OVALADA x 100 (UN) VAG PLAST","201604":1,"201606":0,"201607":2,"201605":0,"201608":1,"201603":0,"subt":4},"10050":{"0":"BANDEJA 105 PP *F\/C* RECTANGULAR x 100 (UN) VAG PLAST","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"528":{"0":"BANDEJA CARTON GRIS *NRO 2* x 100 (unid)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"529":{"0":"BANDEJA CARTON GRIS *NRO 3* x 100 (unid)","201604":0,"201606":2,"201607":2,"201605":1,"201608":1,"201603":0,"subt":6},"1932":{"0":"BANDEJA CARTON GRIS *NRO 4* x 100 (unid)","201604":0,"201606":0,"201607":1,"201605":0,"201608":0,"201603":0,"subt":1},"2003":{"0":"BANDEJA CARTON GRIS *NRO 5* x 100 (unid)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"10476":{"0":"BARBIJO DOBLE HEMOREPELENTE x 100 (UN)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"7616":{"0":"BOBINA DE PAPEL *DOBLE HOJA* Rollo x 400 (MTS)","201604":6,"201606":2,"201607":0,"201605":2,"201608":2,"201603":0,"subt":12},"1646":{"0":"BOBINA DE PAPEL *SULFITO* 60cm x 13 (KG)","201604":0,"201606":0,"201607":1,"201605":1,"201608":1,"201603":0,"subt":3},"6102":{"0":"BOLSA ARRANQUE *15X25* Rollo x 280 (GR)","201604":0,"201606":0,"201607":4,"201605":2,"201608":0,"201603":0,"subt":6},"6042":{"0":"BOLSA ARRANQUE *30X40* Rollo x 750 (GR)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"6045":{"0":"BOLSA ARRANQUE *40X50* Rollo x 750 (GR)","201604":1,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":2},"6044":{"0":"BOLSA ARRANQUE *40X60* Rollo x 750 (GR)","201604":4,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":5},"6015":{"0":"BOLSA CAMISETA *40X50* Paquete x 100 (unid)","201604":4,"201606":4,"201607":5,"201605":0,"201608":2,"201603":2,"subt":17},"6010":{"0":"BOLSA CONSORCIO NEGRA *60X90* Paquete x 10 (unid)","201604":5,"201606":0,"201607":0,"201605":5,"201608":0,"201603":0,"subt":10},"6030":{"0":"BOLSA CONSORCIO NEGRA *80X110* Paquete x 10 (unid)","201604":0,"201606":5,"201607":0,"201605":2,"201608":0,"201603":0,"subt":7},"6021":{"0":"BOLSA CONSORCIO NEGRA *90X120* Paquete x 10 (unid)","201604":5,"201606":0,"201607":0,"201605":1,"201608":4,"201603":0,"subt":10},"1672":{"0":"BOLSA PAPEL BLANCA DE PRIMERA *NRO 5* x 100 (unid)","201604":0,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"8108":{"0":"BOLSA PAPEL BLANCA DE SEGUNDA *NRO 2* x 100 (unid)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"1595":{"0":"BOLSA PAPEL BLANCA DE SEGUNDA *NRO 3* x 100 (unid)","201604":2,"201606":1,"201607":0,"201605":1,"201608":0,"201603":0,"subt":4},"1599":{"0":"BOLSA PAPEL BLANCA DE SEGUNDA *NRO 4* x 100 (unid)","201604":0,"201606":0,"201607":0,"201605":0,"201608":1,"201603":0,"subt":1},"1596":{"0":"BOLSA PAPEL BLANCA DE SEGUNDA *NRO 5* x 100 (unid)","201604":2,"201606":1,"201607":0,"201605":0,"201608":1,"201603":0,"subt":4},"6350":{"0":"BOLSA PP CRISTAL 04x25 Paquete x 200 (unid)","201604":6,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":6},"740":{"0":"CAJA MICROCORRUGADA 33X33X5 x 50 (unid)","201604":1,"201606":1,"201607":3,"201605":1,"201608":1,"201603":0,"subt":7},"871":{"0":"CALDO GRANULADO *CARNE* x 650 (GR) KNORR","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"847":{"0":"CALDO GRANULADO *GALLINA* x 650 (GR) KNORR","201604":0,"201606":0,"201607":0,"201605":2,"201608":0,"201603":0,"subt":2},"849":{"0":"CALDO GRANULADO *VERDURA* x 650 (GR) KNORR","201604":0,"201606":0,"201607":0,"201605":3,"201608":1,"201603":0,"subt":4},"1174":{"0":"CARTE DOR *BROWNIE* Bolsa x 800 (GR)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"1155":{"0":"CARTE DOR *PETIT GATEAU* Bolsa x 800 (GR)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"1151":{"0":"CARTE DOR *TIRAMISU* Bolsa x 1 (KG)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"1165":{"0":"CARTE DOR MOUSSE *CHOCOLATE* Bolsa x 1 (KG)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"8089":{"0":"CARTE DOR MOUSSE *DULCE DE LECHE* x 1 (KG)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"8088":{"0":"CARTE DOR MOUSSE *FRUTOS ROJOS* Bolsa x 1 (KG)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"818":{"0":"CHOCOLATE *P\/TAZA SUBMARINO* (50barx14gr) 66015 Caja x 700 (GR)","201604":0,"201606":1,"201607":4,"201605":0,"201608":12,"201603":0,"subt":17},"2157":{"0":"CINTA AUTOADHESIVA 5cm Rollo x 50 (MT)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"2027":{"0":"CUCHARA SUNDAE BLANCA x 100 (UN)","201604":1,"201606":0,"201607":1,"201605":0,"201608":1,"201603":0,"subt":3},"5009":{"0":"ESCARBADIENTE DE MADERA *ENSOBRADOS* Caja x 1000 (unid)","201604":0,"201606":0,"201607":1,"201605":0,"201608":0,"201603":0,"subt":1},"10321":{"0":"FILM ALUMINIO  x 1 (KG)","201604":0,"201606":2,"201607":0,"201605":1,"201608":0,"201603":0,"subt":3},"971":{"0":"FILM PVC *38cm* Rollo x 1000 (MT) RESINITE","201604":1,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":2},"925":{"0":"FILM PVC *45cm* Rollo x 1400 (MT) RESINITE","201604":0,"201606":0,"201607":1,"201605":0,"201608":0,"201603":0,"subt":1},"3901":{"0":"GUANTES DE LATEX LARGOS Caja x 1 (100unid) -","201604":0,"201606":1,"201607":1,"201605":0,"201608":0,"201603":0,"subt":2},"3900":{"0":"GUANTES DE LATEX MEDIANOS Caja x 1 (100 unid) -","201604":1,"201606":2,"201607":0,"201605":0,"201608":0,"201603":0,"subt":3},"10271":{"0":"JABON EN SPRAY *VAINILLA* Pouch x 400 (ML) KCP","201604":6,"201606":0,"201607":8,"201605":6,"201608":4,"201603":1,"subt":25},"7669":{"0":"JABON EN SPRAY SCOTT *ANTIBACTERIAL* Pouch x 400 (ML) KCP","201604":0,"201606":0,"201607":0,"201605":0,"201608":0,"201603":1,"subt":1},"2773":{"0":"KETCHUP (8cc) Caja x 196 (unid) HELLMANNS","201604":0,"201606":2,"201607":3,"201605":2,"201608":3,"201603":0,"subt":10},"505":{"0":"LAMINA FOLEX *20X25* Paquete x 1 (KG)","201604":0,"201606":3,"201607":2,"201605":0,"201608":0,"201603":0,"subt":5},"1814":{"0":"MAIZENA Bolsa x 2 (KG)","201604":0,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":1},"877":{"0":"MAPSACUBER *S\/AMGO BOTONES* 58-78 Caja x 6 (KG) MAPSA","201604":0,"201606":1,"201607":0,"201605":0,"201608":1,"201603":0,"subt":2},"2462":{"0":"MAYONESA (8cc) Caja x 196 (unid) HELLMANNS","201604":0,"201606":3,"201607":4,"201605":2,"201608":5,"201603":0,"subt":14},"2451":{"0":"MOSTAZA (8cc) Caja x 192 (unid) SAVORA","201604":0,"201606":0,"201607":2,"201605":2,"201608":3,"201603":0,"subt":7},"7611":{"0":"PH KLEENEX ALTO METRAJE JRT SIMPLE HOJA Rollo x 600 (MT) KCP","201604":11,"201606":0,"201607":6,"201605":15,"201608":9,"201603":0,"subt":41},"10374":{"0":"POTE PS CRISTAL *270cc*  x 100 (UN)","201604":0,"201606":0,"201607":0,"201605":0,"201608":0.5,"201603":0,"subt":0.5},"7059":{"0":"POTE PS DEGUSTACION *55cc* x 100 (unid)","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"7058":{"0":"POTE PS DEGUSTACION *80cc* x 100 (unid)","201604":0,"201606":1,"201607":0,"201605":1,"201608":0,"201603":1,"subt":3},"7048":{"0":"PREPARADO CARNE PICADA  Bolsa x 1,1 (KG) KNORR","201604":1,"201606":2,"201607":2,"201605":1,"201608":0,"201603":0,"subt":6},"1079":{"0":"REBOZADOR  Bolsa x 10 (KG) KNORR","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"850":{"0":"SALSA *BLANCA* Bolsa x 880 (GR) KNORR","201604":1,"201606":0,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"838":{"0":"SALSA *DEMI GLACE* Bolsa x 1 (KG) KNORR","201604":1,"201606":0,"201607":0,"201605":1,"201608":0,"201603":0,"subt":2},"2467":{"0":"SALSA GOLF (8cc) Caja x 196 (unid) HELLMANNS","201604":0,"201606":0,"201607":1,"201605":0,"201608":0,"201603":0,"subt":1},"10323":{"0":"SERVILLETA 30X30 Caja x 1000 (UN) SAMSENG","201604":0,"201606":0,"201607":0,"201605":3,"201608":0,"201603":3,"subt":6},"2219":{"0":"SERVILLETA DE PAPEL BLANCA *33X33* Caja x 1000 (unid)","201604":0,"201606":3,"201607":3,"201605":0,"201608":0,"201603":0,"subt":6},"2218":{"0":"SERVILLETA PAPEL BLANCA *18X18* Caja x 2000 (unid)","201604":5,"201606":2,"201607":3,"201605":3,"201608":5,"201603":0,"subt":18},"2325":{"0":"SIROPPI CHOCOLATE Pomo x 840 (GR) EUREKA","201604":0,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"2327":{"0":"SIROPPI FRUTILLA  Pomo x 840 (GR) EUREKA","201604":0,"201606":1,"201607":0,"201605":0,"201608":0,"201603":0,"subt":1},"294":{"0":"TAPA PARA VASO O POTE 63mm  x 100 (unid)","201604":1,"201606":1,"201607":0,"201605":1,"201608":0,"201603":1,"subt":4},"2350":{"0":"TAPA PP PARA BANDEJA *105* x 100 (unid) BOULEVARES","201604":0,"201606":0,"201607":0.5,"201605":0,"201608":0.5,"201603":0,"subt":1},"2786":{"0":"TAPA PS *8JL* C\/RESPIRADERO x 100 (unid) DART","201604":4,"201606":3,"201607":3,"201605":1,"201608":4,"201603":0,"subt":15},"10375":{"0":"TAPA PS 88mm *CRISTAL* x 100 (UN)","201604":0,"201606":0,"201607":0,"201605":0,"201608":1,"201603":0,"subt":1},"7684":{"0":"TOALLA SCOTT AIRFLEX* 2 rollos  x 150 (MT) KCP","201604":6,"201606":0,"201607":0,"201605":-6,"201608":0,"201603":0,"subt":0},"7657":{"0":"TOALLA SCOTT MULTIFOLD AIRFLEX* INTERCALADA 20 paquetes x 195 (UN) KCK","201604":2,"201606":3,"201607":2,"201605":2,"201608":2,"201603":0,"subt":11},"93":{"0":"VASO *AMERICAN NATURAL* 110cc x 100 (unid)","201604":0,"201606":0,"201607":0,"201605":3,"201608":0,"201603":0,"subt":3},"843":{"0":"VASO ALUMINIO P\/FLAN V230 *H10*  x 100 (unid)","201604":0.8,"201606":1,"201607":1,"201605":0,"201608":0,"201603":0,"subt":2.8},"41":{"0":"VASO PS *CRISTAL* 110cc  x 100 (unid)","201604":3,"201606":3,"201607":2,"201605":1,"201608":1,"201603":0,"subt":10},"7068":{"0":"VASO PS 330cc *GLASS* x 100 (UN)","201604":0,"201606":0,"201607":0,"201605":0,"201608":0.5,"201603":0,"subt":0.5}}}';
                            var pepe = jQuery.parseJSON(response);
                            //                        var pepe = jQuery.parseJSON(response2);
                            //console.log(pepe);

                            /**
                             * TABLA HTML 
                             * */
                            if ($.fn.dataTable.isDataTable(contienesVentas)) {
                                contienesVentas.DataTable().destroy();
                            }
                            contienesVentas.find("thead").empty();
                            contienesVentas.find("tbody").empty();
                            contienesVentas.find("tfoot").empty();
                            // titulos de las tablas
                            //                        console.log("contieneventas");
                            var trTitulo = "<tr>",
                                trCabecera = "<tr><th></th>",
                                trTituloLista = "";
                            //                            console.log(pepe.titulos);
                            // ordenar bien los titulos porque me los toma mal u obtener la fecha con el mes en dos digitos.
                            $.each(pepe.titulos, function(pos, titulo) {
                                if (pos == 0) {
                                    trTituloLista = "<th>" + titulo.titulo + "</th>";
                                } else {
                                    if (titulo.rowspan > 1) {
                                        trTitulo = trTitulo + "<th colspan='" + titulo.span + "' rowspan='" + titulo.rowspan + "'>" + titulo.titulo + "</th>";
                                        trTitulo = trTitulo + "<th colspan='" + titulo.span + "' rowspan='2'>" + titulo.titulo + "(%)</th>";
                                    } else {
                                        trTitulo = trTitulo + "<th colspan='" + titulo.span + "'>" + titulo.titulo + "</th>";
                                    }

                                }
                            });
                            trTitulo = trTitulo + "</tr>";
                            contienesVentas.find("thead").append(trTitulo);

                            // cabeceras
                            // coloco el listar aca
                            //console.log(trTituloLista);
                            trCabecera = trCabecera + trTituloLista;
                            //console.log(pepe.cabeceras);
                            var itotal = 2;
                            $.each(pepe.cabeceras, function(pos, cabeza) {

                                trCabecera = trCabecera + "<th class='dt-right'>" + cabeza + "</th>";
                                itotal++;

                            });
                            //                        console.log("itotal:"+itotal);
                            contienesVentas.find("thead").append(trCabecera);
                            // renglones 
                            var cuantasCeldas = 0;
                            var cuantosReng = 0;
                            var totalGeneral = 0;
                            var cuantasColumnas = 0;
                            var totalValorA = 0;
                            var totalValorB = 0;
                            var arrColumnas = [];
                            // sumo el total de renglones distintos de cero por columna.
                            var arrCuantos = [];
                            // calculo el total general
                            $.each(pepe.data, function(pos, renglones) {
                                $.each(renglones, function(posi, celda) {

                                    if (posi == "subt") {
                                        totalGeneral += parseFloat(celda);
                                    }
                                    //cuantasCeldas++;
                                });
                            });
                            // dibujo cada columna agrego porcentaje
                            $.each(pepe.data, function(pos, renglones) {
                                var trCampos = "<tr>";
                                var celdaSubTotal = 0;
                                var valorA = 0;
                                var valorB = 0;

                                var valorPorcDif = 0;
                                cuantosReng++;
                                trCampos = trCampos + "<td></td>";
                                cuantasColumnas = 0;
                                $.each(renglones, function(posi, celda) {

                                    cuantasColumnas++;
                                    cuantasCeldas++;
                                    if (posi != 0) {
                                        if (opRango != "suma" && posi == 1) {
                                            valorA = parseFloat(celda);
                                        }
                                        if (opRango != "suma" && posi == 2) {
                                            valorB = parseFloat(celda);
                                        }
                                        // sumatoria de las columnas.
                                        //console.log(arrColumnas[posi]);
                                        if (arrColumnas[posi] === undefined) {
                                            arrCuantos[posi] = 1;
                                            arrColumnas[posi] = parseFloat(celda);
                                        } else {
                                            if (parseFloat(celda) !== 0) {
                                                arrCuantos[posi] = arrCuantos[posi] + 1;
                                            }
                                            arrColumnas[posi] = arrColumnas[posi] + parseFloat(celda);

                                        }

                                        trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + simbIzq + ReplaceNumberWithCommas(celda) + simbDer + "</td>";
                                    } else {

                                        trCampos = trCampos + "<td class='dt-left'><div class='corto'>" + celda + "</div></td>";
                                    }
                                    //                                console.log(posi);
                                    if (posi == "subt") {
                                        celdaSubTotal += parseFloat(celda);
                                        totalValorA += valorA;
                                        totalValorB += valorB;
                                        //                                    if(opRango=="resta"){
                                        //                                        if(valorA==0){
                                        //                                            valorPorcDif=100;
                                        //                                        }else{
                                        //                                            valorPorcDif=parseFloat(celda)*100/valorA;
                                        //                                        }
                                        //                                    }
                                        if (opRango != "suma") {
                                            //                                        console.log("opRango=> "+opRango);
                                            //                                        console.log("valor A=> "+valorA);
                                            //                                        console.log("celda => "+valorB);
                                            if (valorA == 0) {
                                                valorPorcDif = 1;
                                            } else {
                                                valorPorcDif = valorB / valorA;
                                            }
                                            //                                        console.log("rango2/valora=> "+valorPorcDif);
                                            if (valorPorcDif < 1) {
                                                //                                            console.log("Menos de uno "+valorPorcDif);

                                                valorPorcDif = ((1 - valorPorcDif) * 100) * (-1);
                                                //                                             console.log("porcentaje menos "+valorPorcDif);
                                            } else {
                                                if (valorPorcDif == 1) {
                                                    valorPorcDif = 0;
                                                } else {
                                                    valorPorcDif = ((1 - valorPorcDif) * 100) * -1;
                                                }
                                                //                                            console.log("mayor a uno "+valorPorcDif);
                                                //                                            
                                                //                                            console.log("porcentaje mas "+valorPorcDif);
                                            }


                                        }
                                    }
                                    cuantasCeldas++;

                                });
                                if (opRango != "suma") {
                                    trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + ReplaceNumberWithCommas(parseFloat(valorPorcDif)) + " <strong>%</strong></td>";
                                } else {
                                    trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + ReplaceNumberWithCommas(parseFloat(celdaSubTotal * 100 / totalGeneral)) + " <strong>%</strong></td>";
                                }
                                //trCampos = trCampos + "<td class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(parseFloat(celdaSubTotal*100/totalGeneral))+" <strong>%</strong></td>";
                                cuantasCeldas++;
                                trCampos = trCampos + "</tr>";
                                contienesVentas.find("tbody").append(trCampos);
                            });

                            arrColumnas['porc'] = 100;
                            arrCuantos['porc'] = "";

                            var totalCampos = cuantasColumnas - 2;
                            // linea final con subtotal


                            var trTotal = "<tr><td colspan='2'>Total Ventas</td>\n";
                            // subtotales por columna
                            //                        for (var po in arrColumnas) {
                            //                            if(po=="porc"){
                            //                                trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(arrColumnas[po])+ " %</strong></td>\n";
                            //                            }else{
                            //                                trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(arrColumnas[po])+ simbDer+" </strong></td>\n";
                            //                            }
                            //                        }
                            var valorSubtotalCol = 0;
                            for (var po in arrColumnas) {
                                if (opRango != "suma") {
                                    if (po == "porc") {
                                        // evaluo la division
                                        //                                     console.log("totalValorA "+totalValorA);
                                        //                                    console.log("totalGeneral "+totalValorB);
                                        if (totalValorA == 0) {
                                            valorSubtotalCol = 1;
                                        } else {
                                            valorSubtotalCol = totalValorB / totalValorA;
                                        }

                                        if (valorSubtotalCol < 1) {
                                            valorSubtotalCol = ((1 - valorSubtotalCol) * 100);
                                            //                                          valorPorcDif = ((1-valorPorcDif) *100 )*(-1);

                                        } else {
                                            if (valorSubtotalCol == 1) {
                                                valorSubtotalCol = 0;
                                            } else {
                                                valorSubtotalCol = (1 - valorSubtotalCol) * 100 * -1;
                                            }
                                        }

                                        trTotal += "<td class='dt-right dt-nowrap'><strong>" + ReplaceNumberWithCommas(valorSubtotalCol) + " %</strong></td>\n";
                                    } else {
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>" + simbIzq + "" + ReplaceNumberWithCommas(arrColumnas[po]) + simbDer + " </strong></td>\n";
                                    }
                                } else {
                                    if (po == "porc") {
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>" + ReplaceNumberWithCommas(arrColumnas[po]) + " %</strong></td>\n";
                                    } else {
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>" + simbIzq + "" + ReplaceNumberWithCommas(arrColumnas[po]) + simbDer + " </strong></td>\n";
                                    }
                                }
                            }
                            trTotal += "</tr>";

                            //                        console.log(arrColumnas);
                            // notas de credito
                            /*
                             *  si no viene el array de notas de credito saco los dos renglones de nc y d total gral.
                             */
                            if (pepe.impNC !== undefined) {
                                trTotal += "<tr><td colspan='2'>Total NC por importe <br> Total Descuentos </td>";
                                var totalNC = 0;
                                var textoTotGeneral = "";
                                var valorTotal = 0;
                                $.each(pepe.impNC, function(pos, renglones) {
                                    //                                console.log(arrColumnas[pos]);
                                    //                                console.log((renglones));

                                    totalNC = totalNC + parseFloat(renglones);
                                    valorTotal = parseFloat(arrColumnas[pos]) + parseFloat(renglones);
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>" + simbIzq + "(" + ReplaceNumberWithCommas(renglones) + simbDer + " )</strong></td>";
                                    textoTotGeneral += "<td class='dt-right dt-nowrap'><strong>" + simbIzq + "" + ReplaceNumberWithCommas(valorTotal) + " </strong></td>";
                                });

                                trTotal += "<td class='dt-right dt-nowrap'><strong>" + simbIzq + "(" + ReplaceNumberWithCommas(totalNC) + simbDer + " )</strong></td>";
                                if (opRango != "suma") {
                                    //                                console.log(valorTotalB);
                                    //                                console.log(valorTotalA);
                                    var indice = parseFloat(valorTotalB / valorTotalA);
                                    //                                console.log(indice);
                                    var porciento = 0;
                                    if (indice < 1) {
                                        porciento = (1 - indice) * 100;
                                    } else {
                                        if (indice == 1) {
                                            porciento = 0;
                                        } else {
                                            porciento = ((1 - indice) * 100) * -1;
                                        }
                                    }
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>" + ReplaceNumberWithCommas(porciento) + "%</strong></td></tr>";
                                } else {
                                    trTotal += "<td></td></tr>";
                                }
                                //                            trTotal += "<td></td></tr>";

                                //                        total general


                                trTotal += "<tr><td colspan='2'>Total General </td>" + textoTotGeneral + "<td class='dt-right dt-nowrap'><strong>" + simbIzq + ReplaceNumberWithCommas(totalGeneral + totalNC) + simbDer + " </strong></td><td></td></tr>";
                            }
                            // totales de registro por columna
                            trTotal += "<tr><td colspan='2'>Total Registros</td>\n";
                            // subtotales por columna
                            for (var po in arrCuantos) {
                                if (po == "porc") {
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>-</strong></td>\n";
                                } else {
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>" + arrCuantos[po] + " </strong></td>\n";
                                }
                            }
                            trTotal += "</tr>";
                            contienesVentas.find("tfoot").append(trTotal);
                            //contienesVentasRubro.DataTable();  
                            //                        console.log("itotal:===>"+itotal);
                            //                        console.log(contienesVentas.html());
                            var tt = contienesVentas.DataTable({
                                "ordering": true,

                                dom: 'lBfrtip',
                                buttons: [
                                    'excel',
                                    {
                                        extend: 'pdf',
                                        orientation: 'landscape'
                                    }
                                ],
                                //                            "order":[[0,'asc'],[itotal,'desc']]
                                "order": [
                                    [itotal, 'desc']
                                ]

                            });

                            // ordenamiento con la columna de indices
                            tt.on('order.dt search.dt', function() {
                                tt.column(0, {
                                    search: 'applied',
                                    order: 'applied'
                                }).nodes().each(function(cell, i) {
                                    cell.innerHTML = i + 1;
                                });

                            }).draw();


                            if (grafico == 1) {
                                /*
                                 * GRAFICO BARRA
                                 * **/
                                /*GRAFICO*/
                                drawChart(pepe.gdata, pepe.goption, "ColumnChart", "graficoVentas");

                                /**
                                 * GRAFICO DE TORTA
                                 * */
                                /* parametros*/
                                //drawChart(pepe.gdataT,pepe.goptionT,"PieChart","graficoVentasRubroT"); 
                            }
                        }
                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
                        if (s === 0) {
                            m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                            m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                            m += e;
                        }
                        alert(m);
                    }
                });
                //            console.log("todo bien?");
                //            console.log($.ajax);
                /* 
                 * VENTAS POR RUBRO 
                 * */
                $.ajax({
                    type: 'GET',
                    url: 'relay-ventas-netas.php',
                    data: {
                        "ajax": "true",

                        "fechaDesde": fechaDesde,
                        "fechaHasta": fechaHasta,
                        "tipoResumen": tipoResumen,
                        "fechaDesdeDos": fechaDesdeDos,
                        "fechaHastaDos": fechaHastaDos,
                        "rangoDoble": rangoDoble,
                        "tipo": tipo,
                        "opRango": opRango,
                        "filtrarPor": filtrarPor,
                        "queInforme": "vtr",
                        "queSalida": "html"

                    },
                    success: function(response) {
                        //                   console.log(response);
                        if (response === "vacio") {
                            // sin resultado
                            var trCampos = "<tr><td>No se encontraron resultados</td></tr>";
                            contienesVentasRubro.find("tbody").empty();
                            contienesVentasRubro.find("tbody").append(trCampos);
                        } else {
                            var pepe = jQuery.parseJSON(response);
                            //                        console.log(pepe);

                            /**
                             * TABLA HTML 
                             * */
                            if ($.fn.dataTable.isDataTable(nombreVentasRubro)) {
                                contienesVentasRubro.DataTable().destroy();
                            }
                            contienesVentasRubro.find("thead").empty();
                            contienesVentasRubro.find("tbody").empty();
                            contienesVentasRubro.find("tfoot").empty();
                            // titulos de las tablas
                            //contienesVentasRubro.find("thead").append("<tr></tr>");
                            var trTitulo = "<tr>",
                                trCabecera = "<tr>";

                            $.each(pepe.titulos, function(pos, titulo) {
                                if (titulo.rowspan > 1) {
                                    trTitulo = trTitulo + "<th colspan='" + titulo.span + "' rowspan='" + titulo.rowspan + "'>" + titulo.titulo + "</th>";
                                } else {
                                    trTitulo = trTitulo + "<th colspan='" + titulo.span + "'>" + titulo.titulo + "</th>";
                                }

                            });
                            trTitulo = trTitulo + "</tr>";
                            contienesVentasRubro.find("thead").append(trTitulo);

                            // cabeceras

                            $.each(pepe.cabeceras, function(pos, cabeza) {

                                trCabecera = trCabecera + "<th class='dt-right'>" + cabeza + "</th>";

                            });
                            //console.log(trCabecera);
                            contienesVentasRubro.find("thead").append(trCabecera);
                            // renglones 
                            var cuantasCeldas = 0;
                            var cuantosReng = 0;
                            var totalGeneral = 0;
                            $.each(pepe.data, function(pos, renglones) {
                                var trCampos = "<tr>";
                                cuantosReng++;
                                $.each(renglones, function(posi, celda) {
                                    if (posi != "cat") {
                                        if (posi != 0) {
                                            trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + simbIzq + ReplaceNumberWithCommas(celda) + simbDer + "</td>";
                                        } else {
                                            trCampos = trCampos + "<td class='dt-left'>" + celda + "</td>";
                                        }
                                    }

                                    if (posi == "subt") {
                                        totalGeneral += parseFloat(celda);
                                    }
                                    cuantasCeldas++;

                                });
                                trCampos = trCampos + "</tr>";
                                contienesVentasRubro.find("tbody").append(trCampos);
                            });
                            var totalCampos = (cuantasCeldas / cuantosReng) - 3;
                            // linea final con subtotal
                            var trTotal = "<tr><td>Total Ventas</td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>" + simbIzq + ReplaceNumberWithCommas(totalGeneral) + simbDer + " </strong></td></tr>";
                            if (pepe.impNC !== undefined) {
                                var totalNC = parseFloat(pepe.impNC);
                                trTotal += "<tr><td>Total NC por importe <br> Total Descuentos </td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>(" + simbIzq + ReplaceNumberWithCommas(pepe.impNC) + simbDer + " )</strong></td></tr>";
                                trTotal += "<tr><td>Total General </td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>" + simbIzq + ReplaceNumberWithCommas(totalGeneral + totalNC) + simbDer + " </strong></td></tr>";

                            }
                            contienesVentasRubro.find("tfoot").append(trTotal);
                            contienesVentasRubro.DataTable({
                                dom: 'lBfrtip',
                                buttons: [
                                    'excel',
                                    {
                                        extend: 'pdf',
                                        orientation: 'landscape'
                                    }
                                ]
                            });


                            if (grafico == 1) {

                                /*
                                 * GRAFICO BARRA
                                 * **/
                                /*GRAFICO*/
                                drawChart(pepe.gdata, pepe.goption, "ColumnChart", "graficoVentasRubro");

                                /**
                                 * GRAFICO DE TORTA
                                 * */
                                /* parametros*/
                                drawChart(pepe.gdataT, pepe.goptionT, "PieChart", "graficoVentasRubroT");
                            }
                        }
                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
                        if (s === 0) {
                            m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                            m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                            m += e;
                        }
                        alert(m);
                    }
                }); /* fin ventas por rubro*/
                /* 
                 * VENTAS POR RUBRO X PROVEEDOR
                 * */
                $.ajax({
                    type: 'GET',
                    url: 'relay-ventas-netas.php',
                    data: {
                        "ajax": "true",

                        "fechaDesde": fechaDesde,
                        "fechaHasta": fechaHasta,
                        "tipoResumen": tipoResumen,
                        "fechaDesdeDos": fechaDesdeDos,
                        "fechaHastaDos": fechaHastaDos,
                        "opRango": opRango,
                        "filtrarPor": filtrarPor,
                        "tipo": tipo,
                        "rangoDoble": rangoDoble,
                        "queInforme": "vtrp",
                        "queSalida": "html"

                    },
                    success: function(response) {
                        //                   console.log(response);
                        if (response === "vacio") {
                            // sin resultado
                            var trCampos = "<tr><td>No se encontraron resultados</td></tr>";
                            contienesVentasRubroP.find("tbody").empty();
                            contienesVentasRubroP.find("tbody").append(trCampos);
                        } else {
                            var pepe = jQuery.parseJSON(response);
                            //                        console.log(pepe);

                            /**
                             * TABLA HTML 
                             * */
                            if ($.fn.dataTable.isDataTable(nombreVentasRubroP)) {
                                contienesVentasRubroP.DataTable().destroy();
                            }
                            contienesVentasRubroP.find("thead").empty();
                            contienesVentasRubroP.find("tbody").empty();
                            contienesVentasRubroP.find("tfoot").empty();
                            // titulos de las tablas
                            //contienesVentasRubroP.find("thead").append("<tr></tr>");
                            var trTitulo = "<tr>",
                                trCabecera = "<tr>";

                            $.each(pepe.titulos, function(pos, titulo) {
                                if (titulo.rowspan > 1) {
                                    trTitulo = trTitulo + "<th colspan='" + titulo.span + "' rowspan='" + titulo.rowspan + "'>" + titulo.titulo + "</th>";
                                } else {
                                    trTitulo = trTitulo + "<th colspan='" + titulo.span + "'>" + titulo.titulo + "</th>";
                                }

                            });


                            trTitulo = trTitulo + "</tr>";
                            contienesVentasRubroP.find("thead").append(trTitulo);

                            // cabeceras

                            var itotal = 2;
                            $.each(pepe.cabeceras, function(pos, cabeza) {

                                trCabecera = trCabecera + "<th class='dt-right'>" + cabeza + "</th>";
                                itotal++;
                            });
                            //                        console.log("itotal"+itotal);
                            //console.log(trCabecera);
                            contienesVentasRubroP.find("thead").append(trCabecera);
                            // renglones 
                            var cuantasCeldas = 0;
                            var cuantosReng = 0;
                            var totalGeneral = 0;

                            $.each(pepe.data, function(pos, rubro) {

                                $.each(rubro, function(posp, prov) {

                                    ///agrego los campos
                                    var trCampos = "<tr>";
                                    cuantosReng++;
                                    $.each(prov, function(posi, celda) {
                                        if ($.isNumeric(celda)) {
                                            //if(posi!="rubro"&&posi!="proveedor"){
                                            if (posi == "port") {
                                                trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + ReplaceNumberWithCommas(celda) + "%</td>";
                                            } else {
                                                trCampos = trCampos + "<td class='dt-right dt-nowrap'>" + simbIzq + ReplaceNumberWithCommas(celda) + simbDer + "</td>";
                                            }
                                        } else {
                                            trCampos = trCampos + "<td class='dt-left'>" + celda + "</td>";
                                        }
                                        if (posi == "subt") {
                                            //console.log(celda.toFixed(2));

                                            totalGeneral += parseFloat(celda);
                                            //totalGeneral = totalGeneral.toFixed(2);
                                        }
                                        cuantasCeldas++;
                                    });
                                    trCampos = trCampos + "</tr>";
                                    contienesVentasRubroP.find("tbody").append(trCampos);
                                });

                            });
                            //console.log(contienesVentasRubroP);
                            var totalCampos = (cuantasCeldas / cuantosReng) - 2;
                            // linea final con subtotal
                            //var trTotal = "<tr><td>Total</td><td colspan='"+totalCampos+"'></td><td class='dt-right dt-nowrap'><strong><i class='fa fa-usd'></i>"+totalGeneral.toFixed(2)+" </strong></td></tr>";
                            var trTotal = "<tr><td>Total Ventas</td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>" + simbIzq + ReplaceNumberWithCommas(totalGeneral) + simbDer + " </strong></td></tr>";
                            if (pepe.impNC !== undefined) {
                                var totalNC = parseFloat(pepe.impNC);

                                trTotal += "<tr><td>Total NC por importe <br> Total Descuentos </td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>(" + simbIzq + ReplaceNumberWithCommas(pepe.impNC) + simbDer + " )</strong></td></tr>";
                                trTotal += "<tr><td>Total General </td><td colspan='" + totalCampos + "'></td><td class='dt-right dt-nowrap'><strong>" + simbIzq + ReplaceNumberWithCommas(totalGeneral + totalNC) + simbDer + " </strong></td></tr>";
                            }

                            contienesVentasRubroP.find("tfoot").append(trTotal);
                            contienesVentasRubroP.DataTable({
                                "ordering": true,

                                //"order":[[0,'asc'],[itotal,'desc']]

                                "order": [
                                    [0, 'asc'],
                                    [itotal, 'desc']
                                ],
                                dom: 'lBfrtip',
                                buttons: [
                                    'excel',
                                    {
                                        extend: 'pdf',
                                        orientation: 'landscape'
                                    }
                                ]
                            });
                        }

                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
                        if (s === 0) {
                            m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                            m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                            m += e;
                        }
                        alert(m);
                    }
                }); /* fin ventas por rubro proveedor*/

            });
            /*
             * buscar los valores del filtro 
             */

            $('#filtrarPor').change(function() {
                var filtro = $(this).val(),
                    listado = $('#seleccionFiltro');
                //        console.log(filtro);
                if (filtro == "") {
                    return false;
                }

                $.ajax({
                    type: 'POST',
                    url: 'relay-ventas-netas.php',
                    data: {
                        "ajax": "true",
                        "tabla": filtro,
                        "queInforme": "seleccion"


                    },
                    success: function(response) {
                        //                    console.log(response);
                        var listaVuelta = jQuery.parseJSON(response);
                        listado.val("");
                        //                    listado.empty();
                        //                    listado.html('<option value="">- todos -</option>');
                        //                    listado.append(response);
                        $("#seleccionFiltro").autocomplete({
                            source: listaVuelta,
                            select: function(event, ui) {
                                event.preventDefault();
                                //                        console.log(ui.item.label);
                                //                        console.log($(this).attr("alt"));
                                $(this).attr("alt", ui.item.value);
                                $(this).val(ui.item.label);
                            }

                        });
                        listado.focus();
                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
                        if (s === 0) {
                            m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                            m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                            m += e;
                        }
                        alert(m);
                    }
                });
            });


            /* funcion para agregar filtros*/
            $('#addFiltro').on('click', function() {
                var listaFiltro = $('#listaFiltro'),
                    textFiltro = $('#filtroSelec'),
                    filtro = $('#filtrarPor').val(),
                    seleccion = $('#seleccionFiltro').attr("alt").split("|");
                var tFiltro = textFiltro.val();
                //agregar item a la lista
                var indiceLi = listaFiltro.children().length + 1;
                if (seleccion !== "" && seleccion[1] !== undefined) {
                    listaFiltro.append('<li id="' + indiceLi + '"> <i class="fas fa-check-square fa-lg fa-fw"></i> ' + filtro + ' - ' + seleccion[1] + ' <a class="borrarLi" rel="listaFiltro|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fas fa-trash fa-lg fa-fw"></i></a></li>');
                    tFiltro = tFiltro + filtro + '|' + seleccion[0] + '|' + seleccion[1] + '|' + indiceLi + '||';
                    textFiltro.val(tFiltro);

                    $('.borrarLi').on('click', function() {
                        var valorLi = $(this).attr("rel").split("|"),
                            textFiltro = $('#filtroSelec'),
                            arrFiltro = $('#filtroSelec').val().split("||");
                        var ul = valorLi[0],
                            li = valorLi[1] - 1,
                            liObj = valorLi[1],
                            textoFiltro = "";

                        for (var po in arrFiltro) {
                            if (arrFiltro[po] != "") {
                                var arrLinea = arrFiltro[po].split('|');
                                var iLi = arrLinea[3] - 1;
                                if (iLi != li) {

                                    textoFiltro = textoFiltro + arrFiltro[po] + "||";
                                }
                            }
                        }
                        textFiltro.val(textoFiltro);
                        $('#' + ul + ' #' + liObj).remove();
                    });
                    $('#seleccionFiltro').attr("alt", "");
                    $('#seleccionFiltro').val("");
                }
            });
            $('#verFiltros').on("click", function() {
                if ($(this).hasClass('fa-chevron-down')) {
                    $(this).removeClass('fa-chevron-down');
                    $(this).addClass('fa-chevron-up');
                } else {
                    $(this).removeClass('fa-chevron-up');
                    $(this).addClass('fa-chevron-down');
                }


                $(this).toggleClass('iconoAzul');
                $('#formBusca').toggle();
            });
            $('#formBusca').hide();
        });
    </script>
</head>

<body>
    <div id="wrapper">
        <?php
        require_once $barra;
        ?>

        <div id="content">
            <div class="buscador">
                <label>Parámetros:</label> <i class="fas fa-chevron-down fa-lg " id="verFiltros"></i>
                <form id="formBusca" name="formBusca" method="POST" action="">

                    <div class="control">
                        <label for="campoPeriodo" class="parametros">Periodo:
                            <select name="campoPeriodo" id="campoPeriodo" class="param">
                                <option value="dia">Diario</option>
                                <option value="semana">Semanal</option>
                                <option selected="selected" value="mes">Mensual</option>

                            </select>
                        </label>
                    </div>
                    <div class="separador10px"></div>
                    <div class="control">
                        <label for="verInforme" class="parametros">Totalizar por:
                            <select name="verInforme" id="verInforme" required="required" class="param">
                                <option value=""> - seleccionar -</option>
                                <option value="un">Unidades (Un)</option>
                                <option value="peso">Peso (Kg)</option>
                                <option value="monto" selected="selected">Monto ($)</option>

                            </select>
                        </label>
                    </div>
                    <div class='buscadorDentro'>
                        <div class="titulo">
                            Rangos
                        </div>
                        <div class="tituloFecha">

                            <div id="buscaFecha" class="control">
                                <label>Primario </label><br>
                                <label for="fechaDesde" class="parametros">Desde: <input type="date" name="fechaDesde" id="fechaDesde" required="required"></label>
                                <br>
                                <label for="fechaHasta" class="parametros">Hasta: <input type="date" name="fechaHasta" id="fechaHasta" required="required"></label>
                            </div>
                        </div>

                        <div class="tituloFecha">
                            <div id="buscaFecha" class="control">
                                <label>Secundario</label><br>
                                <label for="fechaDesdeDos" class="parametros">Desde: <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"></label>
                                <br>
                                <label for="fechaHastaDos" class="parametros">Hasta: <input type="date" name="fechaHastaDos" id="fechaHastaDos"></label>
                            </div>
                            <div id="tipoComparacion" class="control">
                                <label class="parametros">Operación:
                                    <select id="tipoOperacion" name="tipoOperacion" class="param">
                                        <option value="suma" selected="">Suma</option>
                                        <option value="sumag">Suma agrupada</option>
                                        <option value="resta">Diferencia</option>
                                    </select>
                                </label>
                            </div>
                        </div>
                    </div>


                    <div class="control">
                        <label for="agrupoPor" class="parametros">Listar:
                            <select name="agrupoPor" id="agrupoPor" required="required" class="param">
                                <option value=""> - seleccionar -</option>
                                <option value="cliente">Cliente</option>
                                <option value="tipocliente">Tipo Cliente</option>
                                <option value="articulo">Articulo</option>
                                <?php if (isset($_SESSION['vendedor_a_cargo']) && !empty($_SESSION['vendedor_a_cargo'])) : ?>
                                    <option value="vendedor">Vendedor</option>
                                <?php endif; ?>

                            </select>
                        </label>
                    </div>
                    <div class="buscadorDentro">
                        <div class="separador25px"></div>
                        <div class="titulo">
                            Filtros
                        </div>
                        <div class="control">

                            <label for="filtrarPor" class="parametros">Tipo:
                                <select name="filtrarPor" id="filtrarPor" class="param">
                                    <option value=""> - Ninguno -</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="tipocliente">Tipo Cliente</option>
                                    <option value="articulo">Articulos</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="zona">Zona</option>
                                    <option value="marca">Marca</option>
                                    <?php if (isset($_SESSION['vendedor_a_cargo']) && !empty($_SESSION['vendedor_a_cargo'])) : ?>
                                        <option value="vendedor">Vendedor</option>
                                    <?php endif; ?>

                                </select>
                            </label>
                        </div>

                        <div class="control cien">
                            <label for="seleccionFiltro" class="parametros">Opción: </label><br>
                            <input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
                            <button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fas fa-plus fa-lg fa-fw"></i> </button>
                        </div>
                        <div class="separador"></div>
                        <div class="control">
                            <label for="listaFiltro" class="parametros">Selección:
                                <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>

                                <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">

                            </label>
                        </div>
                    </div>

                    <div class="control">

                        <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                        <label for="aceptaGrafico"> Ver gráficos <i class="fa fa-bar-chart fa-1x"></i> </label>
                    </div>
                    <div class="separador10px"></div>
                    <div class="control">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo grande azul">
                            <i class="fas fa-search fa-1x"></i> Generar
                        </button>
                        <!--                        <input type="button" name="botonBuscar" class="buttons" id="botonBuscar" value="Buscar">-->
                    </div>

                </form>
            </div>

            <div id="spinner" class="spinner" style="display:none;">
                <img src="_img/logo-administranet-ecommerce.png">
                <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla">
                <h1>Estadísticas de Venta</h1>
                <h2 class="alignLeft">Ventas netas</h2>
                <table class="display" cellspacing="1" id="myTableVentas" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
                <h3 class="alignLeft">Gráfico</h3>
                <div id="graficoVentas"></div>

                <h2 class="alignLeft">Ventas netas por rubro</h2>
                <table class="display" cellspacing="1" id="myTableVentasRubro" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>

                <h3 class="alignLeft">Gráfico</h3>

                <div id="graficoVentasRubro"></div>
                <div id="graficoVentasRubroT"></div>


                <h2 class="alignLeft">Ventas netas por rubro por proveedor</h2>

                <table class="display" cellspacing="1" id="myTableVentasRubroP" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>

                <!--                <h3 class="alignLeft">Ventas netas</h3>
                <canvas id="graficoVentasRubroProv" width="600" height="400"></canvas>-->



            </div>
        </div>

        <?php require_once 'footer.php'; ?>

    </div>
    <div id="basic-modal-content"> </div>
</body>

</html>