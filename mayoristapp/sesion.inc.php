<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

function is_session_started()
{
    
    return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        
}

// Example
if ( is_session_started() === FALSE ) {	
		
		session_start();
	
	
}

include_once 'includes/includes.inc.php';
$const = get_defined_constants(true);
//echo print_r($const['user']);
//session_start();
	if(!isset($_SESSION['id_sesion'])){
        // sin sesion creada, o fin de sesion.
		$currentPath = dirname($_SERVER['PHP_SELF']);

        // Ruta objetivo (mayorista/sistema/index.php)
        echo $targetPath = $currentPath . '/index.php';
        echo 'sin sesion debo partir';
        // Redireccionar
        //header("Location: $targetPath");
                

		
		exit();
		
	}else{
        /*
         * TIPO DE USUARIO
         */
        
            // Dispositivo
            $caminoDispo = $_SESSION["caminoDisp"];
            
            switch($_SESSION['tipousuario']){
                case 'cliente':
                    require_once 'conexion.inc.php';
                    $barra = 'header-cliente.inc.php';
                    //$objCliente = $_SESSION['cliente'];
                    if(isset($_SESSION['cliente']) && is_array($_SESSION['cliente'])){
//                        print_r($_SESSION['cliente']);

                        $objCliente = $_SESSION['cliente'][0];
                        $arrCliente = $_SESSION['cliente'][1];
                    }else{
                        $objCliente = $_SESSION['cliente'];
                    }
                    break;
                case 'vendedor':
                    
                    require_once 'conexion-vendedor-empresa.inc.php';
                    $barra = 'header-vendedor.inc.php';
                    $objVendedor = $_SESSION['vendedor'];
                    $lista_Pv = $_SESSION['lista_pv']; 
                    if(isset($_SESSION['cliente']) && is_array($_SESSION['cliente'])){
//                        print_r($_SESSION['cliente']);

                        $objCliente = $_SESSION['cliente'][0];
                        $arrCliente = $_SESSION['cliente'][1];
                    }else{
                        if(isset($_SESSION['cliente'])){
                                $objCliente = $_SESSION['cliente'];
                        }
                    }
                    break;
            }
        }
        
if(isset($_SESSION['id_sesion'])){        
    include "conf.php";
}