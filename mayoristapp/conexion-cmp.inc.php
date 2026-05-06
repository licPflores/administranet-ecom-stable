<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'conexion-general.inc.php';
$sqlBaseElegida = "SELECT 
                            base_empresa,
                            nombre_empresa,
                            id_empresa
                        FROM empresas 
                        WHERE id_empresa=".$dbid;
    $hacerElegida= mysqli_query($conexionT,$sqlBaseElegida) or die(mysqli_error($conexionT));
    
    if($hacerElegida){
        $baseEncontrada = mysqli_fetch_object($hacerElegida);
        $baseConecto = $baseEncontrada->base_empresa;
        $nombreEmpresa = $baseEncontrada->nombre_empresa;
        $idEmpresa= $baseEncontrada->id_empresa;
        //mysql_close();
        
//        mysql_connect("localhost","administranet","a7v8xx0805");
        //mysql_connect("servidor","administranet","a7v8xx0805");
        mysqli_select_db($conexionT,$baseConecto);
        mysqli_set_charset($conexionT,'utf8');
        
         $sqlEmpresa = "SELECT Nombre AS nombre_empresa,
                            Telefono AS telefono_empresa,
                            Cuit AS cuit_empresa,
                            Domicilio AS domicilio_empresa,
                            Email AS email_empresa,
                            IngBrutos AS ingbrutos_empresa,
                            InicioAct AS iniact_empresa,
                            contribuyentes.IVA AS iva_empresa,
                            whatsapp AS whatsapp_empresa,
                            facebook_messenger AS facebook_messenger_empresa,
                            twitter AS twitter_empresa,
                            direccion_web As direccion_web_empresa,
                            observaciones As observaciones_empresa,
                            url_ecommerce_cliente AS url_ecommerce_cliente_empresa,
                            url_ecommerce_vendedor AS url_ecommerce_vendedor_empresa
                           
                      FROM datosempresa
                      LEFT JOIN contribuyentes ON contribuyentes.IDIva = datosempresa.IDIva  
                        WHERE id_empresa=1";
        $hacerEmpresa = mysqli_query($conexionT,$sqlEmpresa) 
                                            or die(
                                                    'No puedo recuperar los datos de la empresa'. mysqli_error($conexionT).'<br>'.$sqlEmpresa
                                                    );
        $empresa = mysqli_fetch_object($hacerEmpresa);
       
        
    }
    
