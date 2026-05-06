<!DOCTYPE HTML>
<html lang="es-AR">
<head>
    
    <title>ventas gerencia | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    
     <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="_css/main_styles.css" rel="stylesheet" type="text/css" />
<!--<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">-->
<!--<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">-->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type='text/javascript'></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" type='text/javascript'></script>

<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js" type='text/javascript'></script>


<link href="_css/tablas.css" rel="stylesheet" type="text/css" />

<!--<link href="//cdn.datatables.net/tabletools/2.2.4/css/dataTables.tableTools.css" rel="stylesheet" type="text/css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js" type='text/javascript'></script>
<script src="//cdn.datatables.net/tabletools/2.2.4/js/dataTables.tableTools.js" type='text/javascript'></script>-->


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.css"/> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.js"></script>



<link href='_css/basic.css' rel='stylesheet' type='text/css' media='screen'    />
<script src='_lib/jquery.simplemodal.js' type='text/javascript' ></script>


<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>

<script>

  
jQuery(document).ready(function($){
    
    $('#modal-ncancelados-cliente').hide();
    $('#modal-consumos-cliente').hide();
    
    $('#icono-ver-fact').on("click",function(){
        var wPantalla=$(document).width();

        
         var contienes = $('#tablaCancelados'),
             ventanita = $('#modal-ncancelados-cliente'),   
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            numeroComp = $('#numeroComp').val(),
            estadoPedido =  $('#estadoPedido').val();
            //$('formBusca').submit();
         
        $.ajax({
                type: 'POST',
                url: 'relay-comp-no-cancelados-resumen.php',
//                 url: 'relay-comprobantes-ncancelados.php',
                data:{
                    "ajax" : "true",
                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "numeroComp" : numeroComp,
                    "estadoPedido" : estadoPedido
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
////                                    alert(response);  
//                        console.log(response);
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#tablaCancelados' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching:false,
                            responsive:true,
                            paging:false,
                            info:false,
                            "language": {
                                "emptyTable":     "No hay datos disponibles ",
                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
                                "infoPostFix":    "",
                                "thousands":      "",
                                "lengthMenu":     "Ver _MENU_ entradas",
                                "loadingRecords": "Loading...",
                                "processing":     "Processing...",
                                "search":         "Buscar:",
                                "zeroRecords":    "No se encontraron Registros",
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
                                
                            }
                             
                        });
                        var wVentana=0, hVentana=0;    
                            if(wPantalla>320){
                               wVentana=450;
                               hVentana=650;
                            }else{
                                wVentana=300;
                                hVentana=400;
                            }
                        /* ventana modal*/    
                        ventanita.modal({
                            minWidth:wVentana,
                           
                            minHeight:100,
                            maxHeight:hVentana,
                            close:false,
                            onShow: function(){
                                $('#cierroNcanc').on("click",function(e){
                                    e.preventDefault();
                                    var contienes2 = $('#tablaCancelados');

                                    $.modal.close();
                                    contienes2.DataTable().destroy();
                                });
                            }
                            
                            
                            
                        });
                        
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
    
    /**
     * ver consumos del ultimo año. solo 20
     * */
    $('#icono-ver-consumos').on("click",function(){    
        var wPantalla=$(document).width();  
        var contienes = $('#tablaConsumos'),
             ventanita = $('#modal-consumos-cliente');
            
            
         
        $.ajax({
                type: 'POST',
                url: 'relay-consumos-resumen.php',
//                 url: 'relay-comprobantes-ncancelados.php',
                data:{
                    "ajax" : "true"
                    
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
////                                    alert(response);  
//                        console.log(response);
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#tablaConsumos' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching:false,
                            responsive:true,
                            paging:false,
                            info:false,
                            ordering:false,
                            "language": {
                                "emptyTable":     "No hay datos disponibles ",
                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
                                "infoPostFix":    "",
                                "thousands":      "",
                                "lengthMenu":     "Ver _MENU_ entradas",
                                "loadingRecords": "Loading...",
                                "processing":     "Processing...",
                                "search":         "Buscar:",
                                "zeroRecords":    "No se encontraron Registros",
                                "paginate": {
                                    "first":      "Primero",
                                    "last":       "Ultimo",
                                    "next":       "Siguiente",
                                    "previous":   "Anterior"
                                },
                                "aria": {
                                    "sortAscending":  ": activate to sort column ascending",
                                    "sortDescending": ": activate to sort column descending"
                                },
                                "order": [[ 3, "desc" ]]
                                
                            }
                             
                        });
                        // reviso el tamaño de la pantalla.
                        var wVentana=0, hVentana=0;    
                            if(wPantalla>320){
                               wVentana=650;
                               hVentana=650;
                            }else{
                                wVentana=300;
                                hVentana=400;
                            }
                        /* ventana modal*/
                        ventanita.modal({
                            minWidth:wVentana,
                           
                            minHeight:hVentana,
                            maxHeight:hVentana,
                            close:false,
                            onShow: function(){
                                $('#cierroConsumos').on("click",function(e){
                                    e.preventDefault();
                                    var contienes2 = $('#tablaConsumos');

                                    $.modal.close();
                                    contienes2.DataTable().destroy();
                                });
                            }
                            
                            
                            
                        });
                        
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
    
    /*
     * Editar domicilio del cliente seleccionado.
     */
    $("#editarClienteH").on("click",function(){
        var codigoCliente = $(this).attr('rel');
        console.log("hice click en el edicion:=> "+codigoCliente);
        location.href="mod-cliente-rapido.php?id="+codigoCliente;

    });
    
    /*
     * Editar domicilios del cliente
     */
                    
    $('#domicilioClienteH').on("click",function(){
       //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
       var codigoCliente = $(this).attr('rel');

       location.href="abm-cliente-domicilios.php?id="+codigoCliente;

   });

    
    $('#datosVendedor').hide();
    
    $('#iconoVendedor').on("click",function(){
        var icono= $('#iconoVendedor i');
        icono.toggleClass('iconoAzul');
         $('#datosVendedor').toggle();
    });
    $("#iconoCliente").on("click",function(){
        $(this).toggleClass("iconoAzul");
        if($(this).hasClass("iconoAzul")){
            //mostrar
            $(".izquierda").show();
        }else{
            //ocultar
            $(".izquierda").hide();
        }
    });
     $("input[type=radio]").on("click",function(){
            $("label").removeClass("iconoAzul");
            $("label[for="+ $(this).attr("id") +"]").addClass('iconoAzul');
        });
 
});
</script>

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


$.extend( $.fn.dataTable.defaults, {
    "language": {
        "emptyTable":     "No data available in table",
        "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
        "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        
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
    "ordering": true,
    "paging": true,
    "responsive":false
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
        if(filtro==""){
            return false;
        }
        
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
                    console.log(listaVuelta);
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
            var yourNumberF = Math.round(yourNumber*100)/100;
            var n= yourNumberF.toString().split(".");
            //Comma-fies the first part
            n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            //Combines the two sections
            return n.join(",");
            //return yourNumberF;
//            return yourNumber;
        }
              $("#spinner").hide();          
         var contienesVentas = $('#myTableVentas'),
            nombreVentas    = "#myTableVentas",
            contienesVentasRubro = $('#myTableVentasRubro'),
            nombreVentasRubro    = "#myTableVentasRubro",
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            fechaDesdeDos = $('#fechaDesdeDos').val(),
            fechaHastaDos = $('#fechaHastaDos').val(),
            rangoDoble = 0,
            opRango = $('#tipoOperacion').val(),
            datasetGrafico="",
            tipoResumen = $('#campoPeriodo').val(),
            tipo =  $('#verInforme').val(), 
            listarPor = $('#agrupoPor').val(),
            filtrarPor = $('#filtroSelec').val(),
            decimales = $('#decimales').val(),
            simbDer ="",
            simbIzq ="",
            
            vuelta  = 0,
            grafico = 0,
            puntoVenta = $('#pvSelec').val(),
            verGrafico = $('#aceptaGrafico');
			
				
			//console.log("idusuario:{"+idUsuario+"} empresa:{"+empresa+"} idempresa:{"+idEmpresa+"}");	

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
            
            if(decimales===""){
                alert("Debe seleccionar decimales");
                return false;
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
                 simbDer = "$";
                 simbIzq = "";
                 break;
             case "peso":
                 simbDer = "";
                 simbIzq = "";
                 break;
         }
         $("#spinner").hide();
         $.ajax({
                type: 'POST',
                url: 'relay-ventas-netas-gerencia.php',
                data:{
                    "ajax" : "true",
                                        
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "fechaDesdeDos" : fechaDesdeDos,
                    "fechaHastaDos" : fechaHastaDos,
                    "tipoResumen": tipoResumen,
                    "tipo" :  tipo, 
                    "listarPor" : listarPor,
                    "filtrarPor" : filtrarPor,                    
                    "puntoVenta" : puntoVenta,
                    "grafico"   : grafico,
                    "rangoDoble": rangoDoble,
                    "opRango"   : opRango,
                    "decimales": decimales,
                    "queInforme" : "vt",
                    "queSalida" : "html"
                    
                },
                success: function(response) {
                  
                   
                   if(response==="vacio"){
                        // sin resultado
                        var trCampos="<tr><td>No se encontraron resultados</td></tr>";
                        contienesVentasRubro.find("tbody").empty();
                        contienesVentasRubro.find("tbody").append(trCampos);
                    }else{
                        
                         //console.log(response);
                        var pepe = jQuery.parseJSON(response);
//                        console.log(pepe);

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
                        var itotal = 2;
                        $.each(pepe.cabeceras,function(pos,cabeza){

                            trCabecera = trCabecera +"<th class='dt-right'>"+cabeza+"</th>";
                            itotal++;

                        });
//                        console.log("itotal"+itotal);
//                        console.log("opRango"+opRango);
                        contienesVentasRubro.find("thead").append(trCabecera);
                        // renglones 
                        var cuantasCeldas =0;
                        var cuantosReng = 0;
                        var totalGeneral =0;
                        var totalValorA =0;
                        var totalValorB =0;
                        var cuantasColumnas = 0;
                        var arrColumnas= [];
                        // sumo el total de renglones distintos de cero por columna.
                        var arrCuantos = [];
                        // calculo el total general
                        // ver de que capaz no haga falta.
                         $.each(pepe.data,function(pos,renglones){
                              $.each(renglones, function(posi,celda){
                                  
                                  if(posi=="subt"){
                                    totalGeneral += parseFloat(celda); 
                                    }
                                    //cuantasCeldas++;
                            });                                                                                                   
                        });
//                        console.log("TotalGeneral:=> " + totalGeneral);
                        // pasar los subtotales como un footer para poder operar por pagina 
                        //  y mostrar los valores negativos y positov.s
                        // dibujo cada columna agrego porcentaje
                        $.each(pepe.data,function(pos,renglones){
                            var trCampos = "<tr>";
                            var celdaSubTotal =0;
                            var valorA = 0;
                            var valorB = 0;
                            
                            var valorPorcDif=0;
                            cuantosReng++;
                            trCampos = trCampos + "<td></td>";
                            cuantasColumnas =0;
                            $.each(renglones, function(posi,celda){
                                cuantasColumnas++;
                                 cuantasCeldas++;
//                                  console.log("Posi=>"+posi+" ArrCuantos[posi]=>"+arrCuantos[posi]+"-ArrColumnas[posi]=>"+arrColumnas[posi]+" -parsefloat celda=>"+celda);
                                if(posi!=0){
                                    // resta o diferencia
                                    // ahora el tema es que sea agrupado o resta hago un calculo.
                                    if(opRango!="suma"&&posi==1){
                                        valorA=parseFloat(celda);
                                    }
                                    if(opRango!="suma"&&posi==2){
                                        valorB=parseFloat(celda);
                                    }
//                                    console.log("valorA="+valorA);
//                                    console.log("valorB="+valorB);
//                                    if(posi=="subt"&&opRango!="suma"){
//                                        console.log("totalValor{"+(valorB+valorA)+"}");
//                                    }
                                     // sumatoria de las columnas.
                                    //console.log("Posi=>"+posi+" ArrCuantos[posi]=>"+arrCuantos[posi]+"-ArrColumnas[posi]=>"+arrColumnas[posi]+" -parsefloat celda=>"+parseFloat(celda));
                                    if(arrColumnas[posi]===undefined){
                                        if(parseFloat(celda)!==0){
                                            arrCuantos[posi] = 1;                                                                             
                                        }else{
                                            arrCuantos[posi] = 0;
                                        }
                                        arrColumnas[posi] = parseFloat(celda);
                                    }
                                    else{
                                        
                                        if(parseFloat(celda)!==0){
                                             arrCuantos[posi] = arrCuantos[posi] + 1;
                                        }
                                        arrColumnas[posi] = arrColumnas[posi] + parseFloat(celda);
                                        
                                    }
                                    
                                    trCampos =  trCampos + "<td data-order='"+celda+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(celda)+"</td>";
                                }else{
                                    // es texto
                                    trCampos =  trCampos + "<td data-order='"+celda+"' class='dt-left'><div class='corto'>"+celda+"</div></td>";
                                }
//                                console.log(posi);
                                if(posi=="subt"){
                                   
                                    //celdaSubTotal += valorB 
                                    totalValorA +=valorA;
                                    totalValorB +=valorB;
//                                    if(opRango=="resta"){
//                                        if(valorA==0){
//                                            valorPorcDif=100;
//                                        }else{
//                                            valorPorcDif=parseFloat(celda)*100/valorA;
//                                        }
//                                    }
                                    if(opRango!="suma"){
//                                        console.log("opRango=> "+opRango);
//                                        console.log("valor A=> "+valorA);
//                                        console.log("celda => "+valorB);
                                        // aca ver si es negativo.
                                        
//                                        if(valorA<0){
//                                            console.log("Es un valor negativo lo bajo?"+valorA);
//                                        }
                                        if(valorA<=0){
                                            valorPorcDif=1;
                                        }else{
                                            valorPorcDif=valorB/valorA;
                                        }
//                                        console.log("rango2/valora=> "+valorPorcDif);
                                        if(valorPorcDif<1){
//                                            console.log("Menos de uno "+valorPorcDif);
                                            
                                            valorPorcDif = ((1-valorPorcDif) *100 )*(-1);
//                                             console.log("porcentaje menos "+valorPorcDif);
                                        }else{
                                            if(valorPorcDif==1){
                                                valorPorcDif = 100;
                                            }else{
                                                valorPorcDif = ((1-valorPorcDif)*100)*-1;
                                            }
//                                            console.log("mayor a uno "+valorPorcDif);
//                                            
//                                            console.log("porcentaje mas "+valorPorcDif);
                                        }
                                        
                                        
                                    }else{
                                         celdaSubTotal += celda;
                                    }
                                    
                                }
                                cuantasCeldas++;

                            });
                            /* cambiar cuando es diferncia  el porcentaje que toma el % respecto del valor del primer campo, 
                            * quizas haya ya que traerlo*/
                            //console.log("opRango:=>"+opRango);
                            if(opRango!="suma"&&opRango!="sumag"){
                                trCampos = trCampos + "<td data-order='"+valorPorcDif+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(valorPorcDif)+"%</td>";
                            }else{
                                var totalGral= parseFloat(celdaSubTotal*100/totalGeneral);
                                trCampos = trCampos + "<td data-order='"+totalGral+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(totalGral)+"%</td>";
                            }                        
                            cuantasCeldas++;
                            trCampos = trCampos+"</tr>";
                            contienesVentasRubro.find("tbody").append(trCampos);                                                
                        });
                        
                        arrColumnas['porc'] = 100;
                        arrCuantos['porc'] ="";
    
                        var totalCampos=cuantasColumnas-2;
                        // linea final con subtotal

                        
                        var trTotal = "<tr><td colspan='2'>Total Ventas</td>\n";
                        // subtotales por columna
//                       console.log("totalValorA "+totalValorA);
//                       console.log("totalGeneral "+totalGeneral);
                        var valorSubtotalCol=0;
                        for (var po in arrColumnas) {
                            if(opRango!="suma"&&opRango!="sumag"){
                                if(po=="porc"){
                                    // evaluo la division
//                                    console.log("valor es igual o mayor que cero?"+totalValorA);
                                    if(totalValorA<=0){
                                        valorSubtotalCol=1;
                                    }else{
                                        valorSubtotalCol=totalValorB/totalValorA;
                                    }
                                    
                                    if(valorSubtotalCol<1){
                                         valorSubtotalCol = ((1-valorSubtotalCol) *100 );
                                    }else{
                                        if(valorSubtotalCol==1){
                                                valorSubtotalCol = 0;
                                            }else{
                                                valorSubtotalCol = (1-valorSubtotalCol )*100 *-1;
                                            }
                                    }
                                    
                                    trTotal += "<td data-order='"+valorSubtotalCol+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(valorSubtotalCol)+ "%</td>\n";
                                }else{
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap' >"+ReplaceNumberWithCommas(arrColumnas[po])+"</td>\n";
                                }
                            }else{
                                if(po=="porc"){
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(arrColumnas[po])+ "%</td>\n";
                                }else{
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(arrColumnas[po])+"</td>\n";
                                }
                            }
                        }
                        trTotal += "</tr>";
                        
//                        console.log(arrColumnas);
                        // notas de credito
                        /*
                        *  si no viene el array de notas de credito saco los dos renglones de nc y d total gral.
                         */
                        if(pepe.impNC !==undefined){ 
                            trTotal += "<tr><td colspan='2'>Total NC por importe <br> Total Descuentos </td>";
                            var totalNC = 0;
                            var textoTotGeneral = "";
                            var valorTotal =0;
                            var valorTotalA=0;
                            var valorTotalB=0;
                            var valorNcA =0;
                            var valorNcB =0;
//                            console.log(pepe.impNC);
                            $.each(pepe.impNC,function(pos,renglones){
//                                console.log("arrColumns["+pos+"]:=>";
//                                console.log("arrColums[pos]: "+arrColumnas[pos]);
//                                console.log("pos: "+pos);
////                                console.log("totalNC: "+totalNC);
//                                console.log(renglones);
//                                console.log("opRango: "+ opRango);
                                totalNC = totalNC  + parseFloat(renglones);
                                
                                valorTotal = parseFloat(arrColumnas[pos]) + parseFloat(renglones) ;
                                if(pos==1){
                                    valorTotalA=parseFloat(arrColumnas[pos])+ parseFloat(renglones);
                                    valorNcA = parseFloat(renglones);
                                }else{
                                    valorTotalB=parseFloat(arrColumnas[pos])+ parseFloat(renglones) ;
                                    valorNcB = parseFloat(renglones);
                                }
//                                console.log("valorNCA: "+ valorNcA);
//                                console.log("valorNCB: "+ valorNcB);
                                
//                                if(opRango!="resta"){
//                                    totalNC = totalNC  + parseFloat(renglones);
//                                }
                                
                                trTotal += "<td data-order='"+renglones+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(renglones)+"</td>";
                                textoTotGeneral += "<td data-order='"+valorTotal+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(valorTotal)+"</td>";
                            });
                            
                            if(opRango=="resta"){
//                                console.log("valorNCA:=>"+valorNcA+" ValorNCB=>"+valorNcB);
                                totalNC = valorNcA -valorNcB;
                                // si el resultado es negativo, debe ir positivo si el resultado es positivo va negativo.
//                                console.log("totalNC: "+ totalNC);
                                totalNC = (totalNC *-1);
                            }
                            
                            if(totalNC >0){
                                trTotal +="<td  data-order='"+totalNC+"' class='dt-right dt-nowrap'><strong>"+simbIzq+""+ReplaceNumberWithCommas(totalNC)+simbDer+"</td>";
                            }else{
                                trTotal +="<td data-order='"+totalNC+"' class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(totalNC)+"</td>";
                            }
                            trTotal += "<td></td></tr>";

    //                        total general


                            trTotal += "<tr><td colspan='2'>Total General </td>"+textoTotGeneral+"<td data-order='"+totalGeneral+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(totalGeneral+totalNC)+"</td>";
                            
                            if(opRango!="suma"){
//                                console.log(valorTotalB);
//                                console.log(valorTotalA);
                                var indice = valorTotalB/valorTotalA;
//                                console.log(indice);
                                var porciento =0;
                                if(indice<1){
                                    porciento = (1-indice)*100;
                                }else{
                                    if(indice==1){
                                        porciento=0;
                                    }else{
                                        porciento=((1-indice)*100)*-1;
                                    }
                                }
                                trTotal +="<td data-order='"+porciento+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(porciento)+"%</td></tr>";
                            }else{
                                trTotal +="<td></td></tr>";
                            }
                        
                        }
                        // totales de registro por columna
                        trTotal += "<tr><td colspan='2'>Total Registros</td>\n";
                        // subtotales por columna
//                        console.log("total ArrCuentos=>"+JSON.stringify(arrCuantos));
                        for (var po in arrCuantos) {
                            //console.log("po=>"+po+" cuantos=>"+arrCuantos[po]);
                            if(po=="porc"){
                                trTotal += "<td data-order='0' class='dt-right dt-nowrap'><strong>-</strong></td>\n";
                            }else{
                                trTotal += "<td data-order='"+arrCuantos[po]+"' class='dt-right dt-nowrap'><strong>"+ReplaceNumberWithCommas(arrCuantos[po])+ " </strong></td>\n";
                            }
                        }
                        trTotal += "</tr>";
                        contienesVentasRubro.find("tfoot").append(trTotal);
                        //contienesVentasRubro.DataTable();  
                         var tt = contienesVentasRubro.DataTable({
                            "ordering":true,
                            "responsive":false,
                            "info":true,                           
                            // column definitions
                            "columnDefs": [ {
                                "searchable": false,
                                "orderable": false,
                                "targets": 0
                            } ],
//                            "order":[[0,'asc'],[itotal,'desc']]
// nota: si tengo el itotal, lo ordeno si no que no orden nada y venga por la base
                            "order":[[itotal,'desc']],
                            "dom": 'lBfrtip',
                                        buttons: [
                                        {
                                            extend: 'excelHtml5',
                                            text: 'generar Excel',
                                            customize: function( xlsx ) {
                                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                                $('c', sheet).attr( 's', '64' );
                                                var celdas = $('c',sheet);
                                                $.each(celdas,function(pos,valor){
                                                    var n= $("v",valor);
                                                    
                                                    console.log(n.text());
                                                });
                                               // console.log(sheet);
                                            }
                                        }
                                    ]
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
            listaFiltro.append('<li id="'+indiceLi+'"> <i class="fa fa-check-square"></i> '+ filtro +' - '+ seleccion[1] +' <a class="borrarLi" rel="listaFiltro|'+indiceLi+'" href="#" title="Eliminar de la lista"><i class="fa fa-trash fa-lg"></i></a></li>');
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
            listaPv.append('<li  id="'+indiceLi+'"><i class="fa fa-check-square"></i> Punto venta: '+ seleccion[1] +' <a class="borrarLi" rel="listaPv|'+indiceLi+'" href="#" title="Eliminar de la lista"><i class="fa fa-trash fa-lg"></i></a></li>');
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
            <div id="header" class="noPrint">
    <div id="headerLogo">
        <img id="imgLogo" src="foto.php?origen=logo|0" alt="Arnaldo Chapini SRL" title="Arnaldo Chapini SRL" class="asBlock" base64="data:image/pjpeg;base64,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"/>
        
    </div>
    <div id="statusBar">
             
        <span class="vendedor">
          
            <i class="fa fa-users fa-fw fa-lg" title="Supervisor de Ventas" ></i> Alejandro Chapini            <strong><i class="fa fa-cog fa-fw fa-lg" id="iconoVendedor" title="Opciones del vendedor"></i></strong>
                
<!--            <strong><a href="alta_pedido.php" title="Mi Pedido" > <i class="fa fa-shopping-cart fa-lg"></i> (<span id="totalCarrito"></span>)</a></strong>-->
                <br>
              
                                    <strong style="font-size: 10px;"> <i class="fa fa-certificate fa-lg"></i> Los precios publicados NO incluyen IVA </strong>
                        </span>       
        <div class="clase-tooltip" id="datosVendedor">
                      <i class="fa fa-users fa-fw fa-lg"></i>Supervisor<br/>  
                      <i class="fa fa-truck fa-fw fa-lg"></i> Deposito RE<br/> 
           <i class="fa fa-check-square fa-fw fa-lg"></i> Venta sin stock: <strong>No</strong><br/>
           <i class="fa fa-check-square fa-fw fa-lg"></i> Lim Desc Pie: <strong>100%</strong><br/>
           <i class="fa fa-check-square fa-fw fa-lg"></i> Lim Desc Reng: <strong>100%</strong> 
        </div>
        
 
    </div>
        <ul id="nav">
	<li ><a href="escritorio.php">Escritorio</a></li>
	 <li><a href="listado-clientes.php">Cliente</a></li>
    <li><a>Listados</a>
            <ul>
<!--                <li><a href="listado-clientes.php">Busqueda Cliente</a></li>-->
                
                <li><a href="lista_precio.php">Lista de Precios</a></li>
                <li><a href="lista-promociones.php">Lista de Promociones</a></li>
                <li><a href="lista-mis-consumos.php">Mis consumos</a></li>   
                <li><a href="lista-comprobantes-ncancelados.php">Comprobantes No Cancelados</a></li>
                <li><a href="lista-cuenta-corriente.php">Cuenta Corriente</a></li>
                <li><a href="lista-presupuestos-vendedor.php">Lista de Presupuestos</a></li>
                <li><a href="lista-pedidos-vendedor.php">Lista de Pedidos</a></li>
                <li><a href="gestion-devoluciones.php">Lista de Devoluciones</a></li>
                <li><a href="lista-remitos.php">Lista de Remitos</a></li>
                <li><a href="lista-articulo-remito.php">Lista de Artículos Remitados</a></li>
                <li><a href="lista_facturas_electronicas.php">Lista de Facturas</a></li>

            </ul>          
	</li>
     <li ><a href="lista-precio-catalogo.php" title="Catálogo de productos">Catálogo</i></a></li>
          <li>
              <a >Comprobantes</a>
              <ul>
                  <li><a href="listado-clientes.php?frm=3">Presupuesto</a></li>
                  <li><a href="listado-clientes.php?frm=0">Pedido</a></li>
                  <li><a href="listado-clientes.php?frm=5">Devolución</a></li>
<!--                  <li><a href="listado-clientes.php?frm=1">Remito Sistema</a> </li>-->
                              </ul>
          </li>
          
        <li>
            <a>Estadísticas</a>
            <ul>
                <li ><a href="informe-ventas-total.php" title="Ventas netas por rubro , proveedor">Ventas Netas</a></li>
            
                              
                    <li><a href="informes-ventas-gerenciales.php">Ventas Gerenciales</a></li>                 
                     <li><a href="informe-utilidad-gerencial.php">Utilidades Gerenciales</a></li>
                     <li><a href="informe-utilidad-inflacion-gerencial.php">Utilidades Gerenciales con inflación</a></li>
                                        
                            </ul>
        </li>
<!--        <li><a href="lista-remitos.php">Remitos</a>
        <li><a href="lista-cuenta-corriente.php">Cta Cte</a></li>
        
        <li><a href="lista-comprobantes-ncancelados.php">Fact.a Pagar</a></li>-->
        <li ><a href="salida.inc.php?cliente=si">Salir</a></li>
    </ul>
    <div id="modal-ncancelados-cliente">
            <div class="tituloVentana">Comprobantes no cancelados <button id="cierroNcanc" class="botonNuevo black grande">X</button></div>
            <table id="tablaCancelados" name="tablaCancelados"  cellspacing="1" style="width:98%">
            </table>
        </div>
        <div id="modal-consumos-cliente">
             <div class="tituloVentana">TOP 20 Productos consumidos <button id="cierroConsumos" class="botonNuevo black grande">X</button></div>
            <table id="tablaConsumos" name="tablaConsumos" cellspacing="1" style="width:98%">
            </table>
        </div>
</div>
        
        <div id="content">
            <div class="buscador">
                <form id="formBusca" name="formBusca" method="POST" action="">
                    <div class="control">
                        <label for="verInforme" class="parametros">Ver:  
                            <select name="verInforme" id="verInforme" required="required">
                                <option value=""> - seleccionar -</option>
                                <option value="un">Unidades (Un)</option>
                                <option value="peso">Peso (Kg)</option>
                                <option value="monto">Monto ($)</option>
                                                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="decimales" class="parametros">Decimales:  
                            <select name="decimales" id="decimales" required="required">
                                <option value=""> - seleccionar -</option>
                                <option value="0">No</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                
                                
                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="agrupoPor" class="parametros">Listar:  
                            <select name="agrupoPor" id="agrupoPor" required="required">
                                <option value=""> - seleccionar -</option>
                                <option value="cliente">Cliente</option>
                                <option value="tipocliente">Tipo Cliente</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="articulo">Articulo</option>
                                <option value="proveedor">Proveedor</option>
                                <option value="zona">Zona</option>
                                <option value="categoria">Categorias</option>
                                <option value="rubro">Rubro</option>
                                <option value="subrubro">Sub Rubro</option>
                                
                            </select>
                        </label>
                    </div>
                     <div class="control">
                            <label for="campoPeriodo" class="parametros">Periodo:  
                                <select name="campoPeriodo" id="campoPeriodo" required="required">
                                    <option value="dia">Diario</option>
                                    <option value="semana">Semanal</option>
                                    <option value="mes" selected="selected">Mensual</option>

                                </select>
                            </label>
                        </div>
                    <div class='buscadorDentro'>
                        <div class="titulo">
                            Fechas
                        </div>
                        <div class="tituloFecha">
                            
                            <div id="buscaFecha"  class="control" >
                                <label>Primario </label><br>    
                                <label for="fechaDesde" class="parametros">Desde: <input type="date" name="fechaDesde" id="fechaDesde" required="required" ></label>
                                <br>
                                <label for="fechaHasta" class="parametros">Hasta: <input type="date" name="fechaHasta" id="fechaHasta" required="required"></label>
                            </div>
                        </div>
                        
                        <div class="tituloFecha">
                            <div id="buscaFecha"  class="control">
                                <label>Secundario</label><br>
                                <label for="fechaDesdeDos" class="parametros">Desde: <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"  ></label>
                                <br>
                                <label for="fechaHastaDos" class="parametros">Hasta: <input type="date" name="fechaHastaDos" id="fechaHastaDos" ></label>
                            </div>
                            
                        </div>
                        <div class="tituloFecha">
                            
                            <div id="tipoComparacion" class="control">
                                <label class="parametros">Operación de Rango:
                                <select id="tipoOperacion" name="tipoOperacion">
                                    <option value="suma">Suma</option>
                                    <option value="sumag">Suma agrupada</option>
                                    <option value="resta">Diferencia</option>
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
                            <label for="puntoVenta" class="parametros">P venta: 
                                <select name="puntoVenta" id="puntoVenta" >
                                    <option value="|Todos"> - todos - </option>
                                <option value="1|1"> 1 </option>
<option value="4|2"> 2 </option>
<option value="5|10"> 10 </option>
<option value="6|11"> 11 </option>
<option value="7|3"> 3 </option>
<option value="8|4"> 4 </option>
<option value="9|5"> 5 </option>
<option value="10|6"> 6 </option>
<option value="11|7"> 7 </option>
                                </select>
                            </label>
                            <button name="addPv" id="addPv" type="button" class="botonNuevo chico azul"><i class="fa fa-check"></i> </button>
                        </div>
                        <div class="control">
                            <label for="listaPv" class="parametros">Selección:  
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

                            <label for="filtrarPor" class="parametros">Tipo:  
                                <select name="filtrarPor" id="filtrarPor">
                                    <option value=""> - seleccionar -</option>
                                    <option value="cliente">Cliente</option>
                                    <option value="tipocliente">Tipo Cliente</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="articulo">Articulo</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="zona">Zona</option>
                                    <option value="categoria">Categorias</option>
                                    <option value="rubro">Rubro</option>
                                    <option value="subrubro">Sub Rubro</option>

                                </select>
                            </label>
                        </div>
                   
                        <div class="control">
                                <label for="seleccionFiltro" class="parametros">Valor a filtrar: </label>
                                <input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
                            <button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fa fa-check"></i> </button>
                        </div>
                        <div class="separador"></div>
                        <div class="control">
                            <label for="listaFiltro" class="parametros">Selección: 
                                <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
                                <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">

                            </label>
                        </div>
                   </div>
                    <div class="separador10px"></div>
                     <div class="control">
                         
                         <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                         <label for="aceptaGrafico">  Ver gráficos <i class="fa fa-bar-chart fa-1x" ></i> </label>
                    </div>
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
                <h2 class="alignLeft">Ventas netas</h2>
               <div id="myTableVentasRubro_wrapper" class="dataTables_wrapper"><div class="dataTables_length" id="myTableVentasRubro_length"><label>Ver <select name="myTableVentasRubro_length" aria-controls="myTableVentasRubro" class=""><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select> entradas</label></div><div class="dt-buttons"><button class="dt-button buttons-excel buttons-html5" tabindex="0" aria-controls="myTableVentasRubro"><span>generar Excel</span></button> </div><div id="myTableVentasRubro_filter" class="dataTables_filter"><label>Buscar:<input type="search" class="" placeholder="" aria-controls="myTableVentasRubro"></label></div><table class="display dataTable" cellspacing="1" id="myTableVentasRubro" style="width: 99%;" role="grid" aria-describedby="myTableVentasRubro_info">
                    <thead><tr role="row"><th colspan="2" rowspan="1">VENTAS NETAS x mes  /   - Cliente: SEGUNDO ARACENA S.R.L. (cod:13597),ESTABLECIMIENTO FRUTICOLA CARLETI S.A. (cod:28328)</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="1">2018</th><th colspan="1" rowspan="2" class="sorting_desc" tabindex="0" aria-controls="myTableVentasRubro" aria-label="SubTotal: activate to sort column ascending" aria-sort="descending" style="width: 59px;">SubTotal</th><th colspan="1" rowspan="2" class="sorting" tabindex="0" aria-controls="myTableVentasRubro" aria-label="SubTotal(%): activate to sort column ascending" style="width: 76px;">SubTotal(%)</th></tr><tr role="row"><th class="sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 12px;"></th><th class="sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="Cliente: activate to sort column ascending" style="width: 417px;">Cliente</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="ene: activate to sort column ascending" style="width: 43px;">ene</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="feb: activate to sort column ascending" style="width: 43px;">feb</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="mar: activate to sort column ascending" style="width: 49px;">mar</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="abr: activate to sort column ascending" style="width: 37px;">abr</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="may: activate to sort column ascending" style="width: 25px;">may</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="jun: activate to sort column ascending" style="width: 25px;">jun</th><th class="dt-right sorting" tabindex="0" aria-controls="myTableVentasRubro" rowspan="1" colspan="1" aria-label="jul: activate to sort column ascending" style="width: 43px;">jul</th></tr></thead>
                    <tbody><tr role="row" class="odd"><td>1</td><td data-order="ESTABLECIMIENTO FRUTICOLA CARLETI S.A. (Cod: 28328)" class="dt-left"><div class="corto">ESTABLECIMIENTO FRUTICOLA CARLETI S.A. (Cod: 28328)</div></td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="133573.9" class="dt-right dt-nowrap">133.573,9</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="133573.9" class="dt-right dt-nowrap sorting_1">133.573,9</td><td data-order="55.927997893078775" class="dt-right dt-nowrap">55,93%</td></tr><tr role="row" class="even"><td>2</td><td data-order="SEGUNDO ARACENA S.R.L. (Cod: 13597)" class="dt-left"><div class="corto">SEGUNDO ARACENA S.R.L. (Cod: 13597)</div></td><td data-order="38032.8" class="dt-right dt-nowrap">38.032,8</td><td data-order="29054.399999999998" class="dt-right dt-nowrap">29.054,4</td><td data-order="10869.6" class="dt-right dt-nowrap">10.869,6</td><td data-order="7896.9" class="dt-right dt-nowrap">7.896,9</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="0" class="dt-right dt-nowrap">0</td><td data-order="19404.3" class="dt-right dt-nowrap">19.404,3</td><td data-order="105258" class="dt-right dt-nowrap sorting_1">105.258</td><td data-order="44.07200210692123" class="dt-right dt-nowrap">44,07%</td></tr></tbody>
                    <tfoot><tr><td colspan="2" rowspan="1">Total Ventas</td><td data-order="38032.8" class="dt-right dt-nowrap" rowspan="1" colspan="1">38.032,8</td><td data-order="29054.399999999998" class="dt-right dt-nowrap" rowspan="1" colspan="1">29.054,4</td><td data-order="144443.5" class="dt-right dt-nowrap" rowspan="1" colspan="1">144.443,5</td><td data-order="7896.9" class="dt-right dt-nowrap" rowspan="1" colspan="1">7.896,9</td><td data-order="0" class="dt-right dt-nowrap" rowspan="1" colspan="1">0</td><td data-order="0" class="dt-right dt-nowrap" rowspan="1" colspan="1">0</td><td data-order="19404.3" class="dt-right dt-nowrap" rowspan="1" colspan="1">19.404,3</td><td data-order="238831.9" class="dt-right dt-nowrap" rowspan="1" colspan="1">238.831,9</td><td data-order="100" class="dt-right dt-nowrap" rowspan="1" colspan="1">100%</td></tr><tr><td colspan="2" rowspan="1">Total Registros</td><td data-order="1" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>1 </strong></td><td data-order="1" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>1 </strong></td><td data-order="2" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>2 </strong></td><td data-order="1" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>1 </strong></td><td data-order="0" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>0 </strong></td><td data-order="0" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>0 </strong></td><td data-order="1" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>1 </strong></td><td data-order="2" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>2 </strong></td><td data-order="0" class="dt-right dt-nowrap" rowspan="1" colspan="1"><strong>-</strong></td></tr></tfoot>
                </table><div class="dataTables_info" id="myTableVentasRubro_info" role="status" aria-live="polite">Viendo 1 de 2 de 2 resultados</div><div class="dataTables_paginate paging_simple_numbers" id="myTableVentasRubro_paginate"><a class="paginate_button previous disabled" aria-controls="myTableVentasRubro" data-dt-idx="0" tabindex="0" id="myTableVentasRubro_previous">Anterior</a><span><a class="paginate_button current" aria-controls="myTableVentasRubro" data-dt-idx="1" tabindex="0">1</a></span><a class="paginate_button next disabled" aria-controls="myTableVentasRubro" data-dt-idx="2" tabindex="0" id="myTableVentasRubro_next">Siguiente</a></div></div>
                <h3 class="alignLeft">Gráfico</h3>
                 <div id="graficoVentasRubro"></div>
                <div id="graficoVentasRubroT"></div>
                
                
                
<!--                <h3 class="alignLeft">Ventas netas</h3>
                <canvas id="graficoVentasRubroProv" width="600" height="400"></canvas>-->
                
                
                
            </div>
        </div>
 
        <div id="footer" class="noPrint">
            <div class="nombreEmpresa">
                Arnaldo Chapini SRL
            </div>
            <div>
            <span class="datoEmpresa">
                <i class="fa fa-home fa-lg"></i>Remedios de Escalada 2760</span>
            <span class="datoEmpresa">
                <i class="fa fa-phone fa-lg"></i>0261-4320022
            </span>
            <span class="datoEmpresa">
                <i class="fa fa-info fa-lg"></i>30-63929595-4</span>
                     
            </div>
            <div>
            <a href="https://www.administranet.com.ar" title="administraNET gestión software de facturación gratis" target="_blank">
            <img src="_img/logo-administranet-ecommerce.png" alt="desarrollado por administraNET gestión" title="administraNET gestión" />
            </a>
</div>
           </div>    
    
    </div>
     <div id="basic-modal-content"> </div>
    </body>
</html>