<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Necesito incrustar el css de la lista de las tablas para poder imprimirla mas o menos bien.
 */
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
//ini_set("memory_limit","1000M");
date_default_timezone_set('Europe/London');

require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';

$tipoCliente = $_GET["tipoCliente"];
$claseLista = $_GET["claseLista"];
    
//header("Content-Type: application/vnd.ms-excel;");


//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// Include jcart before session start

//$stylesheet = file_get_contents('_css/style.css');
//print '<html> <head>';
//print "<style>";
//print $stylesheet;
//print "</style>";//
//print "</head>"
//        . "<body>";

       
//dependiendo del dato de tipo de cliente
if($tipoCliente =='consumo'){
    $textoLista= $articulos->mostrar_consumos(1);
}
if($tipoCliente=='si'){
    $textoLista=$articulos->mostrar_articulo_lista(1,"listap");
}
if($tipoCliente=='ranking'){
    $textoLista=$articulos->mostrar_articulo_lista(1,"ranking",$claseLista);
}
if($tipoCliente =='catalogo'){
    //echo "entre catalogo";
    $textoLista=$articulos->mostrar_articulo_lista(1,"catalogo",$claseLista);
}

//echo "<pre>";
//print_r($textoLista);
//echo "</pre>";

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
//require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';
require_once 'Classes/PHPExcel.php';

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')
                                          ->setSize(10);

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");

//las columnas mas comunes
$letras= range('A','Z');

// Add some data
$objPHPExcel->setActiveSheetIndex(0);

//pegar columnas 
//$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:C1');

$titulos = $textoLista["titulo"];
$cabeceras = $textoLista["cabeceras"];
$campos = $textoLista["campos"];

//echo "<pre>";
//print_r($textoLista);
////print_r($cabeceras);
////print_r($campos);
//
//echo "</pre>";

// hago que los titulos ocupen el largo de las columnas.
$cuantasCol = count($cabeceras)-1;
//$largoTitulo = $letras[0].'1:'.$letras[$cuantasCol].'1';

/*
 * Titulos
 */
$contadorFilas =1;
foreach($titulos as $k => $tt){
    $largoTitulo = $letras[0].$contadorFilas.':'.$letras[$cuantasCol].$contadorFilas;
    $objPHPExcel->getActiveSheet()->setCellValue($letras[0].$contadorFilas, $tt);
    $objPHPExcel->getActiveSheet()->mergeCells($largoTitulo);               
    $contadorFilas++;
}

/*
 * Columnas
 */


foreach($cabeceras as $kk => $cc){
    $objPHPExcel->getActiveSheet()->setCellValue($letras[$kk].$contadorFilas, $cc);
    $objPHPExcel->getActiveSheet()->getStyle($letras[$kk].$contadorFilas)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension($letras[$kk])->setAutoSize(true);
}
// paso a la siguente linea para poner el relleno.
$contadorFilas++;
//recorro filas
foreach($campos as $camp){
    //recorro campos de las filas
    foreach($camp as $col => $ca){
        $objPHPExcel->getActiveSheet()->setCellValue($letras[$col].$contadorFilas, $ca);
    }
    $contadorFilas++;
}


// Miscellaneous glyphs, UTF-8
//$objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A4', 'Miscellaneous glyphs')
//            ->setCellValue('A5', 'éàèùâêîôûëïüÿäöüç');

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
//header('Content-Type: application/vnd.ms-excel');
//if($tipoCliente=="consumo"){
//    header('Content-Disposition: attachment;filename="lista_mis_consumos_"'.date("d-m-Y-h-s").'".xls"');
//}else{
//    header('Content-Disposition: attachment;filename="lista_precios_'.date("d-m-Y-h-s").'.xls"');
//}
////header('Content-Disposition: attachment;filename="01simple.xls"');
//header('Cache-Control: max-age=0');
//// If you're serving to IE 9, then the following may be needed
//header('Cache-Control: max-age=1');
//
//// If you're serving to IE over SSL, then the following may be needed
//header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
//header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
//header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
//header ('Pragma: public'); // HTTP/1.0
//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

// office 2007

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
if($tipoCliente=="consumo"){
    header('Content-Disposition: attachment;filename="lista_mis_consumos_"'.date("d-m-Y-h-s").'".xlsx"');
}else{
    header('Content-Disposition: attachment;filename="lista_precios_'.date("d-m-Y-h-s").'.xlsx"');
}
//header('Content-Disposition: attachment;filename="01simple.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save('php://output');
exit;



//        
//if($tipoCliente =='consumo'){
//    $textoLista = $articulos->mostrar_consumos(1);
//} else {
//    $textoLista = $articulos->mostrar_articulo_lista(1);
//}

//$textoLista = $articulos->mostrar_articulo_lista(1);

//print iconv("UTF-8", "ISO-8859-1//TRANSLIT",$textoLista);
//print "</body></html>";