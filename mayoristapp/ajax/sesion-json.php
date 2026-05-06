<?php

require_once '../sesion.inc.php';
$vuelta = array('estado'=>'error','descripcion'=>'sesion vacia salir.');

if(!isset($_SESSION['id_sesion'])){

    $vuelta = array('estado'=>'error','descripcion'=>'sesion vacia salir.');
    header('Content-Type: application/json');
    print json_encode($vuelta);

}

//* datos de sesion sola sin cilente sin vendedor
if(isset($_GET['datosSesion'])&&$_GET['datosSesion']==1){
    if(isset($_SESSION['id_sesion'])){
        $arrayVuelta = $_SESSION;
        $vuelta = array('estado'=>'ok','data'=>$arrayVuelta);
         

    }
    header('Content-Type: application/json');
    print json_encode($vuelta);

}

// * mandar usuario
if(isset($_GET['usuarioLogin'])&&$_GET['usuarioLogin']==1){
    if(isset($_SESSION['id_sesion'])){
        // mandar los datos del usuario solamente.
        $arrayVuelta = $_SESSION;
        $vuelta = array('estado'=>'ok','data'=>$arrayVuelta);
        
        // echo 'hay sesion=?<pre>';
        // print_r($_SESSION);
        // echo'</pre>';  
    }
    header('Content-Type: application/json');
    print json_encode($vuelta);

}

// * mandar cliente
if(isset($_GET['traeDatosClienteSeleccionado'])&&$_GET['traeDatosClienteSeleccionado']==1){
    $clienteSeleccionado = array();
    // cliente seleccionado
    if(isset( $_SESSION['cliente'])&&!empty($_SESSION['cliente'])){
        $clienteSeleccionado = $_SESSION['cliente'];
        $vuelta = array('estado'=>'ok','data'=>$clienteSeleccionado);
    }
    // no esta seleccionado un cliente
    if(!isset( $_SESSION['cliente'])){
        
        $vuelta = array('estado'=>'error','data'=>null);
    }
    header('Content-Type: application/json');
    print json_encode($vuelta);
}

// vopy a ir colocando pedidos de las sesiones

?>