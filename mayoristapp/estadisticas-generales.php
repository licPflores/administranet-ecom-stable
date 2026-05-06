<?php
/*
 * ESTADISTICAS TODOS LOS INFORMES PASAN POR ACA
 * ============================================================================
 */
require_once 'sesion.inc.php';
 
/**
 * variables de configuracion para colocar los encabezados
 */

$uTablas    = 0;
$uModal     = 0;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom =0;
$usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
//echo "<PRE>";
//print_r($Conf);
//echo "</PRE>";
?>
<!DOCTYPE HTML>
<html lang="es-AR">
<head>
    
    <title>Estadisticas Generales | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    
     <?php require_once 'cabecera.php';?>
   
<script>
    

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
   // $('#fechaDesde').val('dd/mm/aaaa');
    //$('#fechaHasta').val('dd/mm/aaaa');
    
    $('#filtrarPor').change(function(){
       var filtro = $(this).val(),
           listado = $('#seleccionFiltro'); 
        //console.log(filtro);
        if(filtro==""){
            return false;
        }
        
        $.ajax({
                type: 'POST',
                url: 'relay-filtros-estadisticas.php',
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
         console.log("ingresando...");
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
         var relayInforme=$('#agrupoPor').val(),
            titulo =$('#agrupoPor>option:selected').text(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            fechaDesdeDos = $('#fechaDesdeDos').val(),
            fechaHastaDos = $('#fechaHastaDos').val(),
            rangoDoble = 0,
            opRango = $('#tipoOperacion').val(),
            datasetGrafico="",
            tipoResumen = $('#campoPeriodo').val(),
            tipo =  $('#verInforme').val(), 
            motor = $('#agrupoPor>option:selected').attr("motor"),
            filtrarPor = $('#filtroSelec').val(),
            decimales = $('#decimales').val(),
            puntoVenta = $('#pvSelec').val(),
            caja =$("#idCaja").val(),
            tipoCajaTitulo=$('#Supra>option:selected').text(),
            nombreCajaTitulo=$('#idCaja>option:selected').text(),
            cuentaBanco=$("#cuentaBanco").val(),
            simbDer ="",
            simbIzq ="",
            vuelta  = 0,
            grafico = 0;
            
         window.status = "Informe: "+titulo;
            
			
            
            
            if(tipo==""){
                alert("Debe seleccionar tipo de información ");
                
                return false;
                $('#verInforme').focus();
            }
            if(relayInforme==""){
                alert("Debe seleccionar un informe");
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
                url: relayInforme,
                data:{
                    "ajax" : "true",
                                        
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "fechaDesdeDos" : fechaDesdeDos,
                    "fechaHastaDos" : fechaHastaDos,
                    "tipoResumen": tipoResumen,
                    "tipo" :  tipo, 
                    "filtrarPor" : filtrarPor,                    
                    "puntoVenta" : puntoVenta,
                    "grafico"   : grafico,
                    "rangoDoble": rangoDoble,
                    "opRango"   : opRango,
                    "decimales": decimales,
                    "idCaja":   caja,
                    "tipoCajaTitulo":tipoCajaTitulo,
                    "nombreCajaTitulo":nombreCajaTitulo,
                    "cuentabanco": cuentaBanco,
                    "queInforme" : "vt",
                    "queSalida" : "html",
					"titulo": titulo,
					"TipoCaja": $('#uuuu').val(),
					"estadodecheque": $('#estadodecheque').val(),
					"tfecha": $('#tfecha').val()
                    
                },
                success: function(response) {
                  // codigo del leopoldo
                  // 0 - datatables2
                  // 1 - databales
                  //TABLA HTML
				     $( ".buscador" ).toggle( "fast", function() {
    // Animation complete.
  });
				 
                    var tablita = $('#ifrTabla');
					console.log("Motor "+motor);
                    if(tablita.attr('src')==null){
                        if(motor==0){
                            tablita.attr('src', 'moto/datatables2.php');

                        }
                        if(motor==1){
                            tablita.attr('src', 'moto/datatables.php');
							$('#ifrGrafico').hide();
							$('#ifrGrafico1').hide();
							$('#ifrGrafico2').hide();
                        }
					 
                    }else{

                        tablita.attr('src', tablita.attr('src'));
						if(motor==0)tablita.attr('src', 'moto/datatables2.php');
                        if(motor==1){
                            tablita.attr('src', 'moto/datatables.php');
							$('#ifrGrafico').hide();
							$('#ifrGrafico1').hide();
							$('#ifrGrafico2').hide();
                        }
                    }
                    // tablita.css( "height",750);
			tablita.animate({
            height: '730px'
        }, 1500);
	if(motor==0){
                     // GRAFICO DE BARRAS
                     console.log("ingresando grafico uno------------");
                     var grafico = $('#ifrGrafico');
                     if(grafico.attr('src')==null){
                        grafico.attr('src', 'moto/gchart.php');
                      }else{
                           // alert(grafico.attr('src'));
                            grafico.attr('src', grafico.attr('src'));
                    }
                     grafico.css( "height", 450);
                    
                    //GRAFICO D TORTA
                    console.log("ingresando grafico Dos------------");
                     var grafico1 = $('#ifrGrafico1');
                    if (grafico1.attr('src') == null){
						//moto/gchart2.php es torta de google
						//moto/chartist.php es de Chartists
                        grafico1.attr('src', 'moto/chartist2.php');
                    } else {
                       // alert(grafico1.attr('src'));
                        grafico1.attr('src', grafico1.attr('src'));
                    }
                    grafico1.css("height", 450);
			console.log("ingresando grafico tres------------");		
					grafico1 = $('#ifrGrafico2');
					if (grafico1.attr('src') == null){
						grafico1.attr('src', 'moto/gchart3.php');
						} else {
                       
								grafico1.attr('src', grafico1.attr('src'));
								}
								grafico1.css("height", 500);
					
					
					
                                                //$('iframe').attr('src', $('iframe').attr('src'));
                    console.log('------------------Linea terminada');
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
    
//    $("#agrupoPor").on("change",function(){
//       
//        var titulo= $("#tituloInforme"),
//        opcion = $("#agrupoPor option:selected").text();
//        console.log("en el agrupo "+$("#agrupoPor option:selected").text());
//        titulo.text("Ventas por "+opcion);
//        
//    });
$('#parametrosInformes').on('click', function() {
                // console.log('hago click en la busqueda avanzada---------');
                var divAvanzado = $(".panelesBloqueInforme");
                if ($(this).hasClass('fa-angle-down') === true) {
                    // console.log('tenglo clase angle down----');
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
                    divAvanzado.show();
                } else {
                    // console.log('tenglo clase angle UP----');
                    $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
                    divAvanzado.hide();
                }

            });

            $("#expandir").on("click", function() {//fa fa-minus-square fa-lg fa-fw
                $("#contraer").show();
                var dFiltros = $(".filtroInformes");
                dFiltros.animate({
                    height: "toggle"
                }, 700);
                $(this).toggleClass("fa-minus-square").toggleClass("fa-expand");

            });
    

    
    
    
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>        
        
            <div class="paneles filtroInformes">
                <h1><?php echo ucwords($Conf["modulo"]);?><span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">                    
                <?php 
                // insertare segun configuracion las opciones.
                $queOpciones = $Conf["parte"];

                foreach($queOpciones AS $op => $val){

                    require_once $val["include"];
                }
                
                ?>    
                    
            <input type="hidden" name="uuuu" id="uuuu" value=9999 />
            <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Generar
                        </button>
                    </span>
                </div>

                </form>
            </div>


        <div class="paneles" id="contiene-tabla" style="min-width:fit-content">
              <h1>Estadísticas<i id="expandir" class="fa fa-minus-square fa-lg fa-fw" title="expandir"></i> </h1>
       
                <h2 class="alignLeft">Utilidades</h2>
                <table class="display" id="myTableVentasRubro" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>                
                </table>
                
                <h3 class="alignLeft">Gráfico</h3>
                 <div id="graficoVentasRubro"></div>
                <div id="graficoVentasRubroT"></div>

        </div>   
 
        <?php require_once 'footer.php';?>   
    
    
    <div id="basic-modal-content"> </div>
     <!--spinner admNET-->
<div id="spinner" class="spinnerAdm" style="display:none;">
    <div class="centro">
        <img src="_img/logo-administranet-ecommerce.png">   
        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
    </div>
</div>    
<!--fin spinner-->
    </body>

</html>