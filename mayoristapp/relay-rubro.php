<?php
require_once 'sesion.inc.php';

if(isset($_REQUEST['ajax'])){
    //categorias
    $idCategoria=NULL;
    $idRubro = null;
    $tipoCliente=null;
    
    // rubros
    if(isset($_REQUEST['idcategoria'])&&($_REQUEST['idcategoria']!="")){
        $rubros=array();
        $idCategoria = $_REQUEST['idcategoria'];
		$filtroCategoria ="";
		// si idCategoria es cero, no se filtra por categorias
		if($idCategoria !='0'){
			$filtroCategoria .= "AND rubro.id_categoria={$idCategoria}";
			
		}
        $sqlRubro = "SELECT 
                            rubro.CodigoRubro AS id,
                            rubro.NombreRubro AS name
                         FROM  articulo 
                         LEFT JOIN rubro ON rubro.CodigoRubro = articulo.CodigoRubro
                         WHERE
							articulo.tipo_art='Articulo'
							AND articulo.Discontinuo='No' 
							AND articulo.disponible_vta='Si'                                                 
							".$filtroCategoria."                         
							AND rubro.anulado='No'
                         GROUP BY articulo.CodigoRubro
                         ORDER BY rubro.NombreRubro ASC";
        $hacerRubro = mysqli_query($connV,$sqlRubro) or die('No puedo recuperar el rubro'.mysqli_error($connV));
		
        $rubros[]  = array("id"=>"","name"=>"- todos -");
//             echo "{".$sqlSubRubro."}";   
        while($rubro =  mysqli_fetch_assoc($hacerRubro)){
           $rubro['name'] = mb_convert_case(mb_strtolower($rubro['name'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
              $rubros[] =$rubro;                   
        }

        echo $json= json_encode($rubros);
    }
    // subrubros
    if(isset($_REQUEST['idrubro'])&&($_REQUEST['idrubro']!="")){
        $idRubro = $_REQUEST['idrubro'];   
        $subRubros= array();
        //reviso si hay tipo de cliente para traer los subrubros que corresp
        $filtroSubRubro =  " AND subrubro.CodigoRubro={$idRubro}";
        
        $sqlSubRubro = "SELECT 
                            subrubro.IDSubRubro AS id,
                            subrubro.NombreSubRubro AS name
                         FROM  articulo 
                         LEFT JOIN subrubro ON subrubro.IDSubRubro = articulo.IDSubRubro
                         WHERE
                        
                         subrubro.anulado='No'
						 ".$filtroSubRubro."
                         GROUP BY articulo.IDSubRubro
                         ORDER BY subrubro.NombreSubRubro ASC";
        $hacerSubRubro = mysqli_query($connV,$sqlSubRubro) or die('No puedo recuperar el subrubro'.mysqli_error($connV));
        //$subRubros[]  = array("id"=>"","name"=>"- todos -");
             //echo "{".$sqlSubRubro."}";   
        while($subRubro =  mysqli_fetch_assoc($hacerSubRubro)){
            //$subRubro['name'] = ucwords(strtolower($subRubro['name'])); // pasando Ucwoeds la 
			$subRubro['name'] = mb_convert_case(mb_strtolower($subRubro['name'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
              $subRubros[] = $subRubro;                   
        }
		
        $json= json_encode($subRubros, JSON_UNESCAPED_UNICODE);
		
		print $json;
        
    }
        
}