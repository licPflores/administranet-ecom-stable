<?php
$soyCliente="no";
if(isset($_REQUEST["cliente"])&&$_REQUEST["cliente"]=="si"){
    $soyCliente="si";
}
require_once 'sesion.inc.php';
//require_once 'conexion.inc.php';
        
        $tipoUsuario = $_SESSION['tipousuario'];
        session_unset();
        session_destroy();
        unset($_SESSION);
        //$_SESSION = array();
	//echo"<script>parent.location.href='index.php'</script>";
	if($soyCliente=="si"){
            header("Location: ../index.php");
        }else{
            header("Location: index.php");

        }