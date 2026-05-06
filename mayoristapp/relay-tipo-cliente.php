<?php
require_once 'sesion.inc.php';
if(isset($_REQUEST['ajax'])){
    //hacer rubro y todo junto busco rubro y subrubro.
    //reviso si hay tipo de cliente para traer los subrubros que corresp
    if(isset($_REQUEST["tipoCliente"])&& ($_REQUEST["tipoCliente"]!="")){
        $sqlSubRubro = "SELECT 
                            subrubro.IDSubRubro,
                            subrubro.NombreSubRubro
                         FROM articulo_tipo_cliente AS atc
                         LEFT JOIN articulo ON articulo.IDArt = atc.id_articulo
                         LEFT JOIN subrubro ON subrubro.IDSubRubro = articulo.IDSubRubro
                         WHERE
                         subrubro.CodigoRubro={$_REQUEST['idrubro']}
                         AND atc.id_tipo_cliente = {$_REQUEST['tipoCliente']}
                         GROUP BY articulo.IDSubRubro
                         ORDER BY subrubro.NombreSubRubro ASC";
        $hacerSubRubro = mysql_query($sqlSubRubro) or die('No puedo recuperar el subrubro'.mysql_error());
        $subRubros  = array();
        while($subRubro =  mysql_fetch_object($hacerSubRubro)){
              $subRubros[$subRubro->IDSubRubro] = $subRubro->NombreSubRubro;                   
        }

        $json = '['; // start the json array element
        $json_names = array();
        foreach ($subRubros as $id => $name) {
            $json_names[] = '{"id": '.$id.', "name": "'.$name.'"}';

        }

        $json .= implode(',', $json_names); // join the objects by commas;
        $json .= ']'; // end the json array element
        echo $json;
    }
    else{
        $sqlSubRubro = "SELECT * 
                    FROM subrubro 
                    WHERE
                    CodigoRubro ={$_REQUEST['idrubro']} 
                    AND anulado='No' ORDER BY NombreSubRubro";
        $hacerSubRubro = mysql_query($sqlSubRubro) or die('No puedo recuperar el subrubro'.mysql_error());
        $subRubros  = array();
        while($subRubro =  mysql_fetch_object($hacerSubRubro)){
              $subRubros[$subRubro->IDSubRubro] = $subRubro->NombreSubRubro; 
        }

        $json = '['; // start the json array element
        $json_names = array();
        foreach ($subRubros as $id => $name) {
            $json_names[] = '{"id": '.$id.', "name": "'.$name.'"}';
        }

        $json .= implode(',', $json_names); // join the objects by commas;
        $json .= ']'; // end the json array element
        echo $json;
    }
    
        
}


