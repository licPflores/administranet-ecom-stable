<?php 
    
    //require_once $caminoDispo.'jcart/jcart.php';
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

$usaLaboratorio = $_SESSION["usa_laboratorio"];

// echo '<pre>',print_r($_SESSION['cliente']),'</pre>';
$nombreCliente = "";
if(isset($objCliente)&&is_object($objCliente)){
    
    $cadena= $objCliente->cliente;
    $longitud = 30;
    $nombreCliente=$cadena;
    if (mb_strlen($cadena) > $longitud) {
        // Trunca la cadena y agrega los puntos suspensivos
        $nombreCliente= mb_substr($cadena, 0, $longitud - 3) . '...';
    } 
    
}
// $result = $conn->query($sql);

// if ($result->num_rows > 0) {
//     echo "El campo $field existe en la tabla $table.";
// } else {
//     echo "El campo $field no existe en la tabla $table.";
// } 

// [baseConecto] => administranet74
//     [servidor] => 190.15.214.142
//     [puerto_db] => 3306
 
    
    /**
     * Tipo de clientes que ya estan en la tabla nueva
     */
    
    $tipoCliente=array();
    $sqlTipoCliente = "SELECT 
                            tipo_cliente.NombreTipoCliente AS tipo, 
                            tipo_cliente.IDTipoCliente AS id                        
                        FROM tipo_cliente 
                        WHERE
                        tipo_cliente.anulado='No'
                        ORDER BY tipo_cliente.NombreTipoCliente ASC;";
    $hacerT = mysqli_query($connV,$sqlTipoCliente) or die("No puedo ejecutar consulta TipoCliente".mysqli_error($connV));
    while($tp = mysqli_fetch_assoc($hacerT)){
        $tp['tipo'] = ucwords(strtolower($tp['tipo']));
        $tipoCliente[] = $tp;
    }
    // echo '<pre>';    ESTE ARRAY VA PARA TIPO NEGOCIO
    // print_r($tipoCliente);
    // echo '</pre>';    
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();

    
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <title>administraNET e-com | Lista de precios al <?php echo date("d/m/Y");?></title>
   
     <?php require_once 'cabecera.php';?>
    
    
   
<script>
    
 $(document).ready(async function(){
    // Botón PDF: envía los filtros actuales al backend para exportar PDF
    $('#botonExportarPDF').click(function() {

        var params = [];
        params.push('categoria=' + encodeURIComponent($('#buscaCategoria').val() || ''));
        params.push('rubro=' + encodeURIComponent($('#buscaRubro').val() || ''));
        params.push('subrubro=' + encodeURIComponent($('#buscaSubRubro').val() || ''));
        params.push('marca=' + encodeURIComponent($('#buscaMarca').val() || ''));

        // agregar los text de los val que son los que se muestran en el select, no el id, para mostrarlo en el pdf.
         params.push('categoriaText=' + encodeURIComponent($('#buscaCategoria option:selected').text() || ''));
        params.push('rubroText=' + encodeURIComponent($('#buscaRubro option:selected').text() || ''));
        params.push('subrubroText=' + encodeURIComponent($('#buscaSubRubro option:selected').text() || ''));
        params.push('marcaText=' + encodeURIComponent($('#buscaMarca option:selected').text() || ''));


        params.push('tipoCliente=' + encodeURIComponent($('#tipoCliente').val() || ''));
        params.push('listaDePrecios=' + encodeURIComponent($('#listaDePrecios').val() || ''));
        params.push('imagenProducto=' + encodeURIComponent($('#imagenProducto').val() || ''));
        params.push('proveedor=' + encodeURIComponent($('#buscaProveedor').val() || ''));
        params.push('tacc=' + encodeURIComponent($('#buscaTacc').val() || ''));
        params.push('ivaIncluido=' + encodeURIComponent($('#buscaTipoIva').val() || ''));
        params.push('cliente=' + encodeURIComponent($('#cliente').val() || ''));
        params.push('clienteText=' + encodeURIComponent($('#cliente option:selected').text() || ''));
        params.push('queArticulo=' + encodeURIComponent($('#nombreBuscaRapido').val() || ''));
        params.push('idArticulo=' + encodeURIComponent($('#itemId').val() || ''));

        var claseBuscaPdf = $('#claseBusca').val() || '';
        if ($('#itemId').val()) {
            claseBuscaPdf = 'codigo';
        } else if ($('#nombreBuscaRapido').val()) {
            claseBuscaPdf = 'texto';
        }
        params.push('claseBusca=' + encodeURIComponent(claseBuscaPdf));

        if ($('#buscaMiConsumo').is(':checked')) {
            params.push('consumo=1');
        }
        var url = 'exporta_lista_pdf.php?' + params.join('&');
        window.open(url, '_blank');
    });

// var datosSesion = await obtenerDatosSesion();


 //console.log('soy mobile=>',soyMobil);
 inicioAutoCompletar("articuloListaPrecio");


     
     // lista precios
     $('#formBusca').on("submit",function(){
         event.preventDefault();

     });


    $('#botonBuscar').click(async function() {
        await buscarProductosLp();
    });


  /*
  * 
  * Busca Categoria
   */
   
   $('#buscaCategoria').change(function(){
        var categoria = $(this).val();
        $("#spinner").show();
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
                    $("#spinner").hide();
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
                    $("#spinner").hide();
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
                // console.log(j);
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
   
   // laboratorio No para todos
    function lista_laboratorio(){
        var laboratorio = $('#buscaLaboratorio');
        if(laboratorio!==undefined){
            $.ajax({
                type: 'get' ,
                url: 'relay-laboratorio.php',
                data: {
                    "listaLaboratorio" : 1
                },
                dataType: 'json',
                success: function (j) {                   
                    // console.log(j);
                    var options = [], i = 0, o = null;
                    
                    $('#buscaLaboratorio').append(o);
                    // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                    if(j!==null){
                        for(i = 0; i < j.length; i++) {
                            // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                            o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                            o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                            o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                            $('#buscaLaboratorio').append(o);
                        }
                    }
                },

                error: function (xhr, desc, er) {
                    // add whatever debug you want here.
                    alert("an error occurred"+xhr+desc+er);
                }
            });
        }       
        
        
    }
    // * sin TACC
    function lista_tacc(){
        var tacc = $('#buscaTacc');
        if(tacc!==undefined){
            $.ajax({
                type: 'get' ,
                url: 'relay-tacc.php',
                data: {
                    "listaTacc" : 1
                },
                dataType: 'json',
                success: function (vuelta) {                   
                    // console.log('vuelta sin tacc',vuelta);
                    // mensaje, valores
                    if(vuelta.mensaje=='sinTacc'){
                        tacc.hide();
                        return;
                    }
                    var j = vuelta.valores;
                    var options = [], i = 0, o = null;
                    
                    tacc.append(o);
                    // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                    if(j!==null){
                        for(i = 0; i < j.length; i++) {
                            // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                            o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                            o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                            o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                            tacc.append(o);
                        }
                    }
                },

                error: function (xhr, desc, er) {
                    // add whatever debug you want here.
                    alert("an error occurred"+xhr+desc+er);
                }
            });
        }       
    }
    // * proveedores
    function lista_proveedores(){
        var proveedor = $('#buscaProveedor');
        if(proveedor!==undefined){
            $.ajax({
                type: 'get' ,
                url: 'relay-proveedor.php',
                data: {
                    "listaProveedor" : 1
                },
                dataType: 'json',
                success: function (j) {                   
                    // console.log(j);
                    var options = [], i = 0, o = null;
                    
                   proveedor.append(o);
                    // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                    if(j!==null){
                        for(i = 0; i < j.length; i++) {
                            // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                            o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                            o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                            o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                           proveedor.append(o);
                        }
                    }
                },

                error: function (xhr, desc, er) {
                    // add whatever debug you want here.
                    alert("an error occurred"+xhr+desc+er);
                }
            });
        }       
    }
    // * lista de precios con los nombres y valor por defecto segun el cliente.
    // validar si tengo cliente seleccionado, su lista de precio sale por defecto.
    // validar no tengo cliente, cual es la lista de precio por defecto y seleccionarla.
    function lista_de_precios(){
        // ya viene con el valor que debera salir por defecto 
        var listaPrecio = $('#listaDePrecios');
        if(listaPrecio!==undefined){
            $.ajax({
                type: 'get' ,
                url: 'relay-lista-precio.php',
                data: {
                    "listaPrecio" : 1
                },
                dataType: 'json',
                success: function (j) {                   
                    // console.log("lista de precio:",j);
                    var options = [], i = 0, o = null;
                    
                   listaPrecio.append(o);
                    // validar si elijo la palabra rubro. que traiga vacie el subrubro.
                    if (j !== null) {
                        for (i = 0; i < j.length; i++) {
                            // Crear el elemento OPTION
                            o = document.createElement("OPTION");

                            // Asignar el valor y el texto de la opción
                            o.value = j[i]['id']; // Asignar el valor del id
                            o.text = j[i]['name']; // Asignar el texto del name

                            // Convertir el valor de selected a booleano
                            
                            const isSelected = j[i]['selected'] === true; // Convierte "true" a true
                            // console.log('isSelected=>',isSelected);
                            // console.log('j[i]["selected"]',j[i]['selected']);
                            // Verificar si la opción debe estar seleccionada por defecto
                            if (isSelected) {
                                o.selected = true; // Seleccionar la opción si selected es "true"
                            }

                            // Agregar la opción al select
                            listaPrecio.append(o);
                        }
                    }
                },

                error: function (xhr, desc, er) {
                    // add whatever debug you want here.
                    alert("an error occurred"+xhr+desc+er);
                }
            });
        }       
    }

    // funcion inicial para cargar los rubros si no se filtra por categoria.
	function inicial_rubros(){
		//rubro
		console.log('invocando a la lista de rubros sin categoria');
		 $("#spinner").show();
            $.ajax({
                type: 'get' ,
                url: 'relay-rubro.php',
                data: {
                    "ajax" : true,
                    "idcategoria" : 0
                },
                dataType: 'json',
                success: function (j) {                   
                    //alert(j);
                    $("#spinner").hide();
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
                    $("#spinner").hide();
                    alert("an error occurred"+xhr+desc+er);
                }
            });
	}

    // * buscar rubros.
    $('#buscaRubro').change(function(){
        var rubro = $(this).val(),
        tipoCliente = $('#tipoCliente').val();
        $("#spinner").show();
        // console.log("rubro{"+rubro+"}");
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
                $("#spinner").hide();                  
                // console.log(j);
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
                $("#spinner").hide();  
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
                // console.log(j);
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
    

// check bonito
    $("#buscaMiConsumo").hide();
    $('label[for=buscaMiConsumo]').on("click",function(){
       
        if($("#buscaMiConsumo").prop("checked")===false){
            $('label[for=buscaMiConsumo] > i').removeClass("fa-square").addClass("fa-check-square");
        }else{
            $('label[for=buscaMiConsumo] > i').removeClass("fa-check-square").addClass("fa-square");
        }
    });
    
    
    
    lista_laboratorio();   
    lista_proveedores();
    lista_tacc();
    lista_de_precios();
    inicial_rubros();
    $('#nombreBuscaRapido').focus();
});
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
        <h1 id="tituloListaP">Listado de precios al <?php echo date("d/m/Y"); ?></h1>

            <div class="paneles filtroInformes">  
           
                <form id="formBusca" name="formBusca" method="POST" action="">
                    
                    <div class='panelesBloqueInforme' style="justify-content: flex-start;">
                        <div class="titulo">Parámetros</div>
                        

                        <div class="controlContainer">
                            <?php if($_SESSION['tipousuario']=='vendedor'):?>
                                <div class="control">
                                    <label class="parametros" for="cliente">Cliente:</label>  
                                        <select name="cliente" id="cliente">
                                            
                                                <option value="todos">-Todos-</option>

                                                <?php if(is_object($objCliente)):?>
                                                    <option value="cliente" selected="selected"><?php echo $nombreCliente;?></option>
                                                <?php endif;?>

                                        </select>
                                </div>   
                        <?php endif;?>

                        <div class="control">
                            <!-- tengo la lista de precios para que seleccione, validar si es un cliente que seleccione pordefecto,
                            si es en todos pone iguala por defecto,
                            configura el ajax articulo para saber si viene la lista de precio seleccionarla. -->
                            <label class="parametros" for="listaDePrecios">Lista de precios: </label>  
                                <select name="listaDePrecios" id="listaDePrecios"></select>
                            
                        </div>
                        <?php if($_SESSION['tipousuario']=='cliente'):?>
                            <div class="control" >
                                <input type="checkbox" name="buscaMiConsumo" id="buscaMiConsumo" value="si">
                                <label class="parametros" for="buscaMiConsumo">                               
                                    <i class="fa fa-square fa-lg" ></i>Mis Consumos</label>
                                
                            </div>
                        <?php endif;?>    
                    
                        <div class="control" >
                            <label class="parametros" for="buscaRubro">IVA Incluido:</label> 
                                    <select id="buscaTipoIva" name="buscaTipoIva">
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                
                        </div>

                        <div class="control" >
                            <label class="parametros" for="buscaRubro">Imagen:</label> 
                                    <select id="imagenProducto" name="imagenProducto">
                                        <option value="Si">Si</option>
                                        <option selected="selected" value="No">No</option>
                                    </select>
                                
                        </div>
                        <div class="control">
                                    <label class="parametros" for="buscaTacc">Sin Tacc:</label>
                                        <select id="buscaTacc" name="buscaTacc">
                                        </select>
                                    
                                </div>
                        
                        </div>
                    </div>


                    <div class="panelesBloqueInforme" style="justify-content: flex-start;">
                        <div class="titulo">Búsqueda rápida - Producto</div>

                        <div class="controlContainer">
                            <div class="barra-busqueda">
                        
                                
                                    <input type="search" id="nombreBuscaRapido" name="nombreBuscaRapido" placeholder="nombre o id ..." class="input-buscar-rapido" autocomplete="off" />
                                
                                
                                <button title="Buscar" alt="Buscar" type="button" id="botonBuscarRapido" name="botonBuscarRapido" class="boton-busca-rapido">
                                    <i class="fab fa-sistrix"></i> Buscar
                                </button>
                                
                                <input type="hidden" name="itemId" id="itemId">

                            </div>

                        </div>
                        
                    </div>
                    
                        


                        
                    <div  class="panelesBloqueInforme" style="justify-content: flex-start;" id="divBuscaRubro">
                        <div class="titulo">Búsqueda avanzada</div>

                        <div class="controlContainer">
                            <div class="control">
                                <label class="parametros" for="tipoCliente">Tipo Negocio: </label>  
                                    <select name="tipoCliente" id="tipoCliente">
                                        <option value="0">- todos -</option>
                                        <?php foreach($tipoCliente as $tpc):?>
                                        <option value="<?php echo $tpc["id"]?>"><?php echo $tpc["tipo"]?></option>
                                        <?php endforeach;?>
                                    </select>
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
                                        <option value="">- todos -</option>
                                    </select>
                            </div>

                            <div class="control">
                                <label class="parametros" for="buscaSubRubro">Sub Rubro:</label> 
                                <select id="buscaSubRubro" name="buscaSubRubro">
                                    <option value="">-todos-</option>    
                                </select>      
                            </div>

                            <div class="control">
                                <label class="parametros" for="buscaMarca">Marca:</label>
                                    <select id="buscaMarca" name="buscaMarca">
                                    <option value="">- todos -</option>
                                        <?php $articulos->muestra_marcas();?>
                                    </select>
                            </div>

                            <div class="control">
                                <label class="parametros" for="buscaProveedor">Proveedor:</label>
                                    <select id="buscaProveedor" name="buscaProveedor">
                                        
                                    </select>
                            </div>
                                    

                            <?php if(isset($usaLaboratorio)&&$usaLaboratorio=="si"):?>
                                <div class="control">
                                    <label class="parametros" for="buscaLaboratorio">Laboratorio:</label>
                                        <select id="buscaLaboratorio" name="buscaLaboratorio">                                                                        
                                        </select>
                                    
                                </div>
                            <?php endif;?>
                                    
                        </div>
                    </div>


                    <div class="panelesBloqueInformeAccion" id="divBotonBuscar">
                        <span class="centro">
                            <button  title="Buscar" alt="Buscar" type="submit" id="botonBuscar" name="botonBuscar" class="botonNuevo boton azul" value="submit">
                                <i class="fas fa-search fa-lg fa-fw"></i>  Generar
                            </button>
                            <button  title="Exportar PDF" alt="Exportar PDF" type="button" id="botonExportarPDF" name="botonExportarPDF" class="botonNuevo boton rojo" style="margin-left:10px;">
                                <i class="fas fa-file-pdf fa-lg fa-fw"></i> PDF
                            </button>
                        </span>
                    </div>

                </form>

            </div>
            
          


            <div  id="contiene-tabla"> 


              
              


                <table class="display" cellspacing="1" id="myTable" > <!-- data-page-length='50' -->

                </table>

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
    </body>
</html>