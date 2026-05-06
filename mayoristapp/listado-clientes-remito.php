<?php require_once 'sesion.inc.php';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>administraNET e-com | seleccionar Ciente</title>
     <?php require_once 'cabecera.php';?>
    <link rel='stylesheet' type='text/css' media='screen' href='_css/basic.css'   />
    <script type='text/javascript' src='_lib/jquery.simplemodal.js'></script>
<script>
   
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(function(){
         
            $("#spinner").bind("ajaxSend", function() {
                $(this).show();
            }).bind("ajaxStop", function() {
                $(this).hide();
            }).bind("ajaxError", function() {
                $(this).hide();
            });
            //activar y desactivar el boton de busqueda rapida.
            $("input[name='tipoBusqueda']").change(function(){
                var valor = $("input[name='tipoBusqueda']:checked").val();
                ///alert(valor);
                if(valor==0){
                    $('#buscarClienteR').css({display:"inline"});
                }else{
                    $('#buscarClienteR').css({display:"none"});
                }
            
            });
            
            $('#buscarClienteR').click(function(){
                var contienes = $('#myTable'),
                comoBusco = $("input[name='tipoBusqueda']:checked").val(),
                buscaRapida = $('#queCliente').val();
                if(comoBusco==0){   
                    $.ajax({
                        type: 'POST',
                        url: 'relay-clientes.php',
                        data:{
                            "ajax" : "true",
                            "queCliente" : buscaRapida
                            
                        
                        
                        },
                        success: function(response) {
                            contienes.empty();    
                            contienes.html(response);
                            $(".selCliente").click(function () {
                                //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                                var codigoCliente = $(this).attr('rel');
                                //                                        alert(codigoCliente);
                                $.ajax({
                                    type:   'POST',
                                    url:    'seleccionar-cliente.php',
                                    data:{
                                        "ajax":"true",
                                        "codCliente": codigoCliente
                                    },
                                    success: function(response){

                                        var oferta = $("#buscaOferta").val();
                                        
                                        if(oferta == ''){
//                                            location.reload();
                                            location.href = 'alta_pedido.php';
                                        }else{
                                            var idArt = $("#IDArt").val();
                                            var cantidad = $("#cant").val();
                                            var urlLink = "alta_pedido.php?buscaOferta=" + oferta + "&IDArt=" + idArt + "&cant=" + cantidad;    
                                            location.href = urlLink;
                                        }
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
                            //mostrar la modal
                            contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
                            contienes.tablesorterPager({container: $("#pager")});
                        
                            //$('#jcart-buttons').remove();
                        
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
                }else{
                    return false;
                }
            });
            $('#queCliente').focus(function(){
                //$(this).val();
                //alert('a ver si hace focus');
                $(this).val('');
            });
            $('#queCliente').keyup(function(){            
                var contienes = $('#myTable'),
                comoBusco = $("input[name='tipoBusqueda']:checked").val(),
                buscaRapida = $('#queCliente').val();
                if(comoBusco==1){   
                    $.ajax({
                        type: 'POST',
                        url: 'relay-clientes.php',
                        data:{
                            "ajax" : "true",
                            "queCliente" : buscaRapida
                        },
                        success: function(response) {
                            // Refresh the cart display after a successful Ajax request
                            //                                    alert(response);  
                            contienes.empty();    
                            contienes.html(response);
                            $(".selCliente").click(function () {
                                //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                                var codigoCliente = $(this).attr('rel');
                                //                                        alert(codigoCliente);
                                $.ajax({
                                    type:   'POST',
                                    url:    'seleccionar-cliente.php',
                                    data:{
                                        "ajax":"true",
                                        "codCliente": codigoCliente
                                    },
                                    success: function(response){

                                        var oferta = $("#buscaOferta").val();
                                        
                                        if(oferta == ''){
                                            location.href = 'alta_pedido.php';
//                                            location.reload();
                                        }else{
                                            var idArt = $("#IDArt").val();
                                            var cantidad = $("#cant").val();
                                            var urlLink = "alta_pedido.php?buscaOferta=" + oferta + "&IDArt=" + idArt + "&cant=" + cantidad;    
                                            location.href = urlLink;
                                        }
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
                            contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
                            contienes.tablesorterPager({container: $("#pager")});
                        
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
                }else{
                    return false;
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
            <div id="titulo">    
                <div class="texto"><h1> 1. Seleccione Cliente</h1></div> 
                 <?php if(isset($_REQUEST['cartel'])):?>
                   <div id="basic-modal-content" class="cartelCliente"><h1>Debe seleccionar<br/>un cliente</h1></div>
                <?php endif;?>           
            </div>
               
            <div class="buscador">    
                
                <form method="post" action="" id="formBusca">
                    
                    <div class="separador10px"></div>
                    <div class="control" style="margin-bottom: 3px;margin-top:2px;">
                        
                        <label for="tipoBusqueda"><input type="radio" value="1" name="tipoBusqueda" />&nbsp;&nbsp;Busqueda rápida &nbsp;&nbsp;</label>
                        <label for="tipoBusqueda"><input type="radio" value="0" name="tipoBusqueda" checked="checked"/>&nbsp;&nbsp;Busqueda completa</label>
                    </div>
                    <div class="separador10px clear"></div>
                    <div class="control" >
                         
                        <input type="text" id="queCliente" name="queCliente" value="Nombre/Código" />
                        
                    </div>
                    <div class="separador10px"></div>
                    <div class="control">
                         <img src="_img/buscar_2.png" title="Buscar" alt="Buscar" id="buscarClienteR" name="buscarClienteR"  style="cursor:pointer;"/>

                    </div>
                </form>
            <input type="hidden" name="buscaOferta" id="buscaOferta" value="<?php echo $_GET['buscaOferta']?>">
            <input type="hidden" name="IDArt" id="IDArt" value="<?php echo $_GET['IDArt']?>">
            <input type="hidden" name="cant" id="cant" value="<?php echo $_GET['cant']?>">    
            </div>
            
            <div id="spinner" class="spinner" style="display:none;"></div>
            <div id="contiene-tabla">
                <table class="tablesorter" cellspacing="1" id="myTable">
                </table>
        
        
<!--            <div id="pager" class="pager">-->
                <div id="pager" class="tablesorterPager">
                
                    <form>
<!--                        <img src="../addons/pager/icons/first.png" class="first"/>-->
                        <input type="button" value="<<" class="first">
                        <input type="button" value="<" class="prev">
<!--                        <img src="../addons/pager/icons/prev.png" class="prev"/>-->
                        <input type="text" class="pagedisplay"/>
                        <input type="button" value=">" class="next">
                        <input type="button" value=">>" class="last">
<!--                        <img src="../addons/pager/icons/next.png" class="next"/>-->
<!--                        <img src="../addons/pager/icons/last.png" class="last"/>-->
                        <select class="pagesize">
                                <option selected="selected"  value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option  value="40">40</option>
                        </select>
                </form>
                </div>
            </div>
           
            </div>

 
        <?php require_once 'footer.php';?>   
    
    </div>
     <?php if(isset($_REQUEST['cartel'])):?>
        <script>
            $(document).ready(function(){
        //        $('#basic-modal-content').empty();
        //        $('#basic-modal-content').html(response);
                //$('#basic-modal-content').maxHeight = 200,
                $('#basic-modal-content').modal();
            });
        </script>
    <?php endif;?>
    </body>
</html>

