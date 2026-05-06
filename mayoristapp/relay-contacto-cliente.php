<?php
require_once 'sesion.inc.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function alta_contacto($p,$connV,$completo){
    if($completo=="No"){
        // solo nombre y dni
        $sql="INSERT INTO cliente_contacto SET "            
            . "nombre_cliente_contacto='{$p["nombre_contacto"]}',"
            . "tipo_doc='{$p["tipo_doc"]}',"
            . "nro_doc='{$p["nro_doc"]}',"
            //. "CelularContacto='{$p["TelefonoContacto"]}',"
            //. "TelefonoContacto='{$p["TelefonoContacto"]}',"
            //. "EmailContacto='{$p["EmailContacto"]}',"
            . "id_cliente='{$p["id_cliente"]}',"
            . "anulado='No';";
    }else{
        // todos los datos del contacto
        $sql="INSERT INTO cliente_contacto SET "            
            . "nombre_cliente_contacto='{$p["nombre_contacto"]}',"
            . "tipo_doc='{$p["tipo_doc"]}',"
            . "nro_doc='{$p["nro_doc"]}',"
            . "CelularContacto='{$p["TelefonoContacto"]}',"
            . "TelefonoContacto='{$p["TelefonoContacto"]}',"
            . "EmailContacto='{$p["EmailContacto"]}',"
            . "id_cliente='{$p["id_cliente"]}',"
            . "anulado='No';";
    }
    $hacer =mysqli_query($connV,$sql);
    if($hacer){
        $v=true;
        
    }else{
        //error
        
        $v=false;
        echo "error:".mysqli_error($connV)."<pre>".$sql."</pre>";
    }
    return $v;
    
}

function lista_contacto($idCliente,$connV){
   $sqlC="SELECT "
                . "contacto.nombre_cliente_contacto AS nombre,"
                . "contacto.tipo_doc,"
                . "contacto.nro_doc,"
                . "contacto.id_cliente_contacto AS codigo "
                . "FROM cliente_contacto AS contacto "
                . "WHERE contacto.id_cliente=".$idCliente." "
                . "ORDER BY contacto.nombre_cliente_contacto ASC";
        $hc=mysqli_query($connV,$sqlC) or die("No puedo encontar los contactos ".mysqli_error($connV)."<pre>".$sqlC."</pre>");
       
        $hay= mysqli_num_rows($hc);
        $contactos=array();
        $keyContacto=array();
        while($c= mysqli_fetch_assoc($hc)){
            $contactos[]=$c;
            $keyContacto[]=$c["codigo"];
        }
        //ordeno las claves de mayor a menor.
        
        $ultimo=max($keyContacto);
         $txtLista='<option value="">-seleccione contacto-</option>';
        foreach($contactos AS $k=>$c){
            if($ultimo==$c["codigo"]){
                $txtLista .='<option value="'.$c["codigo"].'|('.$c["codigo"].') '.$c["nombre"].' '.$c["tipo_doc"].' '.$c["nro_doc"].'" selected="selected">'.$c["nombre"].'</option>';
            }else{
                $txtLista .='<option value="'.$c["codigo"].'|('.$c["codigo"].') '.$c["nombre"].' '.$c["tipo_doc"].' '.$c["nro_doc"].'" >'.$c["nombre"].'</option>';
            }
        }
        
        if($hay==0){
            $txtLista="0";
        }
        return $txtLista;
}

//controlador de acciones
//echo "<pre>";
//var_dump($connV);
//echo "</pre>";
if(isset($_REQUEST["ajax"])){
    //inicio de variables.
    $accion=$_REQUEST["accion"];
    $idCliente = $_SESSION["idcliente"];
    $completo= $_SESSION["contacto_completo"];
    // todos los datos del contacto o solo dni y nombre
    if($completo=="No"){
        //solo dni y nombre
        $p=array(
        "nombre_contacto"=>$_REQUEST["nombreContacto"],
        "tipo_doc"=>$_REQUEST["tipoDocContacto"],
        "nro_doc"=>$_REQUEST["nroDocContacto"],
        //"TelefonoContacto"=>$_REQUEST["telefonoContacto"],
        //"EmailContacto"=>$_REQUEST["emailContacto"],
        "id_cliente"=>$idCliente    
        
        );
    }else{
        // todos los datos.
        $p=array(
        "nombre_contacto"=>$_REQUEST["nombreContacto"],
        "tipo_doc"=>$_REQUEST["tipoDocContacto"],
        "nro_doc"=>$_REQUEST["nroDocContacto"],
        "TelefonoContacto"=>$_REQUEST["telefonoContacto"],
        "EmailContacto"=>$_REQUEST["emailContacto"],
        "id_cliente"=>$idCliente    
        
        );
    }
    $vuelta="0";
    
    
    
    if($accion=="alta"){
        $resultado=alta_contacto($p,$connV,$completo);
        if($resultado==false){
            $vuelta="0";
        }else{
            $resultadolista= lista_contacto($idCliente,$connV);
            if($resultadolista=="0"){
                $vuelta="0";
            }
            else{
                $vuelta=$resultadolista;
            }
        }
        
    }
    echo $vuelta;
}