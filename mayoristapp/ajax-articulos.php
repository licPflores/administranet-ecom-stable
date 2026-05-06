<?
error_reporting(E_ERROR);
ini_set("display_errors", 1);
set_time_limit(0);
define('LOGERROR','Si');
// define('LOGERROR','No');

// funcion que loging de error

function logError($mensaje)
{
	// guardar log de errores 
	//$urlLog ='../log/log_errores_'.date('Y-m-d_H').'.txt';
    if(defined('LOGERROR') && LOGERROR=='Si'){
        $urlLog = 'log/log_errores_' . date('Y-m-d_H_i') . '.txt';
        $mensajeLog = date('Y-m-d H:i:s') .' ==> '.$mensaje.PHP_EOL;
        file_put_contents($urlLog, $mensajeLog, FILE_APPEND);
    }   
	
}


class articulos
{

    private $colArticulos = array();
    private $connV = null;

    function __construct($connV)
    {
        $this->connV = $connV;
    }

    public function propColArt()
    {
        return count($this->colArticulos);
    }

    // * saldo pendiente No calculado.
    private function buscar_saldo_pendiente_no_calculado()
    {
        $arrProductos = $this->colArticulos;
        foreach ($arrProductos as $id => $producto) {
            $saldo = $producto->saldo;
            $saldoCliente = $producto->saldo;
            if (property_exists($producto, 'saldoCliente')) {
                $saldoCliente = $producto->saldoCliente;
            }
            $saldoPendiente = $producto->saldo;
            $calculo = $saldo - $saldoCliente;
            $saldoPendiente = $calculo;
            //* valor negativo.
            if ($calculo < 0) {
                $saldoPendiente = 0;
            }

            $producto->stockDisponible = $saldoPendiente;
            $arrProductos[$id] = $producto;
        }


        //echo "</pre>";
        $this->colArticulos = $arrProductos;
    }

    // * saldo pendiente calculado en caso que no se ejecute script
    private function buscar_saldo_pendiente($arrIdPendiente, $idDeposito)
    {
        $multiplicadorEmbalaje = 1;
        //$idDeposito = $_SESSION['deposito'];
        $arrProductos = $this->colArticulos; // productos ya asignados al objeto
        $inProductos = implode(',', $arrIdPendiente);
        $sqlSaldosPedidos = "SELECT 
                                stockp.IDArt,       			
                            SUM(stockp.salida)  AS saldo_pedido_cliente 
                            FROM stockp
                            LEFT JOIN comp_ped ON (stockp.CodigoMovimiento = comp_ped.CodigoMovimiento) 
                            WHERE 
                            stockp.IDArt IN(" . $inProductos . ")
                            AND stockp.CodDeposito=" . $idDeposito . "
                            AND (comp_ped.Estado = 'Pendiente' OR comp_ped.Estado = 'En Preparación' ) 
                            AND comp_ped.Anulado = 'No'
                            AND  comp_ped.TipoComprobante = 'PED' 
                            GROUP BY stockp.IDArt";
        // la conexion debe ser a la base real. 
        //echo "<pre>";
        //print_r($sqlSaldosPedidos);
        // echo "</pre>";                          
        $connBaseCarrito = $this->connV;
        if ($connBaseCarrito) {
            $hacerPendiente = mysqli_query($connBaseCarrito, $sqlSaldosPedidos) or die('No puedo recueprar pendientes ' . mysqli_error($connBaseCarrito) . ' SQL:' . $sqlSaldosPedidos . PHP_EOL);
            //echo "<pre>";
            while ($pendiente = mysqli_fetch_assoc($hacerPendiente)) {
                //print_r($pendiente);
                $idProducto = $pendiente['IDArt'];
                $producto = $arrProductos[$idProducto]; // producto recuperado del array.

                //echo "Producto:<br>";
                //print_r($producto);
                $saldo = $producto->saldo;
                $saldoPendiente = $producto->saldo;
                //echo "haysaldo=".var_dump($pendiente['saldo_pedido_cliente'])."<br>";
                if ($pendiente['saldo_pedido_cliente'] != null) {
                    if ($_SESSION['utilizaEmbalaje'] == 'Si') {
                        $multiplicadorEmbalaje = $producto->multiplicador_comp;
                    }
                    $saldoPendiente = $saldo - ($pendiente['saldo_pedido_cliente'] / $multiplicadorEmbalaje);
                    if ($saldoPendiente < 0) {
                        // resultado negativo seteo en CERO
                        $saldoPendiente = 0;
                    }
                    //$saldoPendiente=7;
                    // seteo el nuevo saldo pendiente
                    // edito el valor pendiente en el productos separado.
                    //  echo "Antes:<br>";
                    //print_r($producto);

                    $producto->stockDisponible = $saldoPendiente;
                    //echo "Despues<br>";
                    //print_r($producto);
                    $arrProductos[$idProducto] = $producto;
                }
            }
            //echo "</pre>";
            $this->colArticulos = $arrProductos;
        }
    }

    private function busqueda($arrParam = null)
    {
        // parametros que recibe la funcion buscar PRoducto antiguo.
        // $queCampo = null,
        // $rubro = null,
        // $subrubro = null,
        // $marca = null,
        // $modelo = null,
        // $laboratorio = null,
        // $buscRapida = null,
        // $idArt = null,
        // $idDeposito = null,
        // $claseBusca = null,
        // $tipoCliente = null,
        // $idTipoCliente = null,
        // $codCliente = null,
        // $desde = null,
        // $hasta = null,
        // $artFactura = null,
        // $misConsumos = null,
        // $categoria = null,
        // $promo = null
        // $tipoListado =null



        // echo 'Parametros busqueda<pre>',print_r($arrParam),'</pre>'.PHP_EOL;
        // pasando parametros nuevos==>>
        //  $queCampo =  $arrParam['queCampo'];
        $rubro =  $arrParam['rubro'];
        $subrubro =  $arrParam['subrubro'];
        $marca =   $arrParam['marca'];
        $modelo =   $arrParam['$modelo'];
        $laboratorio =  $arrParam['laboratorio'];
        $buscRapida =   $arrParam['buscRapida'];
        $idArt =  $arrParam['idArt'];
        $claseBusca =  $arrParam['claseBusqueda'];
        $tipoCliente =  $arrParam['tipoCliente'];
        $idTipoCliente =  $arrParam['idTipoCliente'];
        $codCliente =  $arrParam['codCliente'];
        $desde = $arrParam['desde'];
        $hasta =  $arrParam['hasta'];
        $artFactura =  $arrParam['artFactura'];
        $misConsumos =  $arrParam['consumo'];
        $categoria =  $arrParam['categoria'];
        $promo =  $arrParam['promo'];
        $tipoListado = null;
        $imagenProducto = 'No';
        $proveedor=null;
        $tacc=null;
        // compatibilidad con los muchachos.
        if (key_exists('tipoListado', $arrParam)) {
            $tipoListado = $arrParam['tipoListado']; // si es consumo, ranking lista p
        }

        // fotos en la lista de precios.
        if (key_exists('imagenProducto', $arrParam)) {
            $imagenProducto = $arrParam['imagenProducto']; // si es consumo, ranking lista p
        }
        //  proveedor
        if (key_exists('proveedor', $arrParam)) {
            $proveedor = $arrParam['proveedor']; // si es consumo, ranking lista p
        }

        // tacc
        if (key_exists('tacc', $arrParam)) {
            $tacc = $arrParam['tacc']; // si es consumo, ranking lista p
        }
        
        // mis consumos debo obtener cliente si fue generado
        if($misConsumos==1){
            // echo '<pre>mis consumos: =>',var_dump($misConsumos),'>codcliente: =>',var_dump($codCliente),'</pre>';
            if($codCliente==null){
                $codCliente = $_SESSION['idcliente'];
            }
        }

        $filtro = "";
        $campoReglaPrecio = "";
        $sqlReglaPrecio = "";
        $limite = " LIMIT 100";
        $usoRegla = $_SESSION["usaReglaPrecio"];
        $usaIdManual = $_SESSION["usa_id_manual"];
        $idDeposito =  $_SESSION['deposito'];
        $verStockCero = "Si";
        // si existe la vision se instancia.
        // echo "<pre>";
        // print_r($_SESSION["verStockCero"]);
        // echo "</pre>";
        if (isset($_SESSION["verStockCero"])) {
            $verStockCero = $_SESSION["verStockCero"];
        }

        $catNo = "";
        if (isset($_SESSION["categoriaNo"])) {
            $filtro .= " AND cat.id_categoria NOT IN(" . join(",", $_SESSION["categoriaNo"]) . ") ";
        }




        /* reglas de precio si tengo habilitado configuro su uso pero ademas en el caso
            de la lista de precios si no he elegido un cliente regla general        */
        if ($usoRegla == "Si" && $codCliente != null) {
            $campoReglaPrecio = "rp.tipo_calculo,rp.importe_regla,";
            $sqlReglaPrecio = "LEFT JOIN reglas_precio AS rp ON  
                            (rp.id_articulo = articulo.IDArt 
                            AND rp.id_cliente={$codCliente} 
                            AND  ('" . date('Y-m-d') . "' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
                            AND rp.anulado='No' )";
            //            echo "<pre>";
            //        echo "usoRegla::=>{".var_dump($usoRegla)."}<br>";
            //        echo "codCliente::=>{".var_dump($codCliente)."}<br>";
            //        echo "sql:=>>{".print_r($sqlReglaPrecio)."}";
            //        echo "</pre>";

        }
        if ($categoria) {
            $filtro .= " AND rubro.id_categoria=" . $categoria.PHP_EOL;
        }
        if ($rubro) {
            $filtro .= " AND articulo.CodigoRubro=" . $rubro.PHP_EOL;
        }
        if ($subrubro) {
            $filtro .= " AND articulo.IdSubRubro=" . $subrubro.PHP_EOL;
        }
        if ($marca) {
            $filtro .= " AND articulo.CodigoMarca=" . $marca.PHP_EOL;
        }
        if ($modelo) {
            $filtro .= " AND articulo.CodigoModelo=" . $modelo.PHP_EOL;
        }
        if ($laboratorio) {
            $filtro .= " AND articulo.CodLaboratorio=" . $laboratorio.PHP_EOL;
        }
        // busca promociones
        if ($promo) {
            $filtro .= " AND (articulo.promocion='Si' AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "')".PHP_EOL;
        }

        // busca

        // proveedor
        if($proveedor){
            $filtro .= " AND articulo.CodigoProveedor='".$proveedor."'".PHP_EOL;
        }
        


        


        if ($tipoCliente != 'no' && $tipoCliente != NULL) {
            // consumos del cliente
            if ($tipoCliente == 'consumo') {
                if ($codCliente != null) {
                    $filtro .= " AND stock.CodigoCP=" . $codCliente;
                }

                if ($desde != null && $hasta != null) {

                    $filtro .= " AND stock.Fecha BETWEEN '{$desde}' AND '{$hasta}' ";
                } else {
                    $desde = date('Y-m-d', strtotime('-1 year'));
                    $hasta = date('Y-m-d');
                    $filtro .= " AND stock.Fecha BETWEEN '{$desde}' AND '{$hasta}' ";
                }
            }

            // catalogo por busqueda
            if ($tipoListado == "catalogo") {
                $limite = "LIMIT 250";
            }
            // catalogo por busqueda
            if ($tipoListado == "consumo") {
                $limite = "";
            }
            // ranking
            if ($tipoListado == "ranking") {
                $limite = "LIMIT 30";
            }
            // lista de precio pdf
            if ($tipoListado == "listap") {
                $limite = "";
            }
            if ($tipoListado == "listaprecioPDF") {
                $limite = "";
            }
            if ($tipoListado == "listaPromociones") {
                $limite = "";
            }
        }

        if (($idTipoCliente != null) && ($idTipoCliente <> 0)) {

            // tipo de cliente

            $filtro .= " AND tipo_cliente.IDTipoCliente=" . $idTipoCliente;
        }

        // mis consumos slo para consumos y lista precio
        if ($misConsumos == 1 && $tipoListado != "consumo") {

            $filtro .= " AND stock.CodigoCP=" . $codCliente;

            $desde = date('Y-m-d', strtotime('-1 year'));
            $hasta = date('Y-m-d');
            $filtro .= " AND stock.Fecha BETWEEN '{$desde}' AND '{$hasta}' ";
        }

        if ($misConsumos == 1 && $tipoListado == "no" && $codCliente != null) {
            $filtro .= " AND stock.CodigoCP=" . $codCliente;
            $desde = date('Y-m-d', strtotime('-1 year'));
            $hasta = date('Y-m-d');
            $filtro .= " AND stock.Fecha BETWEEN '{$desde}' AND '{$hasta}' ";
            // puedo limitar el ranking
        }

        // uso regla de precio.
        if ($codCliente == null) {
            $codCliente = 0;
        }
        // lista de mis consumos
        //        echo "Artics{<pre>";
        //                print_r($artFactura);
        //                echo "</pre>}";
        if ($artFactura == null) {
            if ($idArt) {
                // si es ideart manual....
                if ($usaIdManual == "Si") {
                    $filtro .= " AND articulo.id_manual = '" . $idArt . "'";
                }
                if ($usaIdManual == 'No') {
                    $filtro .= " AND articulo.IDArt = '" . $idArt . "'";
                }
            }
        } else {
            // vengo desde el remito de facturas sin stock.
            $filtro .= " AND articulo.IDArt IN(";
            $filtro .= implode(",", array_keys($artFactura));
            $filtro .= ")";
            //            foreach($artFactura as $id=>$ar){
            //                
            //                $filtro .= " AND articulo.IDArt = ".$id;
            //            }
        }

        // en ambas busquedas van a buscar por nombre articulo codigo 
        // pero a menos que se especifique no se buscara en ningun lado mas.
        // echo 'ClaseBusca<pre>', var_dump($claseBusca == "texto"),'</pre>';
        // * busqueda rapida, no me importan los otros filtros porque estoy buscando lo que yo quiero.
        // ------------------------------------------------------------------------------------------
        // buscamos en ese orden y en orden contrario o sea blanco negro y negro blanco.
        if ($claseBusca == "texto" || $claseBusca=="filtro") {
            // si soy busqueda rapida solamente, sin filtro piso los filtros.
            if($claseBusca=="texto"){
                $filtro="";
            }


            preg_match_all('/\w+/', $buscRapida, $matches);    // match words
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
            $filtro .=" AND (";
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
        }

        // busco por codigo
        if ($claseBusca == "codigo") {
            // reinicio filtro quiero ese producto.
            $filtro ="";
            if ($usaIdManual == "Si") {

                $filtro .=  " AND articulo.id_manual='{$idArt}'";
            }

            if ($usaIdManual == "No") {
                $filtro .= " AND articulo.IDArt='{$idArt}'";
            }
        }


        // tacc
        if($tacc){
            $filtro .= " AND articulo.sin_tacc='".$tacc."'".PHP_EOL;

        }
    


        // solo productos con stock
        if (isset($verStockCero) && $verStockCero == 'No') {
            // stock cero no lo veo.
            $filtro .= " AND  stock_deposito.saldo >0 ";
        }

        $paramSql = array();
        $paramSql['codCliente'] = $codCliente;
        $paramSql['filtro'] = $filtro;
        $paramSql['limite'] = $limite;
        $paramSql['idDeposito'] = $idDeposito;


        // diferenciar por tipo de cliente si es consumo o lista de precio.
        // lista de precios
        //========================
        if ($tipoListado == 'listap' || $tipoListado == 'listaprecioPDF') {


            $impInterno = "";
            $join = "";
            //* impuesto interno calculo Nuevo
            if (isset($_SESSION['usa_impuesto_interno_abm']) && $_SESSION['usa_impuesto_interno_abm'] == "Si") {


                $join .= " LEFT JOIN impuesto_interno_abm AS interno ON interno.id_impuesto_interno_abm = articulo.id_impuesto_interno_abm";
                $impInterno .= "interno.descripcion_impuesto_interno AS interno_descripcion, ";
                $impInterno .= "interno.tipo_impuesto_interno AS interno_tipo, ";
                $impInterno .= "interno.porcentaje AS interno_porcentaje, ";
                $impInterno .= "interno.monto_fijo AS interno_monto_fijo, ";
                $impInterno .= "interno.peso_calculo AS interno_peso_calculado, ";
                $impInterno .= "interno.pago_minimo AS interno_pago_minimo, ";
                $impInterno .= "interno.id_unimed AS interno_id_unimed ,";
            }
            $paramSql['join'] = $join;
            $paramSql['impInterno'] = $impInterno;
            // no he seleccionado ni consumos ni tipo de clientes puedo buscar lista generica
            if ($idTipoCliente == 0 && $misConsumos != 1) {
                $sqlArticulo = $this->armarSqlListaPrecios($paramSql);
            }
            // consumos , tipo de cliente, consulta tabla stock.
            if ($idTipoCliente != 0 || $misConsumos == 1) {

                $sqlArticulo = $this->armarSqlListaPrecioStock($paramSql);
            }
        }

        // * lista de promociones.

        if ($tipoListado == 'listaPromociones') {


            $impInterno = "";
            $join = "";
            //* impuesto interno calculo Nuevo
            if (isset($_SESSION['usa_impuesto_interno_abm']) && $_SESSION['usa_impuesto_interno_abm'] == "Si") {


                $join .= " LEFT JOIN impuesto_interno_abm AS interno ON interno.id_impuesto_interno_abm = articulo.id_impuesto_interno_abm";
                $impInterno .= "interno.descripcion_impuesto_interno AS interno_descripcion, ";
                $impInterno .= "interno.tipo_impuesto_interno AS interno_tipo, ";
                $impInterno .= "interno.porcentaje AS interno_porcentaje, ";
                $impInterno .= "interno.monto_fijo AS interno_monto_fijo, ";
                $impInterno .= "interno.peso_calculo AS interno_peso_calculado, ";
                $impInterno .= "interno.pago_minimo AS interno_pago_minimo, ";
                $impInterno .= "interno.id_unimed AS interno_id_unimed ,";
            }
            $paramSql['join'] = $join;
            $paramSql['impInterno'] = $impInterno;
            // no he seleccionado ni consumos ni tipo de clientes puedo buscar lista generica
            

            $sqlArticulo = $this->armarSqlListaPromociones($paramSql);
            
        }
        // lista de precios consumos.
        //============================
        if ($tipoListado == 'consumo') {
            //echo "consumito";
            $sqlArticulo = $this->armarSqlConsumos($paramSql);
        }
        // lista de precios consumos.
        //============================
        if ($tipoListado == 'catalogo') {
            // sin consumos activados.
            if ($misConsumos != 1) {
                $sqlArticulo = $this->armarSqlCatalogo($paramSql);
            }
            // filtro mis consumos activado
            if ($misConsumos == 1) {
                $sqlArticulo = $this->armarSqlCatalogoMisConsumos($paramSql);
            }
        }

        // * lista de precios consumos.
        //============================
        if ($tipoListado == 'ranking') {
            $sqlArticulo = $this->armarSqlRanking($paramSql);
        }


        //* busqueda productos para CARRITO ( pedidos, remitos,etc)
        if ($tipoCliente == null) {
            // controlar si el campo multiplica cantidad de venta existe en la tabla articulo 
            $sqlCampo = "SHOW COLUMNS FROM articulo LIKE 'multiplo_cantidad_vta'";
            $hacerCampo = mysqli_query($this->connV, $sqlCampo) or die('No puedo comprobar campo ' . mysqli_error($this->connV) . ' SQL:' . $sqlCampo . PHP_EOL);
            $existeCampo = mysqli_num_rows($hacerCampo);
            if ($existeCampo > 0) {
                $paramSql['existeCampoMultiplica'] = true;
            } else {
                $paramSql['existeCampoMultiplica'] = false;
            }

        // echo "busar productos";
            $tabla = "articulo";
            $agrupo = "";
            $join = "";
            $promedio = "";
            $impInterno = "";
            if ($misConsumos == 1) {
                $tabla = "stock";
                $promedio = "ROUND(AVG(stock.Cantidad)) AS CantidadProm,";
                $agrupo = " GROUP BY stock.IDArt ";
                $join = " LEFT JOIN articulo ON articulo.IDArt=stock.IDArt ";
            }
            //* impuesto interno calculo Nuevo
            if (isset($_SESSION['usa_impuesto_interno_abm']) && $_SESSION['usa_impuesto_interno_abm'] == "Si") {
                $join .= " LEFT JOIN impuesto_interno_abm AS interno ON interno.id_impuesto_interno_abm = articulo.id_impuesto_interno_abm";
                $impInterno .= "interno.descripcion_impuesto_interno AS interno_descripcion, ";
                $impInterno .= "interno.tipo_impuesto_interno AS interno_tipo, ";
                $impInterno .= "interno.porcentaje AS interno_porcentaje, ";
                $impInterno .= "interno.monto_fijo AS interno_monto_fijo, ";
                $impInterno .= "interno.peso_calculo AS interno_peso_calculado, ";
                $impInterno .= "interno.pago_minimo AS interno_pago_minimo, ";
                $impInterno .= "interno.id_unimed AS interno_id_unimed ,";
            }

            $paramSql['tabla'] = $tabla;
            $paramSql['agrupo'] = $agrupo;
            $paramSql['join'] = $join;
            $paramSql['promedio'] = $promedio;
            $paramSql['impInterno'] = $impInterno;


            $sqlArticulo = $this->sqlArticuloComprobantes($paramSql);
        }

        // echo 'conexion::<pre>',var_dump($this->connV),'</pre>',PHP_EOL;
        $hacerArt = mysqli_query($this->connV, $sqlArticulo) or die('No me puedo conectar ' . mysqli_error($this->connV) . '<br> sql:' . $sqlArticulo);
        // echo "<pre>";
        logError("SQL Articulos: " . $sqlArticulo);
        // echo print_r($sqlArticulo);
        // echo "</pre>";
        // echo 'SQL TRAE PROUCTOS <pre>',print_r($sqlArticulo),'</pre>'.PHP_EOL;
        // exit(0);
        if (!empty($this->colArticulos)) {
            // inicializo la variable x las dudas
            $this->colArticulos = array();
        }

        $listaProductosId = array();
        while ($articulo = mysqli_fetch_object($hacerArt)) {
            $this->colArticulos[$articulo->IDArt] = $articulo;
            $listaProductosId[] = $articulo->IDArt;
        }

        # Stock Disponible analisis.
        // comentada por temas de velocidad
        // if (!empty($listaProductosId)) {
        //     $this->buscar_saldo_pendiente($listaProductosId, $idDeposito);
        // }

        // * stock disponible No calculado
        // if (!empty($listaProductosId)) {
        $this->buscar_saldo_pendiente_no_calculado();
        // }
    }

    // * # funciones de sql para diferentes objetivos,
    // * =======================================================================
    // * sql tipo cliente: Ranking
    private function armarSqlRanking($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $sql = "SELECT
        stock.IDArt,
        COUNT(stock.IDArt) as Cuantos,
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        modelo.NombreModelo AS Modelo,     
        articulo.PrecioCosto,                   
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic, 
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
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
        articulo.promocion_vigencia_desde,
        articulo.promocion_vigencia_hasta,
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni, 
        unidmed.descrip_corta AS nombre_unimed,
        articulo.CodigoProveedor,
        presentacion_abm.nombre_presentacion, 
        articulo_prov.id_presentacionC, 
        articulo_prov.id_unimed,
        rp.tipo_calculo,
        rp.importe_regla,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM stock                                                       
            LEFT JOIN articulo ON articulo.IDArt = stock.IDArt
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
            
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            LEFT JOIN reglas_precio AS rp ON  
            (rp.id_articulo = articulo.IDArt 
            AND rp.id_cliente={$codCliente} 
            AND  ('" . date('Y/m/d') . "' BETWEEN rp.vigencia_desde AND rp.vigencia_hasta) 
            AND rp.anulado='No' ) 
            WHERE 
            articulo.Discontinuo='No' 
            AND articulo.disponible_vta='Si'                           
            AND articulo.tipo_art='Articulo' 
            {$filtro}
            GROUP BY stock.IDArt    
          ORDER BY rubro.CodigoRubro ASC, 
                   subrubro.NombreSubRubro ASC, 
                   articulo.NombreArticulo ASC 
            {$limite};";

        return $sql;
    }

    // * sql de consumos solo.
    private function armarSqlConsumos($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];

        $sql = "SELECT
        stock.IDArt,
        SUM(stock.Cantidad) AS Cuantos,
        ROUND(AVG(stock.Cantidad)) AS CantidadProm,
        articulo.Alicuota,
        articulo.AlicuotaIB,     
        articulo.PrecioCosto,                                           
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial,
        rubro.id_categoria,
        articulo.CodigoMarca,
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic,
        cat.nombre_categoria As NombCategoria,
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
        marca.NombreMarca AS Marca,
        modelo.NombreModelo AS Modelo, 
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
        articulo.promocion_vigencia_desde,
        articulo.promocion_vigencia_hasta,
        articulo.CodigoProveedor,
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni, 
        unidmed.descrip_corta AS nombre_unimed,  
        presentacion_abm.nombre_presentacion, 
        articulo_prov.id_presentacionC,
        {$campoReglaPrecio}
        articulo_prov.id_unimed,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
       mart.descrip_corta AS uniArt
        FROM stock                                                       
            LEFT JOIN articulo ON articulo.IDArt = stock.IDArt
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)                            
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
            {$sqlReglaPrecio}
            WHERE 
            articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'                            
            AND articulo.tipo_art='Articulo' 
            {$filtro}
            GROUP BY stock.IDArt    
          ORDER BY  
                  cat.nombre_categoria ASC,
                   rubro.NombreRubro ASC, 
                   subrubro.NombreSubRubro ASC, 
                   articulo.NombreArticulo ASC 
            {$limite};";
        return $sql;
    }

    // * sql de catalogo Mis consumos ACTIVADO ( mostrar productos en catalogo vista catalogo obsoleto)
    private function armarSqlCatalogoMisConsumos($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];

        $sql = "SELECT
        articulo.IDArt,
        ROUND(AVG(stock.Cantidad)) AS CantidadProm,                        
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        marca.CodMarca,
        modelo.NombreModelo AS Modelo, 
        articulo.PrecioCosto,                        
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic,
         cat.nombre_categoria AS NombreCategoria,
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
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
        articulo.promocion_vigencia_desde,
        articulo.promocion_vigencia_hasta,
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni, 
        unidmed.descrip_corta AS nombre_unimed,  
        presentacion_abm.nombre_presentacion, 
        articulo.CodigoProveedor,
        articulo_prov.id_presentacionC,
        {$campoReglaPrecio}
        articulo_prov.id_unimed,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM stock 
            LEFT JOIN articulo ON articulo.IDArt= stock.IDArt
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)                            
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            {$sqlReglaPrecio}
            WHERE 
            articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'
            AND articulo.tipo_art='Articulo' 
            {$filtro}
        GROUP BY stock.IDArt         
          ORDER BY 
                   cat.nombre_categoria ASC, 
                   rubro.NombreRubro ASC, 
                   subrubro.NombreSubRubro ASC, 
                   articulo.NombreArticulo ASC 
            {$limite};";
        return $sql;
    }

    // * sql de catalogo Sin consumos ( vista de catalogo obsoleta)
    private function armarSqlCatalogo($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];
        $sql = "SELECT
        articulo.IDArt,                        
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        marca.CodMarca,
        modelo.NombreModelo AS Modelo,   
        articulo.PrecioCosto,                      
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic, 
        cat.nombre_categoria AS NombreCategoria,
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
        
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
        articulo.promocion_vigencia_desde,
        articulo.promocion_vigencia_hasta,
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni, 
        unidmed.descrip_corta AS nombre_unimed,  
        presentacion_abm.nombre_presentacion, 
        articulo.CodigoProveedor,
        articulo_prov.id_presentacionC,
        {$campoReglaPrecio}
        articulo_prov.id_unimed,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM articulo 
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)                            
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            {$sqlReglaPrecio}
            WHERE 
			articulo.tipo_art='Articulo'
            AND articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'
            
            AND rubro.anulado='No'
             
            {$filtro}                            
          ORDER BY 
                   cat.nombre_categoria ASC,
                   rubro.NombreRubro ASC, 
                   subrubro.NombreSubRubro ASC, 
                   articulo.NombreArticulo ASC 
            {$limite};";
        return $sql;
    }

    // * sql lista de precios para todos (sin cliente seleccionado)
    private function armarSqlListaPrecios($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];
        $idDeposito = $param['idDeposito'];
        $join = $param['join'];
        $impInterno = $param['impInterno'];
        $sql = "SELECT 
        articulo.id_manual,
        articulo.tipo_art,
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        modelo.NombreModelo AS Modelo,
        articulo.PrecioCosto, 
        articulo.IDArt,
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.Moneda,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic, 
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
        cat.nombre_categoria AS NombCategoria,
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
        articulo.lote,                       
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni,                     
        articulo_prov.id_unimed,                        
        articulo_prov.cantidad_unidad_display,
        articulo_prov.cantidad_display_bulto,
        articulo_prov.cantidad_bulto_pallet,
        articulo_prov.recargo_fraccion,
        articulo_prov.recargo_fraccion_porcentaje,
        articulo_prov.cantidad_unidad_lista2,
        articulo_prov.cantidad_unidad_lista3,
        articulo_prov.precio_unidad AS tipoPrecioUnidad,
        articulo.sin_tacc,
        unidmed.descrip_corta AS nombre_unimed,  
        presentacion_abm.nombre_presentacion,
        articulo.CodigoProveedor,
        ecom.nombre_articulo_ecom,                        
        ecom.usa_nombre_articulo_ecom,
        {$campoReglaPrecio}
        {$impInterno}
        stock_deposito.saldo,
        (stock_deposito.saldo - stock_deposito.saldo_pedido_cliente) AS disponible,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM  articulo 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed)
            {$join}
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
            LEFT JOIN stock_deposito ON stock_deposito.id_articulo = articulo.IDArt AND stock_deposito.id_deposito ={$idDeposito} 
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
            {$sqlReglaPrecio}
            WHERE 
			 articulo.tipo_art='Articulo'
			AND articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si' 
            AND rubro.anulado='No'
            
            
            {$filtro}
          ORDER BY 
                cat.nombre_categoria ASC,
                rubro.NombreRubro ASC, 
                subrubro.NombreSubRubro ASC, 
                articulo.NombreArticulo ASC 
            {$limite};";
        return $sql;
    }
    // * listado de promociones como lista de precio pero siemrep con foto.
    private function armarSqlListaPromociones($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];
        $idDeposito = $param['idDeposito'];
        $join = $param['join'];
        $impInterno = $param['impInterno'];
        $sql = "SELECT 
        articulo.id_manual,
        articulo.tipo_art,
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        modelo.NombreModelo AS Modelo,
        articulo.PrecioCosto, 
        articulo.IDArt,
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.Moneda,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic, 
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
        cat.nombre_categoria AS NombCategoria,
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
        articulo.lote,                       
        articulo_prov.multiplicador_comp,
                    articulo_prov.cantidad_uni,                     
                    articulo_prov.id_unimed,                        
                    articulo_prov.cantidad_unidad_display,
                    articulo_prov.cantidad_display_bulto,
                    articulo_prov.cantidad_bulto_pallet,
                    articulo_prov.recargo_fraccion,
                    articulo_prov.recargo_fraccion_porcentaje,
                    articulo_prov.cantidad_unidad_lista2,
                    articulo_prov.cantidad_unidad_lista3,
                    articulo_prov.precio_unidad AS tipoPrecioUnidad,
        unidmed.descrip_corta AS nombre_unimed,  
        presentacion_abm.nombre_presentacion,
        articulo.CodigoProveedor,
                      
        {$campoReglaPrecio}
        {$impInterno}
        stock_deposito.saldo,
        (stock_deposito.saldo - stock_deposito.saldo_pedido_cliente) AS disponible,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM  articulo 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed)
            {$join}
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
            LEFT JOIN stock_deposito ON stock_deposito.id_articulo = articulo.IDArt AND stock_deposito.id_deposito ={$idDeposito} 
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            {$sqlReglaPrecio}
            WHERE 
			articulo.tipo_art='Articulo' 
			AND articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'
            articulo.promocion_vigencia_desde<='" . date('Y-m-d') . "' 
            AND articulo.promocion_vigencia_hasta>='" . date('Y-m-d') . "'             
            AND rubro.anulado='No'
            
           
            {$filtro}
          ORDER BY 
                articulo.promocion_por DESC,
                cat.nombre_categoria ASC,
                rubro.NombreRubro ASC, 
                subrubro.NombreSubRubro ASC, 
                articulo.NombreArticulo ASC 
            {$limite};";
        return $sql;
    }


    // * sql lista de precios sale del stock pude vernir x cliente tipo de cliente y consumos.
    private function armarSqlListaPrecioStock($param)
    {
        $codCliente = $param['codCliente'];
        $filtro = $param['filtro'];
        $limite = $param['limite'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];
        $idDeposito = $param['idDeposito'];
        $join = $param['join'];
        $impInterno = $param['impInterno'];


        $sql = "SELECT 
        articulo.id_manual,
        articulo.tipo_art,
        articulo.Alicuota,
        articulo.AlicuotaIB,
        marca.NombreMarca AS Marca,
        modelo.NombreModelo AS Modelo,
        articulo.IDArt,
        articulo.PrecioCosto, 
        articulo.Precio1V,
        articulo.Precio2V,
        articulo.Precio3V,
        articulo.Precio4V,
        articulo.Precio5V,
        articulo.PNOficial, 
        articulo.IDSubRubro, 
        articulo.CodigoSubRubro,
        articulo.CodigoRubro,
        articulo.Moneda,
        articulo.CodigoArticuloT,
        articulo.NombreArticulo,
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
        articulo.impuesto_interno,    
        articulo.tipoIVA,
        iva.Alicuota AS Alic,
        cat.nombre_categoria AS NombCategoria,
        rubro.NombreRubro AS NombRub, 
        subrubro.NombreSubRubro AS NombSubRub,
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
        articulo.lote,
       
        tipo_cliente.NombreTipoCliente,
        articulo_prov.multiplicador_comp,
        articulo_prov.cantidad_uni, 
        unidmed.descrip_corta AS nombre_unimed, 
        presentacion_abm.nombre_presentacion,
        articulo.CodigoProveedor,
        articulo_prov.id_presentacionC, 
        articulo_prov.id_unimed,
         {$campoReglaPrecio}
         {$impInterno}
        stock_deposito.saldo,
        (stock_deposito.saldo - stock_deposito.saldo_pedido_cliente) AS disponible,
        articulo.cantidad_promedio_bulto,
        mart.tipo_unidad,
        mart.descrip_corta AS uniArt
        FROM stock
                                      
            LEFT JOIN articulo ON articulo.IDArt = stock.IDArt 
            LEFT JOIN stock_deposito ON stock_deposito.id_articulo = articulo.IDArt AND stock_deposito.id_deposito ={$idDeposito} 
            LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.idArt)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
            {$join}
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = modelo.CodMarca
            LEFT JOIN cliente ON cliente.Codigo=stock.CodigoCP
            LEFT JOIN tipo_cliente ON tipo_cliente.IDTipoCliente = cliente.TipoCliente
            {$sqlReglaPrecio} 
            WHERE 
                stock.tipo_art='Articulo'                                      
            AND articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'
            {$filtro}
        GROUP BY stock.IDArt  
        ORDER BY 
                   
                   cat.nombre_categoria ASC,
                   rubro.NombreRubro ASC, 
                   subrubro.NombreSubRubro ASC, 
                   articulo.NombreArticulo ASC,
                   tipo_cliente.NombreTipoCliente ASC
            {$limite};";
        return $sql;
    }
    // * sql para comprobantes, pedidos,remitos,etc el MAS IMPORTANTE para Carritos
    // * ya que de aqui salen los precios finales con todas las reglas aplicadas.
    private function sqlArticuloComprobantes($param)
    {
        // echo 'parametros busca sql<pre>',print_r($param),'</pre>';

        $promedio = $param['promedio'];
        $campoReglaPrecio = $param['campoReglaPrecio'];
        $impInterno = $param['impInterno'];
        $tabla = $param['tabla'];
        $join = $param['join'];
        $idDeposito = $param['idDeposito'];
        $sqlReglaPrecio = $param['sqlReglaPrecio'];
        $filtro = $param['filtro'];
        $agrupo = $param['agrupo'];
        $limite = $param['limite'];
        $multiploCantidadVenta = $param['existeCampoMultiplica']; // es una variable que vendra con indicador si o no, para agregar el campo al sql 
        if ($multiploCantidadVenta == true) {
            $promedio .= "articulo.multiplo_cantidad_vta AS multiplo_cantidad_vta,";
        } else {
            $promedio .= "";
        }   

        $sql = "SELECT 
                    articulo.id_manual,
                    {$promedio}
                    articulo.tipo_art,
                    articulo.Alicuota,
                    articulo.AlicuotaIB,
                    marca.NombreMarca AS Marca,
                    modelo.NombreModelo AS Modelo,
                    articulo.PrecioCosto, 
                    articulo.IDArt,
                    articulo.Precio1V,
                    articulo.Precio2V,
                    articulo.Precio3V,
                    articulo.Precio4V,
                    articulo.Precio5V,
                    articulo.PNOficial, 
                    articulo.IDSubRubro, 
                    articulo.CodigoSubRubro,
                    articulo.CodigoRubro,
                    articulo.Moneda,
                    articulo.CodigoArticuloT,
                    articulo.NombreArticulo,
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
                    articulo.impuesto_interno,    
                    articulo.tipoIVA,
                    iva.Alicuota AS Alic, 
                    cat.nombre_categoria AS NombreCategoria,
                    rubro.NombreRubro AS NombRub, 
                    subrubro.NombreSubRubro AS NombSubRub,
                    laboratorio.NombreLaboratorio AS Laboratorio,
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
                    #DATE_FORMAT(articulo.promocion_vigencia_hasta,'%W %d de %M del %Y') as fhastaT,
                    #DATE_FORMAT(articulo.promocion_vigencia_desde,'%W %d de %M del %Y') as fdesdeT,
                    articulo.lote,                        
                    articulo.ensamblado,
                    stock_deposito.saldo,
                    stock_deposito.saldo AS stockDisponible, 
                    stock_deposito.saldo_pedido_cliente AS saldoCliente,                   
                    articulo_prov.multiplicador_comp,
                    articulo_prov.cantidad_uni,                     
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
                    unidmed.descrip_corta AS nombre_unimed, 
                    presentacion_abm.nombre_presentacion,
                    articulo.CodigoProveedor,
                    articulo_prov.id_presentacionC, 
                    {$campoReglaPrecio}
                    {$impInterno}
                    articulo_prov.id_unimed,
                    articulo.cantidad_promedio_bulto,
                    mart.tipo_unidad,
                    mart.descrip_corta AS uniArt
        
        FROM {$tabla}
            {$join}
            LEFT JOIN articulo_prov ON (articulo_prov.IDArt = articulo.IDArt)
            #LEFT JOIN articulo_prov ON (articulo_prov.idArt = articulo.IDArt AND articulo_prov.CodProveedor = articulo.CodigoProveedor)
            LEFT JOIN unidmed ON (unidmed.id_unimed = articulo_prov.id_unimed) 
            LEFT JOIN unidmed AS mart ON (mart.id_unimed=articulo.id_unimed)
            LEFT JOIN presentacion_abm ON (presentacion_abm.id_presentacion = articulo_prov.id_presentacionC)
            LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
            LEFT JOIN iva  ON articulo.Alicuota = iva.id 
            LEFT JOIN subrubro ON subrubro.IdSubRubro = articulo.IdSubRubro
            LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
            LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria
            LEFT JOIN modelo ON modelo.CodModelo = articulo.CodigoModelo
            LEFT JOIN marca ON marca.CodMarca = articulo.CodigoMarca
            LEFT JOIN laboratorio ON laboratorio.CodLaboratorio = articulo.CodLaboratorio
            LEFT JOIN stock_deposito ON stock_deposito.id_articulo = articulo.IDArt AND stock_deposito.id_deposito ={$idDeposito} 
            {$sqlReglaPrecio}
            WHERE 
			articulo.tipo_art='Articulo'            
            AND rubro.anulado='No'
            AND articulo.Discontinuo='No'
            AND articulo.disponible_vta='Si'
             
            AND stock_deposito.id_deposito={$idDeposito}
            {$filtro}
          {$agrupo}      
          ORDER BY articulo.NombreArticulo {$limite};";
        return $sql;
    }


    private function tab($n)
    {
        $tabs = null;
        while ($n > 0) {
            $tabs .= "\t";
            --$n;
        }
        return $tabs;
    }


    /*
     * Funcion Vista Html
     */
    function vista_lista_html(
        $listaPrecioCliente = null,
        $descRenglon = null,
        $objCliente = null,
        $quien = null,
        $claseLista = null,
        $imagenProducto = null
    ) {

        $limDescReng    = 0;
        $codCliente     = null;
        $usoBultoCerrado = 'No';
        $usaDisplay = 'No';
        // $imagenProducto='Si';
            // $arrDetallePromo = func_num_args();
            // echo '<pre>','listaPrecioCliente:['.$listaPrecioCliente.']',
            // '</pre>';
        //  echo 'args vista_lista_html <pre>',print_r($arrDetallePromo).'</pre>';
        if (isset($_SESSION['vendedor'])) {

            $objVendedor = $_SESSION['vendedor'];
            $limDescReng    = $objVendedor->lim_desc_renglon;
        }
        if ($objCliente != null) {
            //$listaPrecioCliente = $objCliente->listaPrecio;
            //$descRenglon        = $objCliente->descRenglon;
            $codCliente = $objCliente->Codigo;
        }
        $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
        $tabla = "";

        // bulto cerrado 
        if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
            $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
        }

        // // display 
        if ($_SESSION['utiliza_display'] == "Si") {
            $usaDisplay = $_SESSION['utiliza_display'];
        }
        if (!empty($this->colArticulos)) {


            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            $precioVentaF = 0;
            if ($_SESSION["utilizaEmbalaje"] == "Si") {
                $usaEmbalaje = "Si";
            } else {
                $usaEmbalaje = "No";
            }

            $usaIdManual = $_SESSION["usa_id_manual"];
            // * Iva incluido desde login.
            if (isset($_SESSION['ivaIncluido']) && $_SESSION['ivaIncluido'] != "") {
                $ivaIncluido = $_SESSION["ivaIncluido"];
            }


            $caminoDispo = "";
            if (isset($_SESSION["caminoDispo"])) {
                $caminoDispo = $_SESSION["caminoDispo"];
            }
            $soyMovil = 0;
            if ($caminoDispo != "") {
                $soyMovil = 1;
            }

            //            $cantidad = $_REQUEST['cantidadOferta'];
            //con el tema de la oferta dejo una idea a desarrollar, pasar el id
            //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
            //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
            //            borro todo y coloco un uno
            if (isset($_REQUEST['cantidadOferta']) && $_REQUEST["cantidadOferta"] != "") {
                $cantidad = $_REQUEST['cantidadOferta'];
            } else {
                $cantidad = 1;
            }
            /*
             * RANKING MAS VENDIDOS
             * *****************************************************************
             */
            if ($quien == "ranking") {
                // preguntar si es lista o galeria


                // presentar la tabla con el consumo
                // presentar la tabla para lista de precios html.
                $tabla .=  "<thead>" . PHP_EOL;
                $tabla .=  "<tr>" . PHP_EOL;

                $tabla .=  "<th>rubro</th>" . PHP_EOL;
                $tabla .=  "<th>subrubro</th>" . PHP_EOL;
                $tabla .=  "<th>Cod</th>" . PHP_EOL;
                $tabla .=  "<th class='center'>Artículo</th>" . PHP_EOL;

                $tabla .=  "<th>Cant.</th>" . PHP_EOL;
                $tabla .=  "<th>Lista</th>" . PHP_EOL;

                $tabla .=  "<th>Desc</th>" . PHP_EOL;
                $tabla .=  "<th>Lista c/dcto</th>" . PHP_EOL;
                if ($usaEmbalaje == "Si") {
                    $tabla .=  "<th>Bulto Cerrado</th>" . PHP_EOL;
                }
                $tabla .=  "</tr>" . PHP_EOL;
                $tabla .=  "</thead>" . PHP_EOL;
                $tabla .=  "<tbody>" . PHP_EOL;
                $promoLista = "no";
                foreach ($this->colArticulos as $arti) {
                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = $arti->IDArt;
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;

                    /*
                     * LISTA DE PRECIOS
                     */

                    $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon);
                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        }
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                    }

                    //$hoy3 = $hoy; 
                    $clase = $precios["clase"];
                    $nombreArticulo .= $precios["promoNombre"];
                    $clasePrecio = $precios["clasePrecio"];
                    $promo = $precios["promo"];

                    //                    if($ivaIncluido=='no'){
                    //                        $precioVenta = $precios["neto"];
                    //                        $precioVentaFinal = $precios["netoCalc"];
                    //                    }else{
                    //                        $precioNeto = $precios["neto"];
                    //                        $precioVenta = $precios["precioVenta"];
                    //                        $precioVentaFinal = $precios["precioFinal"];
                    //                    }
                    if ($ivaIncluido == 'no') {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["neto"];

                        $precioVentaFinal = $precios["netoCalc"];
                        $precioVentaFinalIva = $precios["precioFinal"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["netoCalc"];
                        $precioVentaFinalIva = $precios["precioFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, ',', '');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '');
                    $precioNetoF = number_format($precioNeto, 2, ',', '');

                    $tabla .= "<tr>";

                    $tabla .= "<td {$clase}>{$arti->NombRub}</td>";
                    $tabla .= "<td {$clase}>{$arti->NombSubRub}</td>";
                    $tabla .= "<td {$clase}>{$idArtT}</td>";
                    $tabla .= "<td {$clase}>{$nombreArticulo}</td>";

                    $tabla .= "<td {$clase}>{$arti->Cuantos}</td>";
                    $tabla .= "<td class='importe {$clasePrecio}' > <i class='fa fa-dollar-sign'></i><span id='mi-art-precio{$arti->IDArt}'>{$precioVentaF}</span></td>" . PHP_EOL;
                    $tabla .= "<td {$clase}>" . PHP_EOL;

                    if ($promo == 'no') {
                        /*
                         * No tengo promocion
                         */
                        if ($_SESSION['tipousuario'] == 'cliente') {
                            /*
                             * Soy un cliente y tengo descuento pero no puedo tocarlo
                             * debo calcular el precio con el descuento
                             */



                            $tabla .= "{$descFinal}%" . PHP_EOL;
                            $tabla .= "</td>" . PHP_EOL;
                        } else {
                            /*
                             * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                             * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                             */
                            if ($descFinal == 0) {

                                $tabla .= "0%" . PHP_EOL;
                            } else {
                                $tabla .= "{$descFinal}%" . PHP_EOL;
                            }
                            $tabla .= "</td>" . PHP_EOL;
                        }
                    } else {
                        /*
                         * Hay promocion
                         */
                        $tabla .= "{$descFinal}%" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    }
                    $tabla .= "<td class='importe {$clasePrecio}' > <i class='fa fa-dollar-sign'></i><span id='mi-art-precio{$arti->IDArt}'>{$precioVentaFinalF}</span></td>" . PHP_EOL;
                    if ($usaEmbalaje == "Si") {
                        $tabla .= "<td {$clase}>{$bulto}</td>";
                    }
                    $tabla .= "</tr>" . PHP_EOL;
                }
                $tabla .= "</tbody>" . PHP_EOL;
            }
            /*
             * CATALOGO DE PRODUCTOS
             * *****************************************************************
             */
            if ($quien == "catalogo") {
                if ($claseLista == "lista") {
                    // presentar la tabla con el consumo
                    // presentar la tabla para lista de precios html.
                    $tabla .=  "<thead>" . PHP_EOL;
                    $tabla .=  "<tr>" . PHP_EOL;

                    $tabla .=  "<th>&nbsp;</th>" . PHP_EOL;
                    $tabla .=  "<th>&nbsp;</th>" . PHP_EOL;

                    $tabla .=  "</tr>" . PHP_EOL;
                    $tabla .=  "</thead>" . PHP_EOL;
                    $tabla .=  "<tbody>" . PHP_EOL;
                    $promoLista = "no";
                    foreach ($this->colArticulos as $arti) {
                        if ($usaIdManual == "Si") {
                            $idArtT = $arti->id_manual;
                        } else {
                            $idArtT = $arti->IDArt;
                        }
                        $idArt          = $arti->IDArt;
                        $nombreArticulo = $arti->NombreArticulo;

                        /*
                         * LISTA DE PRECIOS
                         */
                        $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);
                        //$precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon); 
                        //                        echo "<pre>";
                        //                        print_r($precios);
                        //                        echo "</pre>";
                        /*
                         * EMBALAJE
                         */
                        $bulto = "";
                        if ($usaEmbalaje == "Si") {
                            // tengo que hacer la busqueda de los valores para mostrar
                            if ($arti->nombre_presentacion != "") {
                                $bulto = "" . $arti->nombre_presentacion . " x " . $arti->cantidad_uni;
                            }
                            if ($arti->nombre_unimed != "") {
                                $bulto .= " (" . $arti->nombre_unimed . ")";
                            }
                        }

                        //$hoy3 = $hoy; 
                        $promoCant = $precios["cantidad"];
                        $promo = $precios["promo"];
                        $tagPromo = "";
                        if ($precios["clasePrecio"] == "promocion") {
                            $tagPromo = '<div class="promocion"><i class="fa fa-gift fa-lg fa-fw"></i> En promoción  </div>';
                        }
                        $clasePrecio = $precios["clasePrecio"];

                        if ($ivaIncluido == 'no') {
                            $precioNeto = $precios["neto"];
                            $precioVenta = $precios["neto"];

                            $precioVentaFinal = $precios["netoCalc"];
                            $precioVentaFinalIva = $precios["precioFinal"];
                        } else {
                            $precioNeto = $precios["neto"];
                            $precioVenta = $precios["neto"];
                            $precioVentaFinal = $precios["netoCalc"];
                            $precioVentaFinalIva = $precios["precioFinal"];
                        }



                        $descFinal = number_format($precios["descuento"], 0);
                        //                formateo los precios a cuatro decimales..just in case...
                        $precioVentaF = number_format($precioVenta, 2, ',', '.');
                        $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
                        $precioNetoF = number_format($precioNeto, 2, ',', '.');
                        $precioVentaFinalIva = number_format($precioVentaFinalIva, 2, ',', '.');
                        // si es consumidor final no dejo ir a pedidos.
                        //                        if($precioVentaFinal ==0){
                        //                            $srcArticulo="#";}
                        //                        else{
                        $srcArticulo = 'articulo-info.php?buscaOferta=si&IDArt=' . $idArt . '&cant=' . number_format($arti->promocion_cant);
                        //                        }
                        if ($descFinal == 0) {
                            $descFinecho = "<div style='width:40px;height:50px;'></div>";
                        } else {
                            $descFinalT = "<div class='oferta'>{$descFinal}%<br><span class='chico'>desc.</span></div>";
                        }
                        $tabla .= '<tr>';
                        $tabla .= '<td >'
                            . '<a href="' . $srcArticulo . '" title="Ver detalle"> '
                            . '<div class="fotoChica">'
                            . '<img src="foto.php?origen=foto1|' . $idArt . '&mini=1">'
                            . '</div>'

                            . '</a></td>';

                        $tabla .= '<td>'
                            . '<div class="divArticuloListaC">'
                            . '<div class="articuloNombre"><a href="' . $srcArticulo . '" title="Ver detalle">' . $nombreArticulo . '</a></div>';
                        if ($descFinal <> 0) {
                            $tabla .= "<div class='precioListaOld'>$" . $precioVentaF . "</div>";
                        }
                        // precio con el descuento
                        $tabla .= "<div class='precioLista' id='mi-art-precio{$arti->IDArt}'>"
                            . "<label>$ " . $precioVentaFinalF . "</label>";
                        if ($descFinal <> 0) {
                            $tabla .= " <span class='verde'>" . $descFinal . "% OFF</span>" . PHP_EOL;
                        }
                        $tabla .= '</div>';

                        $tabla .= "<div class='precioLista' id='mi-art-precio{$arti->IDArt}'>"
                            . "<label>$ " . $precioVentaFinalIva . "</label>";

                        $tabla .= " <span>c/Iva</span>" . PHP_EOL;

                        $tabla .= '</div>';

                        // precio con el descuento

                        $tabla .= '<div class="descLista"><i class=" fa fa-hashtag fa-lg fa-fw"></i> ' . $idArtT . '</div>';
                        $tabla .= $tagPromo;
                        $tabla .= '<div class="descLista"><i class="fa fa-cube fa-lg fa-fw"></i> ' . $arti->NombreCategoria . ', ';
                        $tabla .= '' . $arti->NombRub . ', ';
                        $tabla .= '' . $arti->NombSubRub . '</div>';
                        if ($arti->CodMarca <> 1) {
                            $tabla .= '<div class="descLista"><i class="fas fa-tag fa-lg fa-fw"></i>' . ucwords($arti->Marca) . '</div>';
                        }



                        if ($usaEmbalaje == "Si") {
                            $tabla .= '<div class="descLista"><i class="fa fa-briefcase fa-lg fa-fw"></i> ' . $bulto . '</div>';
                        }

                        $tabla .= '</div>';
                        $tabla .= '</td>';


                        $tabla .= "</tr>";
                    }
                    $tabla .= "</tbody>" . PHP_EOL;
                }
                /**
                 * TIPO GALERIA
                 */
                if ($claseLista == "galeria") {
                    // presentar la tabla con el consumo
                    // presentar la tabla para lista de precios html.
                    $tabla .=  "<thead>" . PHP_EOL;
                    $tabla .=  "<tr><th>Galeria</th></tr>";
                    $tabla .=  "</thead>" . PHP_EOL;
                    $tabla .=  "<tbody>" . PHP_EOL;
                    $promoLista = "no";
                    $cuenta = 0;
                    $caminoDispo = $_SESSION["caminoDisp"];
                    if ($caminoDispo == "") {
                        //                        $limiteFoto =4;
                        $limiteFoto = 6;
                    } else {
                        $limiteFoto = 1;
                    }
                    foreach ($this->colArticulos as $arti) {
                        if ($usaIdManual == "Si") {
                            $idArtT = $arti->id_manual;
                        } else {
                            $idArtT = $arti->IDArt;
                        }
                        $idArt          = $arti->IDArt;
                        $nombreArticulo = $arti->NombreArticulo;

                        /*
                         * LISTA DE PRECIOS
                         */
                        $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);
                        //                        $precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon); 
                        //                        echo "<pre>";
                        //                        print_r($precios);
                        //                        echo "</pre>";
                        /*
                         * EMBALAJE
                         */
                        $bulto = "";
                        if ($usaEmbalaje == "Si") {
                            // tengo que hacer la busqueda de los valores para mostrar
                            if ($arti->nombre_presentacion != "") {
                                $bulto = "" . $arti->nombre_presentacion . " x " . $arti->cantidad_uni;
                            }
                            if ($arti->nombre_unimed != "") {
                                $bulto .= " (" . $arti->nombre_unimed . ")";
                            }
                        }

                        //$hoy3 = $hoy; 
                        $clase = $precios["clase"];
                        $tagPromo = "";
                        if ($precios["promoNombre"] != "") {

                            //                            $nombreArticulo = '<i class="fa fa-certificate fa-lg"></i> '.$nombreArticulo;
                            //                            $nombreArticulo .=$precios["promoNombre"];
                            $tagPromo = '<div class="promocion"><i class="fa fa-gift fa-lg fa-fw"></i> En promoción  </div>';
                        }
                        $clasePrecio = $precios["clasePrecio"];

                        if ($ivaIncluido == 'no') {
                            $precioNeto = $precios["neto"];
                            $precioVenta = $precios["neto"];

                            $precioVentaFinal = $precios["netoCalc"];
                            $precioVentaFinalIva = $precios["precioFinal"];
                        } else {
                            $precioNeto = $precios["neto"];
                            $precioVenta = $precios["neto"];
                            $precioVentaFinal = $precios["netoCalc"];
                            $precioVentaFinalIva = $precios["precioFinal"];
                        }
                        //                        echo "neto:=> <pre>";
                        //                            var_dump($precioNeto);
                        //                        echo "<br>Pventa:=> ";    
                        //                            var_dump($precioVenta);
                        //                            echo "<br>PventaFinal:=> ";    
                        //                            var_dump($precioVentaFinal);
                        //                        echo "</pre>";    
                        $descFinal = number_format($precios["descuento"], 0);
                        //                formateo los precios a cuatro decimales..just in case...
                        $precioVentaF = number_format($precioVenta, 2, ',', '.');
                        $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
                        $precioNetoF = number_format($precioNeto, 2, ',', '.');
                        $precioVentaFinalIva = number_format($precioVentaFinalIva, 2, ',', '.');

                        // si es un consumidor final no dejo que permita comprar
                        //if($precioVentaFinal ==0){
                        //    $srcArticulo="#";}
                        //else{
                        $srcArticulo = 'articulo-info.php?buscaOferta=si&IDArt=' . $idArt . '&cant=' . number_format($arti->promocion_cant);
                        //}

                        if ($descFinal == 0) {
                            //                            $descFinalT = "<div style='width:40px;height:50px;'></div>";
                            $descFinalT = "";
                        } else {
                            //                            $descFinalT = "<div style='width:40px;height:50px;'></div>";
                            $descFinalT = "<div class='oferta'>{$descFinal}%<br><span class='chico'>desc.</span></div>";
                        }


                        if ($cuenta == 0) {
                            $tabla .= '<tr><td class="dt-center"> ';
                        }
                        $tabla .= '<div class="contieneOferta">'
                            . '<a href="' . $srcArticulo . '" title="Ver detalle"> '

                            . '<div class="fotoChicaC">'
                            . '<img src="foto.php?origen=foto1|' . $idArt . '&mini=0">'
                            . '</div>';

                        $tabla .= '<div class="articuloNombre">' . $nombreArticulo . '</div>';
                        $tabla .= '<div class="descLista"><i class="fa fa-hashtag fa-lg"></i> ' . $idArtT . '</div>';
                        if ($descFinal <> 0) {
                            $tabla .= '<div class="precioListaOld">'
                                . '<div id="mi-art-precio' . $arti->IDArt . '">$ ' . $precioVentaF . '</div>'
                                . '</div>';
                        }
                        $tabla .= '<div class="precioLista" > '
                            . '<span id="mi-art-precio' . $arti->IDArt . '">$ ' . $precioVentaFinalF . '</span>';
                        if ($descFinal <> 0) {
                            $tabla .= " <span class='verde'>" . $descFinal . "% OFF</span>" . PHP_EOL;
                        }
                        $tabla .= '</div>';
                        $tabla .= '<div class="precioLista" > '
                            . '<span id="mi-art-precioi' . $arti->IDArt . '">$ ' . $precioVentaFinalIva . '</span>';

                        $tabla .= " <span class='iva'>c/Iva</span>" . PHP_EOL;
                        if ($arti->CodigoMarca <> 1) {
                            $tabla .= '<span class="descLista"><i class="fas fa-tag fa-lg fa-fw"></i>';
                            $tabla .= '' . ucwords($arti->Marca) . '';
                            $tabla .= '</span>';
                        }
                        $tabla .= '</div>';

                        $tabla .= '</a></div>';


                        $cuenta++;
                        if ($cuenta == $limiteFoto) {
                            $tabla .= "</td></tr>";
                            $cuenta = 0;
                        }
                    }
                    //                    for($i=$cuenta; $i<$limiteFoto; $i++ ){
                    ////                        $tabla .="<td></td>";
                    //                    }
                    $tabla .= "</td></tr>";
                    $tabla .= "</tbody>" . PHP_EOL;
                }
            }
            /*
             *CONSUMOS DEL CLIENTE
             * =================================================================
             */
            if ($quien == "consumo") {
                // presentar la tabla con el consumo
                // presentar la tabla para lista de precios html.

                $tabla .=  "<thead>" . PHP_EOL;
                $tabla .=  "<tr>" . PHP_EOL;
                $tabla .=  "<th>Categoria</th>" . PHP_EOL;
                $tabla .=  "<th>Rubro</th>" . PHP_EOL;
                $tabla .=  "<th>Sub Rubro</th>" . PHP_EOL;
                $tabla .=  "<th>Codigo</th>" . PHP_EOL;
                $tabla .=  "<th class='center'>Artículo</th>" . PHP_EOL;

                $tabla .=  "<th>Cant</th>" . PHP_EOL;
                $tabla .=  "<th class='right'>Lista Act</th>" . PHP_EOL;

                $tabla .=  "<th class='right'>Desc</th>" . PHP_EOL;
                $tabla .=  "<th class='right'>Lista c/dcto Act</th>" . PHP_EOL;
                if ($usaEmbalaje == "Si") {
                    $tabla .=  "<th>Bulto cerrado</th>" . PHP_EOL;
                }
                $tabla .=  "</tr>" . PHP_EOL;
                $tabla .=  "</thead>" . PHP_EOL;
                $tabla .=  "<tbody>" . PHP_EOL;
                $promoLista = "no";

                foreach ($this->colArticulos as $arti) {
                    //                    echo "<pre>";
                    //                    print_r($arti);
                    //                    echo "</pre>";
                    // echo gettype($arti);
                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = $arti->IDArt;
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;

                    /*
                     * LISTA DE PRECIOS
                     */

                    //                    $precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon);  
                    $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);

                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0);
                        }
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                    }

                    //$hoy3 = $hoy; 
                    $clase = $precios["clase"];
                    $nombreArticulo .= $precios["promoNombre"];
                    if ($arti->CodigoMarca != 1) {
                        $nombreArticulo .= "- " . $arti->NombreMarca;
                    }
                    $clasePrecio = $precios["clasePrecio"];
                    $promo = $precios["promo"];
                    if ($ivaIncluido == 'no') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["netoCalc"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioFinal"];
                    }

                    $descFinal = number_format($precios["descuento"], 0, '', '');
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, ',', '.');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
                    $precioNetoF = number_format($precioNeto, 2, ',', '.');

                    $tabla .= "<tr>";
                    $tabla .= "<td {$clase}>{$arti->NombCategoria}</td>";
                    $tabla .= "<td {$clase}>{$arti->NombRub}</td>";
                    $tabla .= "<td {$clase}>{$arti->NombSubRub}</td>";
                    $tabla .= "<td {$clase}>{$idArtT}</td>";
                    $tabla .= "<td {$clase}>{$nombreArticulo}</td>";

                    $tabla .= "<td data-order='{$arti->Cuantos}' class='importe {$clasePrecio}'>{$arti->Cuantos}</td>";
                    $tabla .= "<td data-order='{$precioVenta}' class='dt-nowarp importe {$clasePrecio}' >$<span id='mi-art-precio{$arti->IDArt}'>{$precioVentaF}</span></td>" . PHP_EOL;
                    $tabla .= "<td data-order='{$descFinal}' {$clase}>" . PHP_EOL;

                    if ($promo == 'no') {
                        /*
                         * No tengo promocion
                         */
                        if ($_SESSION['tipousuario'] == 'cliente') {
                            /*
                             * Soy un cliente y tengo descuento pero no puedo tocarlo
                             * debo calcular el precio con el descuento
                             */



                            $tabla .= "{$descFinal}%" . PHP_EOL;
                            $tabla .= "</td>" . PHP_EOL;
                        } else {
                            /*
                             * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                             * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                             */
                            if ($descFinal == 0) {

                                $tabla .= "0%" . PHP_EOL;
                            } else {
                                $tabla .= "{$descFinal}%" . PHP_EOL;
                            }
                            $tabla .= "</td>" . PHP_EOL;
                        }
                    } else {
                        /*
                         * Hay promocion
                         */
                        $tabla .= "{$descFinal}%" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    }
                    $tabla .= "<td data-order='{$precioVentaFinal}' class='dt-nowarp  importe {$clasePrecio}' >$<span id='mi-art-precio{$arti->IDArt}'>{$precioVentaFinalF}</span></td>" . PHP_EOL;
                    if ($usaEmbalaje == "Si") {
                        $tabla .= "<td {$clase}>{$bulto}</td>";
                    }
                    $tabla .= "</tr>" . PHP_EOL;
                }
                $tabla .= "</tbody>" . PHP_EOL;
            }
            //* * LISTA DE PRECIOS           
            if ($quien == "listap") {
                //* recupero el tipo de iva que yo mando desde lista de precios.
                // echo 'Iva incluido por sesion.<pre>',var_dump($_SESSION['ivaIncluidoLista']),PHP_EOL,'</pre>';
                if (isset($_SESSION['ivaIncluidoLista']) && $_SESSION['ivaIncluidoLista'] != '') {
                    //  piso la seleccion para todo el sistema.
                    $ivaIncluido = ucfirst($_SESSION['ivaIncluidoLista']);
                }

                // presentar la tabla para lista de precios html.
                if ($ivaIncluido == 'No') {
                    $ivaInc = "<tr><th colspan='12'>Los precios publicados NO incluyen IVA </th></tr>";
                } else {
                    $ivaInc = "<tr><th colspan='11'>Los precios publicados incluyen IVA </th></tr>";
                }
                
                $tabla .=  "<thead>" . PHP_EOL;
                $tabla .= $ivaInc;
                $tabla .=  "<tr>" . PHP_EOL;

                $tabla .=  "<th class='left'>Categoría</th>" . PHP_EOL;
                $tabla .=  "<th class='left'>Rubro</th>" . PHP_EOL;
                $tabla .=  "<th class='left'>Sub Rubro</th>" . PHP_EOL;
                if ($imagenProducto == 'Si') {
                    $tabla .=  "<th>Imagen</th>" . PHP_EOL;
                }
                $tabla .=  "<th>Cod</th>" . PHP_EOL;
                $tabla .=  "<th class='left'>Artículo</th>" . PHP_EOL;
                if ($ivaIncluido == "Si") {
                    $tabla .=  "<th class='right'>Final</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Desc</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Final c/dcto</th>" . PHP_EOL;
                }
                if ($ivaIncluido == "No") {
                    $tabla .=  "<th class='right'>Neto</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Desc</th>" . PHP_EOL;
                    // $tabla .=  "<th class='right'>Iva(%)</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Iva</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Final c/dcto</th>" . PHP_EOL;
                }

                // * cambia bulto cerrado si es display o bulto cerrado
                if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
                    $tabla .=  "<th>Presentación</th>" . PHP_EOL;
                }

                // if ($usaEmbalaje == "Si") {
                //     $tabla .=  "<th>Bulto cerrado</th>" . PHP_EOL;
                // }
                $tabla .=  "</tr>" . PHP_EOL;
                $tabla .=  "</thead>" . PHP_EOL;
                $tabla .=  "<tbody>" . PHP_EOL;
                $promoLista = "no";
                $arrFotos = array();
                $arrClaves = array_keys($this->colArticulos);
                $listadoClavesProductos = "";
                //echo '<pre>',print_r($this->colArticulos),'<pre>';
                // echo 'array de claves=><pre>',print_r(array_values(array_keys($this->colArticulos))),'</pre>';
                $listadoClavesProductos = implode(",", array_keys($this->colArticulos));

                if ($imagenProducto == 'Si') {
                    foreach ($arrClaves as $id => $clave) {
                        $arrFotos[$clave] = '_img/sinfoto.jpg';
                    }
                    // echo 'array previo a las fotos::<pre>',print_r($arrFotos),'</pre>',PHP_EOL;
                    // buscar las fotos que esten

                    $armarFotos = $this->listaFotosProducto($arrFotos, $listadoClavesProductos);
                    // echo 'array posterior a las fotos::<pre>',var_dump($armarFotos),PHP_EOL,print_r($arrFotos),'</pre>',PHP_EOL;
                }

                foreach ($this->colArticulos as $arti) {
                    // echo '<pre>',print_r($arti),'<pre>';
                    // echo "idManual:". var_dump($usaIdManual);
                    // convertir la imagen a base 64


                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = str_pad($arti->IDArt, 8, "0", STR_PAD_LEFT);
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;
                    // convertir la imagen a base 64
                    if ($imagenProducto == 'Si') {
                        $imagenBase64 = $this->convertImageToBase64($arrFotos[$idArt]);
                    }

                    /*
                     * LISTA DE PRECIOS
                     */
                    //                     $precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon,$usaReglaPrecio,$codCliente);
                    $arrPrecios = array();
                    $arrPrecios['arti'] = $arti;
                    $arrPrecios['listaPrecioCliente'] = "Lista ".$listaPrecioCliente;
                    $arrPrecios['descRenglon'] = $descRenglon;
                    $arrPrecios['usaReglaPrecio'] = $usaReglaPrecio;
                    $arrPrecios['codCliente'] = $codCliente;

                    $precios = $this->calculaPrecios($arrPrecios);
                    //    echo "iva:". var_dump($ivaIncluido);
                    //    echo "<br><pre>";
                    //    print_r($precios);
                    //    echo "</pre>";
                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        }
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                    }

                    //$hoy3 = $hoy; 
                    $clase = '';
                    $clase = $precios["clasePrecio"];
                    $nombreArticulo .= ucwords(strtolower($precios["promoNombre"]));
                    $nombreCategoria =ucwords(strtolower($arti->NombCategoria)); 
                    $nombreRubro = ucwords(strtolower($arti->NombRub));
                    $nombreSubRubro = ucwords(strtolower($arti->NombSubRub));
                    
                    $clasePrecio = $precios["clasePrecio"];
                    $promo = $precios["promo"];
                    if ($ivaIncluido == 'No') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["precioFinal"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    $ivaFinal = $precios["impIvaFinal"];
                    $aliIva = $precios["ivaAlic"];
                    $ivaFinalF = number_format($ivaFinal, 2, '.', '');
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, '.', '');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, '.', '');
                    $precioNetoF = number_format($precioNeto, 2, '.', '');

                    $tabla .= "<tr>";

                    $tabla .= "<td class='{$clase}'>{$nombreCategoria}</td>";
                    $tabla .= "<td class='dt-nowrap {$clase}'>{$nombreRubro}</td>";
                    $tabla .= "<td class='dt-nowarp {$clase}'>{$nombreSubRubro}</td>";
                    if ($imagenProducto == 'Si') {
                        // convertir las imagenes a base 64 para poder exportarlas, creando la imagen sino existe y devolviendo el path url
                        // $tabla .= "<td class='dt-nowarp {$clase}'><img class='fotoProducto' src='foto.php?origen=foto1|" . $idArt . "&mini=2'></td>"; // foto armarSqlCatalogoMisConsumos
                        $tabla .= "<td class='dt-nowarp {$clase}'><img class='fotoProducto' src='data:image/jpeg;base64," . $imagenBase64 . "' alt='Imagen del Producto 1' width='100'></td>";
                    }
                    $tabla .= "<td class='{$clase}'>{$idArtT}</td>";
                    $tabla .= "<td class='{$clase}'>{$nombreArticulo}</td>";

                    $tabla .= "<td class='importe {$clasePrecio}' >$" . $precioVentaF . "</td>" . PHP_EOL;

                    // $tabla .= "<td class='dt-body-right {$clase}'>" . PHP_EOL;
                   
                    // hay promocion
                    // if (isset($promo) && $promo == 'si')  {
                    //     /*
                    //      * Hay promocion
                    //      */
                    //     $tabla .= "{$descFinal}%" . PHP_EOL;
                    //     $tabla .= "</td>" . PHP_EOL;
                    // }

                    // sin descuento o promocion
                    if ($descFinal == 0) {
                        $tabla .= "<td class='dt-body-right {$clase}'>" . PHP_EOL;
                        $tabla .= "-" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    } 
                    // descuento o prmocion
                    if ($descFinal !== 0) {
                        $tabla .= "<td class='dt-body-right ".$clase." importe-descuento'>" . PHP_EOL;
                        $tabla .= "{$descFinal}%" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    }
                    
                    if ($ivaIncluido == 'No') {
                        $tabla .= "<td class='importe {$clasePrecio}' >${$ivaFinalF}({$aliIva}%)</td>" . PHP_EOL;
                    }
                    $tabla .= "<td class='importe {$clasePrecio}' >$" . $precioVentaFinalF . "</td>" . PHP_EOL;
                    if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
                        $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
                        $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
                        $cantidadUnidadMinimaCaja = 1;
                        $cantidadMinimaFinal = 1;
                        $tipoUnidad = 'Unidad'; // valor por defecto



                        if ($arti->tipoPrecioUnidad != '') {
                            $tipoUnidad = $arti->tipoPrecioUnidad; // como viene el precio descuento
                        }

                        // display
                        if ($arti->cantidad_unidad_display != 0 && $arti->cantidad_unidad_display != null) {
                            $cantidadUnidadDisplay = (int)$arti->cantidad_unidad_display;
                        }

                        // bulto
                        if ($arti->cantidad_display_bulto != 0 && $arti->cantidad_display_bulto != null) {
                            $cantidadDisplayBulto = $arti->cantidad_display_bulto;
                        }

                        $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.

                        //*  unidad :: sacamos la unidad porque no se usa
                        // precios en unidad
                        if ($tipoUnidad == 'Unidad') {
                            $tabla .= "<td class='dt-nowrap {$clase}'>Unidad x 1 </td>";
                        }
                        //* display

                        if ($tipoUnidad == 'Display') {
                            $tabla .= "<td class='dt-nowrap {$clase}'>Display x " . round($cantidadUnidadDisplay, 0) . " </td>";
                        }
                        //* bulto
                        if ($tipoUnidad == 'Bulto') {

                            $tabla .= "<td class='dt-nowrap {$clase}'>Bulto x " . round($cantidadUnidadMinimaCaja, 0) . " </td>";
                        }
                    }
                    // if ($usaEmbalaje == "Si") {

                    //     $tabla .= "<td class='dt-nowrap {$clase}'>{$bulto}</td>";
                    // }
                    $tabla .= "</tr>" . PHP_EOL;
                }
                $tabla .= "</tbody>" . PHP_EOL;
            }
            if($quien== "listaprecioPDF"){
                //* recupero el tipo de iva que yo mando desde lista de precios.
                // echo 'Iva incluido por sesion.<pre>',var_dump($_SESSION['ivaIncluidoLista']),PHP_EOL,'</pre>';
                if (isset($_SESSION['ivaIncluidoLista']) && $_SESSION['ivaIncluidoLista'] != '') {
                    //  piso la seleccion para todo el sistema.
                    $ivaIncluido = ucfirst($_SESSION['ivaIncluidoLista']);
                }

                // presentar la tabla para lista de precios html.
                if ($ivaIncluido == 'No') {
                    $ivaInc = "<tr class='aviso-iva'>
                    <th colspan='10'>Los precios publicados NO incluyen IVA</th>
                </tr>";
                } else {
                    $ivaInc = "<tr class='aviso-iva'>
                    <th colspan='10'>Los precios publicados incluyen IVA</th>
                        </tr>";
                }

                // como es pdf tengo que armar la tabla completa segun el ejemplo 
                $tabla .= "<table class='tabla-productos zebra-striping'>" . PHP_EOL;

                $tabla .=  "<thead>" . PHP_EOL;
                $tabla .= $ivaInc;
                $tabla .=  "<tr>" . PHP_EOL;

                $tabla .=  "<th class='col-cat'>Categoría</th>" . PHP_EOL;
                $tabla .=  "<th class='col-rub'>Rubro</th>" . PHP_EOL;
                $tabla .=  "<th class='col-sub'>Sub Rubro</th>" . PHP_EOL;
                if ($imagenProducto == 'Si') {
                    $tabla .=  "<th class='col-foto'>Imagen</th>" . PHP_EOL;
                }
                $tabla .=  "<th class='col-cod'>Cod</th>" . PHP_EOL;
                $tabla .=  "<th class='col-art'>Artículo</th>" . PHP_EOL;
                if ($ivaIncluido == "Si") {
                    $tabla .=  "<th class='col-final right'>Final</th>" . PHP_EOL;
                    $tabla .=  "<th class='col-desc right'>Desc</th>" . PHP_EOL;
                    $tabla .=  "<th class='col-final-cdcto right'>Final c/dcto</th>" . PHP_EOL;
                }
                if ($ivaIncluido == "No") {
                    $tabla .=  "<th class='col-neto right'>Neto</th>" . PHP_EOL;
                    $tabla .=  "<th class='col-desc right'>Desc</th>" . PHP_EOL;
                    // $tabla .=  "<th class='col-iva right'>Iva(%)</th>" . PHP_EOL;
                    $tabla .=  "<th class='col-iva right'>Iva</th>" . PHP_EOL;
                    $tabla .=  "<th class='col-final-cdcto right'>Final c/dcto</th>" . PHP_EOL;
                }

                // * cambia bulto cerrado si es display o bulto cerrado
                if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
                    $tabla .=  "<th class='col-presentacion'>Presentación</th>" . PHP_EOL;
                }

                // if ($usaEmbalaje == "Si") {
                //     $tabla .=  "<th>Bulto cerrado</th>" . PHP_EOL;
                // }
                $tabla .=  "</tr>" . PHP_EOL;
                $tabla .=  "</thead>" . PHP_EOL;
                $tabla .=  "<tbody>" . PHP_EOL;
                $promoLista = "no";
                $arrFotos = array();
                $arrClaves = array_keys($this->colArticulos);
                $listadoClavesProductos = "";
                //echo '<pre>',print_r($this->colArticulos),'<pre>';
                // echo 'array de claves=><pre>',print_r(array_values(array_keys($this->colArticulos))),'</pre>';
                $listadoClavesProductos = implode(",", array_keys($this->colArticulos));

                if ($imagenProducto == 'Si') {
                    foreach ($arrClaves as $id => $clave) {
                        $arrFotos[$clave] = '_img/sinfoto.jpg';
                    }
                    // echo 'array previo a las fotos::<pre>',print_r($arrFotos),'</pre>',PHP_EOL;
                    // buscar las fotos que esten

                    $armarFotos = $this->listaFotosProducto($arrFotos, $listadoClavesProductos);
                    // echo 'array posterior a las fotos::<pre>',var_dump($armarFotos),PHP_EOL,print_r($arrFotos),'</pre>',PHP_EOL;
                }

                foreach ($this->colArticulos as $arti) {
                    // echo '<pre>',print_r($arti),'<pre>';
                    // echo "idManual:". var_dump($usaIdManual);
                    // convertir la imagen a base 64


                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = str_pad($arti->IDArt, 8, "0", STR_PAD_LEFT);
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;
                    // convertir la imagen a base 64
                    // if ($imagenProducto == 'Si') {
                    //     $imagenBase64 = $this->convertImageToBase64($arrFotos[$idArt]);
                    // }

                    /*
                     * LISTA DE PRECIOS
                     */
                    //                     $precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon,$usaReglaPrecio,$codCliente);
                    $arrPrecios = array();
                    $arrPrecios['arti'] = $arti;
                    $arrPrecios['listaPrecioCliente'] = "Lista ".$listaPrecioCliente;
                    $arrPrecios['descRenglon'] = $descRenglon;
                    $arrPrecios['usaReglaPrecio'] = $usaReglaPrecio;
                    $arrPrecios['codCliente'] = $codCliente;

                    $precios = $this->calculaPrecios($arrPrecios);
                    //    echo "iva:". var_dump($ivaIncluido);
                    //    echo "<br><pre>";
                    //    print_r($precios);
                    //    echo "</pre>";
                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        }
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                    }

                    //$hoy3 = $hoy; 
                    // $clase = '';
                    // $clase = $precios["clasePrecio"];
                    $nombreArticulo .= ucwords(strtolower($precios["promoNombre"]));
                    $nombreCategoria =ucwords(strtolower($arti->NombCategoria)); 
                    $nombreRubro = ucwords(strtolower($arti->NombRub));
                    $nombreSubRubro = ucwords(strtolower($arti->NombSubRub));
                    
                    $clasePrecio = $precios["clasePrecio"];
                    $promo = $precios["promo"];
                    if ($ivaIncluido == 'No') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["precioFinal"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    $ivaFinal = $precios["impIvaFinal"];
                    $aliIva = $precios["ivaAlic"];
                    $ivaFinalF = number_format($ivaFinal, 2, ',', '.');
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, ',', '.');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
                    $precioNetoF = number_format($precioNeto, 2, ',', '.');

                    $tabla .= "<tr>";

                    $tabla .= "<td class='col-cat'>{$nombreCategoria}</td>";
                    $tabla .= "<td class='col-rub'>{$nombreRubro}</td>";
                    $tabla .= "<td class='col-sub'>{$nombreSubRubro}</td>";
                    if ($imagenProducto == 'Si') {
                        
                        if($arrFotos[$idArt] == '_img/sinfoto.jpg'){
                            $htmlFoto ='<td class="col-foto"><div class="img-placeholder">Sin foto</div></td>';
                        } else {
                            $htmlFoto = "<td class='col-foto'><img class='foto-producto' src='".$arrFotos[$idArt]."'></td>"; // foto armarSqlCatalogoMisConsumos

                        }
                        $tabla .= $htmlFoto;

                        // convertir las imagenes a base 64 para poder exportarlas, creando la imagen sino existe y devolviendo el path url
                        // $tabla .= "<td class='col-foto {$clase}'><img class='fotoProducto' src='data:image/jpeg;base64," . $imagenBase64 . "' alt='Imagen del Producto 1' width='100'></td>";
                    }
                    $tabla .= "<td class='col-cod'>{$idArtT}</td>";
                    // analizamos si es con o sin tacc 
                    // Supongamos que $fila['tacc'] viene de tu base de datos ('S' o 'N')
                    $badgeTacc = "";
                    if ($arti->sin_tacc == 'Si') {
                        $badgeTacc = ' <span class="badge-tacc">Sin Tacc</span>';
                    }

                    // Luego lo concatenas al nombre del artículo
                    $nombreArticulo .= $badgeTacc;

                    $tabla .= "<td class='col-art'>{$nombreArticulo}</td>";

                    $tabla .= "<td class='col-neto'>$" . $precioVentaF . "</td>" . PHP_EOL;

                    // $tabla .= "<td class='dt-body-right {$clase}'>" . PHP_EOL;
                   
                    // hay promocion
                    // if (isset($promo) && $promo == 'si')  {
                    //     /*
                    //      * Hay promocion
                    //      */
                    //     $tabla .= "{$descFinal}%" . PHP_EOL;
                    //     $tabla .= "</td>" . PHP_EOL;
                    // }

                    // sin descuento o promocion
                    if ($descFinal == 0) {
                        $tabla .= "<td class='col-desc right'>" . PHP_EOL;
                        $tabla .= "-" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    } 
                    // descuento o prmocion
                    if ($descFinal !== 0) {
                        $tabla .= "<td class='col-desc right'>" . PHP_EOL;
                        $tabla .= "{$descFinal}%" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    }
                    
                    if ($ivaIncluido == 'No') {
                        $tabla .= "<td class='col-iva' >${$ivaFinalF}({$aliIva}%)</td>" . PHP_EOL;
                    }
                    $tabla .= "<td class='col-final' >$" . $precioVentaFinalF . "</td>" . PHP_EOL;
                    if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
                        $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
                        $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
                        $cantidadUnidadMinimaCaja = 1;
                        $cantidadMinimaFinal = 1;
                        $tipoUnidad = 'Unidad'; // valor por defecto



                        if ($arti->tipoPrecioUnidad != '') {
                            $tipoUnidad = $arti->tipoPrecioUnidad; // como viene el precio descuento
                        }

                        // display
                        if ($arti->cantidad_unidad_display != 0 && $arti->cantidad_unidad_display != null) {
                            $cantidadUnidadDisplay = (int)$arti->cantidad_unidad_display;
                        }

                        // bulto
                        if ($arti->cantidad_display_bulto != 0 && $arti->cantidad_display_bulto != null) {
                            $cantidadDisplayBulto = $arti->cantidad_display_bulto;
                        }

                        $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.

                        //*  unidad :: sacamos la unidad porque no se usa
                        // precios en unidad
                        if ($tipoUnidad == 'Unidad') {
                            $tabla .= "<td class='col-pres {$clase}'>Unidad x 1 </td>";
                        }
                        //* display

                        if ($tipoUnidad == 'Display') {
                            $tabla .= "<td class='col-pres {$clase}'>Display x " . round($cantidadUnidadDisplay, 0) . " </td>";
                        }
                        //* bulto
                        if ($tipoUnidad == 'Bulto') {

                            $tabla .= "<td class='col-pres {$clase}'>Bulto x " . round($cantidadUnidadMinimaCaja, 0) . " </td>";
                        }
                    }
                    // if ($usaEmbalaje == "Si") {

                    //     $tabla .= "<td class='dt-nowrap {$clase}'>{$bulto}</td>";
                    // }
                    $tabla .= "</tr>" . PHP_EOL;
                }
                $tabla .= "</tbody>" . PHP_EOL;
                $tabla .= "</table>" . PHP_EOL;
            }
            return $tabla;
        } else {
            //            $tabla = "<tr><td class='vacio'>No se encontaron resultados </td></tr>";
            //            echo $tabla;
            //            echo "<pre>";
            //                print_r($pp);
            //                echo "</pre>";
            return '0';
        }
    }

    // * la vista html pero de las promociones
    function vista_lista_promociones($listaPrecioCliente = null, $descRenglon = null, $objCliente = null)    {
        $limDescReng    = 0;
        $codCliente     = null;
        $usoBultoCerrado = 'No';
        $usaDisplay = 'No';
        $imagenProducto='Si';

        if (isset($_SESSION['vendedor'])) {

            $objVendedor = $_SESSION['vendedor'];
            $limDescReng    = $objVendedor->lim_desc_renglon;
        }
        if ($objCliente != null) {
            //$listaPrecioCliente = $objCliente->listaPrecio;
            //$descRenglon        = $objCliente->descRenglon;
            $codCliente = $objCliente->Codigo;
        }
        $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
        $tabla = "";

        // bulto cerrado 
        if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
            $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
        }

        // // display 
        if ($_SESSION['utiliza_display'] == "Si") {
            $usaDisplay = $_SESSION['utiliza_display'];
        }

        if (!empty($this->colArticulos)) {


            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            $precioVentaF = 0;
            if ($_SESSION["utilizaEmbalaje"] == "Si") {
                $usaEmbalaje = "Si";
            } else {
                $usaEmbalaje = "No";
            }

            $usaIdManual = $_SESSION["usa_id_manual"];
            // * Iva incluido desde login.
            if (isset($_SESSION['ivaIncluido']) && $_SESSION['ivaIncluido'] != "") {
                $ivaIncluido = $_SESSION["ivaIncluido"];
            }


            $caminoDispo = "";
            if (isset($_SESSION["caminoDispo"])) {
                $caminoDispo = $_SESSION["caminoDispo"];
            }
            $soyMovil = 0;
            if ($caminoDispo != "") {
                $soyMovil = 1;
            }

            //            $cantidad = $_REQUEST['cantidadOferta'];
            //con el tema de la oferta dejo una idea a desarrollar, pasar el id
            //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
            //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
            //            borro todo y coloco un uno
            if (isset($_REQUEST['cantidadOferta']) && $_REQUEST["cantidadOferta"] != "") {
                $cantidad = $_REQUEST['cantidadOferta'];
            } else {
                $cantidad = 1;
            }
            
                       
            
           
                //* recupero el tipo de iva que yo mando desde lista de precios.
                // echo 'Iva incluido por sesion.<pre>',var_dump($_SESSION['ivaIncluidoLista']),PHP_EOL,'</pre>';
                if (isset($_SESSION['ivaIncluidoLista']) && $_SESSION['ivaIncluidoLista'] != '') {
                    //  piso la seleccion para todo el sistema.
                    $ivaIncluido = ucfirst($_SESSION['ivaIncluidoLista']);
                }

                // presentar la tabla para lista de precios html.
                if ($ivaIncluido == 'No') {
                    $ivaInc = "<tr><th colspan='11'>Los precios publicados NO incluyen IVA </th></tr>";
                } else {
                    $ivaInc = "<tr><th colspan='11'>Los precios publicados incluyen IVA </th></tr>";
                }


                $tabla .=  "<thead>" . PHP_EOL;
                $tabla .= $ivaInc;
                $tabla .=  "<tr>" . PHP_EOL;

                $tabla .=  "<th>Categoria</th>" . PHP_EOL;
                $tabla .=  "<th>Rubro</th>" . PHP_EOL;
                $tabla .=  "<th>Sub Rubro</th>" . PHP_EOL;
                
                // $tabla .=  "<th>Imagen</th>" . PHP_EOL;
                
                $tabla .=  "<th>Cod</th>" . PHP_EOL;
                $tabla .=  "<th class='center'>Artículo</th>" . PHP_EOL;
                if ($ivaIncluido == "Si") {
                    $tabla .=  "<th class='right'>Final</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Desc</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Final c/dcto</th>" . PHP_EOL;
                }
                if ($ivaIncluido == "No") {
                    $tabla .=  "<th class='right'>Neto</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Desc</th>" . PHP_EOL;
                    // $tabla .=  "<th class='right'>Iva(%)</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Iva</th>" . PHP_EOL;
                    $tabla .=  "<th class='right'>Final c/dcto</th>" . PHP_EOL;
                }

                
                $tabla .=  "<th>Promoción</th>" . PHP_EOL;
                $tabla .=  "<th>Vigencia</th>" . PHP_EOL;

                // if ($usaEmbalaje == "Si") {
                //     $tabla .=  "<th>Bulto cerrado</th>" . PHP_EOL;
                // }
                $tabla .=  "</tr>" . PHP_EOL;
                $tabla .=  "</thead>" . PHP_EOL;
                $tabla .=  "<tbody>" . PHP_EOL;
                $promoLista = "no";
                $arrFotos = array();
                $arrClaves = array_keys($this->colArticulos);
                $listadoClavesProductos = "";
                //echo '<pre>',print_r($this->colArticulos),'<pre>';
                // echo 'array de claves=><pre>',print_r(array_values(array_keys($this->colArticulos))),'</pre>';
                $listadoClavesProductos = implode(",", array_keys($this->colArticulos));

                // if ($imagenProducto == 'Si') {
                    foreach ($arrClaves as $id => $clave) {
                        $arrFotos[$clave] = '_img/sinfoto.jpg';
                    }
                    // echo 'array previo a las fotos::<pre>',print_r($arrFotos),'</pre>',PHP_EOL;
                    // buscar las fotos que esten

                    $armarFotos = $this->listaFotosProducto($arrFotos, $listadoClavesProductos);
                    // echo 'array posterior a las fotos::<pre>',var_dump($armarFotos),PHP_EOL,print_r($arrFotos),'</pre>',PHP_EOL;
                // }

                foreach ($this->colArticulos as $arti) {
                    // echo '<pre>',print_r($arti),'<pre>';
                    // echo "idManual:". var_dump($usaIdManual);
                    // convertir la imagen a base 64
                    $textoDisplay = "";

                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = str_pad($arti->IDArt, 8, "0", STR_PAD_LEFT);
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;
                    // convertir la imagen a base 64
                    // if ($imagenProducto == 'Si') {
                        $imagenBase64 = $this->convertImageToBase64($arrFotos[$idArt]);
                    // }

                    if ($usaDisplay == 'Si' || $usoBultoCerrado == 'Si') {
                        $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
                        $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
                        $cantidadUnidadMinimaCaja = 1;
                        $cantidadMinimaFinal = 1;
                        $tipoUnidad = 'Unidad'; // valor por defecto



                        if ($arti->tipoPrecioUnidad != '') {
                            $tipoUnidad = $arti->tipoPrecioUnidad; // como viene el precio descuento
                        }

                        // display
                        if ($arti->cantidad_unidad_display != 0 && $arti->cantidad_unidad_display != null) {
                            $cantidadUnidadDisplay = (int)$arti->cantidad_unidad_display;
                        }

                        // bulto
                        if ($arti->cantidad_display_bulto != 0 && $arti->cantidad_display_bulto != null) {
                            $cantidadDisplayBulto = $arti->cantidad_display_bulto;
                        }

                        $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.

                        //*  unidad :: sacamos la unidad porque no se usa
                        // precios en unidad
                        if ($tipoUnidad == 'Unidad') {
                            $textoDisplay .= "Unidad x 1";
                        }
                        //* display

                        if ($tipoUnidad == 'Display') {
                            $textoDisplay .= "Display x " . round($cantidadUnidadDisplay, 0) . "";
                        }
                        //* bulto
                        if ($tipoUnidad == 'Bulto') {

                            $textoDisplay .= "Bulto x " . round($cantidadUnidadMinimaCaja, 0) . "";
                        }
                    }
                    /*
                     * LISTA DE PRECIOS
                     */
                    //                     $precios = $this->calculaPrecios($arti,$listaPrecioCliente,$descRenglon,$usaReglaPrecio,$codCliente);
                    $arrPrecios = array();
                    $arrPrecios['arti'] = $arti;
                    $arrPrecios['listaPrecioCliente'] = $listaPrecioCliente;
                    $arrPrecios['descRenglon'] = $descRenglon;
                    $arrPrecios['usaReglaPrecio'] = $usaReglaPrecio;
                    $arrPrecios['codCliente'] = $codCliente;

                    $precios = $this->calculaPrecios($arrPrecios);
                    //    echo "iva:". var_dump($ivaIncluido);
                    //    echo "<br><pre>";
                    //    print_r($precios);
                    //    echo "</pre>";
                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        }
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                    }

                    //$hoy3 = $hoy; 
                    $clase = '';
                    $clase = $precios["clasePrecio"];
                    $nombreArticulo .= $precios["promoNombre"];
                    $clasePrecio = $precios["clasePrecio"];
                    $promo = $precios["promo"];
                    if ($ivaIncluido == 'No') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["precioFinal"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    $ivaFinal = $precios["impIvaFinal"];
                    $aliIva = $precios["ivaAlic"];
                    $ivaFinalF = number_format($ivaFinal, 2, '.', '');
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, '.', '');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, '.', '');
                    $precioNetoF = number_format($precioNeto, 2, '.', '');

                    $tabla .= "<tr>";

                    $tabla .= "<td class='{$clase}'>{$arti->NombCategoria}</td>";
                    $tabla .= "<td class='dt-nowrap {$clase}'>{$arti->NombRub}</td>";
                    $tabla .= "<td class='dt-nowarp {$clase}'>{$arti->NombSubRub}</td>";
                    // if ($imagenProducto == 'Si') {
                        // convertir las imagenes a base 64 para poder exportarlas, creando la imagen sino existe y devolviendo el path url
                        // $tabla .= "<td class='dt-nowarp {$clase}'><img class='fotoProducto' src='foto.php?origen=foto1|" . $idArt . "&mini=2'></td>"; // foto armarSqlCatalogoMisConsumos
                        // $tabla .= "<td class='dt-nowarp {$clase}'><img class='fotoProducto' src='data:image/jpeg;base64," . $imagenBase64 . "' alt='Imagen del Producto 1' width='100'></td>";
                    // }
                    $tabla .= "<td class='{$clase}'>{$idArtT}</td>";
                    $tabla .= "<td class='{$clase}'><img class='fotoProducto' src='data:image/jpeg;base64," . $imagenBase64 . "' alt='Imagen del Producto 1' width='100'>{$nombreArticulo}<br>{$textoDisplay}</td>";

                    $tabla .= "<td class='importe {$clasePrecio}' >$" . $precioVentaF . "</td>" . PHP_EOL;

                    $tabla .= "<td class='dt-body-right {$clase}'>" . PHP_EOL;

                    if (isset($promo) && $promo == 'no') {
                        /*
                         * No tengo promocion
                         */

                        /*
                             * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                             * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                             */
                        if ($descFinal == 0) {

                            $tabla .= "" . PHP_EOL;
                        } else {
                            $tabla .= "<span class='producto-promocion'>{$descFinal}%</span>" . PHP_EOL;
                        }
                        $tabla .= "</td>" . PHP_EOL;
                    } else {
                        /*
                         * Hay promocion
                         */
                        $tabla .= "<span class='producto-promocion'>{$descFinal}%</span>" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    }
                    if ($ivaIncluido == 'No') {
                        $tabla .= "<td class='importe {$clasePrecio}' >$<span id='mi-art-precio{$arti->IDArt}'>{$ivaFinalF}</span>({$aliIva}%)</td>" . PHP_EOL;
                    }
                    $tabla .= "<td class='importe {$clasePrecio}' >$" . $precioVentaFinalF . "</td>" . PHP_EOL;
                    $tabla .="<td>Promocion</td>";
                    $tabla .="<td>{$arti->promocion_vigencia_hasta}</td>";
                    // if ($usaEmbalaje == "Si") {

                    //     $tabla .= "<td class='dt-nowrap {$clase}'>{$bulto}</td>";
                    // }
                    $tabla .= "</tr>" . PHP_EOL;
                }
                $tabla .= "</tbody>" . PHP_EOL;
            
            return $tabla;
        } else {
            //            $tabla = "<tr><td class='vacio'>No se encontaron resultados </td></tr>";
            //            echo $tabla;
            //            echo "<pre>";
            //                print_r($pp);
            //                echo "</pre>";
            return '0';
        }
    }


    /*
     * Funcion Vista Pdf
     */
    function vista_lista_pdf($listaPrecioCliente = null, $descRenglon = null, $objCliente = null)
    {
        $tabla = '<table class="tablesorter" cellspacing="1" id="myTable" >';
        if (!empty($this->colArticulos)) {

            $limDescReng    = 0;
            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            $precioVentaF = 0;
            $usaIdManual = "No";
            //            $cantidad = $_REQUEST['cantidadOferta'];
            //con el tema de la oferta dejo una idea a desarrollar, pasar el id
            //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
            //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
            //            borro todo y coloco un uno
            if ($_REQUEST['cantidadOferta']) {
                $cantidad = $_REQUEST['cantidadOferta'];
            } else {
                $cantidad = 1;
            }

            $tabla .=  "<thead>" . PHP_EOL;
            $tabla .= '<tr>'
                . '<th colspan="5"><img src="' . $this->traeLogo() . '" /></th>'
                . '<th colspan="3"><strong>' . $_SESSION['nombre_empresa'] . '</strong></th>'
                . '</tr>';
            $tabla .= '<tr>'
                . '<th colspan="8">Direccion: ' . $_SESSION['domicilio_empresa'] . ' '
                . '| Tel: ' . $_SESSION['telefono_empresa'] . ' '
                . '| E-mail: ' . $_SESSION['email_empresa'] . '</th>'
                . '</tr>';
            //            $tabla .=   "<tr><th>{$objCliente->listaPrecio}</th></tr>";
            $tabla .= "<tr>";
            $tabla .= "<th colspan='8'>Lista de precios al " . date("d/m/Y") . " </th>";
            $tabla .= "</tr>";
            if (is_object($objCliente)) {
                $tabla .= "<tr>";
                $tabla .= "<th colspan='8'>Cliente: <strong>" . $objCliente->NombreCliente . "</strong> </th>";
                $tabla .= "</tr>";
            }
            $tabla .=  "<tr>" . PHP_EOL;
            $tabla .=  "<th>Negocio</th>" . PHP_EOL;
            $tabla .=  "<th>Rubro</th>" . PHP_EOL;
            $tabla .=  "<th>Sub Rubro</th>" . PHP_EOL;
            $tabla .=  "<th>Cod</th>" . PHP_EOL;
            $tabla .=  "<th>Producto</th>" . PHP_EOL;
            //            $tabla .=  "<th>P.Neto</th>".PHP_EOL;
            //            $tabla .=  "<th>Alic.</th>".PHP_EOL;
            $tabla .=  "<th>P.Final</th>" . PHP_EOL;
            //            $tabla .=  "<th>Cant</th>".PHP_EOL;
            $tabla .=  "<th>Desc</th>" . PHP_EOL;
            $tabla .=  "<th>P.Final Desc</th>" . PHP_EOL;
            $tabla .=  "</tr>" . PHP_EOL;
            $tabla .=  "</thead>" . PHP_EOL;
            $tabla .=  "<tbody>" . PHP_EOL;
            $promoLista = "no";
            foreach ($this->colArticulos as $arti) {

                switch ($listaPrecioCliente) {
                    case 'Lista 1':
                        $precioNeto     = $arti->Precio1V;
                        $importeIva     = $arti->impIva1;
                        $importeInterno = $arti->imp_interno1;
                        $precioVenta    = $arti->Precio1VI;
                        if ($arti->promocion_lista1 == "Si") {
                            $promoLista = "si";
                        } else {
                            $promoLista = "no";
                        }
                        break;
                    case 'Lista 2':
                        $precioNeto     = $arti->Precio2V;
                        $importeIva     = $arti->impIva2;
                        $importeInterno = $arti->imp_interno2;
                        $precioVenta    = $arti->Precio2VI;
                        if ($arti->promocion_lista2 == "Si") {
                            $promoLista = "si";
                        } else {
                            $promoLista = "no";
                        }
                        break;
                    case 'Lista 3':
                        $precioNeto     = $arti->Precio3V;
                        $importeIva     = $arti->impIva3;
                        $importeInterno = $arti->imp_interno3;
                        $precioVenta    = $arti->Precio3VI;
                        //                        echo "<pre> ".$arti->IDArt." - ".$arti->promocion_lista3."</pre>";
                        if ($arti->promocion_lista3 == "Si") {
                            $promoLista =   "si";
                        } else {
                            $promoLista =   "no";
                        }
                        break;
                    case 'Lista 4':
                        $precioNeto     = $arti->Precio4V;
                        $importeIva     = $arti->impIva4;
                        $importeInterno = $arti->imp_interno4;
                        $precioVenta    = $arti->Precio4VI;
                        if ($arti->promocion_lista4 == "Si") {
                            $promoLista = "si";
                        } else {
                            $promoLista = "no";
                        }
                        break;
                    case 'Lista 5':
                        $precioNeto     = $arti->Precio5V;
                        $importeIva     = $arti->impIva5;
                        $importeInterno = $arti->imp_interno5;
                        $precioVenta    = $arti->Precio5VI;
                        if ($arti->promocion_lista5 == "Si") {
                            $promoLista = "si";
                        } else {
                            $promoLista = "no";
                        }
                        break;
                    case 'Lista Oficial':
                        $precioNeto     = $arti->PNOficial;
                        $importeIva     = $arti->impOf;
                        $importeInterno = $arti->imp_internoOF;
                        $precioVenta    = $arti->PFOficial;
                        $promoLista     = "si";

                        break;
                }
                //                validar si se usa una promocion.
                //                primero a por las fechas..
                //                segundo a por los descuentos calculados...
                $precioVentaFinal = $precioVenta;
                $descFinal      = 0;
                $clase          = "";
                $clasePrecio    = "";
                if ($usaIdManual == "Si") {
                    $idArtT = $arti->id_manual;
                } else {
                    $idArtT = $arti->IDArt;
                }
                $idArt          = $arti->IDArt;
                $promoCant      = "";
                $promoPorc      = "";
                $promoTipo      = $arti->promocion_tipo;
                $promo          = "no";
                $nombreArticulo = $arti->NombreArticulo;
                $aplicaPromo    = "no";
                $desc           = "si";
                /*
                 * Articulo en promocion
                 * 
                 * coloco los datos de la promocion para saber si se aplica y que descuentos
                 * la promocion se aplica cuando se compra la cantidad, 
                 * **/
                if ($arti->promocion == 'Si' && $promoLista == "si") {
                    /*
                     * Hay promocion cargada
                     */
                    $promoCant = $arti->promocion_cant;
                    $promoPorc = $arti->promocion_por;

                    /*
                     * Evaluo si la promocion que podria aplicar tiene un porcentaje
                     * que sea mayor al descuento del renglon del cliente, si no
                     * dejo el descuento del cliente. 
                     */
                    if ($descRenglon > 0) {
                        /*
                         * Hay descuento por renglon
                         */
                        if ($descRenglon > $promoPorc) {
                            /*
                         * el descuento x renglon es mayor que la promocion
                         * la desactivo
                         */
                            $descFinal = $descRenglon;
                            $aplicaPromo = "no";
                        } else {
                            /*
                             * el descuento x renglon es menor uso la promocion
                             */
                            $descFinal = $promoPorc;
                            $aplicaPromo = "si";
                        }
                    } else {
                        /*
                         * No hay descuento x renglon uso la promocion
                         */
                        $descFinal = $promoPorc;
                        $aplicaPromo = "si";
                    }

                    if ($arti->promocion_vigencia_desde == null || $arti->promocion_vigencia_hasta == null) {
                        /*
                         * vigencias en Nulo esta siempre en promocion.
                         */

                        $desc = 'no'; // desactivo el descuento
                        //                        $idArt .='-(P)';
                        $nombreArticulo .= '-(promocion)';
                        $clase          = 'class="promocion"';
                        $clasePrecio    = "promocion";

                        /*
                         * Promocion por cantidad
                         */
                        if ($promoCant == 0) {
                            /*
                            * No aplico la promocion por cantidad
                            */
                            //                            $precioNetoNuevo    = $precioNeto - ($precioNeto * $promoCant /100);
                            $precioNetoNuevo    = $precioNeto;
                            $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                            $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioNeto         = $precioNetoNuevo;
                            $promoCant          = "";
                            //                            $promoPorc          = "";
                            $promo              = "no";
                            $cantidad           =   1;
                        } else {
                            /*
                             * coloco la cantidad de articulos que se usa p promocion
                             * pero si por el porcentaje no se aplica..no lo uso
                             */
                            if ($aplicaPromo == "si") {
                                $promo      = "si";
                                $cantidad   = number_format($promoCant);
                            } else {
                                $promo    = "no";
                                $cantidad = number_format($promoCant);
                            }
                        }
                    } else {
                        /*
                         * Evaluo la vigencia de la promocion tiene un intervalo
                         */
                        //echo var_dump($arti->promocion_vigencia_desde!=null);
                        $fd     = explode('-', $arti->promocion_vigencia_desde);
                        $fh     = explode('-', $arti->promocion_vigencia_hasta);
                        $desde  = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
                        $hasta  = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
                        $hoyD   = getdate();
                        $hoy    = mktime(0, 0, 0, $hoyD['mon'], $hoyD['mday'], $hoyD['year']);
                        if ($hoy >= $desde && $hoy <= $hasta) {
                            /*
                             * Promocion Valida de intervalo
                             */
                            //                            $idArt .='-(P)';
                            $desc = 'no'; // desactivo el descuento
                            $nombreArticulo .= '-(promocion)';
                            $clase          = 'class="promocion"';
                            $clasePrecio    = "promocion";
                            /*
                             * Compre la promociones con la cantidad
                             */
                            if ($promoCant == 0) {
                                /*
                                 * No aplico la promocion por cantidad
                                 */
                                //                              $precioNetoNuevo    = $precioNeto - ($precioNeto * $promoPorc /100);
                                $precioNetoNuevo    = $precioNeto;
                                $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                                $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                                $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                                $precioNeto         = $precioNetoNuevo;
                                $promoCant          = "";
                                //                                $promoPorc          = "";
                                $promo              = "no";
                                $cantidad           = 1;
                            } else {
                                /*
                                 * tengo la cantidad que entra en la promocion
                                 * pero si no la uso por el porcentaje no la activo
                                 */
                                if ($aplicaPromo == "si") {
                                    $promo      = "si";
                                    $cantidad   = number_format($promoCant);
                                } else {
                                    $promo      = "no";
                                    $cantidad   = 1;
                                }
                            }
                        } else {
                            // hay promocion pero vencida no la uso.

                            $promoCant          = "";
                            //                                $promoPorc          = "";
                            $promo              = "no";
                            $cantidad           = 1;
                        }
                    }
                } else {
                    /*
                     * No existe promocion asi que evaluo si aplico el descuento
                     * x renglon del articulo.
                     */
                    if ($descRenglon > 0) {
                        /*
                         * Debo recalcular el precio de acuerdo al descuento
                         */
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc  * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = $descRenglon;
                    }
                    $cantidad = 1;
                    $promo = "no";
                }
                //$hoy3 = $hoy; 
                //                formateo los precios a cuatro decimales..just in case...
                $precioVentaF = number_format($precioVenta, 4, ',', '');
                $precioVentaFinalF = number_format($precioVentaFinal, 4, ',', '');
                $precioNetoF = number_format($precioNeto, 4, ',', '');

                $tabla .= "<tr>";
                $tabla .= "<td {$clase}>{$arti->NombreTipoCliente}</td>";
                $tabla .= "<td {$clase}>{$arti->NombRub}</td>";
                $tabla .= "<td {$clase}>{$arti->NombSubRub}</td>";
                $tabla .= "<td {$clase}>{$idArtT}</td>";
                $tabla .= "<td {$clase}>{$nombreArticulo}</td>";
                //                . "<div class='rubroSub'>".$arti->NombRub.", ".$arti->NombSubRub."</div></td>".PHP_EOL;
                //                $tabla .= "<td class='importe {$clasePrecio}'>$".$precioNetoF."</td>".PHP_EOL;
                //                $tabla .= "<td class='importe {$clasePrecio}'>{$arti->Alic}</td>".PHP_EOL;
                $tabla .= "<td class='importe {$clasePrecio}' > <i class='fa fa-dollar-sign'></i><span id='mi-art-precio{$arti->IDArt}'>{$precioVentaF}</span></td>" . PHP_EOL;
                //                $tabla .= "<td {$clase}>".PHP_EOL;
                ////                $tabla .= "<input type='number' id='mi-cantidad-{$arti->IDArt}' pattern='[0-9]+([,\.][0-9]+)?' value='{$cantidad}' min='1.00' step='0.00'  style='width:40px;' />".PHP_EOL; 
                //                    //$tabla .= "<input type='text' id='mi-cantidad-{$arti->IDArt}' value='1' size='3' />".PHP_EOL;                                      
                //                $tabla .= "</td>".PHP_EOL;
                $tabla .= "<td {$clase}>" . PHP_EOL;

                if ($promo == 'no') {
                    /*
                     * No tengo promocion
                     */
                    if ($_SESSION['tipousuario'] == 'cliente') {
                        /*
                         * Soy un cliente y tengo descuento pero no puedo tocarlo
                         * debo calcular el precio con el descuento
                         */



                        $tabla .= "{$descFinal}%" . PHP_EOL;
                        $tabla .= "</td>" . PHP_EOL;
                    } else {
                        /*
                         * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                         * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                         */
                        if ($descFinal == 0) {

                            $tabla .= "0%" . PHP_EOL;
                        } else {
                            $tabla .= "{$descFinal}%" . PHP_EOL;
                        }
                        $tabla .= "</td>" . PHP_EOL;
                    }
                } else {
                    /*
                     * Hay promocion
                     */
                    $tabla .= "{$descFinal}%" . PHP_EOL;
                    $tabla .= "</td>" . PHP_EOL;
                }
                $tabla .= "<td class='importe {$clasePrecio}' > <i class='fa fa-dollar-sign'></i><span id='mi-art-precio{$arti->IDArt}'>{$precioVentaFinalF}</span></td>" . PHP_EOL;
                $tabla .= "</tr>" . PHP_EOL;
            }
            $tabla .= "</tbody>" . PHP_EOL;
            $tabla .= "</table>" . PHP_EOL;
            return $tabla;
        } else {
            //            $tabla = "<tr><td class='vacio'>No se encontaron resultados </td></tr>";
            //            echo $tabla;
            //            echo "<pre>";
            //                print_r($pp);
            //                echo "</pre>";
            return '0';
        }
    }
    /*
     * Logo para listados.
     */
    function traeLogo()
    {
        $query = "SELECT 
                        logo AS Foto,
                        'image/pjpeg' AS Tipo 
                FROM configuracion;";
        $sal = mysqli_query($this->connV, $query) or die("no anduvo" . mysqli_error($this->connV));
        $fila = mysqli_fetch_array($sal);

        $fileName = "_img/logototal.png";
        $foto = imagepng(imagecreatefromstring($fila["Foto"]), $fileName);
        //    $logo=fopen($fileName,"w");
        //    fwrite($logo, $foto);
        //    fclose($logo);
        //file_put_contents($fileName, $logo);
        return $fileName;
    }
    /*
     * Funcion Vista Excel
     */
    function vista_lista_excel($listaPrecioCliente = null, $descRenglon = null, $objCliente = null, $quien = null, $desde = null, $hasta = null)
    {
        // ahora tengo que devolve un array con opciones de Titulo, Cabeceras y Datos de cada cabecera.
        //
        $usaIdManual = $_SESSION["usa_id_manual"];
        $arrayExcel = array();
        //$tabla='<table class="tablesorter" cellspacing="1" id="myTable" >';
        if (!empty($this->colArticulos)) {

            $limDescReng    = 0;
            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            $precioVentaF = 0;
            /*
             * Embalaje
             */
            if ($_SESSION["utilizaEmbalaje"] == "Si") {
                $usaEmbalaje = "Si";
            } else {
                $usaEmbalaje = "No";
            }
            /*
             * IVA incluido
             */
            $ivaIncluido = $_SESSION["ivaIncluido"];
            //            $cantidad = $_REQUEST['cantidadOferta'];
            //con el tema de la oferta dejo una idea a desarrollar, pasar el id
            //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
            //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
            //            borro todo y coloco un uno
            if ($_REQUEST['cantidadOferta']) {
                $cantidad = $_REQUEST['cantidadOferta'];
            } else {
                $cantidad = 1;
            }
            /*
             * TITULOS
             */
            if ($quien == "consumo") {
                $arrayExcel["titulo"][] =  $_SESSION['nombre_empresa'];
                $arrayExcel["titulo"][] = 'Direccion: ' . $_SESSION['domicilio_empresa'] . ' '
                    . '| Tel: ' . $_SESSION['telefono_empresa'] . ' '
                    . '| E-mail: ' . $_SESSION['email_empresa'];


                //                $tabla .=  "<thead>".PHP_EOL;
                //                $tabla .='<tr>'
                //                       .'<th colspan="9"><strong>'.$_SESSION['nombre_empresa'].'</strong></th>'
                //
                //                       .'</tr>';
                //                $tabla .='<tr>'
                //                        . '<th colspan="8">Direccion: '.$_SESSION['domicilio_empresa'].' '
                //                        . '| Tel: '.$_SESSION['telefono_empresa'].' '
                //                        . '| E-mail: '.$_SESSION['email_empresa'].'</th>'
                //                        . '</tr>';
                //            $tabla .=   "<tr><th>{$objCliente->listaPrecio}</th></tr>";
                if ($desde != null && $hasta != null) {
                    $arrayExcel["titulo"][] = "Lista de consumos desde " . $hasta . " al " . $desde;
                    //                    $tabla .="<tr>";
                    //                    $tabla .="<th colspan='9'>Lista de consumos desde ".$hasta." al ".$desde." </th>";
                    //                    $tabla .="</tr>";
                } else {
                    $arrayExcel["titulo"][] = "Lista de consumos al " . date("d/m/Y");
                    //                    $tabla .="<tr>";
                    //                    $tabla .="<th colspan='9'>Lista de consumos al ".date("d/m/Y")." </th>";
                    //                    $tabla .="</tr>";
                }

                if (is_object($objCliente)) {
                    $arrayExcel["titulo"][] = "Cliente: " . $objCliente->cliente;
                    //                    $tabla .="<tr>";            
                    //                    $tabla .="<th colspan='9'>Cliente: <strong>".$objCliente->cliente."</strong> </th>";
                    //                    $tabla .="</tr>";
                    if ($ivaIncluido == 'no') {
                        $arrayExcel["titulo"][] = "Los precios NO incluyen IVA. ";
                        //                         $tabla .="<tr>";            
                        //                        $tabla .="<th colspan='9'>Los precios NO incluyen IVA. </th>";
                        //                        $tabla .="</tr>";
                    } else {
                        $arrayExcel["titulo"][] = "Los precios tienen IVA incluido.";
                        //                        $tabla .="<tr>";            
                        //                        $tabla .="<th colspan='9'>Los precios tienen IVA incluido. </th>";
                        //                        $tabla .="</tr>";
                    }
                }
                /*
                 * CABECERAS
                 */
                //                $tabla .=  "<tr>".PHP_EOL;
                $arrayExcel["cabeceras"][] = "Rubro";
                $arrayExcel["cabeceras"][] = "Sub Rubro";
                $arrayExcel["cabeceras"][] = "Cod";
                $arrayExcel["cabeceras"][] = "Artículo";
                $arrayExcel["cabeceras"][] = "Lista";
                $arrayExcel["cabeceras"][] = "Desc";
                $arrayExcel["cabeceras"][] = "Lista c/dcto";
                if ($usaEmbalaje == "Si") {
                    $arrayExcel["cabeceras"][] = "Bulto Cerrado";
                }

                //                $tabla .=  "<th>Rubro</th>".PHP_EOL;
                //                $tabla .=  "<th>Sub Rubro</th>".PHP_EOL;
                //                $tabla .=  "<th>Cod</th>".PHP_EOL;
                //                $tabla .=  "<th class='center'>Artículo</th>".PHP_EOL;
                //                
                //    
                //                $tabla .=  "<th>Cant.</th>".PHP_EOL;
                //                $tabla .=  "<th class='right'>Lista</th>".PHP_EOL;
                //    
                //                $tabla .=  "<th class='right'>Desc</th>".PHP_EOL;
                //                $tabla .=  "<th class='right'>Lista c/dcto</th>".PHP_EOL;
                //                if($usaEmbalaje=="Si"){
                //                    $tabla .=  "<th>Bulto Cerrado</th>".PHP_EOL;    
                //                }
                //                $tabla .=  "</tr>".PHP_EOL;
                //                $tabla .=  "</thead>".PHP_EOL;

                //                $tabla .=  "<tbody>".PHP_EOL;
                /**
                 * RENGLONES
                 */
                $promoLista = "no";
                $numFila = 0;
                foreach ($this->colArticulos as $arti) {
                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = $arti->IDArt;
                    }
                    $idArt = $arti->IDArt;
                    // $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;

                    /*
                     * LISTA DE PRECIOS
                     */

                    $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon);

                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                        //                    $bulto = $arti->nombre_presentacion ." x ".$arti->cantidad_uni."(".$arti->nombre_unimed.")";

                    }

                    if ($ivaIncluido == 'no') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["netoCalc"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioVentaFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, '.', '');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, '.', '');
                    $precioNetoF = number_format($precioNeto, 2, '.', '');

                    $clase = $precios["clase"];
                    $nombreArticulo .= $precios["promoNombre"];
                    $clasePrecio = $precios["clasePrecio"];


                    $arrayExcel["campos"][$numFila][] = $arti->NombRub;
                    $arrayExcel["campos"][$numFila][] = $arti->NombSubRub;
                    $arrayExcel["campos"][$numFila][] = $idArtT;
                    $arrayExcel["campos"][$numFila][] = $nombreArticulo;
                    $arrayExcel["campos"][$numFila][] = $arti->Cuantos;
                    $arrayExcel["campos"][$numFila][] = $precioVentaF;
                    $arrayExcel["campos"][$numFila][] = $descFinal;
                    if ($usaEmbalaje == "Si") {
                        $arrayExcel["campos"][$numFila][] = $bulto;
                    }

                    $numFila++;
                }
                //$tabla .= "</tbody>".PHP_EOL;
                // $tabla .= "</table>".PHP_EOL;
            }
            if ($quien == "listap") {
                /*
                 * *TITULOS
                 */
                //                $tabla .=  "<thead>".PHP_EOL;
                //                $tabla .='<tr>'
                //                       .'<th colspan="9"><strong>'.$_SESSION['nombre_empresa'].'</strong></th>'
                //
                //                       .'</tr>';
                //                $tabla .='<tr>'
                //                        . '<th colspan="8">Direccion: '.$_SESSION['domicilio_empresa'].' '
                //                        . '| Tel: '.$_SESSION['telefono_empresa'].' '
                //                        . '| E-mail: '.$_SESSION['email_empresa'].'</th>'
                //                        . '</tr>';
                //            $tabla .=   "<tr><th>{$objCliente->listaPrecio}</th></tr>";

                $arrayExcel["titulo"][] =  $_SESSION['nombre_empresa'];
                $arrayExcel["titulo"][] = 'Direccion: ' . $_SESSION['domicilio_empresa'] . ' '
                    . '| Tel: ' . $_SESSION['telefono_empresa'] . ' '
                    . '| E-mail: ' . $_SESSION['email_empresa'];
                $arrayExcel["titulo"][] = "Lista de precios al " . date("d/m/Y");
                //                $tabla .="<tr>";
                //                $tabla .="<th colspan='9'>Lista de precios al ".date("d/m/Y")." </th>";
                //                $tabla .="</tr>";
                if (is_object($objCliente)) {
                    //                    $tabla .="<tr>";            
                    //                    $tabla .="<th colspan='9'>Cliente: <strong>".$objCliente->cliente."</strong> </th>";
                    //                    $tabla .="</tr>";
                    $arrayExcel["titulo"][] = "Cliente: " . $objCliente->cliente;
                    if ($ivaIncluido == 'no') {

                        //                        $tabla .="<tr>";            
                        //                        $tabla .="<th colspan='9'>Los precios NO incluyen IVA. </th>";
                        //                        $tabla .="</tr>";
                        $arrayExcel["titulo"][] = "Los precios NO incluyen IVA. ";
                    } else {
                        $arrayExcel["titulo"][] = "Los precios tienen IVA incluido. ";
                        //                        $tabla .="<tr>";            
                        //                        $tabla .="<th colspan='9'>Los precios tienen IVA incluido. </th>";
                        //                        $tabla .="</tr>";
                    }
                }



                /*
                 * CABECERAS
                 */
                $arrayExcel["cabeceras"][] = "Negocio";
                $arrayExcel["cabeceras"][] = "Rubro";
                $arrayExcel["cabeceras"][] = "Sub Rubro";
                $arrayExcel["cabeceras"][] = "Cod";
                $arrayExcel["cabeceras"][] = "Artículo";
                $arrayExcel["cabeceras"][] = "Lista";
                $arrayExcel["cabeceras"][] = "Desc";
                $arrayExcel["cabeceras"][] = "Lista c/dcto";

                if ($usaEmbalaje == "Si") {
                    $arrayExcel["cabeceras"][] = "Bulto cerrado";
                }


                /*
                 * RENGLONES
                 */

                //$tabla .=  "<tbody>".PHP_EOL;
                $promoLista = "no";
                $numFila = 0;
                foreach ($this->colArticulos as $arti) {
                    if ($usaIdManual == "Si") {
                        $idArtT = $arti->id_manual;
                    } else {
                        $idArtT = $arti->IDArt;
                    }
                    $idArt          = $arti->IDArt;
                    $nombreArticulo = $arti->NombreArticulo;

                    /*
                     * LISTA DE PRECIOS
                     */

                    $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon);

                    /*
                     * EMBALAJE
                     */
                    $bulto = "";
                    $nombreTipoCliente = "";
                    if ($usaEmbalaje == "Si") {
                        // tengo que hacer la busqueda de los valores para mostrar
                        if ($arti->nombre_presentacion != "") {
                            $bulto = $arti->nombre_presentacion . " x " . number_format($arti->cantidad_uni, 0, ',', '');
                        }

                        if ($arti->nombre_unimed != "") {
                            $bulto .= " (" . $arti->nombre_unimed . ")";
                        }
                        //                    $bulto = $arti->nombre_presentacion ." x ".$arti->cantidad_uni."(".$arti->nombre_unimed.")";

                    }

                    if ($ivaIncluido == 'no') {
                        $precioVenta = $precios["neto"];
                        $precioVentaFinal = $precios["netoCalc"];
                    } else {
                        $precioNeto = $precios["neto"];
                        $precioVenta = $precios["precioVenta"];
                        $precioVentaFinal = $precios["precioVentaFinal"];
                    }

                    $descFinal = $precios["descuento"];
                    //                formateo los precios a cuatro decimales..just in case...
                    $precioVentaF = number_format($precioVenta, 2, '.', '');
                    $precioVentaFinalF = number_format($precioVentaFinal, 2, '.', '');
                    $precioNetoF = number_format($precioNeto, 2, '.', '');


                    $clase = $precios["clase"];
                    $nombreArticulo .= $precios["promoNombre"];
                    $clasePrecio = $precios["clasePrecio"];


                    if ($arti->NombreTipoCliente != "") {
                        //                        $tabla .= "<td {$clase}>{$arti->NombreTipoCliente}</td>";
                        $nombreTipoCliente = $arti->NombreTipoCliente;
                    } else {
                        //                        $tabla .= "<td {$clase}>-</td>";
                        $nombreTipoCliente = "-";
                    }
                    $arrayExcel["campos"][$numFila][] = $nombreTipoCliente;
                    $arrayExcel["campos"][$numFila][] = $arti->NombRub;
                    $arrayExcel["campos"][$numFila][] = $arti->NombSubRub;
                    $arrayExcel["campos"][$numFila][] = $idArtT;
                    $arrayExcel["campos"][$numFila][] = $nombreArticulo;
                    $arrayExcel["campos"][$numFila][] = $precioVentaF;
                    $arrayExcel["campos"][$numFila][] = $descFinal;
                    $arrayExcel["campos"][$numFila][] = $precioVentaFinalF;
                    if ($usaEmbalaje == "Si") {
                        $arrayExcel["campos"][$numFila][] = $bulto;
                    }
                    $numFila++;
                }
                //                $tabla .= "</tbody>".PHP_EOL;
                //                 $tabla .= "</table>".PHP_EOL;
            }
            return $arrayExcel;
        } else {
            //            $tabla = "<tr><td class='vacio'>No se encontaron resultados </td></tr>";
            //            echo $tabla;
            //            echo "<pre>";
            //                print_r($pp);
            //                echo "</pre>";
            return '0';
        }
    }

    /*
     * Funcion Lista Precio 
     */

    public function mostrar_articulo_lista($arrParametros, $tipo = null, $claseLista = null)
    {
        //    echo "escubidoo<pre>";
        //    print_r($arrParametros);
        //    echo "</pre>";
        //if(isset($_REQUEST['idTipoCliente'])){
        //buscar com hacer para mandar los parametros si estan todos y mandarlos porque si falta alguno
        // lo vamos completando con otros datos y demas pero una vez que busco y tengo el articulo lleno
        $categoria      = null;
        $rubro          = null;
        $subRubro       = null;
        $marca          = null;
        $modelo         = null;
        $laboratorio    = null;
        $buscRapida     = null;
        $idArt          = null;
        $idDeposito     = null;
        $claseBusqueda  = null;
        $tipoCliente    = $tipo;
        $idTipoCliente  = null;
        $queCliente     = null;
        $listaPrecioCliente = 'Lista 1';
        
        $misConsumos    = null;
        $queCampo       = "nombre";
        $promo          = null;
        $ivaIncluido = null;
        $imagenProducto = null;
        $proveedor= null;
        $tacc=null;
        // $tipoListado = $tipo;

        $arrParametros['tipoListado'] = $tipo;
        // * datos del vendedor para desposito.


        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            $idDeposito = $objVendedor->id_deposito;
        } else {
            $idDeposito = $_SESSION["deposito"];
        }

        $arrParametros['idDeposito'] = $idDeposito;



        // echo 'Param <pre>',print_r($arrParametros),'</pre>';
        if(key_exists('ivaIncluido',$arrParametros)){
            $ivaIncluido = $arrParametros['ivaIncluido'];
        }

        if ($queCliente == "cliente") {
            // Cliente seleccionado
            if (is_object($_SESSION['cliente'])) {
                $objCliente = $_SESSION['cliente'];
            } else {
                $objCliente = $_SESSION['cliente'][0];
            }

            //posiblemente un vendedor
            if ($objCliente->listaPrecio != '') {
               // $listaPrecioCliente = $objCliente->listaPrecio;
                $descRenglon        = $objCliente->descRenglon;
                $codCliente = $objCliente->Codigo;
            } else {
                // todos los clientes
                
                $descRenglon    = 0;
                $codCliente = null;
            }
        }

        if ($queCliente !== "cliente") {
            // todos los clientes

            
            $descRenglon    = 0;
            $codCliente = null;
            $objCliente = null;
        }

        if (key_exists('listaDePrecio', $arrParametros)) {
            $listaPrecioCliente = $arrParametros['listaDePrecio'];
        }
        if (key_exists('imagenProducto', $arrParametros)) {
            $imagenProducto = $arrParametros['imagenProducto'];
        }
        // $this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, null, null, null, $misConsumos, $categoria, $promo);
        $this->busqueda($arrParametros);

        //}
        $objLista = null;

        // Html
        // echo "HTML";
        //incluyo en variable sesion para evitar tocar mucho mas codigo.
        $_SESSION['ivaIncluidoLista'] = $ivaIncluido;

        echo $this->vista_lista_html($listaPrecioCliente, $descRenglon, $objCliente, $tipo, $claseLista, $imagenProducto);


        return $objLista;
    }

    /** Funcion Lista de precio PDF con imagenes o sin */
    public function mostrar_articulo_lista_pdf($arrParametros, $tipo = null, $claseLista = null)
    {
        //    echo "escubidoo<pre>";
        //    print_r($arrParametros);
        //    echo "</pre>";
        //if(isset($_REQUEST['idTipoCliente'])){
        //buscar com hacer para mandar los parametros si estan todos y mandarlos porque si falta alguno
        // lo vamos completando con otros datos y demas pero una vez que busco y tengo el articulo lleno
        $categoria      = null;
        $rubro          = null;
        $subRubro       = null;
        $marca          = null;
        $modelo         = null;
        $laboratorio    = null;
        $buscRapida     = null;
        $idArt          = null;
        $idDeposito     = null;
        $claseBusqueda  = null;
        $tipoCliente    = $tipo;
        $idTipoCliente  = null;
        $queCliente     = null;
        $listaPrecioCliente = 'Lista 1';
        
        $misConsumos    = null;
        $queCampo       = "nombre";
        $promo          = null;
        $ivaIncluido = null;
        $imagenProducto = null;
        $proveedor= null;
        $tacc=null;
        // $tipoListado = $tipo;

        $arrParametros['tipoListado'] = $tipo;
        // * datos del vendedor para desposito.


        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            $idDeposito = $objVendedor->id_deposito;
        } else {
            $idDeposito = $_SESSION["deposito"];
        }

        $arrParametros['idDeposito'] = $idDeposito;



        // echo 'Param <pre>',print_r($arrParametros),'</pre>';
        if(key_exists('ivaIncluido',$arrParametros)){
            $ivaIncluido = $arrParametros['ivaIncluido'];
        }

        if ($queCliente == "cliente") {
            // Cliente seleccionado
            if (is_object($_SESSION['cliente'])) {
                $objCliente = $_SESSION['cliente'];
            } else {
                $objCliente = $_SESSION['cliente'][0];
            }

            //posiblemente un vendedor
            if ($objCliente->listaPrecio != '') {
               // $listaPrecioCliente = $objCliente->listaPrecio;
                $descRenglon        = $objCliente->descRenglon;
                $codCliente = $objCliente->Codigo;
            } else {
                // todos los clientes
                
                $descRenglon    = 0;
                $codCliente = null;
            }
        }

        if ($queCliente !== "cliente") {
            // todos los clientes

            
            $descRenglon    = 0;
            $codCliente = null;
            $objCliente = null;
        }

        if (key_exists('listaDePrecio', $arrParametros)) {
            $listaPrecioCliente = $arrParametros['listaDePrecio'];
        }
        if (key_exists('imagenProducto', $arrParametros)) {
            $imagenProducto = $arrParametros['imagenProducto'];
        }
        // $this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, null, null, null, $misConsumos, $categoria, $promo);
        $this->busqueda($arrParametros);

        //}
        $objLista = null;

        // Html
        // echo "HTML";
        //incluyo en variable sesion para evitar tocar mucho mas codigo.
        $_SESSION['ivaIncluidoLista'] = $ivaIncluido;
        $htmlFinal = $this->vista_lista_html($listaPrecioCliente, $descRenglon, $objCliente, $tipo, $claseLista, $imagenProducto);


        return $htmlFinal;
    }

    
    // * funcion para las promociones separadas.

    public function mostrar_listado_promociones($arrParametros)
    {
        $categoria      = null;
        $rubro          = null;
        $subRubro       = null;
        $marca          = null;
        $modelo         = null;
        $laboratorio    = null;
        $buscRapida     = null;
        $idArt          = null;
        $idDeposito     = null;
        $claseBusqueda  = null;
        $idTipoCliente  = null;
        $queCliente     = null;
        $listaPrecioCliente = "";
        $misConsumos    = null;
        $queCampo       = "nombre";
        $promo          = null;
        $ivaIncluido = null;
        $imagenProducto = null;
        $tipo="listaPromociones";
        // $tipoListado = $tipo;
        $arrParametros['tipoListado'] = $tipo;

        // * datos del vendedor para desposito.


        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            $idDeposito = $objVendedor->id_deposito;
        } else {
            $idDeposito = $_SESSION["deposito"];
        }

        $arrParametros['idDeposito'] = $idDeposito;



        // es el descuento por cli del cilente

        // iva incluido para todos
        if (isset($_SESSION['ivaIncluido']) && $_SESSION['ivaIncluido'] != "") {
            $ivaIncluido = $_SESSION['ivaIncluido'];
        }
        // si existe viene de un formulario prestar atencion.
        if (isset($_REQUEST['ivaIncluido'])) {
            $ivaIncluido = $_REQUEST['ivaIncluido'];
        }

        if ($queCliente == "cliente") {
            // Cliente seleccionado
            if (is_object($_SESSION['cliente'])) {
                $objCliente = $_SESSION['cliente'];
            } else {
                $objCliente = $_SESSION['cliente'][0];
            }

            //posiblemente un vendedor
            if ($objCliente->listaPrecio != '') {
                $listaPrecioCliente = $objCliente->listaPrecio;
                $descRenglon        = $objCliente->descRenglon;
                $codCliente = $objCliente->Codigo;
            } else {
                // todos los clientes
                $listaPrecioCliente = 'Lista 1';
                $descRenglon    = 0;
                $codCliente = null;
            }
        } else {
            // todos los clientes

            $listaPrecioCliente = 'Lista 1';
            $descRenglon    = 0;
            $codCliente = null;
            $objCliente = null;
        }
        if (key_exists('imagenProducto', $arrParametros)) {
            $imagenProducto = $arrParametros['imagenProducto'];
        }
        // $this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, null, null, null, $misConsumos, $categoria, $promo);
        $this->busqueda($arrParametros);

        //}
        $objLista = null;

        // Html
        // echo "HTML";
        //incluyo en variable sesion para evitar tocar mucho mas codigo.
        $_SESSION['ivaIncluidoLista'] = $ivaIncluido;
        echo $this->vista_lista_promociones($listaPrecioCliente, $descRenglon, $objCliente, $imagenProducto);


        return $objLista;
    }

    /*
     * Funcion Lista precio Mis consumos
     */

    public function mostrar_consumos($salida = 0)
    {


        $categoria      = null;
        $rubro          = null;
        $subRubro       = null;
        $marca          = null;
        $modelo         = null;
        $laboratorio    = null;
        $buscRapida     = null;
        $idArt          = null;
        $idDeposito     = null;
        $claseBusqueda  = null;
        $tipoCliente    = 'consumo';
        $idTipoCliente  = null;
        $queCliente     = null;
        $queCampo       = null;
        $desde          = null;
        $hasta          = null;
        $listaPrecioCliente = "";
        $listaPrecioCliente = 'Lista 1';
        $descRenglon    = 0;
        $codCliente = null;
        $objCliente = null;
        $arr = array();
        if (isset($_REQUEST['tipoCliente'])) {
            //buscar com hacer para mandar los parametros si estan todos y mandarlos porque si falta alguno
            // lo vamos completando con otros datos y demas pero una vez que busco y tengo el articulo lleno
            //echo '<pre>'. print_r($_REQUEST) .'</pre>';



            /*
                 * Datos del vendedor para usar el deposito.
                 */

            if (isset($_SESSION['vendedor'])) {
                $objVendedor = $_SESSION['vendedor'];
                $idDeposito = $objVendedor->id_deposito;
            } else {
                $idDeposito = $_SESSION["deposito"];
            }
            // debo buscar algun algoritmo que me permita buscar los
            // request sin tener que 
            if (isset($_REQUEST['categoria']) && $_REQUEST['categoria'] != "") {
                $arr['categoria'] = $_REQUEST['categoria'];
            }
            if (isset($_REQUEST['rubro']) && $_REQUEST['rubro'] != "") {
                $arr['rubro'] = $_REQUEST['rubro'];
            }
            if (isset($_REQUEST['subrubro']) && $_REQUEST['subrubro'] != "") {
                $arr['subrubro'] = $_REQUEST['subrubro'];
            }
            if (isset($_REQUEST['marca']) && $_REQUEST['marca'] != "") {
                $arr['marca'] = $_REQUEST['marca'];
            }
            if (isset($_REQUEST['modelo']) && $_REQUEST['modelo'] != "") {
                $arr['modelo'] = $_REQUEST['modelo'];
            }
            if (isset($_REQUEST['buscaLaboratorio']) && $_REQUEST['buscaLaboratorio'] != "") {
                $arr['laboratorio'] = $_REQUEST['buscaLaboratorio'];
            }
            if (isset($_REQUEST['queArticulo']) && $_REQUEST['queArticulo'] != "") {
                $arr['buscRapida'] = $_REQUEST['queArticulo'];
            }
            if (isset($_REQUEST['idArticulo']) && $_REQUEST['idArticulo'] != "") {
                $arr['idArt'] = $_REQUEST['idArticulo'];
            }
            if (isset($_REQUEST["claseBusca"])) {
                $arr['claseBusqueda'] = $_REQUEST["claseBusca"];
            }

            if (isset($_REQUEST["idTipoCliente"]) && $_REQUEST["idTipoCliente"] != "") {
                $arr['idTipoCliente'] = $_REQUEST["idTipoCliente"];
            }
            if (isset($_REQUEST["queCliente"]) && $_REQUEST["queCliente"] != "") {
                $arr['queCliente'] = $_REQUEST["queCliente"];
            }
            if (isset($_REQUEST["fechaDesde"]) && $_REQUEST["fechaDesde"] != "") {
                $arr['desde'] = $_REQUEST["fechaDesde"];
            }
            if (isset($_REQUEST["fechaHasta"]) && $_REQUEST["fechaHasta"] != "") {
                $arr['hasta'] = $_REQUEST["fechaHasta"];
            }

            if ($queCliente == "cliente") {
                // Cliente seleccionado
                if (is_object($_SESSION['cliente'])) {
                    $objCliente = $_SESSION['cliente'];
                } else {
                    $objCliente = $_SESSION['cliente'][0];
                }
                $listaPrecioCliente = $objCliente->listaPrecio;
                $descRenglon        = $objCliente->descRenglon;
                $codCliente = $objCliente->Codigo;
            }

            $this->busqueda($arr);
            //$this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, $desde, $hasta, null, null, $categoria);
        }

        $objLista = null;

        switch ($salida) {
            case 0:
                // Html

                echo $this->vista_lista_html($listaPrecioCliente, $descRenglon, $objCliente, "consumo");
                break;
            case 1:
                // excel
                $objLista = $this->vista_lista_excel($listaPrecioCliente, $descRenglon, $objCliente, "consumo", $desde, $hasta);

                break;
            case 2:
                // pdf
                $objLista = $this->vista_lista_pdf($listaPrecioCliente, $descRenglon, $objCliente);
                break;
        }

        return $objLista;
    }

    /*
     * CARRITO (PEDI,REMITOS,etc)
     *  Funcion Lista de precios con tipo de cliente
     * =========================================================================
     */

    public function mostrar_articulo($arrParam = null)
    {
        if (is_object($_SESSION['cliente'])) {
            $objCliente = $_SESSION['cliente'];
        } else {
            $objCliente = $_SESSION['cliente'][0];
        }

        $vistaProducto = 'grid'; // lista
        if (isset($_SESSION['vista_producto'])) {
            $vistaProducto = $_SESSION['vista_producto'];
        }

        $usaIdManual = $_SESSION["usa_id_manual"];
        $listaPrecioCliente = $objCliente->listaPrecio;
        // es el descuento por cli del cilente
        $descRenglon = $objCliente->descRenglon;

        $codCliente = $objCliente->Codigo;
        $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
        $verStock = $_SESSION["verStock"];


        //REMITO de FACTURAS SIN STOCK
        $artFactura = null;

        if (isset($_SESSION["sel_factura"])) {
            $artFactura = $_SESSION["sel_factura"]["art"];
        }

        $arrParam['artFactura'] = $artFactura;
        $arrParam['desde'] = null;
        $arrParam['hasta'] = null;



        //        echo "que hay <pre>";
        //        print_r($_REQUEST);
        //        echo "</pre>";
        // if (isset($_REQUEST['buscarArticulo'])) {
        //buscar com hacer para mandar los parametros si estan todos y mandarlos porque si falta alguno
        // lo vamos completando con otros datos y demas pero una vez que busco y tengo el articulo lleno
        //        echo "que hay <pre>";
        //        print_r($_REQUEST);
        //        echo "</pre>";
        // $categoria = null;
        // $rubro          = null;
        // $subRubro       = null;
        // $marca          = null;
        // $modelo         = null;
        // $laboratorio    = null;
        // $buscRapida     = null;
        // $idArt          = null;
        // $idDeposito     = null;
        // $claseBusqueda  = null;
        // $tipoCliente    = null;
        // $idTipoCliente  = null;
        // $queCampo       = null;
        // $promo          = null;
        // $consumo        = null;
        /*
                 * Datos del vendedor para usar el deposito.
                 */

        if (isset($_SESSION['vendedor'])) {
            $objVendedor = $_SESSION['vendedor'];
            //                    $idDeposito = $objVendedor->id_deposito;

        }


        //                if($_REQUEST['idDeposito']!=""){
        //                    $idDeposito = $_REQUEST['idDeposito'];
        //                }

        //$this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, null, null, $artFactura, $consumo, $categoria, $promo);
        //$this->busqueda($queCampo, $rubro, $subRubro, $marca, $modelo, $laboratorio, $buscRapida, $idArt, $idDeposito, $claseBusqueda, $tipoCliente, $idTipoCliente, $codCliente, null, null, $artFactura, $consumo, $categoria, $promo);
        $this->busqueda($arrParam);




        $tabla = "";
        if (!empty($this->colArticulos)) {
            $limDescReng    = 0;
            $modificaReng = 'No';
            if (isset($objVendedor) && is_object($objVendedor)) {
                $limDescReng    = $objVendedor->lim_desc_renglon;
                $modificaReng = $objVendedor->mod_descuento_renglon;
            }

            $precioNeto = 0;
            $importeIva = 0;
            $importeInterno = 0;
            $precioVenta = 0;
            /*
             * EMBALAJE
             */
            $caminoDispo = $_SESSION["caminoDisp"];
            if ($_SESSION["utilizaEmbalaje"] == "Si") {
                $usaEmbalaje = "Si";
            } else {
                $usaEmbalaje = "No";
            }

            /* Calcula Cantidad X bultos 
             * =========================
             * Si el campo preso promedio bulto, esta con valores y el permiso
             * esta configurado, se multiplica la cantidad ingresada a comprar 
             * por este valor, luego se transmite la cantidad ingresada como
             * url o detalle para saber cuantos bultos reales se compraron,.
             *              
             */
            $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
            $queFormulario = $_SESSION['formulario'];


            /*
            * IVA INCLUIDO
            */
            $ivaIncluido = $_SESSION["ivaIncluido"];
            //            $cantidad = $_REQUEST['cantidadOferta'];
            //con el tema de la oferta dejo una idea a desarrollar, pasar el id
            //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
            //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
            //            borro todo y coloco un uno
            $cantidad = 1;
            // oferta
            if (isset($_REQUEST['cantidadOferta'])) {
                $cantidad = $_REQUEST['cantidadOferta'];
            }
            $soyMovil = 0;
            if ($caminoDispo != "") {
                $soyMovil = 1;
            }
            /**
             * LISTA CARRITOS WEB DESKTOP
             * =============================================================================
             */

            if ($soyMovil == 0) {

                // $html = $this->armarHTMLMovilProductosLista();
                $html = $this->armarHTMLDesktopProductosLista();
                echo $html;
                // echo $tabla;
            }
            /**
             * LISTA CARRITOS MOVIL
             * =============================================================================
             */
            if ($soyMovil == 1) {
                //     $tabla .=  "<thead>".PHP_EOL;
                //     $tabla .=  "<tr>".PHP_EOL;
                //     $tabla .=  "<th>&nbsp;</th>".PHP_EOL;
                //     $tabla .=  "<th>producto</th>".PHP_EOL;
                //     //        $tabla .=  "<th>precio</th>".PHP_EOL; 
                //     //        $tabla .=  "<th>cantidad</th>".PHP_EOL; 
                //     //         $tabla .=  "<th>descuento</th>".PHP_EOL;
                //     $tabla .=  "<th>&nbsp;</th>".PHP_EOL;
                //     $tabla .=  "</tr>".PHP_EOL;
                //     $tabla .=  "</thead>".PHP_EOL;
                //     $tabla .=  "<tbody>".PHP_EOL;
                //     $promoLista = "no";

                //     foreach ($this->colArticulos as $arti) {
                //         // remito de cantidad fija.
                //         $descFinalT = '';
                //         //                $promo = $arti->promocion;
                //         if ($usaIdManual == "Si") {
                //             $idArtT = $arti->id_manual;
                //         } else {
                //             $idArtT = $arti->IDArt;
                //         }
                //         $idArt = $arti->IDArt;
                //         $nombreArticulo = $arti->NombreArticulo;
                //         /*
                //  * Lista de precios
                //  */
                //         $precios = $this->calculaPrecios($arti, $listaPrecioCliente, $descRenglon, $usaReglaPrecio, $codCliente);
                //         //echo "<pre>";
                //         ////                print_r($arti);
                //         // echo "<pre>";
                //         //print_r($arti);
                //         //print_r($precios);
                //         //echo "</pre>";
                //         /*
                //  * EMBALAJE
                //  */
                //         $bulto = "";
                //         if ($usaEmbalaje == "Si") {
                //             // tengo que hacer la busqueda de los valores para mostrar
                //             $bulto = $arti->nombre_presentacion . " x " . $arti->cantidad_uni;
                //             if ($arti->nombre_unimed != "") {
                //                 $bulto .= " (" . $arti->nombre_unimed . ")";
                //             }
                //             //                    $bulto = $arti->nombre_presentacion ." x ".$arti->cantidad_uni."(".$arti->nombre_unimed.")";

                //         }

                //         //$hoy3 = $hoy; 
                //         //                formateo los precios a cuatro decimales..just in case...
                //         $clase = $precios["clase"];
                //         //            $nombreArticulo .=$precios["promoNombre"];
                //         $clasePrecio = "";
                //         //            $clasePrecio = $precios["clasePrecio"];
                //         $cantidad = 1;
                //         if ($precios["cantidad"] > 0) {
                //             $cantidad = $precios["cantidad"];
                //         }
                //         $maxCant = "";
                //         // facturas remitos
                //         if (isset($artFactura) && $artFactura != null) {
                //             $cantidad = $artFactura[$arti->IDArt]["cuanto"];
                //             $maxCant = "max='" . $cantidad . "'";
                //         }

                //         // consumos
                //         if (property_exists($arti, 'CantidadProm')) {
                //             $cantidad = $arti->CantidadProm;
                //         }


                //         $tStock = "";
                //         if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                //             $tStock .= " Stock: " . number_format(($arti->saldo / $arti->cantidad_promedio_bulto), 2, ',', '');
                //             $tStock .= ", Disp: <strong>" . number_format(($arti->stockDisponible / $arti->cantidad_promedio_bulto), 2, ',', '') . "</strong>";
                //             $cantBulto = $arti->cantidad_promedio_bulto;
                //             $tStock .= ", Pres: (" . $arti->cantidad_promedio_bulto . " " . $arti->uniArt . ")";
                //         } else {
                //             $tStock .= "Stock: " . $arti->saldo;
                //             $tStock .= ", Disp: <strong>" . $arti->stockDisponible . "</strong>";
                //             $cantBulto = 1;
                //         }

                //         $promoCant = $precios["cantidad"];
                //         $promo = $precios["promo"];
                //         $tagPromo = "";

                //         if ($precios["promo"] == "si") {
                //             $tagPromo = '<div class="promocion"><i class="fa fa-gift fa-lg fa-fw"></i> En promoción  </div>';
                //             $tagPromo .= '<div class="promocion">' . $this->detalle_promo($precios["promoTipo"], $precios["descuento"], $precios["cantidad"], $precios["idart"]) . "</div>";
                //         }
                //         $importeInterno = $precios["importeInterno"];
                //         $importeIva = $precios["importeIva"];


                //         if ($ivaIncluido == 'no') {
                //             $precioNeto = $precios["neto"];
                //             $precioVenta = $precios["neto"];

                //             $precioVentaFinal = $precios["netoCalc"];
                //             $precioVentaFinalIva = $precios["precioFinal"];
                //         } else {
                //             $precioNeto = $precios["neto"];
                //             $precioVenta = $precios["neto"];
                //             $precioVentaFinal = $precios["netoCalc"];
                //             $precioVentaFinalIva = $precios["precioFinal"];
                //         }

                //         $descFinal = $precios["descuento"];

                //         $precioVentaF = number_format($precioVenta, 2, ',', '');
                //         $precioNetoF = number_format($precioNeto, 2, ',', '');
                //         $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
                //         $precioVentaFinalFiva = number_format($precioVentaFinalIva, 2, ',', '.');
                //         $descFinalF = number_format($descFinal, 0);
                //         $maxCant = "";
                //         $campoMax = "";
                //         if (isset($artFactura) && $artFactura != null) {
                //             $cantidad = $artFactura[$arti->IDArt]["cuanto"];
                //             $maxCant = "max='" . $cantidad . "'";
                //             $campoMax = "<input type='hidden'  id='mi-cant-factura{$arti->IDArt}' value='" . $cantidad . "'>";
                //         }
                //         //                $precioVenta = $precioVenta; 
                //         //                $precioNeto=$precioNeto;
                //         //$alicuota = format_number($arti->Alic);
                //         //print_r($arti);
                //         $tabla .= "<tr>";

                //         /* desde la lista catalogo*/
                //         $tabla .= '<td style="vertical-align: top;padding-top:3%">'
                //             //. '<a href="'.$srcArticulo.'" title="Ver detalle"> '                          

                //             . '<div class="fotoChica">'
                //             . $descFinalT
                //             . '<img src="foto.php?origen=foto1|' . $idArt . '&mini=2">'
                //             . '</div>'

                //             . '</td>';

                //         $tabla .= '<td ><div class="divArticuloLista"> '

                //             . '<div class="articuloNombre">'
                //             . '<a id="mi-art-nombre' . $arti->IDArt . '" rel="' . $arti->IDArt . '"'
                //             . 'title="' . $nombreArticulo . '" '
                //             . ' class="desc-articulo" >' . $nombreArticulo . '</a>'
                //             . '</div>';

                //         if ($precioNeto != $precioVentaFinal) {
                //             $tabla .= "<div class='precioListaOld'>$" . $precioVentaF . "</div>";
                //         }

                //         // precio con el descuento
                //         $tabla .= "<div class='precioLista' id='mi-art-precio{$arti->IDArt}'><label>$ " . $precioVentaFinalF . "</label>";

                //         $descModifica = "";


                //         /// descuentos
                //         if ($promo == 'No' || $promo == "no") {
                //             /*
                //                  * No tengo promocion
                //                  */
                //             if ($_SESSION['tipousuario'] == 'cliente') {
                //                 /*
                //                      * Soy un cliente y tengo descuento pero no puedo tocarlo
                //                      */
                //                 $tabla .= "<input type='hidden' id='mi-desc-{$arti->IDArt}' disabled value='{$descFinal}' style='width:50px;' />".PHP_EOL;
                //                 if ($descFinal != 0) {

                //                     $tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                //                 }
                //             } else {
                //                 /*
                //                      * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                //                      * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                //                      * pero depende del permiso si permito modificarlo.
                //                      */

                //                 if ($descFinal == 0) {
                //                     if ($modificaReng == "Si" && $limDescReng > 0) {

                //                         //$tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                //                         $limDescReng = str_replace(",", ".", $limDescReng);
                //                         //echo var_dump($re)
                //                         //$limDescReng=number_format($limDescReng,2,'.','');
                //                         $descModifica = "<br><span>Desc</span><input type='text'  inputmode='numeric' id='mi-desc-{$arti->IDArt}' value='0.00' alt='{$descFinal} - {$modificaReng} - {$limDescRengT}'  step='0.01'  min='0.00' max='{$limDescReng}' />".PHP_EOL;
                //                     } else {
                //                         $descModifica = "<input type='hidden' id='mi-desc-{$arti->IDArt}' value='0.00' alt='{$descFinal} - {$modificaReng}'  step='1.00'  min='0.00' max='{$limDescReng}' />".PHP_EOL;
                //                     }
                //                 } else {

                //                     //$tabla .= "<input type='number' id='mi-desc-{$arti->IDArt}' value='{$descFinal}' disabled min='0.00' max='{$descFinal}' style='width:50px;' />".PHP_EOL;
                //                     $tabla .= "<input type='hidden' id='mi-desc-{$arti->IDArt}' disabled value='{$descFinal}' style='width:50px;' />".PHP_EOL;
                //                     $tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                //                 }
                //             }
                //         }

                //         if ($promo == "si") {
                //             /*
                //                  * Hay promocion
                //                  */
                //             $tabla .= "<input type='hidden' id='mi-desc-{$arti->IDArt}' value='{$descFinal}' disabled style='width:50px;' />".PHP_EOL;


                //             if ($precios["tipoPromo"] == "Importe descuento") {
                //                 $tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                //             }
                //         }
                //         $tabla .= "</div>"
                //             . "<div class='precioLista'><label>$ " . $precioVentaFinalFiva . "</label><span>c/IVA</span>";
                //         $tabla .= "</div>"; // fin div de precio
                //         $tabla .= '<div class="descLista"><i class="fa fa-hashtag fa-lg fa-fw"></i>' . $idArtT . '</div>';
                //         if ($arti->CodigoMarca <> 1) {
                //             $tabla .= '<div class="descLista"><i class="fas fa-tag fa-lg fa-fw"></i>';
                //             $tabla .= '' . ucwords($arti->Marca) . '';
                //             $tabla .= '</div>';
                //         }
                //         $tabla .= $tagPromo;
                //         // $tabla .= '<div class="descLista"><i class="fa fa-cube fa-lg fa-fw"></i> '.$arti->NombreCategoria.' > '.$arti->NombRub.' > '.$arti->NombSubRub.'</div>';
                //         //$tabla .= '<br><span class="descLista"><i class="fa fa-cubes fa-lg"></i> '.$arti->NombSubRub.'</span>';


                //         if ($usaEmbalaje == "Si") {
                //             $tabla .= '<div class="descLista"><i class="fa fa-briefcase fa-lg fa-fw"></i> ' . $bulto . '</div>';
                //         }
                //         if ($verStock == "Si") {
                //             $tabla .= '<div class="descLista"><i class="fa fa-briefcase fa-lg fa-fw"></i> ' . $tStock . '</div>';
                //         }
                //         // cantidad

                //         $tabla .= $campoMax . "".PHP_EOL;

                //         $tabla .= "</div></td>".PHP_EOL;

                //         //            $tabla .= "<td class='importe {$clasePrecio}' title='Neto: $ {$precioNetoF} \nImpIva: $ {$importeIva}\nIva: {$arti->Alic}%' ><span id='mi-art-precio{$arti->IDArt}'>$".$precioVentaF."</span></td>".PHP_EOL;
                //         //            $tabla .= "<td {$clase}>".PHP_EOL;
                //         //            
                //         // 
                //         //            $tabla .= "</td>".PHP_EOL;
                //         //            
                //         //            $tabla .= "<td {$clase}>".PHP_EOL;


                //         $tabla .= "<td {$clase}>".PHP_EOL;
                //         $tabla .= "<div class='miCantidad'>"
                //             . "<span>Cant</span><br>"
                //             . "<input type='text' inputmode='numeric' id='mi-cantidad-{$arti->IDArt}' pattern='[0-9]+([,\.][0-9]+)?' value='{$cantidad}' min='1.00' step='0.00' {$maxCant}  />"
                //             . $descModifica
                //             . "<br>"
                //             . "<button class='botonNuevo mediano green tecompro' name='{$arti->IDArt}' ><i class='fa fa-plus fa-lg '   alt='agregar articulo' title='agregar articulo'></i></button>"
                //             . "</div>";
                //         //            $tabla .= "<div class='miCantidad'>"
                //         //                    . "<i class='fa fa-plus-circle fa-lg tecompro' name='{$arti->IDArt}'  alt='agregar articulo' title='agregar articulo'></i>"                    
                //         //                    . "</div>";


                //         //                . "<img src='_img/agregar_p1.png' class='tecompro' name='{$arti->IDArt}'  alt='agregar articulo' title='agregar articulo'/>";

                //         // voy a armar un objeto json que me va a facilitar el uso en javascript calculos y demas
                //         $jsonArt = (array) $arti;
                //         $jsonArt['importeIva'] = $importeIva;
                //         $jsonArt['importeInterno'] = $importeInterno;
                //         $jsonArt['precioNeto'] = $precioNeto;
                //         $jsonArt['promo'] = $promo;
                //         $jsonArt['promoCant'] = $promoCant;
                //         $jsonArt['descFinal'] = $descFinal;
                //         $jsonArt['cantBulto'] = $cantBulto;

                //         $tabla .= "<input type='hidden' id='mi-imp-iva{$arti->IDArt}' value='{$importeIva}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-imp-interno{$arti->IDArt}' value='{$importeInterno}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-imp-interno-tasa{$arti->IDArt}' value='{$arti->impuesto_interno}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-tipo-iva{$arti->IDArt}' value='{$arti->tipoIVA}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-alic-iva{$arti->IDArt}' value='{$arti->Alic}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-iva{$arti->IDArt}' value='{$arti->Alicuota}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-neto{$arti->IDArt}' value='{$precioNeto}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-promo{$arti->IDArt}' value='{$promo}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-promocant{$arti->IDArt}' value='{$promoCant}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-promoporc{$arti->IDArt}' value='{$descFinal}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-lote{$arti->IDArt}' value='{$arti->lote}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-saldo{$arti->IDArt}' value='{$arti->stockDisponible}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-ensamblado-vta{$arti->IDArt}' value='{$arti->ensamblado}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-promotipo{$arti->IDArt}' value='{$arti->promocion_tipo}'/>".PHP_EOL;
                //         $tabla .= "<input type='hidden' id='mi-json{$arti->IDArt}' value='" . json_encode($jsonArt) . "'/>".PHP_EOL;

                //         // usando el permiso activo que pueda usar multiplicador x bulto.
                //         $tabla .= "<input type='hidden' id='mi-bulto{$arti->IDArt}' value='{$cantBulto}'/>".PHP_EOL;

                //         $tabla .= "</td>".PHP_EOL;
                //         $tabla .= "</tr>".PHP_EOL;
                //     }
                //     $tabla .= "</tbody>".PHP_EOL;
                //     echo $tabla;
                $html = $this->armarHTMLMovilProductosLista();
                echo $html;
            } // fin soy moviel
        } else {
            //            $tabla = "<tr><td class='vacio'>No se encontaron resultados </td></tr>";
            //            echo $tabla;
            echo '0';
        }
    }

    /**
     * Funcion Calcula Precios
     * @objCliente := cliente para calcular precios
     * @objArticulo:= el objeto resultado con los articulos
     * return precios =array() con precio neto, precio final sin descuento,
     * precio con descuento y el descuento aplicado x lo que sea.
     */
    // private function calculaPrecios($arti = null, $listaPrecioCliente = null, $descRenglon = null, $usaReglaPrecio = null, $codCliente = null)
    public function calculaPrecios($param)
    {
        // calculo de precios, seteo inicial}
        if (gettype($param) == 'object') {
            $arti = $param->arti;
            $listaPrecioCliente = $param->listaPrecioCliente;
            $descRenglon = $param->descRenglon;
            $usaReglaPrecio = $param->usaReglaPrecio;
            $codCliente = $param->codCliente;
        } else {
            $arti = $param['arti'];
            $listaPrecioCliente = $param['listaPrecioCliente'];
            $descRenglon = $param['descRenglon'];
            $usaReglaPrecio = $param['usaReglaPrecio'];
            $codCliente = $param['codCliente'];
        }
        // * funcionalidad Unidad, display o bulto.
        $divisorPrecio = 1;
        // analizo si unidad = display y el precio esta como unidado o display 

        $nombreArticulo = "";
        // echo 'listaprecioCliente=>{'.$listaPrecioCliente.'}';
        // echo "soy un numero lista de precio?=>",var_dump(is_int($listaPrecioCliente)) , " entonces que soy? =>",var_dump($listaPrecioCliente);
        switch ($listaPrecioCliente) {
            case 'Lista 1':
                $precioNeto     = $arti->Precio1V;
                $importeIva     = $arti->impIva1;
                $importeInterno = $arti->imp_interno1;
                $precioVenta    = $arti->Precio1VI;
                if ($arti->promocion_lista1 == "Si") {
                    $promoLista = "si";
                } else {
                    $promoLista = "no";
                }
                break;
            case 'Lista 2':
                $precioNeto     = $arti->Precio2V;
                $importeIva     = $arti->impIva2;
                $importeInterno = $arti->imp_interno2;
                $precioVenta    = $arti->Precio2VI;
                if ($arti->promocion_lista2 == "Si") {
                    $promoLista = "si";
                } else {
                    $promoLista = "no";
                }
                break;
            case 'Lista 3':
                $precioNeto     = $arti->Precio3V;
                $importeIva     = $arti->impIva3;
                $importeInterno = $arti->imp_interno3;
                $precioVenta    = $arti->Precio3VI;
                //                        echo "<pre> ".$arti->IDArt." - ".$arti->promocion_lista3."</pre>";
                if ($arti->promocion_lista3 == "Si") {
                    $promoLista =   "si";
                } else {
                    $promoLista =   "no";
                }
                break;
            case 'Lista 4':
                $precioNeto     = $arti->Precio4V;
                $importeIva     = $arti->impIva4;
                $importeInterno = $arti->imp_interno4;
                $precioVenta    = $arti->Precio4VI;
                if ($arti->promocion_lista4 == "Si") {
                    $promoLista = "si";
                } else {
                    $promoLista = "no";
                }
                break;
            case 'Lista 5':
                $precioNeto     = $arti->Precio5V;
                $importeIva     = $arti->impIva5;
                $importeInterno = $arti->imp_interno5;
                $precioVenta    = $arti->Precio5VI;
                if ($arti->promocion_lista5 == "Si") {
                    $promoLista = "si";
                } else {
                    $promoLista = "no";
                }
                break;
            case 'Lista Oficial':
                $precioNeto     = $arti->PNOficial;
                $importeIva     = $arti->impOf;
                $importeInterno = $arti->imp_internoOF;
                $precioVenta    = $arti->PFOficial;
                $promoLista     = "si";

                break;
        }

        /*
     * REGLAS DE PRECIO primero y si hay reglas no se hacen descuentos
     * las reglas se buscan solo si hay permiso de reglas.
     * 
     */
        //    echo "articulo precio:{<pre>";
        //    print_r($arti);
        //    echo "}</pre>";

        $precioVentaFinal = $precioVenta;
        $precioNetoCalc = $precioNeto;

        $descFinal      = 0;
        $clase          = "";
        $clasePrecio    = "";

        $promoCant      = "";
        $promoPorc      = "";
        $promoTipo      = $arti->promocion_tipo;
        $promo          = "no";

        $aplicaPromo    = "no";
        $aplicoRegla    = "no";
        $cualRegla      = "";
        $desc           = "si";
        $usoPromocion   = "Si";
        $encontreRegla  = 0;
        /*
     * Si no hay cliente seleccionado no hace el descuento.
     */
        if ($usaReglaPrecio == "Si") {
            /* Variables de Reglas 
         * ======================
         */
            $idArtR = $arti->IDArt;
            $codigoRubroR = $arti->CodigoRubro;
            $idSubRubroR = $arti->IDSubRubro;
            $codigoProveedorR = $arti->CodigoProveedor;
            $codClienteR = $codCliente;

            /* Reglas Particulares
         * ===================
         */

            if (property_exists($arti, 'tipo_calculo') && $arti->tipo_calculo != null) {
                // regla plarticular
                //echo "particular";
                $hayRegla = "Si";
                $usoPromocion   = "No";
                $encontreRegla++;
                $tipoCalculo = $arti->tipo_calculo;
                $importeRegla = $arti->importe_regla;
            }
            /* Reglas Masivas
         * ======================== 
         */
            if ($encontreRegla == 0) {
                //echo "regla MAsivas";
                // ir a buscar la funcion que recupera si hay alguna regla masiva.
                $idReglaMasiva = $this->reglasPrecioMasivas($idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR, $codClienteR);
                // echo "hay regla masiva=?:{<pre>";
                // echo $idArtR."},{".$codigoProveedorR."},{".$codigoRubroR."},{".$idSubRubroR."},{".$codClienteR;
                //             echo "}<Br>id regla=:{";
                //             var_dump($idReglaMasiva);
                //             echo "}</pre>";
                if ($idReglaMasiva != null) {
                    // hay regla masiva

                    $sqlReglaM = "SELECT * FROM reglas_precio_masivas "
                        . "WHERE id_regla_precio_masivas ={$idReglaMasiva} ";
                    $hacerRM = mysqli_query($this->connV, $sqlReglaM) or die("No puedo recuperar la Regla masiva encontrada " . mysqli_error($this->connV) . "<pre>" . $sqlReglaM . "</pre>");
                    $rm = mysqli_fetch_assoc($hacerRM);
                    //                echo "<pre>";
                    //                print_r($rm);
                    //                echo "</pre>";
                    $hayRegla = "Si";
                    $encontreRegla++;
                    $tipoCalculo = $rm["tipo_calculo"];
                    $importeRegla = $rm["importe_regla"];
                }
            }
            /*
         * Reglas Generales
         * =====================================================================
         */

            if ($encontreRegla == 0) {
                //echo "reglas generales";
                // ir a buscar la funcion que recupera si hay alguna regla General.
                $idReglaGeneral = $this->reglasPrecioGeneral($idArtR, $codigoProveedorR, $codigoRubroR, $idSubRubroR);
                if ($idReglaGeneral != null) {
                    // hay regla general

                    $sqlReglaG = "SELECT * FROM reglas_precio_alta_art "
                        . "WHERE id_regla_precio_alta_art = {$idReglaGeneral}";
                    $hacerRG = mysqli_query($this->connV, $sqlReglaG) or die("No puedo recuperar la Regla general encontrada " . mysqli_error($this->connV) . "<pre>" . $sqlReglaG . "</pre>");
                    $rg = mysqli_fetch_assoc($hacerRG);
                    //                echo "regka general<pre>";
                    //                print_r($rg);
                    //                echo "</pre>";
                    $hayRegla = "Si";
                    $encontreRegla++;
                    $tipoCalculo = $rg["tipo_calculo"];
                    $importeRegla = $rg["importe_regla"];
                    $prioridad_regla = $rg["prioridad_regla"];
                }
            }


            /** encontre alguna Regla y la tengo que usar.*/
            if ($encontreRegla != 0) {
                //            echo "<pre>encontre regla</pre>";
                $usoPromocion = "No";
                $aplicoRegla = "si";
                //            $usoPromocion
                // vemos el tipo de regla si Descuento - Marcacion o Precio Fijo
                //    echo "<pre> ImpoPromo=>";
                //    print_r($importeRegla).PHP_EOL;
                //    echo "<BR>Tipoc=>";
                //    print_r($tipoCalculo).PHP_EOL;
                //    print_r($prioridad_regla).PHP_EOL;
                //    echo "</PRE>";
                switch ($tipoCalculo) {
                    case "Descuento":
                        //cargo descuento
                        // analizo la prioridad de la regla del cliente

                        if (isset($prioridad_regla) && $prioridad_regla != "Desc. Cliente") {
                            // aplico el descuento de menor valor.

                            $descRenglon = $importeRegla;
                        } else {
                            // prioridad descuento de cliente
                            // echo 'descuento regla a vre que hay',$descRenglon,$importeRegla.PHP_EOL;
                            // aplico el descuento de menor valor.
                            if ($descRenglon < $importeRegla) {

                                $descRenglon = $importeRegla;
                            }
                        }


                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;

                        $importeIva         = ($precioNetoCalc  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoCalc * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoCalc + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc
                            + (($precioNetoCalc  * $arti->Alic) / 100)
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = $descRenglon;
                        $promoCant          = "";
                        //                            $promoPorc          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        //    echo "<pre>";
                        //    var_dump($descRenglon).PHP_EOL;
                        //    var_dump($precioNetoNuevo).PHP_EOL;
                        //    var_dump($descRenglonCalc).PHP_EOL;
                        //    var_dump($precioNetoCalc).PHP_EOL;
                        //    var_dump($importeIva).PHP_EOL;
                        //    var_dump($importeInterno).PHP_EOL;
                        //    var_dump($precioVenta).PHP_EOL;
                        //    echo "</pre>";
                        break;
                    case "Marcacion":
                        $descRenglon = $importeRegla;
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo + $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoCalc;
                        $precioVentaFinal = $precioNetoCalc
                            + (($precioNetoCalc  * $arti->Alic) / 100)
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = 0;
                        $promoCant          = "";
                        //                 $promoPorc          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        $descFinal = 0;
                        $precioVenta = $precioVentaFinal;
                        //                    echo "<pre>";
                        //                    echo "arti{".$arti->IDArt."}<Br>";
                        //                    echo "descRenglon:=>{".var_dump($descRenglon)."}<br>----";
                        //                    echo "precioNetoNuevo:=>{".var_dump($precioNetoNuevo)."}<br>----";
                        //                    echo "descRenglonCalc:=>{".var_dump($descRenglonCalc)."}<br>---";
                        //                    echo "precioNetoCalc:=>{".var_dump($precioNetoCalc)."}<br>----";
                        //                    echo "importeIva:=>{".var_dump($importeIva)."}<br>----";
                        //                    echo "importeInterno:=>{".var_dump($importeInterno)."}<br>---";
                        //                    echo "precioVenta:=>{".var_dump($precioVenta)."}<br>----";
                        //                     echo "precioVentaFinal:=>{".var_dump($precioVentaFinal)."}+++++<br>";
                        //                    echo "</pre>";

                        // hago el aumento pero no muestro descuento
                        break;
                    case "Precio Fijo":
                        $descuento = $importeRegla;
                        $precioNetoNuevo    = $descuento;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc
                            + (($precioNetoCalc  * $arti->Alic) / 100)
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                        $descFinal = $descRenglon;
                        $promoCant          = "";
                        //                            $promoPorc          = "";
                        $promo              = "no";
                        $cantidad           =   1;
                        $descFinal = 0;
                        //reemplazo el neto x este nuevo y cero descuento
                        break;

                    case "Cantidad - Unidad":

                        $descRenglon = 0;
                        $precioNetoNuevo    = $precioNeto;
                        $descRenglonCalc    = ($descRenglon * $precioNeto / 100);
                        $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                        $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                        $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                        $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                        $precioNeto         = $precioNetoNuevo;
                        $precioVentaFinal = $precioNetoCalc
                            + (($precioNetoCalc  * $arti->Alic) / 100)
                            + ($precioNetoCalc * ($arti->impuesto_interno / 100));


                        $sqlPCant = "SELECT rp.promocion_por, rp.promocion_cant "
                            . " FROM reglas_precio AS rp"
                            . " WHERE rp.id_articulo ={$idArtR}  AND "
                            . "rp.tipo_calculo = 'Cantidad - Unidad' AND "
                            . "rp.id_cliente ={$codClienteR} ";

                        $hacerPcant = mysqli_query($this->connV, $sqlPCant) or die("No puedo recuperar la promocion cantidad de las reglas " . mysqli_error($this->connV) . "<pre>" . $sqlPCant . "</pre>");
                        $arrPcant = mysqli_fetch_assoc($hacerPcant);

                        // print_r($arrPcant);
                        $promoCant = $arrPcant["promocion_cant"];
                        $promo      = "si";
                        $descFinal = $arrPcant["promocion_por"];
                        $cantidad   = number_format($promoCant);
                        break;
                }
                $cualRegla = $tipoCalculo;
            } else {
                // no hay reglas ni una entonces reviso promociones
                $usoPromocion = "Si";
            }
        }
        /* NO HAY REGLA o no utiliza regla de precios.
        * USO PROMOCION
        * ============================================
        */
        // echo "usoPromocion {".$usoPromocion."}";
        if ($usoPromocion == "Si") {

            /*
            * Articulo en promocion
            * =========================================================
            * coloco los datos de la promocion para saber si se aplica y que descuentos
            * la promocion se aplica cuando se compra la cantidad, 
            * Si la promocion es por intervalo, puede que tenga la vigencia solo para el.
            * **/
            // echo "arti-promocion {".$arti->promocion."} y promolista=>{".$promoLista."}";
            if ($arti->promocion == 'Si' && $promoLista == "si") {
                /*
                * Hay promocion cargada
                */
                // echo 'soy el arti de caclula precios cant<pre>',print_r($arti->promocion_cant),'</pre>';
                $promoCant = $arti->promocion_cant;
                $promoPorc = $arti->promocion_por;
                $promoTipo = $arti->promocion_tipo;
                $aplicaPromo = "no";
                /*
                * Evaluo si la promocion que podria aplicar tiene un porcentaje
                * que sea mayor al descuento del renglon del cliente, si no
                * dejo el descuento del cliente. 
                */

                /* PROMOCION PERIODO  PARA TODAS LAS PROMOS EXCEPTO CANT-INTERVALO */
                /*===============================================================
                *  */
                $hayVigencia = $this->vigencia_promo($arti->promocion_vigencia_desde, $arti->promocion_vigencia_hasta, $arti->IDArt, $promoTipo);

                // echo "y la vigencia? {" . $hayVigencia . "}";

                if ($hayVigencia == "si") {
                    $aplicaPromo = "si";
                }

                // calculo promociones.
                if ($aplicaPromo == "si") {
                    switch ($promoTipo) {
                        case 'Cantidad - Intervalo':
                            // no hago nada porque ni siquiera se si esta vigente.
                            //                            echo "adentro cantidad intervalo";
                            $promo = "si";
                            $descFinal = 0;
                            $cantidad = 1;

                            break;
                        case 'Importe descuento':
                            if ($descRenglon > $promoPorc) {
                                /*
                                 * el descuento x renglon es mayor que la promocion
                                 * la desactivo
                                 */
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
                                /*
                                 * el descuento x renglon es menor uso la promocion
                                 */
                                $descFinal = $promoPorc;
                                $promo = "si";
                            }

                            $precioNetoNuevo = $precioNeto;
                            $precioNetoCalc = $precioNeto - ($precioNeto * $descFinal / 100);

                            $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                            $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                            $precioNeto = $precioNetoNuevo;
                            $promoCant = "";
                            $cantidad = 1;



                            break;
                        case 'Cantidad':
                            // % descuento por la compra de X unidades
                            if ($descRenglon > $promoPorc) {
                                /*
                                 * el descuento x renglon es mayor que la promocion
                                 * la desactivo
                                 */
                                $descFinal = $descRenglon;
                                $promo = "no";
                            } else {
                                /*
                                 * el descuento x renglon es menor uso la promocion
                                 */
                                $descFinal = $promoPorc;
                                $promo = "si";
                            }
                            // echo 'dentro de promo canntidad.::<pre>',print_r($promoCant),'</pre>';
                            $cantidad = $promoCant;
                            // echo 'dentro de cantidad canntidad.::<pre>',print_r($cantidad),'</pre>';
                            // echo 'dentro de promo descuento.::<pre>',print_r($descFinal),'</pre>';
                            break;
                        case 'Cantidad - Unidad':

                            // 2 x 1 gratis
                            $promo = "si";
                            // cantidad Gratis
                            $descFinal = $promoPorc;
                            // cantidad a comprar
                            $cantidad = $promoCant;

                            break;
                        case "Monto fijo":
                            // monto fijo 
                            // precio final con impuestos , desglosar impuesto solo iva Monto fijo en promocon por esta el valor final.
                            $promo = "si";
                            // $precioVenta = $promoPorc;
                            //$precioVentaFinal= $promoPorc;
                            $precioNetoNuevo = $precioNeto;

                            // $precioNetoNuevo = round($promoPorc/ (1 +($arti->Alic /100)),4);



                            $precioNetoCalc = round($promoPorc / (1 + ($arti->Alic / 100)), 4);
                            // echo 'Monto fijo:<pre>',$promoPorc,PHP_EOL,$arti->Alic,PHP_EOL,$precioNeto,PHP_EOL,$precioNetoCalc,PHP_EOL,'</pre>';
                            $descFinal = round((($precioNeto - $precioNetoCalc) * 100 / $precioNeto), 1);
                            $importeIva = ($precioNetoNuevo * $arti->Alic) / 100;
                            $importeInterno = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
                            $precioVentaFinal = $promoPorc;
                            $precioNeto = $precioNetoNuevo;
                            $promoCant = "";
                            $cantidad = 1;
                            break;
                    }
                }

                // articulo en promocion pero fuera de intervalo
                // aplico descuento del cliente
                if ($aplicaPromo == "no" && $descRenglon > 0) {
                    //                    echo "hay descuento del cliente no promocion".$aplicaPromo." des c".$descRenglon;
                    /*
                         * el descuento 
                         * la desactivo
                         */
                    $descFinal = $descRenglon;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descFinal * $precioNeto / 100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioVentaFinal = $precioNetoCalc
                        + (($precioNetoCalc  * $arti->Alic) / 100)
                        + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                    $precioNeto         = $precioNetoNuevo;
                    //$precioNetoCalc     = $precioNeto;
                    $promoCant          = "";
                    //                                $promoPorc          = "";
                    $promo              = "no";
                    $cantidad           = 1;

                    $descFinal = $descRenglon;
                }

                if($aplicaPromo =="no"){
                    $promoTipo="";
                    $promo="no";
                }
            }




            /* ARTICULOS SIN PROMOCION
            // SIN PRoMOCION DESCUENTO X CLIENTE
            * ==================================================================
           */
            if ($arti->promocion == 'No' || $promoLista == "no") {
                               // echo "hay articulo sin promocion";
                // sin promocion en la lista pero revisar si hay promocion en intervalo.
                $cantidad = 1;
                $promo = "no";
                if ($promoLista == "si" && $promoTipo == 'Cantidad - Intervalo') {
                    $hayVigencia = $this->vigencia_promo($arti->promocion_vigencia_desde, $arti->promocion_vigencia_hasta, $arti->IDArt, $promoTipo);

                    // echo "y la vigencia? {" . $hayVigencia . "}";

                    if ($hayVigencia == "si") {
                        $promo = "si";
                        $descFinal = 0;
                        $cantidad = 1;
                    }
                }

                // SIN PRoMOCION DESCUENTO X CLIENTE



                /*
                * No existe promocion asi que evaluo si aplico el descuento
                * x renglon del articulo.
                */
                if ($descRenglon > 0 && $promoTipo != 'Cantidad - Intervalo') {
                    /*
                    * Debo recalcular el precio de acuerdo al descuento
                    */

                    $descFinal = $descRenglon;
                    $precioNetoNuevo    = $precioNeto;
                    $descRenglonCalc    = ($descFinal * $precioNeto / 100);
                    $precioNetoCalc     = $precioNetoNuevo - $descRenglonCalc;
                    $importeIva         = ($precioNetoNuevo  * $arti->Alic) / 100;
                    $importeInterno     = $precioNetoNuevo * ($arti->impuesto_interno / 100);
                    $precioVenta        = $precioNetoNuevo + $importeIva + $importeInterno;
                    $precioNeto         = $precioNetoNuevo;
                    $precioVentaFinal = $precioNetoCalc
                        + (($precioNetoCalc  * $arti->Alic) / 100)
                        + ($precioNetoCalc * ($arti->impuesto_interno / 100));
                }
            }
        }

        # forzar calculo de iva
        if ($precioNeto == $precioVenta || $precioNetoCalc == $precioVentaFinal) {
            // echo "Adentro del precios<pre>";
            // echo var_dump($precioNeto, $precioVenta, $precioNetoCalc, $precioVentaFinal);
            // echo "</pre>";
            // viene mal el precio.
            $alicuotaIva = $arti->Alic;
            $neto = $precioNeto;
            $netoFinal = $precioNetoCalc;
            $iva = ($precioNeto * $alicuotaIva) / 100;
            $ivaFinal = ($precioNetoCalc * $alicuotaIva) / 100;
            $precioIva = $neto + $iva;
            $precioIvaFinal = $netoFinal + $ivaFinal;
            // reasigno valores 
            $precioVenta = $precioIva;
            $precioVentaFinal = $precioIvaFinal;
            $importeIva = $iva;
        }

        // * calculo de impuesto interno si hay que mostrarlo.
        if (isset($_SESSION['usa_impuesto_interno_abm']) && $_SESSION['usa_impuesto_interno_abm'] == "Si") {
            // calcular el imppuesto interno mostrarlo para formar el precio pero no editar el precio final. o neto.
            // echo 'entre en impuesto interno tipo de promocion ::'.$promoTipo.PHP_EOL;
            $impInterno = 0;
            $descuentoCalculo =0;
            if ($arti->interno_descripcion != null) {
                // todo calcular el impuesto interno.
                $arrInterno = array();
                $arrInterno['cantidad'] = 1;
                $arrInterno['neto'] = $precioNeto;
                $arrInterno['costo'] = $arti->PrecioCosto;
                $arrInterno['descripcion'] = $arti->interno_descripcion;
                $arrInterno['tipo'] = $arti->interno_tipo;
                $arrInterno['porcentaje'] = $arti->interno_porcentaje;
                $arrInterno['montoFijo'] = $arti->interno_monto_fijo;
                $arrInterno['pesoCalculado'] = $arti->interno_peso_calculado;
                $arrInterno['pagoMinimo'] = $arti->interno_pago_minimo;
                $arrInterno['idUnimed'] = $arti->interno_id_unimed;

                $impInterno = $this->calcularImpuestoInterno($arrInterno); // funcion calcula nuevo impuesto interno
            }
            // si soy promocion 2x 1 no debo recalcular por mas que viene el descuento con un valor.
            if($promoTipo!='Cantidad - Unidad'){
            $descuentoCalculo = $descFinal;

            }

            // echo 'dentro del impuesto interno.'.$_SESSION['usa_impuesto_interno_abm'];
            $precioNetoNuevo = $precioNeto;
            $precioNetoCalc = $precioNeto - ($precioNeto * $descuentoCalculo / 100);

            $importeIvaViejo = ($precioNetoNuevo * $arti->Alic) / 100;
            $importeIva = ($precioNetoCalc * $arti->Alic) / 100;
            $importeInterno = $impInterno;
            $precioVenta = $precioNetoNuevo + $importeIva + $importeInterno;
            $precioVentaFinal = $precioNetoCalc + (($precioNetoCalc * $arti->Alic) / 100) + ($importeInterno);
            $precioNeto = $precioNetoNuevo;
            // $promoCant = "";
            // $cantidad = 1;
        }


        if (isset($idCliente) && $idCliente == 1) {
            $precioNeto = 0;
            $precioNetoCalc = 0;
            $precioVenta = 0;
            $descFinal = 0;
            $precioVentaFinal = 0;
        }

        // calculo de impuesto interno Nuevo
        // hacer el calculo y pasar los precios finales de todo si existiera.
        // * calculo de impuesto interno Nuevo.



        $precios = array(
            "idart"     => $arti->IDArt,
            "neto"          => $precioNeto,
            "netoCalc"      => $precioNetoCalc,
            "precioVenta"   => $precioVenta,
            "descuento"     => $descFinal,
            "precioFinal"   => $precioVentaFinal,
            "promoNombre"   => $nombreArticulo,
            "clase"         => $clase,
            "clasePrecio"   => $clasePrecio,
            "importeIva"    => $importeIva,
            "importeInterno"    => $importeInterno,
            "promo"          => $promo,
            "descCli"       => $descRenglon,
            "montoDescuento" => $precioNeto - $precioNetoCalc,
            "cantidad" => round($cantidad, 0),
            "promoTipo"   => $promoTipo,
            "usoRegla"    => $aplicoRegla,
            "queRegla"    => $cualRegla,
            "importeIvaViejo"    => $importeIvaViejo,
            "ivaAlic"       => $arti->Alic,
            "impIvaFinal"  => $precioVentaFinal - $precioNetoCalc
        );

        return $precios;
    }


    /** 
     ** #Calcula el impuesto interno Nuevo calculo con Abm.
     * @param $arrParametros: array()
     * @return double importe del impuesto calculado.
     */

    private function calcularImpuestoInterno($arrParametros)
    {
        /*
         ' Calculo del tipo de impuesto interno

        ' Porcentaje

        ' Porcentaje - Minimo

        ' Monto fijo

        ' Peso

        ' Peso - Monto fijo

       

        ' Porcentaje

        If rs_consulta.Fields!tipo_impuesto_interno = "Porcentaje" Then

            Calculo_Nuevo_Impuesto_Interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

        End If

       

        ' Porcentaje - Minimo

        If rs_consulta.Fields!tipo_impuesto_interno = "Porcentaje - Minimo" Then

            monto_imp_interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

            If monto_imp_interno < rs_consulta.Fields!pago_minimo Then

                Calculo_Nuevo_Impuesto_Interno = rs_consulta.Fields!pago_minimo

            Else

                Calculo_Nuevo_Impuesto_Interno = monto_neto_calculo * rs_consulta.Fields!porcentaje / 100

            End If

        End If

       

        ' Monto fijo

        If rs_consulta.Fields!tipo_impuesto_interno = "Monto fijo" Then

            Calculo_Nuevo_Impuesto_Interno = rs_consulta.Fields!monto_fijo * Cantidad

        End If
         */
        // echo 'parametros funcion impuesto interno.<pre>'.PHP_EOL;
        // print_r($arrParametros);

        $valorImpuesto = 0;
        // Porcentaje 
        if ($arrParametros['tipo'] == 'Porcentaje') {
            $valorImpuesto = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
        }
        // Porcentaje Minimo
        if ($arrParametros['tipo'] == 'Porcentaje - Minimo') {
            $montoCalculado = (($arrParametros['cantidad'] * $arrParametros['costo']) * $arrParametros['porcentaje']) / 100;
            if ($montoCalculado < $arrParametros['montoMinimo']) {
                $valorImpuesto = $arrParametros['montoMinimo'];
            }
            if ($montoCalculado >= $arrParametros['montoMinimo']) {
                $valorImpuesto = $montoCalculado;
            }
        }

        // Monto Fijo
        if ($arrParametros['tipo'] == 'Monto fijo') {
            $valorImpuesto = $arrParametros['cantidad'] * $arrParametros['montoFijo'];
        }
        // Peso 
        // Peso - Monto fijo
        //  echo 'vuelta del impuesto',$valorImpuesto;
        return $valorImpuesto;
    }


    private function reglasPrecioMasivas($idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null, $codCliente = null)
    {

        /*  ''''''''''''''''
    'Reglas Masivas'
    ''''''''''''''''
 */

        $varR = null;
        $fecha = date("Y-m-d");

        //    'Existen reglas de alta
        $sqlRegla = "SELECT * FROM reglas_precio_masivas WHERE Anulado = 'No'";
        $hacer = mysqli_query($this->connV, $sqlRegla) or die('No puedo recuperar reglas de precio ' . mysqli_error($this->connV) . ' <pre>' . $sqlRegla . '</pre>');
        $arrReglas = array();
        while ($rr = mysqli_fetch_assoc($hacer)) {
            $arrReglas[] = $rr;
        }

        if (empty($arrReglas)) {
            // sin reglas masivas me vuelvo si o si
            return $varR;
        }

        // no tengo cliente asi que le asigno el cliente consumidor final. por defecto
        if ($codCliente == null) {
            $codCliente = 1;
        }

        //    '''''''''''
        //    '#Vigencia'
        //    '''''''''''
        //    fechita = Split(CStr(Principal.Fecha), "/")
        //    Fecha3 = fechita(2) & "-" & fechita(1) & "-" & fechita(0)

        //    'Inicializo
        //    $VarR = 0;

        //    'Obtengo datos del articulo
        //    rs_r.Open "SELECT CodigoProveedor, CodigoRubro, IDSubRubro FROM articulo " & _
        //              "WHERE articulo.IDART = " & IDArt & " ", conn, adOpenDynamic, adLockOptimistic
        //
        //    If Not IsNull(rs_r.Fields!CodigoProveedor) Then
        //        CodProv = rs_r.Fields!CodigoProveedor
        //    End If
        //    
        //     If Not IsNull(rs_r.Fields!CodigoRubro) Then
        //        CodRubro = rs_r.Fields!CodigoRubro
        //    End If
        //    
        //    If Not IsNull(rs_r.Fields!IDSubRubro) Then
        //        IDSubRubro = rs_r.Fields!IDSubRubro
        //    End If
        //    
        //    rs_r.Close

        /*''''''''''''
//' X CLIENTE'
//''''''''''''*/

        if ($codigoProveedor != null) {

            //        '5Rubro
            //        ================================
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente = {$codCliente} AND "
                . "rpm.id_rubro = {$codigoRubro} AND "
                . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-Rubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del rubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }

            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

            //        '4SubRubro
            //        =================================================              
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente = {$codCliente} AND "
                . "rpm.id_sub_rubro = {$idSubRubro} AND "
                . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro) LIMIT 1 ";
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-SubRubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del Subrubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }

            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

            //        '3Proveedor
            //         ========================================================================             
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente = {$codCliente} AND "
                . "rpm.id_proveedor ={$codigoProveedor} AND "
                . "ISNULL(rpm.id_rubro) AND ISNULL(rpm.id_sub_rubro)";

            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }

            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

            //        '2Proveedor Rubro
            //          ========================================================================  
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente = {$codCliente} AND "
                . "rpm.id_proveedor ={$codigoProveedor} AND "
                . "rpm.id_rubro ={$codigoRubro}  AND "
                . "ISNULL(rpm.id_sub_rubro)";
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor-Rubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del cliente proveedor rubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }

            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

            //        '1Proveedor SubRubro
            //        =====================================================================
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente ={$codCliente}  AND "
                . "rpm.id_proveedor ={$codigoProveedor}  AND "
                . "rpm.id_sub_rubro ={$idSubRubro} AND "
                . "ISNULL(rpm.id_rubro)";
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-Proveedor-SubRubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del cliente proveedor Subrubro devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }
            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

        } else {

            //        '5Rubro
            //        ============================================================================    
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente ={$codCliente} AND "
                . "rpm.id_rubro ={$codigoRubro} AND "
                . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_sub_rubro)";
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-Rubro Sin Proveedor" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del cliente rubro sin proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }
            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close

            //        '4SubRubro
            //        ==========================================================================    
            //            rs_r.Open "SELECT id_regla_precio_masivas " & _
            //                      "FROM reglas_precio_masivas " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "reglas_precio_masivas.id_cliente = " & CodigoCliente & " AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpm.id_regla_precio_masivas "
                . "FROM reglas_precio_masivas AS rpm "
                . "WHERE rpm.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpm.vigencia_desde  AND rpm.vigencia_hasta AND "
                . "rpm.id_cliente ={$codCliente}  AND "
                . "rpm.id_sub_rubro ={$idSubRubro} AND "
                . "ISNULL(rpm.id_proveedor) AND ISNULL(rpm.id_rubro)";

            //            If rs_r.RecordCount = 1 Then
            //                VarR = rs_r.Fields!id_regla_precio_masivas
            //            End If
            //
            //            rs_r.Close
            $hacerRpm = mysqli_query($this->connV, $sqlRegla) or die("no pude buscar regla Cliente-SubRubro Sin Proveedor" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . "</pre>");
            $arrRrubro = array();
            while ($rrubro = mysqli_fetch_assoc($hacerRpm)) {
                $arrRrubro = $rrubro;
            }
            if (!empty($arrRrubro)) {
                // habia regla del cliente subrubro sin proveedor devuelvo la regla.
                $varR = $arrRrubro["id_regla_precio_masivas"];
                //                return $varR;
            }
        }

        /*'Resultado'''''''
    RPrecioM = VarR '
    '''''''''''''''''*/
        return $varR;
    }

    private function reglasPrecioGeneral($idArt = null, $codigoProveedor = null, $codigoRubro = null, $idSubRubro = null)
    {
        //    ''''''''''''''''''''''''''''
        //    'Reglas Masivas - Generales'
        //    ''''''''''''''''''''''''''''    
        $varR = null;
        $fecha = date("Y-m-d");

        //    'Existen reglas de alta
        //    Dim rs_r As New ADODB.Recordset
        //    rs_r.Open "SELECT * FROM reglas_precio_alta_art WHERE Anulado = 'No' ", conn, adOpenDynamic, adLockOptimistic
        //
        //    If rs_r.RecordCount = 0 Then
        //        rs_r.Close
        //        Exit Function
        //    End If
        //
        //    rs_r.Close
        $sqlG = "SELECT id_regla_precio_alta_art FROM reglas_precio_alta_art WHERE Anulado='No' LIMIT 1";
        $hacerG = mysqli_query($this->connV, $sqlG) or die("No puedo recuper reglas precio alta art " . mysqli_error($this->connV) . "<PRE>" . $sqlG . "</PRE>");
        $hayR = mysqli_fetch_array($hacerG);
        if (empty($hayR)) {
            // no hay reglas asi que me vuelvo sin nada
            return $varR;
        }


        //          '''''''''''''''
        //          ' No X CLIENTE'
        //          '''''''''''''''

        if ($codigoProveedor != null) {

            //        '5Rubro
            //        ======================================================    
            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_sub_rubro) ", conn, adOpenDynamic, adLockOptimistic
            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_rubro = {$codigoRubro} AND "
                . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";

            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Rubro " . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo por Proveedo y Rubro.
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }


            //        '4SubRubro
            //        '==========================================================    
            //
            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_rubro) LIMIT 1 ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_sub_rubro = {$idSubRubro} AND "
                . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro) LIMIT 1 ";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por SubRubro " . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo por Proveedo y Subrubro.
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }


            //        '3Proveedor
            //        ==========================================================================

            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "isnull(id_rubro) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_proveedor = {$codigoProveedor} AND "
                . "ISNULL(rpma.id_rubro) AND ISNULL(rpma.id_sub_rubro)";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor " . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo por Proveedo .
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }


            //        '2Proveedor Rubro
            //        ===========================================================================    
            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_proveedor = {$codigoProveedor} AND "
                . "rpma.id_rubro = {$codigoRubro} AND "
                . "isnull(rpma.id_sub_rubro)  ";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y Rubro juntos" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo por Proveedor y rubro juntos .
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }
            //        '1Proveedor SubRubro
            //        =======================================================================

            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_proveedor = " & CodProv & " AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_proveedor = {$codigoProveedor} AND "
                . "rpma.id_sub_rubro = {$idSubRubro} AND "
                . "ISNULL(rpma.id_rubro) ";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art por Proveedor y SubRubro juntos" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo por Proveedor y Subrubro juntos .
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }
        } else {

            //        '5Rubro
            //        =========================================================================    
            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_rubro = " & CodRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_sub_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art  AS rpma"
                . " WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_rubro ={$codigoRubro} AND "
                . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_sub_rubro)";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por Rubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo Sin Proveedor y por Rubro .
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }

            //        '4SubRubro
            //        ===========================================================================

            //            rs_r.Open "SELECT id_regla_precio_alta_art " & _
            //                      "FROM reglas_precio_alta_art " & _
            //                      "WHERE Anulado = 'No' AND " & _
            //                      " '" & Fecha3 & "' BETWEEN vigencia_desde  AND vigencia_hasta AND " & _
            //                      "id_sub_rubro = " & IDSubRubro & " AND " & _
            //                      "isnull(id_proveedor) AND isnull(id_rubro)  ", conn, adOpenDynamic, adLockOptimistic

            $sqlRegla = "SELECT rpma.id_regla_precio_alta_art "
                . "FROM reglas_precio_alta_art AS rpma "
                . "WHERE rpma.Anulado = 'No' AND "
                . " '{$fecha}' BETWEEN rpma.vigencia_desde  AND rpma.vigencia_hasta AND "
                . "rpma.id_sub_rubro ={$idSubRubro} AND "
                . "ISNULL(rpma.id_proveedor) AND ISNULL(rpma.id_rubro)";
            $hacerR = mysqli_query($this->connV, $sqlRegla) or die("No puedo recuperar la lista precio alta art SIN Proveedor y por SubRubro" . mysqli_error($this->connV) . "<pre>" . $sqlRegla . " </pre>");
            $arrRegla = array();
            while ($ff = mysqli_fetch_assoc($hacerR)) {
                $arrRegla = $ff;
            }
            if (!empty($arrRegla)) {
                // hay regla general de articulo Sin Proveedor y por Rubro .
                $varR = $arrRegla["id_regla_precio_alta_art"];
                //                return $varR;
            }
        }

        //    'Resultado'''''''
        //    RPrecioG = VarR '
        //    '''''''''''''''''    
        return $varR;
    }

    /*
     * Funcion para mostrar los rubros.
     */
    public function muestra_rubros($idTipoCliente = null)
    {
        if (!isset($_REQUEST['ajax'])) {
            if (!$idTipoCliente) {
                $sqlRubro = "SELECT rubro.*,cat.nombre_categoria AS categoria "
                    . "FROM rubro "
                    . "LEFT JOIN rubro_categoria AS cat ON cat.id_categoria=rubro.id_categoria "
                    . "WHERE rubro.anulado='No' "
                    . " "
                    . "ORDER BY NombreRubro";
                $hacerRubro = mysqli_query($this->connV, $sqlRubro) or die('No puedo recuperar el rubro' . mysqli_error($this->connV));
                while ($rubro =  mysqli_fetch_object($hacerRubro)) {
                    echo "<option value='" . $rubro->CodigoRubro . "'>" . $rubro->categoria . " | " . $rubro->NombreRubro . "</option>";
                }
            } else {
                // traigo los rubros del tipo de cliente nada mas. 

            }
        }
    }
    public function muestra_categorias()
    {
        if (!isset($_REQUEST['ajax'])) {
            $cateNo = "";
            if (isset($_SESSION["categoriaNo"])) {
                $cateNo = " AND cat.id_categoria NOT IN(" . join(",", $_SESSION["categoriaNo"]) . ")";
            }
            $sqlRubro = "SELECT "
                . "cat.id_categoria,"
                . "cat.nombre_categoria AS categoria "
                . "FROM rubro_categoria AS cat "
                . "WHERE cat.anulado='No' "
                . " {$cateNo} "
                . "  "
                . "ORDER BY categoria ASC";
            $hacerRubro = mysqli_query($this->connV, $sqlRubro) or die('No puedo recuperar la categoria' . mysqli_error($this->connV) . '<pre>' . $sqlRubro . '</pre>');
            while ($rubro =  mysqli_fetch_object($hacerRubro)) {
                echo "<option value='" . $rubro->id_categoria . "'>" . $rubro->categoria . "</option>";
            }
        }
    }
    public function muestra_marcas()
    {
        
            $sqlMarca = "SELECT marca.* "
                . "FROM articulo "
                . " LEFT JOIN marca ON marca.CodMarca=articulo.CodigoMarca"
                . " WHERE "
                . " articulo.tipo_art='Articulo'"
                . " AND articulo.Discontinuo='No'"                
                . " AND marca.anulado='No' "
                . " GROUP BY articulo.CodigoMarca"
                . " ORDER BY NombreMarca";
            $hacerMarca = mysqli_query($this->connV, $sqlMarca) or die('No puedo recuperar el rubro' . mysqli_error($this->connV));

            while ($marca =  mysqli_fetch_object($hacerMarca)) {
                echo "<option value='" . $marca->CodMarca . "'>" . $marca->NombreMarca . "</option>";
            }
        
    }

    public function muestra_proveedor()
    {
        
            $sqlProveeddor = "SELECT proveedor.Codigo, proveedor.Nombre ";
            $sqlProveeddor .= "FROM articulo ";
            $sqlProveeddor .= " LEFT JOIN proveedor ON proveedor.Codigo=articulo.CodigoProveedor";
            $sqlProveeddor .= " WHERE ";
            $sqlProveeddor .= " articulo.tipo_art='Articulo'";
            $sqlProveeddor .= " AND articulo.Discontinuo='No'";           
            $sqlProveeddor .= " GROUP BY articulo.CodigoProveedor";
            $sqlProveeddor .= " ORDER BY proveedor.Nombre";
            // echo '<pre>',$sqlProveeddor,'</pre>';
            $hacerProveedor = mysqli_query($this->connV, $sqlProveeddor) or die('No puedo recuperar el proveedor' . mysqli_error($this->connV));
           
            while ($proveedor =  mysqli_fetch_object($hacerProveedor)) {
                echo "<option value='" . $proveedor->CodigoProveedor . "'>" . $proveedor->Nombre . "</option>";
            }
        
    }
    /*
 * Analiza la vigencia de las promos y evuelvo si esta dentro o no..
 */

    public function vigencia_promo($desde, $hasta, $idArt, $promoTipo)
    {

        // hay un rango valido 
        //    echo "y las vigencias???<br>";
        //    echo var_dump($desde);
        //    echo "<br>";
        //    echo var_dump($hasta);
        //    echo "<pre>";
        $vigencia = "no";


        // if ($promoTipo == 'Cantidad - Intervalo') {
        //     // debo buscar si hay intervalos con vigencia
        //     $hoy = date('Y-m-d');

        //     //$db=mysqli_select_db($base, $link);
        //     //require_once 'sesion.inc.php';
        //     $sqlIntervalo = "SELECT pint.* "
        //         . "FROM articulo_promo_intervalo AS pint "
        //         . "WHERE "
        //         . "pint.id_articulo = {$idArt} And pint.anulado = 'No'"
        //         . " AND '" . $hoy . "' BETWEEN pint.vigencia_desde AND pint.vigencia_hasta ORDER BY pint.desde_cantidad ASC";
        //     $hacerInt = mysqli_query($this->connV, $sqlIntervalo);
        //     if ($hacerInt) {
        //         $hayRegistro = mysqli_num_rows($hacerInt);
        //         if ($hayRegistro > 0) {
        //             $vigencia = "si";
        //         }
        //     }
        // }

        // la vigencia la traigo del articulo
        // if ($promoTipo != 'Cantidad - Intervalo') {
            if ($desde !== null && $hasta !== null) {
                $fd     = explode('-', $desde);
                $fh     = explode('-', $hasta);

                if ($fh[0] > 2038) {
                    $fh[0] = 2037;
                }
                //                            $desde  = mktime(0, 0, 0, $fd[1], $fd[2], $fd[0]);
                //                            $hasta  = mktime(0, 0, 0, $fh[1], $fh[2], $fh[0]);
                $desde = new DateTime($desde);
                $hasta = new DateTime($hasta);


                $hoy = new DateTime(date('Y-m-d'));
                //        echo "y las nueasvas???<br><pre>";
                //        echo var_dump($desde);
                //        echo "<br>";
                //        echo var_dump($hasta);
                //        echo "<br>";
                //        echo var_dump($hoy);
                //        echo "<br>";
                //        echo var_dump($hoy>=$desde && $hoy<=$hasta);
                //        echo "</pre>";
                // VIGENTE
                if ($hoy >= $desde && $hoy <= $hasta) {
                    //echo "no entras";
                    $vigencia = "si";
                }
            }
            // vigencia infinita
            if ($desde == null && $hasta == null) {
                $vigencia = "si";
            }

            // inicio infinito pero con fin
            if ($desde == null && $hasta !== null) {

                $fh     = explode('-', $hasta);

                if ($fh[0] > 2038) {
                    $fh[0] = 2037;
                }

                $hasta = new DateTime($hasta);

                $hoy = new DateTime(date('Y-m-d'));

                if ($hoy <= $hasta) {

                    $vigencia = "si";
                }
            }

            // inicio desde peor sin fin o fin nulo
            if ($desde !== null && $hasta == null) {

                $fd     = explode('-', $desde);
                $desde = new DateTime($desde);
                $hoy = new DateTime(date('Y-m-d'));

                if ($hoy >= $desde) {
                    $vigencia = "si";
                }
            }
        // }

        return $vigencia;
    }

    private function detalle_promo($tipoPromo, $descuento, $cantidad, $idArt,$reducido=null,$bultoPromedio=null,$tipoUnidad=null,$precios=null)
    {
        $detalle = "";
        $textoUnidad="unidades";
        $textoCantidad=round($cantidad, 0);
        // echo 'precios destalle_promo <pre>',print_r($precios).'</pre>';
    //    $arrDetallePromo = func_num_args();
        // echo 'args destalle_promo <pre>',print_r($arrDetallePromo).'</pre>';

//         Array
// (
//     [idart] =&gt; 244
//     [neto] =&gt; 4835.5392
//     [netoCalc] =&gt; 4593.76224
//     [precioVenta] =&gt; 5800.2292704
//     [descuento] =&gt; 5.00
//     [precioFinal] =&gt; 5558.4523104
//     [promoNombre] =&gt; 
//     [clase] =&gt; 
//     [clasePrecio] =&gt; 
//     [importeIva] =&gt; 964.6900704
//     [importeInterno] =&gt; 0
//     [promo] =&gt; si
//     [descCli] =&gt; 0.00
//     [montoDescuento] =&gt; 241.77696
//     [cantidad] =&gt; 14
//     [promoTipo] =&gt; Cantidad
//     [usoRegla] =&gt; no
//     [queRegla] =&gt; 
//     [importeIvaViejo] =&gt; 1015.463232
//     [ivaAlic] =&gt; 21.00
//     [impIvaFinal] =&gt; 964.6900704
// )


        // soy bulto promedio.
        if($bultoPromedio && $tipoUnidad){
            $textoUnidadBulto = $tipoUnidad;
            $textoCantidadUnidadBulto = round($cantidad,1,PHP_ROUND_HALF_UP);
            $textoUnidad="unidades";
            $cantidadCalculada = round($cantidad/$bultoPromedio,2,PHP_ROUND_HALF_UP);
            // $textoCantidad=round($cantidad/$bultoPromedio,2,PHP_ROUND_HALF_UP) . " - ".round($cantidad/$bultoPromedio,2,PHP_ROUND_HALF_UP)*round($bultoPromedio,1,PHP_ROUND_HALF_UP);  ;
            $cantidadCalculadaFinal = $this->ajusteRedondeoBulto($cantidad,$cantidadCalculada,$bultoPromedio);
            // $textoCantidad=round($cantidad/$bultoPromedio,2,PHP_ROUND_HALF_UP) . " - ".$cantidadCalculadaFinal; 
            $textoCantidad = $cantidadCalculadaFinal ;

        }

        switch ($tipoPromo) {
            case 'Cantidad - Intervalo':
                if(!$reducido){
                    $detalle = $this->detalle_promo_intervalo($idArt);
                }
                if($reducido){
                    $detalle = $this->detalle_promo_intervalo($idArt,1,$bultoPromedio,$tipoUnidad,$precios);
                }
                break;
            case 'Monto fijo':
                    $precioMontoDescuentoFinal = '$'.number_format($precios['montoDescuento'],2,',','.');
                    $precioTextoFinal ='$'.number_format($precios['precioFinal'],2,',','.');
                    $detalle = '<i class="fas fa-gift"></i> Monto: <strong>'.$precioMontoDescuentoFinal . ' OFF</strong> (<strong>'.round($descuento, 1) . '% OFF)</strong>, precio final: <strong>'.$precioTextoFinal.'</strong>';
                    break;       
            case 'Importe descuento':
                $precioTextoFinal ='$'.number_format($precios['precioFinal'],2,',','.');
                $detalle = '<i class="fas fa-gift"></i> <strong>'.round($descuento, 1) . '% OFF</strong>, precio final: <strong>'.$precioTextoFinal.'</strong>';
                break;
            case 'Cantidad':
                $precioTextoFinal ='$'.number_format($precios['precioFinal'],2,',','.');
                if($bultoPromedio && $tipoUnidad){
                    $detalle = '<i class="fas fa-gift"></i> Llevando <strong>' . $textoCantidadUnidadBulto . '</strong> '.$textoUnidadBulto.' (<strong>'.$textoCantidad.' un</strong>), <strong>' . round($descuento, 1) . '% OFF</strong>, precio final: <strong>'.$precioTextoFinal.'</strong>';
                }else{
                    $detalle = '<i class="fas fa-gift"></i> Llevando <strong>' . $textoCantidad . '</strong> '.$textoUnidad.', <strong>' . round($descuento, 1) . '% OFF</strong>, precio final: <strong>'.$precioTextoFinal.'</strong>';
                }

                break;
            case 'Cantidad - Unidad':
                
                $detalle = '<i class="fas fa-gift"></i> Llevando <strong>' . $textoCantidad . '</strong> '.$textoUnidad.', ' . round($descuento, 0) . ' gratis <strong>(' . $textoCantidad . ' x ' . round($descuento, 0) . ')</strong>';
                break;
        }
        return $detalle;
    }

    private function detalle_promo_intervalo($idArt,$reducido=null,$bultoPromedio=null,$tipoUnidad=null,$precios=null)
    {
        // $base = $_SESSION["baseConecto"];
        // $servidor = $_SESSION["servidor"];
                //     echo 'detalle_promo intervalo  <pre>',print_r($precios),'</pre>';
                //    print_r($_SESSION);
                //    echo"</pre>";
        $hoy = date('Y-m-d');
        $link =$this->connV;
        //$db=mysqli_select_db($base, $link);
        //require_once 'sesion.inc.php';
        $sqlIntervalo = "SELECT pint.* "
            . " FROM articulo_promo_intervalo AS pint "
            . " WHERE "
            . " pint.id_articulo = {$idArt} And pint.anulado = 'No'"
            //. " AND '" . $hoy . "' BETWEEN pint.vigencia_desde AND pint.vigencia_hasta "
            . " ORDER BY pint.desde_cantidad ASC";
        $hacerInt = mysqli_query($link, $sqlIntervalo) or die("no pude recuperar la promocion." . mysqli_error($link) . "<pre>" . $sqlIntervalo . "</pre>");

            //    echo "<pre>";
            //    print_r($sqlIntervalo);
            //    echo "</pre>";
        $detalle = "<span>Llevando <br>";
        $detalleChico ="";
        $arrIntervalos=array();
        // precios para recalculo.
        if($precios!=null){
        
            $precioNeto = $precios['neto'];
            $precioAlicuotaIva = $precios['ivaAlic'];
            $precioImporteInterno= $precios['importeInterno'];
            $precioFinal = $precios['precioVenta'];
        }

        while ($pi = mysqli_fetch_assoc($hacerInt)) {
            
                
                $textoPrecio ="";
                if($precios!=null){
                    $precioDescuento= $pi["monto_descuento"];
                    $precioNetoCalculado = $precioNeto - ($precioNeto*$precioDescuento /100);
                    $precioIva = ($precioNetoCalculado * $precioAlicuotaIva /100);
                    $precioFinalPromocion = $precioNetoCalculado + $precioImporteInterno +$precioIva;
                    $precioNetoPromocion = $precioNetoCalculado;
                    $textoPrecio ="$". number_format($precioFinalPromocion, 2, ',', '.');
                }
            // $arrayIntervalo[] = "<span><strong>" . round($pi["monto_descuento"], 0) . "% OFF</strong> por la compra de <strong>" . round($pi["desde_cantidad"], 0) . '</strong> a <strong>' . round($pi["hasta_cantidad"], 0) . '</strong> unidades, </span> ';
            if($bultoPromedio!=null&&$tipoUnidad!=null){
                $cantidadUnidad = round($pi['desde_cantidad']/$bultoPromedio,1);
                $cantidadBulto= round($pi['desde_cantidad'],1);
                // $cantidadHastaTexto = round($pi['hasta_cantidad']/$bultoPromedio,0);
                $tipoUnidadBulto = $tipoUnidad;
                $tipoUnidadText ="un";
                $detalle .= '<div class="linea-promo-intervalo"><strong>' . round($pi["monto_descuento"], 1) . '% OFF</strong> llevando <strong>' . round($pi["desde_cantidad"], 0) . '</strong> '.$tipoUnidadBulto.' ('.$cantidadUnidad.' '.$tipoUnidadText.') o más , precio final: <strong>'.$textoPrecio.'</strong></div>';
                $arrayIntervalo[] = '<div class="linea-promo-intervalo"><i class="fas fa-gift"></i> <strong>' . round($pi["monto_descuento"], 0) . '% OFF</strong> llevando <strong>' . round($pi["desde_cantidad"], 0) . ' '.$tipoUnidadBulto.'</strong> (<strong>'.$cantidadUnidad.' '.$tipoUnidadText.'</strong>) o más, precio final: <strong>'.$textoPrecio.'</strong></div> ';
            }else{
                $detalle .= "  " . round($pi["desde_cantidad"], 0) . ' a ' . round($pi["hasta_cantidad"], 0) . ' un, ' . round($pi["monto_descuento"], 0) . '% OFF  precio final: <strong>'.$textoPrecio.'</strong></span><br>';

                $arrayIntervalo[] = '<div><i class="fas fa-gift"></i> <strong>' . round($pi["monto_descuento"], 0) . '% OFF</strong> llevando <strong>' . round($pi["desde_cantidad"], 0) . '</strong> unidades o más, precio final: <strong>'.$textoPrecio.'</strong></div> ';
            }

        }
        //mysqli_close($link);
        if(!$reducido){
            $vuelta = $detalle;
        }
        if($reducido==1){
            $vuelta=$detalleChico .join('',$arrayIntervalo);
        }
        return $vuelta;
    }



    // * funcion que arma html tipo lista reducido  mas productos por pagina sin foto con link (MOBIL)

    private function armarHTMLMovilProductosLista()
    {
        global $objVendedor;
        // datos del cliente
        if (is_object($_SESSION['cliente'])) {
            $objCliente = $_SESSION['cliente'];
        } else {
            $objCliente = $_SESSION['cliente'][0];
        }



        $usaIdManual = $_SESSION["usa_id_manual"];
        $listaPrecioCliente = $objCliente->listaPrecio;
        // es el descuento por cli del cilente
        $descRenglon = $objCliente->descRenglon;

        $codCliente = $objCliente->Codigo;
        $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
               
        $usaDisplay = 'No';
        $usaBultoCerrado = 'No';
        $controlVenta=0;
       
        $ventaSinStock ="Si";
        $comoValidoSaldo ='stock';       
        $verDisponible = 'Si';
        $verStock = 'Si';  

        //REMITO de FACTURAS SIN STOCK
        $artFactura = null;

        if (isset($_SESSION["sel_factura"])) {
            $artFactura = $_SESSION["sel_factura"]["art"];
        }
        $limDescReng    = 0;
        $modificaReng = 'No';
        if (isset($objVendedor) && is_object($objVendedor)) {
            $limDescReng    = $objVendedor->lim_desc_renglon;
            $modificaReng = $objVendedor->mod_descuento_renglon;
        }
        // echo '<pre>';
        // print_r($objVendedor);
        // echo '</pre>';

        $precioNeto = 0;
        $importeIva = 0;
        $importeInterno = 0;
        $precioVenta = 0;
        /*
             * EMBALAJE
             */
        $caminoDispo = $_SESSION["caminoDisp"];
        if ($_SESSION["utilizaEmbalaje"] == "Si") {
            $usaEmbalaje = "Si";
        } else {
            $usaEmbalaje = "No";
        }

        /* Calcula Cantidad X bultos 
             * =========================
             * Si el campo preso promedio bulto, esta con valores y el permiso
             * esta configurado, se multiplica la cantidad ingresada a comprar 
             * por este valor, luego se transmite la cantidad ingresada como
             * url o detalle para saber cuantos bultos reales se compraron,.
             *              
             */
        $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
        $queFormulario = $_SESSION['formulario'];


        /*
            * IVA INCLUIDO
            */
        $ivaIncluido = $_SESSION["ivaIncluido"];
        //            $cantidad = $_REQUEST['cantidadOferta'];
        //con el tema de la oferta dejo una idea a desarrollar, pasar el id
        //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
        //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
        //            borro todo y coloco un uno
        $cantidad = 1;
       
        // oferta
        if (isset($_REQUEST['cantidadOferta'])) {
            $cantidad = $_REQUEST['cantidadOferta'];
        }

        if (isset($_SESSION['utiliza_display'])) {
            $usaDisplay = $_SESSION['utiliza_display'];
        }
        if (isset($_SESSION['utiliza_bulto_cerrado'])) {
            $usaBultoCerrado = $_SESSION['utiliza_bulto_cerrado'];
        }

        // validar si puede vender si stock
        if(isset($_SESSION['venta_sin_stock'])){
            $ventaSinStock=$_SESSION['venta_sin_stock'];
        }
        // como voy a controlar el saldo por stock o disponible.
        if(isset($_SESSION['comoValidoSaldo'])){
            $comoValidoSaldo=$_SESSION['comoValidoSaldo'];
        }
        // ver stock
        if(isset($_SESSION['verStock'])){
            $verStock=$_SESSION['verStock'];
        }

        // ver disponible
        if(isset($_SESSION['verDisponible'])){
            $verDisponible=$_SESSION['verDisponible'];
        }

        // *  inicio cabecera de los productos.
        $tabla = '';
        $tabla .=  "<thead>" . PHP_EOL;
        $tabla .=  "<tr>" . PHP_EOL;
        // $tabla .=  "<th>&nbsp;</th>".PHP_EOL;
        $tabla .=  "<th>producto</th>" . PHP_EOL;
        // $tabla .=  "<th>precio</th>".PHP_EOL;
        // $tabla .=  "<th>cantidad</th>".PHP_EOL;
        // $tabla .=  "<th></th>".PHP_EOL;

        $tabla .=  "</tr>" . PHP_EOL;
        $tabla .=  "</thead>" . PHP_EOL;
        $tabla .=  "<tbody>" . PHP_EOL;

        // * recorrido de los productos

        foreach ($this->colArticulos as $arti) {
            $renglon = '';
            // remito de cantidad fija.
            $descFinalT = '';
            //                $promo = $arti->promocion;
            if ($usaIdManual == "Si") {
                $idArtT = $arti->id_manual;
            } else {
                $idArtT = $arti->IDArt;
            }

            $idArt = $arti->IDArt;
            $nombreArticulo = $arti->NombreArticulo;
            // * lista de precios standar
            $paramPrecios['arti'] = $arti;
            $paramPrecios['listaPrecioCliente'] = $listaPrecioCliente;
            $paramPrecios['descRenglon'] = $descRenglon;
            $paramPrecios['usaReglaPrecio'] = $usaReglaPrecio;
            $paramPrecios['codCliente'] = $codCliente;
            $precios = $this->calculaPrecios($paramPrecios);
            // echo 'PAramPRecios<pre>',print_r($paramPrecios),'</pre>',PHP_EOL;
            // * datos de display y bulto vienene desde la sesion.
            // bulto cerrado 
            // if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
            //     $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
            // }

            // // display 
            // if ($_SESSION['utiliza_display'] == "Si") {
            //     $usaDisplay = $_SESSION['utiliza_display'];
            // }
            // si ambos permisos estan habilitados, mustro botones y armo la busqueda de valores. po lista precio.
            // * si tengo los permisos, debo recuperar precios para display.

            // * si tengo los permisos debo recuparar precios para bulto.

            // echo "<pre>";
            ////                print_r($arti);
            // echo "Precios<pre>";
            // print_r($arti);
            // print_r($precios);
            // echo "</pre>",PHP_EOL;
            /*
     * EMBALAJE
     */
            $bulto = "";
            if ($usaEmbalaje == "Si") {
                // tengo que hacer la busqueda de los valores para mostrar
                $bulto = $arti->nombre_presentacion . " x " . $arti->cantidad_uni;
                if ($arti->nombre_unimed != "") {
                    $bulto .= " (" . $arti->nombre_unimed . ")";
                }
                //                    $bulto = $arti->nombre_presentacion ." x ".$arti->cantidad_uni."(".$arti->nombre_unimed.")";

            }

            //$hoy3 = $hoy; 
            //                formateo los precios a cuatro decimales..just in case...
            $clase = $precios["clase"];
            //            $nombreArticulo .=$precios["promoNombre"];
            $clasePrecio = "";
            //            $clasePrecio = $precios["clasePrecio"];
            $cantidad = 1;
            if ($precios["cantidad"] > 1) {
                // echo 'soy una promocion por cantidad';
                // promocion por cantidad, tiene que hacer la division de kilos porque la cntidad es por kilos.
                if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                    // echo 'soy una promocion por cantidad 2';
                    // $cantidad = round(($precios["cantidad"] / $arti->cantidad_promedio_bulto),2);
                    $cantidad = $this->ajusteRedondeoBulto($precios['cantidad'],round($precios["cantidad"] / $arti->cantidad_promedio_bulto,2,PHP_ROUND_HALF_UP),$arti->cantidad_promedio_bulto);
                }else{
                    $cantidad = $precios["cantidad"];
                }
                
            }
            $maxCant = "";
            // facturas remitos
            if (isset($artFactura) && $artFactura != null) {
                $cantidad = $artFactura[$arti->IDArt]["cuanto"];
                $maxCant = "max='" . $cantidad . "'";
            }

            // consumos
            if (property_exists($arti, 'CantidadProm')) {
                $cantidad = $arti->CantidadProm;
            }

            // STOCK - DISPONIBLE
            $tStock = "";
            $tDisponible = "";
            $tPresentacion = ""; // muestra el divisor proedio
            if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                // $tStock .= " Stock: <strong>" . number_format(($arti->saldo / $arti->cantidad_promedio_bulto), 2, ',', '');
                // $tStock .= "<strong>" . number_format(($arti->stockDisponible / $arti->cantidad_promedio_bulto), 2, ',', '') . "</strong>";
                $tStock .= number_format(($arti->saldo / $arti->cantidad_promedio_bulto), 2, ',', '');
                $tDisponible .= number_format(($arti->stockDisponible / $arti->cantidad_promedio_bulto), 2, ',', '');
                $cantBulto = $arti->cantidad_promedio_bulto;
                // $tStock .= ", Pres: (" . $arti->cantidad_promedio_bulto . " " . $arti->uniArt . ")";
                // $tDisponible .= ", Pres: (" . $arti->cantidad_promedio_bulto . " " . $arti->uniArt . ")";
                $tPresentacion .= number_format($arti->cantidad_promedio_bulto, 1) . " " . $arti->uniArt;
            } else {
                // $tStock .= "Stock: <strong>" . $arti->saldo."</strong>";
                $tStock .= $arti->saldo;
                $tDisponible .= $arti->stockDisponible;

                $cantBulto = 1;
            }





            // PROMOCIONES ANALISIS
            $promoCant = $precios["cantidad"];
            $promo = $precios["promo"];
            $tagPromo = "";

            if ($precios["promo"] == "si" || ($precios["promo"]=="no" && $precios["promoTipo"]=="Cantidad - Intervalo")) {
                // $tagPromo = '<div class="descuento-verde"><i class="fa fa-gift fa-lg fa-fw"></i>' . $precios['descuento'] . '% OFF</div>';
                // * promocion por intervalo por bulto
                if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                    $tagPromo .= '<div class="descuento-verde">Promoción<br> ' . $this->detalle_promo($precios["promoTipo"], $precios["descuento"], $precios["cantidad"], $precios["idart"],1,$arti->cantidad_promedio_bulto,$arti->uniArt,$precios) . "</div>";

                }else{

                    $tagPromo .= '<div class="descuento-verde">Promoción<br> ' . $this->detalle_promo($precios["promoTipo"], $precios["descuento"], $precios["cantidad"], $precios["idart"],1,null,null,$precios) . "</div>";
                }
            }

            $importeInterno = $precios["importeInterno"];

            $importeIva = $precios["importeIva"];

            // echo var_dump($ivaIncluido);
            if ($ivaIncluido == 'no') {
                $precioNeto = $precios["neto"];
                $precioVenta = $precios["precioVenta"];

                $precioVentaFinal = $precios["netoCalc"];
                $precioVentaFinalIva = $precios["precioFinal"]; // precio
            } else {
                $precioNeto = $precios["neto"];
                $precioVenta = $precios["precioVenta"];
                $precioVentaFinal = $precios["netoCalc"];
                $precioVentaFinalIva = $precios["precioFinal"];
            }

            $descFinal = $precios["descuento"];

            $precioVentaF = number_format($precioVenta, 2, ',', '.');
            $precioNetoF = number_format($precioNeto, 2, ',', '.');
            $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
            $precioVentaFinalFiva = number_format($precioVentaFinalIva, 2, ',', '.');
            // neto menos descuento y sin iva.
            $precioNetoCalc = $precios['netoCalc'];
            $precioNetoCalcF = number_format($precioNetoCalc, 2, ',', '.');
            $importeInternoF = number_format($importeInterno, 2, ',', '.');
            $descFinalF = number_format($descFinal, 2);
            $montoDescuento = $precios['montoDescuento'];
            $montoDescuentoFormato = number_format($montoDescuento, 2, ',', '.');
            $campoMax = "";

            if (isset($artFactura) && $artFactura != null) {
                $cantidad = $artFactura[$idArt]["cuanto"];
                $maxCant = "max='" . $cantidad . "'";
                $campoMax = "<input type='hidden'  id='mi-cant-factura{$idArt}' value='" . $cantidad . "'>";
            }
            //                $precioVenta = $precioVenta; 
            //                $precioNeto=$precioNeto;
            //$alicuota = format_number($arti->Alic);
            //print_r($arti);
            // RENGLON INICIAL 
            $renglon .= "<tr>";

            /* desde la lista catalogo*/
            // $tabla .= '<td style="vertical-align: top;padding-top:3%">'
            //     //. '<a href="'.$srcArticulo.'" title="Ver detalle"> '                          

            //     . '<div class="fotoChica">'
            //     . $descFinalT
            //     . '<img src="foto.php?origen=foto1|' . $idArt . '&mini=2">'
            //     . '</div>'

            //     . '</td>';

            // TD CON NOMBRES Y DETALLES DE PRODUCTOS
            $renglon .= '<td >';
            $renglon .= "    <div class='contenedor'>";
            $renglon .= "        <div class='producto'>";

            $renglon .= '           <div class="producto-foto ver-mas" id="mi-art' . $idArt . '" rel="' . $idArt . '" producto-nombre="' . $nombreArticulo . '">';
            //$renglon .= '         	<div class="desc-articulo ver-mas" rel="' . $idArt . '" ><i class="fas fa-info-circle fa-lg "></i></div>' . PHP_EOL;
            $renglon .= '               <div class="producto-imagen"><img src="foto.php?origen=foto1|' . $idArt . '&mini=2" onload="this.classList.add(\'loaded\')"></div>' . PHP_EOL;
			$renglon .= '           </div>';

			$renglon .= '           <div class="producto-nombre ver-mas" id="mi-art' . $idArt . '" rel="' . $idArt . '" producto-nombre="' . $nombreArticulo . '">';
			$renglon .= '               <div class="titulo"><i class="fas fa-info-circle fa-lg "></i> ' . PHP_EOL;

            $renglon .=                 	$nombreArticulo;
			$renglon .= '           	</div>';
            $renglon .= $tagPromo;
            $renglon .= '           </div>';
            // $renglon .='<br>';


            // linea con el detalle 
            $renglon .= '<span class="producto-detalle">' . PHP_EOL;

            
            $renglon .= '<span>Cod: <strong>' . $idArtT . '</strong></span>';
            // linea con stock
           
                // verifico vista stock
                if($verStock == "Si"){
                    $renglon .= '<span>Saldo: <strong class="cantidad-stock">' . $tStock . '</strong></span>';
                }
                // verifico disponible
                if($verDisponible=="Si"){
                    $renglon .= '<span>Dispo: <strong class="cantidad-stock">' . $tDisponible . '</strong></span>';
                }
                
                
                if ($tPresentacion != "") {
                    $renglon .= '<span>Pres: <strong class="cantidad-stock">' . $tPresentacion . '</strong></span>';
                }
            
            // $renglon .= 'Cod: <strong>' . $idArtT . '</strong> ';
            // promocion y descuento Manejo

            $descModifica = "";


            /// descuentos
            if ($promo == 'No' || $promo == "no") {
                /*
                     * No tengo promocion
                     */
                if ($_SESSION['tipousuario'] == 'cliente') {
                    /*
                         * Soy un cliente y tengo descuento pero no puedo tocarlo
                         */
                    $descModifica .= "<input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  />" . PHP_EOL;
                    if ($descFinal != 0) {

                        $renglon .= "<span class='descuento-verde'>{$descFinalF}% OFF</span>" . PHP_EOL;
                    }
                }

                if ($_SESSION['tipousuario'] == 'vendedor') {
                    /*
                         * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                         * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                         * pero depende del permiso si permito modificarlo.
                         */

                    if ($descFinal == 0) {
                        if ($modificaReng == "Si" && $limDescReng > 0) {

                            //$tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                            $limDescReng = str_replace(",", ".", $limDescReng);
                            //echo var_dump($re)
                            //$limDescReng=number_format($limDescReng,2,'.','');
                            $descModifica .= "<label for='mi-desc-{$idArt}'>Descuento: <input type='text' class='input-descuento'  inputmode='numeric' id='mi-desc-{$arti->IDArt}'  value='' placeholder='0%' alt='{$descFinal} - {$modificaReng} - {$limDescReng}'  step='1'  min='0' max='{$limDescReng}' /></label>" . PHP_EOL;
                        }

                        // if ($modificaReng != "Si" || $limDescReng <= 0) {
                        //     $descModifica = "<label for='mi-desc-{$arti->IDArt}'>Desc:</label>: <input type='text' class='input-descuento' id='mi-desc-{$arti->IDArt}' value='0' alt='{$descFinal} - {$modificaReng}'  step='1'  min='0' max='{$limDescReng}' />".PHP_EOL;
                        // }
                    }

                    if ($descFinal != 0) {

                        //$tabla .= "<input type='number' id='mi-desc-{$arti->IDArt}' value='{$descFinal}' disabled min='0.00' max='{$descFinal}' style='width:50px;' />".PHP_EOL;
                        $descModifica .= "<label for='mi-desc-{$idArt}'>Descuento <input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  ></label>" . PHP_EOL;
                        $renglon .= "  <span class='descuento-verde'>{$descFinalF}% OFF</span>" . PHP_EOL;
                    }
                }
            }

            if ($promo == "si") {
                /*  Hay promocion       */
                $descModifica .= "<input type='hidden' id='mi-desc-{$idArt}' value='{$descFinal}' disabled style='width:50px;' />" . PHP_EOL;
            }
            // $descModifica .= "<label for='mi-desc-{$idArt}'>Desc: <input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  ></label>" . PHP_EOL;
            $renglon .= $campoMax . "" . PHP_EOL;

            $renglon .= '</span>'; // fin span de detalle.
            // lista de precios del cliente
            $renglon .= '<span class="mi-lista-precio"><input type="hidden" id="mi-lista-precio-'.$idArt.'" name="mi-lista-precio-'.$idArt.'" value="'.$listaPrecioCliente.'" ></span>';

            
            // PRECIOS UNA  LINEA
            // $renglon .="    </div>"; //div de nombre. 
            // * presentacion unidad display bulto
            if ($usaDisplay != 'No' || $usaBultoCerrado != 'No') {
                $renglon .= $this->htmlUnidadDisplayBulto($arti);
            }
            $renglon .= "    <div class='precios'>";
            // averiguo si hay descuento. 
            // hay descuento 
            // echo var_dump($montoDescuento);
            $tituloFinal="";
            $renglon .= "        <p><span class='precio-nombre'>Neto</span><br><span class='precio-neto' id='precio-neto-texto-" . $idArt . "'>$" . $precioNetoF . "</span></p>";
            if ($montoDescuento != 0) {
                // $renglon .="<p><span class='precio-nombre'>{$descFinalF}% OFF</span> <span class='precio-descuento'>$".$montoDescuentoFormato."</span></p>";                
                $renglon .= "        <p><span class='precio-nombre'>Final</span><br><span class='precio-neto' id='precio-final-texto-anterior-" . $idArt . "'>$" . $precioVentaF . "</span></p>";
                $renglon .= "        <p><span class='precio-nombre precio-nombre-descuento'>Neto desc<br></span> <span class='precio-descuento' id='precio-descuento-texto-" . $idArt . "'>$" . $precioNetoCalcF . "</span></p>";
                // $tituloFinal = " <span class='precio-descuento'>c/desc</span>";
            }

            // impuesto interno si no tengo valor no lo pongo.
            if($importeInterno!=0){
                $renglon .= "        <p><span class='precio-nombre'>Imp Int</span><br><span class='impuesto-interno' id='impuesto-interno-texto-" . $idArt . "'>$" . $importeInternoF . "</span></p>";
            }

           
            $renglon .= "            <p><span class='precio-nombre'>Final (" . round($arti->Alic, 0) . "%)".$tituloFinal."</span><br><span class='precio-final' id='precio-final-texto-" . $idArt . "'>$" . $precioVentaFinalFiva . "</span></p>";
            $renglon .= "       </div>"; // div de precios
            $renglon .= "      </div>"; // div de productos

            // $renglon .= "</td>";

            // CELDA CANTIDAD Y DESCUENTO

            // $renglon .= "<td>".PHP_EOL;
            $renglon .= "    <div class='inputs'>"; // div container.
            // display y bulto



            // VALIDAR SI TIENE MULTIPLICADOR DE CANTIDAD
            $stepCantidad = '0.00';
            $multiplicadorCantidad = 1;
            $minCantidad = 1;
            $badgeMultiplo = '';
            if (isset($arti->multiplo_cantidad_vta) && $arti->multiplo_cantidad_vta > 0) {
                $multiplicadorCantidad = (int) $arti->multiplo_cantidad_vta;
                $stepCantidad = $multiplicadorCantidad;
                $minCantidad = $multiplicadorCantidad;
                $cantidad = $multiplicadorCantidad;
                $badgeMultiplo = "<br><span class='badge badge-warning' style='display:inline-block; margin-top:4px; font-size:0.78em; font-weight:800; text-transform:uppercase; letter-spacing:0.4px; background:#ffce3a; color:#1f2a44; border:1px solid #d3a21f; box-shadow:0 2px 6px rgba(0,0,0,0.25); padding:3px 8px; border-radius:12px;'>{$multiplicadorCantidad} en {$multiplicadorCantidad}</span>";
            }

            $renglon .= "<label for='mi-cantidad-{$arti->IDArt}'>Cantidad <input type='text' class='input-cantidad' onclick='this.select();' inputmode='numeric' id='mi-cantidad-{$arti->IDArt}'  pattern='[0-9]+([,\.][0-9]+)?' value='{$cantidad}' min='{$minCantidad}' step='{$stepCantidad}' {$maxCant}  />{$badgeMultiplo}</label>";
            // $renglon .="    <br>";
            $renglon .=         $descModifica;






            // voy a armar un objeto json que me va a facilitar el uso en javascript calculos y demas
            $jsonArt = (array) $arti;
            // * agregar los valores para las lista de precio segun la lista elegida.
            $jsonArt['importeIva'] = $importeIva;
            $jsonArt['importeInterno'] = $importeInterno;
            $jsonArt['precioNeto'] = $precioNeto;
            $jsonArt['precioNetoDesc'] = $precioNetoCalc;
            $jsonArt['precioFinalIva'] = $precioVentaFinalIva;
            $jsonArt['promo'] = $promo;
            $jsonArt['promoCant'] = $promoCant;
            $jsonArt['descFinal'] = $descFinal;
            $jsonArt['cantBulto'] = $cantBulto;
            $jsonArt['multiplo_cantidad_vta'] = isset($arti->multiplo_cantidad_vta) ? intval($arti->multiplo_cantidad_vta) : 1;
            $tipoPrecioUnidad  = 'Unidad';

            $renglon .= "<input type='hidden' id='mi-imp-iva{$arti->IDArt}' value='{$importeIva}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-imp-interno{$arti->IDArt}' value='{$importeInterno}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-imp-interno-tasa{$arti->IDArt}' value='{$arti->impuesto_interno}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-tipo-iva{$arti->IDArt}' value='{$arti->tipoIVA}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-alic-iva{$arti->IDArt}' value='{$arti->Alic}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-iva{$arti->IDArt}' value='{$arti->Alicuota}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-neto{$arti->IDArt}' value='{$precioNeto}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promo{$arti->IDArt}' value='{$promo}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promocant{$arti->IDArt}' value='{$promoCant}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promoporc{$arti->IDArt}' value='{$descFinal}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-lote{$arti->IDArt}' value='{$arti->lote}'/>" . PHP_EOL;
            $valorSaldoControl = $arti->saldo;
            // valido con el stock
            if($comoValidoSaldo=="stock"){
                $valorSaldoControl = $arti->saldo;
            }
            // valido con el disponible.
            if($comoValidoSaldo=="disponible"){
                $valorSaldoControl = $arti->stockDisponible;
            }
            $renglon .= "<input type='hidden' id='mi-saldo{$arti->IDArt}' value='{$valorSaldoControl}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-ensamblado-vta{$arti->IDArt}' value='{$arti->ensamblado}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promotipo{$arti->IDArt}' value='{$arti->promocion_tipo}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-json{$arti->IDArt}' value='" . json_encode($jsonArt) . "'/>" . PHP_EOL;


            // usando el permiso activo que pueda usar multiplicador x bulto.
            $renglon .= "<input type='hidden' id='mi-bulto{$arti->IDArt}' value='{$cantBulto}'/>" . PHP_EOL;
            if ($arti->tipoPrecioUnidad != '') {
                $tipoPrecioUnidad = $arti->tipoPrecioUnidad;
            }
            $renglon .= "<input type='hidden' id='mi-como-cuento{$arti->IDArt}' value='{$tipoPrecioUnidad}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-multiplo-cantidad-vta{$arti->IDArt}' value='{$multiplicadorCantidad}'/>" . PHP_EOL;

            // validar si puedo comprar o no segun stock y permiso. 
            // valido Stock 
            $andaBoton="";
            if($ventaSinStock=="No"){
                if($comoValidoSaldo=='stock'){
                    // solo valido el saldo / stock
                    if($arti->saldo<=0){
                        $andaBoton=" disabled='disabled' ";
                    }
                }
                if($comoValidoSaldo=='disponible'){
                    // solo valido el saldo / stock
                    if($arti->saldo<=0 || $arti->stockDisponible<=0){
                        $andaBoton=" disabled='disabled' ";
                    }
                }
                // if($arti->saldo<=0 || $arti->stockDisponible<=0){
                //     $andaBoton=" disabled='disabled' ";
                // }
            }
            // $renglon .= "  <button class='comprar tecompro' name='{$arti->IDArt}' ><i class='fa fa-shopping-cart fa-lg '   alt='agregar articulo' title='agregar articulo'></i></button>";
            

            $renglon .= "  <button class='comprar tecompro' name='{$arti->IDArt}'  ".$andaBoton."><i class='fas fa-plus'></i></button>";
            


            // $renglon .= "  <button class='comprar tecompro' name='{$arti->IDArt}' >Agregar</button>";


            $renglon .= "    </div>"; // div inputs.
            $renglon .= "    </div>"; // div container.
            $renglon .= "</td>" . PHP_EOL;


            // BOTON COMPRAR
            // $renglon .="<td>".PHP_EOL;
            // $renglon.= "  <button class='agregar-carrito tecompro' name='{$arti->IDArt}' ><i class='fa fa-shopping-cart fa-lg '   alt='agregar articulo' title='agregar articulo'></i></button>";

            // $renglon .="</td>".PHP_EOL;
            // CIERRRE DE LINEA     
            $renglon .= "</tr>" . PHP_EOL;

            $tabla .= $renglon;
        }

        $tabla .= "</tbody>" . PHP_EOL;
        // echo $tabla;
        return $tabla;
    }
    // * funcion que arma el html pero para productos listado con foto (DESKTOP)
    private function armarHTMLDesktopProductosLista()
    {
        global $objVendedor;
        // datos del cliente
        if (is_object($_SESSION['cliente'])) {
            $objCliente = $_SESSION['cliente'];
        } else {
            $objCliente = $_SESSION['cliente'][0];
        }

        $usaDisplay = 'No';
        $usaBultoCerrado = 'No';

        $usaIdManual = $_SESSION["usa_id_manual"];
        $listaPrecioCliente = $objCliente->listaPrecio;
        // es el descuento por cli del cilente
        $descRenglon = $objCliente->descRenglon;

        $codCliente = $objCliente->Codigo;
        $usaReglaPrecio = $_SESSION['usaReglaPrecio'];
        // $verStock = $_SESSION["verStock"];
        $usaDisplay = 'No';
        $usaBultoCerrado = 'No';
        $controlVenta=0;
        
        $ventaSinStock ="Si";
        $verStock = 'Si'; 
        $comoValidoSaldo ='stock';       
        $verDisponible = 'Si';
         


        //REMITO de FACTURAS SIN STOCK
        $artFactura = null;

        if (isset($_SESSION["sel_factura"])) {
            $artFactura = $_SESSION["sel_factura"]["art"];
        }
        $limDescReng    = 0;
        $modificaReng = 'No';
        if (isset($objVendedor) && is_object($objVendedor)) {
            $limDescReng    = $objVendedor->lim_desc_renglon;
            $modificaReng = $objVendedor->mod_descuento_renglon;
        }
        // echo '<pre>';
        // print_r($objVendedor);
        // echo '</pre>';

        $precioNeto = 0;
        $importeIva = 0;
        $importeInterno = 0;
        $precioVenta = 0;
        /*
             * EMBALAJE
             */
        $caminoDispo = $_SESSION["caminoDisp"];
        if ($_SESSION["utilizaEmbalaje"] == "Si") {
            $usaEmbalaje = "Si";
        } else {
            $usaEmbalaje = "No";
        }

        /* Calcula Cantidad X bultos 
             * =========================
             * Si el campo preso promedio bulto, esta con valores y el permiso
             * esta configurado, se multiplica la cantidad ingresada a comprar 
             * por este valor, luego se transmite la cantidad ingresada como
             * url o detalle para saber cuantos bultos reales se compraron,.
             *              
             */
        $usoBultoPromedio = $_SESSION["uso_bulto_promedio"];
        $queFormulario = $_SESSION['formulario'];


        /*
            * IVA INCLUIDO
            */
        $ivaIncluido = $_SESSION["ivaIncluido"];
        //            $cantidad = $_REQUEST['cantidadOferta'];
        //con el tema de la oferta dejo una idea a desarrollar, pasar el id
        //            con la oferta y si esta coincide recien ahi le clavo la cantidad con 
        //            la oferta. porque viene de hacerle click a una oferta si no cuando busco
        //            borro todo y coloco un uno
        $cantidad = 1;
        // oferta
        if (isset($_REQUEST['cantidadOferta'])) {
            $cantidad = $_REQUEST['cantidadOferta'];
        }



        if (isset($_SESSION['utiliza_display'])) {
            $usaDisplay = $_SESSION['utiliza_display'];
        }
        if (isset($_SESSION['utiliza_bulto_cerrado'])) {
            $usaBultoCerrado = $_SESSION['utiliza_bulto_cerrado'];
        }
        // validar si puede vender si stock
        if(isset($_SESSION['venta_sin_stock'])){
            $ventaSinStock=$_SESSION['venta_sin_stock'];
        }
        // como voy a controlar el saldo por stock o disponible.
        if(isset($_SESSION['comoValidoSaldo'])){
            $comoValidoSaldo=$_SESSION['comoValidoSaldo'];
        }
        // ver stock
        if(isset($_SESSION['verStock'])){
            $verStock=$_SESSION['verStock'];
        }

        // ver disponible
        if(isset($_SESSION['verDisponible'])){
            $verDisponible=$_SESSION['verDisponible'];
        }

        // *  inicio cabecera de los productos.
        $tabla = '';
        $tabla .=  "<thead>" . PHP_EOL;
        $tabla .=  "<tr>" . PHP_EOL;
        // $tabla .=  "<th>&nbsp;</th>".PHP_EOL;
        $tabla .=  "<th>producto</th>" . PHP_EOL;
        // $tabla .=  "<th>precio</th>".PHP_EOL;
        // $tabla .=  "<th>cantidad</th>".PHP_EOL;
        // $tabla .=  "<th></th>".PHP_EOL;

        $tabla .=  "</tr>" . PHP_EOL;
        $tabla .=  "</thead>" . PHP_EOL;
        $tabla .=  "<tbody>" . PHP_EOL;

        // * recorrido de los productos

        foreach ($this->colArticulos as $arti) {
            $renglon = '';
            // remito de cantidad fija.
            $descFinalT = '';
            //                $promo = $arti->promocion;
            if ($usaIdManual == "Si") {
                $idArtT = $arti->id_manual;
            } else {
                $idArtT = $arti->IDArt;
            }

            $idArt = $arti->IDArt;
            $nombreArticulo = $arti->NombreArticulo;
            // * lista de precios standar
            $paramPrecios['arti'] = $arti;
            $paramPrecios['listaPrecioCliente'] = $listaPrecioCliente;
            $paramPrecios['descRenglon'] = $descRenglon;
            $paramPrecios['usaReglaPrecio'] = $usaReglaPrecio;
            $paramPrecios['codCliente'] = $codCliente;
            $precios = $this->calculaPrecios($paramPrecios);

            // * datos de display y bulto vienene desde la sesion.
            // bulto cerrado 
            // if ($_SESSION["utiliza_bulto_cerrado"] == "Si") {
            //     $usoBultoCerrado = $_SESSION["utiliza_bulto_cerrado"];
            // }

            // // display 
            // if ($_SESSION['utiliza_display'] == "Si") {
            //     $usaDisplay = $_SESSION['utiliza_display'];
            // }
            // si ambos permisos estan habilitados, mustro botones y armo la busqueda de valores. po lista precio.
            // * si tengo los permisos, debo recuperar precios para display.

            // * si tengo los permisos debo recuparar precios para bulto.

            //echo "<pre>";
            ////                print_r($arti);
            // echo "<pre>";
            // print_r($arti);
            // print_r($precios);
            // echo "</pre>";
            /*
     * EMBALAJE
     */
            $bulto = "";
            if ($usaEmbalaje == "Si") {
                // tengo que hacer la busqueda de los valores para mostrar
                $bulto = $arti->nombre_presentacion . " x " . $arti->cantidad_uni;
                if ($arti->nombre_unimed != "") {
                    $bulto .= " (" . $arti->nombre_unimed . ")";
                }
                //                    $bulto = $arti->nombre_presentacion ." x ".$arti->cantidad_uni."(".$arti->nombre_unimed.")";

            }

            //$hoy3 = $hoy; 
            //                formateo los precios a cuatro decimales..just in case...
            $clase = $precios["clase"];
            //            $nombreArticulo .=$precios["promoNombre"];
            $clasePrecio = "";
            //            $clasePrecio = $precios["clasePrecio"];
            $cantidad = 1;

            if ($precios["cantidad"] > 1) {
                // echo 'soy una promocion por cantidad';
                // promocion por cantidad, tiene que hacer la division de kilos porque la cntidad es por kilos.
                if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                    // echo 'soy una promocion por cantidad 2';
                    // $cantidad = round($precios["cantidad"] / $arti->cantidad_promedio_bulto,2,PHP_ROUND_HALF_UP);
                    $cantidad = $this->ajusteRedondeoBulto($precios['cantidad'],round($precios["cantidad"] / $arti->cantidad_promedio_bulto,2,PHP_ROUND_HALF_UP),$arti->cantidad_promedio_bulto);
                }else{
                    $cantidad = $precios["cantidad"];
                }
                
            }
            $maxCant = "";
            // facturas remitos
            if (isset($artFactura) && $artFactura != null) {
                $cantidad = $artFactura[$arti->IDArt]["cuanto"];
                $maxCant = "max='" . $cantidad . "'";
            }

            // consumos
            if (property_exists($arti, 'CantidadProm')) {
                $cantidad = $arti->CantidadProm;
            }

            // STOCK - DISPONIBLE
            $tStock = "";
            $tDisponible = "";
            $tPresentacion = ""; // muestra el divisor proedio
            if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                // $tStock .= " Stock: <strong>" . number_format(($arti->saldo / $arti->cantidad_promedio_bulto), 2, ',', '');
                // $tStock .= "<strong>" . number_format(($arti->stockDisponible / $arti->cantidad_promedio_bulto), 2, ',', '') . "</strong>";
                $tStock .= number_format(($arti->saldo / $arti->cantidad_promedio_bulto), 2, ',', '');
                $tDisponible .= number_format(($arti->stockDisponible / $arti->cantidad_promedio_bulto), 2, ',', '');
                $cantBulto = $arti->cantidad_promedio_bulto;
                $tPresentacion .= number_format($arti->cantidad_promedio_bulto, 1) . " " . $arti->uniArt;
                // $tStock .= ", Pres: (" . $arti->cantidad_promedio_bulto . " " . $arti->uniArt . ")";
                // $tDisponible .= ", Pres: (" . $arti->cantidad_promedio_bulto . " " . $arti->uniArt . ")";


            } else {
                // $tStock .= "Stock: <strong>" . $arti->saldo."</strong>";
                $tStock .= $arti->saldo;
                $tDisponible .= $arti->stockDisponible;

                $cantBulto = 1;
            }





            // PROMOCIONES ANALISIS
            $promoCant = $precios["cantidad"];
            $promo = $precios["promo"];
            $tagPromo = "";

            // if ($precios["promo"] == "si" || ($precios["promo"]=="no" && $precios["promoTipo"]=="Cantidad - Intervalo")) {
            if ($precios["promo"] == "si" ) {

                // $tagPromo = '<div class="descuento-verde"><i class="fa fa-gift fa-lg fa-fw"></i>' . $precios['descuento'] . '% OFF</div>';
                // * promocion por intervalo por bulto
                if ($arti->cantidad_promedio_bulto > 0 && $arti->tipo_unidad == "Peso" && $queFormulario != "devolucion") {
                    $tagPromo .= '<div class="descuento-verde">Promoción<br> ' . $this->detalle_promo($precios["promoTipo"], $precios["descuento"], $precios["cantidad"], $precios["idart"],1,$arti->cantidad_promedio_bulto,$arti->uniArt,$precios) . "</div>";

                }else{

                    $tagPromo .= '<div class="descuento-verde">Promoción<br> ' . $this->detalle_promo($precios["promoTipo"], $precios["descuento"], $precios["cantidad"], $precios["idart"],1,null,null,$precios) . "</div>";
                }
            }

            $importeInterno = $precios["importeInterno"];

            $importeIva = $precios["importeIva"];

            // echo var_dump($ivaIncluido);
            if ($ivaIncluido == 'no') {
                $precioNeto = $precios["neto"];
                $precioVenta = $precios["precioVenta"];

                $precioVentaFinal = $precios["netoCalc"];
                $precioVentaFinalIva = $precios["precioFinal"]; // precio
            } else {
                $precioNeto = $precios["neto"];
                $precioVenta = $precios["precioVenta"];
                $precioVentaFinal = $precios["netoCalc"];
                $precioVentaFinalIva = $precios["precioFinal"];
            }

            $descFinal = $precios["descuento"];

            $precioVentaF = number_format($precioVenta, 2, ',', '.');
            $precioNetoF = number_format($precioNeto, 2, ',', '.');
            $precioVentaFinalF = number_format($precioVentaFinal, 2, ',', '.');
            $precioVentaFinalFiva = number_format($precioVentaFinalIva, 2, ',', '.');
            // neto menos descuento y sin iva.
            $precioNetoCalc = $precios['netoCalc'];
            $precioNetoCalcF = number_format($precioNetoCalc, 2, ',', '.');
            $importeInternoF = number_format($importeInterno, 2, ',', '.');
            $descFinalF = number_format($descFinal, 2);
            $montoDescuento = $precios['montoDescuento'];
            $montoDescuentoFormato = number_format($montoDescuento, 2, ',', '.');
            $campoMax = "";

            if (isset($artFactura) && $artFactura != null) {
                $cantidad = $artFactura[$idArt]["cuanto"];
                $maxCant = "max='" . $cantidad . "'";
                $campoMax = "<input type='hidden'  id='mi-cant-factura{$idArt}' value='" . $cantidad . "'>";
            }
            //                $precioVenta = $precioVenta; 
            //                $precioNeto=$precioNeto;
            //$alicuota = format_number($arti->Alic);
            //print_r($arti);
            // RENGLON INICIAL 
            $renglon .= "<tr>";

            /* desde la lista catalogo*/
            // $tabla .= '<td style="vertical-align: top;padding-top:3%">'
            //     //. '<a href="'.$srcArticulo.'" title="Ver detalle"> '                          

            //     . '<div class="fotoChica">'
            //     . $descFinalT
            //     . '<img src="foto.php?origen=foto1|' . $idArt . '&mini=2">'
            //     . '</div>'

            //     . '</td>';

            // TD CON NOMBRES Y DETALLES DE PRODUCTOS
            $renglon .= '<td >';
            $renglon .= "    <div class='contenedor'>";
            $renglon .= "        <div class='producto'>";

            $renglon .= '           <div class="producto-foto ver-mas" id="mi-art' . $idArt . '" rel="' . $idArt . '" producto-nombre="' . $nombreArticulo . '">';
            //$renglon .= '        		<div class="desc-articulo ver-mas" rel="' . $idArt . '" ><i class="fas fa-info-circle fa-lg "></i></div>' . PHP_EOL;
            $renglon .= '				<div class="producto-imagen"><img src="foto.php?origen=foto1|' . $idArt . '&mini=2" onload="this.classList.add(\'loaded\')"></div>' . PHP_EOL;
			$renglon .= '           </div>';

			$renglon .= '           <div class="producto-nombre ver-mas" id="mi-art' . $idArt . '" rel="' . $idArt . '" producto-nombre="' . $nombreArticulo . '">';
            $renglon .= '               <div class="titulo"><i class="fas fa-info-circle fa-lg "></i> ' . PHP_EOL;

            $renglon .=                 	$nombreArticulo;
			$renglon .= '           	</div>';
            $renglon .= $tagPromo;
            $renglon .= '           </div>';
            // $renglon .='<br>';


            // linea con el detalle 
            $renglon .= '<span class="producto-detalle">' . PHP_EOL;

            // <span class="producto-detalle">Stock: 5 | ID: 67890 | Descuento: <span class="descuento-verde">5%</span></span>
            // <br>
            // <a href="#" class="ver-mas">Ver más</a>
            // linea con id producto.


           
            $renglon .= '<span>Cod: <strong>' . $idArtT . '</strong></span>';
            // linea con stock

            //if ($verStock == "Si") {
                // verifico vista stock
                if($verStock == "Si"){
                    $renglon .= '<span>Saldo: <strong class="cantidad-stock">' . $tStock . '</strong></span>';
                }
                if($verDisponible=="Si"){
                    $renglon .= '<span>Dispo: <strong class="cantidad-stock">' . $tDisponible . '</strong></span>';
                }
                // verifico vista disponible
                //$renglon .= '<span>Dispo: <strong class="cantidad-stock">' . $tDisponible . '</strong></span>';
                if ($tPresentacion != "") {
                    $renglon .= '<span>Pres: <strong class="cantidad-stock">' . $tPresentacion . '</strong></span>';
                }
            // }
            // $renglon .= 'Cod: <strong>' . $idArtT . '</strong> ';
            // promocion y descuento Manejo

            $descModifica = "";


            /// descuentos
            if ($promo == 'No' || $promo == "no") {
                /*
                     * No tengo promocion
                     */
                if ($_SESSION['tipousuario'] == 'cliente') {
                    /*
                         * Soy un cliente y tengo descuento pero no puedo tocarlo
                         */
                    $descModifica .= "<input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  />" . PHP_EOL;
                    if ($descFinal != 0) {

                        $renglon .= "<span class='descuento-verde'>{$descFinalF}% OFF</span>" . PHP_EOL;
                    }
                }

                if ($_SESSION['tipousuario'] == 'vendedor') {
                    /*
                         * Soy el vendedor y si el cliente tiene descuento lo aplico y desactivo si no
                         * lo dejo que pueda agregarlo hasta un limite buscar ese limite.
                         * pero depende del permiso si permito modificarlo.
                         */

                    if ($descFinal == 0) {
                        if ($modificaReng == "Si" && $limDescReng > 0) {

                            //$tabla .= "<span class='verde'>{$descFinalF}% OFF</span>".PHP_EOL;
                            $limDescReng = str_replace(",", ".", $limDescReng);
                            //echo var_dump($re)
                            //$limDescReng=number_format($limDescReng,2,'.','');
                            $descModifica .= "<label for='mi-desc-{$idArt}'>Descuento: <input type='text' class='input-descuento'  inputmode='numeric' id='mi-desc-{$arti->IDArt}'  value='' placeholder='0%' alt='{$descFinal} - {$modificaReng} - {$limDescReng}'  step='1'  min='0' max='{$limDescReng}' /></label>" . PHP_EOL;
                        }

                        // if ($modificaReng != "Si" || $limDescReng <= 0) {
                        //     $descModifica = "<label for='mi-desc-{$arti->IDArt}'>Desc:</label>: <input type='text' class='input-descuento' id='mi-desc-{$arti->IDArt}' value='0' alt='{$descFinal} - {$modificaReng}'  step='1'  min='0' max='{$limDescReng}' />".PHP_EOL;
                        // }
                    }

                    if ($descFinal != 0) {

                        //$tabla .= "<input type='number' id='mi-desc-{$arti->IDArt}' value='{$descFinal}' disabled min='0.00' max='{$descFinal}' style='width:50px;' />".PHP_EOL;
                        $descModifica .= "<label for='mi-desc-{$idArt}'>Descuento <input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  ></label>" . PHP_EOL;
                        $renglon .= "  <span class='descuento-verde'>{$descFinalF}% OFF</span>" . PHP_EOL;
                    }
                }
            }

            if ($promo == "si") {
                /*  Hay promocion       */
                $descModifica .= "<input type='hidden' id='mi-desc-{$idArt}' value='{$descFinal}' disabled style='width:50px;' />" . PHP_EOL;
            }
            // $descModifica .= "<label for='mi-desc-{$idArt}'>Desc: <input type='text' class='input-descuento' id='mi-desc-{$idArt}' disabled value='{$descFinal}'  ></label>" . PHP_EOL;
            $renglon .= $campoMax . "" . PHP_EOL;

            $renglon .= '</span>'; // fin span de detalle.
            // lista de precios
            $renglon .= '<span class="mi-lista-precio"><input type="hidden" id="mi-lista-precio-'.$idArt.'" name="mi-lista-precio-'.$idArt.'" value="'.$listaPrecioCliente.'" ></span>';
            // PRECIOS UNA  LINEA
            // $renglon .="    </div>"; //div de nombre. 
            // * presentacion unidad display bulto
            if ($usaDisplay != 'No' || $usaBultoCerrado != 'No') {
                $renglon .= $this->htmlUnidadDisplayBulto($arti);
            }
            $renglon .= "    <div class='precios'>";
            // averiguo si hay descuento. 
            // hay descuento 
            // echo var_dump($montoDescuento);
            $renglon .= "        <p><span class='precio-nombre'>Neto</span><br><span class='precio-neto' id='precio-neto-texto-" . $idArt . "'>$" . $precioNetoF . "</span></p>";
            $tituloFinal = "";
            if ($montoDescuento != 0) {
                // $renglon .="<p><span class='precio-nombre'>{$descFinalF}% OFF</span> <span class='precio-descuento'>$".$montoDescuentoFormato."</span></p>";                
                $renglon .= "        <p><span class='precio-nombre'>Final</span><br><span class='precio-neto' id='precio-final-texto-anterior-" . $idArt . "'>$" . $precioVentaF . "</span></p>";
                
                $renglon .= "        <p><span class='precio-nombre precio-nombre-descuento'>Neto desc<br></span> <span class='precio-descuento' id='precio-descuento-texto-" . $idArt . "'>$" . $precioNetoCalcF . "</span></p>";
                // $tituloFinal = " <span class='precio-descuento'>c/desc</span>";

            }

            // impuesto interno si no tengo valor no lo pongo.
            if($importeInterno!=0){
                $renglon .= "        <p><span class='precio-nombre'>Imp Int</span><br><span class='impuesto-interno' id='impuesto-interno-texto-" . $idArt . "'>$" . $importeInternoF . "</span></p>";
            }

            $renglon .= "            <p><span class='precio-nombre'>Final (" . round($arti->Alic, 0) . "%)".$tituloFinal."</span><br><span class='precio-final' id='precio-final-texto-" . $idArt . "'>$" . $precioVentaFinalFiva . "</span></p>";
            $renglon .= "       </div>"; // div de precios
            $renglon .= "      </div>"; // div de productos

            // $renglon .= "</td>";

            // CELDA CANTIDAD Y DESCUENTO

             // CELDA CANTIDAD Y DESCUENTO

            // $renglon .= "<td>".PHP_EOL;
            $renglon .= "    <div class='inputs'>"; // div container.
            // display y bulto



            // VALIDAR SI TIENE MULTIPLICADOR DE CANTIDAD
            $stepCantidad = '0.00';
            $multiplicadorCantidad = 1;
            $minCantidad = 1;
            $badgeMultiplo = '';
            if (isset($arti->multiplo_cantidad_vta) && $arti->multiplo_cantidad_vta > 0) {
                $multiplicadorCantidad = (int) $arti->multiplo_cantidad_vta;
                $stepCantidad = $multiplicadorCantidad;
                $minCantidad = $multiplicadorCantidad;
                $cantidad = $multiplicadorCantidad;
                $badgeMultiplo = "<br><span class='badge badge-warning' style='display:inline-block; margin-top:4px; font-size:0.78em; font-weight:800; text-transform:uppercase; letter-spacing:0.4px; background:#ffce3a; color:#1f2a44; border:1px solid #d3a21f; box-shadow:0 2px 6px rgba(0,0,0,0.25); padding:3px 8px; border-radius:12px;'>{$multiplicadorCantidad} en {$multiplicadorCantidad}</span>";
            }

            $renglon .= "<label for='mi-cantidad-{$arti->IDArt}'>Cantidad <input type='text' class='input-cantidad' onclick='this.select();' inputmode='numeric' id='mi-cantidad-{$arti->IDArt}'  pattern='[0-9]+([,\.][0-9]+)?' value='{$cantidad}' min='{$minCantidad}' step='{$stepCantidad}' {$maxCant}  />{$badgeMultiplo}</label>";
            // $renglon .="    <br>";
            $renglon .=         $descModifica;






            // voy a armar un objeto json que me va a facilitar el uso en javascript calculos y demas
            $jsonArt = (array) $arti;
            // * agregar los valores para las lista de precio segun la lista elegida.
            $jsonArt['importeIva'] = $importeIva;
            $jsonArt['importeInterno'] = $importeInterno;
            $jsonArt['precioNeto'] = $precioNeto;
            $jsonArt['precioNetoDesc'] = $precioNetoCalc;
            $jsonArt['precioFinalIva'] = $precioVentaFinalIva;
            $jsonArt['promo'] = $promo;
            $jsonArt['promoCant'] = $promoCant;
            $jsonArt['descFinal'] = $descFinal;
            $jsonArt['cantBulto'] = $cantBulto;
            $tipoPrecioUnidad  = 'Unidad';

            $renglon .= "<input type='hidden' id='mi-imp-iva{$arti->IDArt}' value='{$importeIva}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-imp-interno{$arti->IDArt}' value='{$importeInterno}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-imp-interno-tasa{$arti->IDArt}' value='{$arti->impuesto_interno}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-tipo-iva{$arti->IDArt}' value='{$arti->tipoIVA}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-alic-iva{$arti->IDArt}' value='{$arti->Alic}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-iva{$arti->IDArt}' value='{$arti->Alicuota}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-neto{$arti->IDArt}' value='{$precioNeto}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promo{$arti->IDArt}' value='{$promo}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promocant{$arti->IDArt}' value='{$promoCant}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promoporc{$arti->IDArt}' value='{$descFinal}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-lote{$arti->IDArt}' value='{$arti->lote}'/>" . PHP_EOL;
            $valorSaldoControl = $arti->saldo;
            // valido con el stock
            if($comoValidoSaldo=="stock"){
                $valorSaldoControl = $arti->saldo;
            }
            // valido con el disponible.
            if($comoValidoSaldo=="disponible"){
                $valorSaldoControl = $arti->stockDisponible;
            }
            $renglon .= "<input type='hidden' id='mi-saldo{$arti->IDArt}' value='{$valorSaldoControl}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-ensamblado-vta{$arti->IDArt}' value='{$arti->ensamblado}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-promotipo{$arti->IDArt}' value='{$arti->promocion_tipo}'/>" . PHP_EOL;
            $renglon .= "<input type='hidden' id='mi-json{$arti->IDArt}' value='" . json_encode($jsonArt) . "'/>" . PHP_EOL;


            // usando el permiso activo que pueda usar multiplicador x bulto.
            $renglon .= "<input type='hidden' id='mi-bulto{$arti->IDArt}' value='{$cantBulto}'/>" . PHP_EOL;
            if ($arti->tipoPrecioUnidad != '') {
                $tipoPrecioUnidad = $arti->tipoPrecioUnidad;
            }
            $renglon .= "<input type='hidden' id='mi-como-cuento{$arti->IDArt}' value='{$tipoPrecioUnidad}'/>" . PHP_EOL;

            $andaBoton="";
            if($ventaSinStock=="No"){
                if($comoValidoSaldo=='stock'){
                    // solo valido el saldo / stock
                    if($arti->saldo<=0){
                        $andaBoton=" disabled='disabled' ";
                    }
                }
                if($comoValidoSaldo=='disponible'){
                    // solo valido el saldo / stock
                    if($arti->saldo<=0 || $arti->stockDisponible<=0){
                        $andaBoton=" disabled='disabled' ";
                    }
                }
                // if($arti->saldo<=0 || $arti->stockDisponible<=0){
                //     $andaBoton=" disabled='disabled' ";
                // }
            }
            $renglon .= "  <button class='comprar tecompro' name='{$arti->IDArt}' >Agregar</button>";

            $renglon .= "    </div>"; // div inputs.
            $renglon .= "    </div>"; // div container.
            $renglon .= "</td>" . PHP_EOL;


            // BOTON COMPRAR
            // $renglon .="<td>".PHP_EOL;
            // $renglon.= "  <button class='agregar-carrito tecompro' name='{$arti->IDArt}' ><i class='fa fa-shopping-cart fa-lg '   alt='agregar articulo' title='agregar articulo'></i></button>";

            // $renglon .="</td>".PHP_EOL;
            // CIERRRE DE LINEA     
            $renglon .= "</tr>" . PHP_EOL;

            $tabla .= $renglon;
        }

        $tabla .= "</tbody>" . PHP_EOL;
        // echo $tabla;
        return $tabla;
    }
    // * funcion que analiza la cantidad y trae datos. nuevos.
    private function htmlUnidadDisplayBulto($objArticulo)
    {
        $arrArticulo = (array) $objArticulo;
        $tipoUnidad = 'Unidad'; // valor por defecto


        $idArt = $arrArticulo['IDArt'];
        if ($arrArticulo['tipoPrecioUnidad'] != '') {
            $tipoUnidad = $arrArticulo['tipoPrecioUnidad']; // como viene el precio descuento
        }

        $cantidadUnidadDisplay = 1; // cuantas unidaees minimas hay en un display
        $cantidadDisplayBulto = 1; // cuantos display hay es una caja o bulto 
        $cantidadUnidadMinimaCaja = 1;
        $cantidadMinimaFinal = 1;
        // $tipoUnidad=$arrArticulo['tipoPrecioUnidad']; // como viene el precio descuento

        // $iconoCheck='<i class="fas fa-check-circle fa-lg"></i>';
        $iconoCheck = '<i class="fas fa-check-circle fa-lg" aria-hidden="true"></i>';
        // validando
        // display
        if ($arrArticulo['cantidad_unidad_display'] != 0 && $arrArticulo['cantidad_unidad_display'] != null) {
            $cantidadUnidadDisplay = (int)$arrArticulo['cantidad_unidad_display'];
        }

        // bulto
        if ($arrArticulo['cantidad_display_bulto'] != 0 && $arrArticulo['cantidad_display_bulto'] != null) {
            $cantidadDisplayBulto = $arrArticulo['cantidad_display_bulto'];
        }

        $cantidadUnidadMinimaCaja = $cantidadUnidadDisplay * $cantidadDisplayBulto; // cuantas unidades minimas hay en una caja.
        //$cantidadMinimaFinal = $cantidadUnidadDisplay * $cantidadDisplayBulto;

        $html = "";
        $html .= '<div class="presentacion" id="divUnidadDisplayBulto">';

        //*  unidad :: sacamos la unidad porque no se usa
// evaluar si tiene el permiso para cambiar la lista de precio se saca dle ajax cacula precio.

        // precios en unidad
        if ($tipoUnidad == 'Unidad') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '" ';
            $html .= '               id="tipoUnidadUnidad' . $idArt . '" ';
            $html .= '               value="Unidad" ';
            $html .= '               checked="checked"';
            $html .= '               onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Unidad\',this)">';
            $html .= '       <label class="cantidad-unidad elegida" for="tipoUnidadUnidad' . $idArt . '">Unidad <strong>x1</strong> ' . $iconoCheck . '</label>';
        }

        if ($tipoUnidad != 'Unidad') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '" ';
            $html .= '               id="tipoUnidadUnidad' . $idArt . '" ';
            $html .= '               value="Unidad" ';
            $html .= '               onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Unidad\',this)">';
            $html .= '       <label class="cantidad-unidad" for="tipoUnidadUnidad' . $idArt . '"> Unidad <strong>x1</strong></label>';
        }

        //* display

        if ($tipoUnidad == 'Display') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '"';
            $html .= '               id="tipoUnidadDisplay' . $idArt . '" ';
            $html .= '               checked="checked"';
            $html .= '               value="Display" ';
            $html .= '               onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Display\',this)">';
            $html .= '       <label class="cantidad-display elegida" for="tipoUnidadDisplay' . $idArt . '">Display <strong>x' . round($cantidadUnidadDisplay, 0) . '</strong> ' . $iconoCheck . '</label>';
            // $html .='       <label class="cantidad-display elegida" for="tipoUnidadDisplay'.$idArt.'"><i class="fa-solid fa-check"></i> Display (x'.round($cantidadUnidadDisplay,0).')</label>';


        }

        if ($tipoUnidad != 'Display') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '"';
            $html .= '               id="tipoUnidadDisplay' . $idArt . '" ';
            $html .= '               value="Display" ';
            $html .= '               onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Display\',this)">';
            $html .= '       <label class="cantidad-display" for="tipoUnidadDisplay' . $idArt . '">Display <strong>x' . round($cantidadUnidadDisplay, 0) . '</strong></label>';
        }

        //* bulto
        if ($tipoUnidad == 'Bulto') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '" ';
            $html .= '               id="tipoUnidadBulto' . $idArt . '" ';
            $html .= '               checked="checked"';
            $html .= '              value="Bulto" ';
            $html .= '              onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Bulto\',this)">';
            $html .= '       <label class=" cantidad-bulto elegida"  for="tipoUnidadBulto' . $idArt . '">Bulto <strong>x' . round($cantidadUnidadMinimaCaja, 0) . '</strong> ' . $iconoCheck . '</label>';
            // $html .='       <label class=" cantidad-bulto elegida"  for="tipoUnidadBulto'.$idArt.'"><i class="fa-solid fa-check"></i> Bulto (x'.round($cantidadUnidadMinimaCaja,0).')</label>';


        }

        if ($tipoUnidad != 'Bulto') {
            $html .= '       <input type="radio" name="tipoUnidad' . $idArt . '" ';
            $html .= '               id="tipoUnidadBulto' . $idArt . '" ';
            $html .= '              value="Bulto" ';
            $html .= '              onclick="cambiaPrecioTipoUnidad(' . $idArt . ',\'Bulto\',this)">';
            $html .= '       <label class=" cantidad-bulto"  for="tipoUnidadBulto' . $idArt . '">Bulto <strong>x' . round($cantidadUnidadMinimaCaja, 0) . '</strong></label>';
        }

        $html .= '</div>';
        return $html;
    }

    //* funcion que buscara las fotos de productos, de aquellos que tengan alguna, 

    private function listaFotosProducto(&$arrFotos, $listaProductos)
    {
        // buscar fotos en base de datos, luego buscar en disco, la que no existe la creo, y debo recuperar la url.
        $conex = $this->connV;

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
                    $arrUrlFoto = explode("|", $foto['extension']);
                    $urlFotoDisco = "_img/productos/miniatura/xs/" . $arrUrlFoto[1] . "." . $arrUrlFoto[0];


                    // verificamos que el archivo exista fisicamente.
                    if (file_exists($urlFotoDisco)) {
                        // la foto existe reemplazo el sin foto por esta imagen.
                        $arrFotos[$id] = $urlFotoDisco;
                    }

                    // echo 'urlfotodisco<pre>',print_r($urlFotoDisco),'</pre>',PHP_EOL;
                    //no existe la foto hay que crearla.
                    if (!file_exists($urlFotoDisco)) {
                        $arrNombreFoto = explode("|", $foto["extension"]);
                        // echo 'nombre foto<pre>',print_r($arrNombreFoto),'</pre>';
                        $urlFoto = $foto["url"];
                        $nombreArchivo = $arrNombreFoto[1];
                        $extension = $arrNombreFoto[0];
                        // $url = $fila["url"];
                        //https://i.imgur.com/fHtzRLg.jpg
                        /*
                        * Thumbnail Suffix	Thumbnail Name	Thumbnail Size	Keeps Image Proportions
                            s	Small Square	90x90	No
                            b	Big Square	160x160	No
                            t	Small Thumbnail	160x160	Yes
                            m	Medium Thumbnail	320x320	Yes
                            l	Large Thumbnail	640x640	Yes
                            h	Huge Thumbnail	1024x1024	Yes
                        */
                        // servidor img ur antigua version.
                        if (strpos($urlFoto, "i.imgur.com")) {
                            $a = explode(".", $urlFoto);
                            // echo 'a<pre>',print_r($a),'</pre>';
                            $arrUrl = explode("/", $foto["url"]);
                            $urlGrande = $a[0] . "." . $a[1] . "." . $a[2] . "h" . "." . $a[3];
                            $urlMediana = $a[0] . "." . $a[1] . "." . $a[2] . "m" . "." . $a[3];
                            $urlChica = $a[0] . "." . $a[1] . "." . $a[2] . "s" . "." . $a[3];
                            $arrExterno = array('nombreFoto' => $arrUrl[3], 'urlGrande' => $urlGrande, 'urlMediana' => $urlMediana, 'urlChica' => $urlChica);
                        }
                        // nuevo servidor de fotos administraNET
                        // ---------------------------------------------------
                        if (!strpos($urlFoto, "i.imgur.com")) {
                            $a = explode('.jpg', $urlFoto);
                            $arrUrl = explode("|", $urlFoto);
                            $urlGrande = $urlFoto;
                            $urlMediana = $a[0] . "_l." . $extension;
                            $urlChica = $a[0] . "_m." . $extension;
                            $arrExterno = array('urlFoto' => $urlFoto, 'nombreFoto' => $nombreArchivo . '.' . $extension, 'urlGrande' => $urlGrande, 'urlMediana' => $urlMediana, 'urlChica' => $urlChica);
                        }
                        // echo 'arrExterno<pre>',print_r($arrExterno),'</pre>',PHP_EOL;
                        // buscar la foto o crearla en el disco.
                        $urlFotoDisco = $this->crearFotoChicaEnDisco($arrExterno);
                        $arrFotos[$id] = $urlFotoDisco;
                    }
                }
            }

            // no hay fotos de nadie


        }
        return true;
    }

    // * creo la foto en miniatura en disco => retorna url final.
    private function crearFotoChicaEnDisco($arrUrl)
    {
        $rutaMini = "miniatura/xs/";
        // foto chica
        $url = $arrUrl['urlChica'];

        $nombreFoto = $arrUrl['nombreFoto'];

        // $ch = curl_init($url);

        $my_save_dir = '_img/productos/' . $rutaMini;

        //$filename = basename($url);
        $filename = $nombreFoto;
        $complete_save_loc = $my_save_dir . $filename;

        // echo '<pre>',var_dump($complete_save_loc),'</pre>',PHP_EOL;
        $fotoExterna = file_get_contents($url);
        if ($fotoExterna) {
            // tamaño chico.
            $tam = 90;
            //$nombreFoto = "imagen-chica";
            $calidad = 100;
            $im = imagecreatefromstring($fotoExterna);


            $width = imagesx($im);
            $height = imagesy($im);
            $imagen = imagecreatefromstring($fotoExterna);
            $imgw = $tam;
            //CALCULAMOS EL ALTO DE LA IMAGEN PARA MANTER EL ASPECTO
            $imgh = $height / $width * $imgw;
            // CREAMOS UNA NUEVA IMAGEN UTILIZANDO LAS NUEVAS MEDIDAS
            $thumb = imagecreatetruecolor($imgw, $imgh);


            // COPIAMOS LA IMAGEN ORIGINA AL THUMBNAIL
            imagecopyresized($thumb, $im, 0, 0, 0, 0, $imgw, $imgh, imagesx($im), imagesy($im));
            $back = imagecolorallocate($thumb, 255, 255, 255);
            imagefill($thumb, 0, 0, $back);

            $nuevaFoto = imagejpeg($thumb, $complete_save_loc, 100);
            // echo 'tengo la foto externa=>',var_dump($fotoExterna),PHP_EOL;
            // $nuevaFoto = imagejpeg($imagen, $complete_save_loc, 100);
            if (!$nuevaFoto) {
                echo 'no puedo generar la nueva foto.', PHP_EOL;
            }
        }
        // exit;


        return $complete_save_loc;
    }

    //* funcion para pasar una imagen a base 64

    private function convertImageToBase64($imagePath)
    {

        // buscar la foto en la base para traerla.
        $imageData = file_get_contents($imagePath);
        //$imageData = file_get_contents('_img/productos/miniatura/xs/00c3fa9cf983a8748eb5da9229a4ffb0.jpg');

        // http://localhost:8090/administraweb/sistema/foto.php?origen=foto1|8102&mini=2
        //echo 'imagen data',var_dump($imagePath), var_dump($imageData);

        // curl?

        // echo 'imagen data',var_dump($imagePath);

        return base64_encode($imageData);
    }
    // ajuste calculo de cantidad por bulto promedio.
    private function ajusteRedondeoBulto ($bultoPromo,$cantidadPromo,$bultoPromedio){
        
        // $arg_list = func_get_args();
        // echo 'que recibo d ajuste redondeo <pre>',print_r($arg_list),'</pre>',PHP_EOL;
        $cantidadFinal = $cantidadPromo;
        if($bultoPromo>($cantidadPromo*$bultoPromedio)){
            // echo ' me pase mucho::', 'Diferenci:=>[',$bultoPromo -($cantidadPromo*$bultoPromedio),']';
            $diferencia = $bultoPromo -($cantidadPromo*$bultoPromedio);
            $cantidadFinal =$cantidadFinal + $diferencia;
        }
        // echo 'que devuelvoo::[',$cantidadFinal,']';
        return $cantidadFinal;
    }
}
$articulos = new articulos($connV);
