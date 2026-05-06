<?php 
require_once 'sesion.inc.php';
include_once '_scripts/php/funciones.php';

/*
 * Parametros
 */

$link=$connV;
$codMov=$_GET['codigomovimiento'];


hacer_recibo_pdf($codMov,$connV,'D');
exit();