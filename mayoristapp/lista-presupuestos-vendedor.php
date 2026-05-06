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
$usaZoom=0;

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
    <title>Presupuestos del vendedor|administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>

<script>
 $.extend( $.fn.dataTable.defaults, {
   
     searching:false,
     responsive:true,
     ordering:false,
           
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
    }
} );      
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(document).ready(function(){ 
    var buttonCommon = {
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
                    format: {
                        body: function(data, row, column, node) {
                            // Strip $ from salary column to make it numeric
                            //                    return column === 5 ?
                            //                        data.replace( /[$,]/g, '' ) :
                            //                        data;
                            //                    console.log({data});
                            //                    console.log({row}); 
                            //                    console.log({column});
                            if (!isNaN(data)) {
                                // console.log("data formateado: "+dinero.format(data));

                                data = dinero.format(data);
                                //console.log("data formateado: "+data);
                                //data=numerito.format(data);
                                //console.log("data formateado: "+data);

                                //b=a.replace(/ /g, "");
                                //                        data = '$ '+data;
                                //                        console.log("data sin el espacio del pesos"+data);

                            }
                            if (column == 12) {
                                console.log('soy la columna del anulado', data, column);
                                var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                                //anulado = anulado.replace('</strong>,', '');
                                // console.log("data coma x punto: "+numero);
                                data = anulado;
                            }
                            return data;
                        },
                        footer: function(data, row, column, node) {

                            if (!isNaN(data)) {
                                // console.log("data formateado: "+dinero.format(data));

                                const arrColumnasNumero = [5, 6, 7];
                                // console.log('soy la columna debo ingresar',arrColumnasNumero.includes(column),'columna:',column);
                                if (arrColumnasNumero.includes(column)) {
                                    data = dinero.format(data);

                                }

                            }
                            return data;
                        }
                    }
                }
            };
            var buttonCommonExcel = {
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
                    format: {
                        body: function(data, row, column, node) {

                            const arrColumnasNumero = [4, 5, 6];

                            if (arrColumnasNumero.includes(column)) {

                                var numero = data.replace(/[$.]/g, '');

                                numero = numero.replace(',', '.');
                                // console.log("data coma x punto: "+numero);
                                data = numero;


                            }
                            // console.log('soy la columna del anulado',data,column);
                            if (column == 12) {
                                // console.log('soy la columna del anulado', data, column);
                                var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                                //anulado = anulado.replace('</strong>,', '');
                                // console.log("data coma x punto: "+numero);
                                data = anulado;
                            }
                            return data;
                        },
                        footer: function(data, row, column, node) {
                            // console.log('datos del pie',data,row,column,node);
                            const arrColumnasNumero = [5, 6, 7];

                            if (arrColumnasNumero.includes(row)) {

                                data = data.replace(/[$.]/g, '');
                                data = data.replace(',', '.');
                            }
                            return data;
                        }
                    }
                }
            };
            // funcion para cambiar la fecha.
            function fechaReverso(date) {
                console.log(date);
                var a = date.split('-');
                var d = new Number(a[2]);
                var m = new Number(a[1]);
                var y = new Number(a[0]);
                var dd = new Date(y, m - 1, d);
                var y = dd.getFullYear();
                var m = dd.getMonth() + 1;
                var d = dd.getDate();
                return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y;
                //return dd;
            }
       
       
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).hide();
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
           // para que se borren lo que tienen adentro las fechas   
     
     $('#campoBusca').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
         $('#fechaDesde').val('dd/mm/aaaa'),
         $('#fechaHasta').val('dd/mm/aaaa'),
         $('#numeroComp').val('');
         switch(valor){
             case 'Fecha':
                   $('#buscaNumero').hide();
                   $('#buscaTipo').hide();
                   $('#suggestions').hide();
                   $('#buscaFecha').show(400);
                   
                   
                 break;
             case 'NroComprobante':
                   $('#buscaFecha').hide();
                   $('#buscaTipo').hide();
                   $('#buscaNumero').show(400);
                 break;
             case 'TipoPedido':
                   $('#buscaFecha').hide();
                   $('#buscaTipo').show(400);
                   $('#buscaNumero').hide();                    
                 break;

             case '-':
                   $('#buscaFecha').hide();
                   $('#buscaTipo').hide();
                   $('#buscaNumero').hide();                    
                 break;
         }
         
     });
     // boton para buscar coincidencias
     $('#botonBuscar').on("click",function(event){
        //$('#spinner').show()
        let botonBusca =$(this);
        botonBusca.prop('disabled', true);
        botonBusca.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
        var contienes      = $('#myTable'), 
            campoBusca      = $('#campoBusca').val(),
            fechaDesde      = $('#fechaDesde').val(),
            fechaHasta      = $('#fechaHasta').val(),
            numeroComp      = $('#numeroComp').val(),
            estadoPedido    = $('#estadoPedido').val(),
            tipoPedido      = $('#tipoPedido').val(),
            listaPed        = $('#listaTodos').val();
        var filtraVendedor = $('#filtraVendedor').val();
        var tituloListado = "",
                    mensajeTop = "",
                    nombreInforme = "";
            //$('formBusca').submit();
            var clienteTexto, tipoPedidoTexto, estadoPedidoTexto, vendedorTexto;

            estadoPedidoTexto = $('#estadoPedido option:selected').text();
            vendedorTexto = $('#filtraVendedor option:selected').text();

        var datosLogin = obtenerUsuarioLogueado();

        $.ajax({
            type: 'GET',
            url: 'relay-clientes.php',
            data: {
                "traeDatosClienteSeleccionado": 1,

            },
            success: function(response) {
                console.log('objeto cliente seleccionado', response.length);
                let resultado = response.length;
                if (resultado != 0) {
                    clienteTexto = response[0].cliente + ' (Cod: ' + response[0].Codigo + ')';
                }
                // cero si viene vacio.

                // console.log('cliente',response[0].cliente);
                // console.log('codigo',response[0].Codigo);

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
         
        $.ajax({
                type: 'POST',
                url: 'relay-presupuestos.php',
                data:{
                    "ajax"          : "true",
                    "vendedor"      : "true",
                    "campoBusca"    : campoBusca,
                    "fechaDesde"    : fechaDesde,
                    "fechaHasta"    : fechaHasta,
                    "numeroComp"    : numeroComp,
                    "estadoPedido"  : estadoPedido,
                    "tipoPedido"    : tipoPedido,
                    "listaPed"      : listaPed,
                    "filtraVendedor":filtraVendedor,

                },
                success: function(response) {
                    // $('#spinner').hide()
                                        
                    botonBusca.prop('disabled', false);
                    botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar'); 

                    if(response==='0'){
                        let tablaVuelta = "";
                            tablaVuelta +='<thead>';
                            tablaVuelta +='            <tr>';                
                            tablaVuelta +='                <th></th>';				
                            tablaVuelta +='            </tr>';
                            tablaVuelta +='        </thead>';
                            tablaVuelta +='        <tbody>';
                            tablaVuelta +='<tr>';               
                            tablaVuelta +='<td>';
                            tablaVuelta +='No se encontaron resultados';
                            tablaVuelta +='</td>';
                            tablaVuelta +='</tr>';
                            tablaVuelta +='</tbody>';
                            contienes.html(tablaVuelta);
                        return false;
                    }

                    var fecha = new Date();
                        var fechaFormateada = fecha.getFullYear() + '_' +
                            ("0" + (fecha.getMonth() + 1)).slice(-2) + '_' +
                            ("0" + fecha.getDate()).slice(-2) + '_' +
                            ("0" + fecha.getHours()).slice(-2) + '_' +
                            ("0" + fecha.getMinutes()).slice(-2) +
                            ("0" + fecha.getSeconds()).slice(-2);
                        const today = new Date();
                        const day = String(today.getDate()).padStart(2, '0');
                        const month = String(today.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
                        const year = today.getFullYear();

                        const formattedDate = `${day}/${month}/${year}`;
                        console.log(formattedDate);
                        let simbIzq = "$";

                        // nombre archivo 
                        nombreInforme += "listado_pedidos_" + fechaFormateada;
                        tituloListado += "Listado de Pedidos";
                        // fecha
                        if (campoBusca == "Fecha" && fechaDesde != "" && fechaHasta != "") {

                            mensajeTop += " Rango: " + fechaDesde + " al " + fechaHasta + "\n";
                        }
                        // por numero de comprobante
                        if (campoBusca == "NroComprobante" && numeroComp != "") {
                            mensajeTop += " N° Comprobante: " + numeroComp + "\n";
                        }
                        // clientes
                        if (listaPed != "todos" && clienteTexto != "") {
                            // soy cliente seleccionado pero puedo no tener un cliente seleccionado.

                            mensajeTop += " Cliente: " + clienteTexto + "\n";
                        }

                        
                        if (estadoPedido != "") {
                            mensajeTop += " Estado: " + estadoPedidoTexto + "\n";
                        }

                        // vendedor
                        mensajeTop +=" Vendedor: "+vendedorTexto +"\n";

                        mensajeTop += " Emitido: " + formattedDate + "\n";
                        mensajeTop += " Generado por: " + datosLogin.vendedor.nombre_usuario + " " + datosLogin.vendedor.apellido_usuario + "\n";

                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            dom: 'Bfrtip',
                            "paging": true,                           
                           "pageLength": 5,
                            "lengthMenu": [ [5, 18, 21, -1], [5, 18, 21, "All"] ],
                           "columnDefs": [ 
                            {
                                    "targets": 0, // Esta será la columna para el número de fila (se crea de manera dinámica)
                                    "orderable": false, // No queremos que sea ordenable
                                    "searchable": false, // No queremos que sea parte de la búsqueda
                                    "render": function(data, type, row, meta) {
                                        return meta.row + 1; // Calcula el índice (meta.row devuelve el número de fila 0-indexado)
                                    }
                                },
                            {
                                            "targets": [5,6,7],
                                            "render":  $.fn.dataTable.render.number( '.', ',', 2, '$')  
                
                                          } 
                                        ],
                            "createdRow": function(row, data, dataIndex) {
                                // console.log('que viene en columna 12:',data[12]);
                                if (data[13] === '<strong>Si</strong>') {
                                    // Apply red color to the entire row
                                    $(row).css('color', 'red');
                                }
                            },            
                            "footerCallback": function(row, data, start, end, display) {
                                var api = this.api();

                                // Loop through each column you want to update in the footer
                                api.columns([5, 6, 7]).every(function() {
                                    var column = this;
                                    // Use reduce to sum the data
                                    var sum = column
                                        .data()
                                        .reduce(function(a, b) {
                                            return parseFloat(a) + parseFloat(b);
                                        }, 0);

                                    // Update footer with formatted number
                                    $(column.footer()).html(
                                        $.fn.dataTable.render.number('.', ',', 2, simbIzq).display(sum)
                                    );
                                });
                            },  
                            buttons: [
                                $.extend(true, {}, buttonCommonExcel, {
                                    extend: 'excelHtml5',

                                    footer: true,
                                    title: tituloListado,
                                    filename: nombreInforme,
                                    messageTop: mensajeTop,
                                    pageSize: 'LEGAL',
                                    orientation: 'landscape'

                                }),



                                $.extend(true, {}, buttonCommon, {
                                    extend: 'pdfHtml5',
                                    title: tituloListado,
                                    filename: nombreInforme,
                                    messageTop: mensajeTop,
                                    footer: true,

                                    pageSize: 'A3',
                                    orientation: 'landscape'

                                })
                            ]
                        });
                         //$("#spinner").hide();
                },
                error: function(x, e) {
                    $('#spinner').hide()
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar'); 
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
       * VISUALIZAR COMPROBANTE MOVIL
       * @param {type} event
       * @returns {undefined}
       */
     $(document).on('click', '.verComprobante', function(event) {
        //event.preventDefault();
        var 
            codigoMovimiento    = $(this).attr('mov'),
            tipoComprobante     = $(this).attr('comprobante');
//            alert($(this).attr('mov') + ' -  ' + $(this).attr('comprobante')+' - '+ este.attr('mov')+' - '+este.attr('comprobante'));
            
        $.ajax({
                type:   'POST',
                url:    'ver_presupuesto-movil.php',
                data:{
                        "ajax":"true",
                        "codigomovimiento": codigoMovimiento,
                        "comprobante": tipoComprobante
                },
                success: function(response){
//                    alert(response);
                    $('#basic-modal-content').html('');
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
                    //return false;
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
    
     //esta es la forma de hacer que ande sin tanto complicaion    
    // $('#verFiltros').on("click",function(){
    //      $(this).toggleClass('iconoAzul');
    //      $('#formBusca').toggle();
    //  });
    //   $('#formBusca').hide();
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
        
        <div id="content">  
            
        
        <div class="paneles filtroInformes">          
               <h1>Filtros <span ><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">

            <div class='panelesBloqueInforme'>
                <!-- <label>Filtros:</label> <i class="fa fa-filter fa-lg " id="verFiltros"></i> -->
                <div class="control">
                        <label for="filtraVendedor">Vendedor / Viajante:  
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
                        </label>
                    </div>
                    <div class="control">
                        <label class="parametros" for="estadoPedido">Clientes:   </label>
                            <select name="listaTodos" id="listaTodos">
                                <option value="cliente">Seleccionado</option>
                                <option selected value="todos">Todos </option>
                            </select>
                       
                    </div>




                   <div class="control">
                        <label class="parametros" for="estadoPedido">Estado:</label>  
                            <select name="estadoPedido" id="estadoPedido">
                                <option value="1">-</option>
                               
                                <option value="En Remito">En Remito</option>
                                <option value="Facturado">Facturado</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En Pedido">En Pedido</option>
                                <option value="Imput manual">Imput manual</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="Completo">Completo</option>
                                <option value="Parcial">Parcial</option>
                                <option value="Cerrado">Cerrado</option>
                                <option value="En preparación">En preparación</option>
                                <option value="Preparado">Preparado</option>
                            </select>
                        
                    </div>


                  



                    
                    <div class="control">
                        <label class="parametros" for="campoBusca">Buscar por: </label> 
                            <select name="campoBusca" id="campoBusca">
                                <option value="-">-</option>
                                <option value="Fecha">Fecha</option>
                                <option value="NroComprobante">Número</option>
                                <option value="TipoPedido">Tipo Presupuesto</option>
                            </select>
                        
                    </div>



                    
                    <div id="buscaFecha" style="display:none" class="control">
                        <label class="parametros" for="fechaDesde">Desde: </label>
                            <input type="date" name="fechaDesde" id="fechaDesde">
                            
                        <label class="parametros" for="fechaHasta">Hasta: </label>
                            <input type="date" name="fechaHasta" id="fechaHasta">
                    </div>
                    
                    <div id="buscaNumero"  class="control" style="display:none">
                        <label for="numeroComp">Nº Comp.: </label>
                            <input type="text" name="numeroComp" id="numeroComp" >
                        
                    </div>
                    <div id="buscaTipo" class="control" style="display:none">
                        <label class="parametros" for="tipoPedido">Tipo de presupuesto: </label>
                            <select id="tipoPedido" name="tipoPedido" >
                                <option value="1">-</option>
                                <option value="Sistema">Sistema</option>
                                <option value="Web">Web</option>
                            </select>
                        
                        
                    </div>
</div>
                 
                <div class="panelesBloqueInformeAccion">
                        <span class="centro w100p">
                            <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                                <i class="fas fa-search fa-lg fa-fw"></i> Buscar
                            </button>
                        </span>
                    </div>
                    
               </form>
            </div>
           
                <!-- <div class="buscador" id="barraBusca">
                     <label>Filtros:</label> <i class="fa fa-filter fa-lg " id="verFiltros"></i>
                    <form id="formBusca" name="formBusca" method="POST" action="">
                       
                        <div class="control">
                            <label for="estadoPedido">Clientes:  
                                <select name="listaTodos" id="listaTodos">
                                    <option value="cliente">Seleccionado</option>
                                    <option selected value="todos">Todos </option>
                                </select>
                            </label>
                        </div>
                        <div class="control">
                            <label for="estadoPedido">Estado:  
                                <select name="estadoPedido" id="estadoPedido">
                                    <option value="">-</option>

                                    <option value="En Remito">En Remito</option>
                                    <option value="Facturado">Facturado</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En Pedido">En Pedido</option>
                                    <option value="Imput manual">Imput manual</option>
                                    <option value="Aprobado">Aprobado</option>
                                    <option value="Completo">Completo</option>
                                    <option value="Parcial">Parcial</option>
                                    <option value="Cerrado">Cerrado</option>
                                    <option value="En preparación">En preparación</option>
                                    <option value="Preparado">Preparado</option>
                                </select>
                            </label>
                        </div>
                        <div class="separador25px"></div>
                        <div class="control">
                            <label for="campoBusca">Buscar por:  
                                <select name="campoBusca" id="campoBusca">
                                    <option value="">-</option>
                                    <option value="Fecha">Fecha</option>
                                    <option value="NroComprobante">Número</option>
                                    <option value="TipoPedido">Tipo Pedido</option>
                                </select>
                            </label>
                        </div>
                        <div class="separador10px"></div>
                        <div id="buscaFecha" style="display:none" class="control">
                            <label for="fechaDesde">Desde: 
                                <input type="date" name="fechaDesde" id="fechaDesde"></label>
                            <label for="fechaHasta">Hasta: 
                                <input type="date" name="fechaHasta" id="fechaHasta"></label>
                        </div>

                        <div id="buscaNumero"  class="control" style="display:none">
                            <label for="numeroComp">Nº Comp.: 
                                <input type="text" name="numeroComp" id="numeroComp" >
                            </label>
                        </div>
                        <div id="buscaTipo" class="control" style="display:none">
                            <label for="tipoPedido">Tipo de pedido: 
                                <select id="tipoPedido" name="tipoPedido" >
                                    <option value="">-</option>
                                    <option value="Sistema">Sistema</option>
                                    <option value="Web">Web</option>
                                </select>
                            </label>

                        </div>

                        <div class="separador10px"></div>
                        <div class="control">
                            <button  title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo chico azul">
                                <i class="fa fa-search fa-lg" ></i>
                            </button>
                           <input type="button" name="botonBuscar" class="buttons" id="botonBuscar" value="Buscar">
                        </div>

                    </form>
                </div>      -->
            
<!--             
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->
            <div class="paneles table-responsive"   id="contiene-tabla" >
            <?php if(isset($_REQUEST['cartel'])&&($_REQUEST['cartel']=='5' || $_REQUEST['cartel']=='6' )):?>
                    <?php
                        $textoCartel = '<div id="alertas-formulario" class="alerta-exito">'
                                . 'Se ha generado:<br>';
                        if(isset($_GET['pre'])){ $textoCartel .='Presupuesto: <strong>'.$_GET['pre'].' <i class="fa fa-check-circle"></i></strong><br>';}
                        if($_GET['cartel']=='6'){ $textoCartel .='Email enviado: <strong> <i class="fa fa-check-circle"></i></strong><br>';}
                        if($_GET['cartel']=='5'){ $textoCartel .='Email NO enviado: <strong> <i class="fa fa-times-circle"></i></strong><br>';}
                        $textoCartel .='<div style="text-align:center">'

                                . '</div></div>';
                     ?>
                    <div id="basic-modal-content" class="cartelCliente"><?php echo $textoCartel;?>

                    </div>
                <?php endif;?>
                <h1>Listado de presupuestos</h1>
                <table class="display" cellspacing="1" id="myTable" ></table>

           
        </div>
    </div>
    <?php require_once 'footer.php';?>   
    
    </div>
    <div id="basic-modal-content" > </div>
      <!--spinner admNET-->
    <div id="spinner" class="spinnerAdm" style="display:none;">
        <div class="centro">
            <img src="_img/logo-administranet-ecommerce.png">
            <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
        </div>
    </div>
    <!--fin spinner-->
    <?php if(isset($_REQUEST['cartel'])):?>
        <script>
            $(document).ready(function(){
        //        $('#basic-modal-content').empty();
        //        $('#basic-modal-content').html(response);
                //$('#basic-modal-content').maxHeight = 200,
                var wPantalla=$(document).width();  
                var wVentana=0, hVentana=0;    
                            if(wPantalla>320){
                               wVentana=400;
                               hVentana=100;
                            }else{
                                wVentana=300;
                                hVentana=200;
                            }
//                console.log(wPantalla);
                
                    $('#basic-modal-content').modal({
                            minWidth : wVentana,
                            minHeight: hVentana
                            
                        });
                
            });
        </script>
    <?php endif;?>
    <div id="basic-modal-content" > </div>
    </body>
</html>