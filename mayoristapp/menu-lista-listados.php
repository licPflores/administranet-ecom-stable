<?php require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas        = 1;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 0;
$iconoDisabled  = 1;
$usaZoom = 0;

$codPuesto = $objVendedor->id_puesto;
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Listados | administraNET </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <?php require_once 'cabecera.php'; ?>


</head>

<body>
    <div id="wrapper">
        <?php
        require_once $barra;
        ?>

       

            <div class="paneles buscador">
                <h1>Informes generales</h1>
                <h4>Consulta de Comprobantes</h4>
				<!-- informes y listados de vendedor -->
				<?php if($codPuesto !=1): ?>
                <ul class="misOpciones">
                   
                    <li><a href="lista-presupuestos-vendedor.php"> Presupuestos</a></li>
                    
                    <li><a href="lista-pedidos-total.php"> Pedidos</a></li>
                    
                    <li><a href="gestion-devoluciones.php"> Devoluciones</a></li>                   

                    <li><a href="lista-remitos.php"> Remitos</a></li>
                    <li><a href="lista_facturas_electronicas.php"> Facturas</a></li>
                    <li><a href="lista-recibos.php"> Recibos Web</a></li>
                    <li><a href="lista_nota_credito.php"> Nota de Crédito</a></li>
                </ul>
                <h4>Listados</h4>
                <ul class="misOpciones">
                    <li> <a href="logistica_lista_comprobantes_rutas.php" class="textField"> Comprobantes en ruta</a></li>
                    <li> <a href="lista_precio.php" class="textField"> Lista de precios</a></li>
                    <li> <a href="lista_catalogo_productos.php" class="textField"> Catálogo de Productos</a></li>
                    <li><a href="lista-mis-consumos.php"> Mis consumos</a></li>
                    <li><a href="lista-promociones.php"> Lista de promociones</a></li>
                    <li><a href="lista-articulo-remito.php"> Lista articulo remitados</a></li>
                    <li><a href="lista-cuenta-corriente.php"> Cuenta corriente</a></li>
                    <li><a href="lista-comprobantes-ncancelados.php"> Comprobantes no cancelados</a></li>


                </ul>
				<?php endif;?>
				<!-- fin infomres y listados de vendedor -->
				
				<!-- informes y listados de Supervisor -->
				<?php if($codPuesto==1):?>
				<ul class="misOpciones">
                   
                    <li><a href="lista-presupuestos-vendedor.php"> Presupuestos</a></li>
                    
                    <li><a href="lista-pedidos-total.php"> Pedidos</a></li>
                    
                    <li><a href="gestion-devoluciones.php"> Devoluciones</a></li>                   

                    <li><a href="lista-remitos.php"> Remitos</a></li>
                    <li><a href="lista_facturas_electronicas.php"> Facturas</a></li>
                    <li><a href="lista-recibos.php"> Recibos Web</a></li>
                    <li><a href="lista_nota_credito.php"> Nota de Crédito</a></li>
                </ul>
                <h4>Listados</h4>
                <ul class="misOpciones">
                    <li> <a href="logistica_lista_comprobantes_rutas.php" class="textField"> Comprobantes en ruta</a></li>
                    <li> <a href="lista_precio.php" class="textField"> Lista de precios</a></li>
                    <li> <a href="lista_catalogo_productos.php" class="textField"> Catálogo de Productos</a></li>
                    <li><a href="lista-mis-consumos.php"> Mis consumos</a></li>
                    <li><a href="lista-promociones.php"> Lista de promociones</a></li>
                    <li><a href="lista-articulo-remito.php"> Lista articulo remitados</a></li>
                    <li><a href="lista-cuenta-corriente.php"> Cuenta corriente</a></li>
                    <li><a href="lista-comprobantes-ncancelados.php"> Comprobantes no cancelados</a></li>
                    <li><a href="listado-stock-existencias.php">Stock y Existencias</a></li>


                </ul>
				<?php endif;?>
				<!-- fin informe y listados supervisor -->
				


            </div>
        

        <?php require_once 'footer.php'; ?>

    </div>

</body>

</html>