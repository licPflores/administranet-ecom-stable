<?php
// backend para generar diferentes listados 
require_once '../sesion.inc.php';
// funciones
include_once '../_scripts/php/funciones.php';
// * # FUNCIONES

//* funcion para pasar una imagen a base 64

function convertImageToBase64($imagePath){

    // buscar la foto en la base para traerla.
    $imageData = file_get_contents($imagePath);
    
    return base64_encode($imageData);
}

//  funcion para traer las imagenes de un solo tiron para no estar llamando a la base de datos cada vez que necesito una imagen
function listaFotosProducto(&$arrFotos, $listaProductos,$conexion)
    {
        // buscar fotos en base de datos, luego buscar en disco, la que no existe la creo, y debo recuperar la url.
        $conex = $conexion;

        // echo 'lista de fotos=><pre>',print_r($arrFotos),PHP_EOL,'</pre>',PHP_EOL;

        $sqlFotos = "SELECT af.idArt AS idArt, af.url_externo AS url, af.nombre_archivo AS extension        
        FROM articulo_foto af
        JOIN (
            SELECT idArt, MIN(id_articulo_foto) AS min_id
            FROM articulo_foto
            WHERE idArt IN (" . $listaProductos . ")
            GROUP BY idArt
        ) subquery ON af.id_articulo_foto = subquery.min_id
        ORDER BY af.id_articulo_foto DESC";

        // echo 'buscar listado de productos todos lista fotos productos<pre>',print_r($sqlFotos),'</pre>',PHP_EOL;

        $buscarFotos = mysqli_query($conex, $sqlFotos);
        if (!$buscarFotos) {
            echo 'No pude buscar las fotos error:' . mysqli_error($conex) . ' sql::' . $sqlFotos;
            return false;
        }
        if ($buscarFotos) {
            $arrFotoProducto = array();
            while ($f = mysqli_fetch_assoc($buscarFotos)) {
                $arrFotoProducto[$f["idArt"]] = $f;
            }
            // echo 'arrFotoProducto<pre>',print_r($arrFotoProducto),'</pre>',PHP_EOL;
            if (!empty($arrFotoProducto)) {
                // tengo fotos entonces por cada 
                foreach ($arrFotoProducto as $id => $foto) {
                    
                       
                        // echo 'nombre foto<pre>',print_r($arrNombreFoto),'</pre>';
                        $urlFoto = $foto["url"];
                        $arrNombreFoto = explode("|", $foto["extension"]);
                        $a = explode('.jpg', $urlFoto);
                      
                        //echo 'nombre foto<pre>',print_r($arrNombreFoto),'</pre>';
                        if(sizeof($arrNombreFoto)==2){
                            $nombreArchivo = $arrNombreFoto[1];
                            $extension =$arrNombreFoto[0];
                        }
                        if(sizeof($arrNombreFoto)==1){
                            $nombreArchivo = $arrNombreFoto[0];
                            $extension =$a[1];
                        }
                        // nuevo servidor de fotos administraNET
                        // ---------------------------------------------------
                        
                            $a = explode('.jpg', $urlFoto);
                            $arrUrl = explode("|", $urlFoto);
                            $urlGrande = $urlFoto;
                            $urlMediana = $a[0] . "_l." . $extension;
                            $urlChica = $a[0] . "_m." . $extension;
                            $arrExterno = array('urlFoto' => $urlFoto, 'nombreFoto' => $nombreArchivo . '.' . $extension, 'urlGrande' => $urlGrande, 'urlMediana' => $urlMediana, 'urlChica' => $urlChica);
                        
                        // echo 'arrExterno<pre>',print_r($arrExterno),'</pre>',PHP_EOL;
                        // buscar la foto o crearla en el disco.
                       
                        $arrFotos[$id] = $arrExterno;
                    
                }
            }

            // no hay fotos de nadie


        }
        return true;
    }


    // * funcion para crear la foto localmente si no esta
    function obtenerImagenLocal($arrUrl, $directorioDestino) {
        // Obtener el nombre del archivo de la URL
        $urlFoto = $arrUrl['urlFoto'];
        $nombreArchivo = basename($arrUrl['nombreFoto']);
    
        // Ruta completa del archivo en el directorio de destino
        $rutaArchivoLocal = $directorioDestino . '/' . $nombreArchivo;
    
        // Verificar si el archivo ya existe en el directorio de destino
        if (!file_exists($rutaArchivoLocal)) {
            // Descargar la imagen desde la URL
            $contenidoImagen = file_get_contents($urlFoto);
    
            // Guardar la imagen en el directorio de destino
            file_put_contents($rutaArchivoLocal, $contenidoImagen);
        }
    
        // Devolver la ruta local de la imagen
        return $rutaArchivoLocal;
    }

// * pdf directo

function armar_catalogo_producto_pdf($conexion, $param){ 
    $empresa=array(
        "nombre_empresa"=>$_SESSION['nombre_empresa'],
        "domicilio_empresa"=>$_SESSION['domicilio_empresa'],
        "telefono_empresa"=>$_SESSION['telefono_empresa'],
        "email_empresa"=>$_SESSION['email_empresa'],
        "iva_empresa"=>$_SESSION['iva_empresa'],
        "cuit_empresa"=>$_SESSION['cuit_empresa'],
        "iniact_empresa"=> $_SESSION['iniact_empresa'],
        "ingbrutos_empresa"=>$_SESSION['ingbrutos_empresa']    
    );   

    $fotoProducto = $param['imagenProducto'];
    $sizeFotoProducto = $param['sizeFotoProducto'];
    // Validar el filtro
    $where="";
    $orden="articulo.NombreArticulo"; // si ordeno por cate, rubro, subrubro, nombre articulo , o por codigo manual o por codigo sistema.
    $direccionOrden="ASC"; // ASC, DESC
    $usoBultoCerrado = 'No';
    $usaDisplay = 'No';
    $usaEmbalaje ='No';
    $sizeFotoProducto = $param['sizeFotoProducto'];
    $proveedor=null;
    $tacc=null;
    $buscaRapida = $param['buscaRapida'];
    $usaIdManual = $_SESSION["usa_id_manual"];
    $claseBusca = $param['claseBusqueda'];
    $idArt = $param['idArt'];
    // seteos sesion
    if ($_SESSION["utilizaEmbalaje"] == "Si") {
        $usaEmbalaje = "Si";
    }

    if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
        $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
    }

    // display
    if ($_SESSION['utiliza_display'] == "Si") {
        $usaDisplay = $_SESSION['utiliza_display'];
    }





    // Analizar los parámetros
    if($claseBusca == ''){
        if ($param['categoria'] != null && $param['categoria'] != "") {
            $where .= " AND cat.id_categoria=" . $param['categoria'] . PHP_EOL;
        }
        if ($param['rubro'] != null && $param['rubro'] != "") {
            $where .= " AND articulo.CodigoRubro=" . $param['rubro'] . PHP_EOL;
        }
        if ($param['subrubro'] != null && $param['subrubro'] != "") {
            $where .= " AND articulo.IDSubRubro=" . $param['subrubro'] . PHP_EOL;
        }
        if ($param['marca'] != null && $param['marca'] != "") {
            $where .= " AND articulo.CodigoMarca=" . $param['marca'] . PHP_EOL;
        }
        if ($param['modelo'] != null && $param['modelo'] != "") {
            $where .= " AND articulo.CodigoModelo=" . $param['modelo'] . PHP_EOL;
        }
        if ($param['laboratorio'] != null && $param['laboratorio'] != "") {
            $where .= " AND articulo.CodLaboratorio=" . $param['laboratorio'] . PHP_EOL;
        }
        if ($param['tacc'] != null && $param['tacc'] != "") {
            $where .= " AND articulo.sin_tacc='" . $param['tacc'] . "'" . PHP_EOL;
        }
        if ($param['proveedor'] != null && $param['proveedor'] != "") {
            $where .= " AND articulo.CodigoProveedor='" . $param['proveedor'] . "'" . PHP_EOL;
        }
    }

    // busqueda rapida de producto
    if($claseBusca !== ''){
       
        // buscar por codigo.
        if($claseBusca=='codigo'){
            $where ="articulo.IDArt = ".$idArt.PHP_EOL;
            $where ="";
            if ($usaIdManual == "Si") {

                $where .=  " AND articulo.id_manual='{$idArt}'";
            }

            if ($usaIdManual == "No") {
                $where .= " AND articulo.IDArt='{$idArt}'";
            }
        }
        // buscar por palabras
        if($claseBusca=='texto'){

           // $where .= " AND articulo.NombreArticulo LIKE '%" . $param['busqueda'] . "%'" . PHP_EOL;
            preg_match_all('/\w+/', $buscaRapida, $matches);    // match words
            $matchesUnique = array_unique($matches[0]); // get new array w/o duplicates

            $listaPalabras = join('%', $matchesUnique);
            $listaPalabrasReves = join('%',array_reverse($matchesUnique));



            if (sizeof($matchesUnique) > 1) {
                $listaPalabras = join('%', $matchesUnique);
            }

            // 1 solo elemento a buscar
            if (sizeof($matchesUnique) == 1) {
                $listaPalabras = $matchesUnique[0];
                $listaPalabrasReves="";
            }


            // buscar por el nombre del producto
            $filtro =" AND (";
            $filtro .= " articulo.NombreArticulo LIKE '%{$listaPalabras}%' ";
            // y por el nombre ecomm(debo hacerlo porque asi se muestra en ela busca rapida.)
            $filtro .= " OR ecom.nombre_articulo_ecom LIKE '%{$listaPalabras}%' ";
            if ($usaIdManual == "Si") {
                //busca incluye texto
                $filtro .=  " OR articulo.id_manual LIKE '%{$listaPalabras}%' ";
            }

            if ($usaIdManual == "No") {
                $filtro .= " OR articulo.IDArt LIKE '%{$listaPalabras}%'";
            }

            // lista palabras al reves.
            if($listaPalabrasReves!=""){
                $filtro .= " OR articulo.NombreArticulo LIKE '%{$listaPalabrasReves}%' ";
                // y por el nombre ecomm(debo hacerlo porque asi se muestra en ela busca rapida.)
                $filtro .= " OR ecom.nombre_articulo_ecom LIKE '%{$listaPalabrasReves}%' ";
                if ($usaIdManual == "Si") {
                    //busca incluye texto
                    $filtro .=  " OR articulo.id_manual LIKE '%{$listaPalabrasReves}%' )";
                }

                if ($usaIdManual == "No") {
                    $filtro .= " OR articulo.IDArt LIKE '%{$listaPalabrasReves}%'";
                }
            }
            $filtro .=" )";
            $where .=$filtro;
            if ($param['marca'] != null && $param['marca'] != "") {
                $where .= " AND articulo.CodigoMarca=" . $param['marca'] . PHP_EOL;
            }
            if ($param['modelo'] != null && $param['modelo'] != "") {
                $where .= " AND articulo.CodigoModelo=" . $param['modelo'] . PHP_EOL;
            }
            if ($param['laboratorio'] != null && $param['laboratorio'] != "") {
                $where .= " AND articulo.CodLaboratorio=" . $param['laboratorio'] . PHP_EOL;
            }
            if ($param['tacc'] != null && $param['tacc'] != "") {
                $where .= " AND articulo.sin_tacc='" . $param['tacc'] . "'" . PHP_EOL;
            }
            if ($param['proveedor'] != null && $param['proveedor'] != "") {
                $where .= " AND articulo.CodigoProveedor='" . $param['proveedor'] . "'" . PHP_EOL;
            }
        }
    }





    $sqlArticulo = "SELECT 
                        articulo.id_manual,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,
                        articulo.IDArt,
                        articulo.lote,
                        #articulo.detalle_web,
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoProveedor, 
                        articulo.CodigoRubro,
                        articulo.CodigoMarca,
                        cat.id_categoria,
                        
                        articulo.NombreArticulo,
                        #articulo.promocion,
                        #articulo.promocion_por,
                        #articulo.promocion_cant,
                        #articulo.promocion_alcance,
                        #articulo.promocion_tipo,
                        #articulo.promocion_listaoficial,
                        #articulo.promocion_lista1,
                        #articulo.promocion_lista2,
                        #articulo.promocion_lista3,
                        #articulo.promocion_lista4,
                        #articulo.promocion_lista5,
                        #articulo.promocion_destacado_web,
                        #articulo.promocion_vigencia_desde,
                        #articulo.promocion_vigencia_hasta,
                        
                        #DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                        #DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,                       
                        rubro.NombreRubro AS NombRub, 
                        subrubro.NombreSubRubro AS NombSubRub,
                        cat.nombre_categoria AS NombCat,
                        articulo.NroCodBarra,
                        articulo.NroCodBarraF,                        
                        articulo.nro_cod_barra_display,
                        articulo.nro_cod_barra_bulto,
                        articulo_prov.multiplicador_comp,
                        articulo_prov.cantidad_uni, 
                        unidmed.descrip_corta AS nombre_unimed,                        
                        articulo_prov.id_presentacionC, 
                        articulo_prov.id_unimed,                        
                        articulo_prov.cantidad_unidad_display,
                        articulo_prov.cantidad_display_bulto,
                        articulo_prov.cantidad_bulto_pallet,
                        articulo_prov.recargo_fraccion,
                        articulo_prov.recargo_fraccion_porcentaje,
                        articulo_prov.cantidad_unidad_lista2,
                        articulo_prov.cantidad_unidad_lista3,
                        articulo_prov.precio_unidad AS tipoPrecioUnidad,
                        articulo.cantidad_promedio_bulto,
                        mart.tipo_unidad,
                        mart.descrip_corta AS uniArt, 
                        presentacion_abmV.nombre_presentacion AS nombre_presentacion_vta,                 
                        
                        articulo.ecommerce,
                        #ecom.descuento_solo_web,
                        #ecom.promo_solo_web,
                        #ecom.vigencia_desde_solo_web,
                        #ecom.vigencia_hasta_solo_web,
                        #ecom.destacado_ecom,
                        #ecom.detalle_ecom,
                        ecom.nombre_articulo_ecom,
                        #ecom.link_articulo_ecom,
                        #ecom.link_video_articulo_ecom,
                        #ecom.garantia_articulo,
                        ecom.usa_nombre_articulo_ecom
                        
                        
                        FROM articulo
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt)
                            #LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)                           
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                           
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm AS presentacion_abmV ON (presentacion_abmV.id_presentacion = articulo.id_presentacionV)                            
                            #LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
                            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
                            
                        WHERE
                            articulo.tipo_art='Articulo'
                            AND articulo.Discontinuo='No' 
                            AND articulo.disponible_vta='Si'
                            AND rubro.anulado='No'
                            AND subrubro.anulado='No'
                            AND cat.anulado='No'
                        {$where}
                    ORDER BY {$orden} {$direccionOrden};";
$sqlArticulo;
//exit();
    $hacer = mysqli_query($conexion, $sqlArticulo);
    $arrArticulo = array();
    if ($hacer) {
        while ($producto = mysqli_fetch_assoc($hacer)) {
            $arrArticulo[$producto['IDArt']] = $producto;
        }
    }
    if(!$hacer){
        $vuelta = array('error' => 'Error al buscar los productos', 'sql' => $sqlArticulo,'descripcion'=>mysqli_error($conexion));
        echo json_encode($vuelta);
        exit;
    }

    // obteniendo las imagenes
    $arrFotos = array();
    $arrClaves = array_keys($arrArticulo);
    $listaProductos = implode(",", array_keys($arrArticulo));   
    // llenando las fotos
    foreach ($arrClaves as $id => $clave) {
        $arrFotos[$clave] = array('urlFoto' => '../_img/sinfoto.jpg', 'nombreFoto' => '../_img/sinfoto' . '.' . 'jpg', 'urlGrande' => '../_img/sinfoto.jpg', 'urlMediana' => '../_img/sinfoto.jpg', 'urlChica' => '../_img/sinfoto.jpg');
    }

    $hagoFotos = listaFotosProducto($arrFotos, $listaProductos,$conexion);
    $tamano_imagen = $sizeFotoProducto; // Cambia este valor según sea necesario

    // Definir las dimensiones y el tamaño de fuente en función del tamaño de la imagen
    switch ($tamano_imagen) {
        case "chica":
            $imagen_width = 100;
            $imagen_height = "auto";
            $fuente_size = "14px";
            $urlImagen = "urlChica";
            $dirImagen = "../_img/productos/miniatura/xs";
            break;
        case "mediana":
            $imagen_width = 150;
            $imagen_height = "auto";
            $fuente_size = "16px";
            $urlImagen = "urlMediana";
            $dirImagen = "../_img/productos/miniatura";
            break;
        case "grande":
            $imagen_width = 500;
            $imagen_height = "auto";
            $fuente_size = "20px";
            $urlImagen = "urlGrande";
            $dirImagen = "../_img/productos";

            break;
        default:
            $imagen_width = 100;
            $imagen_height = "auto";
            $fuente_size = "14px"; 
            $urlImagen = "urlChica";
            $dirImagen = "../_img/productos/miniatura/xs";
    }
    
    $clase_imagen = "imagen-" . $tamano_imagen;
    $clase_texto = "texto-" . $tamano_imagen;
    // Generar el contenido del PDF
    $html = '<div class="catalogo-productos">'.PHP_EOL;
    
    $html .= '<table class="table">'.PHP_EOL;
    $html .= '<thead>'.PHP_EOL;
    $html .= '<tr>'.PHP_EOL;
    $html .= '<th>Imagen</th>'.PHP_EOL;
    $html .= '<th>Detalles del Producto</th>'.PHP_EOL;
    $html .= '</tr>'.PHP_EOL;
    $html .= '</thead>'.PHP_EOL;
    $html .= '<tbody>'.PHP_EOL;

    foreach ($arrArticulo as $arti) {
        $idArt = $arti['IDArt'];
        
        
        $arrExterno = $arrFotos[$idArt];
        
        $html .= '<tr>'.PHP_EOL;
        $html .= '<td class="product-image">'.PHP_EOL;
        if ($fotoProducto == 'Si') {
            $urlFotoCompleta = obtenerImagenLocal($arrExterno, $dirImagen);
               // $tamFoto = 50;
            $html .= '<img src="'.$urlFotoCompleta.'" alt="'. $arti['NombreArticulo'] .'" class="'.$clase_imagen.'">'.PHP_EOL;
            
        }
        $html .= '</td>'.PHP_EOL;
        $html .= '<td class="product-details '.$clase_texto.'">'.PHP_EOL;
        
        $html .= ' <div class="product-name">' . $arti['NombreArticulo'] . '</div>'.PHP_EOL;
        $html .= '<div class="product-code">Cod Sistema: ' . $arti['IDArt'] . '</div>'.PHP_EOL;
        if($arti['id_manual']!=""){
            $html .= '<div class="product-code">Cod Manual: ' . $arti['id_manual'] . '</div>'.PHP_EOL;
        }
        // $html .= '<div class="product-code">Cod Manual: ' . $arti['id_manual'] . '</div>'.PHP_EOL;
        if($arti['Marca']!=""){
            $html .= '<div class="product-brand">Marca: <strong>' . $arti['Marca'] . '</strong></div>'.PHP_EOL;
        }
        // $html .= '<div class="product-brand">Marca: ' . $arti['Marca'] . '</div>'.PHP_EOL;
        if($arti['Modelo']!=""){
            $html .= '<div class="product-brand">Modelo: <strong>' . $arti['Modelo'] . '</strong></div>'.PHP_EOL;
        }
        // $html .= '<div class="product-brand">Modelo: ' . $arti['Modelo'] . '</div>'.PHP_EOL;

        if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
            $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
            $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
            $cantidadUnidadMinimaCaja = 1;
            $cantidadMinimaFinal = 1;
            $tipoUnidad = 'Unidad'; // valor por defecto



            if ($arti['tipoPrecioUnidad'] != '') {
                $tipoUnidad = $arti['tipoPrecioUnidad']; // como viene el precio descuento
            }

            // display
            if ($arti['cantidad_unidad_display'] != 0 && $arti['cantidad_unidad_display'] != null) {
                $cantidadUnidadDisplay = (int)$arti['cantidad_unidad_display'];
            }

            // bulto
            if ($arti['cantidad_display_bulto'] != 0 && $arti['cantidad_display_bulto'] != null) {
                $cantidadDisplayBulto = $arti['cantidad_display_bulto'];
            }

            $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.

            //*  unidad :: sacamos la unidad porque no se usa
            // precios en unidad
            if ($tipoUnidad == 'Unidad') {
                $html .= '<div class="product-unit">Presentación: <strong>Unidad x 1 </strong></div>'.PHP_EOL;
            }
            //* display

            if ($tipoUnidad == 'Display') {
                $html .= '<div class="product-unit">Presentación: <strong>Display x ' . round($cantidadUnidadDisplay, 0) . '</strong></div>'.PHP_EOL;
            }
            //* bulto
            if ($tipoUnidad == 'Bulto') {

                $html .= '<div class="product-unit">Presentación: <strong>Bulto x ' . round($cantidadUnidadMinimaCaja, 0) . '</strong> </div>'.PHP_EOL;
            }
        }
        
        $html .= '<div class="product-category">Categoría: <strong>' . $arti['NombCat'] . '</strong></div>'.PHP_EOL;
        $html .= '<div class="product-category">Rubro: <strong>' .  $arti['NombRub'] . '</strong></div>'.PHP_EOL;
        $html .= '<div class="product-category">SubRubro: <strong>' .  $arti['NombSubRub'] . '</strong></div>'.PHP_EOL;

        
        // if (!empty($arti['CodigoBarras'])) {
        //     $html .= '<h5>Cod Barras: ' . $arti['CodigoBarras'] . '</h5>';
        // }
        $html .= '</td>'.PHP_EOL;
        $html .= '</tr>'.PHP_EOL;
    }

    $html .= '</tbody>'.PHP_EOL;
    $html .= '</table>'.PHP_EOL;
    $html .= '</div>'.PHP_EOL;


    // Definir el CSS directamente en la variable $stylesheet
    $stylesheet = "    /* Colores */
    .azul-primario {
        color: #2A3E72;
    }
    .gris-oscuro {
        color: #333333;
    }
    .gris-medio {
        color: #555555;
    }
    .gris-claro {
        background-color: #F2F2F2;
    }

    /* Estilos generales */
    body {
        font-family: 'Roboto', sans-serif;
        font-size: 12px;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    
    .logo{
        width: 100px;
        height: auto;
        margin-right: 10px;
        vertical-align: middle;
        float: left;
        }
    .company-name {
        font-size: 26px;
        font-weight: bold;
        color: #2A3E72; /* Azul primario */
        margin: 0;
        display: inline-block;
        vertical-align: middle; /* Alinea el texto con el logo */
        float:left;
    }

    .catalog-title {
        font-size: 18px;
        font-weight: bold;
        color: #333333; /* Gris oscuro */
        margin: 10px 0;
        text-align: center;
    }

    /* Estilos para la tabla */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px; /* Espacio vertical entre filas */
        margin-bottom: 20px;
    }

    .table th, .table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
        vertical-align: top; /* Alineación vertical superior */
    }

    .table th {
        background-color: #F2F2F2; /* Gris claro */
        font-size: 14px;
        font-weight: bold;
        color: #333333; /* Gris oscuro */
    }

    .table td {
        background-color: #fff; /* Fondo blanco */
    }

    /* Estilos para las imágenes */
    .imagen-chica {
        width: 100px;
        height: auto;
    }
    .imagen-mediana {
        width: 200px;
        height: auto;
    }
    .imagen-grande {
        width: 400px;
        height: auto;
    }

    /* Estilos para el texto junto a la imagen */
    .texto-chica {
        font-size: 14px;
    }
    .texto-mediana {
        font-size: 16px;
    }
    .texto-grande {
        font-size: 20px;
    }

    /* Estilos para los detalles del producto */
    .product-details div {
        margin: 5px 0;
    }

    .product-name {
        font-weight: bold;
        color: #2A3E72; /* Azul primario */
        margin-bottom: 10px;
    }

    .product-code {
        font-weight: bold;
        color: #333333; /* Gris oscuro */
        margin-bottom: 5px;
    }

    .product-brand {
        color: #555555; /* Gris medio */
        margin-bottom: 5px;
    }

    .product-unit, .product-category {
        color: #555555; /* Gris medio */
        margin-bottom: 5px;
    }
    ";

    require_once  '../_lib/mpdf2/vendor/autoload.php';
    $config_fonts=array('default_font' => 'roboto',
    'fontdata' => [
        'roboto' => [
            'R' => 'Roboto-Regular.ttf',
            'B' => 'Roboto-Bold.ttf',
            'I' => 'Roboto-Italic.ttf',
            'BI' => 'Roboto-BoldItalic.ttf',
        ]
    ],
    'default_font_size' => 12,);
    
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'c',
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_top' => 30,
        'margin_bottom' => 5,
        'margin_header' => 3,
        'margin_footer' => 7,
        'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
            __DIR__ . '/../fonts',
        ]),
        'fontdata' => $config_fonts['fontdata'],
        'default_font' => $config_fonts['default_font'],
        'default_font_size' => $config_fonts['default_font_size'],
    ]);
        // Agregar encabezado y pie de página
        $htmlHeader ='';
        $htmlHeader .= '<div class="header">'.PHP_EOL;
        // $html .= ' <div>'.PHP_EOL;
        $htmlHeader .= '      <div class="logo">'.PHP_EOL;
        $htmlHeader .='           <img src="'.traeLogo($conexion,"../_img/",$empresa['cuit_empresa']).'" alt="'.$empresa['nombre_empresa'].'" title="'.$empresa['nombre_empresa'].'" />'.PHP_EOL;
        $htmlHeader .= '      </div>';
        $htmlHeader .= '      <div class="company-name">' . $empresa['nombre_empresa'] . '</div>'.PHP_EOL;
         $htmlHeader .= '</div>'.PHP_EOL;
        $htmlHeader .= '      <div class="catalog-title">'.PHP_EOL;
        $htmlHeader .= '        Catálogo de Productos al '.date('d/m/Y').PHP_EOL;
        $htmlHeader .= '      </div>'.PHP_EOL;
        $htmlHeader .= '</div>'.PHP_EOL;

        $mpdf->SetHTMLHeader($htmlHeader);

        $mpdf->SetHTMLFooter('
            <div style="text-align: center;">
                Página {PAGENO} de {nb} - generado por administraNET - https://www.administranet.com.ar - 
            </div>
        ');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($html,2);
        $mpdf->SetDisplayMode('fullpage');
        // file_put_contents('../log/pdf-html_catalogo_'.date('Y-m-d-h-i-s').'.html','<html><header><style>'.$stylesheet.'</style></header><body>'.PHP_EOL.$html.'</body>');
       // Guardar el PDF en el servidor y devolver la URL
        $pdfFilePath = 'catalogo-productos-' . date('Y-m-d_h_i') . '.pdf';
        // echo json_encode(array('url' => '_tmp/catalogo-productos-' . date('Y-m-d_h_i') . '.pdf'));
        $mpdf->Output($pdfFilePath, 'D');
    //    $mpdf->Output($pdfFilePath);

            
    
        // Devolver la URL del PDF generado
       
}

// echo 'Posteo<pre>',print_r($_POST),'</pre>',PHP_EOL;
// * # ACCIONES

// armar pdf directo

if(isset($_POST['listaCatalogoProducto'])&&$_POST['listaCatalogoProducto']==2){
    echo '<pre>',print_r($_POST),'</pre>';
    // exit;

    $categoria = null;
    $rubro          = null;
    $subRubro       = null;
    $marca          = null;
    $modelo         = null;
    $laboratorio    = null;
    $proveedor = null;
    $tacc = null;
    $buscRapida     = null; // nombre del producto
    $idArt          = null;
    $imagenProducto = null;
    $sizeFotoProducto = null;
    $ordenarPor = null;
    $direccionOrden = null;
    $promo          = null;// solo promociones....
    $claseBusqueda=null;
    $idArt=null;
    $busquedaRapida=null;
    
    $arrParametros = array();

    // "buscarProducto"    : 1,                               
    // "queArticulo"       : nombreArticulo,                   
    // "idArticulo"        : idArticulo,                   
    // "claseBusca"        : claseBusca, texto o codigo

    /*
    [claseBusca] => codigo
    [idArticulo] => 2715
    [nombreArticulo] => ALFAJOR AGUILA MINITORTA BLANCO 69GR (COD 2715) Cod: 2715
    [listaCatalogoProducto] => 1
    [categoria] => 1
    [rubro] => 16
    [subrubro] => 338
    [marca] => 
    [laboratorio] => 
    [imagenProducto] => Si
    [sizeFoto] => mediana
    [ordenarPor] => manual
    */


    if (isset($_POST["queCampo"])) {
        $queCampo = $_POST["queCampo"];
    }

    if (isset($_POST['categoria']) && $_POST['categoria'] != "") {
        $categoria = $_POST['categoria'];
    }
    if (isset($_POST['rubro']) && $_POST['rubro'] != "") {
        $rubro = $_POST['rubro'];
    }
    if (isset($_POST['subrubro']) && $_POST['subrubro'] != "") {
        $subRubro = $_POST['subrubro'];
    }
    if (isset($_POST['marca']) && $_POST['marca'] != "") {
        $marca = $_POST['marca'];
    }
    if (isset($_POST['modelo']) && $_POST['modelo'] != "") {
        $modelo = $_POST['buscaModelo'];
    }
    if (isset($_POST['laboratorio']) && $_POST['laboratorio'] != "") {
        $laboratorio = $_POST['buscaLaboratorio'];
    }
    
    if (isset($_POST['idArticulo']) && $_POST['idArticulo'] != "") {
        $idArt = $_POST['idArticulo'];
    }
   
    if (isset($_POST['promo']) && $_POST['promo'] == "1") {
        $promo = $_POST['promo'];
    }
    if(isset($_POST['proveedor'])&& $_POST['proveedor'] != ""){
        $proveedor = $_POST['proveedor'];
    }
    if(isset($_POST['tacc'])&& $_POST['tacc'] != ""){
        $tacc = $_POST['tacc'];
    }
    if(isset($_POST['claseBusca'])&& $_POST['claseBusca'] != ""){
        $claseBusqueda = $_POST['claseBusca'];
    }
    if(isset($_POST['nombreArticulo'])&& $_POST['nombreArticulo'] != ""){
        $busquedaRapida = $_POST['nombreArticulo'];
    }
    if(isset($_POST['imagenProducto'])){
        $imagenProducto = $_POST['imagenProducto'];
    }

    if(isset($_POST['sizeFoto'])){
        $sizeFotoProducto = $_POST['sizeFoto'];
    }

    if(isset($_POST['ordenarPor'])){
        $ordenarPor = $_POST['ordenarPor'];
    }
    if(isset($_POST['direccionOrden'])){
        $direccionOrden = $_POST['direccionOrden'];
    }
    
    $arrParametros['categoria'] = $categoria;
    $arrParametros['rubro']          = $rubro;
    $arrParametros['subrubro']       = $subRubro;
    $arrParametros['marca']          = $marca;
    $arrParametros['modelo']         = $modelo;
    $arrParametros['laboratorio']    = $laboratorio;
    $arrParametros['proveedor'] = $proveedor;
    $arrParametros['tacc'] = $tacc;
    
    $arrParametros['idArt']          = $idArt;
    $arrParametros['buscaRapida']          = $busquedaRapida;    
    $arrParametros['claseBusqueda']  = $claseBusqueda; // si busco por texto o id articul o texto,codigo
    // $arrParametros['tipoCliente']    = $tipoCliente;
    
    // $arrParametros['queCampo']       = $queCampo;
    // $arrParametros['promo']          = $promo;
    
    
    $arrParametros['imagenProducto'] = $imagenProducto;
    $arrParametros['sizeFotoProducto'] = $sizeFotoProducto;
    $arrParametros['ordenarPor'] = $ordenarPor;
    $arrParametros['direccionOrden'] = $direccionOrden;
    


    armar_catalogo_producto_pdf($connV,$arrParametros);
}