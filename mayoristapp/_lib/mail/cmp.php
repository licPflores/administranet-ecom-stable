<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// obtendremos el id por GET+
// dessencryptar
// llamar al visualizador y base que corresponda con los datos de id.
// visualizar a ver que pasa..
require_once 'sistema/_scripts/php/funciones.php';
if (isset($_REQUEST["id"])){
    //echo "ESToy id:{" .$_GET["id"]."}\n";
    $id=$_REQUEST["id"];
    $arrVuelta= desencriptar_comprobante($id);
//    echo print_r($arrVuelta);
    
    $dbid=$arrVuelta[0]["db"];
    $tipoComp=$arrVuelta[0]["tipocomp"];
    $codMov=$arrVuelta[0]["codmov"];
    
//    echo "<pre>";
//    echo "dbid:{".$dbid."}";
//    echo "tipocomp:{".$tipoComp."}";
//    echo "codmov:{".$codMov."}";
//    echo "</pre>";
    // traigo los datos de la conexion.
    require_once 'sistema/conexion-cmp.inc.php';
    //$foto = traeLogo("_/lib/mail");
    switch($tipoComp){
        case "PRE":
            trae_pre_movil($empresa, $codMov, $conexionT, $baseConecto);
            //generar la conexion y enviarla al que dibuja el comprobante.
            break;
        case "PED":
            trae_ped_movil($empresa, $codMov, $conexionT, $baseConecto);
            break;
        case "FA":
        case "FB":
        case "FC":
        case "FM":
        case "FE":
            trae_fact_movil($empresa, $codMov, $conexionT, $baseConecto);
            break;
        case "REM":
            trae_rem_movil($empresa, $codMov, $conexionT, $baseConecto);
            break;
        case "REC":
            trae_rec_movil($empresa,$codMov,$conexionT,$baseConecto);
            break;
        case "NCA":
        case "NCB":
        case "NCC":
        case "NCM":
        case "NCE":
            trae_nota_credito_movil($empresa,$codMov,$conexionT,$baseConecto);
            break;
    }
    
}

//$id="D2iwEjsLZMKH9mOi0/vFhp1kRoWTTS2OjTrr/6YbfFth9uxuBiYsaPquaW7Nx7jNSRNCu7WJS6wezziX9yCM+g==";
