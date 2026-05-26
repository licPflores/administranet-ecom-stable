<?php 
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$usaZoom    = 0;
if (isset($_SESSION['cliente'])) {
    if (is_object($_SESSION['cliente'])) {
        $clienteObj = $_SESSION['cliente'];
    } else {
        $clienteObj = $_SESSION['cliente'][0];
    }
}
// echo 'camino dispo',var_dump($caminoDispo);
$objVendedor =  $_SESSION['vendedor'];
$codPuesto = $objVendedor->id_puesto; 
// echo 'camino dispo',var_dump($caminoDispo);
$whereViajante="";
if($codPuesto!=1){
    $whereViajante= " AND viajantes.CodViajante=".$objVendedor->CodViajante.PHP_EOL;
}
// buscando vendedores rapido.
$sql = "SELECT viajantes.CodViajante AS valor,"
                . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                . " FROM viajantes"
                . " WHERE viajantes.Anulado='No'" 
                . $whereViajante               
                . " ORDER BY texto ASC";

$hacerVendedor = mysqli_query($connV,$sql);
$arrVendedores = array();
if($hacerVendedor){
    while($viajante = mysqli_fetch_assoc($hacerVendedor)){
        $arrVendedores[] = $viajante;
    }
}     
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Recibos | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <style>

.spinnerAdm {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.centro {
    text-align: center;
}
    .table-responsive {
        overflow-x: auto;
        max-width: 100%;
    }

    .table-responsive table {
        width: 100%;
    }
    </style>
     <?php require_once 'cabecera.php';?>
    
    
<script>
    var soyMobil=false;
    <?php if($caminoDispo!=''):?>
        // soy Mobil;
        soyMobil=true;
    <?php endif;?>

// const numerito= new Intl.NumberFormat('es-AR',{
//             style: 'decimal',                    
//             minimumFractionDigits: 2
//         }); 
        
// const dinero = new Intl.NumberFormat('es-AR', {
//             style: 'currency',
//             currency: 'ARS',
            
//             minimumFractionDigits: 2

//           });
        
  $(document).ready(function() { 

    //$('#buscaFecha').hide();
         
      // codigo para exportar con formato al pdf y excel mas bonito, despues aplicar a la tabla.
      
      var buttonCommon = {
        exportOptions: {
            columns: [ 0, 1,2,3,4,5,6,7,8,9,10,11,12,13,14,17 ],
            format: {
                body: function ( data, row, column, node ) {
                    // Strip $ from salary column to make it numeric
//                    return column === 5 ?
//                        data.replace( /[$,]/g, '' ) :
//                        data;
//                    console.log({data});
//                    console.log({row}); 
//                    console.log({column});
                    if(!isNaN(data)){
                        // console.log("data formateado: "+dinero.format(data));
                        
                        data=dinero.format(data);
                        //console.log("data formateado: "+data);
                        //data=numerito.format(data);
                        //console.log("data formateado: "+data);
                        
                        //b=a.replace(/ /g, "");
//                        data = '$ '+data;
//                        console.log("data sin el espacio del pesos"+data);
                        
                    }
                    return data;
                },
                footer: function ( data, row, column, node ) {
                    
                    if(!isNaN(data)){
                       // console.log("data formateado: "+dinero.format(data));
                        
                        const arrColumnasNumero=[5,6,7,8,9,10,11,12,13];
                        // console.log('soy la columna debo ingresar',arrColumnasNumero.includes(column),'columna:',column);
                        if(arrColumnasNumero.includes(column)) {
                            data=dinero.format(data);
                            
                        }
                        
                    }
                    return data;
                }
            }
        }
    };
    var buttonCommonExcel = {
        exportOptions: {
            columns: [ 0, 1,2,3,4,5,6,7,8,9,10,11,12,13,14,17 ],
            format: {
                body: function ( data, row, column, node ) {
                    // Strip $ from salary column to make it numeric
//                    return column === 5 ?
//                        data.replace( /[$,]/g, '' ) :
//                        data;
                //    console.log({data});
//                    console.log({row}); 
//                    console.log({column});
                  //console.log('soy un texto :',!isNaN(data));
                    // if(!isNaN(data)){
                        const arrColumnasNumero=[5,6,7,8,9,10,11,12,13];
                    // console.log('soy la columna debo ingresar',arrColumnasNumero.includes(column),'columna:',column);
                    if(arrColumnasNumero.includes(column)) {
                    // if(isNaN(data)){
                          //console.log("dentro del body " +{data});
                        // console.log({data});
                        //console.log("data formateado: "+dinero.format(data));
                        // data =parseFloat(data.replace(/[^\d.-]/g, ''));
                        //data=data.replace( /[$,]/g, '' );
                        //data=dinero.format(data);
                        var numero = data.replace(/[$.]/g, '');
                        // Reemplazar el punto como separador de decimales
                        // 123.526,36
                        // console.log("data formateado sin peso: "+numero);
                        // numero = numero.replace('.', '');
                        // console.log("data sin punto sin peso: "+numero);
                        numero = numero.replace(',', '.');
                        // console.log("data coma x punto: "+numero);
                        data = numero;
                        // console.log("data formateado: "+data);
                        //data=numerito.format(data);
                        //console.log("data formateado: "+data);
                        
                        //b=a.replace(/ /g, "");
//                        data = '$ '+data;
//                        console.log("data sin el espacio del pesos"+data);
                        
                    }
                    return data;
                },
                footer: function ( data, row, column, node ) {
                    // console.log('datos del pie',data,row,column,node);
                    const arrColumnasNumero=[5,6,7,8,9,10,11,12,13];
                    // console.log('soy la columna debo ingresar',arrColumnasNumero.includes(column),'columna:',data);
                    // console.log('dato al pie',data);
                    if(arrColumnasNumero.includes(row)) {
//                        console.log("pie");
//                        console.log({data});
                       // console.log("data formateado: "+dinero.format(data));
                       data=data.replace( /[$.]/g, '' );
                       data = data.replace(',', '.');
//                        data=dinero.format(data);
                        // console.log('dato al pie',data);
                        
                    }
                    return data;
                }
            }
        }
    };     
         
     // aca atacch a los eventos del spinner funcionando.
     
        
     // para que se borren lo que tienen adentro las fechas   
//     $( "#fechaDesde" ).datepicker({ dateFormat: "dd/mm/yy" });
//     $( "#fechaHasta" ).datepicker({ dateFormat: "dd/mm/yy" });
//     
//     $('#fechaDesde').focus(function(){
//         $('#fechaDesde').val('');
//     });
//     $('#fechaHasta').focus(function(){
//         $('#fechaHasta').val('');             
//     });
     $('#campoBusca').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
//         $('#fechaDesde').val('dd/mm/aaaa'),
//         $('#fechaHasta').val('dd/mm/aaaa'),
         $('#numeroComp').val('');
         switch(valor){
             case 'Fecha':
                   $('#buscaNumero').hide();
                   $('#suggestions').hide();
                   $('#buscaFecha').show(400);
                   
                   
                 break;
             case '':
                   $('#buscaFecha').hide();
                   $('#buscaNumero').show(400);
                   
//                 $('#buscaNumero').css({'display':'block'});
//                 $('#buscaFecha').css({'display':'none'});
                 
                 break;
         }
         
     });
     
     
     // funcion para cambiar la fecha.
     function fechaReverso(date){
        console.log(date);
        var a = date.split('-');
        var d = new Number(a[2]);
        var m = new Number(a[1]);
        var y = new Number(a[0]);
        var dd = new Date(y, m-1, d);
        var y = dd.getFullYear();
        var m = dd.getMonth() + 1;
        var d = dd.getDate();
        return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y;
        //return dd;
     }
     
     
     
     
     // boton para buscar coincidencias
     $('#botonBuscar').click(function(){
       
        var contienes = $('#myTable'), 
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            filtraCliente=$('#filtraCliente').val();
        var filtraVendedor = $('#filtraVendedor').val();
        var elBotonBuscar = $('#botonBuscar');
        var textoBuscar = '<i class="fas fa-check fa-lg fa-fw"></i> Buscar';
        var textoEspere = '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere...';
            
//            numeroComp = $('#numeroComp').val(),
           // tipoRemito = $('#tipoRemito').val(),
            //estadoPedido =  $('#estadoRecibo').val();
            //$('formBusca').submit();
            if(campoBusca==="Fecha"&&(fechaDesde===''||fechaHasta==='')){
                // console.log("fechaDesde::: "+fechaDesde );
                // console.log("fechaDesde::: "+fechaHasta );
                alert("Debe completar la fecha");
                $('#fechaDesde').focus();
                return false;
            }
            elBotonBuscar.attr('disabled', true);                       
            elBotonBuscar.html(textoEspere);

        $.ajax({
                type: 'GET',
                url: 'relay-recibos.php',
                data:{
                    "consulta" : 1,

                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "filtraCliente":filtraCliente,
                    "filtraVendedor":filtraVendedor,
                    
                    
                },
                beforeSend: function(){
                           
                 },
                success: function(response) {
                   
//                        // Refresh the cart display after a successful Ajax request
                    // var fechaHoy 
                    // console.log('valor soy Mobil',soyMobil);
                    let soyResponsive=false;
                    let soyPageLenght=9;
                    if(soyMobil==true){
                        soyResponsive=true,
                        soyPageLenght=6;
                    }
                    elBotonBuscar.attr('disabled', false);                       
                    elBotonBuscar.html(textoBuscar);
                    var fecha = new Date();
                    var fechaFormateada = fecha.getFullYear() +
                                        ("0" + (fecha.getMonth() + 1)).slice(-2) +
                                        ("0" + fecha.getDate()).slice(-2) +
                                        ("0" + fecha.getHours()).slice(-2) +
                                        ("0" + fecha.getMinutes()).slice(-2) +
                                        ("0" + fecha.getSeconds()).slice(-2);
//                    console.log(response);
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching: false,
                           // responsive:soyResponsive,
                           "info":false,     
                           "paging": true,                           
                           "pageLength": 5,
                            "lengthMenu": [ [5, 18, 21, -1], [5, 18, 21, "All"] ],
                           "columnDefs": [ {
                                            "targets": [5,6,7,8,9,10,11,12 ],
                                            "render":  $.fn.dataTable.render.number( '.', ',', 2, '$')  
                //                             "render": function (data, type, row) {
                // if (type === 'display' || type === 'filter') {
                //     // Formatear el número para mostrarlo correctamente en la tabla
                //     return '$' + parseFloat(data).toLocaleString('es-ES');
                // }
                // // Devolver el número en su formato original para exportar a Excel
                // return parseFloat(data.replace(/[^\d.-]/g, ''));
            // }
                                          } 
                                        ],
                        
                            "footerCallback": function(tfoot, data, start, end, display) {

                                var $th = $(tfoot).find('th');

                                $.each($th,function(posi,td){
                                    
                                    $(td).text( $.fn.dataTable.render.number('.', ',', 2,'$').display( $(td).text() )); 
                                });
                              
                             },          

                            "language": {
                                
                            
                                "emptyTable":     "No data available in table",
                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
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
                                
                            }, "order": [[ 0, "desc" ]],                           
                                dom: 'Blfrtip',
                                        buttons: [
//                                            
//                                           { extend: 'excelHtml5',
//                                               columns: [ 0, 1,2,3,4,5,6,7,8 ],
//                                               footer: true,
//                                                //messageTop: "Listado de recibos <?php echo date('d/m/Y h:i');?>",
//                                                messageTop: "Listado de recibos Periodo: "+fechaReverso(fechaDesde)+" al "+fechaReverso(fechaHasta)+ " \n - Emitido el: <?php //echo date('d/m/Y h:i');?> - Generado por: <?php echo $objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario;?>",
//                                                
//                                                customize: function( xlsx, row ) {
//                                                    console.log({row});
//                                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
//                                                    console.log({sheet});
////                                                     $('row c[r^="D"], row c[r^="E"]', sheet).attr( 's', 64);
////                                                     $('row c[r^="F"], row c[r^="G"]', sheet).attr( 's', 64);
////                                                     $('row c[r^="H"], row c[r^="I"]', sheet).attr( 's', 64);
//                                                      $('row[r!=1] c', sheet).attr('s', '64');
//                                             }
//                                            },
                                            $.extend( true, {}, buttonCommonExcel, {
                                                extend: 'excelHtml5',
                                                title: 'lista_recibos_web_'+fechaFormateada,
                                                footer: true, 
                                            //     customize: function( xlsx, row,data ) {
                                            //         // console.log({row});
                                            //         console.log({data});
                                            //         var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                            //         // console.log({sheet});

                                            //          $('row[r!=1] c', sheet).attr('s', '64');
                                            //  },
                //                             customize: function(xlsx) {
                //     var sheet = xlsx.xl.worksheets['sheet1.xml'];
                //     $('row c[r^="A"]', sheet).each( function () {
                //         if ($(this).text().indexOf("$") === 0) {
                //             $(this).attr('t', 'n'); // Cambiar el tipo de celda a número
                //             $(this).text($(this).text().replace("$", "")); // Eliminar el símbolo de peso
                //         }
                //     });
                // },
                                                pageSize: 'LEGAL',
                                                orientation: 'landscape',
                                                messageTop: "Listado de recibos \n Periodo: "+fechaReverso(fechaDesde)+" al "+fechaReverso(fechaHasta)+ " \n Emitido el: <?php echo date('d/m/Y h:i');?> \n Generado por: <?php echo $objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario;?>"
                                            } ),
                                            
                                            
                                            
                                            $.extend( true, {}, buttonCommon, {
                                                extend: 'pdfHtml5',
                                                footer: true, 
                                                title: 'lista_recibos_web_'+fechaFormateada,
                                                pageSize: 'A3',
                                                orientation: 'landscape',
                                                messageTop: "Listado de recibos \n Periodo: "+fechaReverso(fechaDesde)+" al "+fechaReverso(fechaHasta)+ " \n Emitido el: <?php echo date('d/m/Y h:i');?> \n Generado por: <?php echo $objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario;?>"
                                            } )
                                            
                                        ]
                        });

                },
                error: function(x, e) {
                    
                    elBotonBuscar.attr('disabled', false);                       
                    elBotonBuscar.html(textoBuscar);
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
                },
                complete: function(){
                    elBotonBuscar.attr('disabled', false);                       
                    elBotonBuscar.html(textoBuscar);
                        }
            });
      });
      
     //ventana modal para mostrar el contenido sin tener que usar una ventana abierta y se vea el codigo
      $(document).on('click', '.verComprobante', function(event) {
      
            var codigoMovimiento = $(this).attr('mov'),
                tipoComprobante = $(this).attr('comprobante');
            
        $.ajax({
                type:   'GET',
                url:    'ver_remito.php',
                data:{
                        "ajax":"true",
                        "codigomovimiento": codigoMovimiento,
                        "comprobante": tipoComprobante
                },
                success: function(response){
                    $('#basic-modal-content').empty();
            //                                                si hubiera una foto uno o foto dos
            //                                                lo correcto seria crear el objeto 
            //                                                y luego modificar el src a traves
            //                                                de ajax....
                    $('#basic-modal-content').html(response);
            //                                                $('#basic-modal-content').modal();
                    $('#basic-modal-content').modal({
                        //minHeight : 200,
                        maxWidth : 950,
                        minHeight: 700
                    });
                    $('#imprimir').click(function(){
                        $(this).hide();
                        window.print();
                    });    
                    return false;
                },
                error: function(x, e) {
                        var s = x.status, 
                                m = 'Ajax error: ' ; 
                        if (s === 0) {
                                m += 'Check your network connection.';
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
      //$('#formBusca').hide();
      //var fecha = new Date();
    //document.getElementById("FechaActual").value = fecha.toJSON().slice(0,10); 
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        
           
        <div class="paneles filtroInformes"> 
                 <!-- <label><i class="fas fa-sliders-h fa-lg iconoAzul" id="verFiltros"></i> Parametros</label>  -->
                 <h1>Filtros <span ><i id="parametrosInformes"  class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">
                <div class='panelesBloqueInforme'>
                <div class="control">
                        <label for="filtraVendedor">Vendedor / Viajante:</label>  
						<select name="filtraVendedor" id="filtraVendedor">
							
							<?php if($codPuesto==1):?>
								<option value="todos" >- Todos -</option>
								<?php 
								if(!empty($arrVendedores)){
									foreach($arrVendedores as $vendedor){
									echo '<option value="'.$vendedor['valor'].'">'.$vendedor['texto'].'</option>'.PHP_EOL;

									}
								}
								?>    
							<?php endif;?>    
							<?php if($codPuesto!=1):?>
							<?php    
									if(!empty($arrVendedores)){
										foreach($arrVendedores as $vendedor){
											echo '<option selected="selected"  value="'.$vendedor['valor'].'">'.$vendedor['texto'].'</option>'.PHP_EOL;

										}
									}
								?>
							<?php endif;?>    
							
						</select>
                    </div>

                    <div class="control">
                        <label for="filtraCliente">Cliente:</label>  
						<select name="filtraCliente" id="filtraCliente">
							<option value="todos" >- Todos -</option>
							<?php if(isset($clienteObj)):?>
							<option selected="selected"  value="<?php echo $clienteObj->Codigo;?>"><?php echo $clienteObj->cliente.'(Cod:'.$clienteObj->Codigo.')';?></option>
							<?php endif;?>
						</select>
                    </div>
                  
                    <div class="control">
                        <label for="campoBusca">Buscar por:</label>
						<select name="campoBusca" id="campoBusca">
							<option value="" >-</option>
							<option value="Fecha" selected="selected">Fecha</option>
						</select>
                    </div>
                  
                    <div id="buscaFecha" class="control">
                    	<label> Desde:</label> 
						<input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo  date("Y-m-d", strtotime("-15 day"));?>">
                        <label> Hasta:</label> 
                        <input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo date("Y-m-d");?>">       
                    </div>

                    <div class="control control-con-boton">
						<label></label>
						<button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
							<i class="fas fa-check fa-lg fa-fw"></i> Generar
						</button>
                    </div>
                    
               </form>
            </div>
                        </div>
<!--             
           <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->


           
            <div class="paneles table-responsive"   id="contiene-tabla" >
            <!-- <div id="contiene-tabla"  style="float:left;" > -->
           
                <h1>Recibos</h1>
                
                <table class="display" cellspacing="1" id="myTable"></table>
           
                 
        
            </div>
        
        
 
        <?php require_once 'footer.php';?>   

         
        <div id="basic-modal-content" > </div>
                <!--spinner admNET-->
                <div id="spinner" class="spinnerAdm" style="display:none;">
                    <div class="centro">
                        <img src="_img/logo-administranet-ecommerce.png">
                        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                    </div>
                </div>
                <!--fin spinner-->
    
    </div>
     <script >
           <?php if(isset($_REQUEST['cartel'])&&($_REQUEST['cartel']=='5' || $_REQUEST['cartel']=='6' )):?>
           <?php if($_REQUEST["cartel"]==6):?>    
            Swal.fire("Hecho!","Se generó rec:<?php echo $_REQUEST["rec"];?> \n e-mail enviado correctamente","success");
           <?php endif;?>
           <?php if($_REQUEST["cartel"]==5):?>    
            Swal.fire("Hecho!","Se generó rec: <?php echo $_REQUEST["rec"]?> \n No se envio e-mail","warning");
           <?php endif;?>
           <?php endif;?>     
                 
     </script>
    </body>
</html>