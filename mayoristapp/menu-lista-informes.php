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
    <title>Listados | administraNET </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>


</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
                                   
             <div class="paneles"> 
                <h1>Informes Gerenciales</h1>
                 
                <ul class="misOpciones"> 
                <?php if($_SESSION["inf_gerenciales"]=="Si"):?>
                    <li><a href="informes-ventas-gerenciales.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Ventas </a></li>
                    <li><a href="informe-utilidad-gerencial.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Rentabilidad </a></li>
                    <li><a href="listado-cobranzas-vendedor.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Cobranzas </a></li>
                    <!--<li><a href="estadisticas-generales.php?modulo=cobranzas"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Cobranzas </a></li>
                    <li><a href="estadisticas-generales.php?modulo=compras"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Compras </a></li>
                    <li><a href="estadisticas-generales.php?modulo=pagos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Pagos </a></li>
                    <li><a href="estadisticas-generales.php?modulo=bancos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Bancos</a></li>
                    <li><a href="estadisticas-generales.php?modulo=caja"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Cajas </a></li>
                    <li><a href="estadisticas-generales.php?modulo=impuestos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Impuestos </a></li>  -->     
                <?php endif;?>
                <?php if($_SESSION["inf_gerenciales"]=="No"):?>
                    <li><a href="informes-ventas-gerenciales.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Ventas </a></li>                  
                    <!--<li><a href="listado-cobranzas-vendedor.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Cobranzas </a></li>-->
                    <!--<li><a href="estadisticas-generales.php?modulo=cobranzas"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Cobranzas </a></li>
                    <li><a href="estadisticas-generales.php?modulo=compras"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Compras </a></li>
                    <li><a href="estadisticas-generales.php?modulo=pagos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Pagos </a></li>
                    <li><a href="estadisticas-generales.php?modulo=bancos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Bancos</a></li>
                    <li><a href="estadisticas-generales.php?modulo=caja"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Cajas </a></li>
                    <li><a href="estadisticas-generales.php?modulo=impuestos"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Impuestos </a></li>  -->     
				<?php endif;?>
                
                </ul>  
        


            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     
    </body>
</html>