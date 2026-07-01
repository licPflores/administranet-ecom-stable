<?php

require_once 'conexion-vendedor.inc.php';
$usuario = mysqli_real_escape_string($conexionT,$_POST["usuario"]);
$pass = mysqli_real_escape_string($conexionT,$_POST["clave"]);

/*
 * PARAMETROS INICIALES 
 * =============================================================================
 */
# TODO: crear cookies con vencimiento a 15 dias  salvo que salga de sesion
# TODO:  guardar usuario y base de datos para hacer login simulado. en conexion general

/*
 * MODULOS
 * -----------------------------------------------------------------------------
 */
// ver reportes gerencialaes web despues evaluar el permiso.
$mod_reportes_gerenciales=0;

// modulo que permite hacer rutas domicilio obligatorio.
$mod_logistica=0;
// alta cliente rapida - edicion - domicilios.
$mod_clientes=0;
// inventario 
$mod_inventario=1;
// modulo de premios : alta canjes configuracion
$mod_premios=0;
/*
 * COMPROBANTES
 * -----------------------------------------------------------------------------
 */

$devoluciones="no";
$remitos="no";
$recibos="no";
$presupuesto="no";




$controlHorario="no";
$empiezaSesion =0;

$vistaProductos = 'lista'; // grid defecto es grid.

//echo "<br>sesion:=><pre>";
//print_r($_SESSION);
//echo "</pre>";
/*control de hora*/
if($controlHorario=="si"){
    $sqlHora="SELECT CURTIME()as horaControl;";
    $hcontrol = mysqli_query($conexionT,$sqlHora) or die("No puedo controlar la hora".mysqli_error($conexionT));
    $hora= mysqli_fetch_assoc($hcontrol);

//    $desdeHora=mktime(07,00,00);
    $desdeHora=mktime(07,00,00);
    $hastaHora=mktime(20,30,00);

    $hh=  explode(":", $hora["horaControl"]);
    $horahora= mktime($hh[0],$hh[1],$hh[2]);
//    var_dump($horahora<=$desdeHora||$horahora>=$hastaHora);
    if($horahora<=$desdeHora||$horahora>=$hastaHora){
        // no ingreso restriccion horaria.
        $cartel=3;
        $empiezaSesion++;
    }

}

//controlo version de permisos, consulto por la nueva.

$sqlControl="SHOW TABLES LIKE 'permiso_sistema_puesto';";
$hacerP=mysqli_query($conexionT,$sqlControl) or die ("No se pudo ejecutar la consulta<br>".  mysqli_error($conexionT).'<br>'.$sqlControl);
$rs= mysqli_fetch_assoc($hacerP);


if(!empty($rs)){
    // las tablas de permiso nuevas existen.
    /*
     *  Listado de permisos web en puestos.
     * =========================================================================
     * 43=>lim_desc_pie,
     * 44=>lim_desc_renglon,
     * 50=>mod_descuento_pie,
     * 51=>mod_descuento_renglon,
     * 71=>obliga_domicilio_cliente,
     * 76=>pedido_web,
     * 84=>remito_web,
     * 96=>ver_informes_gerencia_web,
     * 99=>visualiza_clientes_todos_web,
     * 104 => alta_cliente_web 
     * 3 => acceso_ref_movstock /Todos / Seleccion
     * 5 => acceso_motivo_movstock / Todos / Seleccion
     * 38 => id_refmovstock => 2
     * 193 => ecom_seleccion_deposito_app_stock =Todos|Seleccionado
     * 194 => ecom_permiso_usr_app_stock = Todos|Carga inventario|Editar datos
     * 195 => ecom_unidad_embalaje_app_stock = Unidad|Display|Bulto ( selecciona una opcion por defecto)
     * 196 => ecom_visualizacion_stock_app_stock = Si | No
     * 
     */
     $sqlPer="SELECT psu.*, 
                usuarios.nombre_usuario
            FROM permiso_sistema_puesto AS psu
            LEFT JOIN usuarios ON psu.id_puesto = usuarios.id_puesto     
            WHERE
                psu.id_permiso_sistema IN(3,5,38,43,44,50,51,71,76,84,96,99,104,193,194,195,196)
            AND usuarios.cod_usuario='".$usuario."' 
            AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='".$pass."';";
   //echo "<br>";
   //print_r($sqlPer);
    $hacerPer= mysqli_query($conexionT,$sqlPer) or die("no puedo encontrar los permisos nuevos sistema<br>".mysqli_error($conexionT)."<pre>".$sql."</pre>");
    $textoPermisos = "\n";
    while ($p=mysqli_fetch_assoc($hacerPer)){
             //echo var_dump($p["key_permiso"]=='visualiza_clientes_todos_web');
        if($p["id_permiso_sistema"]==99){
            $textoPermisos .= "'".$p["valor_permiso"]."' AS todosClientes, \n";
        }else{
            $textoPermisos .= "'".$p["valor_permiso"]."' AS ".$p["key_permiso"].", \n";
        }
    }
    
    
    // sistema de permiso nuevos
    $sql="SELECT 
            usuarios.id_usuario,
            usuarios.CodViajante,
            usuarios.cod_usuario,
            usuarios.nombre_usuario,
            usuarios.apellido_usuario,              
            usuarios.id_puesto,  
            usuarios.id_sucursal,
            usuarios.pv,
            usuarios.pvc,
            usuarios.id_caja,
            usuarios.id_caja_cheque,
            usuarios.id_caja_tarjeta,
            usuarios.id_deposito,
            usuarios.id_punto_venta,
            usuarios.id_punto_ventac,
            usuarios.tipo_busqueda_defecto,
            puestos.puesto AS puesto,
            usuarios.permiso_supervisor_venta,
            {$textoPermisos}
            sucursales.nombre_sucursal AS sucursal,
            sucursales.salida_sin_stock,
            sucursales.agente_retib,
            sucursales.agente_retg,
            sucursales.agente_reti,
            sucursales.agente_percep,
            deposito.NombreDeposito AS deposito,
	    'Si' AS usa_viajante_cliente,
            punto_venta.nro_punto_venta AS punto_venta            
     FROM usuarios 
     LEFT JOIN puestos ON puestos.idpuesto = usuarios.id_puesto
     
     LEFT JOIN sucursales ON sucursales.id_sucursal = usuarios.id_sucursal
     LEFT JOIN deposito ON deposito.CodDeposito = usuarios.id_deposito
     LEFT JOIN punto_venta ON punto_venta.id_punto_venta = usuarios.id_punto_venta     
     WHERE usuarios.cod_usuario='".$usuario."' 
     AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='".$pass."'
     AND usuarios.vendedor_web ='Si'
     AND usuarios.baja_usuario ='No'";
    
}else{
    // sistema viejo 
    $sql="SELECT 
            usuarios.id_usuario,
            usuarios.CodViajante,
            usuarios.cod_usuario,
            usuarios.nombre_usuario,
            usuarios.apellido_usuario,              
            usuarios.id_puesto,  
            usuarios.id_sucursal,
            usuarios.pv,
            usuarios.pvc,
            usuarios.id_deposito,
            usuarios.id_punto_venta,
            usuarios.id_punto_ventac,
            usuarios.id_caja,
            usuarios.id_caja_cheque,
            usuarios.tipo_busqueda_defecto,
            puestos.puesto AS puesto,
            usuarios.permiso_supervisor_venta,
            ps.visualiza_clientes_todos_web AS todosClientes,            
            ps.lim_desc_pie,
            ps.mod_descuento_pie,
            ps.lim_desc_renglon,
            ps.mod_descuento_renglon,
            ps.descuento_cv,
            ps.pedido_web,
            ps.remito_web ,
            ps.obliga_domicilio_cliente,
            ps.ver_informes_gerencia_web,
            sucursales.nombre_sucursal AS sucursal,
            sucursales.salida_sin_stock,
            sucursales.agente_retib,
            sucursales.agente_retg,
            sucursales.agente_reti,
            sucursales.agente_percep,
            deposito.NombreDeposito AS deposito,
	    'No' AS usa_viajante_cliente,
            punto_venta.nro_punto_venta AS punto_venta            
     FROM usuarios 
     LEFT JOIN puestos ON puestos.idpuesto = usuarios.id_puesto
     LEFT JOIN permisos_sistema AS ps ON ps.IDPuesto = puestos.idpuesto
     LEFT JOIN sucursales ON sucursales.id_sucursal = usuarios.id_sucursal
     LEFT JOIN deposito ON deposito.CodDeposito = usuarios.id_deposito
     LEFT JOIN punto_venta ON punto_venta.id_punto_venta = usuarios.id_punto_venta     
     WHERE usuarios.cod_usuario='".$usuario."' 
     AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='".$pass."'
     AND usuarios.vendedor_web ='Si'
     AND usuarios.baja_usuario ='No'";

}

//$sql="select * from Usuario where CodUsuario='".$_POST['txtusuario']."' and AES_DECRYPT(Clave,'a7v8xx2')='".$_POST['txtclave']."'";

//if ($result = mysqli_query($conexionT, "SELECT DATABASE()")) {
//    $row = mysqli_fetch_row($result);
//    printf("Default database is %s.\n", $row[0]);
//    mysqli_free_result($result);
//}

//print_r($sql);
//echo "<br";


$ejecutar   =   mysqli_query($conexionT,$sql) or die ("No se pudo ejecutar la consulta<br>".  mysqli_error($conexionT).'<br>'.$sql);
$hay        =   mysqli_num_rows($ejecutar);
$campo      =   mysqli_fetch_object($ejecutar);

//print_r($campo);

//echo "</pre>";
//exit();
if($hay>0){
    //si soy gerente no tengo restriccion horaria
    if($campo->ver_informes_gerencia_web=="Si"){
        $empiezaSesion = 0;
    }
    /*
     * Chapini bonafede
     */
    $arrayBonafede=array(16,27,32,35);
    if(in_array($campo->id_usuario,$arrayBonafede)){
        $empiezaSesion=0;
    }
    
    /*
     * Configuracion General
     * =========================================================================
     */
    $sqlConf = "SELECT "
            . "utiliza_embalaje,"
            . "activ_logistica,"
            . "reglas_precios,"
            . "usa_multiplica_bulto_promedio,"
            . "utiliza_display,"
            . "utiliza_bulto_cerrado,"
            . "lista_precio_web,"
            . "desc_util1,"
            . "desc_util2,"
            . "desc_util3,"
            . "desc_util4,"
            . "desc_util5"
            . " FROM configuracion WHERE id_configuracion = 1";
    $hacerConf = mysqli_query($conexionT,$sqlConf); 
            //or die("No puedo recuperar la configuracion".mysql_error());
    $permisoEmbalaje = "No";
    //permiso que permite dar de alta o modiicar datos completos de un cliente
    
    $permisoAltaCliente ="No";
    //echo var_dump(property_exists($campo,'alta_cliente_web'));
    if(property_exists($campo,'alta_cliente_web') && $campo->alta_cliente_web!=""){
      //  echo "dentro del properti existe";
      //  echo var_dump(property_exists($campo,'alta_cliente_web'));
            $permisoAltaCliente =$campo->alta_cliente_web;
    }
    //exit();
    $contactoCompleto="No";
    $arrListaPrecio=array();
    if($hacerConf){
        $conf = mysqli_fetch_assoc($hacerConf);
        $permisoEmbalaje = $conf["utiliza_embalaje"];
        $activ_logistica = $conf["activ_logistica"];
        $utilizaReglasPrecio = $conf["reglas_precios"];
        $usaBultoPromedio = $conf["usa_multiplica_bulto_promedio"];
        $usaDisplay = $conf["utiliza_display"];
        $usaBultoCerrado = $conf["utiliza_bulto_cerrado"];
        $usaIdManual ="No";
        $listaPrecioDefecto = "Lista ".$conf["lista_precio_web"]; // Lista 1...Lista 5
        $numeroListaPrecioDefecto = $conf["lista_precio_web"]; // 1...5
        $usaGeolocalizacion="No";
        // armando el array de lista de precio para trabajarlo con javascript o php
        for ($i=1;$i<=5;$i++){
            $defecto = "no";
            if($i==$numeroListaPrecioDefecto){
                $defecto = "si";
            }
            $arrListaPrecio[$i] = array("id"=>$i,"texto"=>"Lista ".$i,"nombre"=>$conf["desc_util".$i],"defecto"=>$defecto);
        
        }
        
    }else{
        // no dejo permisos
        
        $permisoEmbalaje = "No";
        $usaDisplay="No";
        $activ_logistica = "No";
        $utilizaReglasPrecio = "No";
        $usaBultoPromedio = "No";       
        $usaBultoCerrado = "No";
        $usaIdManual ="No";
        $listaPrecioDefecto="Lista 1";
        $numeroListaPrecioDefecto = 1; // 1...5
        $arrListaPrecio[1] = array("id"=>1,"texto"=>"Lista 1","nombre"=>"Lista 1","defecto"=>"si");
        $usaGeolocalizacion="No";
    }
    
    //forzar campos de id.

    // $usaIdManual="No";
    $usaDomClienteInf=0;
    $usaGeolocalizacion="No";
    $verStockCero ="Si";
    $verTodosClientes = "No";

    // * analisis del permiso de todos los clientes
    if(property_exists($campo,'todosClientes')){
        $verTodosClientes=$campo->todosClientes;        
   
    }
    // forzado
    // $verTodosClientes="No";

    /*
     * DATOS MAIL usuario vendedor
     * =========================================================================
     */
    
    $sqlCo="SELECT id_usuario,
                    nombre_servidor_smtp,
                    nombre_servidor_pop3,
                    puerto_servidor_smtp,
                    puerto_servidor_pop,
                    nombre_usuario,
                    pass_usuario,
                    firma_mensaje 
                    FROM correo_usr WHERE correo_usr.id_usuario=".$campo->id_usuario;
    $hacerm=mysqli_query($conexionT,$sqlCo)or die("no puedo recuperar los datos de mail<pre>".mysqli_error($conexionT)."<br>".$sqlCo."</pre>");
    $arrCo=mysqli_fetch_assoc($hacerm);
    
    
    /*
     * DATOS DE EMPRESA
     * =========================================================================
     */
        $sqlEmpresa = "SELECT Nombre,
                            Telefono,
                            Cuit,
                            Domicilio,
                            Email,
                            IngBrutos,
                            InicioAct,
                            contribuyentes.IVA,
                            whatsapp,
                            facebook_messenger,
                            twitter,
                            direccion_web,
                            observaciones,
                            url_ecommerce_cliente,
                            url_ecommerce_vendedor
                           
                      FROM datosempresa
                      LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                        WHERE id_empresa=1";
        $hacerEmpresa = mysqli_query($conexionT,$sqlEmpresa) 
                                            or die(
                                                    'No puedo recuperar los datos de la empresa'. mysqli_error($conexionT).'<br>'.$sqlEmpresa
                                                    );
        $empresa = mysqli_fetch_object($hacerEmpresa);
    /*
     * DATOS SUCURSAL 
     */    
        
        $sqlSucursal = "SELECT cant_renglon_venta FROM sucursales WHERE id_sucursal=1";
        $hacerSucursal = mysqli_query($conexionT,$sqlSucursal) or die("No me puedo conectar con la sucursal <br>" . mysqli_error($conexionT));
        $sucursal = mysqli_fetch_object($hacerSucursal);
    /*
     * DATOS PUNTO DE VENTA
     */
        
        $sqlPunVta = "SELECT 
                        pv.id_punto_venta, 
                        pv.nro_punto_venta,
                        pv.cont 
                        FROM punto_venta_usr AS pvu
                        LEFT JOIN punto_venta As pv  ON pv.id_punto_venta=pvu.id_pv 
                        WHERE pvu.id_usuario=".$campo->id_usuario." ORDER BY pv.nro_punto_venta ASC";
        $hacerPv = mysqli_query($conexionT,$sqlPunVta) or die("error con el punto de venta".mysqli_error($conexionT));
        
        // Listado del punto de venta p jcart
        $listaPvOpc = '';
        $listaPv ='<select name="jcart-suc" id="jcart-suc">'."\n";
        $arrPv = array();
        while($pv = mysqli_fetch_assoc($hacerPv)){
            $arrPv[]=$pv;
            $selected='';
            if($campo->id_punto_venta==$pv["id_punto_venta"]){
                $selected = ' selected="selected"';
            }
            $listaPv .= '<option '. $selected .' value="'.$pv["id_punto_venta"]. '|' 
                    . $pv["nro_punto_venta"].'"> '
                    . $pv["nro_punto_venta"].' </option>'."\n";
            
            $listaPvOpc .='<option value="'.$pv["id_punto_venta"].'|'. $pv["nro_punto_venta"] .'"> '
                    . str_pad($pv["nro_punto_venta"],2,"0",STR_PAD_LEFT).' </option>'."\n";

        }
        $listaPv .='</select>';
        
    // Listado de puntos de venta para informes.
        // permiso para ver todas las fcaturas por punto
        // pasar a session y a recibo. si no esxiste x defecto es no.
        $listarPuntoDeVenta="No";
        
    /*
     * CONFIGURO DISPOSITIVO
     */    
        require_once 'api/Mobile_Detect.php';
        $detect = new Mobile_Detect;
        if($detect->isMobile()||$detect->isTablet()){
            $deviceType ="tablet";
        }else{
            $deviceType ="computer";
        }
        //$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
        $caminoDisp = "";
        
        //$deviceType="c";
        $usaZoom = 0;
        $cantMasVendidos = 4;
        
        if($deviceType!="computer"){
            $caminoDisp = "tmobile/";
            
        }
        
//        if($dispositivo=="pc"){
//            // Es PC
//            $caminoDisp = "";
//        }else{
//            //Es MOVIL 
//            $caminoDisp = "tmobile/";
//            
//        }

        
        /*
         * IVA incluido
         */
        
        $ivaIncluido ='no';
        
        /**
         * Fecha Entrega con 48 hs o 2 dias
         * ================================
         */
        $arrDiaNoLaborable =array(7);
        $cantDiasEntrega=2;
        
        /*
         * Supervisor de Ventas
         * ======================================
         * puesto que permite tener vendedores a cargo sobre los cuales consultar
         *  sobre sus ventas
         */
        $arrVendaCargo = array();
       if($campo->permiso_supervisor_venta=="Si"){

            // ibero
            /*
           switch($campo->id_usuario){
               case 37:
                   // laura
                    $arrVendaCargo=array(31,17,18,20,22);
               break;
               case 38:
                   //diego
                   $arrVendaCargo =array(33,14,11,19,12);
                   break;
               case 39:
                   // alejandro
                   $arrVendaCargo = array(32,21,16,13,15);
                   
                   break;
           }*/
           
           // chapini
           switch($campo->id_usuario){
            case 16:
                // cbonafede
                 $arrVendaCargo=array(10,49,46,54); // cod viajantes.
            break;
            
        }
       }
        

        // bloque limito clientes a solo los clientes que coloco ena la tabla vendedor_cliente.
        $permisoUsoVendedoCliente ='No';
        $permisoUsoVendedoMarca ='No';
        $listaClientesVendedor = array();
        $listaArticulosVendedor = array();
        if ( mysqli_num_rows(mysqli_query($conexionT,"SHOW TABLES LIKE 'vendedor_cliente'")) == 1 ){ 
            $permisoUsoVendedoCliente ='Si' ;
            // la tabla esta pero si esta tengo que buscar con mi codviajante si estoy en esa lista,
            // si estoy en la lista recuperar los codigos de clientes que puedo vender.
            // sin no estoy en la lista puedo vender a todos los clientes. no filtro.
        } 
        // bloque limite articulos a solo los articulos que estan en vendedor_marca 
        if ( mysqli_num_rows(mysqli_query($conexionT,"SHOW TABLES LIKE 'vendedor_marca'")) == 1 ){ 
            $permisoUsoVendedoMarca ='Si' ;
            // la tabla esta pero si esta tengo que buscar con mi codviajante si estoy en esa lista,
            // si estoy en la lista recuperar los codigos de articulos que puedo vender.
            // sin no estoy en la lista puedo vender a todos los articulos. no filtro.
        }        

        // para testing voy a forzar los codigos de clientes y articulos que puede vender el viajante.
        $permisoUsoVendedoCliente ='Si';
        $permisoUsoVendedoMarca ='Si';
        $listaClientesVendedor = array(14,15,16,17,18,19,20,21,22,23,24);
        $listaArticulosVendedor = array(8,9,10,11,12);        

       /*
        * Busqueda rapida de clientes , luego articulos 
        */
       $whereC="";
       $campoId = "";

        if($verTodosClientes=='No'){
            $whereC .= " AND cliente.CodViajante =" .$campo->CodViajante;        
       
        }

        if($usaIdManual=='Si'){
            $campoId = " COALESCE(cliente.id_manual_cli,cliente.Codigo)";
            
        }
        
        if($usaIdManual=='No'){
            $campoId = "cliente.Codigo";
            
        } 

        // si tengo el permiso de vendeor cliente activo lo piso.  si tengo permiso de ver todos clientes ...no aplica     
        // echo '<pre>';
        // echo 'permiso ';
        // var_dump($permisoUsoVendedoCliente);
        // echo 'verTodosClientes ';
        // var_dump($verTodosClientes);  
        // echo '</pre>';
        // tiene mal seteado el permiso lo configuro asi por testing
        // if($permisoUsoVendedoCliente=='Si' && $verTodosClientes=='No'){ 
       if($permisoUsoVendedoCliente=='Si'){
            // piso el filtro.
            $whereC = " AND cliente.Codigo IN (" . implode(',', $listaClientesVendedor) . ")";
        }

        $sqlClientes= "SELECT 
                            {$campoId} AS codigo,
                            CONCAT(LTRIM(cliente.nombre_cliente), ' Cod: ',{$campoId}) AS nombre,
                            cliente.codigo AS id

                        FROM cliente 
                        
                        WHERE
                            cliente.Codigo <> 1
                            AND cliente.Estado='Activo'                            
                            {$whereC}                        

                        ORDER BY cliente.nombre_cliente";

// echo '<pre>',$sqlClientes,'</pre>';  
// exit;                  
        $hacerCli = mysqli_query($conexionT,$sqlClientes) or die('No puedo ubicar el busqueda rapida Cliente.'.  mysqli_error($conexionT) .'<br>'.$sqlClientes);
        $cRapido=array();
        while($cli=mysqli_fetch_assoc($hacerCli)){
            //  $cRapido[]=$cli["cliente"];
             $cRapido[]=$cli;


        }

        $arrArtRapido = array();
        $whereArticuloRapido="";
        if($permisoUsoVendedoMarca=='Si'){
            $whereArticuloRapido = " AND articulo.IDArt IN (" . implode(',', $listaArticulosVendedor) . ")";
        }
        
        if($usaIdManual=='Si'){
            $sqlArticuloRapido="SELECT
                       IF(ISNULL(articulo.id_manual)OR articulo.id_manual='',articulo.IDArt,articulo.id_manual) AS codigo, 
                      
                                   
                      IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
                          CONCAT(ecom.nombre_articulo_ecom,' Cod: ',IF(ISNULL(articulo.id_manual)OR articulo.id_manual='',articulo.IDArt,articulo.id_manual)),
                          CONCAT(articulo.NombreArticulo,' Cod: ',IF(ISNULL(articulo.id_manual)OR articulo.id_manual='',articulo.IDArt,articulo.id_manual))
                         ) AS nombre ,
                      articulo.IDArt AS id  
                       
                   FROM articulo                     
            
                   LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt
                   LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                   LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                   WHERE 
                   articulo.tipo_art='Articulo'     
                   AND articulo.Discontinuo='No'    
                   AND articulo.disponible_vta='Si'
                   {$whereArticuloRapido}                   
                  
            
                   ORDER BY articulo.NombreArticulo ASC";
        }

       if($usaIdManual=='No'){
               $sqlArticuloRapido = "SELECT
                               articulo.IDArt AS codigo,
                       
                               IF(NOT ISNULL(ecom.nombre_articulo_ecom) AND ecom.nombre_articulo_ecom<>'',
                                   CONCAT(ecom.nombre_articulo_ecom,' Cod: ',articulo.IDArt),
                                   CONCAT(articulo.NombreArticulo,' Cod: ',articulo.IDArt)) AS nombre,
                                   articulo.IDArt AS id 
                               
                           FROM articulo 
                       
                           LEFT JOIN ecom_info_articulo AS ecom ON ecom.id_articulo=articulo.IDArt 
                           LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                           LEFT JOIN rubro_categoria AS cat ON cat.id_categoria = rubro.id_categoria
                           WHERE 
                           articulo.tipo_art='Articulo'     
                           AND articulo.Discontinuo='No' 
                           AND articulo.disponible_vta='Si'   
                           {$whereArticuloRapido}
                          
                           ORDER BY articulo.NombreArticulo ASC";
           }
// echo '<pre>',$sqlArticuloRapido,'</pre>';
           $hacerProd = mysqli_query($conexionT,$sqlArticuloRapido) or die('No puedo ubicar el busqueda rapida Producto.'.  mysqli_error($conexionT) .'<br>'.$sqlArticuloRapido);
          
           while($prod=mysqli_fetch_assoc($hacerProd)){
               $arrArtRapido[]=$prod;
           }
        /* 
         * Impuesto Interno compatibilidad 
         * 
         * */     
         
        $impuestoInternoAbm='No';
        if ( mysqli_num_rows(mysqli_query($conexionT,"SHOW COLUMNS FROM articulo LIKE 'id_impuesto_interno_abm'")) == 1 ){ $impuestoInternoAbm ='Si' ;}
         
        // automatico si existen productos con laboratorio activo.
         
        $usaLaboratorio="no";
        
        // * recuperar el logo en base 64
        
        $logoBase64 = "";

        $rutaLogo = "_img/logo_" .$empresa->Cuit . ".jpg";
        $rutaLogoFijo = "_img/logo-administranet-ecommerce.png";
        // hay logo creado.   
        if (file_exists($rutaLogo)) {
            $imageData = file_get_contents($rutaLogo);
            $bin64 = base64_encode($imageData);
            $logoBase64 = 'data:image/png;base64,' . $bin64;
        }
        
        // logo standard
        if(!file_exists($rutaLogo)){
            $imageData = file_get_contents($rutaLogoFijo);
            $bin64 = base64_encode($imageData);
            $logoBase64 = 'data:image/png;base64,' . $bin64;
        }


         
	/*
     * SESION ECOMMERCE
     */
        session_start();
        session_destroy();
        unset($_SESSION);
        session_start();    
        //  inicializo las sesiones

        $_SESSION['id_sesion']         = session_id();
        $_SESSION['usuario']            = $campo->cod_usuario;
        // datos de la empresa
        $_SESSION['nombre_empresa']     = $empresa->Nombre;
        $_SESSION['telefono_empresa']   = $empresa->Telefono;
        $_SESSION['cuit_empresa']       = $empresa->Cuit;
        $_SESSION['domicilio_empresa']  = $empresa->Domicilio;
        $_SESSION['email_empresa']      = $empresa->Email;
        $_SESSION['ingbrutos_empresa']  = $empresa->IngBrutos;
        $_SESSION['iniact_empresa']     = $empresa->InicioAct;
        $_SESSION['iva_empresa']        = $empresa->IVA;
        $_SESSION['whatsapp_empresa'] =                 $empresa->whatsapp;
        $_SESSION['facebook_messenger_empresa'] =       $empresa->facebook_messenger;
        $_SESSION['twitter_empresa'] =                  $empresa->twitter;
        $_SESSION['direccion_web_empresa'] =            $empresa->direccion_web;
        $_SESSION['observaciones_empresa'] =            $empresa->observaciones;
        $_SESSION['url_ecommerce_cliente_empresa'] =    $empresa->url_ecommerce_cliente;
        $_SESSION['url_ecommerce_vendedor_empresa'] =   $empresa->url_ecommerce_vendedor;
        // permisos
        $_SESSION['agente_retib']       = $campo->agente_retib;
        $_SESSION['agente_retg']        = $campo->agente_retg;
        $_SESSION['agente_reti']        = $campo->agente_reti;
        $_SESSION['agente_percep']      = $campo->agente_percep;
        $_SESSION['apenom']             = $campo->nombre_usuario .' ' .$campo->apellido_usuario;
        $_SESSION['idusuario']          = $campo->id_usuario;
        $_SESSION['tipo_busqueda']      = $campo->tipo_busqueda_defecto;
        // permisos de stock y disponible.
        // ---------------------------------------------------------------------
        $_SESSION['venta_sin_stock']    = $campo->salida_sin_stock;    
        // $_SESSION['venta_sin_stock']    = 'No';  // me deja vender sin stock 
        $_SESSION['comoValidoSaldo'] = 'stock'; // valido por stock o por disponible la venta.

        $_SESSION["verStock"]="Si"; // muestro el stock
        $_SESSION["verDisponible"]="Si"; // muestro el disponible

        $_SESSION["verStockCero"] = $verStockCero; // visualizo zolo porudctos que tengan stock.
        // ---------------------------------------------------------------------
        $_SESSION['id_sucursal']        = $campo->id_sucursal;
        $_SESSION['id_caja_efectivo_usr'] = $campo->id_caja;
        $_SESSION['id_caja_cheque_usr']   = $campo->id_caja_cheque;
        $_SESSION['id_caja_tarjeta']      = $campo->id_caja_tarjeta;
        
        $_SESSION['obliga_domicilio_cliente'] = $campo->obliga_domicilio_cliente;
        // $_SESSION['todos_clientes']     = $campo->todosClientes;
        $_SESSION['todos_clientes']     = $verTodosClientes;
        $_SESSION['vendedor']           = $campo;
        $_SESSION['tipousuario']        = 'vendedor';
        $_SESSION['baseConecto']        = $baseConecto;
        $_SESSION['servidor']           = servidor_db;
        $_SESSION['puerto_db']          = puerto_db;
        $_SESSION['limite_renglon']     = $sucursal->cant_renglon_venta;
        $_SESSION['deposito']           = $campo->id_deposito;
        $_SESSION['lista_pv']           = $listaPv;
        $_SESSION['lista_pv_opc']       = $listaPvOpc;
        $_SESSION['puntos_de_venta_usr'] = $arrPv;
        $_SESSION['caminoDisp']         = $caminoDisp;
        $_SESSION['utilizaEmbalaje']    = $permisoEmbalaje;

        $_SESSION['utiliza_display']      = $usaDisplay;
        $_SESSION['utiliza_bulto_cerrado']      = $usaBultoCerrado;

        $_SESSION['usaRemito']          = $campo->remito_web; 
        
        $_SESSION['ivaIncluido']        = $ivaIncluido;
        $_SESSION['totalCarrito']       = 0;
        $_SESSION['inf_gerenciales']    = $campo->ver_informes_gerencia_web;
        $_SESSION['activ_logistica']    = $activ_logistica;
        $_SESSION['usaReglaPrecio']     = $utilizaReglasPrecio;
        $_SESSION['utiliza_control_horario'] = $controlHorario;
        $_SESSION["uso_bulto_promedio"] = $usaBultoPromedio;

        $_SESSION["arr_dias_no_laborables"] = $arrDiaNoLaborable;
        $_SESSION["cant_dias_entrega"] = $cantDiasEntrega;
        $_SESSION["usa_id_manual"] = $usaIdManual;
        $_SESSION["usa_domicilio_cliente_informes"] = $usaDomClienteInf;
        $_SESSION["supervisor_venta"] = $campo->permiso_supervisor_venta;
        $_SESSION["vendedor_a_cargo"] = $arrVendaCargo;
        $_SESSION["lista_precio_defecto"] = $listaPrecioDefecto; // Lista 1
        $_SESSION["numero_lista_precio_defecto"] = $numeroListaPrecioDefecto; // 1
        $_SESSION["arr_lista_precio"] = $arrListaPrecio; // id,texto,nombre,defecto
        $_SESSION["permiso_alta_cliente"]=$permisoAltaCliente;
        $_SESSION["contacto_completo"]=$contactoCompleto;
        $_SESSION["id_bd"] = $idEmpresa;
        $_SESSION["correo"] = $arrCo;
	    $_SESSION['usa_viajante_cliente']= $campo->usa_viajante_cliente;
        // permisos de inventario / stock
        
        $_SESSION["seleccion_deposito_inventario"]=$campo->ecom_seleccion_deposito_app_stock; // me deja ver los depositos y cambiarlo
        $_SESSION["accion_inventario"]=$campo->ecom_permiso_usr_app_stock; // puedo hacer todo, o solo contar o solo cambiar fotos y cod  barra
        $_SESSION["tipo_cuenta_defecto"]=$campo->ecom_unidad_embalaje_app_stock; // la forma predeterminada de contar, si por unidad, displya o  bulto
        $_SESSION["visualiza_stock_inventario"]=$campo->ecom_visualizacion_stock_app_stock; // si veo el stock cuando cuento para evitar fraudes

        $_SESSION["clienteRapido"] = json_encode($cRapido);
        $_SESSION["productoRapido"] = json_encode($arrArtRapido);
        $_SESSION["usa_laboratorio"] = $usaLaboratorio;
        $_SESSION["usa_geolocalizacion"]=$usaGeolocalizacion;
        $_SESSION["usa_impuesto_interno_abm"] = $impuestoInternoAbm;
        $_SESSION["vista_producto"] = $vistaProductos;
        $_SESSION["listar_facturas_punto_venta"] = $listarPuntoDeVenta; // si filtro las facturas de recibo x el punto de venta.
        $_SESSION["logo_base_64"] = $logoBase64;
		// buscar y agregar los permisos de cambio lista de preciosy listas de precios de los cambios.

        //activacion de modulos 
        // inventario
        $_SESSION['modulo_inventario'] = $mod_inventario;
        // premios
        $_SESSION['modulo_premios'] = $mod_premios;
        // infomres gerenciales full
        // informes gerenciales ventas y cobranzas.

        $_SESSION['tipoCliente'] = '';

        // filtro clientes por viajante fijos
        // filtro articulos por marca viajante fijos.
        $_SESSION['permiso_uso_vendedor_cliente'] = $permisoUsoVendedoCliente;
        $_SESSION['permiso_uso_vendedor_marca'] = $permisoUsoVendedoMarca;
        $_SESSION['lista_clientes_vendedor'] = $listaClientesVendedor;
        $_SESSION['lista_articulos_vendedor'] = $listaArticulosVendedor;

        
        //header('Location: escritorio.php');
}else{
      // header('Location: index.php?cartel=2');
    $cartel=2;
    $empiezaSesion++;
}
//echo var_dump($empiezaSesion);
//
if($empiezaSesion==0){
   header('Location: escritorio.php');
}else{
    header('Location: index.php?cartel='.$cartel);
}