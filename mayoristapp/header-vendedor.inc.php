<?php
require_once '_scripts/php/funciones.php';
?>
<?php if ($caminoDispo != "") : ?>
    <?php
    /*
* MOVIL
*/
    require_once 'header-vendedor-mobil.inc.php';
    ?>
<?php else : ?>
    <?php
    /*
*  DESKTOP
*/
    ?>
    <?php
    $iconoSuper = "fa-user";
    $tituloPuesto = "Vendedor";
    if ($objVendedor->id_puesto == 1) {
        //supervisor
        $iconoSuper = "fa-users";
        $tituloPuesto = "Supervisor de Ventas";
    } else {
        // veo el permiso
        if ($objVendedor->permiso_supervisor_venta == 'Si') {
            $iconoSuper = "fa-users";
            $tituloPuesto = "Supervisor de Ventas";
        }
    }

    $arrListaPrecio = $_SESSION["arr_lista_precio"];
    $modInventario =0;
    $modPremios = 0;
    if(isset($_SESSION['modulo_inventario'])){
        $modInventario = $_SESSION['modulo_inventario'];
    }
    if(isset($_SESSION['modulo_premios'])){
        $modPremios = $_SESSION['modulo_premios'];
    }

    // echo "<pre>";
    // print_r($_SESSION);
    // echo "</pre>";
    ?>
    <div id="header" class="noPrint">
        <div id="headerLogo">
            <a href="escritorio.php">
                <img id="imgLogo" src="foto.php?origen=logo|0" alt="<?php echo $_SESSION['nombre_empresa']; ?>" title="<?php echo $_SESSION['nombre_empresa']; ?>" class="asBlock" />
            </a>
        </div>
        <div id="statusBar">

            <span class="vendedor">

                <i class="fa <?php echo $iconoSuper; ?> fa-fw fa-lg" title="<?php echo $tituloPuesto; ?>"></i> <?php echo $objVendedor->nombre_usuario . ' ' . $objVendedor->apellido_usuario; ?>
                <strong><i class="fa fa-cog fa-fw fa-lg" id="iconoVendedor" title="Opciones del vendedor"></i></strong>
            </span>
            <div class="clase-tooltip" id="datosVendedor">
                <?php if ($iconoSuper == "fa-users") : ?>
                    <i class="fa fa-users fa-fw fa-lg"></i>Supervisor<br />
                <?php endif; ?>
                <i class="fa fa-truck fa-fw fa-lg"></i> <?php echo $objVendedor->deposito; ?><br />
                <i class="fa fa-check-square fa-fw fa-lg"></i> Venta sin stock: <strong><?php echo $_SESSION["venta_sin_stock"]; ?></strong><br />
                <i class="fa fa-check-square fa-fw fa-lg"></i> Lim Desc Pie: <strong><?php echo $objVendedor->lim_desc_pie; ?>%</strong><br />
                <i class="fa fa-check-square fa-fw fa-lg"></i> Lim Desc Reng: <strong><?php echo $objVendedor->lim_desc_renglon; ?>%</strong>
            </div>


        </div>
        <?php if (isset($_SESSION['cliente'])) : ?>

            <div class="headerCliente">
                <div class="izquierda nombrecliente">
                    <div class="cliente"><a href="listado-clientes.php"><?php echo $objCliente->cliente; ?></a>
                    <span class="cambiar-cliente"><i class="fa-solid fa-arrow-right-arrow-left"></i> Cambiar cliente</span>
                </div>
                    <div class="barra">
                        <i class='fa fa-pen-square fa-lg fa-2x' id="editarClienteH" rel="<?php echo $objCliente->Codigo; ?>" title="Editar Datos"></i>
                        <i class='fa fa-address-card fa-lg fa-2x' id="domicilioClienteH" rel="<?php echo $objCliente->Codigo; ?>" title="Domicilios adicionales"></i>
                        <i class="fa fa-chart-line fa-lg  fa-2x" id="icono-ver-fact" title="Comprobantes no Cancelados"></i>
                        <!-- <i class="fa fa-shopping-basket fa-lg  fa-2x" id="icono-ver-consumos" title="Top 20 últimos consumos"></i> -->
                    </div>
                </div>
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Codigo: <strong><?php echo $objCliente->Codigo; ?></strong></div>
                <!--<div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Lista precio: <strong><?php //echo $objCliente->listaPrecio;
                                                                                                                    ?></strong></div>-->
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Cond.Vta: <strong><?php echo $objCliente->condVenta; ?></strong></div>
                <?php foreach($arrListaPrecio as $lista):?>
                <?php if($lista['texto']==$objCliente->listaPrecio):?>
                    <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i>Lista de precio: <strong><?php echo $lista['texto'].' - '.$lista['nombre']; ?></strong></div>

                <?php endif;?>
            <?php endforeach;?>  
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Saldo: <strong>$<?php echo number_format($objCliente->saldo, 2, ",", "."); ?></strong></div>
                <?php if (property_exists($objCliente, 'usaPremio') && ($objCliente->usaPremio === "Si")) : ?>

                    <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Puntos: <strong><?php echo number_format($objCliente->puntos, 0, ',', '.'); ?></strong></div>
                <?php endif; ?>
                <br>
                <div class="izquierda"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Credito: <strong>$<?php echo $objCliente->Credito; ?></strong></div>
                <?php if ($objCliente->descPie > 0) : ?>
                    <div class="izquierda verde"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Desc. Pie: <strong><?php echo $objCliente->descPie; ?>% </strong></div>
                <?php endif; ?>
                <?php if ($objCliente->descPie > 0) : ?>
                    <div class="izquierda verde"><i class="fa  fa-check-circle fa-fw fa-lg"></i> Desc. Reng: <strong><?php echo $objCliente->descRenglon; ?>%</strong></div>
                <?php endif; ?>

                <?php if (isset($arrCliente['exceso'])) : ?>

                    <?php if ($arrCliente['exceso'] == 1) : ?>
                        <div class="izquierda exceso">
                            <?php echo '<i class="fa  fa-exclamation-triangle fa-lg"></i> Exceso vto en <strong>' . $arrCliente['dias_exceso_limite'] . '</strong> días '; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <ul id="nav">
            <!--<li ><a href="escritorio.php">Escritorio</a></li>-->
            <li><a href="listado-clientes.php"><i class="fa-solid fa-image-portrait fa-lg fa-fw fa-2x"></i> Cliente</a></li>
            <?php if($modInventario!=0):?>
            <li><a href="inventario/index.php"><i class="fa-solid fa-boxes-stacked fa-lg fa-fw fa-2x"></i> Inventario</a></li>
            <?php endif;?>
			<!-- infomres y listados para vendedores -->
			<?php if ($objVendedor->id_puesto != 1) : ?> 
            <li><a><i class="fa fa-rectangle-list fa-fw fa-lg fa-2x"></i> Informes</a>
                <ul>
                    
                    <li>
                        <a> Comprobantes <i class="fas fa-chevron-right fa-lg fa-fw"></i></a>    
                        <ul>
                            <li><a href="lista-presupuestos-vendedor.php"> Presupuestos</a></li>
                            <li><a href="lista-pedidos-total.php"> Pedidos</a></li>

                            <!--<li><a href="gestion-devoluciones.php"> Devoluciones</a></li>-->
                            <?php if ($_SESSION["usaRemito"] == "Si") : ?>
                            <li><a href="lista-remitos.php"> Remitos</a></li>    ´
                            <?php endif;?>                        
                            <!--<li><a href="lista_facturas_electronicas.php"> Facturas</a></li>-->
                            <!--<li><a href="lista-recibos.php"> Recibos Web</a></li>-->
                            <!--<li><a href="lista_nota_credito.php"> Nota de Crédito</a></li>-->
                            
                        </ul>
                    </li>
                    <li>
                        <a> Listados <i class="fas fa-chevron-right fa-lg fa-fw"></i></a>    
                        <ul>
                            <!--<li><a href="logistica_lista_comprobantes_rutas.php"> Comprobantes en Ruta</a></li>-->
                            <li><a href="lista_precio.php"> Lista de Precios</a></li>
                            <li><a href="lista_catalogo_productos.php"> Catálogo de productos</a></li>

                            <!--<li><a href="lista-promociones.php"> Lista de Promociones</a></li>-->
                            <li><a href="lista-mis-consumos.php">  Consumos cliente</a></li>
                            <!--<li><a href="lista-comprobantes-ncancelados.php"> Comprobantes No Cancelados</a></li>-->
                            <!--<li><a href="lista-cuenta-corriente.php"> Cuenta Corriente</a></li>-->
                            <!--<li><a href="lista-articulo-remito.php">Artículos Remitados</a></li>-->
                        </ul>
                    </li>

                </ul>
            </li>
			<?php endif;?>
           <!-- fin informes y listados para vendedores.-->
		   <?php if ($objVendedor->id_puesto == 1) : ?>
		   <!-- Informes y listados para supervisor -->
		   <li><a><i class="fa fa-rectangle-list fa-fw fa-lg fa-2x"></i> Informes</a>
                <ul>
                    
                    <li>
                        <a> Comprobantes <i class="fas fa-chevron-right fa-lg fa-fw"></i></a>    
                        <ul>
                            <li><a href="lista-presupuestos-vendedor.php"> Presupuestos</a></li>
                            <li><a href="lista-pedidos-total.php"> Pedidos</a></li>

                            <li><a href="gestion-devoluciones.php"> Devoluciones</a></li>
                            <?php if ($_SESSION["usaRemito"] == "Si") : ?>
                            <li><a href="lista-remitos.php"> Remitos</a></li>    ´
                            <?php endif;?>                        
                            <li><a href="lista_facturas_electronicas.php"> Facturas</a></li>
                            <li><a href="lista-recibos.php"> Recibos Web</a></li>
                            <li><a href="lista_nota_credito.php"> Nota de Crédito</a></li>
                            
                        </ul>
                    </li>
                    <li>
                        <a> Listados <i class="fas fa-chevron-right fa-lg fa-fw"></i></a>    
                        <ul>
                            <li><a href="logistica_lista_comprobantes_rutas.php"> Comprobantes en Ruta</a></li>
                            <li><a href="lista_precio.php"> Lista de Precios</a></li>
                            <li><a href="lista_catalogo_productos.php"> Catálogo de productos</a></li>

                            <li><a href="lista-promociones.php"> Lista de Promociones</a></li>
                            <li><a href="lista-mis-consumos.php">  Consumos cliente</a></li>
                            <li><a href="lista-comprobantes-ncancelados.php"> Comprobantes No Cancelados</a></li>
                            <li><a href="lista-cuenta-corriente.php"> Cuenta Corriente</a></li>
                            <li><a href="lista-articulo-remito.php">Artículos Remitados</a></li>
                            <li><a href="listado-stock-existencias.php">Stock y Existencias</a></li>
                        </ul>
                    </li>

                </ul>
            </li>
			<?php endif;?>
		   <!-- fin infomres y listados supervisor -->

            <li>
                <a> <i class=" fa fa-chart-line fa-lg fa-fw fa-2x"></i>Estadisticas</a>
                <ul>

                    <?php if ($_SESSION["inf_gerenciales"] == "Si") : ?>
                        <?php if ($objVendedor->id_puesto == 1) : ?>
                            <li><a href="informes-ventas-gerenciales.php"> Ventas </a></li>
                            <li><a href="informe-utilidad-gerencial.php"> Rentabilidad </a></li>
                            <li><a href="listado-cobranzas-vendedor.php" title="Listado de cobranzas"> Cobranzas </a></li>
                            <!--# informes gerenciales  -->
                            <!-- <li><a href="datos_informe_cobranzas.php">Cobranzas </a></li>
                            <li><a href="datos_informe_compras.php"> Compras </a></li>

                            <li><a href="datos_informe_pagos.php"> Pagos </a></li>
                            <li><a href="datos_informe_bancos.php"> Bancos</a></li>
                            <li><a href="datos_informe_cajas.php"> Cajas </a></li>
                            <li><a href="datos_informe_impuestos.php"> Impuestos </a></li>
                            <li><a href="estadisticas-paretto.php"> Paretto </a></li> -->
                        <?php else : ?>

                            <li><a href="informes-ventas-gerenciales.php"> Ventas </a></li>
                            <li><a href="informe-utilidad-gerencial.php"> Rentabilidad </a></li>
                            <li><a href="listado-cobranzas-vendedor.php" title="Listado de cobranzas"> Cobranzas </a></li>

                        <?php endif; ?>
                        
                    <?php else : ?>
                        <li><a href="informes-ventas-gerenciales.php"> Ventas </a></li>
                        <!--<li><a href="listado-cobranzas-vendedor.php" title="Listado de cobranzas"> Cobranzas </a></li>--->

                    <?php endif; ?>
                </ul>
            </li>




            <!-- #mod Premios -->
             <?php if($modPremios!=0):?>
                <li><a> <i class="fas fa-gift fa-lg fa-fw"></i> Premios</a>
                    <ul>
                        <li><a href="modulo_premios.php?pag=catalogo.php"><i class="fas fa-th fa-lg fa-fw"></i> Catálogo canjes</a></li>
                        <li><a href="modulo_premios.php?pag=sp_categoria_abm_premios.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión categorías</a></li>
                        <li><a href="modulo_premios.php?pag=sp_ab_premios.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión premios</a></li>
                        <li><a href="modulo_premios.php?pag=sp_fotos_premios.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Imágenes premios</a></li>
                    <!-- #Solo supervisor puede ver el modulo de los puntos  -->
                        <?php if ($objVendedor->id_puesto == 1) : ?>
                            <li><a href="modulo_premios.php?pag=configuracion.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión puntos</a></li>
                            <li><a href="modulo_premios.php?pag=sp_movimientos_puntos_clientes.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión saldos de puntos</a></li>
                        <?php endif; ?>
                        <?php if ($_SESSION["inf_gerenciales"] == "Si") : ?>

                            <li><a href="premios-listado-historial-canje.php" alt="Historial de puntos" title="historial de puntos"><i class="fas fa-chart-bar fa-lg fa-fw"></i>Historial Puntos</a></li>
                            <li><a href="premios-listado-canje-cliente.php" alt="Listado de canjes" title="Listado de canjes"><i class="fas fa-chart-bar fa-lg fa-fw"></i>Listado de Canjes</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif;?>
            <li style="float: right;"><a href="javascript:void(0);" onclick="salida();"> <i class="fa fa-sign-out-alt fa-lg fa-fw fa-2x"></i> Salir</a></li>
        </ul>
       
        
    </div>
    <div id="modal-consumos-cliente">
            <div class="tituloVentana">TOP 20 Productos consumidos <button id="cierroConsumos" class="botonNuevo black grande">X</button></div>
            <table id="tablaConsumos" name="tablaConsumos" cellspacing="1" style="width:98%">
            </table>
        </div>
<?php endif; ?>