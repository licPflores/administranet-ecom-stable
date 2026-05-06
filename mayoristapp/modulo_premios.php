<?php 
//error_reporting('E_ALL');
require_once 'sesion.inc.php';
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
//si no tengo un cliente y he seleccionado la pagina de catalogo. me voy de aqui.
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 0;
$uModal     = 0;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom =0;
/**
 * elimino el carrito 
**/
unset($_SESSION['jcart']);
$pagina="p/";
if(isset($_REQUEST["pag"])){
    $pagina .=$_REQUEST["pag"];
    if($_REQUEST["pag"]=="catalogo.php"&&!isset($_SESSION["cliente"])){
        // premios solo para clientes en este punto.
        header('Location: listado-clientes.php');
    }       
            
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Premios | administraNET e-com </title>
     <?php require_once 'cabecera.php';?>
  
  
  
<!-- fin css estilo autocmpletar -->
<script>
   
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(document).ready(function(){
 
 
 });
</script>
<style>
    
</style>
</head>
<body>
    <div id="wrapper">
        <?php require_once $barra;?>
        <div id="content" style="text-align:center;">
            <iframe name="ifrPremios" id="ifrPremios" src="<?php echo $pagina;?>" style="width:100%;height:100%;min-height: 650px;max-width: 700px;border: 0;">
                
            </iframe>
           
        </div>

 
        <?php require_once 'footer.php';?>   
    
    </div>
    
        
    </body>
</html>

