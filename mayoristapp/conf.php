<?php


$Conf = Array();

$Conf = [ 'Titulo' => 'Tabla' ];
$Conf["modulo"]="";
if(isset($_REQUEST["modulo"])){
    $Conf["modulo"]=$_REQUEST["modulo"];
}
//echo "<pre>";
//print_r($_REQUEST);
//echo  "</pre>";

$Conf['papa']='Nada';

// unidades




/*
 * PARTES HTML A SETEAR Con sus filtros
 * ==================================================
 * 
 * Informes [0]
 * $Conf["parte"][0]["include"] ="partes-html/agruparpor.php";
 * $Conf["parte"][0]["valores"][] = array("value"=>"Unidades","text"=>"Monto($)");
 * Unidades- Monto [1]
 * 
 * 
 */


// Agrupacion tipo de informes Segun el modulo y filtros
/*
 * VENTAS
 * =============================================================================
 */
// x ahora no lo voy a traducir llamare directamente.


/*
 * COBRANZAS
 * =============================================================================
 */
if($Conf["modulo"]=="cobranzas"){
     // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    
   
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Cobranzas por cliente",
            "valor"=>"informes-json/cobranza_lista_periodo.php",
            "motor"=>0
    ); 
/*	$Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Cobranzas por cliente",
            "valor"=>"informes-json/cobranza_lista_rec_periodo_porfecha.php",
            "motor"=>0
    );*/
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Cobranzas por vendedor",
            "valor"=>"informes-json/cobranza_lista_vendedor.php",
            "motor"=>0
    );
    
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Comprobantes a cobrar por cliente",
            "valor"=>"informes-json/cobranza_facturas_a_cobrar_cliente.php",
            "motor"=>0
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Comprobantes a cobrar por vendedor",
            "valor"=>"informes-json/cobranza_facturas_a_cobrar_vendedor.php",
            "motor"=>0
    );
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
    // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"semana","texto"=>"Semanal");
  $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual","atributo"=>'selected="selected"');
    
    
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
    
    // FECHAS
    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    
    
    // TIPO OPERACION RANGO
    
    
    
    // FILTROS 
    $Conf["parte"]["filtros"]["include"]="partes-html/filtrarpor.php";
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"cliente","texto"=>"Cliente");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"vendedor","texto"=>"Vendedor");
    
}

/*
 * COMPRAS
 * =============================================================================
 */
if($Conf["modulo"]=="compras"){
    // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Compras - Proveedor, por comprobantes",
            "valor"=>"informes-json/compras_proveedor.php",
            "atributo"=>'selected="selected"',
            "motor"=>0
    );
	
	    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Compras -Proveedor, por Registro ",
            "valor"=>"informes-json/compras_proveedor_registro.php",
            "atributo"=>'selected="selected"',
            "motor"=>0
    );
	
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Compras - Artículo por Comprobantes",
            "valor"=>"informes-json/compras_articulo.php",
            "motor"=>0
    );
    
      $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Compras - Artículo por Registro",
            "valor"=>"informes-json/compras_articulo_registro.php",
            "motor"=>0
    );
    
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
    // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"semana","texto"=>"Semanal");
  $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual","atributo"=>'selected="selected"');
    
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
    
        // FECHAS
    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    
    
    //TIPO OPERACION RANGO
 
    
    
    // FILTROS 
    $Conf["parte"]["filtros"]["include"]="partes-html/filtrarpor.php";
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"proveedor","texto"=>"Proveedor");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"articulo","texto"=>"Articulo");
}

/*
 * PAGOS
 * =============================================================================
 */
if($Conf["modulo"]=="pagos"){
    // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Pagos por proveedor",
            "valor"=>"informes-json/pagos_lista_proveedor.php",
            "atributo"=>'selected="selected"',
            "motor"=>0
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Comprobantes a pagar por proveedor",
            "valor"=>"informes-json/pagos_facturas_a_cobrar_proveedor.php",
            "motor"=>0
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Gastos por período",
            "valor"=>"informes-json/estadistica_tc_gastos.php",
            "motor"=>0
    );
    
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
    // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"semana","texto"=>"Semanal");
  $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual","atributo"=>'selected="selected"');
    
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    //
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
    
        // FECHAS
    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    
    
    
    //TIPO OPERACION RANGO
 
    
    
    // FILTROS 
    $Conf["parte"]["filtros"]["include"]="partes-html/filtrarpor.php";
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"proveedor","texto"=>"Cliente");
    //$Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"vendedor","texto"=>"Vendedor");
}

/*
 * BANCOS
 * =============================================================================
 */
if($Conf["modulo"]=="bancos"){
    // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    /// opciones
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Libro banco",
            "valor"=>"informes-json/banco_libro_banco.php",
            "atributo"=>'selected="selected"',
            "motor"=>1
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Saldo de bancos",
            "valor"=>"informes-json/estadistica_tc_saldos_banco_mensual.php",
            "motor"=>0
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Lista de cheques de terceros",
            "valor"=>"informes-json/banco_seguimiento_cheques.php",
            "motor"=>1
    );
	
    
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
   // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"semana","texto"=>"Semanal");
  $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual","atributo"=>'selected="selected"');
    
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    //
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
   
        // FECHAS
    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    
    
    //TIPO OPERACION RANGO
    
    // CUENTAS para mi es un filtro.
    $Conf["parte"]["bancos"]["include"]= "partes-html/cuentas1.php";
    
   	//$Conf["parte"]["bancos"]["include"]="partes-html/estadodecheque.php";
	//$Conf["parte"]["bancos"]["include"]="partes-html/fechabanco.php"; 
    // FILTROS 
    $Conf["parte"]["filtros"]["include"]="partes-html/filtrarpor.php";
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"cliente","texto"=>"Cliente");
    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"proveedor","texto"=>"Proveedor");
	

}

/*
 * CAJA
 * =============================================================================
 */
if($Conf["modulo"]=="caja"){
    // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Caja lista detallada",
            "valor"=>"informes-json/caja_efectivo_general.php",
            "atributo"=>'selected="selected"',
            "motor"=>1
    );
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Caja estadística",
            "valor"=>"informes-json/estadistica_tc_caja_mensual.php",
            "motor"=>0
    );
    
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
    // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario","atributo"=>'selected="selected"');
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual");
    
    
    
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
//    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
//    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
//    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
//    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    //
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
    
        // FECHAS
//    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    $Conf["parte"]["fechas"]["include"]="partes-html/fecha_rango_uno.php";
    
    //TIPO OPERACION RANGO
    // CAJAS
    //$Conf["parte"]["cajas"]["include"]="partes-html/caja1.php";
    $Conf["parte"]["cajas"]["include"]="partes-html/cmdTipo.php";
    
    // FILTROS 
//    $Conf["parte"]["filtros"]["include"]="partes-html/filtrarpor.php";
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"cliente","texto"=>"Cliente");
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"vendedor","texto"=>"Vendedor");
}

/*
 * IMPUESTOS
 * =============================================================================
 */
if($Conf["modulo"]=="impuestos"){
    // INFORMES
    
    $Conf["parte"]["informes"]["include"]="partes-html/agruparpor.php";
    $Conf["parte"]["informes"]["opciones"][]=array(
            "texto"=>"Impuestos por período",
            "valor"=>"informes-json/estadistica_tc_impuestos.php",
            "atributo"=>'selected="selected"',
            "motor"=>0
    );
    
    
    // OPC  UNIDADES MONTO
    $Conf["parte"]["unidades"]["include"]="partes-html/verinforme.php";
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"un","texto"=>"Unidades (Un)");
    //$Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"peso","texto"=>"Peso (kg)");
    $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"monto","texto"=>"Monto ($)","atributo"=>'selected="selected"');
//    if ($usoBultoPromedio == "Si"){
//        $Conf["parte"]["unidades"]["opciones"][]=array( "valor" =>"pieza","texto"=>"Pieza (Bulto)");
//    }
   
    // PERIODO DE FECHAS
    
    $Conf["parte"]["periodofechas"]["include"]="partes-html/periodo_fechas.php";
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"dia","texto"=>"Diario");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"semana","texto"=>"Semanal");
    $Conf["parte"]["periodofechas"]["opciones"][]=array( "valor" =>"mes","texto"=>"Mensual","atributo"=>'selected="selected"');
    
    //  DECIMALES
    $Conf["parte"]["decimales"]["include"]="partes-html/decimales.php";
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"0","texto"=>"No");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"1","texto"=>"1");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"2","texto"=>"2","atributo"=>'selected="selected"');
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"3","texto"=>"3");
    $Conf["parte"]["decimales"]["opciones"][]=array( "valor" =>"4","texto"=>"4");
//    $Conf["decimales"]='Si';// agrega unidades
    //
    // PUNTO DE VENTAS
    //$Conf["parte"]["puntoVenta"]["include"]="partes-html/pv.php";
    
        // FECHAS
    $Conf["parte"]["fechas"]["include"]="partes-html/fechas.php";
    
    
    //TIPO OPERACION RANGO
 
    
    
    // FILTROS 
//    $Conf["parte"]["filtros"]["include"]="partes-html/decimales.php";
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"","texto"=>"- seleccionar -");
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"cliente","texto"=>"Cliente");
//    $Conf["parte"]["filtros"]["opciones"][]=array( "valor" =>"vendedor","texto"=>"Vendedor");
}

