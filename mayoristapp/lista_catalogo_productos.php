<?php 
    //* LISTA tipo Catalogo 
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
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <title>administraNET mayoristapp | catalogo de productos <?php echo date("d/m/Y");?></title>
   
     <?php require_once 'cabecera.php';?>
    <?php 
    
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
        $tipoCliente[] = $tp;
    }
    // echo '<pre>';    ESTE ARRAY VA PARA TIPO NEGOCIO
    // print_r($tipoCliente);
    // echo '</pre>';    
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();

    ?>
   
<script>
    // $("#spinner").bind("ajaxSend", function() {
    //         $(this).show();
    //     }).bind("ajaxStop", function() {
    //         $(this).hide();
    //     }).bind("ajaxError", function() {
    //         $(this).hide();
    //     });
 $(document).ready(async function(){
    inicioAutoCompletar("catalogoProducto");
     
     
     $('#formBusca').on("submit",function(event){
         event.preventDefault();
         console.log("formulario detectado");
     });
     
     
 
    $('#botonBuscar').click(async function() {
        
        await buscarProductosCatalogo();
    
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

    /*
    * Busco rubros
     */
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
    
 
    
    
     lista_laboratorio();   
     lista_proveedores();
     lista_tacc();
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


            <div class="paneles filtroInformes">  
            <h1 id="tituloListaP">Catálogo de productos al <?php echo date("d/m/Y"); ?></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">
                <div class='panelesBloqueInforme' style="justify-content: flex-start;">
                        <div class="control" >
                            <label class="parametros" for="imagenProducto">Imagen:</label> 
                                    <select id="imagenProducto" name="imagenProducto">
                                        <option selected="selected" value="Si">Si</option>
                                        <!-- <option  value="No">No</option> -->
                                    </select>
                               
                        </div>
                        

                        
                    
                        

                        <div class="control" >
                            <label class="parametros" for="sizeFoto">Tamaño:</label> 
                                    <select id="sizeFoto" name="sizeFoto">
                                        <option selected="selected" value="chica">Chica</option>
                                        <option value="mediana">Mediana</option>
                                        <option value="grande">Grande</option>
                                      
                                    </select>
                               
                        </div>
                        <div class="control">
                                    <label class="parametros" for="ordenarPor">Ordenar Por:</label>
                                        <select id="ordenarPor" name="ordenarPor">
                                            <option value="nombre" selected="selected">Nombre</option> 
                                            <option value="sistema">Código Sistema</option> 
                                            <option value="manual">Código Manual</option> 
                                        </select>
                                    
                                </div>
                                <div class="control">
                                    <label class="parametros" for="direccionOrden">Tipo Orden:</label>
                                        <select id="direccionOrden" name="direccionOrden">
                                            <option value="ASC">menor a mayor</option> 
                                            <option value="DESC">mayor a menor</option> 
                                        </select>
                                    
                                </div>
                    </div>
                <div class='panelesBloqueInforme' style="justify-content: flex-start;">
                    <div class="titulo">Búsqueda rápida</div>
                    

                    <label for="nombreBuscaRapido">Producto: </label>

                        <input type="search" id="nombreBuscaRapido" name="nombreBuscaRapido" placeholder="nombre o id ..." class="input-buscar-rapido" autocomplete="off" />
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscarRapido" name="botonBuscarRapido" class="boton-busca-rapido">
                            <i class="fab fa-sistrix"></i> Buscar
                        </button>
                        <input type="hidden" name="itemId" id="itemId">
                    

                </div>
                    

                    
                    <div  class='panelesBloqueInforme' style="justify-content: flex-start;" id="divBuscaRubro">
                    <div class="titulo">Búsqueda avanzada</div>

                                <div class="control" >
                                    <label class="parametros" for="buscaRubro">Categoría: </label> 
                                        <select id="buscaCategoria" name="buscaCategoria">
                                            <option value="">- todas -</option>
                                        <?php $articulos->muestra_categorias();?>  
                                        </select>
                                   
                                </div>
                                <div class="control" >
                                    <label for="buscaRubro">Rubro:</label> 
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
                                            <option value="">- todas -</option> 
                                            <?php $articulos->muestra_marcas();?>
                                        </select>
                                    
                                </div>
                                <?php if(isset($usaLaboratorio)&&$usaLaboratorio=="si"):?>
                                <div class="control">
                                    <label class="parametros" for="buscaLaboratorio">Laboratorio:</label>
                                        <select id="buscaLaboratorio" name="buscaLaboratorio">                                                                        
                                        </select>
                                    
                                </div>
                                <?php endif;?>
                                <div class="control">
                                    <label class="parametros" for="buscaProveedor">Proveedor:</label>
                                        <select id="buscaProveedor" name="buscaProveedor">
                                           
                                        </select>
                                    
                                </div>
                                <div class="control">
                                   <label class="parametros" for="buscaTacc">Sin Tacc:</label>
                                       <select id="buscaTacc" name="buscaTacc">
                                       </select>
                                   
                               </div>

                    </div>
                    


                    <div class="panelesBloqueInformeAccion" id="divBotonBuscar">
                                    <span class="centro">
                        <button  title="Buscar" alt="Buscar" type="submit" id="botonBuscar" name="botonBuscar" class="botonNuevo boton azul" value="submit">
                         <i class="fas fa-search fa-lg fa-fw"></i>  Generar
                        </button>            
                                </span>      
                    </div>

                    
                </form>




            </div>
            
            <!-- <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->


            <div  id="contiene-tabla"> 


               
                <!-- <h3>Validez de 48hs. hasta el <?php //echo date('d/m/Y', strtotime("+2 days"));?></h3> -->


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