<?php
ini_set("display_errors", 1);

session_start();
$caminoDispo = $_SESSION['caminoDisp'];
//if (isset($_SESSION["jcart"])){
//    unset($_SESSION["jcart"]);
//   
//}
$_SESSION["totalCarrito"] = 0;

session_write_close();
$soyMovil = "No";
if ($caminoDispo != "") {
    $soyMovil = "Si";
}


$comprobante = "PED";

require_once 'jcart/jcart.php';
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';

$iconoDisabled = 0;
$tipoBusca = $_SESSION["tipo_busqueda"];
$usoZona = $_SESSION["activ_logistica"];
$arrProductos = $_SESSION["productoRapido"];

if (!isset($_SESSION['cliente'])) {
    header('Location:listado-clientes.php?frm=0&cartel=1');
}
//
// echo "<pre>";
// echo print_r($_SESSION);
// echo "</pre>";
if (is_object($jcart)) {
    $_SESSION["totalCarrito"] = $jcart->totalCarrito();
}
// bulto cerrado 
if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
    $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
}

// display 
if ($_SESSION['utiliza_display'] == "Si") {
    $usaDisplay = $_SESSION['utiliza_display'];
}
// echo "<pre>";
// echo $usoBultoCerrado;
// echo "<br>";
// echo $usaDisplay;
// echo "</pre>";


?>
<!DOCTYPE HTML>
<html lang="es">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <title> Nuevo Pedido | administraNET e-com </title>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script> -->
    <?php require_once 'cabecera-articulo.php'; ?>
    


</head>

<body>

    <div id="wrapper">
        <input type="hidden" id="usoZona" name="usoZona" value="<?php echo $usoZona; ?>">
        <?php require_once $barra; ?>


        <div id="paneles content">
            <div class="tituloComprobante">
                <span class="textoTituloComprobante"><i class="fas fa-shipping-fast fa-lg"></i> Pedido</span>

                <span>
                    <button type="button" class="botonAccionPrimario" onclick="mostrarBuscarProductos()">Productos</button>
                    <!-- </span>
            <span > -->

                    <button type="button" class="botonAccionPrimario" onclick="mostrarCarrito()">
                        Carrito
                        <?php if (isset($_SESSION["totalCarrito"])) : ?>
                            <span class="fa-stack">
                                <i class="fas fa-circle fa-stack-2x" style="color:Tomato"></i>
                                <strong id="totalCarrito" class="fa-stack-1x fa-inverse"><?php echo $_SESSION["totalCarrito"]; ?></strong>
                            </span>
                        <?php endif; ?>
                    </button>
                </span>
            </div>
            <!-- <div id="cartelItemAgregado">Item agregado! <i class="fa fa-check-circle fa-1x"></i></div> -->

            <div class="paneles" id="panelBuscaRapido">

                <form method="get" action="" id="formBusca">

                    <!-- <div id="divBuscaArticulo" > -->
                    
                    <div class="barra-busqueda">

                        <!-- <div class="control" id="divBarraArticulo"> -->

                        <!-- <label for="queArticulo">Producto </label> -->
                        <!-- <input type="search" id="queArticulo" name="queArticulo" onsearch="" autocomplete="off" class="input-buscar-rapido"  placeholder="nombre o código..." />
                        
                        <button title="Buscar" alt="Buscar" type="submit" id="buscarArticuloR" name="buscarArticuloR"  class="boton-busca-rapido">
                                <i class="fab fa-sistrix"></i> Buscar
                        </button> -->

                         
                        <input type="search" id="nombreBuscaRapido" name="nombreBuscaRapido" placeholder="Producto nombre o id ..." class="input-buscar-rapido" autocomplete="off" />
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscarRapido" name="botonBuscarRapido" class="boton-busca-rapido">
                            <i class="fab fa-sistrix"></i> Buscar
                        </button>
                        <input type="hidden" name="itemId" id="itemId">

                    </div>
                    <h3 class="paneles-titulo">Filtros: <i id="filtroAvanzado" class="fas fa-angle-down fa-lg fa-fw"></i></h3>
                    <div class="div-busqueda-avanzada-producto ocultar" id="divBuscaRubro">
                        <!-- <h4>Filtros</h4> -->
                       
                            <div class="div-opciones-busqueda">
                                <label for="buscaPromo">
                                    <input type="checkbox" name="buscaPromo" id="buscaPromo" value="si"> Promociones
                                </label>
                                <label for="buscaMisConsumos">
                                    <input type="checkbox" name="buscaMisConsumos" id="buscaMisConsumos" value="si"> Mis consumos
                                </label>
                            </div>
                            

                            <div class="div-opciones-busqueda">
                                <label for="buscaRubro">Categoría:
                                    <select id="buscaCategoria" name="buscaCategoria">
                                        <option value="" selected>- todas -</option>
                                        <?php $articulos->muestra_categorias(); ?>
                                    </select>
                                </label>
                            </div>
                            <div class="div-opciones-busqueda">
                                <label for="buscaRubro">Rubro:
                                    <select id="buscaRubro" name="buscaRubro">
                                        <option value=""> - todos -</option>

                                    </select>
                                </label>
                            </div>

                            <div class="div-opciones-busqueda">
                                <label for="buscaSubRubro">Sub Rubro:
                                    <select id="buscaSubRubro" name="buscaSubRubro">
                                        <option value="">- todos -</option>
                                    </select>
                                </label>
                            </div>
                            <div class="div-opciones-busqueda">

                                <label for="buscaMarca">Marca:
                                    <select id="buscaMarca" name="buscaMarca">
                                        <option value="">- todas -</option>
                                        <?php $articulos->muestra_marcas(); ?>
                                    </select>
                                </label>
                            </div>
                            <div class="div-opciones-busqueda">
                                <label for="buscaModelo">Modelo:
                                    <select id="buscaModelo" name="buscaModelo">
                                        <option value="">- todos -</option>
                                    </select>
                                </label>
                            </div>
                            <!-- <div class="div-opciones-busqueda">
                                <label for="buscaModelo">Proveedor:
                                    <select id="buscaModelo" name="buscaModelo">
                                        <option value="">- todos -</option>
                                    </select>
                                </label>
                            </div> -->
                            <div class="div-opciones-busqueda accion">
                                <button title="Filtrar" alt="Filtrar" type="button" id="botonBuscarFiltrar" name="botonBuscarFiltrar" class="botonAccionPrimario">
                                    <i class="fas fa-sliders-h"></i> Aplicar
                                </button>
                            </div>
                       
                        
                    </div>





                </form>
            </div>






            <div id="contiene-tabla-comprobante" class="paneles">
                <?php if ($soyMovil == "No") : ?>
                    <table class="display compact" id="myTable" data-page-length='5'>
                <?php else : ?>
                    <table class="display compact" id="myTable" data-page-length='5'>
                <?php endif; ?>
                    <thead>
                        <tr>
                            <th>Listado de Productos</th>
                        </tr>
                    </thead>
                        <tr>
                            <td class='vacio'>No se encontaron resultados </td>
                        </tr>
                    </tbody>
                    </table>

            </div>

            <div id="sidebar">
                <div id="jcart">
                    <?php

                    if (isset($jcart) && (is_object($jcart))) {
                        if ($soyMovil == 'Si') {
                            $jcart->display_carrito_pedido_mobil();
                        }
                        if ($soyMovil == 'No') {
                            $jcart->display_carrito_pedido_desktop();
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div id="spinner" class="spinner" style="display:none;">
                <img src="_img/logo-administranet-ecommerce.png" />
                <div class="texto">Procesando...</div>
            </div>


            <form method="post" action="" class="jcart">

                <input type="hidden" name="my-item-qty" value="<?php echo $_REQUEST['cant']; ?>" size="3" />
                <input type="hidden" name="jcartToken" value="<?php echo $_SESSION['jcartToken']; ?>" />
                <input type="hidden" name="my-item-id" value="" />
                <input type="hidden" name="my-item-id-manual" value="" />
                <input type="hidden" name="my-item-name" value="" />
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

                <!-- impuesto interno traer todos los campos para poder calcular bien.
                                        `id_impuesto_interno_abm`
                                        `descripcion_impuesto_interno`
                                        `tipo_impuesto_interno`
                                        `porcentaje`
                                        `monto_fijo`
                                        `peso_calculo`
                                        `pago_minimo`
                                        `id_unimed`
                                        `anulado` -->

                <input type="hidden" name="my-item-impInternoTasa" value="" />
                <input type="hidden" name="my-item-impInterno" value="" />

                <input type="hidden" name="my-item-impInternoDescripcion" value="" />
                <input type="hidden" name="my-item-impInternoTipo" value="" />
                <input type="hidden" name="my-item-impInternoPorcentaje" value="" />
                <input type="hidden" name="my-item-impInternoMontoFijo" value="" />
                <input type="hidden" name="my-item-impInternoPesoCalculado" value="" />
                <input type="hidden" name="my-item-impInternoPagoMinimo" value="" />
                <input type="hidden" name="my-item-impInternoIdUnimed" value="" />
                <input type="hidden" name="my-item-costo" value="" />

                <!-- cantidad unidad display bulto -->
                <input type="hidden" name="my-item-cantidad-unidad-display" value="" />
                <input type="hidden" name="my-item-cantidad-dividir" value="" />
                <input type="hidden" name="my-item-tipo-unidad-contada" value="" />
                <input type="hidden" name="my-item-cantidad-minima-contada" value="" />


                <input type="hidden" name="permiso-sin-stock" id="permiso-sin-stock" value="<?php echo $_SESSION['venta_sin_stock']; ?>">
                <input type="hidden" name="usa-bulto-promedio" value="<?php echo $_SESSION["uso_bulto_promedio"];  ?>" />
                <input type="hidden" name="my-item-saldo" value="">
                <input type="hidden" name="my-item-ensamblado-vta" value="">
                <!-- agregar los campos de display para pasar al jcart.. -->

            </form>







            <script>
                $(document).ready(function() {
                    // filtro avanzado.
                    var divAvanzado = $("#divBuscaRubro");
                    //divAvanzado.fadeOut(); 
                    $('#filtroAvanzado').on('click', function() {
                        // console.log('hago click en la busqueda avanzada---------');
                        // var divAvanzado = $("#divBuscaRubro");
                        // mostrar
                        if ($(this).hasClass('fa-angle-down') === true) {
                            // console.log('tenglo clase angle down----');
                           // vamos a hacer un toggle class
                           divAvanzado.removeClass('ocultar').addClass('mostrar');
                           $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
                        } else {
                            //ocultar panel
                            // console.log('tenglo clase angle UP----');
                            $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
                            // divAvanzado.hide();
                            divAvanzado.removeClass('mostrar').addClass('ocultar');
                            
                        }

                    });

                    $('#nombreBuscaRapido').focus(function() {
                        //$(this).val();
                        //alert('a ver si hace focus');
                        $(this).val('');
                    });
                    $('#buscaCategoria option').each(function() {
                        if ($(this).val() !== '') {
                            $(this).prop('selected', true);
                            $('#buscaCategoria').trigger('change'); // Disparar el evento change manualmente
                            return false; // Salir del each una vez seleccionada la primera opción no vacía
                        }
                        
                    });
                    // agrego al los botones la activacion
                    // $('#iconoCarritoCompra').toggleClass("iconoActivo");
                    // $('#iconoListaProductos').toggleClass("iconoActivo");
                    // meter esto en el cabecera articulo para que se pueda usar con todos los demas
                    // * armando el cartel sweet alert
                    var paramGets = listaGetUrl();
                    let cartel = paramGets.searchParams.get("cartel");
                    let comprobante = paramGets.searchParams.get("ped");
                    let remito = paramGets.searchParams.get("rem");
                    let estado = paramGets.searchParams.get("est");
                    let tipoComprobante = 'pedido';
                    let tipoUsuario = '<?php echo $_SESSION["tipousuario"]; ?>';
                    // console.log('todos los parametros', paramGets);
                    // console.log('remito',remito);
                    // console.log('pedido', comprobante);
                    // console.log('cartel', cartel);
                    // console.log('tipoUsuario', tipoUsuario);

                    // console.log('estado', estado);
                    if (cartel !== null) {
                        let htmlCartel = '';
                        let tituloCartel = '';
                        // todo bien
                        if (cartel == 0) {
                            tituloCartel += 'Excelente';
                            htmlCartel += '<span class="alerta-exito">Se ha generado el ' + tipoComprobante + '<br>';
                            htmlCartel += ' <strong>' + comprobante + '</strong> (<strong>' + estado + '</strong>)</span>';
                            icono = 'success';

                        }
                        // todo mal
                        if (cartel == 1) {

                            tituloCartel += 'Atención!';
                            htmlCartel += '<span class="texto-alerta"> No se hizo el Pedido, intente nuevamente.</span>';
                            icono = 'error';
                        }

                        // * soy cliente
                        if (tipoUsuario == 'vendedor') {
                            Swal.fire({
                                title: tituloCartel,
                                html: htmlCartel,
                                icon: icono,
                                showConfirmButton: true,
                                confirmButtonText: 'Cambiar Cliente',
                                confirmButtonColor: '#2A3E72',
                                showCancelButton: true,
                                cancelButtonText: "No",
                            }).then((accion) => {
                                // console.log('estoy confirmado accion',accion);
                                if (accion.isConfirmed) {
                                    location.href = 'listado-clientes.php?accion=cambiar';
                                }


                            });


                            // listado-clientes.php
                        }
                        if (tipoUsuario == 'cliente') {
                            Swal.fire({
                                title: tituloCartel,
                                html: htmlCartel,
                                icon: icono,
                                showConfirmButton: true,
                                confirmButtonColor: '#2A3E72',
                            });
                        }


                    }

                    // variables globales de inicio del captura de texto.    


                });
            </script>
        </div>
        <?php require_once 'footer.php'; ?>
    </div>
    <div id="basic-modal-content"></div>
</body>

</html>