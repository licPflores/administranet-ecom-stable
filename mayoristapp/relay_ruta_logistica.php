<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'sesion.inc.php';
if(isset($_REQUEST['ajax'])){
    if(isset($_REQUEST['idZona'])&& $_REQUEST["idZona"]!==""){
        $idZona = $_REQUEST['idZona'];
        
        $sql = "SELECT logi_hoja_ruta.id_ruta,
                        logi_hoja_ruta.fecha_salida,
                       logi_hoja_ruta.desc_ruta,
                        GROUP_CONCAT(CAST(b.nombre_zona AS CHAR) SEPARATOR ',') as nombre_zona,
                        GROUP_CONCAT(CAST(b.id_zona AS CHAR) SEPARATOR ',') as ids_zona
                FROM logi_hoja_ruta 
                LEFT JOIN logi_ruta_zona ON (logi_ruta_zona.id_ruta = logi_hoja_ruta.id_ruta) 
                LEFT JOIN erp_zona as b ON (b.id_zona = logi_ruta_zona.id_zona)
               
                WHERE 
                    logi_hoja_ruta.Anulado = 'No'
                    AND logi_hoja_ruta.estado_ruta = 'Abierto'
                    AND logi_hoja_ruta.fecha_salida > Now()
                GROUP BY logi_hoja_ruta.id_ruta 
                
                ORDER BY logi_hoja_ruta.fecha_salida ASC";
                
               // "Ruta: " & hoja_ruta.Columns.Item(0) & " - Zona: " & hoja_ruta.Columns.Item(1) & " "
//        ver de donde saco el id de la zona para hacer esta consulta con todos los datos que tiene.
        $hacer = mysqli_query($connV,$sql) or die("no puedo localizar las rutas <br><pre>" .$sql."</pre>" );
        
        $listaRutas = array();
        while($rr = mysqli_fetch_assoc($hacer)){
            $listaRutas[] = $rr;
        }
//          echo "<pre>";  
//        echo print_r($sql);
//        echo "</pre><br><pre>";
//        echo var_dump($_REQUEST['idZona']);
        $listaFinal = array();
        foreach($listaRutas as $key=>$ruta){
            $listaZonas = explode(",",$ruta["ids_zona"]);
//            echo "<pre>";  
//        echo print_r($listaZonas);
//        echo "</pre>";
            if(in_array($idZona,$listaZonas)){
                $listaFinal[] = $ruta;
            }
        }
        // hago el html par ponerlo donde va... pero solo de las opciones.
        // $listadoRuta = '<option value="">-elegir ruta-</option >';
        $listadoRuta = '';

        $contador = 0;
        
        foreach($listaFinal as $ruta){
           //echo str_pad($ruta["nombre_zona"], 20, '.', STR_PAD_RIGHT)) .'<br>';
            
            $nombreRuta = $ruta["id_ruta"].' | ';
            $nombreRuta = $ruta["desc_ruta"] .'|';
            $nombreRuta .= str_pad(substr($ruta["nombre_zona"],0,35),38,'.');
            
            $nombreRuta .= ' | ' . $ruta["fecha_salida"];
            if($contador==0){
                $listadoRuta .='<option selected value="'. $ruta["id_ruta"] .'">'.$nombreRuta.'</option>\n';
            }else{
                $listadoRuta .='<option value="'. $ruta["id_ruta"] .'">'.$nombreRuta .'</option>\n';
            }
            $contador++;
        }
        if($contador>0){
            echo $listadoRuta;
        }else{
            echo "error: sin rutas disponibles para la zona{$idZona}";
        }
    }else{
        echo "error: domicilio sin ruta";
    }
}