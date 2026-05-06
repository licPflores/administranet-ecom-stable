<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set("memory_limit","1000M");

require_once '_lib/mpdf2/mpdf.php';
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';
$muestraR = trim($articulos->mostrar_articulo_lista(2));
$mpdf = new mPDF('c','A4');
$mpdf->simpleTables = true; 
//$stylesheet = file_get_contents('_css/style.css');
//$mpdf->WriteHTML($stylesheet,1);

$mpdf->WriteHTML($muestraR);
$mpdf->Output('Lista-precios-'.date("d-m-Y-h-s"),'D');
exit; 