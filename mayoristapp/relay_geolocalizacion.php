<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'sesion.inc.php';
if(isset($_POST["geo_lat"])){
    $latitud = $_POST["geo_lat"];
    $longitud = $_POST["geo_long"];
    $geolocEstado=$_POST["estado"];
    
    $_SESSION["latitud"] = $latitud;
    $_SESSION["longitud"] = $longitud;
    $_SESSION["geolocEstado"] =$geolocEstado;
//    echo "<pre>";
//    print_r($_SESSION);
//    echo "</pre>";
}
