<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'sesion.inc.php';
function lista_laboratorio($connV){
    $sqlLab = "SELECT laboratorio.CodLaboratorio AS id,
                      laboratorio.NombreLaboratorio AS name
                    FROM laboratorio 
                    WHERE
                    laboratorio.anulado='No' 
                    AND laboratorio.CodLaboratorio <> 1
                    ORDER BY NombreLaboratorio ASC";
                    //echo $sqlModelo;
        $hacerLab= mysqli_query($connV,$sqlLab) or die('No puedo recuperar el laboratorio '.mysqli_error($connV));
        $labs[]  = array("id"=>"","name"=>"- todos -");
//             echo "{".$sqlSubRubro."}";   
        while($labo =  mysqli_fetch_assoc($hacerLab)){
              $labs[] =$labo;                   
        }

       print json_encode($labs);
}


if(isset($_REQUEST['listaLaboratorio'])&&$_REQUEST['listaLaboratorio']==1){
    lista_laboratorio($connV);
}