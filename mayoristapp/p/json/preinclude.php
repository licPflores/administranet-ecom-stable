<?php
	if(!isset($_SESSION))require_once '../../sesion.inc.php';
	if(!isset($_SESSION['id_sesion'])){
			require_once '../../sesion.inc.php';	

		}

if(!isset($mysqli)){
	require_once '../../conexion-general.inc.php'; 
	$mysqli = $connV;
}
			//echo '<pre>';
			//echo $_SESSION['vendedor']->id_usuario;
		//	echo '</pre>';
		
		define('MAXIMO', 4);//Nimero de productos que apareceran
		

function GuardaVar($variable,$contenido){
	$retorno = false;
	if(isset($_SESSION)){
		$_SESSION['variables'][$variable]=$contenido;
		return true;
	}
	
return $retorno;	
}


function LeerVar($variable){
	$retorno = false;
	if(isset($_SESSION))
	if(isset($_SESSION['variables'][$variable])){
		$retorno =	$_SESSION['variables'][$variable];
		
	}
	
return $retorno;	
}

