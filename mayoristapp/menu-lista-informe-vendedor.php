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
    <title>Listados Ventas | administraNET </title>
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
                <h1>Informes Ventas/Cobranzas</h1>
                 
                <ul class="misOpciones"> 
                    <!-- <li><a href="informe-ventas-total.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Ventas </a></li> -->
                    <li><a href="informes-ventas-gerenciales.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i> Ventas </a></li>
                    <li><a href="listado-cobranzas-vendedor.php"><i class="fa fa-chevron-right fa-lg fa-fw"></i>Cobranzas </a></li>
                   

                </ul>  
        


            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     
    </body>
</html>