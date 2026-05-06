<?php require_once 'sesion.inc.php';?>
<?php 
/**
 * variables de configuracion para colocar los encabezados
 * UTILIDAD
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>utilidad inflacion gerencia | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    
     <?php require_once 'cabecera.php';?>
    <?php 
      
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();
        
    ?>
<script>
     google.load('visualization', '1',null); // No 'packages' section.

    function drawChart(dataG,optionsG,tipo,quien) {
        var wrap = new google.visualization.ChartWrapper();
        wrap.setChartType(tipo);        
        wrap.setContainerId(quien);
        wrap.setDataTable(dataG);
        wrap.setOptions(optionsG);
        wrap.draw();
    }
$.fn.dataTable.TableTools.defaults.aButtons = [ "copy", "csv", "xls" ];    
$.extend( $.fn.dataTable.defaults, {
    "language": {
        "emptyTable":     "No data available in table",
        "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
        "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        "thousands":      "",
        "lengthMenu":     "Ver _MENU_ entradas",
        "loadingRecords": "Loading...",
        "processing":     "Processing...",
        "search":         "Buscar:",
        "zeroRecords":    "No matching records found",
        "paginate": {
            "first":      "Primero",
            "last":       "Ultimo",
            "next":       "Siguiente",
            "previous":   "Anterior"
        },
        "aria": {
            "sortAscending":  ": activate to sort column ascending",
            "sortDescending": ": activate to sort column descending"
        }
    },
    "searching": true,
    "ordering": false,
    "paging": true,
    "autoWidth":true
} );

 $(document).ready(function(){
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
    
    $('#filtrarPor').change(function(){
       var filtro = $(this).val(),
           listado = $('#seleccionFiltro'); 
        //console.log(filtro);
        
        
        $.ajax({
                type: 'POST',
                url: 'relay-ventas-netas-gerencia.php',
                data:{
                    "ajax" : "true",
                    "tabla" : filtro,
                    "queInforme" : "seleccion"
                    
                    
                },
                success: function(response) {
                    console.log(response);
                    var listaVuelta = jQuery.parseJSON(response);
                    listado.val("");
//                    listado.empty();
//                    listado.html('<option value="">- todos -</option>');
//                    listado.append(response);
                    $( "#seleccionFiltro" ).autocomplete({
                    source: listaVuelta,
                    select: function( event, ui ) {
                        event.preventDefault();
//                        console.log(ui.item.label);
//                        console.log($(this).attr("alt"));
                        $(this).attr( "alt", ui.item.value );
                        $(this).val(ui.item.label);
                    }
                   
                  });
                   listado.focus();
                },
                error: function(x, e) {
                        var s = x.status, 
                                m = 'Ajax error: ' ; 
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
        
     // boton para buscar coincidencias
     $('#botonBuscar').click(function(){
         
         function ReplaceNumberWithCommas(yourNumber) {
            //Seperates the components of the number
            var yourNumberF = parseFloat(yourNumber).toFixed(2);
            var n= yourNumberF.toString().split(".");
            //Comma-fies the first part
            n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            //Combines the two sections
            return n.join(",");
        }
                        
         var contienesVentas = $('#myTableVentas'),
            nombreVentas    = "#myTableVentas",
            contienesVentasRubro = $('#myTableVentasRubro'),
            nombreVentasRubro    = "#myTableVentasRubro",
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            fechaDesdeDos = $('#fechaDesdeDos').val(),
            fechaHastaDos = $('#fechaHastaDos').val(),
            rangoDoble = 0,
//            opRango = $('#tipoOperacion').val(),
            datasetGrafico="",
            tipoResumen = $('#campoPeriodo').val(),
            tipo =  $('#verInforme').val(), 
            listarPor = $('#agrupoPor').val(),
            filtrarPor = $('#filtroSelec').val(),
            tipoInflacion = $('#tipoInflacion').val(),
            simbDer ="",
            simbIzq ="",
            
            vuelta  = 0,
            grafico = 0,
            puntoVenta = $('#pvSelec').val(),
            verGrafico = $('#aceptaGrafico');
//            console.log($('#tipoInflacion'));
            /* valido los filtros al menos el del punto de venta no puede
             * venir vacio
             * */

            if(verGrafico.is(':checked')===true){
                grafico = 1;
            }else{
                grafico = 0;
            }
            
            if(tipo==""){
                alert("Debe seleccionar tipo de información ");
                
                return false;
                $('#verInforme').focus();
            }
            if(listarPor==""){
                alert("Debe seleccionar un campo por el cual listar el informe");
              return false;
              $('#agrupoPor').focus();
            }
            if(tipoResumen==""){
                alert("Debe seleccionar un período de resumen");
              return false;
               $('#campoPeriodo').focus();
            }
            if(fechaDesde==""){
                alert("Debe seleccionar una fecha desde primaria");
              return false;
              $('#fechaDesde').focus();
            }
            if(fechaHasta==""){
                alert("Debe seleccionar una fecha hasta secundaria");
              return false;
              $('#fechaHasta').focus();
            }
            // verifico si aplico el rango doble de fecha.
            if(fechaDesdeDos!=="" && fechaHastaDos!==""){
                rangoDoble = 1;                
              
            }
            
            if(puntoVenta==""){
              alert("Debe seleccionar un punto de venta");
              return false;
              $('#puntoVenta').focus();
            }
            
            
         /*
          * VENTAS NETAS GERENCIALES
          * ========================
          * Nota mental: no se puede hacer colspan con los th para listar
          * las columnas si no da error de orden,
          */
         /*
          * configuro el simbolo a mostrar.
          */
         switch(tipo){
             case "un":
                 simbDer = "";
                 simbIzq = "";
                 break;
             case "monto":
                 simbDer = "";
                 simbIzq = "<i class='fa fa-usd'></i>";
                 break;
             case "peso":
                 simbDer = "";
                 simbIzq = "";
                 break;
         }
         simbDer = "";
         simbIzq = "<i class='fa fa-usd'></i>";
         $("#spinner").hide();
         $.ajax({
                type: 'POST',
                url: 'relay-ventas-netas-gerencia.php',
                data:{
                    "ajax" : "true",
                                        
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
//                    "fechaDesdeDos" : fechaDesdeDos,
//                    "fechaHastaDos" : fechaHastaDos,
                    "tipoResumen": tipoResumen,
//                    "tipo" :  tipo, 
                    "listarPor" : listarPor,
                    "filtrarPor" : filtrarPor,                    
                    "puntoVenta" : puntoVenta,
//                    "grafico"   : grafico,
//                    "rangoDoble": 1,
//                    "opRango"   : opRango,
                    "tipoInflacion" : tipoInflacion,
                    "queInforme" : "uti",
                    "queSalida" : "html"
                    
                },
                success: function(response) {
                   console.log(response);
                   if(response==="vacio"){
                        // sin resultado
                        var trCampos="<tr><td>No se encontraron resultados</td></tr>";
                        contienesVentasRubro.find("tbody").empty();
                        contienesVentasRubro.find("tbody").append(trCampos);
                    }else{
                        var pepe = jQuery.parseJSON(response);
                        console.log(pepe);

                        /**
                         * TABLA HTML 
                         * */
                        if ( $.fn.dataTable.isDataTable( nombreVentasRubro)) {
                            contienesVentasRubro.DataTable().destroy();
                        }
                        contienesVentasRubro.find("thead").empty();
                        contienesVentasRubro.find("tbody").empty();
                        contienesVentasRubro.find("tfoot").empty();
                        // titulos de las tablas

                        var trTitulo = "<tr>",
                            trCabecera ="<tr><th></th>",
                            trTituloLista="";
//                            console.log(pepe.titulos);
// ordenar bien los titulos porque me los toma mal u obtener la fecha con el mes en dos digitos.
                        $.each(pepe.titulos,function(pos,titulo){
                            if(pos==0){
                                trTituloLista = "<th>"+titulo.titulo+"</th>";
                            }
                            else {
                                if(titulo.rowspan>1){
                                    trTitulo = trTitulo +"<th colspan='"+titulo.span+"' rowspan='"+titulo.rowspan+"'>"+titulo.titulo+"</th>";
                                    trTitulo = trTitulo +"<th colspan='"+titulo.span+"' rowspan='2'>"+titulo.titulo+"(%)</th>";
                                }else{
                                    trTitulo = trTitulo +"<th colspan='"+titulo.span+"'>"+titulo.titulo+"</th>";
                                }
                               
                            }
                        });
                        trTitulo = trTitulo +"</tr>";
                        contienesVentasRubro.find("thead").append(trTitulo);

                        // cabeceras
                        // coloco el listar aca
                        //console.log(trTituloLista);
                        trCabecera = trCabecera + trTituloLista;
                        //console.log(pepe.cabeceras);
                        var itotal = 1;
                        $.each(pepe.cabeceras,function(pos,cabeza){

                            trCabecera = trCabecera +"<th class='dt-right'>"+cabeza+"</th>";
                            itotal++;

                        });
//                        console.log("itotal"+itotal);
//                        console.log("cabecera: ="+trCabecera);
                        contienesVentasRubro.find("thead").append(trCabecera);
                        // renglones 
                        var cuantasCeldas =0;
                        var cuantosReng = 0;
                        var totalGeneral =0;
                        var totalCosto =0;
                        var totalNeto=0;
                        var totalEsperada=0;
                        var totalUtilidad=0;
                        var totalNCInf=0;
                        var cuantasColumnas = 0;
                        var arrColumnas= [];
                        // sumo el total de renglones distintos de cero por columna.
                        //tengo varios subtotales.
                        var arrCuantos = [];
                        // calculo el total general
                        // ver de que capaz no haga falta.
                        // pasar los subtotales como un footer para poder operar por pagina 
                        //  y mostrar los valores negativos y positov.s
                        // dibujo cada columna agrego porcentaje
                        $.each(pepe.data,function(pos,renglones){
                            var trCampos = "<tr>";
                            var celdaSubTotal =0;
                            cuantosReng++;
                            trCampos = trCampos + "<td></td>";
                            cuantasColumnas =0;
                            $.each(renglones, function(posi,celda){
                                cuantasColumnas++;
                                 cuantasCeldas++;
                                if(posi!=0){
                                     // sumatoria de las columnas.
                                    //console.log(arrColumnas[posi]);
                                    if(arrColumnas[posi]===undefined){
                                        arrCuantos[posi] = 1;
                                        arrColumnas[posi] = parseFloat(celda);
                                    }
                                    else{
                                        if(parseFloat(celda)!==0){
                                             arrCuantos[posi] = arrCuantos[posi] + 1;
                                        }
                                        arrColumnas[posi] = arrColumnas[posi] + parseFloat(celda);
                                        
                                    }
                                    if(posi=="porc"||posi=="indice"||posi=="resultado"){
                                        trCampos =  trCampos + "<td class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(celda)+"</td>";
                                    }else{
                                        
                                        trCampos =  trCampos + "<td class='dt-right dt-nowrap'>"+simbIzq+""+ReplaceNumberWithCommas(celda)+simbDer+"</td>";
                                    }
                                }else{
                                    
                                    
                                        trCampos =  trCampos + "<td class='dt-left'><div class='corto'>"+celda+"</div></td>";
                                    
                                }
//                                console.log(posi);
//                               
                                celdaSubTotal += parseFloat(celda); 
                                
                                cuantasCeldas++;

                            });
//                            trCampos = trCampos + "<td class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(parseFloat(celdaSubTotal*100/totalGeneral))+" <strong>%</strong></td>";
//                             cuantasCeldas++;
                            trCampos = trCampos+"</tr>";
                            contienesVentasRubro.find("tbody").append(trCampos);                                                
                        });
                        
//                        arrColumnas['porc'] = 100;
//                        arrCuantos['porc'] ="";
    
                        var totalCampos=cuantasColumnas-2;
                        // linea final con subtotal
//                        console.log("ArrColumnas: " +arrColumnas);
                        
                        var trTotal = "<tr><td colspan='2'>Total</td>\n";
                        // subtotales por columna
                        for (var po in arrColumnas) {
                            
                            if(po==3){
                                totalNeto=arrColumnas[po];
                            }
                            if(po==4){
                                totalCosto = arrColumnas[po];
                            }
                            if(po=="esperada"){
                                totalEsperada = arrColumnas[po];
                            }
//                            console.log("po: "+po+" arrColumna[po]=>"+arrColumnas[po]);
                            
                            if(po=="porc"){
                                
                                var utilPorciento = totalNeto / totalCosto;
//                                console.log("neto=> "+totalNeto+" costo=> "+totalCosto+" resultado=" + totalNeto/totalCosto);
                                if(utilPorciento>=1){
                                    //utilidad positiva
                                    utilPorciento=(utilPorciento-1)*100;
                                }else{
                                    // utilidad negativa
                                    utilPorciento=((utilPorciento)*100 )*-1;
                                }
                                trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(utilPorciento)+ " %</strong></td>\n";
                            }else{
                                if(po=="indice"){
                                    
                                    var divide = arrCuantos[po];
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(arrColumnas[po]/divide)+"% </strong></td>\n";
                                }
                                if(po=="resultado"){
                                    // divido la venta neta / venta esperada -1 * 100 
                                    var resultadoTotal =((totalNeto/totalEsperada)-1)*100;
                                    
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(resultadoTotal)+"% </strong></td>\n";
                                }
                                if(po!="resultado"&&po!="indice"){
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(arrColumnas[po])+ simbDer+" </strong></td>\n";
                                 }
                            }
                        }
                        trTotal += "</tr>";
                        
//                        console.log(pepe.impNC);
                        // notas de credito
                        /*
                        *  si no viene el array de notas de credito saco los dos renglones de nc y d total gral.
                         */
                        if(pepe.impNC !==undefined){ 
                            trTotal += "<tr><td colspan='2'>Total NC por importe <br> Total Descuentos </td>";
                            var totalNC = 0;
                            var totalNCInf =0;
                            var textoTotGeneral = "";
                            var valorVentaNeta =0;
                            var valorCosto=0;
                            var valorVentaInfNeta=0;
                            var valorUtilidad =0;
                            var valorIndice=0;
                            console.log("indice impnc: "+pepe.impNC[0]);
                            console.log("indice impncinf: "+pepe.impNCInf[0]);
                            totalNC =parseFloat(pepe.impNC[0]);  
                            totalNCInf = parseFloat(pepe.impNCInf[0]); 
                            
//                            valorVentaNeta = parseFloat(totalNeto) + parseFloat(pepe.impNC[0]) ; 
                            
                            // recorremos las columnas con los subtotales y dibujamos de nuevo
                            for (var po in arrColumnas) {
                                // valor de celda regular
                                var valorCelda = arrColumnas[po];
//                                if(po==2){
//                                    // descuentos o notas de credito
//                                    valorCelda = totalNC;
//                                }
                                if(po==3){
                                    // calculo el nuevo neto menos las nc
                                    totalNeto=parseFloat(arrColumnas[po])+totalNC;
                                    valorCelda = totalNeto;
                                    
                                }
                                if(po==4){
                                    // valor de costo 
                                    totalCosto = parseFloat(arrColumnas[po]);
                                    valorCelda=totalCosto;
                                }
                                if(po==5){
                                    // valor de utilidad
                                    valorUtilidad = totalNeto-totalCosto;
                                    valorCelda = totalNeto-totalCosto;
                                }
                                if(po=="venta2"){
                                    valorVentaInfNeta= parseFloat(arrColumnas[po]);
                                }
                                if(po=="indice"){

                                        var divide = arrCuantos[po];
                                        valorIndice =arrColumnas[po]/divide;
//                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(arrColumnas[po]/divide)+"% </strong></td>\n";
                                }
//                                if(po=="esperada"){
//                                    totalEsperada = parseFloat(arrColumnas[po];
//                                }
//                                console.log("po: "+po+" arrColumna[po]=>"+arrColumnas[po]);
//                                console.log("po: "+po+" arrColumna[po]=>"+arrColumnas[po]);
//                                console.log("po ImporteNC =>"+po);
//                                console.log("totalNeto=>"+totalNeto+" total NC=>"+totalNC);
//                                if(po=="nc2"){
//                                    valorCelda =totalNCInf; 
//                                }
                                if(po=="porc"){

                                    var utilPorciento = totalNeto/totalCosto;
                                    console.log("neto=> "+totalNeto+" costo=> "+totalCosto+" resultado=" + totalNeto/totalCosto);
                                    if(utilPorciento>=1){
                                        //utilidad positiva
                                        utilPorciento=(utilPorciento-1)*100;
                                    }else{
                                        // utilidad negativa
                                        utilPorciento=((utilPorciento)*100 )*-1;
                                    }
                                    trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(utilPorciento)+ " %</strong></td>\n";
                                }else{
                                    if(po==1){
                                        //venta
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(arrColumnas[po])+ simbDer+" </strong></td>\n";
                                    }
                                    if(po==2){
                                        // nc descuento
                                        trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+"("+ReplaceNumberWithCommas(totalNC)+simbDer+" )</strong></td>";
                                    }
                                    if(po==3){
                                        //neto
                                        trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalNeto)+simbDer+" </strong></td>";
                                    }
                                    if(po==4){
                                        //costo
                                        trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalCosto)+simbDer+" </strong></td>";
                                    }
                                    if(po==5){
                                        // utilidad importe
                                        
                                        trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(valorUtilidad)+simbDer+" </strong></td>";
                                    }
                                    
                                    if(po=="venta2"){
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(valorVentaInfNeta)+ simbDer+" </strong></td>\n";
                                    }
                                    if(po=="nc2"){
                                       trTotal +="<td class='dt-right dt-nowrap'><strong>"+simbIzq+"("+ReplaceNumberWithCommas(totalNCInf)+simbDer+" )</strong></td>"; 
                                    }
                                    if(po=="esperada"){
                                        var totalEsperada = (valorVentaInfNeta+totalNCInf)* valorIndice;
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalEsperada)+ simbDer+" </strong></td>\n";
                                    }
//                                        if(po=="indice"||po=="resultado"){
//                                            var divide = arrCuantos[po];
//                                            trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(valorCelda/divide)+"% </strong></td>\n";
//                                        }else{
//                                            trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(valorCelda)+ simbDer+" </strong></td>\n";
//                                        }
                                    if(po=="indice"){

//                                        var divide = arrCuantos[po];
                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(valorIndice)+"% </strong></td>\n";
                                    }
                                    if(po=="resultado"){
                                        // divido la venta neta / venta esperada -1 * 100 
                                        
                                        var resultadoTotal =((totalNeto/totalEsperada)-1)*100;

                                        trTotal += "<td class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(resultadoTotal)+"% </strong></td>\n";
                                    }

//                                        if(po!="resultado"&&po!="indice"){
//                                            trTotal += "<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(arrColumnas[po])+ simbDer+" </strong></td>\n";
//                                        }
                                    
                                    
                                    
                                }
                            }
                            
                            trTotal += "</tr>";

    //                        total general


                            //trTotal += "<tr><td colspan='2'>Total General </td>"+textoTotGeneral+"<td class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalGeneral+totalNC)+simbDer+" </strong></td><td></td></tr>";
                        }
                        
                        // cantidad totales de registro por columna
                        trTotal += "<tr><td colspan='2'>Total Registros</td>\n";
                        // subtotales por columna
                        for (var po in arrCuantos) {
                            if(po=="porc"){
                                trTotal += "<td class='dt-right dt-nowrap'><strong>-</strong></td>\n";
                            }else{
                                trTotal += "<td class='dt-right dt-nowrap'><strong>"+arrCuantos[po]+ " </strong></td>\n";
                            }
                        }
                        trTotal += "</tr>";
                        contienesVentasRubro.find("tfoot").append(trTotal);
                        //contienesVentasRubro.DataTable();  
                         var tt = contienesVentasRubro.DataTable({
                            "ordering":true,
                            "language":{
                                "thousand":".",
                                "decimal":","
                            },
                            // column definitions
                            "columnDefs": [ {
                                "searchable": false,
                                "orderable": false,
                                "targets": 0
                            } ],
//                            "order":[[0,'asc'],[itotal,'desc']]
                            "order":[[itotal,'desc']],
                            "dom": 'T<"clear">lfrtip',
                            "tableTools": {
                                "sSwfPath": "_lib/swf/copy_csv_xls_pdf.swf"
                            }
                        }); 

                        // ordenamiento con la columna de indices
                        tt.on( 'order.dt search.dt', function () {
                            tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                cell.innerHTML = i+1;
                            } );
                        } ).draw();
                        
                        if(grafico==1){
                            /*
                             * GRAFICO BARRA
                             * **/
                             /*GRAFICO*/
                            drawChart(pepe.gdata,pepe.goption,"ColumnChart","graficoVentasRubro"); 

                            /**
                             * GRAFICO DE TORTA
                             * */
                            /* parametros*/
                            drawChart(pepe.gdataT,pepe.goptionT,"PieChart","graficoVentasRubroT"); 
                        }   
                    }
                },
                error: function(x, e) {
                        var s = x.status, 
                                m = 'Ajax error: ' ; 
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
            });/* fin ventas por rubro*/
            
             
    
      });
      // funcion para agregar filtros.
    $('#addFiltro').on('click', function(){
        var listaFiltro = $('#listaFiltro'),
            textFiltro = $('#filtroSelec'),
            filtro = $('#filtrarPor').val(),
            seleccion = $('#seleccionFiltro').attr("alt").split("|");            
        var tFiltro = textFiltro.val();
        //agregar item a la lista
        var indiceLi =  listaFiltro.children().length+1;
        if(seleccion!==""&&seleccion[1]!==undefined){
            listaFiltro.append('<li id="'+indiceLi+'"> <i class="fa fa-check-square"></i> '+ filtro +' - '+ seleccion[1] +' <a class="borrarLi" rel="listaFiltro|'+indiceLi+'" href="#" title="Eliminar de la lista"><i class="fa fa-trash-o fa-lg"></i></a></li>');
            tFiltro = tFiltro + filtro +'|'+seleccion[0] + '|' + seleccion[1]  +'|' + indiceLi +'||';
            textFiltro.val(tFiltro);
            
            $('.borrarLi').on('click',function(){                
                var valorLi = $(this).attr("rel").split("|"),
                    textFiltro = $('#filtroSelec'),
                    arrFiltro = $('#filtroSelec').val().split("||");
                var ul = valorLi[0],
                    li = valorLi[1]-1,
                    liObj = valorLi[1],                    
                    textoFiltro="";
                
                for (var po in arrFiltro){
                    if(arrFiltro[po]!=""){
                        var arrLinea = arrFiltro[po].split('|');
                        var iLi = arrLinea[3]-1;
                        if(iLi!=li){
                            
                            textoFiltro =  textoFiltro + arrFiltro[po]+"||";                            
                        }
                    }
                }
                textFiltro.val(textoFiltro);
                $('#'+ul+' #'+liObj).remove(); 
            });
        }
        
        $('#seleccionFiltro').attr("alt","");
        $('#seleccionFiltro').val("");
         // agregar al input una lista
    });
    // borrar un valor de la lista.
    // =====================================
    
    $('#addPv').on('click', function(){
        var listaPv = $('#listaPv'),
            textPv = $('#pvSelec'),
            seleccion = $('#puntoVenta').val().split("|");
        var tPv = textPv.val();
         var indiceLi =  listaPv.children().length+1;
        //agregar item a la lista
        if(seleccion!==""&&seleccion!==undefined){
            listaPv.append('<li  id="'+indiceLi+'"><i class="fa fa-check-square"></i> Punto venta: '+ seleccion[1] +' <a class="borrarLi" rel="listaPv|'+indiceLi+'" href="#" title="Eliminar de la lista"><i class="fa fa-trash-o fa-lg"></i></a></li>');
            tPv = tPv + seleccion[0] + '|' + seleccion[1]  +'|' + indiceLi+'||';
            //console.log(tPv);
            textPv.val(tPv);
        }
         // agregar al input una lista
        $('.borrarLi').on('click',function(){                
                var valorLi = $(this).attr("rel").split("|"),
                    textFiltro = $('#pvSelec'),
                    arrFiltro = $('#pvSelec').val().split("||");
                var ul = valorLi[0],
                    li = valorLi[1]-1,
                    liObj = valorLi[1],                    
                    textoFiltro="";
                
                for (var po in arrFiltro){
                    if(arrFiltro[po]!=""){
                        var arrLinea = arrFiltro[po].split('|');
                        var iLi = arrLinea[2]-1;
//                        console.log("iLi:"+iLi);
//                        console.log("li:"+li);
                        if(iLi!=li){
                            
                            textoFiltro =  textoFiltro + arrFiltro[po]+"||";                            
                        }
                    }
                }
                console.log(textoFiltro);
                textFiltro.val(textoFiltro);
                $('#'+ul+' #'+liObj).remove(); 
            }); 
    }); 
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
                <form id="formBusca" name="formBusca" method="POST" action="">
                    <div class="control">
                        <label for="verInforme">Ver:  
                            <select name="verInforme" id="verInforme" required="required">
                                <option value="ut"> Utilidad</option>
<!--                                <option value="un">Unidades (Un)</option>
                                <option value="peso">Peso (Kg)</option>
                                <option value="monto">Monto ($)</option>-->
                                
                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="agrupoPor">Listar:  
                            <select name="agrupoPor" id="agrupoPor" required="required">
                                <option value=""> - seleccionar -</option>
                                <option value="cliente">Cliente</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="articulo">Articulo</option>
                                <option value="proveedor">Proveedor</option>
                                <option value="zona">Zona</option>
                                <option value="rubro">Rubro</option>
                                <option value="subrubro">Sub Rubro</option>
                                
                            </select>
                        </label>
                    </div>
                     <div class="control">
                            <label for="campoPeriodo">Periodo:  
                                <select name="campoPeriodo" id="campoPeriodo" required="required">
<!--                                    <option value="dia">Diario</option>
                                    <option value="semana">Semanal</option>-->
                                    <option value="mes" selected="selected">Mensual</option>

                                </select>
                            </label>
                        </div>
                    <div class='buscadorDentro'>
                        <div class="titulo">
                            Fechas
                        </div>
                        <div class="tituloFecha">
                            
                            <div id="buscaFecha"  class="control">
                                <label>Rango primario </label><br>    
                                <label for="fechaDesde">Desde: <input type="date" name="fechaDesde" id="fechaDesde" required="required" ></label>
                                <label for="fechaHasta">Hasta: <input type="date" name="fechaHasta" id="fechaHasta" required="required"></label>
                            </div>
                        </div>
                        
                        <div class="tituloFecha">
                            
                            <div id="claseInflacion" class="control">
                                <label>Tipo inflación:
                                <select id="tipoInflacion" name="tipoInflacion">
                                    <option value="mensual">Mensual</option>
                                    <option value="anual">Anual</option>
                                </select>
                                    </label>
                            </div>    
                        </div>
                    </div>
                    <div class='buscadorDentro'>
                        <div class="titulo">
                            Punto de Venta
                        </div>    
                        <div class="control">
                            <label for="puntoVenta">P venta: 
                                <select name="puntoVenta" id="puntoVenta" >
                                    <option value="|Todos"> - todos - </option>
                                <?php echo $_SESSION["lista_pv_opc"];?>
                                </select>
                            </label>
                            <button name="addPv" id="addPv" type="button" class="botonNuevo chico azul"><i class="fa fa-check"></i> </button>
                        </div>
                        <div class="control">
                            <label for="listaPv">Selección:  
                                <ul name="listaPv" id="listaPv" class="listaSeleccionado"></ul>
                                <input type="hidden" name="pvSelec" id="pvSelec" value="" required="required">

                            </label>
                        </div>
                    </div>
                    <div class="buscadorDentro">
                        <div class="separador25px"></div>
                        <div class="titulo">
                            Filtros
                            </div>
                        <div class="control">

                            <label for="filtrarPor">Tipo:  
                                <select name="filtrarPor" id="filtrarPor">
                                    <option value=""> - seleccionar -</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="articulo">Articulo</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="zona">Zona</option>
                                    <option value="rubro">Rubro</option>
                                    <option value="subrubro">Sub Rubro</option>

                                </select>
                            </label>
                        </div>
                   
                        <div class="control">
                                <label for="seleccionFiltro">Valor a filtrar: </label>
                                <input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
                            <button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fa fa-check"></i> </button>
                        </div>
                        <div class="separador"></div>
                        <div class="control">
                            <label for="listaFiltro">Selección: 
                                <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
                                <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">

                            </label>
                        </div>
                   </div>
<!--                    <div class="separador10px"></div>
                     <div class="control">
                         
                         <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                         <label for="aceptaGrafico">  Ver gráficos <i class="fa fa-bar-chart fa-1x" ></i> </label>
                    </div>-->
                    <div class="control">
                         
                        <button  title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo chico azul">
                        <i class="fa fa-search fa-1x" ></i> 
                        </button>
                    </div>
                    
               </form>
            </div>
            
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla"  > 
                <h1>Estadísticas</h1>
                <h2 class="alignLeft">Utilidades</h2>
                <table class="display" cellspacing="1" id="myTableVentasRubro" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
                
                <h3 class="alignLeft">Gráfico</h3>
                 <div id="graficoVentasRubro"></div>
                <div id="graficoVentasRubroT"></div>
                
                
                
<!--                <h3 class="alignLeft">Ventas netas</h3>
                <canvas id="graficoVentasRubroProv" width="600" height="400"></canvas>-->
                
                
                
            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     <div id="basic-modal-content"> </div>
    </body>
</html>