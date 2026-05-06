<?php require_once 'sesion.inc.php';
      ////require_once 'ajax-articulos.php';
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas        = 0;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 0;
$iconoDisabled  = 1;
$usaZoom=0;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Acerca de | administraNET </title>
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
                <h1>Contacto</h1>

                <div>

                    <div class="nombreEmpresa">
                        <?php echo $_SESSION['nombre_empresa']; ?>
                        <br>
                    </div>
                    <div>
                        <span class="datoEmpresa">
                            <i class="fa fa-home fa-lg fa-fw"></i> <?php echo $_SESSION['domicilio_empresa']; ?></span><br>
                        <span class="datoEmpresa">
                            <i class="fa fa-phone fa-lg fa-fw"></i> <?php echo $_SESSION['telefono_empresa']; ?>
                        </span><br>
                        <span class="datoEmpresa">
                            <i class="fa fa-info fa-lg fa-fw"></i> <?php echo $_SESSION['cuit_empresa']; ?></span>

                    </div>

                </div> 
        


            </div>
            <div class="buscador"> 
                <h1>Acerca de</h1>
                <div>
                    <h2>administraNET gestión</h2>
                    <p>Somos una empresa con 10 años de trayectoria en el desarrollo de software,
                        conformada por jóvenes profesionales en sistemas, administración de empresas y contadores. 
                        <br>Hemos logrado desarrollar un Software que cubre diferentes 
                        segmentos: ERP – POS – Gestión – E-commerce, el cual es muy potente y de fácil uso.</p>
                </div>
                <div >
                   <p> visitenos en <a class="text" href="https://www.administranet.com.ar" title="administraNET gestión software de facturación gratis" target="_blank">https://www.administranet.com.ar</a>
                    <a href="https://www.administranet.com.ar" title="administraNET gestión software de facturación gratis" target="_blank">
                   <img src="_img/logo-administranet-ecommerce.png" alt="desarrollado por administraNET gestión" title="administraNET gestión" />
                   </a>
                       </p>
                </div>
        </div>
 
        
    
    </div>
     
    </body>
</html>