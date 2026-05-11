<?php 
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
//$caminoDispo = $_SESSION['caminoDisp'];
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
require_once 'ajax-articulos.php';
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas        = 1;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 1;
$iconoDisabled  = 1;
$usaZoom        = 0;

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Mis consumos | administraNET </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    <?php 

//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();

    ?>

<script>
    
                            
 $(document).ready(function(){
     //$('#divBuscaRubro').hide();
     //activar y desactivar el boton TIPO DE BUSQUEDA 
//        $("input[name='tipoBusqueda']").change(function(){
//            var valor = $("input[name='tipoBusqueda']:checked").val();
//    //                    alert(valor);
//            switch(valor){
//                case '0':
//                    $('#divBuscaArticulo').show();
//                    $('#divBuscaRubro').hide();
//                    break;
//                case '2':
//                    $('#divBuscaArticulo').show();
//                    $('#divBuscaRubro').show();
//                    break;
//            }
//
//
//        });

     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
     
     /*
      * Fechas
      */
//     $( "#fechaDesde" ).datepicker({ dateFormat: "dd/mm/yy" });
//     $( "#fechaHasta" ).datepicker({ dateFormat: "dd/mm/yy" });  
//     // para que se borren lo que tienen adentro las fechas   
//     $('#fechaDesde').focus(function(){
//         $('#fechaDesde').val('');
//     });
//     $('#fechaHasta').focus(function(){
//         $('#fechaHasta').val('');             
//     });
      $('#fechaDesde').val('dd/mm/aaaa'),
      $('#fechaHasta').val('dd/mm/aaaa'),
     /*
      * Lista de Precios
      */
     $('#botonBuscar').click(function(){
        $('#spinner').show()

        var contienes       = $('#myTable'),
            fechaDesde      = $('#fechaDesde').val(),
            fechaHasta      = $('#fechaHasta').val(),
            categoria       =$('#buscaCategoria').val(),            
            rubro           = $('#buscaRubro').val(),
            subrubro        = $('#buscaSubRubro').val(),
            marca           =$('#buscaMarca').val(), 
            queCliente      = "cliente",
            tipoCliente     = "consumo";
            //alert('vamos');
            $.ajax({
                type: 'POST',
                url: 'relay-art.php',
                data:{
                    "ajax" : "true",
                    'buscarProducto' : 1,
                    "tipoCliente"   : tipoCliente,
                    "categoria"     : categoria,
                    "rubro"         : rubro,
                    "subrubro"      : subrubro,
                    "marca"         : marca,       
                    "fechaDesde"    : fechaDesde,
                    "fechaHasta"    : fechaHasta,
                    "queCliente"    : queCliente
                },
                success: function(response) {
                    $('#spinner').hide()
                        // Refresh the cart display after a successful Ajax request
//                        console.log(response);
                        if(response=='0'){
                            //alert('traje vacio');
                            contienes.empty();
                            contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
                            $("#spinner").hide();
                        }
                        else{
                            //alert(response);
                            contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching:false,
                            responsive:true,
                             
                            "order": [[ 4, "desc" ]],
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
                            },
                            "dom": 'lBfrtip',
                            buttons: [
                                'excel', 
                                {
                                    extend: 'pdf',
                                    orientation: 'landscape'
                                }
                            ]
                        });

                        //$('#jcart-buttons').remove();
                        }// el cierre del else del        
                },
                // See: http://www.maheshchari.com/jquery-ajax-error-handling/
                error: function(x, e) {
                    $('#spinner').hide()
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



  
      /*
  * 
  * Busca Categoria
   */
   
   $('#buscaCategoria').change(function(){
        var categoria = $(this).val();
        //alert($(this).val());
        $('#buscaRubro').empty();
        if(categoria!==""){
            //rubro
            $.ajax({
                type: 'get' ,
                url: 'relay-rubro.php',
                data: {
                    "ajax" : true,
                    "idcategoria" : categoria
                },
                dataType: 'json',
                success: function (j) {                   
                    //alert(j);
                    var options = [], i = 0, o = null;
                    //alert(j.length);
                    for(i = 0; i < j.length; i++) {
                        // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                        o = document.createElement("OPTION");
//                            alert(j[i]['id']);
                        o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                        o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
//                            
                        $('#buscaRubro').append(o);
                    }
                },

                error: function (xhr, desc, er) {
                    // add whatever debug you want here.
                    alert("an error occurred"+xhr+desc+er);
                }
            });
            // marca
            //==================================================================
            /// Marca 
        //======================================================================
        var categoria=$('#buscaCategoria').val(),
            rubro=$('#buscaRubro').val(),
            subrubro=$('#buscaSubRubro').val(),
            marca=$(this).val();
            
                   
        
        $('#buscaMarca').empty();
        $.ajax({
            type: 'get' ,
            url: 'relay-marca.php',
            data: {
                "ajax" : true,
                "idrubro"       : rubro,
                "idsubrubro"    : subrubro,
                "idcategoria"   : categoria
                
            },
            dataType: 'json',
            success: function (j) {                   
                console.log(j);
                var options = [], i = 0, o = null;
                //alert(j.length);
                // creo el subrubro todos
                o = document.createElement("OPTION");
//                            alert(j[i]['id']);
                o.value = '';
                o.text =  '-todos-';
                $('#buscaMarca').append(o);
                // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                if(j!==null){
                    for(i = 0; i < j.length; i++) {
                        // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                        o = document.createElement("OPTION");
    //                            alert(j[i]['id']);
                        o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                        o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
    //                            
                        $('#buscaMarca').append(o);
                    }
                }
            },

            error: function (xhr, desc, er) {
                // add whatever debug you want here.
                alert("an error occurred"+xhr+desc+er);
            }
        });
            
            
        }else{
            var o = document.createElement("OPTION");
//                            alert(j[i]['id']);
            o.value = "";
            o.text ="- todos -";
//                            
            $('#buscaRubro').append(o);
            $('#buscaRubro').change();
        }

    });
   
    /*
    * Busco rubros
     */
    $('#buscaRubro').change(function(){
        var rubro = $(this).val(),
        tipoCliente = $('#tipoCliente').val();
        
        console.log("rubro{"+rubro+"}");
        $('#buscaSubRubro').empty();
        $.ajax({
            type: 'get' ,
            url: 'relay-rubro.php',
            data: {
                "ajax" : true,
                "idrubro" : rubro,
                "tipoCliente": tipoCliente
            },
            dataType: 'json',
            success: function (j) {                   
                console.log(j);
                var options = [], i = 0, o = null;
                //alert(j.length);
                // creo el subrubro todos
                o = document.createElement("OPTION");
//                            alert(j[i]['id']);
                o.value = '';
                o.text =  '-todos-';
                $('#buscaSubRubro').append(o);
                // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                if(j!==null){
                    for(i = 0; i < j.length; i++) {
                        // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                        o = document.createElement("OPTION");
    //                            alert(j[i]['id']);
                        o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                        o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
    //                            
                        $('#buscaSubRubro').append(o);
                    }
                }
                
            },
             
            error: function (xhr, desc, er) {
                // add whatever debug you want here.
                alert("an error occurred"+xhr+desc+er);
            }
        });
        /// Marca 
        //======================================================================
        var categoria=$('#buscaCategoria').val(),
            rubro=$('#buscaRubro').val(),
            subrubro=$('#buscaSubRubro').val(),
            marca=$(this).val();
            
                   
        
        $('#buscaMarca').empty();
        $.ajax({
            type: 'get' ,
            url: 'relay-marca.php',
            data: {
                "ajax" : true,
                "idrubro"       : rubro,
                "idsubrubro"    : subrubro,
                "idcategoria"   : categoria
                
            },
            dataType: 'json',
            success: function (j) {                   
                console.log(j);
                var options = [], i = 0, o = null;
                //alert(j.length);
                // creo el subrubro todos
                o = document.createElement("OPTION");
//                            alert(j[i]['id']);
                o.value = '';
                o.text =  '-todos-';
                $('#buscaMarca').append(o);
                // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                if(j!==null){
                    for(i = 0; i < j.length; i++) {
                        // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                        o = document.createElement("OPTION");
    //                            alert(j[i]['id']);
                        o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                        o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
    //                            
                        $('#buscaMarca').append(o);
                    }
                }
            },

            error: function (xhr, desc, er) {
                // add whatever debug you want here.
                alert("an error occurred"+xhr+desc+er);
            }
        });
           
        
    });
//    $('#verFiltros').on("click",function(){
//         $(this).toggleClass('iconoAzul');
//         $('#formBusca').toggle();
//     });
//    $('#formBusca').hide();   


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






 });
 
</script>
</head>
	<body>
		<div id="wrapper">
			<?php 
				require_once $barra;
			?>
			
			<div id="content" class="paneles filtroInformes">
								
				<h1>Mis Consumos <span ><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>

				<form id="formBusca" name="formBusca" method="POST" action="">
					
									
						<div class='panelesBloqueInforme' id="divBuscaRubro">
							<div class="titulo" >
								<label>Fecha: </label>
							</div>
						
							<div id="buscaFecha" class="control-fechas grid-column-2">
								<div class="control">
									<label class="parametros" for="fechaDesde">Desde: </label>
									<input type="date" name="fechaDesde" id="fechaDesde">
								</div>
								<div class="control">
									<label class="parametros" for="fechaHasta">Hasta: </label>
									<input type="date" name="fechaHasta" id="fechaHasta">
								</div>
							</div>

							<div class="titulo" >
								<label>Filtros: </label>
							</div>   
														
							<div class="control" >
								<label class="parametros" for="buscaRubro">Categoría: </label>
								<select id="buscaCategoria" name="buscaCategoria">
									<option value="">- todas -</option>
								<?php $articulos->muestra_categorias();?>  
								</select>                                
							</div>

							<div class="control" >
								<label class="parametros" for="buscaRubro">Rubro:</label> 
								<select id="buscaRubro" name="buscaRubro">
									<option value=""> rubro</option>                               
								</select>
							</div>

							<div class="control">
								<label class="parametros" for="buscaSubRubro">Sub Rubro:</label> 
								<select id="buscaSubRubro" name="buscaSubRubro">
									<option value=""></option>    
								</select>
							</div>

							<div class="control">
								<label class="parametros" for="buscaMarca">Marca:</label>
								<select id="buscaMarca" name="buscaMarca">
									<option value="">- todas -</option> 
									<?php $articulos->muestra_marcas();?>
								</select>
							</div>
						
												
							<?php if(isset($_SESSION['cliente'])):?>

								<div class="control-botones">
									<button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
										<i class="fas fa-search fa-lg fa-fw"></i> Buscar
									</button>
								</div>

							<?php endif;?>

						</div>

				</form>
								
				<div id="basic-modal-content" > </div>

				<!--spinner admNET-->
				<div id="spinner" class="spinnerAdm" style="display:none;">
					<div class="centro">
						<img src="_img/logo-administranet-ecommerce.png">
						<div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
					</div>
				</div>

				<div id="contiene-tabla"> 
					<h1>Listado de consumos</h1>
					<table class="display" cellspacing="1" id="myTable" data-page-length='25'>
						<?php if(!isset($_SESSION['cliente'])):?>
						<tr><td class="cartelAdvertencia"><i class="fa fa-warning fa-lg"></i> Debe seleccionar un Cliente.</td></tr>
						<?php endif;?>
					</table>
				</div>

			</div>
	
			<?php require_once 'footer.php';?>   
		
		</div>
		<div id="basic-modal-content" > </div>
    </body>
</html>