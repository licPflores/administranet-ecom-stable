<?php
// --- Medición de tiempo de ejecución (inicio) ---
function mostrar_tiempo_ejecucion($__tiempo_inicio) {
    $__tiempo_fin = microtime(true);
    $__tiempo_total = $__tiempo_fin - $__tiempo_inicio;
    echo "<div style='position:fixed;bottom:0;right:0;background:#222;color:#fff;padding:6px 12px;font-size:12px;z-index:9999;'>Tiempo de ejecución: " . number_format($__tiempo_total, 3) . " seg</div>";
}
$__tiempo_inicio = microtime(true);
// exporta_lista_pdf.php
// ...existing code...
// Genera PDF de la lista de precios usando mPDF (PHP 5.6)

require_once __DIR__ . '/_lib/mpdf2/vendor/autoload.php'; // Usar mPDF moderno compartido
require_once 'sesion.inc.php';
require_once 'ajax-articulos.php';


// simular relay-art.php para obtener HTML (sin imprimirlo) copiar como maneja los GET y luego generar PDF con ese HTML
// * copia relay art.php 
$claseLista = null;
$tipoCliente = null;


    $categoria = null;
    $rubro          = null;
    $subRubro       = null;
    $marca          = null;
    $modelo         = null;
    $laboratorio    = null;
    $buscRapida     = null; // nombre del producto
    $idArt          = null;
    $idDeposito     = null;
    $claseBusqueda  = null; // si busco por texto o id articul o texto,codigo
    $tipoCliente    = null;
    $idTipoCliente  = null;
    $queCampo       = null;
    $promo          = null;
    $consumo        = null;
    $tacc = null;
    $proveedor = null;
    $listaDePrecio=null;
    $ivaIncluido    ='No';
    $arrParametros = array();

    // "buscarProducto"    : 1,                               
    // "queArticulo"       : nombreArticulo,                   
    // "idArticulo"        : idArticulo,                   
    // "claseBusca"        : claseBusca, texto o codigo

    // echo 'Posteo<pre>',print_r($_GET),'</pre>',PHP_EOL;

    // --- Al final del script, antes de cualquier exit o cierre ---
    // Se mostrará el tiempo de ejecución justo antes del exit final

    if (isset($_GET["queCampo"])) {
        $queCampo = $_GET["queCampo"];
    }

    if (isset($_GET['categoria']) && $_GET['categoria'] != "") {
        $categoria = $_GET['categoria'];
    }
    if (isset($_GET['rubro']) && $_GET['rubro'] != "") {
        $rubro = $_GET['rubro'];
    }
    if (isset($_GET['subrubro']) && $_GET['subrubro'] != "") {
        $subRubro = $_GET['subrubro'];
    }
    if (isset($_GET['marca']) && $_GET['marca'] != "") {
        $marca = $_GET['marca'];
    }
    if (isset($_GET['modelo']) && $_GET['modelo'] != "") {
        $modelo = $_GET['buscaModelo'];
    }
    if (isset($_GET['laboratorio']) && $_GET['laboratorio'] != "") {
        $laboratorio = $_GET['buscaLaboratorio'];
    }

// recibo ademas los nombres de los filtros para mostrar en el pdf, por ejemplo si busco por marca, recibo el id de la marca pero tambien el nombre de la marca para mostrarlo en el pdf
    if (isset($_GET['categoriaText']) && $_GET['categoriaText'] != "") {
        $categoriaTexto = $_GET['categoriaText'];
    }
    if (isset($_GET['rubroText']) && $_GET['rubroText'] != "") {
        $rubroTexto = $_GET['rubroText'];
    }
    if (isset($_GET['subrubroText']) && $_GET['subrubroText'] != "") {
        $subRubroTexto = $_GET['subrubroText'];
    }
    if (isset($_GET['marcaText']) && $_GET['marcaText'] != "") {
        $marcaTexto = $_GET['marcaText'];
    }
    if (isset($_GET['modeloText']) && $_GET['modeloText'] != "") {
        $modeloTexto = $_GET['modeloText'];
    }
    if (isset($_GET['laboratorioText']) && $_GET['laboratorioText'] != "") {
        $laboratorioTexto = $_GET['laboratorioText'];
    }
    if (isset($_GET['clienteText']) && $_GET['clienteText'] != "") {
        $clienteTexto = $_GET['clienteText'];
    }

    if (isset($_GET['queArticulo']) && $_GET['queArticulo'] != "") {
        $buscRapida = $_GET['queArticulo'];
    }

    if (isset($_GET['idArticulo']) && $_GET['idArticulo'] != "") {
        $idArt = $_GET['idArticulo'];
    }
    // si texto o codigo
    if (isset($_GET["claseBusca"])) {
        $claseBusqueda = $_GET["claseBusca"];
    }
    if (isset($_GET['promo']) && $_GET['promo'] == "1") {
        $promo = $_GET['promo'];
    }
    if (isset($_GET['consumo']) && $_GET['consumo'] == "1") {
        $consumo = $_GET['consumo'];
    }
    if (isset($_GET['ivaIncluido'])) {
        $ivaIncluido = $_GET['ivaIncluido'];
    }
    if (isset($_GET['tipoCliente'])) {
        $tipoCliente = $_GET['tipoCliente'];
    }
    if(isset($_GET['imagenProducto'])){
        $imagenProducto = $_GET['imagenProducto'];
    }
    if(isset($_GET['proveedor'])&& $_GET['proveedor'] != ""){
        $proveedor = $_GET['proveedor'];
    }
    if(isset($_GET['tacc'])&& $_GET['tacc'] != ""){
        $tacc = $_GET['tacc'];
    }
    if(isset($_GET['listaDePrecios'])&& $_GET['listaDePrecios'] != ""){
        $listaDePrecio = $_GET['listaDePrecios'];
    }

    if(isset($_GET['queAccion'])&&$_GET['queAccion']){
        $queAccion=$_GET['queAccion'];
    }



    $arrParametros['categoria'] = $categoria;
    $arrParametros['rubro']          = $rubro;
    $arrParametros['subrubro']       = $subRubro;
    $arrParametros['marca']          = $marca;
    $arrParametros['modelo']         = $modelo;
    $arrParametros['laboratorio']    = $laboratorio;
    $arrParametros['buscRapida']     = $buscRapida; // nombre del producto
    $arrParametros['idArt']          = $idArt;
    $arrParametros['idDeposito']     = $idDeposito;
    $arrParametros['claseBusqueda']  = $claseBusqueda; // si busco por texto o id articul o texto,codigo
    $arrParametros['tipoCliente']    = $tipoCliente;
    $arrParametros['idTipoCliente']  = $idTipoCliente;
    $arrParametros['queCampo']       = $queCampo;
    $arrParametros['promo']          = $promo;
    $arrParametros['consumo']        = $consumo;
    $arrParametros['ivaIncluido']        = $ivaIncluido;
    $arrParametros['imagenProducto'] = $imagenProducto;
    $arrParametros['proveedor'] = $proveedor;
    $arrParametros['tacc'] = $tacc;
    $arrParametros['listaDePrecio'] = $listaDePrecio;

    $arrParametros['queAccion'] = $queAccion;
    // echo '<pre>',print_r($arrParametros),'</pre>';
$fecha = date('YmdHis');
$nombreArchivo = 'lista_precios-' . $fecha . '.pdf';
// echo '<pre>Hola: Tarola';
// print_r($_SESSION);
// // print_r($_SESSION['direccion_empresa']);
// echo '</pre>';
// <img src="data:' . $logoBase64 . '" alt="Logo Empresa" style="height:40px;">

$nombreEmpresa = isset($_SESSION['nombre_empresa']) ? $_SESSION['nombre_empresa'] : 'Mi Empresa';
$emailEmpresa = isset($_SESSION['email_empresa']) ? $_SESSION['email_empresa'] : 'ventas@miempresa.com';  
$direccionEmpresa = isset($_SESSION['domicilio_empresa']) ? $_SESSION['domicilio_empresa'] : 'Dirección de la Empresa';
$telefonoEmpresa = isset($_SESSION['telefono_empresa']) ? $_SESSION['telefono_empresa'] : 'Teléfono de la Empresa';
$cuitEmpresa = isset($_SESSION['cuit_empresa']) ? $_SESSION['cuit_empresa'] : 'CUIT de la Empresa';
$logo = '_img/logo_'.$cuitEmpresa.'.jpg'; // logo por cuit
   //$html = $articulos->mostrar_articulo_lista_pdf($arrParametros,"listaprecioPDF");
// * fin 


// ... (Toda tu lógica previa de obtención de datos)

$html = $articulos->mostrar_articulo_lista_pdf($arrParametros, "listaprecioPDF");


// HEADER SIMPLIFICADO (Sin clases, solo estilos inline para asegurar visión)

// armar el header en base a lo que tengo filtrado.
$filtro_jerarquia = "TODOS LOS PRODUCTOS";
if(!empty($_GET['categoria'])) {
    $filtro_jerarquia = $categoriaTexto; // el texto del filtro para mostrar en el pdf
    if(!empty($_GET['rubro'])) $filtro_jerarquia .= " > " . $rubroTexto;
    if(!empty($_GET['subrubro'])) $filtro_jerarquia .= " > " . $subRubroTexto;
    if(!empty($_GET['marca'])) $filtro_jerarquia .= " > " . $marcaTexto;
}


// 2. Estado de TACC en el Filtro
$txtFiltroTacc = "TODOS";
if (isset($_GET['tacc']) && $_GET['tacc'] == 'Si') {
    $txtFiltroTacc = '<span style="color: #e11d48; font-weight: bold;">SIN TACC</span>';
}

// 3. Identificación del Cliente
$displayCliente = "GENERAL / CONSUMIDOR FINAL";
if (!empty($_GET['clienteTexto']) && isset($clienteTexto)) {
    $displayCliente = $clienteTexto;
}

// --- CONSTRUCCIÓN DEL HTML ---

$htmlHeader = '
<table width="100%" cellpadding="0" cellspacing="0" style="font-family: sans-serif;">
    <tr>
        <td style="border-bottom: 2px solid #1a2a3a; padding-bottom: 10px;">
            <table width="100%">
                <tr>
                    <td width="20%"><img src="'.$logo.'" style="width: 120px;"></td>
                    <td width="50%" align="center">
                        <div style="font-weight: bold; font-size: 18pt;">' . $nombreEmpresa . '</div>
                        <div style="font-size: 9pt; color: #475569;">' . $direccionEmpresa . ' | ' . $telefonoEmpresa . '</div>
                    </td>
                    <td width="30%" align="right">
                        <table align="right">
                            <tr>
                                <td style="background: #1a2a3a; color: white; padding: 5px 15px; font-weight: bold; border-radius: 6px; font-size: 12pt;">
                                    Lista de Precios
                                </td>
                            </tr>
                        </table>
                        <div style="font-size: 7pt; margin-top: 5px;">Emisión: ' . date('d/m/Y H:i') . '</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="background-color: #f1f5f9; border: 1px solid #e2e8f0; padding: 5px 10px;">
            <table width="100%" style="font-size: 8pt; color: #334155;">
                <tr>
                    <td width="40%"><strong>FILTROS:</strong> ' . strtoupper($filtro_jerarquia) . '</td>
                    <td width="30%" align="center"><strong>CLIENTE:</strong> ' . $displayCliente . '</td>
                    <td width="30%" align="right"><strong>TACC:</strong> ' . $txtFiltroTacc . '</td>
                </tr>
            </table>
        </td>
    </tr>
</table>';


$htmlFooter = '
<table width="100%" style="border-top: 1px solid #ccc; font-family: sans-serif; font-size: 8pt; color: #666; padding-top: 5px;">
    <tr>
        <td>Esta lista es exclusiva para clientes mayoristas.</td>
        <td align="center">Generado por AdministraNET</td>
        <td align="right">Página {PAGENO} de {nbpg}</td>
    </tr>
</table>';

ob_start(); // Iniciamos buffer para evitar cualquier salida accidental

// 1. CONFIGURACIÓN CRÍTICA DE MEMORIA (PHP 5.6)
ini_set("memory_limit", "1024M");
set_time_limit(120);
$stylesheet = file_get_contents(__DIR__.'/_css/pdf.css');



$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A3-L',
    'margin_top' => 48,
    'margin_header' => 10,
    'compress' => true,           // Comprime el PDF final
    'packTableData' => true,      // Fundamental para tablas largas
    'simpleTables' => true,       // Si no usas celdas combinadas (colspan/rowspan) dentro del body
    'useSubstitutions' => false,  // <--- CRÍTICO: Evita que mPDF busque fuentes alternativas para cada caracter
    'useActiveForms' => false,    // Desactiva formularios activos
]);

// Esto evita que mPDF "vuelva loco" al procesador buscando fuentes
$mpdf->backupSubsFont = array();

// 1. Cargamos el CSS
$stylesheet = file_get_contents(__DIR__.'/_css/pdf.css');
session_write_close(); // Cerramos sesión para liberar el lock y evitar problemas de concurrencia
$mpdf->WriteHTML($stylesheet, 1);

// 2. ACTIVAMOS CABECERA Y PIE DE FORMA PERSISTENTE
// Estas funciones le dicen a mPDF: "guarda esto y úsalo en CADA hoja que generes"
$mpdf->SetHTMLHeader($htmlHeader);
$mpdf->SetHTMLFooter($htmlFooter);

// // 3. PROCESAMIENTO POR BLOQUES
// if (strpos($html, '<tbody>') !== false) {
//     $partes = explode('<tbody>', $html);
    
//     // El inicio de la tabla (solo el <table> y <thead>)
//     $inicio_tabla = $partes[0] . '<tbody>';
    
//     $cuerpo_y_fin = explode('</tbody>', $partes[1]);
//     $filas_puras = $cuerpo_y_fin[0];
//     $fin_tabla = '</tbody>' . $cuerpo_y_fin[1];

//     // Ya NO hacemos WriteHTML($inicio_doc). 
//     // Empezamos directo con la tabla:
//     $mpdf->WriteHTML($inicio_tabla);

//     // Bucle de filas de a 100
//     $array_filas = explode('</tr>', $filas_puras);
//     $bloque_acumulado = "";
//     $contador = 0;

//     foreach ($array_filas as $fila) {
//         if (trim($fila) == "") continue;
//         $bloque_acumulado .= $fila . '</tr>';
//         $contador++;

//         if ($contador % 100 == 0) {
//             $mpdf->WriteHTML($bloque_acumulado);
//             $bloque_acumulado = ""; 
//         }
//     }
    
//     if ($bloque_acumulado != "") {
//         $mpdf->WriteHTML($bloque_acumulado);
//     }
//     $mpdf->WriteHTML($fin_tabla);
// }

session_write_close();

// --- 2. PROCESAMIENTO POR BLOQUES OPTIMIZADO ---
if (strpos($html, '<tbody>') !== false) {
    $partes = explode('<tbody>', $html);
    
    // Forzamos table-layout: fixed. Esto es lo que baja el tiempo de 600s a 30s.
    // IMPORTANTE: Definí los % de ancho en tus <th> dentro de mostrar_articulo_lista_pdf
    $inicio_tabla = str_replace('<table', '<table style="table-layout: fixed; width: 100%; border-collapse: collapse;"', $partes[0]) . '<tbody>';
    
    $cuerpo_y_fin = explode('</tbody>', $partes[1]);
    $filas_puras = $cuerpo_y_fin[0];
    $fin_tabla = '</tbody>' . $cuerpo_y_fin[1];

    // Iniciamos la tabla
    $mpdf->WriteHTML($inicio_tabla);

    // Bucle de filas optimizado
    // $array_filas = explode('</tr>', $filas_puras);
    // $bloque_acumulado = "";
    // $total_filas = count($array_filas);

    // for ($i = 0; $i < $total_filas; $i++) {
    //     $fila = $array_filas[$i];
    //     if (trim($fila) == "") continue;

    //     $bloque_acumulado .= $fila . '</tr>';

    //     // Procesamos cada 150 filas (un bloque más grande suele ser más rápido)
    //     if ($i > 0 && $i % 150 == 0) {
    //         $mpdf->WriteHTML($bloque_acumulado);
    //         $bloque_acumulado = ""; 
    //     }
    // }
    $array_filas = explode('</tr>', $filas_puras);
    $bloque_acumulado = "";
    $total_filas = count($array_filas);

    for ($i = 0; $i < $total_filas; $i++) {
        $fila = $array_filas[$i];
        if (trim($fila) == "") continue;

        // 1. Lógica Zebra Manual
        // Si la fila es par, le ponemos un gris muy clarito (#f8fafc), si es impar, blanco.
        $fondo = ($i % 2 == 0) ? 'background-color: #f8fafc;' : 'background-color: #ffffff;';
        
        // 2. Inyectamos el estilo directamente en el tag <tr>
        // Esto es mucho más rápido para mPDF que procesar selectores CSS complejos
        $fila_con_estilo = str_replace('<tr', '<tr style="' . $fondo . '"', $fila);

        $bloque_acumulado .= $fila_con_estilo . '</tr>';

        // Procesamos bloques de 150
        if ($i > 0 && $i % 150 == 0) {
            $mpdf->WriteHTML($bloque_acumulado);
            $bloque_acumulado = ""; 
        }
    }
    // Escribimos el remanente
    if (!empty($bloque_acumulado)) {
        $mpdf->WriteHTML($bloque_acumulado);
    }

    $mpdf->WriteHTML($fin_tabla);
}

// 6. SALIDA

if (ob_get_length()) ob_clean(); // Borra cualquier eco accidental o espacio en blanco

$mpdf->Output($nombreArchivo, 'D');
// $mpdf->Output($nombreArchivo, 'I');


// --- DESACTIVAR MPDF TEMPORALMENTE PARA DEBUG ---
// Comenta las líneas de $mpdf->Output() y $mpdf->WriteHTML() antes de usar esto

// echo "<!DOCTYPE html>
// <html lang='es'>
// <head>
//     <meta charset='UTF-8'>
//     <title>DEBUG: Inspección de Estructura de Lista</title>
//     <style>
//         body { background: #f0f0f0; font-family: sans-serif; margin: 20px; }
//         .debug-section { background: white; margin-bottom: 30px; border: 2px solid #ccc; position: relative; }
//         .label { position: absolute; top: -15px; left: 10px; background: #333; color: white; padding: 2px 10px; font-size: 12px; border-radius: 5px; }
        
//         /* Colores de identificación */
//         .header-box { border-color: red; }
//         .body-box { border-color: green; }
//         .footer-box { border-color: blue; }
        
//         /* Simular tamaño A3 Landscape para ver proporciones */
//         .preview-container { width: 1000px; margin: auto; }
        
//         /* Importar tu CSS real para ver si rompe algo */
//         " . (isset($stylesheet) ? $stylesheet : "") . "
//     </style>
// </head>
// <body>
//     <div class='preview-container'>
//         <h2 style='text-align:center;'>Modo de Inspección de Errores</h2>
//         <p>Si ves espacios en blanco o tablas rotas aquí, mPDF fallará al generar el PDF.</p>

//         <div class='debug-section header-box'>
//             <span class='label'>HEADER (Rojo)</span>
//             " . $htmlHeader . "
//         </div>

//         <div class='debug-section body-box'>
//             <span class='label'>BODY (Verde)</span>
//             " . $html . "
//         </div>

//         <div class='debug-section footer-box'>
//             <span class='label'>FOOTER (Azul)</span>
//             " . $htmlFooter . "
//         </div>
//     </div>
// </body>
// </html>";

exit; // Detenemos todo aquí para ver el resultado en el navegador
