<?php

ini_set('error_reporting', E_ALL);

ini_set('display_errors', 1);

session_start();

# ingreso desde gerencia.php
if (isset($_SESSION["queServidor"])) {
    $queServidor = $_SESSION["queServidor"];
}

require_once 'conexion-vendedor.inc.php';
$usuario = mysqli_real_escape_string($conexionT, $_POST["usuario"]);
$pass = mysqli_real_escape_string($conexionT, $_POST["clave"]);

$controlHorario = "no";
$empiezaSesion = 0;


/*control de hora*/
if ($controlHorario == "si") {
    $sqlHora = "SELECT CURTIME()as horaControl;";
    $hcontrol = mysqli_query($conexionT, $sqlHora) or die("No puedo controlar la hora" . mysqli_error($conexionT));
    $hora = mysqli_fetch_assoc($hcontrol);

    //    $desdeHora=mktime(07,00,00);
    $desdeHora = mktime(07, 00, 00);
    $hastaHora = mktime(20, 30, 00);

    $hh =  explode(":", $hora["horaControl"]);
    $horahora = mktime($hh[0], $hh[1], $hh[2]);
    //    var_dump($horahora<=$desdeHora||$horahora>=$hastaHora);
    if ($horahora <= $desdeHora || $horahora >= $hastaHora) {
        // no ingreso restriccion horaria.
        $cartel = 3;
        $empiezaSesion++;
    }
}

//controlo version de permisos, consulto por la nueva.

$sqlControl = "SHOW TABLES LIKE 'permiso_sistema_puesto';";
$hacerP = mysqli_query($conexionT, $sqlControl) or die("No se pudo ejecutar la consulta<br>" .  mysqli_error($conexionT) . '<br>' . $sql);
$rs = mysqli_fetch_assoc($hacerP);
//echo "<pre>";/
//print_r($rs);

if (!empty($rs)) {
    // las tablas de permiso nuevas existen.
    $sqlPer = "SELECT psu.*, 
                usuarios.nombre_usuario
            FROM permiso_sistema_puesto AS psu
            LEFT JOIN usuarios ON psu.id_puesto = usuarios.id_puesto     
            WHERE
                psu.id_permiso_sistema IN(43,44,50,51,71,76,84,96,99)
            AND usuarios.cod_usuario='" . $usuario . "' 
            AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='" . $pass . "';";

    $hacerPer = mysqli_query($conexionT, $sqlPer) or die("no puedo encontrar los permisos nuevos sistema<br>" . mysqli_error($conexionT) . "<pre>" . $sql . "</pre>");
    $textoPermisos = "\n";
    while ($p = mysqli_fetch_assoc($hacerPer)) {
        //echo var_dump($p["key_permiso"]=='visualiza_clientes_todos_web');
        if ($p["id_permiso_sistema"] == 99) {
            $textoPermisos .= "'" . $p["valor_permiso"] . "' AS todosClientes, \n";
        } else {
            $textoPermisos .= "'" . $p["valor_permiso"] . "' AS " . $p["key_permiso"] . ", \n";
        }
    }
    // sistema de permiso nuevos
    $sql = "SELECT 
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
            punto_venta.nro_punto_venta AS punto_venta            
     FROM usuarios 
     LEFT JOIN puestos ON puestos.idpuesto = usuarios.id_puesto
     
     LEFT JOIN sucursales ON sucursales.id_sucursal = usuarios.id_sucursal
     LEFT JOIN deposito ON deposito.CodDeposito = usuarios.id_deposito
     LEFT JOIN punto_venta ON punto_venta.id_punto_venta = usuarios.id_punto_venta     
     WHERE usuarios.cod_usuario='" . $usuario . "' 
     AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='" . $pass . "'
     AND usuarios.vendedor_web ='Si'
     AND usuarios.baja_usuario ='No'";
} else {
    // sistema viejo 
    $sql = "SELECT 
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
            punto_venta.nro_punto_venta AS punto_venta            
     FROM usuarios 
     LEFT JOIN puestos ON puestos.idpuesto = usuarios.id_puesto
     LEFT JOIN permisos_sistema AS ps ON ps.IDPuesto = puestos.idpuesto
     LEFT JOIN sucursales ON sucursales.id_sucursal = usuarios.id_sucursal
     LEFT JOIN deposito ON deposito.CodDeposito = usuarios.id_deposito
     LEFT JOIN punto_venta ON punto_venta.id_punto_venta = usuarios.id_punto_venta     
     WHERE usuarios.cod_usuario='" . $usuario . "' 
     AND AES_DECRYPT(usuarios.password_usuario,'a7v8xx2')='" . $pass . "'
     AND usuarios.vendedor_web ='Si'
     AND usuarios.baja_usuario ='No'";
}

//$sql="select * from Usuario where CodUsuario='".$_POST['txtusuario']."' and AES_DECRYPT(Clave,'a7v8xx2')='".$_POST['txtclave']."'";

//if ($result = mysqli_query($conexionT, "SELECT DATABASE()")) {
//    $row = mysqli_fetch_row($result);
//    printf("Default database is %s.\n", $row[0]);
//    mysqli_free_result($result);
//}


$ejecutar   =   mysqli_query($conexionT, $sql) or die("No se pudo ejecutar la consulta<br>" .  mysqli_error($conexionT) . '<br>' . $sql);
$hay        =   mysqli_num_rows($ejecutar);
$campo      =   mysqli_fetch_object($ejecutar);
//echo "<pre>";
//print_r($sql);
//echo "<br>";
//print_r($campo);
//echo "</pre>";//
//exit();
if ($hay > 0) {
    //si soy gerente no tengo restriccion horaria
    if ($campo->ver_informes_gerencia_web == "Si") {
        $empiezaSesion = 0;
    }
    /*
     * Chapini bonafede
     */
    $arrayBonafede = array(16, 27, 32, 35);
    if (in_array($campo->id_usuario, $arrayBonafede)) {
        $empiezaSesion = 0;
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
        . "lista_precio_web"
        . " FROM configuracion WHERE id_configuracion = 1";
    $hacerConf = mysqli_query($conexionT, $sqlConf);
    //or die("No puedo recuperar la configuracion".mysql_error());
    $permisoEmbalaje = "No";
    //permiso que permite dar de alta o modiicar datos completos de un cliente

    $permisoAltaCliente = "No";
    $contactoCompleto = "No";
    if ($hacerConf) {
        $conf = mysqli_fetch_assoc($hacerConf);
        $permisoEmbalaje = $conf["utiliza_embalaje"];
        $activ_logistica = $conf["activ_logistica"];
        $utilizaReglasPrecio = $conf["reglas_precios"];
        $usaBultoPromedio = $conf["usa_multiplica_bulto_promedio"];
        $usaIdManual = "No";
        $listaPrecioDefecto = "Lista " . $conf["lista_precio_web"];
        if (isset($conf["alta_cliente_web"]) && $conf["alta_cliente_web"] != "") {
            $permisoAltaCliente = $conf["alta_cliente_web"];
        }
    } else {
        // no dejo permisos

        $permisoEmbalaje = "No";
        $activ_logistica = "No";
        $utilizaReglasPrecio = "No";
        $usaBultoPromedio = "No";
        $usaIdManual = "No";
        $listaPrecioDefecto = "Lista 1";
        $permisoAltaCliente = "No";
    }

    /*
     * DATOS MAIL usuario vendedor
     * =========================================================================
     */

    $sqlCo = "SELECT id_usuario,
                    nombre_servidor_smtp,
                    nombre_servidor_pop3,
                    puerto_servidor_smtp,
                    puerto_servidor_pop,
                    nombre_usuario,
                    pass_usuario,
                    firma_mensaje 
                    FROM correo_usr WHERE correo_usr.id_usuario=" . $campo->id_usuario;
    $hacerm = mysqli_query($conexionT, $sqlCo) or die("no puedo recuperar los datos de mail<pre>" . mysqli_error($conexionT) . "<br>" . $sqlCo . "</pre>");
    $arrCo = mysqli_fetch_assoc($hacerm);


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
                            contribuyentes.IVA
                           
                      FROM datosempresa
                      LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                        WHERE id_empresa=1";
    $hacerEmpresa = mysqli_query($conexionT, $sqlEmpresa)
        or die('No puedo recuperar los datos de la empresa' . mysqli_error($conexionT) . '<br>' . $sqlEmpresa);
    $empresa = mysqli_fetch_object($hacerEmpresa);
    /*
     * DATOS SUCURSAL 
     */

    $sqlSucursal = "SELECT cant_renglon_venta FROM sucursales WHERE id_sucursal=1";
    $hacerSucursal = mysqli_query($conexionT, $sqlSucursal) or die("No me puedo conectar con la sucursal <br>" . mysqli_error($conexionT));
    $sucursal = mysqli_fetch_object($hacerSucursal);
    /*
     * DATOS PUNTO DE VENTA
     */

    $sqlPunVta = "SELECT id_punto_venta, nro_punto_venta "
        . "FROM punto_venta "
        . "WHERE id_sucursal=" . $campo->id_sucursal;
    $hacerPv = mysqli_query($conexionT, $sqlPunVta) or die("error con el punto de venta" . mysqli_error($conexionT));

    // Listado del punto de venta p jcart
    $listaPvOpc = '';
    $listaPv = '<select name="jcart-suc" id="jcart-suc">' . "\n";
    while ($pv = mysqli_fetch_assoc($hacerPv)) {
        $selected = '';
        if ($campo->id_punto_venta == $pv["id_punto_venta"]) {
            $selected = ' selected="selected"';
        }
        $listaPv .= '<option ' . $selected . ' value="' . $pv["id_punto_venta"] . '|'
            . $pv["nro_punto_venta"] . '"> '
            . $pv["nro_punto_venta"] . ' </option>' . "\n";

        $listaPvOpc .= '<option value="' . $pv["id_punto_venta"] . '|' . $pv["nro_punto_venta"] . '"> '
            . $pv["nro_punto_venta"] . ' </option>' . "\n";
    }
    $listaPv .= '</select>';

    // Listado de puntos de venta para informes.


    /*
     * CONFIGURO DISPOSITIVO
     */
    require_once 'api/Mobile_Detect.php';
    $detect = new Mobile_Detect;
    if ($detect->isMobile() || $detect->isTablet()) {
        $deviceType = "tablet";
    } else {
        $deviceType = "computer";
    }
    //$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $caminoDisp = "";
    //$deviceType="c";
    $usaZoom = 0;
    $cantMasVendidos = 4;

    if ($deviceType != "computer") {
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
         * Colores en Rubros
          * 1 - Descartables
          * 2 - Higiene
          * 3 - Comestibles
         */
    $colorRubro[1] = 'style="border-bottom:3px solid #00A4CC"';
    $colorRubro[2] = 'style="border-bottom:3px solid #109618"';
    $colorRubro[3] = 'style="border-bottom:3px solid #FF9900"';

    /*
         * IVA incluido
         */

    $ivaIncluido = 'no';

    /**
     * Fecha Entrega con 48 hs o 2 dias
     * ================================
     */
    $arrDiaNoLaborable = array(7);
    $cantDiasEntrega = 2;

    /*
         * Supervisor de Ventas
         * ======================================
         * puesto que permite tener vendedores a cargo sobre los cuales consultar
         *  sobre sus ventas son COD VENDEDOR
         */
    $arrVendaCargo = array();
    // sitema de supervisor : JAYNA
    if ($campo->permiso_supervisor_venta == "Si" && $queServidor == 'localhost:30804') {
        switch ($campo->id_usuario) {
            case 23:
                /*$arrVendaCargo=array(39,8,27,5,23);// usuarios*/
                $arrVendaCargo = array(15, 1, 9, 7, 23);
                break;
        }
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
    $_SESSION['nombre_empresa']     = $empresa->Nombre;
    $_SESSION['telefono_empresa']   = $empresa->Telefono;
    $_SESSION['cuit_empresa']       = $empresa->Cuit;
    $_SESSION['domicilio_empresa']  = $empresa->Domicilio;
    $_SESSION['email_empresa']      = $empresa->Email;
    $_SESSION['ingbrutos_empresa']  = $empresa->IngBrutos;
    $_SESSION['iniact_empresa']     = $empresa->InicioAct;
    $_SESSION['iva_empresa']        = $empresa->IVA;
    $_SESSION['agente_retib']       = $campo->agente_retib;
    $_SESSION['agente_retg']        = $campo->agente_retg;
    $_SESSION['agente_reti']        = $campo->agente_reti;
    $_SESSION['agente_percep']      = $campo->agente_percep;
    $_SESSION['apenom']             = $campo->nombre_usuario . ' ' . $campo->apellido_usuario;
    $_SESSION['idusuario']          = $campo->id_usuario;
    $_SESSION['tipo_busqueda']      = $campo->tipo_busqueda_defecto;
    $_SESSION['venta_sin_stock']    = $campo->salida_sin_stock;
    $_SESSION['id_sucursal']        = $campo->id_sucursal;
    $_SESSION['obliga_domicilio_cliente'] = $campo->obliga_domicilio_cliente;
    //        $_SESSION['venta_sin_stock']    = 'No';
    $_SESSION['todos_clientes']     = $campo->todosClientes;
    $_SESSION['vendedor']           = $campo;
    $_SESSION['tipousuario']        = 'vendedor';
    $_SESSION['baseConecto']        = $baseConecto;
    $_SESSION['servidor']           = $servidor;
    $_SESSION['limite_renglon']     = $sucursal->cant_renglon_venta;
    $_SESSION['deposito']           = $campo->id_deposito;
    $_SESSION['lista_pv']           = $listaPv;
    $_SESSION['lista_pv_opc']       = $listaPvOpc;
    $_SESSION['caminoDisp']         = $caminoDisp;
    $_SESSION['utilizaEmbalaje']    = $permisoEmbalaje;
    $_SESSION['usaRemito']          = $campo->remito_web;
    $_SESSION['colorRubro']         = $colorRubro;
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
    $_SESSION["supervisor_venta"] = $campo->permiso_supervisor_venta;
    $_SESSION["vendedor_a_cargo"] = $arrVendaCargo;
    $_SESSION["lista_precio_defecto"] = $listaPrecioDefecto;
    $_SESSION["permiso_alta_cliente"] = $permisoAltaCliente;
    $_SESSION["contacto_completo"] = $contactoCompleto;
    $_SESSION["id_bd"] = $idEmpresa;
    $_SESSION["correo"] = $arrCo;
    $_SESSION["verStock"] = "Si";
    $_SESSION["queServidor"] = $queServidor;


    //header('Location: escritorio.php');
} else {
    // header('Location: index.php?cartel=2');
    $cartel = 2;
    $empiezaSesion++;
}
//echo var_dump($empiezaSesion);
//echo "que Servidor:".$_SESSION["queServidor"];
//
if ($empiezaSesion == 0) {
    header('Location: escritorio.php');
} else {
    //echo "<pre>";
    //	echo var_dump($conexionT);
    //	exit();
    //echo "</pre>";
    if (isset($_SESSION["queServidor"])) {
        header('Location: gerencia.php?cartel=' . $cartel);
    } else {
        header('Location: index.php?cartel=' . $cartel);
    }
}
