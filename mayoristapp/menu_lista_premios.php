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
$usaZoom=0;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Premios | administraNET </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>


</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
                                   
             <div class="buscador"> 
                 <h1><i class="fas fa-gift fa-fw fa-lg"></i> Premios</h1>
                 
                <ul class="misOpciones"> 
                    <li><a href="modulo_premios.php?pag=catalogo.php"><i class="fas fa-th fa-lg fa-fw"></i> Catálogo canjes</a></li>
                    <li><a href="modulo_premios.php?pag=sp_categoria_abm_premios.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Gestión categorías </a></li>
                    <li><a href="modulo_premios.php?pag=sp_ab_premios.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Gestión premios</a></li>
                    <li><a href="modulo_premios.php?pag=sp_fotos_premios.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Imágenes premios</a></li>
                    <!--Solo supervisor puede ver el modulo de los puntos -->        
                    <?php if($objVendedor->id_puesto==1):?>  
                        <li><a href="modulo_premios.php?pag=configuracion.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Gestión puntos</a></li>
                        <li><a href="modulo_premios.php?pag=sp_movimientos_puntos_clientes.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión saldos</a></li>
                   <?php endif;?>        
                <?php if($_SESSION["inf_gerenciales"]=="Si"):?>
						<li><a href="modulo_premios.php?pag=configuracion.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Gestión puntos</a></li>
                        <li><a href="modulo_premios.php?pag=sp_movimientos_puntos_clientes.php"><i class="fas fa-chevron-right fa-lg fa-fw"></i>Gestión saldos</a></li>
                        <li><a href="premios-listado-historial-canje.php" alt="Historial de puntos" title="historial de puntos"><i class="fas fa-chart-bar fa-lg fa-fw"></i>Historial Puntos</a></li>
                         <li><a href="premios-listado-canje-cliente.php" alt="Listado de canjes" title="Listado de canjes"><i class="fas fa-chart-bar fa-lg fa-fw"></i>Listado de Canjes</a></li>
                 <?php endif;?>   
                   

                </ul>  
        


            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     
    </body>
</html>