<?php 
require_once '../sesion.inc.php';

$quien= $_GET["tipo"];
if($quien=="cliente"){
    $listadoJson= $_SESSION["clienteRapido"];
}
if($quien=="articulo"){
    $listadoJson =   $_SESSION["productoRapido"];
}
if($quien=="articuloListaPrecio"){
    $listadoJson =   $_SESSION["productoRapido"];
}
if($quien=="catalogoProducto"){
    $listadoJson =   $_SESSION["productoRapido"];
}
header('Content-Type: application/json');
print $listadoJson;
?>