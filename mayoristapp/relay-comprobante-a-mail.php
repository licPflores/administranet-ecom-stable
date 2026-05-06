<?php
require_once 'sesion.inc.php';
/*
 * obtengo el codigo de movimiento del comprobante solo con ese dato
 * recupero datos del cliente y datos del comprobante para recuperar.
 */
include_once '_scripts/php/funciones.php';


if(isset($_GET["codMov"])){
    $codMov=$_GET["codMov"];
    $tipoComp=$_GET["tipocomprobante"];
    
    
    // comprobantes desde la cuentacliente
    // FA FB FC REC NCA NCB NDA NDB 
    if($tipoComp==1){
        $query = "SELECT 
                        cc.TipoComprobante ,
                        cc.NroComprobante,
                        IF(ISNULL(cc.ImporteVenta),cc.ImporteCobro,cc.ImporteVenta) AS total,
                        cc.Codigo
                        #cli.nombre_cliente,                   
                        #cli.nombre_cliente AS cliente,
                        #cli.IDIVA,
                        #cli.Codigo,
                        #cli.Estado,
                        #cli.telefono,
                        #cli.listaPrecio,
                        #cli.Email As email,
                        #cli.EmailContacto AS emailContacto,
                        #cli.id_manual_cli,
                        #Tipo_Cliente.Nombretipocliente AS NombTC, 
                        #Contribuyentes.Abreviado AS IVA 
                FROM cuentacliente AS cc
                #LEFT JOIN cliente AS cli ON cli.Codigo=cc.Codigo 
                #LEFT JOIN tipo_cliente ON cli.TipoCliente = tipo_cliente.idTipoCliente
                #LEFT JOIN contribuyentes ON contribuyentes.idIVA = cli.idIVA
                WHERE
                cc.CodigoMovimiento=".$codMov;
    }
    
    // comprobantes desde la comp_ped
    // PED REM DEV
    if($tipoComp==0){
        $query = "SELECT 
                        cc.TipoComprobante,
                        cc.NroComprobante,
                        cc.ImporteVenta AS total,
                        #cli.nombre_cliente,                   
                        #cli.nombre_cliente AS cliente,
                        cc.Codigo 
                        #cli.IDIVA,
                        #cli.Codigo,
                        #cli.Estado,
                        #cli.telefono,
                        #cli.listaPrecio,
                        #cli.Email As email,
                        #cli.EmailContacto AS emailContacto,
                        #cli.id_manual_cli,
                        #Tipo_Cliente.Nombretipocliente AS NombTC, 
                        #Contribuyentes.Abreviado AS IVA 
                FROM comp_ped AS cc
                #LEFT JOIN cliente AS cli ON cli.Codigo=cc.Codigo 
                #LEFT JOIN tipo_cliente ON cli.TipoCliente = tipo_cliente.idTipoCliente
                #LEFT JOIN contribuyentes ON contribuyentes.idIVA = cli.idIVA
                WHERE
                cc.CodigoMovimiento=".$codMov;
    }
                            
    $hacer = mysqli_query($connV,$query) or die('No puedo ubicar el comprobante.'.  mysqli_error($connV) .'<br>'.$query);
    //obtener los campos crear dos variables de sesiones y pasar al fin de comprobante.
    if($hacer){
        $comp=mysqli_fetch_assoc($hacer);       
        
        

        $parametros = array(
            'numerocomprobante' =>$comp["NroComprobante"],
            'tipocomprobante'   =>$comp["TipoComprobante"],
            'codigomovimiento'  =>$codMov,
            'codigo'            =>$comp['Codigo']
        );
        
        $parametrosBase64 = base64url_encode(serialize($parametros));           

//        echo "<pre>";
//    print_r($comp);
//        echo "</pre>";
        
        header('Location: fin-comprobante.php?p='.$parametrosBase64);
        
    }else{
        echo "no se encontro el comprobante";
        
    }
}