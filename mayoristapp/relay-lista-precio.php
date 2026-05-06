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
    // $arrListaPrecio[$i] = array("id"=>$i,"texto"=>"Lista ".$i,"nombre"=>$conf["desc_util".$i],"defecto"=>$defecto);

    $arrListaDePrecio = $_SESSION['arr_lista_precio'];
    $listaDefecto = $_SESSION['lista_precio_defecto'];
    $lista = array();
    // echo var_dump($_SESSION['lista_precio_defecto']);
    foreach($arrListaDePrecio as $precio){
        $selected=false;
        // seleccione cliente tomo su defecto 
        if(isset($objCliente)&&is_object($objCliente)){
            // echo 'seleccione cliente';
            // colocar por defecto la lista de precio del cliente.
            if($objCliente->codListaPrecio == $precio['id']){
                $selected=true;
            }
        }
        // no elegi cliente tomo defecto del sistema
        if(!isset($objCliente)){    
            // echo "sin cliente seleccionado";
            if($listaDefecto == $precio['texto']){
                $selected=true;
            }
        }

        $lista[] = array('id'=>$precio['id'],'name'=>$precio['texto'].' '. $precio['nombre'],'selected'=>$selected);
    }
    


            

            header('Content-Type: application/json');
            echo json_encode($lista);
            
?>