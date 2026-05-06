<?php
require_once 'sesion.inc.php';
if(isset($_REQUEST['ajax'])){
    //hacer rubro y todo junto busco marca y modelo
    if(isset($_REQUEST['idmarca'])&&($_REQUEST['idmarca']!="")){
//        recupero las marcas del modelo.
        $sqlModelo = "SELECT modelo.CodModelo AS id,
                            modelo.NombreModelo AS name
                    FROM modelo 
                    WHERE
                    modelo.CodMarca ={$_REQUEST['idmarca']} 
                    AND modelo.anulado='No' 
                    ORDER BY NombreModelo ASC";
                    //echo $sqlModelo;
        $hacerModelo = mysqli_query($connV,$sqlModelo) or die('No puedo recuperar el modelo'.mysqli_error($connV));
        $modelos[]  = array("id"=>"","name"=>"- todos -");
//             echo "{".$sqlSubRubro."}";   
        while($modelo =  mysqli_fetch_assoc($hacerModelo)){
              $modelos[] =$modelo;                   
        }

        echo $json= json_encode($modelos);
    }
    // no busco modelo si no marca.
    
    if(!isset($_REQUEST["idmarca"])){
//        echo "<pre>";
//        print_r($_REQUEST);
//        echo "</pre>";
        $filtro="";
        if(isset($_REQUEST["idcategoria"])&&$_REQUEST["idcategoria"]!=""){
            $filtro .=" AND rubro.id_categoria=".$_REQUEST["idcategoria"];
        }
        if(isset($_REQUEST["idrubro"])&&$_REQUEST["idrubro"]!=""){
            $filtro .=" AND articulo.CodigoRubro=".$_REQUEST["idrubro"];
        }
        if(isset($_REQUEST["idsubrubro"])&&$_REQUEST["idsubrubro"]!=""){
            $filtro .=" AND articulo.IDSubRubro=".$_REQUEST["idsubrubro"];
        }
        
        $sqlMarca = "SELECT marca.CodMarca AS id,
                            marca.NombreMarca AS name
                    FROM articulo
                    LEFT JOIN marca ON articulo.CodigoMarca= marca.CodMarca
                    LEFT JOIN rubro ON articulo.CodigoRubro=rubro.CodigoRubro
                    
                    WHERE
						articulo.tipo_art='Articulo'
						AND articulo.Discontinuo='No' 
						AND articulo.disponible_vta='Si'
                        AND marca.anulado='No' 
                        #AND marca.ecommerce='Si'
						
                        {$filtro}
                    GROUP BY articulo.CodigoMarca        
                    ORDER BY marca.NombreMarca ASC";
                    //echo $sqlModelo;
        $hacerMarca = mysqli_query($connV,$sqlMarca) or die('No puedo recuperar marca'.mysqli_error($connV));
        $marca[]  = array("id"=>"","name"=>"- todas -");
//             echo "{".$sqlMarca."}";   
        while($marca =  mysqli_fetch_assoc($hacerMarca)){
              $marcas[] =$marca;                   
        }

        echo $json= json_encode($marcas);
    }
        
}