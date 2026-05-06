<?php 
require_once 'sesion.inc.php';
function truncarCadena($cadena, $longitud = 30) {
    // Verifica si la longitud de la cadena es mayor que la longitud deseada
    if (mb_strlen($cadena) > $longitud) {
        // Trunca la cadena y agrega los puntos suspensivos
        return mb_substr($cadena, 0, $longitud - 3) . '...';
    } else {
        // Si no es mayor, retorna la cadena original
        return $cadena;
    }
}
            $sqlProveeddor = "SELECT proveedor.Codigo, proveedor.Nombre ";
            $sqlProveeddor .= " FROM articulo ";
            $sqlProveeddor .= " LEFT JOIN proveedor ON proveedor.Codigo=articulo.CodigoProveedor";
            $sqlProveeddor .= " WHERE ";
			$sqlProveeddor .= " articulo.tipo_art='Articulo' ";
			//$sqlProveeddor .= " #AND articulo.ecommerce='Si'";
            //$sqlProveeddor .= " #articulo.ecommerce='Si'";
            $sqlProveeddor .= " AND articulo.Discontinuo='No' ";
            $sqlProveeddor .= " AND articulo.disponible_vta='Si' ";           
            $sqlProveeddor .= " AND NOT ISNULL(proveedor.Codigo) ";  
            $sqlProveeddor .= " AND proveedor.Codigo<>1 ";           
                     


            $sqlProveeddor .= " GROUP BY articulo.CodigoProveedor";
            $sqlProveeddor .= " ORDER BY proveedor.Nombre ASC";
             //echo '<pre>',$sqlProveeddor,'</pre>';
            $hacerProveedor = mysqli_query($connV, $sqlProveeddor) or die('No puedo recuperar el proveedor' . mysqli_error($connV));
            $proveedores = array();
            $proveedores[] = array('id'=>'','name'=>'- todos -');
            if($hacerProveedor){
                while ($proveedor =  mysqli_fetch_assoc($hacerProveedor)) {
                    $nombreProveedor = truncarCadena(ucwords($proveedor['Nombre']),25);
                    $nombreProveedor .= ' (Cod: '.$proveedor['Codigo'].')';
                    $proveedores[] = array('id'=>$proveedor['Codigo'],'name'=>$nombreProveedor);
                    
                }
            }
            echo json_encode($proveedores);
            
?>