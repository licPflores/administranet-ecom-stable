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
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <title>administraNET mayoristapp |Promociones al <?php echo date("d/m/Y");?></title>
   
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
 $(document).ready(function(){
//     $.fn.dataTable.TableTools.defaults.aButtons = [ "xls", 
//        {
//            "sExtends": "pdf",
//            "sPdfOrientation": "landscape",
//            "sPdfMessage": "lista de precios"
//        }
//    ];
// aca atacch a los eventos del spinner funcionando.
      
    //funcion para recuperar el logo en base64
    

     
     /*
      * Lista de Precios
      */
     $('#formBusca').on("submit",function(){
         event.preventDefault();
         console.log("formulario detectado");
     });
     
     
     $('#botonBuscar').click(function(){
         
        var contienes       = $('#myTable'),
            categoria       = $('#buscaCategoria').val(),
            rubro           = $('#buscaRubro').val(),
            subrubro        = $('#buscaSubRubro').val(),
            marca           = $('#buscaMarca').val(),
            idTipoCliente   = $('#tipoCliente').val(),
            laboratorio     = $('#buscaLaboratorio'),
            ivaIncluido     = $('#buscaTipoIva').val(),
            imagenProducto = $('#imagenProducto').val();
            misConsumos     =0,
            queAccion       = "promociones",
            textoNegocio    ="",
            textoCategoria  ="",
            textoRubro      ="",
            textoSubRubro   ="",
            textoLab        ="",
            queLaboratorio  =null,
            textoVigencia   ="Validez de 48hs. hasta el <?php echo date('d/m/Y', strtotime("+2 days"));?>\n",
            queCliente      = $('#cliente').val(),
            tipoCliente     ='si';
            //console.log($("#buscaMiConsumo:checked").val());
            
            if($("#buscaMiConsumo:checked").val()!==undefined){
                
                misConsumos=1;
            }
            if(laboratorio!==undefined){
                queLaboratorio=laboratorio.val(); // indice
                textoLab = " Laboratorio: "+$('#buscaLaboratorio option:selected').text()+"\n";
            }
            
            if(queCliente===undefined){
                queCliente="cliente";
            }
            if(idTipoCliente!==0){
                textoNegocio="Tipo Negocio: "+$('#tipoCliente option:selected').text()+"\n";
            }
            $("#spinner").show();
            $.ajax({
                type: 'POST',
                url: 'relay-art.php',
                data:{
                    "ajax" : "true",
                    "tipoCliente"   : tipoCliente,
                    "buscarProducto": 1,
                    "categoria"     : categoria,    
                    "rubro"         : rubro,
                    "subrubro"      : subrubro,
                    "marca"         : marca,
                    "idTipoCliente" : idTipoCliente,
                    "queCliente"    : queCliente,
                    "ivaIncluido"   : ivaIncluido,
                    "misConsumos"   : misConsumos,
                    "laboratorio"   : queLaboratorio,
                    "imagenProducto": imagenProducto,
                    "queAccion" : queAccion
                },
                success: function(response) {
                    // console.log(response)
                        // Refresh the cart display after a successful Ajax request
                                                //console.log(textoLab);
                        //console.log(response);
                        var titulo=$("#tituloListaP").text(),
                            leyendaIva='',
                            fotoLogo=$("#imgLogo").attr("base64"),
                            nombreLogo=$("#imgLogo").attr("title");
                    if(ivaIncluido=='No'){
                        leyendaIva ='Los precios publicados NO incluyen IVA';
                    }
                    if(ivaIncluido=='Si'){
                        leyendaIva ='Los precios publicados incluyen IVA';
                    }

//                        console.log($(".cartelAdvertencia").text());
//                        console.log($("#imgLogo").attr("base64"));
//                        console.log($("#imgLogo").attr("title"));
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
                               searching:true,
                               responsive:false,
                                
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
                                        },
                                        "order": [],
                                        "dom": 'lBfrtip',
                                        buttons: [
                                            {
                                                extend:'excel',
                                                messageTop:textoLab+" "+textoNegocio+" "+ leyendaIva
                                            }, 
                                            {
                                                extend: 'pdfHtml5',
                                                orientation: 'landscape',
                                                customize: function(doc) {
                                                    var data = contienes.DataTable().rows().data();
                                                    // console.warn('quetrae doc=>>>>>>>>>>>>>>>>>>');
                                                    // console.log(doc);
                                                    if (!doc.content || !Array.isArray(doc.content) || doc.content.length < 2 || !doc.content[2].table || !Array.isArray(doc.content[2].table.body)) {
                                                            console.error('Error: Estructura del documento PDF no válida para la personalización.');
                                                            return;
                                                        }

                                                    for (var i = 0; i < data.length; i++) {
                                                        var imgHtml = data[i][3]; // Columna 4 (índice 3) contiene la imagen
                                                        var imgElement = $(imgHtml).filter('.fotoProducto')[0];

                                                        // if (imgElement && imgElement.src.startsWith('data:image')) {
                                                        if (imgElement && imgElement.src!='') {
                                                            var imgData = imgElement.src;
                                                            if (doc.content[2].table.body[i + 1]) { // i + 1 para ajustar al índice de la tabla en PDF
                                                                doc.content[2].table.body[i + 1][3] = {
                                                                    image: imgData,
                                                                    width: 50
                                                                };
                                                            }
                                                        }
                                                    }

                                                    doc.footer = function(currentPage, pageCount) {
                                                        return {
                                                            text: leyendaIva,
                                                            alignment: 'center'
                                                        };
                                                    };
                                                },
                                                exportOptions: {
                                                    stripHtml: false,
                                                    columns: ':visible',
                                                    search: 'applied',
                                                    order: 'applied'
                                                },
                                                messageTop: textoLab + " " + textoNegocio + " " + leyendaIva + "\n" + textoVigencia
                                            }
                                                                        
                                        ]

                                    });
                                    

                            
                            $("#spinner").hide();

                        //$('#jcart-buttons').remove();
                        }// el cierre del else del        
                },
                // See: http://www.maheshchari.com/jquery-ajax-error-handling/
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
               
                <form id="formBusca" name="formBusca" method="POST" action="">

                    <div class='panelesBloqueInforme' style="justify-content: flex-start;">
                    
                        <div class="control">
                            <label class="parametros" for="tipoCliente">Tipo Negocio: </label>  
                                <select name="tipoCliente" id="tipoCliente">
                                    <option value="0">- todos -</option>
                                    <?php foreach($tipoCliente as $tpc):?>
                                    <option value="<?php echo $tpc["id"]?>"><?php echo $tpc["tipo"]?></option>
                                    <?php endforeach;?>
                                </select>
                           
                        </div>

                        <?php if($_SESSION['tipousuario']=='vendedor'):?>
                        <div class="separador25px"></div>                    
                        <div class="control">
                            <label class="parametros" for="cliente">Cliente:</label>  
                                <select name="cliente" id="cliente">
                                    
                                    <option value="todos" selected="selected">-Todos-</option>

                                    <?php if(is_object($objCliente)):?>
                                    <option value="cliente">Cliente Seleccionado</option>
                                     <?php endif;?>

                                </select>
                            
                        </div>   

                        <?php endif;?>
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
                    </div>


                    
                    <div  class='panelesBloqueInforme' style="justify-content: flex-start;" id="divBuscaRubro">


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


                <h1 id="tituloListaP">Promociones</h1>
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