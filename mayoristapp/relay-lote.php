<?php
require_once 'sesion.inc.php';
if(isset($_REQUEST['ajax'])){
    
    //localizamos los lotes segun el articulo.
    if(isset($_REQUEST['idArt']) && isset($_REQUEST['idDeposito']) && ($_REQUEST['idArt']!=""&&($_REQUEST['idDeposito']!=""))){
        $idArt = $_REQUEST['idArt'];
        $idDeposito = $_REQUEST['idDeposito'];
        $sqlLote = "SELECT 
                    lote.id_lote,
                    lote.cod_lote,
                    lote.fecha_vto_lote,
                    lote.stock_total_lote,
                    lote_stock.stock_lote 
                    FROM lote
                    INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) 
                    WHERE lote.id_articulo = {$idArt} 
                    AND lote.anulado ='No' 
                    AND lote_stock.stock_lote > 0 
                    AND lote_stock.id_deposito = {$idDeposito} 
                    ORDER BY lote.fecha_vto_lote ASC";
        $hacerLote = mysqli_query($connV,$sqlLote) or die('No puedo recuperar el lote'.mysqli_error($connV));
        //$lotes  = array();
        $tituloLote ="<div>Seleccione lote:</div>      "; 
        $textoLote = "";
        $clase ="impar";
        $cuantosLote = 0;
        while($lotes =  mysqli_fetch_object($hacerLote)){
            if($cuantosLote%2){
                $clase="par";
            }else{
                $clase="impar";
            }
            $textoLote .='<div class="'.$clase.'">'
                       . '<div><input type="radio" name="my-item-idLote" value="'.$lotes->id_lote.'|'.$lotes->stock_lote.'">'
                       . ' Nº Lote: <strong>'.$lotes->cod_lote.'</strong></div>'
                       . '<div>Vto: <strong>'. $lotes->fecha_vto_lote.'</strong></div>'
                       . '<div>Stock: <strong>'.$lotes->stock_lote.'</strong> de ('.$lotes->stock_total_lote.')</div>'
                       . '</div>';
        
            $cuantosLote++;
        }
        if($textoLote!=""){
            echo $tituloLote;
        }
        echo $textoLote;
        
//        $json = '['; // start the json array element
//        $json_names = array();
//        foreach ($subRubros as $id => $name) {
////            print_r($id);
////            print_r($name);
//            $json_names[] = '{"id": '.$id.', "name": "'.$name.'"}';
//                
//        }
//
//        $json .= implode(',', $json_names); // join the objects by commas;
//        $json .= ']'; // end the json array element
//        echo $json;
    }
        
}
