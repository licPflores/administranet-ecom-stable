<?php require_once 'sesion.inc.php'; ?>
<?php
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;

$usaZoom = 0;
$usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
?>

<!DOCTYPE HTML>
<html lang="es-AR">

<head>

    <title>Compras | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <?php require 'cabecera.php'; ?>
     
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="_scripts/acciones-informes.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

    <script>

        
$(document).ready(function(){

    
    $('#filtrarPor').change(function() {
                
                var filtro = $(this).val(),
                    botonAgregar  = $('addFiltro');
                    listado = $('#seleccionFiltro');

                if(filtro!==''){
                   
                

                $.ajax({
                    type: 'POST',
                    url: 'relay-ventas-netas-gerencia.php',
                    data: {
                        "ajax": "true",
                        "tabla": filtro,
                        "queInforme": "seleccion"


                    },
                    success: function(response) {
                                             console.log(response);
                        var listaVuelta = jQuery.parseJSON(response);
                        listado.val("");
                                           

                        $("#seleccionFiltro").autocomplete({
                            source: listaVuelta,
                            
                            select: function(event, ui) {
                                event.preventDefault();
                                                
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

            }
            });



            $('#addFiltro').on('click', function() {
                    var listaFiltro = $('#listaFiltro'),
                        textFiltro = $('#filtroSelec'),
                        filtro = $('#filtrarPor').val(),
                        seleccion = $('#seleccionFiltro').attr("alt").split("|");
                    var tFiltro = textFiltro.val();
                    //agregar item a la lista
                    var indiceLi = listaFiltro.children().length + 1;

                    // sin item para agregar
                    if(seleccion=="" || seleccion[1] == undefined){
                        Toast.fire({
                            icon: "warning",
                            text: "Debe seleccionar un Item de la lista",
                            });

                        return false;
                    }

                    // al menos un item de filtro seleccionado
                    if (seleccion !== "" && seleccion[1] !== undefined) {
                        listaFiltro.append('<li id="' + indiceLi +'" data-valor="' + seleccion + '"><i class="fas fa-check-square fa-lg fa-fw"></i>' + filtro + '- <strong>' + seleccion[1] + '</strong> <a class="borrarLi" rel="listaFiltro|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fas fa-trash fa-lg fa-fw"></i></a></li>');
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
                    }

                    $('#seleccionFiltro').attr("alt", "");
                    $('#seleccionFiltro').val("");
                    // agregar al input una lista
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

            $("#expandir").on("click", function() {//fa fa-minus-square fa-lg fa-fw
                $("#contraer").show();
                var dFiltros = $(".filtroInformes");
                dFiltros.animate({
                    height: "toggle"
                }, 700);
                $(this).toggleClass("fa-minus-square").toggleClass("fa-expand");

            });




           






            $('#botonBuscar').click(function(){

                
            let validacion = true;

            //parametros
            let informe = $('#agrupoPor').val();
            let valores = $('#verInforme').val();
            let periodo = $('#campoPeriodo').val();
            let decimales = $('#decimales').val();

            if(informe==''){
                validacion = false;
                Swal.fire({
                    icon: 'info',
                    title: 'Mensaje informativo',
                    text: 'Debe seleccionar un informe.',
                    confirmButtonText: 'Aceptar'
                });

            }


            //fechas
            let fechaDesde = $('#fechaDesde').val();
            let fechaHasta =$('#fechaHasta').val();

            let fechaDesdeDos = $('#fechaDesdeDos').val();
            let fechaHastaDos =$('#fechaHastaDos').val();

            if (!controlarFechas(fechaDesde, fechaHasta,periodo)) {
                Swal.fire({
                    icon: 'info',
                    title: 'Las fechas primarias no cumplen con los requisitos del periodo',
                    confirmButtonText: 'Aceptar',
                    
                });
                validacion = false;
            }
            if(fechaDesdeDos != ''  && fechaHastaDos!= ''){
                if (!controlarFechas(fechaDesdeDos, fechaHastaDos,periodo)) {
                Swal.fire({
                    icon: 'info',
                    title: 'Las fechas secundarias no cumplen con los requisitos del periodo',
                    confirmButtonText: 'Aceptar',
                
                    });
                validacion = false;
                }
            }


            //filtro

            let filtro = $('#filtrarPor').val();
                   


                    var datosAEnviar = []; 


                $('#listaFiltro li').each(function() {
                    var valor = $(this).text(); 
                    var tipoFiltro = valor.split("-");

                    if (tipoFiltro[0] == filtro) {
                        var val = $(this).data('valor');
                        datosAEnviar.push(val.split(',')[0]) 
                    }
                });

         if(validacion){

            $("#spinner").show();

            let datos = {
                compras : true,
                informe : informe,
                valores: valores,
                periodo : periodo,
                decimales : decimales,
                fechaDesde : fechaDesde,
                fechaHasta : fechaHasta,
                fechaDesdeDos : fechaDesdeDos,
                fechaHastaDos : fechaHastaDos,
                filtro:filtro,
                filtroCodigos:datosAEnviar

            }

            
            
            $.ajax({

                    url:'ajax/json_datos_informe_compras.php',
                    method:'GET',
                    data:datos,
                    success: function(response){

                        $('#myTable').remove();
                        $('#myTable_wrapper').remove()
                        $('#contiene-tabla').append('<table class="display" cellspacing="1" id="myTable"></table>');

                        let nombre = 'Proveedor'
                        if(informe == 'comprasArticuloComprobante' || informe == 'comprasArticuloRegistro'){nombre = 'Articulo'}
                       

                        let data = JSON.parse(response)
                       console.log(data)
                       let columnsConfig = [
                                { data: 'codigo', title: 'Código' },
                                { data: 'ncliente', title: nombre },
                                { data: 'fecha', title: 'Fecha' },
                                { data: 'saldo', title: 'Saldo' }
                            ]

                       if(informe == 'comprasArticuloRegistro'){

                            columnsConfig = [
                                
                                { data: 'codigo', title: 'Código' },
                                { data: 'ncliente', title: nombre },
                                { data: 'Cantidad', title: 'Cantidad' },
                                { data: 'PrecioNetoxR', title: 'Precio Neto' },
                                { data: 'fecha', title: 'Fecha' },
                                { data: 'saldo', title: 'Saldo' }
                            ]

                       }
                      
                       if ($.fn.DataTable.isDataTable('#myTable')) {
                                
                                $('#myTable').DataTable().clear().destroy();
                                
                            }
                            
                            $('#myTable').DataTable({
                                data: data,
                                columns: columnsConfig,
                                searching:true,
                                responsive:true,
                                ordering:false,
                                "language": {
                                    "emptyTable":     "No hay datos disponibles en la tabla",
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
                                }
                            });


                    
                       $("#spinner").hide();
                       },

                    error: function(x, e) {

                    $("#spinner").hide();

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
                        

                    })




        }


        
   })












});

    </script>


</head>

<body>

<div id="wrapper">
        <?php
        require_once $barra;
        ?>
                  
            <div class="paneles filtroInformes">
                <h1>Compras<span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw" aria-hidden="true"></i></span></h1>



                <form id="formBusca" name="formBusca" method="POST" action="">                    
          



                <div class="panelesBloqueInforme">
                    <div class="control">
                        <label for="agrupoPor" class="parametros">Informes:  </label>
                            <select name="agrupoPor" id="agrupoPor" required="required">
                                <option value=""> - seleccionar -</option>
                                <option value="comprasProveedorComprobante">Compras - Proveedor, por comprobantes</option>
                                <option value="comprasProveedorRegistro">Compras -Proveedor, por Registro </option>
                                <option value="comprasArticuloComprobante">Compras - Artículo por Comprobantes</option>
                                <option value="comprasArticuloRegistro">Compras - Artículo por Registro</option>

                            </select>
                    
                    </div>
                </div>





                <div class="panelesBloqueInforme">
                <label for="verInforme" class="parametros">Valores por:</label>
                    <select name="verInforme" id="verInforme" required="required">
                        <option value="monto" selected="selected">Monto ($)</option> 
                       </select>
                </div>


                <div class="panelesBloqueInforme">
                    <label for="campoPeriodo" class="parametros">Período: </label>
                        <select name="campoPeriodo" id="campoPeriodo" required="required">
                            
                            <option value="diario">Diario</option>
                            <option value="semanal">Semanal</option>
                            <option value="mensual" selected="selected">Mensual</option>    
                        </select>
                </div>
   
   
                    <div class="panelesBloqueInforme">

                    <div class="control">
                        <label for="decimales" class="parametros">Decimales:</label>
                            <select name="decimales" id="decimales" required="required">
                                <option value="0">No</option>
                                <option value="1">1</option>
                                <option value="2" selected="selected">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>        
                            </select>

                        </div>
                    </div>


                                    
                <div class="panelesBloqueInforme">

                    <div class="titulo">
                        Fechas
                    </div>

                        <div id="buscaFecha" class="control w100p">
                            <label>Primario </label><br>    
                            <label for="fechaDesde" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i> <input type="date" name="fechaDesde" id="fechaDesde" required="required" value=""> </label>al 

                            <label for="fechaHasta" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i> <input type="date" name="fechaHasta" id="fechaHasta" required="required" value=""></label>
                        </div>

                
                        <div id="buscaFecha" class="control w100p">
                            <label>Secundario</label><br>
                            <label for="fechaDesdeDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i> <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"></label> al

                            <label for="fechaHastaDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i> <input type="date" name="fechaHastaDos" id="fechaHastaDos"></label>
                        </div>

                
                </div>


                <div class="panelesBloqueInforme">

                        <div class="separador25px"></div>
                        <div class="titulo">
                            Filtros
                        </div>

                        <div class="control w100p">

                            <label for="filtrarPor" class="parametros">Tipo:
                                <select name="filtrarPor" id="filtrarPor">                
                                    <option value="">- seleccionar -</option>
                                    <option value="proveedor">Proveedor</option>
                                    <option value="articulo">Articulo</option>
                                </select>
                            </label>
                        </div>

                        <div class="control w100p">

                            <label for="seleccionFiltro" class="parametros">Valor a filtrar: </label>
                            <input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
                            <button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fas fa-plus fa-lg fa-fw" aria-hidden="true"></i> </button>
                        </div>
                        <div class="separador"></div>
                        <div class="control w100p">
                            <label for="listaFiltro" class="parametros">
                                <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
                                <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">

                            </label>
                        </div>

                </div>






                                <div class="panelesBloqueInformeAccion">
                                    <span class="centro w100p">
                                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                                            <i class="fas fa-check fa-lg fa-fw" aria-hidden="true"></i> Generar
                                        </button>
                                    </span>
                                </div>

         </form>
    </div>


<div class="paneles" id="contiene-tabla" style="min-width:fit-content">
<!-- <div class="paneles" id="contiene-tabla" style="overflow:auto"> -->

    <h1>Estadísticas de compras <i id="expandir" class="fa fa-expand fa-lg fa-fw" title="expandir"></i> </h1>
    <!-- <h4 id="tituloInforme">Ventas netas por cliente</h4> -->
    <div>

    <div id="contiene-tabla" >
                    
            
        <table class="display" cellspacing="1" id="myTable"></table>
</div>


</div>


<?php require_once 'footer.php'; ?>

</div>
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