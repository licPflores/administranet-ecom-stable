<?php 
    // si traigo un rubro....lo meto en la sesion... 
    session_start();
    $caminoDispo = $_SESSION['caminoDisp'];
    session_write_close();
    require_once $caminoDispo.'jcart/jcart.php';
    require_once 'sesion.inc.php';
    require_once 'ajax-articulos.php';

    $tipoBusca= $_SESSION["tipo_busqueda"];   
    
    $buscaRubro = null;
    $buscaSubRubro = null;
    $queArticulo = null;
    $claseLista = null;
    
//    echo "sesion:: <pre>";
//      print_r($_SESSION);
//      echo "</pre>";
    if(isset($_SESSION["buscaRubro"])){
        $buscaRubro = $_SESSION["buscaRubro"];
    }
    if(isset($_SESSION['buscaSubRubro'])){
        $buscaSubRubro = $_SESSION['buscaSubRubro'];
    }
    if(isset($_SESSION["queArticulo"])){
        $queArticulo = $_SESSION["queArticulo"];
    }
    if(isset($_SESSION["claseLista"])){
        $claseLista = $_SESSION["claseLista"];
    }
    
    
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas        = 1;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 1;
$iconoDisabled  = 1;
$usaZoom=0;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Catálogo de productos | administraNET </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
  
<script>
 $(document).ready(function(){

     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
     
    $('#formBusca').on("submit",function(){
         event.preventDefault();
         console.log("formulario detectado");
     });
     $("input[name='claseLista']").hide();
     /*
      * Lista de Precios
      */
     $('#buscarArticulo').click(function(){
        var contienes       = $('#myTable'),           
            claseBusqueda = 1,
            buscaRapida = $('#queArticulo').val(),
            categoria      = $('#buscaCategoria').val(),
            rubro      = $('#buscaRubro').val(),
            subrubro   = $('#buscaSubRubro').val(),
            marca =$('#buscaMarca').val(),
            modelo=$('#buscaModelo').val(),
            misConsumos=$('#buscaMisConsumos'),
            promociones=$('#buscaPromo'),
            consumo=0,
            promo=0,
            queCliente      = "cliente",
            tipoCliente     ="catalogo",
            claseLista      =$("input[name='claseLista']:checked").val();
            if(claseLista==undefined){
                alert('Debe seleccionar Lista o Galeria!');
                return;
            }
            // mis consumos
            if(misConsumos.prop("checked")===true){
                consumo=1;
            }
            // mis promociones
            if(promociones.prop("checked")===true){
                promo=1;
            }
//            alert('art: '+$('#queArticulo').val()+ ' rub: '+ $('#buscaRubro').val()+' subrub: '+$('#buscaSubRubro').val());
            $.ajax({
                type: 'POST',
                url: 'relay-art.php',
                data:{
                    "ajax" : "true",
                    "tipoCliente"       : tipoCliente,
                    "queArticulo"        : buscaRapida,
                    "queArticuloCat"     : buscaRapida,
                    "buscarArticulo"    : "buscarArticulo",
                    "claseBusca"        : claseBusqueda,
                    "categoria"         : categoria,
                    "rubro"             : rubro,
                    "subrubro"          : subrubro,
                    "marca"             : marca,
                    "modelo"            : modelo,                    
                    "consumo"           : consumo,
                    "promo"             : promo,                  
                    "claseLista"        : claseLista,
                    "queCliente"        : queCliente
                },
                success: function(response) {
                        // Refresh the cart display after a successful Ajax request
                       console.log("vuelta:=>"+response);
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
                                    "ordering": false
                                    });
                            
                            $("#spinner").hide();
                            
//                            contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
//                            contienes.tablesorterPager({container: $("#pager")});

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
    * Busco rubros
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
                    <div class="control" id="divTipoBusqueda">                                
                       
                            
                           <label for="queArticulo">Producto </label>
                            <input type="search" id="queArticulo" name="queArticulo" placeholder="nombre o código..." />
                            <input type="hidden" id="queArticuloId" name="queArticuloId" value=""/>
                        <button  title="Buscar" alt="Buscar" type="submit" id="buscarArticulo" name="buscarArticulo" class="botonNuevo chicoG azul">
                                <i class="fab fa-sistrix"></i>
                        </button>
                        
                        
                    </div>
                     <div class="control" id="divTipoLista">
                         <label for="buscaLista" class="iconoAzul">
                             <input type="radio" value="lista" checked="checked" name="claseLista" id="buscaLista" />
                            <i class="fa fa-th-list fa-lg"></i> Lista
                        </label>
                         <label for="buscaGaleria">
                            <input type="radio" value="galeria" name="claseLista" id="buscaGaleria" />
                            <i class="fa fa-th fa-lg"></i> Galeria
                        </label>
                     </div>    
                                    
                      
                       
                    
                   
                    <div  class="control" id="divBuscaRubro">
                        
                        <div class="control" >
                             <label for="buscaPromo">
                             <input type="checkbox" name="buscaPromo" id="buscaPromo" value="si"> <strong>Promociones</strong></label>
                        </div>
                     <div class="control" >
                             <label for="buscaMisConsumos">
                             <input type="checkbox" name="buscaMisConsumos" id="buscaMisConsumos" value="si"> <strong>Mis consumos</strong></label>
                        </div>
                        <div class="control" >
                            <label for="buscaRubro">Categoría: 
                                <select id="buscaCategoria" name="buscaCategoria">
                                    <option value="">- todas -</option>
                                 <?php $articulos->muestra_categorias();?>  
                                </select>
                            </label>
                        </div>
                        <div class="control" >
                            <label for="buscaRubro">Rubro: 
                                <select id="buscaRubro" name="buscaRubro">
                                    <option value="">- todos -</option>
                                 
                                </select>
                            </label>
                        </div>
                        <div class="control">
                                <label for="buscaSubRubro">Sub Rubro: 
                                <select id="buscaSubRubro" name="buscaSubRubro">
                                    <option value="">-todos-</option>    
                                </select>
                                </label>
                       </div>
                        <div class="control">

                            <label for="buscaMarca">Marca:
                                <select id="buscaMarca" name="buscaMarca">
                                    <option value="">- todas -</option> 
                                     <?php $articulos->muestra_marcas();?>
                                </select>
                            </label>
                        </div>
                         <div class="control">

                            <label for="buscaMarca">Laboratorio:
                                <select id="buscaLaboratorio" name="buscaLaboratorio">
                                    <option value="">- todas -</option> 
                                     <?php $articulos->muestra_marcas();?>
                                </select>
                            </label>
                        </div>
                    </div>
                    
                    
                    
               </form>
            </div>
            
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla"  > 
                <h1>Catálogo de productos</h1>
                <table class="display compact" cellspacing="1" id="myTable" data-page-length='25'>
                 </table>

            </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     <div id="basic-modal-content" > </div>
    </body>
    
</html>