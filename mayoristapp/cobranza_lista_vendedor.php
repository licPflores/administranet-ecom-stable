<?php require_once 'sesion.inc.php';?>
<?php 
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom =0;
$usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
$Conf['Titulo']="Cobranza por vendedor";
$Conf["modulo"]="Cobranzas";
?>
<!DOCTYPE HTML>
<html lang="es-AR">

<head>
    <title><?php echo $Conf['Titulo'];?> | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <?php require_once 'cabecera.php';?>
    <?php 
      
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();
        
    ?>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>
    
    <style>
       /* .numeros:before {
    content: '$ ';
                        }*/
        .numeros {
            text-align: right;
        }
    </style>

   
    <script>


 $.fn.dataTable.ext.errMode = 'none';
$.extend( $.fn.dataTable.defaults, {
    "language": {
        "decimal":        ",",
        "thousands":      ".",
        "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json",
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
            "previous":   "Anterior",
            decimal: ","
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
   	var hoy = new Date();
	var y = hoy.getFullYear().toString();
	var m = (hoy.getMonth() + 1).toString();;
	var d = hoy.getDate().toString();
	(d.length == 1) && (d = '0' + d);
	(m.length == 1) && (m = '0' + m);

	$('#fechaDesde').val(y+"-01-01");

	$('#fechaHasta').val(y+"-"+m+"-"+d);   
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
        
            $('#botonBuscar').click(function(){
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
            
            if(fechaDesde==""){
                alert("Debe seleccionar una fecha desde primaria");
              return false;
              //$('#fechaDesde').slideDown();
            }
            if(fechaHasta==""){
                alert("Debe seleccionar una fecha hasta secundaria");
              return false;
              //$('#fechaHasta').slideDown();
            }
            
            
        var jqxhr =  $.ajax({
                            type: "GET", 
                            url: "informes-json/cobranza_lista_vendedor.php", 
                            async: false,
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
                    
                }
                    }).responseText; 
  
   //  console.log(jqxhr);
var dataObject= eval(jqxhr);
//var dataObject=  jQuery.parseJSON(jqxhr);
 /*************************/
   if ( $.fn.dataTable.isDataTable( '#myTableVentasRubro')) {
	   
   $('#myTableVentasRubro').DataTable().destroy();
       
   }

   var tabla = $('#myTableVentasRubro');
    //tabla.DataTable(dataObject[0]);
    tabla.DataTable({
		columns: dataObject[0].columns,
		data: dataObject[0].datosFormateado
		});
	

var totales = dataObject[0].totalesFormateado;

//tabla.find("th").empty();
$('#myTableVentasRubro tfoot').empty();
if(tabla.append('<tfoot><tr>')){
$.each(totales, function( index, value ) {
    // tabla.append('<th class="numeros">'+value+'</th>');	
    //funciona tabla.append($('<td />', {text : value}));
    $('#myTableVentasRubro tfoot tr').append($('<th />', {text : value}));;
});

tabla.append('</tr></tfoot>');
}
$('#myTableVentasRubro tfoot tr th').addClass("dt-right");

/*----------------------------*/
if ( $.fn.dataTable.isDataTable( '#myTableVentasRubro1')) {
            $('#myTableVentasRubro1').DataTable().destroy();
                }

    tabla = $('#myTableVentasRubro1');
    //tabla.DataTable(dataObject[0]);
    tabla.DataTable({
		columns: dataObject[0].columns,
		data: dataObject[0].datos,
    dom: 'Bfrtip',
        buttons: [
            'copy','print','excel'
        ]

		});
	

 totales = dataObject[0].totales;
tabla.find("tfoot").empty();
tabla.append('<tfoot><tr>');
$.each(totales, function( index, value ) {
     tabla.append('<th class="dt-rigth">'+value+'</th>');	
});

tabla.append('</tr ></tfoot>');

            /*-------------------------------*/ 
//inicia botonera
                
 var buttons = new $.fn.dataTable.Buttons(tabla, {
     buttons: [
       'copyHtml5',
       'excelHtml5',
       'csvHtml5',
       'pdfHtml5'
    ]
}).container().appendTo($('#botonera'));
$('#escondeme').hide();
// fin botonera
var rotulo = [];
$.each(dataObject[0].columns,function(index,value){

	if(index>0)rotulo[index-1]=value.data;
	
});

//ejemplo https://jsfiddle.net/api/post/library/pure/

    var coleccion = dataObject[0].datos;
    var rotulos = dataObject[0].columns;
    
    rrr = Array();
    
    $.each(rotulos,function(indice,valor){
           
        if(valor.title!='Id')
        rrr.push(valor.title);
           });
   
    
    var ttt = Array();
    var uuu = Array();
    var x = 0;
    $.each(coleccion,function(indice,valor){
     
        var linea = (valor);
     // delelte linea.shift();
        delete linea.id;
        
       ttt[x]= Object.values(linea)
        x++;
       });
   x=0;             


                
            });
   

  

     // boton para buscar coincidencias
     $('#botonBuscar8').click(function(){
         
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
        
        //*** redondeo
        
        function precise_round(num, dec){
 
            if ((typeof num !== 'number') || (typeof dec !== 'number')) 
          return false; 

            var num_sign = num >= 0 ? 1 : -1;
//            console.log("numero{"+num+"} - decimal{"+dec+ "}");
            return (Math.round((num*Math.pow(10,dec))+(num_sign*0.0001))/Math.pow(10,dec)).toFixed(dec);
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
                 simbDer = "";
                 simbIzq = "$";
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
                        //console.log(pepe);
                        // decimales en porcentaje
                        if(decimales==="No"){
                            var redondeo=0;
                        }else{
                            var redondeo=parseInt(decimales);
                        }
                        
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
                                    trTitulo = trTitulo +"<th  colspan='"+titulo.span+"' rowspan='2'>"+titulo.titulo+"(%)</th>";
                                }else{
                                    trTitulo = trTitulo +"<th  colspan='"+titulo.span+"'>"+titulo.titulo+"</th>";
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
                                    
                                    trCampos =  trCampos + "<td data-order='"+celda+"' class='dt-right dt-nowrap'>"+simbIzq+ReplaceNumberWithCommas(celda)+"</td>";
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
                                valorPorcDif = precise_round(valorPorcDif,redondeo);
                                trCampos = trCampos + "<td data-order='"+valorPorcDif+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(valorPorcDif)+"%</td>";
                            }else{
                                
                                var totalGral= parseFloat(celdaSubTotal*100/totalGeneral);
                                totalGral =precise_round(totalGral,redondeo);
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
                                    valorSubtotalCol = precise_round(valorSubtotalCol,redondeo);
                                    trTotal += "<td data-order='"+valorSubtotalCol+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(valorSubtotalCol)+ "%</td>\n";
                                }else{
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap' >"+simbIzq+ReplaceNumberWithCommas(arrColumnas[po])+"</td>\n";
                                }
                            }else{
                                if(po=="porc"){
                                    var porc=precise_round(arrColumnas[po],redondeo);
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap'>"+ReplaceNumberWithCommas(porc)+ "%</td>\n";
                                }else{
                                    trTotal += "<td data-order='"+arrColumnas[po]+"' class='dt-right dt-nowrap'>"+simbIzq+ReplaceNumberWithCommas(arrColumnas[po])+"</td>\n";
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
                                
                                trTotal += "<td data-order='"+renglones+"' class='dt-right dt-nowrap'>"+simbIzq+ReplaceNumberWithCommas(renglones)+"</td>";
                                textoTotGeneral += "<td data-order='"+valorTotal+"' class='dt-right dt-nowrap'>"+simbIzq+ReplaceNumberWithCommas(valorTotal)+"</td>";
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


                            trTotal += "<tr><td colspan='2'>Total General </td>"+textoTotGeneral+"<td data-order='"+totalGeneral+"' class='dt-right dt-nowrap'>"+simbIzq+ReplaceNumberWithCommas(totalGeneral+totalNC)+"</td>";
                            
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
                                porciento=precise_round(porciento,redondeo);
//                                console.log("porciento::"+porciento);
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
                                            footer:true,
                                            header:true,
                                            customize: function( xlsx ) {
                                                
                                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                                $('c', sheet).attr( 's', '64' );
                                                //var celdas = $('c>v',sheet);
                                                 var lastCol = sheet.getElementsByTagName('v');
                                                 var colCeldas = sheet.getElementsByTagName('c');
                                                 //console.log(colCeldas);
                                                 $("#spinner").show(); 
                                                 $.each(colCeldas,function(k,cc){
                                                      var texto= $(cc).text();
                                                       
                                                       //busco el peso 
//                                                       if(texto.indexOf("$")==-1){
//                                                        // no hay peso buscar el porciento
//                                                            if(texto.indexOf("%")!==-1){
//                                                                //encontre el porciento
//                                                                //console.log("porcentaje::"+texto);
//                                                                texto = texto.replace("%","");
//                                                                texto = texto.replace(",",".");
//                                                            }
//                                                       }else{
                                                           // encontre el peso
//                                                           console.log("peso::"+texto);
                                                           texto = texto.replace("$","");
                                                           texto = texto.replace("%","");
                                                           texto = texto.replace(/\./gi, "");
                                                           texto = texto.replace(",",".");
//                                                            console.log("peso Cv::"+texto);
                                                           
//                                                       }
                                                       //console.log("texto final::"+texto);
                                                       //console.log($(cc).html());
                                                       if(!isNaN(parseFloat(texto))){
//                                                           console.log("soy numero::"+texto);
                                                           $(colCeldas[k]).html("<v>"+texto+"</v>");
                                                           if($(colCeldas[k]).attr("t")!==undefined){
                                                                $(colCeldas[k]).removeAttr("t");
                                                           }
                                                       }
                                                      
                                                       //console.log(cc.context.outerHTML());
                                                 });

                                               // console.log(sheet);
                                               $("#spinner").hide(); 
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
   $("#expandir").on("click",function(){
        $("#contraer").show();
        var dFiltros=$("div.buscador");
        dFiltros.animate({ height: "toggle" }, 700 );
        $(this).toggleClass("fa-expand").toggleClass("fa-minus-square");
        
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
<?php include 'partes-html/verinforme.php';?>
                    </div>
                   
 <?php include 'partes-html/agruparpor.php';?>
                    <div class="control">
                        <label for="campoPeriodo" class="parametros">Período:
                            <select name="campoPeriodo" id="campoPeriodo" required="required">
                                <option value="dia">Diario</option>
                                <option value="semana">Semanal</option>
                                <option value="mes" selected="selected">Mensual</option>

                            </select>
                        </label>
                    </div>
                     <div class="control">
 <?php include 'partes-html/decimales.php';?>
                    </div>
                    <div class='buscadorDentro'>
                        <div class="titulo">
                            Fechas
                        </div>
                         <div class="tituloFecha">
                            
                            <div id="buscaFecha"  class="control" >
                                <label>Primario </label><br>    
                                <label for="fechaDesde" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaDesde" id="fechaDesde" required="required" value="<?php echo date('Y-m-d', strtotime('-1 years')); ?>"> </label>al 
                                
                                <label for="fechaHasta" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaHasta" id="fechaHasta" required="required" value="<?php echo date('Y-m-d'); ?>" ></label>
                            </div>
                        </div>
                        
                        <div class="tituloFecha">
                            <div id="buscaFecha"  class="control">
                                <label>Secundario</label><br>
                                <label for="fechaDesdeDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"  ></label> al
                                
                                <label for="fechaHastaDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaHastaDos" id="fechaHastaDos" ></label>
                            </div>
                            
                        </div>
                        <div class="tituloFecha">

<?php include "partes-html/trango.php";?>
                        </div>
                    </div>
                    <div class='buscadorDentro'>
<?php include "partes-html/pv.php";?>
<?php include "partes-html/filtro.php";?>

                    </div>
  <?php include "partes-html/filtrarpor.php";?>
                    <div class="separador10px"></div>
                    <div class="control">

                        <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                        <label for="aceptaGrafico"> Ver gráficos <i class="fa fa-bar-chart fa-1x"></i> </label>
                    </div>

                    <div id="grafico"></div>


                    <div class="control">

                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo chico azul">
                            <i class="fa fa-search fa-1x"></i>
                        </button>
                    </div>

                </form>
            </div>

            <div id="spinner" class="spinner" style="display:none;">
                <img src="_img/logo-administranet-ecommerce.png">
                <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla">
               <h1>Estadísticas de <?php echo $Conf["modulo"];?><i id="expandir" class="fa fa-expand fa-lg fa-fw" title="expandir"></i></h1>
                 <h2 class="alignLeft"><?php echo $Conf['Titulo'];?></h2>
                <table class="display" cellspacing="1" id="myTableVentasRubro" style="width:99%">
                </table>
               <div id="escondeme">
                <table class="display" cellspacing="1" id="myTableVentasRubro1" style="width:99%">

                </table></div>
                <div id="botonera"></div>

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
