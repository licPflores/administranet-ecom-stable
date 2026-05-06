<?php

// jCart v1.3
// http://conceptlogic.com/jcart/

// This file takes input from Ajax requests and passes data to jcart.php
// Returns updated cart HTML back to submitting page

header('Content-type: text/html; charset=utf-8');
//include_once '../../sesion.inc.php';
// Include jcart before session start
require_once 'jcart.php';

// Process input and return updated cart HTML
//$jcart->display_cart();

//$jcart->display_cartRemTal();
$queFormulario = $_SESSION['formulario'];
$caminoDispo = $_SESSION['caminoDisp'];
$soyMovil = "no";
if ($caminoDispo != "") {
    $soyMovil = "si";
}

// mobile
if($soyMovil=="si"){
    switch($queFormulario){
        case 'pedido':
            $jcart->display_carrito_pedido_mobil();
            break;
        case 'remitoTalonario':
            $jcart->display_carrito_remito_talonario_mobil();
            break;
        case 'remitoSistema':
            $jcart->display_carrito_remito_talonario_mobil();
            break;
        case 'presupuesto':
            $jcart->display_carrito_presupuesto_mobil();
            break;
        case 'devolucion':
            
            $jcart->display_carrito_pedido_mobil();
            break;
    }
}
// desktop
if($soyMovil=="no"){
    switch($queFormulario){
        case 'pedido':
            $jcart->display_carrito_pedido_desktop();
            break;
        case 'remitoTalonario':
            $jcart->display_carrito_remito_talonario_desktop();
            break;
         case 'remitoSistema':
            $jcart->display_carrito_remito_talonario_desktop();
            break;
        case 'presupuesto':
            $jcart->display_carrito_presupuesto_desktop();
            break;
        case 'devolucion':
             
            $jcart->display_carrito_pedido_desktop();
            break;
    }
}