<?php
// * BACKEND movimentos de stock.
// * -----------------------------
// * V1: solo movimientos ingreso y edicion de ciertos datos de producto.
// * V2: alta de codigos de barra, tipos de movimientos, imagenes de producto servidor nuevo.

// * Datos del servidor de imagenes
# Bucar Imagen por nombre y string
# --------------------------------------------
# curl -X 'GET' \
#   'https://img.api.administranet.com.ar/imagen/75313f33d72fe2b5d74bcc02c16dab67.jpg' \
#   -H 'accept: application/json'

# vuelta  


#{
#    "Resultado": [
#      "https://img.api.administranet.com.ar/static/7/5/3/1/75313f33d72fe2b5d74bcc02c16dab67.jpg",
#      "https://img.#api.administranet.com.ar/static/7/5/3/1/75313f33d72fe2b5d74bcc02c16dab67_m.jpg",
#      "https://img.api.administranet.com.ar/static/7/5/3/1/75313f33d72fe2b5d74bcc02c16dab67_l.jpg"
#    ]
#  }

# alta de imagen
# -------------------------------------
# curl -X 'POST' \
#  'https://img.api.administranet.com.ar/imagen/?codigo=SanMartin' \
#  -H 'accept: application/json' \
#  -H 'Content-Type: multipart/form-data' \
#  -F 'file=@6_Alex_Ross_JLA_The_Original_Seven_2000 (1).jpg;type=image/jpeg'

# vuelta imagne
#  {
#    "message": "Exito",
#    "destino": "https://img.api.administranet.com.ar/static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5.jpg",
#    "thumbnails": {
#      "m": "https://img.api.administranet.com.ar/static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5_m.jpg",
#      "l": "https://img.api.administranet.com.ar/static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5_l.jpg"
#    }
#  }



# borrar imagen
# -------------------------------------------------------------------------------------------------

# curl -X 'DELETE' \
#  'https://img.api.administranet.com.ar/imagen/625c7e19dbce89db2c5a14120e280da5.jpg?codigo=SanMartin' \
#  -H 'accept: application/json'


# vuelta

#{
#    "Resultado": [
#      {
#        "Exito": "static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5.jpg"
#      },
#      {
#        "Exito": "static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5_m.jpg"
#      },
#      {
#        "Exito": "static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5_l.jpg"
#      }
#    ]
#  }

//include __DIR__ . '/../../sesion.inc.php';

header('Content-Type: application/json');

define('URLFOTO','https://img.api.administranet.com.ar/');
define('CODIGOFOTO','SanMartin');


// * FUNCIONES
// ------------------------------

// * cargar tablas armar pagina anterior.de datos que no se pueden armar.

function logInventario($movimiento,$dato){
    $url ='../log/debug-inventario_'.date('Y-m-d_h_i').'.txt';
    file_put_contents($url,$movimiento.'::  '.$dato.PHP_EOL,FILE_APPEND);
}


// * datos del usuario. recupero y envio datos clave del usuario para su uso .
function datosUsuario()
{
    // echo '<pre>';
    // print_r($_SESSION);
    // echo '</pre>';
    $vendedor = (array) $_SESSION['vendedor'];
    // permisos de inventario.
    $datosCuenta = array(
        'tipo_busqueda' => $_SESSION['tipo_busqueda'],
        'venta_sin_stock' => $_SESSION['venta_sin_stock'],
        'id_caja_efectivo_usr' => $_SESSION['id_caja_efectivo_usr'],
        'id_caja_cheque_usr' => $_SESSION['id_caja_cheque_usr'],
        'id_caja_tarjeta' => $_SESSION['id_caja_tarjeta'],
        'obliga_domicilio_cliente' => $_SESSION['obliga_domicilio_cliente'],
        'todos_clientes' => $_SESSION['todos_clientes'],
        'tipousuario' => $_SESSION['tipousuario'],
        'usa_id_manual' => $_SESSION['usa_id_manual'],
        'lista_precio_defecto' => $_SESSION['lista_precio_defecto'],
        'verStock' => $_SESSION['verStock'],
        'verStockCero' => $_SESSION['verStockCero']

    );

    $permisosInventario = array(
        'seleccion_deposito_inventario'=>  $_SESSION["seleccion_deposito_inventario"], // me deja ver los depositos y cambiarlo
        'accion_inventario'=>$_SESSION["accion_inventario"], // puedo hacer todo, o solo contar o solo cambiar fotos y cod  barra
        'tipo_cuenta_defecto'=>$_SESSION["tipo_cuenta_defecto"], // la forma predeterminada de contar, si por unidad, displya o  bulto
        'visualiza_stock_inventario'=> $_SESSION["visualiza_stock_inventario"], // si veo el stock cuando cuento para evitar fraudes
    );

    $datosEmpresa = array(

        'nombre_empresa' => $_SESSION['nombre_empresa'],
        'telefono_empresa' =>  $_SESSION['telefono_empresa'],
        'cuit_empresa' => $_SESSION['cuit_empresa'],
        'domicilio_empresa' => $_SESSION['domicilio_empresa'],
        'email_empresa' => $_SESSION['email_empresa'],
        'ingbrutos_empresa' => $_SESSION['ingbrutos_empresa'],
        'iniact_empresa' => $_SESSION['iniact_empresa'],
        'whatsapp_empresa' => $_SESSION['whatsapp_empresa'],
        'facebook_messenger_empresa' => $_SESSION['facebook_messenger_empresa'],
        'twitter_empresa' => $_SESSION['twitter_empresa'],
        'direccion_web_empresa' => $_SESSION['direccion_web_empresa'],
        'observaciones_empresa' => $_SESSION['observaciones_empresa'],
        'url_ecommerce_cliente_empresa' => $_SESSION['url_ecommerce_cliente_empresa'],
        'url_ecommerce_vendedor_empresa' => $_SESSION['url_ecommerce_vendedor_empresa'],
        'agente_retib' => $_SESSION['agente_retib'],
        'agente_retg' => $_SESSION['agente_retg'],
        'agente_reti' => $_SESSION['agente_reti'],
        'agente_percep' => $_SESSION['agente_percep'],
        'id_sucursal' => $_SESSION['id_sucursal']

    );

    $vuelta = array('usuario' => $vendedor, 'empresa' => $datosEmpresa, 'cuenta' => $datosCuenta,'permisos'=>$permisosInventario);
    print json_encode($vuelta);
}

/** busqueda rapida */
// * creamos el array que se genera por la busqueda rapida.

function busquedaRapidaArticulos($conn)
{
    // vamos de volver el json con nombre y codigo, codigo, nombre, foto principal.


    $sql = "SELECT
        articulo.IDArt AS id,
        articulo.id_manual AS idManual,
       
        #IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
        #    CONCAT(ecom.nombre_articulo_ecom,' Cod: ',articulo.IDArt),
        #    CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt)) AS articulo,
        
            CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt) AS articulo,    
        foto.url_externo AS url    
        

        FROM articulo 

        #LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt 
        #LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
        #LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
        LEFT JOIN (
            SELECT idArt, MIN(id_articulo_foto) AS min_id
            FROM articulo_foto
            GROUP BY idArt
        ) af_min ON articulo.IDArt = af_min.idArt
        LEFT JOIN articulo_foto AS foto ON af_min.min_id = foto.id_articulo_foto
        


        WHERE 
        articulo.tipo_art='Articulo'     
        AND articulo.Discontinuo='No'    
       

        ORDER BY articulo.NombreArticulo ASC";

    $hacer = mysqli_query($conn, $sql);
    $arrRapido = array();
    if ($hacer) {
        while ($art = mysqli_fetch_assoc($hacer)) {
            $arrRapido[] = $art;
        }
    }

    print json_encode($arrRapido);
}

// * cuando apliques la busqueda rapida o el codigo de barra lo buscamos aca. al producto.
// -- $link: conexoin a la base de datos
// -- $codigo: ID por busqueda rapida o codbarra
// -- $tipoBusca: 'id' o 'barra' // tipo de busqueda si por el cod barra y por el idart.
function traeArticuloBusqueda($link, $arrParam){
    /// traer la funcion del ajax que trae el saldo, no el disponible mas datos de series y lotes.

    // codigo puede no venir y en su lugar un nombre escrito.
    $codigo = null;
    $textoBusco = null;
    $codBarraBusco = null;
    $arrTipoCuentaDefecto = array();
    $depositoWeb = $arrParam['idDeposito'];
    $tipoBusca = $arrParam['tipoBusca']; // id texto barra
    
    $usaIdManual ="No";
    $limite="";

    if(isset($_SESSION["usa_id_manual"])){
        $usaIdManual = $_SESSION["usa_id_manual"];
    }

    
    $where = "";
    $textoBarra="";

    if ($tipoBusca == 'id') {
        // el codigo viene vacio buscar por el input.. partir en palabras
        $codigo = $arrParam['codigo'];
        $limite = "LIMIT 1";

        // busco por el id que tiene que venir de la seleccion.
       
        $where .= " articulo.IdArt ='{$codigo}'";
        $textoBarra ="(CASE
                        WHEN 'Unidad'= articulo_prov.precio_unidad THEN '0'                       
                        WHEN 'Display' = articulo_prov.precio_unidad THEN '1'
                        WHEN 'Bulto' = articulo_prov.precio_unidad THEN '2'
                        ELSE 'Desconocido' 
                        END )AS tipoCuentaDefecto, "    ; 
        
    }

    // busqueda por palabras mixtas..
    if($tipoBusca=='texto'){
        $textoBusco = $arrParam['textoBusco'];
          
            // buscar partido. pero por nombre
        preg_match_all('/\w+/', $textoBusco, $matches);    // match words
        $matchesUnique = array_unique($matches[0]); // get new array w/o duplicates

        $listaPalabras = join('%', $matchesUnique);
        // $filtro .= " AND (articulo.NombreArticulo LIKE '%{$listaPalabras}%' ";


        // $where .= " articulo.IdArt ='{$codigo}'";
        $where .= " (";
        $where .= "    articulo.NombreArticulo LIKE '%{$listaPalabras}%'".PHP_EOL;         
        
        if ($usaIdManual == "Si") {
            //busca incluye texto
            $where .=  " OR articulo.id_manual LIKE '%{$listaPalabras}%' )";
        }

        if ($usaIdManual == "No") {
            $where .= " OR articulo.IDArt LIKE '%{$listaPalabras}%')";
        }
        $textoBarra ="(CASE
                        WHEN 'Unidad'= articulo_prov.precio_unidad THEN '0'                       
                        WHEN 'Display' = articulo_prov.precio_unidad THEN '1'
                        WHEN 'Bulto' = articulo_prov.precio_unidad THEN '2'
                        ELSE 'Desconocido' 
                        END )AS tipoCuentaDefecto, "    ;
    }
    

    
    

    if ($tipoBusca == 'barra') {

        $codBarra = $arrParam['codBarra'];
        $where .= " articulo.NroCodBarra='{$codBarra}' 
                    OR articulo.NroCodBarraF='{$codBarra}' 
                    OR articulo.nro_cod_barra_display='{$codBarra}' 
                    OR articulo.nro_cod_barra_bulto='{$codBarra}'";
         // voy a setear por defecto.           
        $textoBarra ="(CASE
                        WHEN '{$codBarra}'= articulo.NroCodBarra THEN '0'
                        WHEN '{$codBarra}'= articulo.NroCodBarraF THEN '0'
                        WHEN '{$codBarra}' = articulo.nro_cod_barra_display THEN '1'
                        WHEN '{$codBarra}' = articulo.nro_cod_barra_bulto THEN '2'
                        ELSE 'Desconocido' 
                        END )AS tipoCuentaDefecto, "    ;  
        $limite = "LIMIT 1";                   

    }
    // if(defined(CODIGOPRODUCTO)&&CODIGOPRODUCTO=='manual'){
    //     $where = " articulo.id_manual='{$idArt}'";
    // }

    //    echo "dentro";
    $campoReglaPrecio = "";
    $sqlReglaPrecio = "";
    $usoRegla = "No";

    # analisis reglas de precio
    // if (isset($_SESSION["cliente"])) {
    //     //            echo "<br>cliente___::".print_r($_SESSION["cliente"]);
    //     $codCliente = $_SESSION["cliente"];
    //     $usoRegla = $_SESSION["usaReglaPrecio"];
    // }
    // if ($usoRegla == "Si" && $codCliente != null) {
    //     $campoReglaPrecio = "rp.tipo_calculo,rp.importe_regla,rp.id_cliente AS clienteRegla,";
    //     $sqlReglaPrecio = "LEFT JOIN reglas_precio AS rp ON  
    //                     (rp.id_articulo = articulo.IDArt 
    //                     AND rp.id_cliente={$codCliente} 
    //                     AND  ('" . date('Y-m-d') . "' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
    //                     AND rp.anulado='No' )";
    // }
    # deposito con stock, lo busco despues veo si lo muestro.
    // $depositoWeb = $_SESSION['deposito'];


    $sqlArticulo = "SELECT 
                        articulo.id_manual,
                        marca.NombreMarca AS Marca,
                        modelo.NombreModelo AS Modelo,
                        articulo.IDArt,
                        articulo.lote,
                        articulo.detalle_web,
                        articulo.IDSubRubro, 
                        articulo.CodigoSubRubro,
                        articulo.CodigoProveedor, 
                        articulo.CodigoRubro,
                        articulo.CodigoMarca,
                        cat.id_categoria,
                        articulo.Moneda,
                        articulo.CodigoArticuloT,
                        articulo.NombreArticulo,
                        articulo.impuesto_interno,
                        articulo.promocion,
                        articulo.promocion_por,
                        articulo.promocion_cant,
                        articulo.promocion_alcance,
                        articulo.promocion_tipo,
                        articulo.promocion_listaoficial,
                        articulo.promocion_lista1,
                        articulo.promocion_lista2,
                        articulo.promocion_lista3,
                        articulo.promocion_lista4,
                        articulo.promocion_lista5,
                        articulo.promocion_destacado_web,
                        articulo.promocion_vigencia_desde,
                        articulo.promocion_vigencia_hasta,
                        articulo.tipoIVA,
                        iva.Alicuota AS Alic,
                        DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                        DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,                       
                        rubro.NombreRubro AS NombRub, 
                        subrubro.NombreSubRubro AS NombSubRub,
                        cat.nombre_categoria AS NombCat,
                        articulo.Precio1V,
                        articulo.Precio2V,
                        articulo.Precio3V,
                        articulo.Precio4V,
                        articulo.Precio5V,
                        articulo.PNOficial, 
                        articulo.PFOficial,
                        articulo.Precio1VI,
                        articulo.Precio2VI,
                        articulo.Precio3VI,
                        articulo.Precio4VI,
                        articulo.Precio5VI,
                        (articulo.Precio1VI-articulo.Precio1V) AS impIva1,
                        (articulo.Precio2VI-articulo.Precio2V) AS impIva2,
                        (articulo.Precio3VI-articulo.Precio3V) AS impIva3,
                        (articulo.Precio4VI-articulo.Precio4V) AS impIva4,
                        (articulo.Precio5VI-articulo.Precio5V) AS impIva5,
                        (articulo.PFOficial-articulo.PNOficial) AS impOf,
                        (articulo.Precio1V*articulo.impuesto_interno/100) AS imp_interno1,
                        (articulo.Precio2V*articulo.impuesto_interno/100) AS imp_interno2,
                        (articulo.Precio3V*articulo.impuesto_interno/100) AS imp_interno3,
                        (articulo.Precio4V*articulo.impuesto_interno/100) AS imp_interno4,
                        (articulo.Precio5V*articulo.impuesto_interno/100) AS imp_interno5,
                        (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf,
                        {$campoReglaPrecio}
                        {$textoBarra}
                        articulo.impuesto_interno,    
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
                        stock_deposito.saldo,
                        stock_deposito.id_deposito,
                        articulo.ecommerce,
                        ecom.descuento_solo_web,
                        ecom.promo_solo_web,
                        ecom.vigencia_desde_solo_web,
                        ecom.vigencia_hasta_solo_web,
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
                            LEFT JOIN stock_deposito ON (stock_deposito.id_articulo=articulo.IDArt AND stock_deposito.id_deposito={$depositoWeb})
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm AS presentacion_abmV ON (presentacion_abmV.id_presentacion = articulo.id_presentacionV)                            
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
                            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
                            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
                            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
                            {$sqlReglaPrecio} 
                        WHERE
                            {$where}
                        ORDER BY articulo.NombreArticulo {$limite};";
    //  echo "<pre>";
    //  print_r($sqlArticulo);
    //  echo "</pre>";
    $hacer = mysqli_query($link, $sqlArticulo);
    $arrArticulo = array();
    $arrListaArticulo = array();
    $tipoResultado = "unico";
    # articulo con datos
    if($hacer){

        $cuantosArticulos = mysqli_num_rows($hacer);
        // si tengo varios producto armo lista.
        if($cuantosArticulos>1){
            $tipoResultado="lista";
            while($arrArticulo = mysqli_fetch_assoc($hacer)){                
                
            
                // inicio la variables a contar
                $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
                $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
                $cantidadUnidadMinimaCaja = 1;
                // validando
                // dispaly
                if ($arrArticulo['cantidad_unidad_display'] != 0 && $arrArticulo['cantidad_unidad_display'] != null) {
                    $cantidadUnidadDisplay = (int)$arrArticulo['cantidad_unidad_display'];
                }

                // bulto
                if ($arrArticulo['cantidad_display_bulto'] != 0 && $arrArticulo['cantidad_display_bulto'] != null) {
                    $cantidadDisplayBulto = (int)$arrArticulo['cantidad_display_bulto'];
                }

                $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.


                $arrPresentacion  = array(
                    array('idPresentacion' => 0, 'defecto'=>'no', 'nombrePresentacion' => 'Unidad', 'cantidadDisplay' => 1, 'cantidadUnidadMinima' => 1),
                    array('idPresentacion' => 1, 'defecto'=>'no','nombrePresentacion' => 'Display/Presentación', 'cantidadDisplay' => 1, 'cantidadUnidadMinima' => $cantidadUnidadDisplay),
                    array('idPresentacion' => 2, 'defecto'=>'no','nombrePresentacion' => 'Bulto', 'cantidadDisplay' => $cantidadDisplayBulto, 'cantidadUnidadMinima' => $cantidadUnidadMinimaCaja),

                );
                // analizar la opcion por defecto 
                if(key_exists('tipoCuentaDefecto',$arrArticulo)&&$arrArticulo['tipoCuentaDefecto']!='Desconocido'){
                    // voy a buscar la opcion que encontre porque cod barra lo encontre.
                    foreach($arrPresentacion as $key=>$presentacion){
                        if($presentacion['idPresentacion']==$arrArticulo['tipoCuentaDefecto']){
                            $arrPresentacion[$key]['defecto']='si';
                        }
                    }
                }

                # fotos con multi foto.
                $sqlArticuloFoto = "SELECT
                                articulo_foto.id_articulo_foto AS id,
                                articulo_foto.url_externo AS url,
                                articulo_foto.foto_principal AS principal 
                                FROM articulo_foto 
                                WHERE articulo_foto.IdArt=" . $arrArticulo["IDArt"];
                $hacerFoto = mysqli_query($link, $sqlArticuloFoto);
                $arrFotos = array();
                while ($ff = mysqli_fetch_assoc($hacerFoto)) {
                    $arrFotos[] = $ff;
                }

                # si es lote busco los lotes,
                $arrLotes = array();
                if ($arrArticulo["lote"] == 'Si') {
                    // $sqlLote="SELECT             
                    //             lote.id_lote AS id,
                    //             lote.fecha_vto_lote AS vencimiento,
                    //             lote.id_articulo AS idArticulo,
                    //             lote.cod_lote AS nombreLote,  
                    //             ls.stock_lote AS stockLote,    
                    //             ls.id_deposito AS idDeposito
                    //         FROM
                    //             lote 
                    //         LEFT JOIN lote_stock AS ls ON ls.id_lote = lote.id_lote
                    //         WHERE 
                    //             lote.id_articulo = '".$arrArticulo["IDArt"]."'
                    //         AND
                    //             ls.id_deposito =  '".$arrArticulo["id_deposito"]."'
                    //         AND 
                    //             ls.stock_lote>0
                    //         AND     
                    //             NOT ISNULL(ls.id_lote_stock) ";

                    // $hacerLote = mysqli_query($link, $sqlLote);
                    // if($hacerLote){
                    //     $arrLotes=mysqli_fetch_all($hacerLote);
                    // }
                    $vuelta = array('msg' => 'error', 'mensaje' => 'No se puede Ajustar Articulo con Lote');
                    print json_encode($vuelta);
                    exit;
                }
                $arrListaArticulo[]=array('articulo' => $arrArticulo, 'fotos' => $arrFotos, 'lote' => $arrLotes, 'presentacion' => $arrPresentacion);
            }


            
        }

        if($cuantosArticulos==1){
            // $arrArticulo = mysqli_fetch_all($hacer, MYSQLI_ASSOC);
          
            $arrArticulo = mysqli_fetch_assoc($hacer);
            // no viene vacio.
            if(!empty($arrArticulo)) {
            
                // inicio la variables a contar
                $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
                $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
                $cantidadUnidadMinimaCaja = 1;
                // validando
                // dispaly
                if ($arrArticulo['cantidad_unidad_display'] != 0 && $arrArticulo['cantidad_unidad_display'] != null) {
                    $cantidadUnidadDisplay = (int)$arrArticulo['cantidad_unidad_display'];
                }

                // bulto
                if ($arrArticulo['cantidad_display_bulto'] != 0 && $arrArticulo['cantidad_display_bulto'] != null) {
                    $cantidadDisplayBulto =(int) $arrArticulo['cantidad_display_bulto'];
                }

                $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.


                $arrPresentacion  = array(
                    array('idPresentacion' => 0, 'defecto'=>'no', 'nombrePresentacion' => 'Unidad', 'cantidadDisplay' => 1, 'cantidadUnidadMinima' => 1),
                    array('idPresentacion' => 1, 'defecto'=>'no','nombrePresentacion' => 'Display/Presentación', 'cantidadDisplay' => 1, 'cantidadUnidadMinima' => $cantidadUnidadDisplay),
                    array('idPresentacion' => 2, 'defecto'=>'no','nombrePresentacion' => 'Bulto', 'cantidadDisplay' => $cantidadDisplayBulto, 'cantidadUnidadMinima' => $cantidadUnidadMinimaCaja),

                );
                // analizar la opcion por defecto 
                if(key_exists('tipoCuentaDefecto',$arrArticulo)&&$arrArticulo['tipoCuentaDefecto']!='Desconocido'){
                    // voy a buscar la opcion que encontre porque cod barra lo encontre.
                    foreach($arrPresentacion as $key=>$presentacion){
                        if($presentacion['idPresentacion']==$arrArticulo['tipoCuentaDefecto']){
                            $arrPresentacion[$key]['defecto']='si';
                        }
                    }
                }

                # fotos con multi foto.
                $sqlArticuloFoto = "SELECT
                                articulo_foto.id_articulo_foto AS id,
                                articulo_foto.url_externo AS url,
                                articulo_foto.foto_principal AS principal 
                                FROM articulo_foto 
                                WHERE articulo_foto.IdArt=" . $arrArticulo["IDArt"];
                $hacerFoto = mysqli_query($link, $sqlArticuloFoto);
                $arrFotos = array();
                while ($ff = mysqli_fetch_assoc($hacerFoto)) {
                    $arrFotos[] = $ff;
                }

                # si es lote busco los lotes,
                $arrLotes = array();
                if ($arrArticulo["lote"] == 'Si') {
                    // $sqlLote="SELECT             
                    //             lote.id_lote AS id,
                    //             lote.fecha_vto_lote AS vencimiento,
                    //             lote.id_articulo AS idArticulo,
                    //             lote.cod_lote AS nombreLote,  
                    //             ls.stock_lote AS stockLote,    
                    //             ls.id_deposito AS idDeposito
                    //         FROM
                    //             lote 
                    //         LEFT JOIN lote_stock AS ls ON ls.id_lote = lote.id_lote
                    //         WHERE 
                    //             lote.id_articulo = '".$arrArticulo["IDArt"]."'
                    //         AND
                    //             ls.id_deposito =  '".$arrArticulo["id_deposito"]."'
                    //         AND 
                    //             ls.stock_lote>0
                    //         AND     
                    //             NOT ISNULL(ls.id_lote_stock) ";

                    // $hacerLote = mysqli_query($link, $sqlLote);
                    // if($hacerLote){
                    //     $arrLotes=mysqli_fetch_all($hacerLote);
                    // }
                    $vuelta = array('msg' => 'error', 'mensaje' => 'No se puede Ajustar Articulo con Lote');
                    print json_encode($vuelta);
                    exit;
                }
                $arrListaArticulo[]=array('articulo' => $arrArticulo, 'fotos' => $arrFotos, 'lote' => $arrLotes, 'presentacion' => $arrPresentacion);
            }
        }
    }

    if (!$hacer) {
        $vuelta = array('msg' => 'error', 'mensaje' => 'Error de busqueda No puedo recuperar los articulos par contar error:' . mysqli_error($link) . ' sql:' . $sqlArticulo);
        print json_encode($vuelta);
        exit;
    }



    $vuelta = array();
    // Calcular precios para el/los artículos encontrados
    $precios = array();
    if (!empty($arrListaArticulo)) {
        if ($tipoResultado == "unico") {
            // Calcular precio para el artículo único
            $precios = obtenerPrecioArticuloPorLista($link, $arrArticulo, 'Lista 1');
            $vuelta = array(
                'msg' => 'ok',
                'tipoResultado' => $tipoResultado,
                'listaArticulo' => array(),
                'articulo' => $arrArticulo,
                'fotos' => $arrFotos,
                'lote' => $arrLotes,
                'presentacion' => $arrPresentacion,
                'precios' => $precios
            );
        }
        if ($tipoResultado == "lista") {
            // Calcular precios para cada artículo de la lista
            foreach ($arrListaArticulo as $item) {
                $precios[] = obtenerPrecioArticuloPorLista($link, $item['articulo'], 'Lista 1');
            }
            $vuelta = array(
                'msg' => 'ok',
                'tipoResultado' => $tipoResultado,
                'listaArticulo' => $arrListaArticulo,
                'articulo' => array(),
                'fotos' => array(),
                'lote' => array(),
                'presentacion' => array(),
                'precios' => $precios
            );
        }
    }
    // vacio
    if (empty($arrListaArticulo)) {
        $vuelta = array('msg' => 'error', 'mensaje' => 'No se encontaron Artículos');
    }
    print json_encode($vuelta);
}


//* lista de depositos para el usuario.
function listarDepositos($conn)
{
    //If Principal.cambia_deposito = "Si" Then
    $idDepositoUsuario = $_SESSION['deposito'];
    $idUSuario =  $_SESSION['idusuario'];
    $seleccionDeposito =$_SESSION["seleccion_deposito_inventario"];
    $where ="";
    if($seleccionDeposito=='Seleccionado'){
        $where .="AND depu.id_deposito = ".$idDepositoUsuario;
    }

    $sqlDepositos = "SELECT
                        depu.id_deposito,
                        dep.NombreDeposito,    
                        IF(dep.CodDeposito='" . $idDepositoUsuario . "','Si','No') AS defecto
                    FROM
                        deposito_usr AS depu
                    LEFT JOIN deposito AS dep ON dep.CodDeposito = depu.id_deposito
                    WHERE
                        depu.id_usuario = " . $idUSuario . "
                        ".$where."
                        AND dep.anulado = 'No'    ";

    $hacerDeposito = mysqli_query($conn, $sqlDepositos);
    $arrDepositos = array();
    if ($hacerDeposito) {
        $arrDepositos = mysqli_fetch_all($hacerDeposito, MYSQLI_ASSOC);
    }

    print json_encode($arrDepositos);
}

// * trae datos de un articulo para invetntario, mov stock
function traeDatosArticuloUnico ($conn,$idArt){

    // * #datos del producto para insertar en tabla stock.
    $sqlArticulo = "SELECT 
                        articulo.id_manual,                      
                        
                        articulo.IDArt,
                        articulo.lote,                        
                        articulo.CodLaboratorio,
                        articulo.Moneda,
                        articulo.CodigoArticuloT,
                        articulo.NombreArticulo,
                        articulo.impuesto_interno,
                        articulo.tipoIVA,
                        articulo.Alicuota,
                        articulo.AlicuotaIB,
                        iva.Alicuota AS Alic,                       
                        articulo.PrecioCosto,                       
                        articulo.PNOficial, 
                        articulo.PFOficial,                        
                        (articulo.PFOficial-articulo.PNOficial) AS impOf,                  
                        (articulo.PNOficial*articulo.impuesto_interno/100) AS imp_interoOf,                        
                        articulo.impuesto_interno,    
                        articulo_prov.multiplicador_comp,
                        articulo.multiplicador_vta,
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
                        articulo.id_presentacionV,
                        articulo.cantidad_promedio_bulto,
                        mart.tipo_unidad,
                        mart.descrip_corta AS uniArt, 
                        presentacion_abmV.nombre_presentacion AS nombre_presentacion_vta                                 
                        
                        
                        
                        FROM articulo
                            #LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
                            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt)
                            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
                            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
                            LEFT JOIN presentacion_abm AS presentacion_abmV ON (presentacion_abmV.id_presentacion = articulo.id_presentacionV)                            
                            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
                           
                            
                            
                        WHERE
                            articulo.IDArt ={$idArt}
                        ";
    // echo "<pre>";
    // print_r($sqlArticulo);
    // echo "</pre>";
    $hacer = mysqli_query($conn, $sqlArticulo);
    if(!$hacer){
        logInventario('error busca articulo','Error:'.mysqli_error($conn) .' sql::'. $sqlArticulo);
        return false;
        
    }

    if ($hacer) {
        $datos = mysqli_fetch_assoc($hacer);
       // print json_encode($datos);
    }
    return $datos;
}


require_once __DIR__ . '/../../util-calculaprecio.inc.php';

// Función utilitaria para obtener el precio de un artículo usando la lista de precios pasada como parámetro
function obtenerPrecioArticuloPorLista($conn, $datosArticulo, $listaPrecioCliente, $connV = null, $descRenglon = 0, $usaReglaPrecio = 'No', $codCliente = null) {
    // $datosArticulo debe ser un array asociativo (como el que devuelve traeDatosArticuloUnico)
    // Lo convertimos a objeto para compatibilidad con la función
    $arti = (object)$datosArticulo;
    $param = [
        'arti' => $arti,
        'listaPrecioCliente' => $listaPrecioCliente,
        'descRenglon' => $descRenglon,
        'usaReglaPrecio' => $usaReglaPrecio,
        'codCliente' => $codCliente
    ];
    // Retorna el array completo de resultados del cálculo de precio
    $conexion = $conn;
    if ($connV !== null) {
        $conexion = $connV;
    }
    return CalculadorPreciosUtil::calculaPrecios($param, $conexion);
}

// * alta de movimiento de stock de entrada..
function altaMovimientoEntrada($conn, $arrParam)
{
    // echo '<pre>', print_r($arrParam), '</pre>', PHP_EOL;
    // el usuario vendedor.
    $usuario = (array) $_SESSION['vendedor'];

    // echo '<pre>', print_r($usuario), '</pre>', PHP_EOL;


    // datos que recibo de parametros.
    logInventario('altaMovimiento arrParam',json_encode($arrParam));
    $textoErrorInventarioID ="";
    $fecha = $arrParam['fecha'];
    $idArt = $arrParam['idArticulo'];
    $tipoCuenta = $arrParam['tipoCuenta']; // unidad / display / bulto como hice la cuenta
    $cantidadContadaUnidad = $arrParam['cantidadContadaUnidad'];
    $cantidadContadaDisplay = $arrParam['cantidadContadaDisplay'];
    $cantidadContadaBulto = $arrParam['cantidadContadaBulto'];


    $cantidadMinimaContada = $arrParam['cantidadMinimaContada'];

    $saldoDeposito = $arrParam['saldoDeposito']; // saldo actual expresado en unidad minima.
    $idDeposito = $arrParam['idDeposito'];
    $tipoAjuste = $arrParam['tipoAjuste']; // tipo de ajuste Saldo Directo (ajuste) o movimient entrada, salida rotura = salida.
    $detalleAjuste ='';
    if(key_exists('detalleAjuste',$arrParam)){    
        $detalleAjuste  = $arrParam['detalleAjuste'];
    }

    // el saldo actual lo puedo o bien volver a pedir o bien tomar el que habia.
    $usaLote = 'No';
    $idSaldoLote = '';
    $idLote = '';
    $saldoLote =  '';
    // tengo lote completo los campos.
    if (key_exists('usaLote', $arrParam) && $arrParam['usaLote'] == 'Si') {
        $usaLote = $arrParam['usaLote'];
        $idSaldoLote = $arrParam['idSaldoLote'];
        $idLote = $arrParam['idLote'];
        $saldoLote =  $arrParam['saldoLote'];
    }
    // datos de configuracion a realizar
    $usaEmbalaje = "No";
    $usoBultoPromedio = "No";
    $usaDisplay = "No";
    $cotizacionDolar = 1;
    $motivo = 'Ajuste';
    $idMotivo = 1;

    $idReferencia = $usuario['id_refmovstock'];
    $idUSuario =  $usuario['id_usuario'];
    $idPvUsuario =   $usuario['id_punto_venta'];
    $codViajante = $usuario['CodViajante']; // vendedor del usuario.
    $idPvUsuarioC = $usuario['id_punto_ventac'];
    $idSucursalUsuario = $usuario['id_sucursal'];


    // * cotizacion de dolar.
    $sqlCotiza = "SELECT c.ValorPesos AS cotizacion FROM cotizacion AS c LIMIT 1";
    $hCotiza = mysqli_query($conn, $sqlCotiza);
    $arrCot = mysqli_fetch_all($hCotiza, MYSQLI_ASSOC);
    $cotizacionDolar = $arrCot[0]['cotizacion'];
 

    // * datos de sesion


    // embalaje
    if ($_SESSION["utilizaEmbalaje"] == "Si") {
        $usaEmbalaje = "Si";
    }

    // bulto promedio
    if ($_SESSION["uso_bulto_promedio"] == "Si") {
        $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
    }
    // bulto cerrado 
    if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
        $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
    }

    // display 
    if ($_SESSION['utiliza_display'] == "Si") {
        $usaDisplay = $_SESSION['utiliza_display'];
    }

    // * buscro datos del producto a ajustar.
    $datos = traeDatosArticuloUnico($conn,$idArt);
    if($datos===false){
        print json_encode(array('estado' => 'error', 'descMensaje' => 'No puedo consultar datos de articulo'));
        exit;
    }

    // instanciar variables con todos las opciones.

    // Tipo = "Movimiento Stock"                    
    // TipoComp = Motivo.Text

    // Comprobante = "MSTOCK"

    $codigoArticuloT = $datos['CodigoArticuloT'];
    $nombreArticulo = $datos['NombreArticulo'];
    $alicuota = $datos['Alicuota'];
    $moneda= $datos['Moneda'];
    $alicuotaIb = $datos['AlicuotaIB'];
    $multiplicadorCompra = $datos['multiplicador_comp'];
    $cantidadUni = $datos['cantidad_uni'];
    $multiplicadorVenta = $datos['multiplicador_vta'];
    $codLaboratorio = $datos['CodLaboratorio'];
    $idArt = $datos['IDArt'];
    $idManual = $datos['id_manual'];
    $tipoIva = $datos['tipoIVA'];
    $valorIva= $datos['Alic'];
    $anulado = 'No';
    $tipoPrecioUnidad = $datos['tipoPrecioUnidad']; // unidad display bulto, para saber el precio en que esta expresado...(deberia ser siempre en unidad ..)             

    // 'Saldo Directo (ajuste)',
    //     // Motivo.AddItem "Faltante Mercaderia", 2
    //     // Motivo.AddItem "Sobrante Mercaderia", 3
        
    //     // Motivo.AddItem "Transferencia", 5
    //     'Mov. Interno Salida',
    //     'Mov. Interno Entrada',
    //     'Rotura',

    // SALDO ANALISIS
    // segun el tipo de movimiento calculo el saldo directo o no.

    if($tipoAjuste=='Saldo Directo (ajuste)'){
        $motivo = 'Ajuste';
        $arrSaldoDirecto = calculoSaldoDirecto($cantidadMinimaContada, $saldoDeposito);

        logInventario('saldoDirecto',json_encode($arrSaldoDirecto));

        if ($arrSaldoDirecto['tipoMovimiento'] == 'no') {
            //debo salir no continuar no es necesario.
            print json_encode(array('estado' => 'error', 'descMensaje' => 'el saldo y la cantidad contada coinciden no se requiere AJUSTE'));
            exit;
        }

    }
    
    // no soy saldo directo tomo soy lode mas la cantidad minimas es la que viene.
    if($tipoAjuste!='Saldo Directo (ajuste)'){
        $arrSaldoDirecto['cantidadDiferencia'] = $cantidadMinimaContada;
        $motivo = $tipoAjuste;
    }
    $divisor = 1;

    $cantidadDividir=1;

    

    // # PRECIO COSTO y DOLAR validar precio costo x unidad y el rprecio costo por renglon y la moneda
    // el precio de costo siempre esta por la unidad minima, 
    // si compro cajas debo multiplicar ese costo por la cantidad minima segun el bulto
    $precioCosto = $datos['PrecioCosto'];

    // supuestamente el costo esta expresado por Bulto siempre
  

    // datos para hacer calculo de precio de costo correctamente.
    $arrDatosPrecio['precioCosto'] = $precioCosto;
    $arrDatosPrecio['comoCuento'] = $tipoCuenta;
    $arrDatosPrecio['tipoPrecioUnidad'] = $tipoPrecioUnidad;
    $arrDatosPrecio['cantidadUnidad'] = 1;
    $arrDatosPrecio['cantidadUnidadDisplay'] = $datos['cantidad_unidad_display'];
    $arrDatosPrecio['cantidadDisplayBulto'] = $datos['cantidad_display_bulto'];


    $precioCostoCalculado = calculaPrecioCostoUnidad($arrDatosPrecio);
    logInventario('Datos para trabajar $datos: ',json_encode($datos));
    logInventario('precioCosto',$datos['PrecioCosto']);

    logInventario('precioCostoVuelta funcion',$precioCostoCalculado);
    logInventario('cotidolar',$cotizacionDolar);
    // dolar
    if($moneda=='Dolar'){
        
        $precioCostoxUnidad = $precioCostoCalculado* $cotizacionDolar; // asumo que el precio de costo segun el divisor
        logInventario('preciocostoUnidadDolar',$precioCostoxUnidad);
    }
    // pesos 
    if($moneda=='Pesos'){
        $precioCostoxUnidad = $precioCostoCalculado; // asumo que el precio de costo segun el divisor
        logInventario('preciocostoUnidadPesos',$precioCostoxUnidad);

    }
    // precio de costo por renglon segun como cuento.
    // analizo el divisor a guardar.
    $cantidadDiferencia = $arrSaldoDirecto['cantidadDiferencia'];// la cantidad final a ajustar exrpesada en unidad
    if($tipoCuenta=='Unidad'){
        $detalle = "Ajuste Movil Tipo " . $tipoCuenta . ': ' . $cantidadContadaUnidad;
    }
    if($tipoCuenta=='Display'){
        $cantidadDividir = $datos['cantidad_unidad_display'] ;
        $detalle = "Ajuste Movil Tipo " . $tipoCuenta . ': ' . $cantidadContadaDisplay;
        // $cantidadDiferencia = $arrSaldoDirecto['cantidadDiferencia'] / $cantidadDividir; // la cantidad final a ajustar expresada en display
        $cantidadDiferencia = $arrSaldoDirecto['cantidadDiferencia'] / $cantidadDividir;
    }
    if($tipoCuenta=='Bulto'){
        $cantidadDividir = $datos['cantidad_display_bulto'] *  $datos['cantidad_unidad_display'] ;
        //$cantidadDividir = $datos['cantidad_display_bulto'];

        $detalle = "Ajuste Movil Tipo " . $tipoCuenta . ': ' . $cantidadContadaBulto;
        $cantidadDiferencia = $arrSaldoDirecto['cantidadDiferencia'] / $cantidadDividir; // la cantidad final a ajustar expresada en bultos
    }
    // el costo del renglon... va por l cantidad contada ( en realida dpor la cantidad diferencia expresasda en como cuento.)
    $precioCostoXRenglon =($precioCostoxUnidad * $cantidadDiferencia);


    logInventario('precioCostoRenglon',$precioCostoXRenglon);


    // * # Creando movimiento de Ajuste.
    // # ============================================
    $errores = 0;
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($conn, $sqlTotal) or die('No puedo iniciar autocommit ' . mysqli_error($conn));
    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($conn, $sqlTotal) or die('No puedo hacer Begin ' . mysqli_error($conn));

    // recupero el codigo de movimiento
    $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew FROM codmov WHERE codigo = 1";
    $resultado = mysqli_query($conn, $sqlMovi) or die('No puedo recuperar el codigo de movimiento' . mysqli_error($conn));
    if (!$resultado) {
        $errores++;
    }
    // recupero el nuevo codigo de movimiento
    $codMov = mysqli_fetch_assoc($resultado);
    $codMovInventario = $codMov["CodigoMovNew"];

    // actualizo el codigo de movimiento en la tabla codigo de movimiento.
    $sqlMoviUp  = "UPDATE codmov 
                    SET CodigoMovimiento=" . $codMovInventario . " 
                    WHERE codmov.codigo=1;";
    $resultado = mysqli_query($conn, $sqlMoviUp) or die('No puedo modificar el codigo de movimiento' . mysqli_error($conn));
    if (!$resultado) {
        $errores++;
    }
    //    
    //    // obtengo el numero de comprobante del pedido
    // revisar que hago con el punto de venta del vendedor si lo hago 
    $sqlTalon = "SELECT * 
                    FROM talonarios 
                    WHERE id_punto_venta ={$idPvUsuarioC}   
                    AND TipoComprobante = 'MSTOCK'";
    $resultado = mysqli_query($conn, $sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($conn));
    if (!$resultado) {
        $errores++;
    }

    $objTalonario = mysqli_fetch_object($resultado);
    //    echo "<pre>".print_r($objTalonario)."</pre>";
    $nroPv = $objTalonario->PV;
    $nroForm = $objTalonario->Nro;


    $numeroFormMovStock = str_pad($nroPv, 4, '0', STR_PAD_LEFT) . "-" . str_pad($nroForm, 8, '0', STR_PAD_LEFT);
    $nroCompBusqForm = $nroForm;
    $fechaComp = date('Y/m/d');

    // actualizo el talonario
    $sqlTalonUp = "UPDATE talonarios 
                        SET Nro = " . $objTalonario->Nro . "+1 
                        WHERE id_punto_venta = {$idPvUsuarioC}   
                        AND TipoComprobante = 'MSTOCK'";
    $resultado = mysqli_query($conn, $sqlTalonUp);
    if (!$resultado) {
        echo 'No puedo actualizar el talonario' . mysqli_error($conn) . "<p>" . $sqlTalonUp . "</p>";
        $errores++;
    }

    $vencimiento = date('Y/m/d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y')));



    // alta movimiento de stock AJUSTE
    //==========================================================================

    $sqlInsertMovStock = "INSERT INTO movimiento_stock SET     
                            motivo_movimiento='{$motivo}',
                            fecha = '{$fechaComp}',
                            deposito_origen={$idDeposito},
                            deposito_destino={$idDeposito},
                            detalle ='Inventario Movil {$detalleAjuste}',
                            codigo_movimiento= {$codMovInventario},
                            nro_comprobante ='{$numeroFormMovStock}',
                            nro_comprobante_busq = '{$nroCompBusqForm}',
                            id_usuario={$idUSuario},
                            id_sucursal={$idSucursalUsuario},
                            id_pv = {$idPvUsuarioC},
                            tipo_comprobante='MSTOCK',
                            id_ref_movstock = {$idReferencia},
                            CotiDolar='{$cotizacionDolar}'
                        ";

    // insertando el movimiento.
    $hacerMovStock = mysqli_query($conn, $sqlInsertMovStock);
    if (!$hacerMovStock) {
        echo 'No puedo crear el movimiento de stock' . mysqli_error($conn) . "<p>" . $sqlInsertMovStock . "</p>";
        $errores++;
    }


    if (!$resultado) {
        $errores++;
    }


    // * # Insertando en la tabla stock
    // en este texto analiza el tipo de movimiento y completa lo que sea necesario.
    $textoInsertMov = "";
    // inicializo variable deposito. con su saldo actual
    $saldoDepositoFinal = $saldoDeposito;
    // si soy saldo directo 
    if($tipoAjuste=='Saldo Directo (ajuste)'){
        $saldoDepositoFinal = $cantidadMinimaContada;
        // entrada 
        if ($arrSaldoDirecto['tipoMovimiento'] == 'entrada') {
            $textoInsertMov .= "Salida = 0 ," . PHP_EOL;
            $textoInsertMov .= "Entrada = " . $arrSaldoDirecto['cantidadDiferencia'] . "," . PHP_EOL;
            $textoInsertMov .= "Cantidad = " . $arrSaldoDirecto['cantidadDiferencia'] . "," . PHP_EOL;
            $textoInsertMov .= "Saldo = " . $saldoDepositoFinal . "," . PHP_EOL;
        }
        // salida
        if ($arrSaldoDirecto['tipoMovimiento'] == 'salida') {
            $textoInsertMov .= "Entrada = 0 ," . PHP_EOL;
            $textoInsertMov .= "Salida = " . $arrSaldoDirecto['cantidadDiferencia'] . "," . PHP_EOL;
            $textoInsertMov .= "Cantidad = " . $arrSaldoDirecto['cantidadDiferencia'] . "," . PHP_EOL;
            $textoInsertMov .= "Saldo = " . $saldoDepositoFinal . "," . PHP_EOL;
        }
        
    }
    // * otros tipos de ajuste 
    if($tipoAjuste=='Mov. Interno Salida'){
        $saldoDepositoFinal = $saldoDeposito-$cantidadMinimaContada;
        $textoInsertMov .= "Entrada = 0 ," . PHP_EOL;
        $textoInsertMov .= "Salida = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Cantidad = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Saldo = " . $saldoDepositoFinal . "," . PHP_EOL;
    }

    if($tipoAjuste=='Rotura'){
        $saldoDepositoFinal = $saldoDeposito-$cantidadMinimaContada;
        $textoInsertMov .= "Entrada = 0 ," . PHP_EOL;
        $textoInsertMov .= "Salida = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Cantidad = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Saldo = " . $saldoDepositoFinal . "," . PHP_EOL;
    }

    if($tipoAjuste=='Mov. Interno Entrada'){
        $saldoDepositoFinal = $saldoDeposito + $cantidadMinimaContada;
        $textoInsertMov .= "Salida = 0 ," . PHP_EOL;
        $textoInsertMov .= "Entrada = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Cantidad = " . $cantidadMinimaContada . "," . PHP_EOL;
        $textoInsertMov .= "Saldo = " . $saldoDepositoFinal . "," . PHP_EOL;
    }

    logInventario('textoInsertMov',$textoInsertMov);

    //* INSERT EN STOCK


    $sqlInsertStock = "INSERT INTO stock SET 
                    Fecha = '{$fechaComp}',
                    CodigoArticulo='{$codigoArticuloT}',
                    Descripcion='{$nombreArticulo}',
                    {$textoInsertMov}
                    PrecioCostoxU={$precioCostoxUnidad},
                    PrecioCostoxR={$precioCostoXRenglon},
                    Alicuota={$alicuota},
                    AlicuotaIB={$alicuotaIb},
                    imp_alicuota_iva='{$valorIva}',
                    multiplicador_comp='{$multiplicadorCompra}',
                    cantidad_uni='{$cantidadUni}',
                    multiplicador_vta='{$multiplicadorVenta}',
                    codViajante={$codViajante},
                    codLaboratorio={$codLaboratorio},
                    codigoMovimiento={$codMovInventario},
                    CodDeposito={$idDeposito},
                    IDArt ={$idArt},
                    Detalle='{$detalle}',
                    id_manual='{$idManual}',
                    codSucursal = {$idSucursalUsuario},
                    idUsuario = {$idUSuario},
                    TipoIVA = '{$tipoIva}',
                    Tipo = 'Movimiento Stock',
                    TipoComp = '{$motivo}',
                    anulado = '{$anulado}',
                    Comprobante = 'MSTOCK',
                    coti_dolar='{$cotizacionDolar}',
                    NroComprobante = '{$numeroFormMovStock}',
                    id_ref_movstock = $idReferencia,
                    tipo_unidad='{$tipoCuenta}',
                    cantidad_unidad_display='{$datos['cantidad_unidad_display']}',
                    cantidad_dividir='{$cantidadDividir}'
                    ";
    $hacerStock = mysqli_query($conn, $sqlInsertStock);
    logInventario('stock',$sqlInsertStock);
    if (!$hacerStock) {
        echo 'No pudimos insertar en el stock, erro:' . mysqli_error($conn) . PHP_EOL . ' sql insert: ' . $sqlInsertStock . PHP_EOL;
        $errores++;
    }
    // inserte exitoso actualizo deposito.
    if ($hacerStock) {
        
        // actualizar el stock deposito con el saldo.
        $sqlUpdateDeposito = "UPDATE  stock_deposito SET 
                                saldo = {$saldoDepositoFinal}
                            WHERE
                                stock_deposito.id_articulo={$idArt}
                            AND 
                                stock_deposito.id_deposito={$idDeposito}
        ";

        $hacerDeposito  = mysqli_query($conn, $sqlUpdateDeposito);
        if (!$hacerDeposito) {
            echo 'fallo actualizar el stock_deposito err:' . mysqli_error($conn) . ' sql:' . $sqlUpdateDeposito . PHP_EOL;
            $errores++;
        }

        // * Guardar en tabla Inventario.
        // * =================================
        // *=> Buscar el id de inventario Disponible....
        $sqlIdInventarioId = " SELECT 
                                inventario_id.id_inventario_id AS id
                            FROM 
                                inventario_id 
                            WHERE 
                            YEAR(inventario_id.fecha_inventario_hasta) =".date('Y')." ORDER BY inventario_id.id_inventario_id DESC LIMIT 1";
         logInventario('IDINVENTARIOID',$sqlIdInventarioId);  

        $hacerIdInventario = mysqli_query($conn,$sqlIdInventarioId);

        if($hacerIdInventario){

             // puedo tener el inventario ID vacio debo validar 
             $hayInventario = mysqli_num_rows($hacerIdInventario);
             // no hay registro de creacion de inventario.

             if($hayInventario==0){
                // logInventario('error sin Inventario ID vacio','error hay registro=>'. $hayInventario);
                // $textoErrorInventarioID ="No existe un Proceso de Inventario para el ".date('Y')." válido";
                // $errores++;
                $fechaActual = new DateTime();
                // echo "Fecha actual: " . $fechaActual->format('Y-m-d') . "\n";

                // Fecha actual + 6 meses
                $fechaMas6Meses = new DateTime();
                $fechaMas6Meses->modify('+6 months');
                // echo "Fecha actual + 6 meses: " . $fechaMas6Meses->format('Y-m-d') . "\n";

                // creando nueva entrada
                $sqlInsertInventarioId = "INSERT INTO inventario_id SET                 
                                        fecha_inventario='".$fechaActual->format('Y/m/d')."' ,
                                        fecha_inventario_desde='".$fechaActual->format('Y/m/d')."',
                                        fecha_inventario_hasta='".$fechaMas6Meses->format('Y/m/d')."'";

                $hacerInsertInventarioId = mysqli_query($conn,$sqlInsertInventarioId);

                // insertar oka
                if($hacerInsertInventarioId){
                    $claveInventarioId = mysqli_insert_id($conn);
                }
                // insertar fallo
                if(!$hacerInsertInventarioId){
                    logInventario('error  insert inventario id', 'error: '.mysqli_error($conn).' sql: '.$sqlInsertInventarioId);
                    $textoErrorInventarioID .="No exite un Inventario Acrtivo, No se pudo crear el nuevo inventario general ";
                    $errores++;
                }

             }

            // tengo registro de inventario_id 
            if($hayInventario!=0){ 

                $idInventarioId = mysqli_fetch_assoc($hacerIdInventario);

                // trae registro  
                $claveInventarioId =  $idInventarioId['id'];
                // la clave de inventario viene null
                if($claveInventarioId==null){
                    logInventario('error sin Inventario ID null','error hay registro=>'. $hayInventario);
                    $textoErrorInventarioID .="No existe un Proceso de Inventario para el ".date('Y')." válido";
                    $errores++;

                }
            }



            // clave de inventario oka
            if($claveInventarioId!=null){
                // validar que el inventario id no sea null
                

                //debo saber si existe id uusario 
                $sqlCampoUsuario = "SHOW COLUMNS FROM inventario LIKE 'id_usuario'";

                $resultadoCampo = mysqli_query($conn, $sqlCampoUsuario);

                if (!$resultadoCampo) {
                    logInventario('error consultando si existe id usuario','error sql=>'. $sqlCampoUsuario);
                    $errores++;
                }

                // Verificar si el campo existe
                if (mysqli_num_rows($resultadoCampo) > 0) {
                    // El campo existe, incluirlo en el INSERT
                    
                    $sqlInsertInventario ="INSERT INTO inventario SET            
                                        id_articulo='{$idArt}',
                                        id_deposito='{$idDeposito}',
                                        fecha_inventario='{$fechaComp}',
                                        saldo_sistema='{$saldoDeposito}',
                                        saldo_manual='{$cantidadMinimaContada}',
                                        diferencia='{$arrSaldoDirecto['cantidadDiferencia']}',
                                        id_inventario_id={$claveInventarioId},
                                        tipo='{$tipoCuenta}',
                                        id_usuario={$idUSuario}
                                        ";
                } else {
                    // El campo no existe, omitirlo en el INSERT
                    
                    $sqlInsertInventario ="INSERT INTO inventario SET            
                                        id_articulo='{$idArt}',
                                        id_deposito='{$idDeposito}',
                                        fecha_inventario='{$fechaComp}',
                                        saldo_sistema='{$saldoDeposito}',
                                        saldo_manual='{$cantidadMinimaContada}',
                                        diferencia='{$arrSaldoDirecto['cantidadDiferencia']}',
                                        id_inventario_id={$claveInventarioId},
                                        tipo='{$tipoCuenta}'";
                }

                // validacion id usuario

                
                $hacerAltaInventario = mysqli_query($conn,$sqlInsertInventario);   
                if(!$hacerAltaInventario){
                    logInventario('error insert inventario','error:'.mysqli_error($conn)." sql:".$sqlInsertInventario);
                    $errores++;

                } 
            }
                            
        }

        // fallo la consulta por algo
        if(!$hacerIdInventario){
            logInventario('error select inventario id ','error:'.mysqli_error($conn)." sql:".$sqlIdInventarioId);
            $errores++;
        }
    }
    // fin
    $vuelta = array();
    // todo bien
    if ($errores == 0) {
        $sqlTotal = "COMMIT;";
        $resultado = mysqli_query($conn, $sqlTotal);
        $vuelta = array('msg' => 'ok', 'mensaje' => '<p>Se ha generado el Ajuste nro: <strong>' . $numeroFormMovStock . '</strong></p>');
        //echo "todo bien";
    }
    // todo mail
    if ($errores != 0) {
        $sqlTotal = "ROLLBACK;";
        $resultado = mysqli_query($conn, $sqlTotal);
        $vuelta = array('msg' => 'error', 'mensaje' => '<p>No se pudo generar el Ajuste</p>'.$textoErrorInventarioID); 
        //echo "todo mal";
    }
    print json_encode($vuelta);
}

//* funcion que analiza el calculo directo
// return: la cantidad real a contar y si es entrada o salida.
// el calculo es con unidad minima.
function calculoSaldoDirecto($cantidadContada, $saldoDeposito)
{
    $totalEntrada = 0;
    $tipoMovimiento = 'entrada';
    logInventario('calculoSaldoDirecto cantidadContada',$cantidadContada);
    logInventario('calculoSaldoDirecto saldoDeposito',$saldoDeposito);

    // conte 20 tengo -50 
    if ($cantidadContada > $saldoDeposito) {
        $totalEntrada = $cantidadContada - $saldoDeposito;
        $tipoMovimiento = 'entrada';
    }

    // conte 10 tengo 15 
    if ($cantidadContada < $saldoDeposito) {
        $totalEntrada = $saldoDeposito - $cantidadContada;
        $tipoMovimiento = 'salida';
    }
    if ($cantidadContada == $saldoDeposito) {
        $totalEntrada = $cantidadContada;
        $tipoMovimiento = 'no'; // si el saldo es igual a lo que conte, no debo continuar.
    }
    
    $vuelta = array('cantidadDiferencia' => $totalEntrada, 'tipoMovimiento' => $tipoMovimiento,'cantidadMinimaContada'=>$cantidadContada);
    return $vuelta;
}



// * funcion para analizar el precio de Costo de inventario.
function calculaPrecioCostoUnidad($datos){
    // $datos es un array con los datos para hacer calculo.
    $comoCuento= $datos['comoCuento'];
    // $cantidadContada=$datos['cantidadContada'];
    $cantidadUnidad=$datos['cantidadUnidad'];
    $cantidadUnidadDisplay=$datos['cantidadUnidadDisplay'];
    $cantidadDisplayBulto=$datos['cantidadDisplayBulto'];
    $tipoPrecioUnidad=$datos['tipoPrecioUnidad'];
    $precioCosto = $datos['precioCosto'];
    $divisor=1;
    $multiplicador=1;
    $precioCostoUnidad = $precioCosto;
    $precioCostoComoCuento = $precioCosto;

    // como cuento
    // if($comoCuento=="Unidad"){
    //     // que tipo unidad del precio.
    //     if($tipoPrecioUnidad=="Unidad"){
    //         // todo igual.
    //     }
    //     // conte por unidad pero el precio esta en display
    //     if($tipoPrecioUnidad=="Display"){
    //         $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
    //         if($divisor == 0) $divisor=1;
    //         $precioCostoUnidad = $precioCosto /$divisor;
    //     }
    //     if($tipoPrecioUnidad=="Bulto"){
    //         $divisor = (int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
    //         if($divisor == 0) $divisor=1;
    //         $precioCostoUnidad = $precioCosto /$divisor;

    //     }
    // }

    // if($comoCuento=="Display"){
    //     // conte display pero el precio esta en unidad
    //     if($tipoPrecioUnidad=="Unidad"){
    //         // $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
    //         // if($divisor == 0) $divisor=1;
    //         // $precioCostoUnidad = $precioCosto /$divisor;
    //     }
    //     if($tipoPrecioUnidad=="Display"){
    //         $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
    //         if($divisor == 0) $divisor=1;
    //         $precioCostoUnidad = $precioCosto /$divisor;
    //     }
    //     // conte en display y el precio esta en bulto.
    //     if($tipoPrecioUnidad=="Bulto"){
    //        $divisor = (int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
    //         if($divisor == 0) $divisor=1;
    //         $precioCostoUnidad = $precioCosto /$divisor;
    //     }
    // }

    // if($comoCuento=="Bulto"){
    //     if($tipoPrecioUnidad=="Unidad"){
    //         $multiplicador =(int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
    //         if($multiplicador == 0) $multiplicador=1;
    //         $precioCostoUnidad = $precioCosto * $multiplicador;
    //         $precioCostoUnidad = $precioCosto;
            
    //     }
    //     if($tipoPrecioUnidad=="Display"){
    //         $multiplicador = (int)$cantidadDisplayBulto;// cuantas unidades tengo 
    //         if($multiplicador == 0) $multiplicador=1;
    //         $precioCostoUnidad = $precioCosto * $multiplicador;
            
    //     }
    //     if($tipoPrecioUnidad=="Bulto"){
    //         $precioCostoUnidad = $precioCosto;
    //     }
    // }

    // el precio de costo siempre va por display

    if($tipoPrecioUnidad=="Unidad"){
        $precioCostoUnidad = $precioCosto;
        
        
    }

    if($tipoPrecioUnidad=="Display"){
        // $divisor = (int)$cantidadUnidadDisplay;// cuantas unidades tengo 
        // if($divisor == 0) $divisor=1;
        // $precioCostoUnidad = $precioCosto /$divisor;
        $precioCostoUnidad = $precioCosto;
        
    }

    if($tipoPrecioUnidad=="Bulto"){
        // $divisor = (int)($cantidadUnidadDisplay*$cantidadDisplayBulto);// cuantas unidades tengo 
        $divisor = (int)($cantidadDisplayBulto);// cuantas display tengo en el bulto

        if($divisor == 0) $divisor=1;
        $precioCostoUnidad = $precioCosto /$divisor;
    }

    

    return $precioCostoUnidad;
    //return $precioCostoComoCuento;
}



// * Guardar codigo de barra de un producto...
function guardarCodBarra($conn,$param){
    // verificar que no exista ese codigo para ese idart ni para ningun otro. el codbarra deber ser univoco.
    $idArt = $param['idArt'];
    $nroCodBarra  = $param['codBarra'];
    $tipoCodBarra = $param['tipoCodBarra'];
    $arrTipoNombre = array(
        'NroCodBarra'=>'código de barra de unidad',
        'NroCodBarraF'=>'código de barra del fabricante de unidad',
        'nro_cod_barra_bulto'=>'código de barra Bulto',
        'nro_cod_barra_display'=>'código de barra Display'
    );

    // armando sql que busca si hay coincidencias.
    $sqlControl = "SELECT 
                    articulo.IDArt,
                    articulo.NroCodBarra,
                    articulo.NroCodBarraF,
                    articulo.nro_cod_barra_bulto,
                    articulo.nro_cod_barra_display
                    FROM 
                    articulo
                    WHERE 
                    articulo.NroCodBarra ='".$nroCodBarra."'
                    OR articulo.NroCodBarraF ='".$nroCodBarra."'
                    OR articulo.nro_cod_barra_bulto ='".$nroCodBarra."'
                    OR articulo.nro_cod_barra_display='".$nroCodBarra."'";
    // no importa si hay un codigo de barra nuevo si es el mismo producto, hace un update si no falla.
    $hacerControl = mysqli_query($conn,$sqlControl);
    // error de conexion base de datos
    if(!$hacerControl){
       // echo '<pre>',mysqli_error($conn).PHP_EOL,$sqlControl.PHP_EOL,'</pre>';
        $vuelta = array('msg'=>'error','mensaje'=>'No se pudo realizar operación, intente más tarde');
        
    }   
    // me pude conectar
    if($hacerControl){
        $arrControlVuelta = array();
        $errorDuplicado =0;
        $mensajeDuplicado ='';
        while($artControl =mysqli_fetch_assoc($hacerControl)){
            $arrControlVuelta[] = $artControl;
            // validar si es el mismo idart.
        }
        // si encontre codbarra, debo analizar. si piso o no.
        if(!empty($arrControlVuelta)){  
            // tengo uno o mas registros con codbarra que coinciden.
            foreach($arrControlVuelta as $prod){
                /// si soy el mismo idart, veo si es el mismo co
                // si soy el mismo idart analizo el campo del codbarra
                if($prod["IDArt"]==$idArt){
                    foreach($prod as $nombre=>$codigo){
                        // estoy editando un codigo difertente al que encontre
                        if($nombre!=$tipoCodBarra&&$codigo==$nroCodBarra){
                            $errorDuplicado++;
                            $mensajeDuplicado ='El código ingresado para este producto, ya existe en ( '.$arrTipoNombre[$nombre].')';
                            // forzar salida
                            $vuelta = array('msg'=>'duplicado','mensaje'=>$mensajeDuplicado);
                            print json_encode($vuelta);
                            exit;

                        }
                    }
                }

                // si el idart es diferente, entonces hay que salir si o si.
                if($prod["IDArt"]!=$idArt){
                    $errorDuplicado++;
                    
                }
            }
        }
        // no tengo duplicado o el duplicado es lo que estoy editando ahora mismo idart mismo codigo para mismo tipo.
        if($errorDuplicado==0){
            $campoEdito="";
            $campoEdito .=$tipoCodBarra."='".$nroCodBarra."' ";

            // sql que actualiza el cod barra
            $sqlCodBarra = "UPDATE articulo SET ".$campoEdito." WHERE articulo.IDArt ='".$idArt."'";
            $hacerEdicion = mysqli_query($conn,$sqlCodBarra);
            if(!$hacerEdicion){
                // fallo edicion.
                //echo '<pre>',mysqli_error($conn).PHP_EOL,$sqlCodBarra.PHP_EOL,'</pre>';

                $vuelta = array('msg'=>'error','mensaje'=>'No se pudo guardar el código de barra');
            }

            if($hacerEdicion){
                $vuelta=array("msg"=>'ok','mensaje'=>'Código de barra guardado con exito');
            }

        }

        // error duplicado
        if($errorDuplicado>0){
            $vuelta = array('msg'=>'duplicado','mensaje'=>'El código de ingresado ya existe en el sistema para otro producto');
        }
    }
    
    print json_encode($vuelta);

}

// * function tipo de movimiento Stock
function listaTipoMovimientoStock(){
    $arrayListaTipoMov=array(
       
       'Saldo Directo (ajuste)',
        // Motivo.AddItem "Faltante Mercaderia", 2
        // Motivo.AddItem "Sobrante Mercaderia", 3
        
        // Motivo.AddItem "Transferencia", 5
        'Mov. Interno Salida',
        'Mov. Interno Entrada',
        'Rotura',
        
        // Motivo.AddItem "Armado", 8
        // Motivo.AddItem "Desarmado", 9
    );
    print json_encode($arrayListaTipoMov);
}

// * funciton referencia de movimiento stock.
function listaReferenciaMovimientoStock($conn){
    $sqlReferencia = "SELECT * FROM ref_movstock WHERE ref_movstock.Anulado='No' ORDER BY nombre_ref_movstock";
    $hacerReferencia = mysqli_query($conn,$sqlReferencia);
    $arrReferencia=array();
    if($hacerReferencia){
        while($ref=mysqli_fetch_assoc($hacerReferencia)){
            $arrRefencia[] = $ref;
        }
    }
    print json_encode($arrRefencia);

}

// * funcion de alta de imagen en servidior administranet y base.
function altaImagenAdministranet($conn,$param){
    // subir foto al servidor de archivos dea dministranet.
    // 1 -> subir la foto,
    // 2-guardar la foto 
    $datosFotoApi = array();
    $fotoBase64 = $param['imagenNueva'];    
    $idArt = $param['idArticulo'];

    $datosFotoApi = subirImagenApiAdministranet($fotoBase64);
    // fallo el alta vengo vacio
    if(empty($datosFotoApi)){
        logInventario('error subida foto nube','No se pudo subir imagen al nube datos fallidos');
        $vuelta=array('msg'=>'error','mensaje'=>'No se pudo subir imagen a la nube');
        print json_encode($vuelta);
        exit;
    }
    // el alta vino bien a cargar en la base d edatos
    if(!empty($datosFotoApi)){
        //$sql = "INSERT into " . $_REQUEST['DB'] . ".articulo_foto SET idART=" . $articulo . ",url_externo='" . $reply->data->link . "',nombre_archivo='" . $_FILES['archivo']['name'] . "';";
        // https://img.api.administranet.com.ar/static/6/2/5/c/625c7e19dbce89db2c5a14120e280da5.jpg
        $urlFoto = $datosFotoApi['destino'];
        // obtener el nombre de la foto si no existiera
        if(!key_exists('nombre',$datosFotoApi)){
        
            // Parsea la URL para obtener la ruta
            $path = parse_url($urlFoto, PHP_URL_PATH);

            // Obtiene la información del path
            $pathInfo = pathinfo($path);

            // El nombre de la imagen estará en 'filename'
            $nombreFoto = $pathInfo['filename'];

            //echo $nombreImagen;
        }
        if(key_exists('nombre',$datosFotoApi)){
            $nombreFoto = $datosFotoApi['nombre'];
        }

        
        $sqlInserArticuloFoto ="INSERT INTO articulo_foto 
                                SET 
                                idART=" . $idArt . ",
                                url_externo='" . $urlFoto . "',
                                nombre_archivo='" . $nombreFoto . "';";
        $hacerAlta = mysqli_query($conn,$sqlInserArticuloFoto);
        // fallo el alta
        if(!$hacerAlta){
            logInventario('error insert imagen','error: '.mysqli_error($conn).' sql: '.$sqlInserArticuloFoto.PHP_EOL );
            $vuelta = array('msg'=>'error','mensaje'=> 'No se pudo crear la imagen');
        }               
        // fallo el alta
        if($hacerAlta){
            $vuelta = array('msg'=>'ok','mensaje'=>'Imagen creada con exito!');
        }                 
        print json_encode($vuelta);
    }

}





// * elimina una imagen de preoducto en servidior administranet  y base
function borrarImagenAdministranet($conn,$param){
    //$nombreFoto = $param['nombreFoto'];
    $idArticuloFoto = $param['idArticuloFoto'];
    $sqlTraeFotoArticulo = "SELECT 
                            articulo_foto.nombre_archivo 
                        FROM 
                            articulo_foto
                        WHERE 
                            articulo_foto.id_articulo_foto=".$idArticuloFoto." LIMIT 1";

    $hacerTrae= mysqli_query($conn,$sqlTraeFotoArticulo);
    if(!$hacerTrae){
        logInventario('error no encuentro foto','error:'.mysqli_error($conn).' sql:'.$sqlTraeFotoArticulo.PHP_EOL);
        $vuelta=array('msg'=>'error','mensaje'=>'No se pudo encontra imagen para eliminar');
        print json_encode($vuelta);
        exit;
    }

    if($hacerTrae){
        $registroFoto = mysqli_fetch_assoc($hacerTrae);
        $nombreFoto = $registroFoto['nombre_archivo'];
        // puedo tener que ir a bsucar la foto 
        $ch = curl_init();

        // URL de la solicitud DELETE
        $url = 'https://img.api.administranet.com.ar/imagen/'.$nombreFoto.'?codigo='.CODIGOFOTO;

        // Configurar la solicitud cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: application/json'
        ));

        // Ejecutar la solicitud cURL
        $response = curl_exec($ch);

        // Verificar errores
        if (curl_errno($ch)) {
        // echo 'Error en la solicitud cURL: ' . curl_error($ch);
            logInventario('error borrar foto curr','Error en la solicitud cURL: ' . curl_error($ch));
            $vuelta = array('msg'=>'error','mensaje'=>'no se pudo eliminar imagen del servidor');
            print json_encode($vuelta);
            exit;
        }

        // Cerrar la sesión cURL
        curl_close($ch);

        // Mostrar la respues
        // si llegue aca la tengo que eliminar de la base d edatos
        $sqlBorraFoto ="DELETE FROM articulo_foto WHERE articulo_foto.id_articulo_foto=".$idArticuloFoto;
        $hacerBorrar = mysqli_query($conn,$sqlBorraFoto);
        // fallo el delete
        if(!$hacerBorrar){
            logInventario('error borrar foto','error: '.mysqli_error($conn).' sql:'.$sqlBorraFoto);
            $vuelta = array('msg'=>'error','mensaje'=>'no se pudo eliminar la imagen');

        }
        // delete oke
        if($hacerBorrar){

            $vuelta = array('msg'=>'ok','mensaje'=>'Imagen eliminada exitosamente');
        }
        print json_encode($vuelta);
    }
    
}

// * coloca la foto como imagen principal.
function ponerFotoPrincipal($conn,$param){
    $idArticuloFoto = $param['idArticuloFoto'];
    $idArticulo = $param['idArticulo'];
    $vuelta=array();
    // poniendo la foto principal.
    $sql ="UPDATE articulo_foto 
            SET articulo_foto.foto_principal ='Si' 
            WHERE articulo_foto.id_articulo_foto=".$idArticuloFoto;
    $hacer = mysqli_query($conn,$sql);
    // actualice la foto a princpial debo
    if($hacer){
        $sqlOtras ="UPDATE articulo_foto 
                    SET articulo_foto.foto_principal ='No' 
                    WHERE 
                    articulo_foto.idArt=".$idArticulo." 
                    AND  articulo_foto.id_articulo_foto<>".$idArticuloFoto;
        $hacerOtras = mysqli_query($conn,$sqlOtras);

        $vuelta = array('msg'=>'ok','mensaje'=>'La imagen es foto principal');           

    }
    // falllo primer update
    if(!$hacer){
        logInventario('error foto principal','Error: '.mysqli_error($conn).' sql: '.$sql.PHP_EOL);
        $vuelta = array('msg'=>'error','mensaje'=>'No se pudo colocar la foto como principal');
    }
    print json_encode($vuelta);

}

// * edicion de datos como nombre, detalle, etc.
function guardarDatosArticulo($conn,$param){
    $idArt = $param['idArticulo'];
    $nombreArticulo = $param['nombreArticulo'];
    $nombreArticuloEcomm = $param['nombreArticuloEcomm'];
    $detalle = $param['detalle'];
    $sqlUpdateArticulo = "UPDATE articulo LEFT JOIN ecom_info_articulo AS ecom ON articulo.IDArt = ecom.id_articulo 
                        SET 
                        articulo.NombreArticulo ='".$nombreArticulo."',
                        ecom.nombre_articulo_ecom ='".$nombreArticuloEcomm."',
                        articulo.Detalle='".$detalle."',
                        articulo.detalle_web='".$detalle."'
                        WHERE 
                        articulo.IDArt =".$idArt;
    $hacerUpdate = mysqli_query($conn,$sqlUpdateArticulo);
    if($hacerUpdate){
        $vuelta = array('msg'=>'ok','mensaje'=>'Datos editados con exito');
    }
    if(!$hacerUpdate){
        logInventario('error datos articulo','Error: '.mysqli_error($conn).' sql: '.$sqlUpdateArticulo.PHP_EOL);
        $vuelta = array('msg'=>'error','mensaje'=>'No se pudieron cambiar datos del articulo');
    }
    print json_encode($vuelta);

}

// * function que sube la foto al servidor administranet y debe regresar con los datos de json para insertar
function subirImagenApiAdministranet($foto){ 
    $datosVuelta = array();

    // URL de la API
    $apiUrl = URLFOTO.'imagen/?codigo='.CODIGOFOTO;
    // base 64
    // $temp_file = tempnam(sys_get_temp_dir(), 'image_');
    // file_put_contents($temp_file, base64_decode($foto));
    // echo 'Api Img<pre>';
    // print_r($foto);
    // echo '</pre>';
    
    // Datos que deseas enviar en la solicitud POST tipo $ file
     $postData = array(
        // iunput file    
             'file' => new CURLFile($foto['tmp_name'], $foto['type'], $foto['name']) // Obtiene la imagen desde el formulario en JavaScript
    //     // 'file' => new CURLFile($_FILES['imagen']['tmp_name'], $_FILES['imagen']['type'], $_FILES['imagen']['name']) // Obtiene la imagen desde el formulario en JavaScript
        // base 64 rarchivo temporario.
        //'file' => new CURLFile($temp_file, 'image/jpeg', 'file.jpg')

    );

// Configuración de la solicitud cURL
    $ch = curl_init($apiUrl);

    // Configurar las opciones de cURL para habilitar la depuración debug
    // curl_setopt($ch, CURLOPT_VERBOSE, true);
    // $verbose = fopen('php://temp', 'w+');
    // curl_setopt($ch, CURLOPT_STDERR, $verbose);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    // Realiza la solicitud
    $response = curl_exec($ch);

    // Verifica si hubo errores
    if (curl_errno($ch)) {
        // echo 'Error al realizar la solicitud: ' . curl_error($ch);
        // debug curl que paso.
        // rewind($verbose);
        // $verboseLog = stream_get_contents($verbose);

        logInventario('error curl foto', 'Error al realizar la solicitud: ' . curl_error($ch).PHP_EOL);
        // logInventario('debug curl', 'Información de depuración:'.PHP_EOL.$verboseLog.PHP_EOL);
        // Procesa la respuesta de la API
    } 
    else {
            // Obtén el tipo de contenido de la respuesta
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        
            // Procesa la respuesta de la API
            if (strpos($contentType, 'application/json') !== false) {
                // La respuesta es JSON
                $datosVuelta = json_decode($response, true); // Decodifica el JSON
                //print_r($jsonData); // Muestra la respuesta JSON como un array
            } else {
                // La respuesta no es JSON, trata de acuerdo al tipo de contenido
                logInventario('error response foto','Respuesta de la API en otro formato: ' . $contentType.PHP_EOL);
                logInventario('error response',serialize($response).PHP_EOL);
                
                // echo 'Respuesta de la API en otro formato: ' . $contentType;
                // echo $response;
            }
    }
    

    // Cierra la conexión cURL
    curl_close($ch);
    return $datosVuelta;
}

// * SESION
// validar la sesion en forma manual para devolver un mensaje en caso d eerror.
        
session_start();

// sin sesion 
if(!isset($_SESSION['id_sesion'])){
    $vuelta=array('msg'=>'sinSesion','mensaje'=>'No hay sesion creada');
    print json_encode($vuelta);	
    exit;
}

// con sesion
if(isset($_SESSION['id_sesion'])){
    include_once '../../includes/includes.inc.php';
    // tipo de usuario
    // Dispositivo
    $caminoDispo = $_SESSION["caminoDisp"];
    
    switch($_SESSION['tipousuario']){
        case 'cliente':
            require_once '../../conexion.inc.php';
            $barra = 'header-cliente.inc.php';
            //$objCliente = $_SESSION['cliente'];
            if(isset($_SESSION['cliente']) && is_array($_SESSION['cliente'])){


                $objCliente = $_SESSION['cliente'][0];
                $arrCliente = $_SESSION['cliente'][1];
            }else{
                $objCliente = $_SESSION['cliente'];
            }
            break;
        case 'vendedor':
            
            require_once '../../conexion-vendedor-empresa.inc.php';
            $barra = 'header-vendedor.inc.php';
            $objVendedor = $_SESSION['vendedor'];
            $lista_Pv = $_SESSION['lista_pv']; 
            if(isset($_SESSION['cliente']) && is_array($_SESSION['cliente'])){
//                        print_r($_SESSION['cliente']);

                $objCliente = $_SESSION['cliente'][0];
                $arrCliente = $_SESSION['cliente'][1];
            }else{
                if(isset($_SESSION['cliente'])){
                        $objCliente = $_SESSION['cliente'];
                }
            }
            break;
    }
}



// * -- MANEJADORESS --- 



// instanciando la conexion.
$conexionT = $connV;


if (isset($_GET['traeMisDatos']) && $_GET['traeMisDatos'] == 1) {
    datosUsuario();
}

if (isset($_GET['autocompletarArticulo']) && $_GET['autocompletarArticulo'] == 1) {
    busquedaRapidaArticulos($conexionT);
}

// buscar un producto por cod barra o por id de un producto.

if (isset($_GET['buscarArticulo']) && $_GET['buscarArticulo'] == 1) {
    $arrParam = array();
    // obligatorios tienen que venir si o si.
    $arrParam['tipoBusca'] = $_GET['tipoBusqueda']; // si es cod barra o codigo o texto
    $arrParam['codBarra'] = ""; // codbarra a buscar
    $arrParam['codigo'] = ""; // codbarra a buscar
    $arrParam['textoBusco'] = ""; // codbarra a buscar

    if(isset($_GET['codigoBusco'])){
        $arrParam['codigo'] = $_GET['codigoBusco']; // id a buscar 
    }
    if(isset($_GET['textoBusco'])){
        $arrParam['textoBusco'] = $_GET['textoBusco']; // texto a buscar
    }
    
    if(isset($_GET['codBarraBusco'])){
        $arrParam['codBarra'] = $_GET['codBarraBusco']; // codbarra a buscar
    }
    $arrParam['idDeposito'] = $_GET['idDeposito'];
    
    //   echo '<pre>';
    //   print_r($_GET);
    //   echo '</pre>';

    traeArticuloBusqueda($conexionT, $arrParam);
}

// buscar depositos

if (isset($_GET['listarDepositos']) && $_GET['listarDepositos'] == 1) {
    // voy a devolver el listado de depositos del usuario.
    // echo ' voy a listar los depositos';
    listarDepositos($conexionT);
}


// * alta movimiento de stock. de a un producto.

if (isset($_REQUEST['altaMovimiento']) && $_REQUEST['altaMovimiento'] == 1) {

    /*
fecha: fechaInventario,
            IdArticulo: IdArticuloInventario,
            cantidad: cantidadInventario,
            idDeposito: idDepositoInventario,
            saldoDeposito: saldoDepositoInventario,
            usaLote: usaLoteInventario,
            idSaldoLote: '',
            idLote: '',
            saldoLote: '',
*/

    $arrParam = array();
    // $arrParam['fecha'];
    // $arrParam['idArticulo'];
    // $arrParam['cantidadContada'];
    // $arrParam['cantidadMinimaContada'];  
    // $arrParam['saldoDeposito'];
    // $arrParam['idDeposito'];
    // $arrParam['tipoCuenta]
    // $arrParam['usaLote'];
    // $arrParam['idSaldoLote'];
    // $arrParam['idLote'];
    // $arrParam['saldoLote'];
    //echo '<pre>',print_r($_REQUEST),PHP_EOL,'<pre>';
    
    if (isset($_REQUEST['fecha']) && $_REQUEST['fecha'] != '') {
        $arrParam['fecha'] = $_REQUEST['fecha'];
    }

    if (isset($_REQUEST['idArticulo']) && $_REQUEST['idArticulo'] != '') {
        $arrParam['idArticulo'] = $_REQUEST['idArticulo'];
    }
    if(isset($_REQUEST['presentacion'])&&$_REQUEST['presentacion']!=''){
        $arrParam['tipoCuenta'] =$_REQUEST['presentacion']; // es la forma en que conte util para el divisor.

    }

    if (isset($_REQUEST['cantidadContada']) && $_REQUEST['cantidadContada'] != '') {
        $arrParam['cantidadContada'] = $_REQUEST['cantidadContada'];
    }
    if (isset($_REQUEST['unidad']) && $_REQUEST['unidad'] != '') {
        $arrParam['cantidadContadaUnidad'] = $_REQUEST['unidad'];
    }
    if (isset($_REQUEST['display']) && $_REQUEST['display'] != '') {
        $arrParam['cantidadContadaDisplay'] = $_REQUEST['display'];
    }
    if (isset($_REQUEST['bulto']) && $_REQUEST['bulto'] != '') {
        $arrParam['cantidadContadaBulto'] = $_REQUEST['bulto'];
    }

    if (isset($_REQUEST['cantidadMinimaContada']) && $_REQUEST['cantidadMinimaContada'] != '') {
        $arrParam['cantidadMinimaContada'] = $_REQUEST['cantidadMinimaContada'];
    }

    if (isset($_REQUEST['saldoDeposito']) && $_REQUEST['saldoDeposito'] != '') {
        $arrParam['saldoDeposito'] = $_REQUEST['saldoDeposito'];
    }

    if (isset($_REQUEST['idDeposito']) && $_REQUEST['idDeposito'] != '') {
        $arrParam['idDeposito'] = $_REQUEST['idDeposito'];
    }
    if (isset($_REQUEST['usaLote']) && $_REQUEST['usaLote'] != '') {
        $arrParam['usaLote'] = $_REQUEST['usaLote'];
    }
    if (isset($_REQUEST['tipoAjuste']) && $_REQUEST['tipoAjuste'] != '') {
        $arrParam['tipoAjuste'] = $_REQUEST['tipoAjuste']; // ajuste es saldo directo., movimiento entrada, movimiento salida, 

    }
    if (isset($_REQUEST['detalleAjuste']) && $_REQUEST['detalleAjuste'] != '') {
        $arrParam['detalleAjuste'] = $_REQUEST['detalleAjuste']; // detalle del ajuste pude venir vacio.


    }

    // LOTE DISCONTINUADO NO SE PUEDE AJSUTAR LOTE
    /* 
     tipoAjuste: selectTipoAjuste,
            detalleAjuste: motivoAjuste
        if (isset($_REQUEST['idSaldoLote']) && $_REQUEST['idSaldoLote'] != '') {
            $arrParam['idSaldoLote'] = $_REQUEST['idSaldoLote'];
        }
        if (isset($_REQUEST['idLote']) && $_REQUEST['idLote'] != '') {
            $arrParam['idLote'] = $_REQUEST['idLote'];
        }
        if (isset($_REQUEST['saldoLote']) && $_REQUEST['saldoLote'] != '') {
            $arrParam['saldoLote'] = $_REQUEST['saldoLote'];
        }
    */
    logInventario('mov',json_encode($_REQUEST));
    altaMovimientoEntrada($conexionT, $arrParam);
} // fin moviento inventario.


if(isset($_GET['guardarCodigoBarra'])&&$_GET['guardarCodigoBarra']==1){
// guardarCodigoBarra: 1,
// idArticulo: IdArticuloInventario,
// tipoCodigo: codigoSelect.value,
// codigo: codigoManual
    $arrParam=array();
    $arrParam['idArt'] = $_GET['idArticulo'];
    $arrParam['tipoCodBarra'] = $_GET['tipoCodigo'];
    $arrParam['codBarra'] = $_GET['codBarra'];
    
    guardarCodBarra($conexionT,$arrParam);



}

// *  trae listado de tipos de movimiento
if(isset($_GET['listarTipoMovimiento'])&&$_GET['listarTipoMovimiento']==1){
    listaTipoMovimientoStock();
}

// * alta de imagenes 

if(isset($_POST['guardarImagen'])&&$_POST['guardarImagen']==1){
    $arrParam = array();
    $arrParam['idArticulo'] = $_POST['idArticulo'];
    $arrParam['imagenNueva'] = $_FILES['imagenNueva'];
//    $binary_data = file_get_contents('php://input');

//    // Hacer algo con los datos binarios, por ejemplo, guardarlos en un archivo
//   $jsonBinario = json_encode($binary_data);
    //  echo '<pre>';
//     print_r($_REQUEST);
    //  print_r($_FILES);
//     print_r($jsonBinario);
//     print_r($binaryData);
    //  echo '</pre>';
    altaImagenAdministranet($conexionT,$arrParam);

}

// * borrar una imagen
if(isset($_GET['borrarImagen'])&&$_GET['borrarImagen']==1){
    // borrarImagen: 1
    // idArticulo: 5209
    // idArticuloFoto: 1942
    $arrParam= array();
    $arrParam['idArticuloFoto']= $_GET['idArticuloFoto'];
    borrarImagenAdministranet($conexionT,$arrParam);
}

// * coloco esta imagen como foto principal.
if(isset($_GET['fotoPrincipal'])&&$_GET['fotoPrincipal']==1){
    $arrParam = array();
    $arrParam['idArticuloFoto'] = $_GET['idArticuloFoto'];
    $arrParam['idArticulo'] = $_GET['idArticulo'];
    ponerFotoPrincipal($conexionT,$arrParam);
}

// * edicion de datos de articulo

if(isset($_POST['guardarDatosProducto'])&& $_POST['guardarDatosProducto']==1){
    $arrParam = array();
    /*
     idARticulo: IdArticuloInventario,
            nombreARticulo: nombreARticulo.value,
            nombreArticuloEComm: nombreArticuloEComm.value,
            detalle: detalle.value
            guardarDatosProducto: 
1
idArticulo: 
5209
nombreArticulo: 
TURRON DE MANI ARCOR 50X25GR FIBRA (4)
nombreArticuloEComm: 
TURRON DE MANI ARCOR FIBRA 25GR X50U (COD 5209) jj
detalle:
     */
    $arrParam['idArticulo'] = $_POST['idArticulo'];
    $arrParam['nombreArticulo'] = $_POST['nombreArticulo'];
    $arrParam['nombreArticuloEcomm'] = $_POST['nombreArticuloEComm'];
    $arrParam['detalle'] =$_POST['detalle'];

    guardarDatosArticulo($conexionT,$arrParam);
}
// traer lotes x
// traer depositosx 

// crear movimiento de stock.
// crear listado de movimientos stocks.
// 