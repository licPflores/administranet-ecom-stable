<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'sesion.inc.php';


function alta_domicilio($p,$connV){
    $hDesde=$p["horaDesde"].":".$p["minutoDesde"].":00";
    $hHasta=$p["horaHasta"].":".$p["minutoHasta"].":00";
    $sql="INSERT INTO cliente_domicilio SET "            
        . "Calle='{$p["calleCliente"]}',"
        . "NroCalle='{$p["numeroCliente"]}',"
        . "Dpto='{$p["deptoCliente"]}',"
        . "IDDistrito='{$p["distritoCliente"]}',"
        . "CodProvincia='{$p["provinciaCliente"]}',"
        . "IDDepartamento='{$p["departamentoCliente"]}',"
        . "id_zona='{$p["zonaCliente"]}',"
        . "id_cliente='{$p["idCliente"]}',"
        . "hora_desde='{$hDesde}',"
        . "hora_hasta='{$hHasta}',"
        . "periodicidad_visita_vendedor='{$p["visitaVendedor"]}',"
        . "visita_vendedor_valor='{$p["intervaloVisita"]}',"
        . "anulado='No';";
    $hacer =mysqli_query($connV,$sql);
    if($hacer){
        $v["estado"]="ok";
        $v["sql"]=$sql;
        $v["cartel"]="Domicilio Agregado";
    }else{
        //error
        $v["estado"]="error";
        $v["sql"]=$sql;
        $v["cartel"]=mysqli_error($connV)."<br>".$sql;
    }
    return $v;
    
}

function edita_domicilio($p,$connV){
    $hDesde=$p["horaDesdeEd"].":".$p["minutoDesdeEd"].":00";
    $hHasta=$p["horaHastaEd"].":".$p["minutoHastaEd"].":00";
//    echo "<pre>";
//    print_r($p);
//    echo "</pre>";
    
    $sql="UPDATE cliente_domicilio SET "            
        . "Calle='{$p["calleClienteEd"]}',"
        . "NroCalle='{$p["numeroClienteEd"]}',"
        . "Dpto='{$p["deptoClienteEd"]}',"
        . "IDDistrito='{$p["distritoClienteEd"]}',"
        . "CodProvincia='{$p["provinciaClienteEd"]}',"
        . "IDDepartamento='{$p["departamentoClienteEd"]}',"
        . "id_zona='{$p["zonaClienteEd"]}',"
        . "hora_desde='{$hDesde}',"
        . "hora_hasta='{$hHasta}',"
        . "periodicidad_visita_vendedor='{$p["visitaVendedorEd"]}',"
        . "visita_vendedor_valor='{$p["intervaloVisitaEd"]}'"
        . " WHERE id_cliente_domicilio='{$p["idClienteDom"]}'";
    $hacer =mysqli_query($connV,$sql);
    if($hacer){
        $v["estado"]="ok";
        $v["cartel"]="Domicilio Editado";
    }else{
        //error
        $v["estado"]="error";
        $v["cartel"]=mysqli_error($connV)."<br>".$sql;
    }
    return $v;
}
function trae_domicilio($idDomicilio,$connV){
    $sql="SELECT cd.id_cliente_domicilio,"
        . "cd.Calle,"
        . "cd.NroCalle,"
        . "cd.Dpto,"
        . "cd.IDDistrito,"
        . "di.NombreDistrito,"
        . "cd.CodProvincia,"
        . "p.Provincia,"
        . "cd.IDDepartamento,"
        . "d.NombreDepartamento,"
        . "cd.id_zona,"
        . "z.nombre_zona,"
        . "cd.id_cliente,"
        . "cd.anulado,"
        . "cd.diasContacto,"
        . "cd.id_pais,"
        . "COALESCE(cd.hora_desde,'00:00:00') AS hora_desde,"
        . "COALESCE(cd.hora_hasta,'00:00:00') AS hora_hasta,"
        . "cd.periodicidad_visita_vendedor,"
        . "cd.visita_vendedor_valor"            
        . " FROM cliente_domicilio AS cd  "
        . " LEFT JOIN provincia AS p ON p.CodProvincia=cd.CodProvincia"
        . " LEFT JOIN departamento AS d ON d.IDDepartamento = cd.IDDepartamento"
        . " LEFT JOIN distrito AS di ON di.IDDistrito = cd.IDDistrito"
        . " LEFT JOIN erp_zona AS z ON z.id_zona=cd.id_zona"
        . " WHERE cd.id_cliente_domicilio=".$idDomicilio
        . " AND cd.anulado='No'";
    // hacer jquery para devolver los valores del edit y la zona por cod provincia.
    $hacer=mysqli_query($connV,$sql) or die("no puedo recuperar el domicilio a editar".mysqli_error($connV)."<pre>".$sql."<pre>");
    $d = mysqli_fetch_assoc($hacer);
    
    return $d;
    
}
function busca_provincia($idPais=null,$connV){
    $where="";
    if($idPais){
        $where.=" AND p.id_pais=".$idPais;
    }
    $sqlProv="SELECT p.CodProvincia AS codigo ,p.Provincia as provincia "
            . "FROM provincia AS p "
            . "WHERE p.Anulado='No' {$where} "
            . "ORDER BY p.Provincia ASC";
    $hacer= mysqli_query($connV,$sqlProv) or die ("error pronvicia ".mysqli_error($connV)."<pre>".$sqlProv."</pre>");
    if($hacer){
        $prov = array();
        while($p = mysqli_fetch_assoc($hacer)){
            $prov[$p["codigo"]]=$p["provincia"];
        }
//        return json_encode($prov);
        return $prov;
    }else{
        return false;
    }
}

function busca_departamento($idProvincia,$connV){
    $where="";
    if($idProvincia){
        $where.=" AND dp.CodProvincia=".$idProvincia;
    }
    $sqlDepto="SELECT dp.IDDepartamento AS codigo ,dp.NombreDepartamento AS depto "
            . "FROM departamento AS dp "
            . "WHERE dp.Anulado='No' {$where} "
            . "ORDER BY dp.NombreDepartamento ASC";
    $hacer= mysqli_query($connV,$sqlDepto) or die ("error pronvicia ".mysqli_error($connV)."<pre>".$sqlDepto."</pre>");
    if($hacer){
        $depto = array();
        while($dp = mysqli_fetch_assoc($hacer)){
            $depto[$dp["codigo"]]=$dp["depto"];
        }
        return $depto;
    }else{
        return false;
    }
}
function busca_distrito($idDepartamento,$connV){
    $where="";
    if($idDepartamento){
        $where.=" AND d.IDDepartamento=".$idDepartamento;
    }
    $sqlProv="SELECT d.IDDistrito AS codigo ,d.NombreDistrito AS distrito "
            . "FROM distrito AS d "
            . "WHERE d.Anulado='No' {$where} "
            . "ORDER BY d.NombreDistrito ASC";
    $hacer= mysqli_query($connV,$sqlProv) or die ("error distrito ".mysqli_error($connV)."<pre>".$sqlProv."</pre>");
    if($hacer){
        $di = array();
        while($d = mysqli_fetch_assoc($hacer)){
            $di[$d["codigo"]]=$d["distrito"];
        }
        return $di;
    }else{
        return false;
    }
}

function busca_zona($codProvincia,$connV){
    $where="";
    if($codProvincia){
        $where.=" AND z.codprovincia=".$codProvincia;
    }
    $sqlProv="SELECT z.id_zona AS codigo ,z.nombre_zona AS zona "
            . "FROM erp_zona AS z "
            . "WHERE z.anulado='No' {$where} "
            . "ORDER BY z.id_zona ASC";
    $hacer= mysqli_query($connV,$sqlProv) or die ("error distrito ".mysqli_error($connV)."<pre>".$sqlProv."</pre>");
    if($hacer){
        $zi = array();
        while($z = mysqli_fetch_assoc($hacer)){
            $zi[$z["codigo"]]=$z["zona"];
        }
        return $zi;
    }else{
        return false;
    }
}


function trae_opciones_visita($tipoVisita){
    
    $vuelta=array();
    // Sin Visita
    if($tipoVisita=="No"){
        $vuelta["titulo"]="Cuando: ";
        $vuelta["opc"][]="No";
    }
    
    // Semanal de l a v
    
    if($tipoVisita=="Semanal"){
        $vuelta["titulo"]="Dia: ";
        $vuelta["opc"][]="Lunes";
        $vuelta["opc"][]="Martes";
        $vuelta["opc"][]="Miercoles";
        $vuelta["opc"][]="Jueves";
        $vuelta["opc"][]="Viernes";
        $vuelta["opc"][]="Sabado";
    }
    // quincenal del l aprimea quiencena
    if($tipoVisita=="Quincenal"){
        $vuelta["titulo"]="Periodo: ";
        $vuelta["opc"][]="01-15";
        $vuelta["opc"][]="15-30";
    }
    if($tipoVisita=="Mensual"){
        $vuelta["titulo"]="Dia: ";
        for($i=1;$i<32;$i++){
            $vuelta["opc"][]=$i;
        }
    }
    $vuelta["msg"]="ok";
    
    print json_encode($vuelta);
    
}



/*
 * hacer funcion que devuelva al cambiar la provincia 
 * completa el departamento y la zona erp_zona
 */

//GET recupero datos y envio.
//echo "GET:=> <pre>";
//print_r($_GET);
//echo "</pre>";
if(isset($_GET["ajax"])){
    $accion=$_GET["accion"];
    if(isset($_GET["idDomicilio"])){$idDomicilio=$_GET["idDomicilio"];}
    $idPais=null;
    if(isset($_GET["idProvincia"])){$idProvincia=$_GET["idProvincia"];}
    if(isset($_GET["idDepartamento"])){$idDepartamento=$_GET["idDepartamento"];}
    if(isset($_GET["idDistrito"])) {$idDistrito=$_GET["idDistrito"];}
    
    
    switch($accion){
        case "traer":
            $arrVuelta= array();
            // traigo el domicilio luego pido taer la provincia y todos lo demas
            $dm = trae_domicilio($idDomicilio,$connV);
//            echo"<pre>";
//            print_r($dm);
//            echo "</pre>";
            $arrVuelta["dom"] = $dm;
            $arrVuelta["prov"] = busca_provincia($idPais,$connV);
            $arrVuelta["dep"] = busca_departamento($dm["CodProvincia"],$connV);
            $arrVuelta["dist"] = busca_distrito($dm["IDDepartamento"],$connV);
            $arrVuelta["zona"] = busca_zona($dm["CodProvincia"],$connV);
            $vuelta = $arrVuelta;
            break;    
        case "provincia":
            $vuelta=busca_provincia($idPais,$connV);
            break;
        case "departamento":
            $vuelta=busca_departamento($idProvincia,$connV);
            break;
        case "distrito":
            $vuelta=busca_distrito($idDepartamento,$connV);
            break;
        
        case "zona":
            $vuelta=busca_zona($idProvincia,$connV);
            break;
    }
    
    echo json_encode($vuelta);
}

if(isset($_POST["accion"])){
    
    $accion=$_POST["accion"];
    if(isset($_POST["idDomicilio"])){$idDomicilio=$_POST["idDomicilio"];}
    $idPais=null;
    if(isset($_POST["idProvincia"])){$idProvincia=$_POST["idProvincia"];}
    if(isset($_POST["idDepartamento"])){$idDepartamento=$_POST["idDepartamento"];}
    if(isset($_POST["idDistrito"])) {$idDistrito=$_POST["idDistrito"];}
    

    switch($accion){
        case "alta":
            $vuelta= alta_domicilio($_POST,$connV);
            break;
        case "editar":
           $vuelta=edita_domicilio($_POST,$connV);
            break;
        
            
    }
    echo json_encode($vuelta);
    
    
    
    
}

// trae los valores de las visitas.

if(isset($_REQUEST["traeVisita"])&&$_REQUEST["traeVisita"]==1){
    $tipoVisita=$_REQUEST["tipoVisita"];
    trae_opciones_visita($tipoVisita);
            
}