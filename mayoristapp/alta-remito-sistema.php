<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file demonstrates a basic store setup

// If your page calls session_start() be sure to include jcart.php first
session_start();
$caminoDispo = $_SESSION['caminoDisp'];
$tipoBusca= $_SESSION["tipo_busqueda"];
if (isset($_SESSION["jcart"])){
    unset($_SESSION["jcart"]);
   
}
 $_SESSION["totalCarrito"]=0;
session_write_close();

require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';
$iconoDisabled = 0;
$comprobante="REM sistema";
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
if(!isset($_SESSION['cliente'])){
    header('Location:listado-clientes.php?frm=1&cartel=1');
}

?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
            <title> Remito Sistema | administraNET e-com </title>
            <?php require_once 'cabecera-articulo.php';?>
           
    </head>
    <body>
       <div id="wrapper">
        
    <?php  require_once $barra; ?>        
        <div id="content">            
             <div id="cartelItemAgregado">Item agregado! <i class="fa fa-check-circle fa-1x"></i></div>
             
             <div class="buscador">
<!--                 <div id="titulo">Productos</div>-->
                  <form method="post" action="" id="formBusca">
                      <div id="divBuscaArticulo">
<!--                        <div class="control">
                            <select  name="claseBusqueda" id="claseBusqueda">
                                    <option value="0" <?php //if($tipoBusca==1){ echo 'selected="selected"';}?>>Comienza con</option>
                                    <option value="1" <?php //if($tipoBusca==2){ echo 'selected="selected"';}?>>Incluye texto</option>
                            </select>
                            <label for="claseBusqueda"><input type=checkbox value="1" name="claseBusqueda" id="claseBusqueda"><i class="fa fa-check-square fa-lg"></i>Incluye texto</label>
                            <label for="DispDesktop"><input type="radio" id="DispDesktop" name="dispositivo" value="pc"> <i class="fa fa-desktop fa-lg"></i> Pc</label>
                            <label for="DispTablet"><input type="radio" id="DispTablet" name="dispositivo" value="tablet"> <i class="fa fa-tablet fa-lg"></i> Tablet/Movil</label>
                        </div>-->
                        <div class="control" id="divBarraArticulo">
                            
                           <label for="queArticulo">Producto </label>
                            <input type="search" id="queArticulo" name="queArticulo" placeholder="nombre o código..." />
                            <input type="hidden" id="queArticuloId" name="queArticuloId" value=""/>
                        </div>
                        <div class="control">
<!--                            <button  title="Buscar" alt="Buscar" type="button" id="buscarArticuloR" name="buscarArticuloR" class="botonNuevo chicoG azul">
                                <i class="fa fa-search fa-1x" ></i>
                            </button>-->
                            <button  title="Buscar" alt="Buscar" type="submit" id="buscarArticuloR" name="buscarArticuloR" class="botonNuevo chicoG azul">
                                <i class="fab fa-sistrix"></i>
                            </button>
                        </div>
                    <div><label>Buscar por:</label></div>
                     <div class="control">
<!--                         <label for="queCampo">Campo: </label>
                            <select name="queCampo" id="queCampo">
                                <option value="codigo">Código</option>
                                <option value="nombre" selected="selected">Nombre</option>    
                            </select>-->
                          <label for="queCampoNombre" name="queCampoLabel"><i class="fa fa-check-square fa-lg" ></i> Nombre </label>
                            <input type="radio" value="nombre" name="queCampo" id="queCampoNombre" checked="checked">
                            
                            <label for="queCampoCodigo" name="queCampoLabel"><i class="fa fa-square fa-lg" ></i> Código </label>
                            <input type="radio" value="codigo" name="queCampo" id="queCampoCodigo">
                            
                             
                         </div>
                    </div>
                    
                 <div  class="control" id="divBuscaRubro">
                     
                   <div class="separador10px clear"></div>
                      <div class="control" ><label>Filtrar por:</label></div>
                      <div class="separador10px clear"></div>
                        <div class="control" >
                             <label for="buscaPromo">
                             <input type="checkbox" name="buscaPromo" id="buscaPromo" value="si"> <strong>Promociones</strong></label>
                        </div>
                     <div class="control" >
                             <label for="buscaMisConsumos">
                             <input type="checkbox" name="buscaMisConsumos" id="buscaMisConsumos" value="si"> <strong>Mis consumos</strong></label>
                        </div>
                      <div class="separador10px clear"></div>
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
                                    <option value=""> - todos -</option>
                                   
                                </select>
                            </label>
                        </div>

                       <div class="control">
                                <label for="buscaSubRubro">Sub Rubro: 
                                <select id="buscaSubRubro" name="buscaSubRubro">
                                    <option value="">- todos -</option>    
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
                            <label for="buscaModelo">Modelo:
                                <select id="buscaModelo" name="buscaModelo">
                                    <option value="">- todos -</option>    
                                </select>
                            </label>
                        </div>

                    </div>


                </form>
             </div>
                    
                    <?php if(isset($_REQUEST['cartel'])):?>
                    
                        <?php if($_REQUEST['cartel']=='errorTalonario'):?>
                            <div id="basic-modal-content" >
                                <!--<h1>Nro de Comprobante Existente.</h1>Los datos de Suc y Nro estan repetidos-->
                                <div id="alertas-formulario" class="alerta-error">
                                    <!--<button class="close" type="button" data-dismiss="alert">×</button>-->
                                   
                                    <strong>
                                        <i class="fa fa-warning"></i> 
                                        Atención! </strong>
                                    <span class="texto-alerta"> N° de Comprobante Existente. Los datos de Suc y Nro están repetidos</span>
                                </div>
                            </div>
                        <?php endif;?>
                            
                        <?php if($_REQUEST['cartel']=='0'):?>
                            <?php
                                $textoCartel = '<div id="alertas-formulario" class="alerta-exito">'
                                        . 'Se ha generado:<br>';
                                if(isset($_GET['rem'])){ $textoCartel .='Remito: <strong>'.$_GET['rem'].' <i class="fa fa-check-circle"></i></strong><br>';}
                                if(isset($_GET['ped'])){ $textoCartel .='Pedido: <strong>'.$_GET['ped'].' <i class="fa fa-check-circle"></i></strong><br>';}
                                $textoCartel .='<div style="text-align:center">'
                                        . '<button class="botonNuevo chico gris" id="cerrarModalD"><i class="fa fa-times"></i></button>'
                                        . '</div></div>';
                             ?>
                            <div id="basic-modal-content" class="cartelCliente"><?php echo $textoCartel;?>
                                
                            </div>
                        <?php endif;?>
                    <?php else:?>
                        <div id="basic-modal-content">
                            
                        </div>
                    <?php endif;?> 
                    <div id="contiene-tabla-comprobante">
                            <table class="display compact" id="myTable">
                             <tr><td class='vacio'>No se encontaron resultados </td></tr>   
                            </table>
                            
                    </div>         
                    <div id="sidebar">
                        <div id="jcart"><?php $jcart->display_cartRemTal();?></div>
                    </div>
                     <div id="spinner" class="spinner" >
                       <img src="_img/logo-administranet-ecommerce.png"/>   
                       <div class="texto">Procesando...</div>
                    </div>
                        
                        
                        <!--<div style="width:500px; height: 20px;float:right;margin-left: 510px" class="subTitulo">Carrito</div>-->
                                                    
                        <div id="modal-stock-articulo">
                            <div id="renglonlote">
                                <form method="post" action="" class="jcart">
                                    <input type="hidden" name="jcartToken" value="<?php echo $_SESSION['jcartToken']; ?>" />
                                    <input type="hidden" name="my-item-price" value="" />
                                    <input type="hidden" name="my-item-tipoIva" value="" />
                                    <input type="hidden" name="my-item-alicuota" value="" />
                                    <input type="hidden" name="my-item-impIva" value="" />
                                    <input type="hidden" name="my-item-iva" value="" />
                                    <input type="hidden" name="my-item-neto" value="" />
                                    <input type="hidden" name="my-item-descPor" value="" />
                                    <input type="hidden" name="my-item-impInterno" value="" />
                                    <input type="hidden" name="my-item-url" value="" />
                                    <input type="hidden" name="my-item-promo" value="" />
                                    <input type="hidden" name="my-item-promoCant" value="" />
                                    <input type="hidden" name="my-item-promoPorc" value="" />
                                    <input type="hidden" name="my-item-promoTipo" value="" />
                                    <input type="hidden" name="my-item-impInternoTasa" value="" />
                                    <input type="hidden" name="my-item-lote" value="" />
                                    <input type="hidden" name="jcart-fecha-rem-clon" value="">
                                    <input type="hidden" name="jcart-suc-clon" value="">   
                                    <input type="hidden" name="jcart-nro-rem-clon" value="">
                                    <input type="hidden" name="permiso-sin-stock" id="permiso-sin-stock" value="<?php echo $_SESSION['venta_sin_stock'];?>">
                                    <input type="hidden" name="my-item-name" value="" />
                                    <input type="hidden" name="my-item-idDeposito" value="<?php echo $objVendedor->id_deposito; ?>">
                                    <input type="hidden" name="my-item-entregado" value="">
                                    <input type="hidden" name="my-item-id" value="" readonly="readonly" />
                                    <input type="hidden" name="my-item-qty" value="<?php if(isset($_REQUEST['cant'])){echo $_REQUEST['cant'];} ?>" size="3" />
                                    <input type="hidden" name="my-item-saldo" value=""> 
                                    <div id="campoDato" name="titulo">
                                        <i class="fa fa-gear fa-lg"></i> <span name="my-item-name-label"></span> 
                                    </div>
                                    <div class="contiene-renglon">
                                        <div>Cod: </div><div class="span-valor" name="my-item-id-label"></div>
                                        <div id="span-separador"></div>
                                        <div>Cant: </div><div class="span-valor" name="my-item-qty-label"></div>
                                        <div id="span-separador"></div>
                                        <div>Saldo: </div><div class="span-valor" name="my-item-saldo-label"></div>
                                        <div id="span-separador"></div>
                                        <div>Precio: </div><div class="span-valor" style="color:#2472a4;" name="my-item-price-label"></div>
                                        
                                    </div>
                                    <div class="contiene-renglon">
                                        

                                            <div id="artSinStock"><i class="fa fa-sign-in fa-lg"></i> Enviar artículo 
                                                <strong>sin stock</strong> a: 
                                            </div>
                                            <div id="artConStock"><i class="fa fa-sign-in fa-lg"></i> Enviar a : </div>
                                            <div id="artSinPermiso"><strong> No tiene permiso para entregar mercadería</strong></div> 
                                            <label id="envRemitoLabel" for="envRemito"> 
                                                <input type="radio" name="entregarItem" value="0" id="envRemito" checked="checked" ><strong> Remito </strong>
                                            </label>
<!--                                            <label id="envPedidoLabel" for="envPedido"> 
                                                <input type="radio" name="entregarItem" value="1" id="envPedido"><strong> Pedido </strong>
                                            </label>-->
                                            
                                            <label id="envNoLabel" for="envNo"> 
                                                <input type="radio" name="entregarItem" value="2" id="envNo"><strong> Sin stock </strong>
                                            </label>
                                    </div>
                                   
                                        <div id="sinStock" name="sinStock"></div>
                                   

                                    <div id="selLote" name="selLote" ></div>
                                    <div id="alertas" class="alerta-error">
                                        <button class="close" type="button" data-dismiss="alert">×</button>
                                        <strong><i class="fa fa-warning fa-lg"></i> Atención! </strong> <span class="texto-alerta"></span>
                                    </div>
                                    <div id="campoDato" name="boton">   
                                        <button class="botonNuevo grande azul" id="aceptaArticulo" ><i class="fa fa-plus fa-lg"></i> agregar</button>
                                        <button class="botonNuevo grande gris" id="cerrarModal" ><i class="fa fa-times fa-lg"></i> cancelar</button>
                                    </div>
                                    
                                </form>
                                
                            </div>
                        </div>

                       

<!--				<p><small>Having trouble? <a href="jcart/server-test.php">Test your server settings.</a></small></p>-->

                        <?php  if(isset($_SESSION["sel_factura"])):?>
                                    <script>
                                        $(document).ready(function() {
                                             $('.buscador').hide();
                                            $('#queArticulo').val('');    
                                            $('#queArticuloId').val('1');
                                            $('#buscarArticuloR').click();
//                                            var form =$('.jcart');   
//                                            var qty = form.find('[name="my-item-qty"]');
//                                            qty.val('1');
                                     });       
                                </script>
                        <?php  endif;?>
                        <?php if(isset($_REQUEST['cartel'])):?>
                            <script>
                                $(document).ready(function(){
                            //        $('#basic-modal-content').empty();
                            //        $('#basic-modal-content').html(response);
                                    //$('#basic-modal-content').maxHeight = 200,
                                    $('#basic-modal-content').modal(
                                    {
                                        minWidth:300,
                                        minHeight:100,
                                        maxHeight:200,
                                        
                                        close: false,
                                        onShow: function(){

                                                $('#cerrarModalD').on("click",function(e){
                                                    e.preventDefault();
                                                   $.modal.close(); 
                                                });
                                            }
                                    });
                                });
                            </script>
                        <?php endif;?>
                            <script>
                                 $(document).ready(function(){
                                     //$('#basic-modal-content').modal();
                                     
                                     $('#queArticulo').focus(function(){
                                        //$(this).val();
                                        //alert('a ver si hace focus');
                                        //$(this).val('');
                                    });
                                 });
                                 
                            </script>
                </div>
           <?php //require_once 'footer.php';?> 
        </div>
        
    </body>
</html>