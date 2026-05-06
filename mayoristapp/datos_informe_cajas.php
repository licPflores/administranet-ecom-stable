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

    <title>Cajas | administraNET</title>
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

        if (!controlarFechas(fechaDesde, fechaHasta,periodo)) {
            Swal.fire({
                icon: 'info',
                title: 'Las fechas no cumplen con los requisitos del periodo',
                confirmButtonText: 'Aceptar',
                
            });
            validacion = false;
        }



        //datos
        let tipoCaja = $('#Supra').val();


         if(validacion){

            $("#spinner").show();

            let datos = {
                cajas : true,
                informe : informe,
                valores: valores,
                periodo : periodo,
                decimales : decimales,
                fechaDesde : fechaDesde,
                fechaHasta : fechaHasta,
                tipoCaja:tipoCaja

            }

           
            
            $.ajax({

                    url:'ajax/json_datos_informe_cajas.php',
                    method:'GET',
                    data:datos,
                    success: function(response){

                       
                        $('#myTable').remove();
                        $('#myTable_wrapper').remove()
                        $('#contiene-tabla').append('<table class="display" cellspacing="1" id="myTable"></table>');
                      
                        let data = JSON.parse(response)
                        console.log(data)
                        let columnsConfig = [];

                    if(informe == 'cajaEstadistica'){

                        columnsConfig = [
                            { data: 'codigo', title: 'Código' },
                            { data: 'ncliente', title: 'Cliente-Proveedo' },
                            { data: 'fecha', title: 'Fecha' },
                            { data: 'saldo', title: 'Saldo' }
                        ]

                    }
                    if(informe == 'cajaDetallada'){

                        columnsConfig = [
                            { data: 'cliente-proveedor', title: 'Cliente-Proveedor' },
                            { data: 'comp', title: 'Comp' },
                            { data: 'detalle', title: 'Detalle' },
                            { data: 'egreso', title: 'Egreso' },
                            { data: 'fecha', title: 'Fecha' },
                            { data: 'ingreso', title: 'Ingreso' },
                            { data: 'nroComp', title: 'NroComp' },
                            { data: 'saldo', title: 'Saldo' },
                            { data: 'tipo', title: 'Tipo' }
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
            
   




    //      }



        
   })




})









</script>


    </head>

<body>

<div id="wrapper">
        <?php
        require_once $barra;
        ?>
                  
            <div class="paneles filtroInformes">
                <h1>Cajas<span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw" aria-hidden="true"></i></span></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">    


                 
                <div class="panelesBloqueInforme">
                        <div class="control">
                            <label for="agrupoPor" class="parametros">Informes:  </label>
                                <select name="agrupoPor" id="agrupoPor" required="required">
                                    <option value="" selected="selected"> - seleccionar -</option>
                                    <option value="cajaDetallada" >Caja lista detallada</option>
                                    <option value="cajaEstadistica">Caja estadística</option>

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

                        <option value="diario" selected="selected">Diario</option>
                        <option value="mensual">Mensual</option>     
                       </select>
                </div>
   
                
                <div class="panelesBloqueInforme">

                    <div class="control">
                        <label for="decimales" class="parametros">Decimales:</label>
                            <select name="decimales" id="decimales" required="required">
                                <option value="2" selected="selected">2</option>      
                          </select>

                        </div>
                    </div>



            <div class="panelesBloqueInforme">

                <div class="titulo">
                    Fechas
                </div>
                <div class="tituloFecha">

                    <div id="buscaFecha" class="control w100p">
                        <label>Primario </label><br>    
                        <label for="fechaDesde" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i> 
                        <input type="date" name="fechaDesde" id="fechaDesde" required="required" value=""> </label> al 

                        <label for="fechaHasta" class="parametros"><i class="fa fa-calendar fa-lg fa-fw" aria-hidden="true"></i>
                         <input type="date" name="fechaHasta" id="fechaHasta" required="required" value=""></label>

                    </div>
                </div>              

            </div>	
            
            


                <div class="panelesBloqueInforme">
                    <div class="titulo">Datos</div>
                    <div class="control">
                    <label for="Supra" class="parametros">Tipo:   </label>		
                    <select name="Supra" id="Supra">
                        <option value="">-tipo de caja-</option>
                        <option value="0">Acumulativa</option>
                        <option value="1">Fondo Fijo</option>
                        <option value="2">Efectivo Pto Vta.</option>
                        <option value="3">Cheque</option>
                        <option value="4">Acumulativa Cheque</option>
                        <option value="5">Tarjeta</option>
                        <option value="6">Otro medio de cobro</option>
                        <option value="7">Acumulativa Otro Medio de Cobro</option>
                    </select>

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

    <h1>Estadísticas de cajas <i id="expandir" class="fa fa-expand fa-lg fa-fw" title="expandir"></i> </h1>
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