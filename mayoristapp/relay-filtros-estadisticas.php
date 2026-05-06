<?php
/**
 * Relay de Ajax de filtros de infomes incluir las tablas que sean necesarias.
 * ========================================================================== 
 *  */

require_once 'sesion.inc.php';
/*
 * function: listado_seleccion
 * desc:    busca el total de la tabla pasado como parametro y devuelve un
 * listado de options para llenar una lista
 * @tabla: valor para saber de que tabla debo buscar los options.
 * @salida: es un texto con options.
 */
function listado_seleccion($connV,$tabla=null,$arrVendCargo=null){
    $sql = "";
    $lista ="";
    $usaIdManual = $_SESSION["usa_id_manual"];
    if (isset($_SESSION["pemiso_supervisor_venta"])){
        $supervisorVenta = $_SESSION["pemiso_supervisor_venta"];
    }else{
        $supervisorVenta = null;
    }
    switch($tabla){
        case "cliente":
            if($usaIdManual=="Si"){
                     $sql="SELECT cliente.id_manual_cli AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.id_manual_cli,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . " ORDER BY texto ASC";
            } else{
                $sql="SELECT cliente.Codigo AS valor,"
                    . " CONCAT(cliente.nombre_cliente,' (cod:',cliente.Codigo,')') AS texto "
                    . " FROM cliente"
                    . " WHERE cliente.Estado='Activo'"
                    . " ORDER BY texto ASC";
            }
            break;
        case "tipocliente":
            $sql="SELECT tipo_cliente.IDTipoCliente AS valor,"
                . " CONCAT(tipo_cliente.NombreTipoCliente,' (cod:',tipo_cliente.IDTipoCliente,')') AS texto "
                . " FROM tipo_cliente"
                . " WHERE tipo_cliente.Anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "articulo":
            if($usaIdManual=="Si"){
                $sql="SELECT articulo.id_manual AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.id_manual,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No' AND articulo.id_manual<>''"
                    . " ORDER BY texto ASC";
            }else{
                $sql="SELECT articulo.IDArt AS valor,"
                    . " CONCAT(articulo.NombreArticulo,' (cod:',articulo.IDArt,')') AS texto "
                    . " FROM articulo"
                    . " WHERE articulo.Discontinuo='No'"
                    . " ORDER BY texto ASC";
            }
            break;
        case "vendedor":
            $listaVendedor="";
            if($arrVendCargo!=null&&!empty($arrVendCargo)){
                $listaVendedor=" AND viajantes.CodViajante IN (". implode(',',$arrVendCargo).")";
            }
            
            $sql="SELECT viajantes.CodViajante AS valor,"
                . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                . " FROM viajantes"
                . " WHERE viajantes.Anulado='No'"
                . $listaVendedor    
                . " ORDER BY texto ASC";
            break;
        case "proveedor":
            $sql="SELECT proveedor.Codigo AS valor,"
                . " CONCAT(proveedor.Nombre,' (cod:',proveedor.Codigo,')') AS texto "
                . " FROM proveedor"
                . " WHERE proveedor.Estado='Activo' AND proveedor.Tipo='Mercaderias'"
                . " ORDER BY texto ASC";    
            break;
        
        case "zona":
            $sql="SELECT erp_zona.id_zona AS valor,"
                . " CONCAT(erp_zona.nombre_zona,' (cod:',erp_zona.id_zona,')') AS texto "
                . " FROM erp_zona"
                . " WHERE erp_zona.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "categoria":
            $sql="SELECT rubro_categoria.id_categoria AS valor,"
                . " CONCAT(rubro_categoria.nombre_categoria,' (cod:',rubro_categoria.id_categoria,')') AS texto "
                . " FROM rubro_categoria"
                . " WHERE rubro_categoria.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "rubro":
            $sql="SELECT rubro.CodigoRubro AS valor,"
                . " CONCAT(rubro.NombreRubro,' (cod:',rubro.CodigoRubro,')') AS texto "
                . " FROM rubro"
                . " WHERE rubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "subrubro":
            $sql="SELECT subrubro.IDSubRubro AS valor,"
                . " CONCAT(subrubro.NombreSubRubro,' (ru: ',rubro.NombreRubro,' - cod: ', subrubro.IDSubRubro ,')') AS texto "
                . " FROM subrubro "
                . " LEFT JOIN rubro ON rubro.CodigoRubro = subrubro.CodigoRubro "
                . " WHERE subrubro.anulado='No'"
                . " ORDER BY texto ASC";
            break;
        case "usuario":
            $sql="SELECT usuarios.id_usuario AS valor,"
                . " CONCAT(usuarios.cod_usuario,' (cod: ', usuarios.id_usuario ,')') AS texto "
                . " FROM usuarios "                
                . " WHERE usuarios.baja_usuario='No'"
                . " ORDER BY texto ASC";
            break;    
                         
    }
    $hacer = mysqli_query($connV,$sql) or die("no puedo ejecutar el listado " . mysqli_error($connV) .'<pre>'.$sql.'</pre>');
    //$arrLista[] = array("label"=>" - Todos -","value"=>"todos|todos");
	$arrLista=array();
    while($listado = mysqli_fetch_assoc($hacer)){
        $lista .= '<option value="'.$listado["valor"].'|'.$listado["texto"].'"> '.$listado["texto"].' </option>' . "\n";
        $arrLista[] = array(                        
                            "label"=>$listado["texto"],
                            "value"=>$listado["valor"].'|'.$listado["texto"],
            );
    }
//    return $lista;
    return json_encode($arrLista);
}

if (isset($_REQUEST["tabla"])){
    $queTabla= $_REQUEST["tabla"];
}else{
    $queTabla=null;
}

$arrVendCargo = $_SESSION["vendedor_a_cargo"];
$resultado = listado_seleccion($connV,$queTabla,$arrVendCargo);
echo $resultado;