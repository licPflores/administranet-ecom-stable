<?php
require_once 'conexion-general.inc.php';
$sqlBaseCliente = "SELECT * FROM empresas";
$hacerBase = mysqli_query($conexionT,$sqlBaseCliente)or die("error de cartel cuatro". mysqli_error($conexionT)."<pre>".$sqlBalseCliente."</pre>");
if($hacerBase){
    $bases=array();
    while($ba = mysqli_fetch_object($hacerBase)){
        $bases[]=$ba;
    }
    $baseResultado=null;
    if(count($bases)==1){
        $baseResultado=$bases[0];
    }else{
        foreach($bases as $b){
            if($b->web_base_defecto=="Si"){
                $baseResultado=$b;
            }
        }
        if($baseResultado==null){
            $baseResultado=$bases[0];
        }
    }
    
    
    
   
        // solo hay una base, la tomo no importa si es web o no.
        
        $baseConecto = $baseResultado->base_empresa;
        $nombreEmpresa = $baseResultado->nombre_empresa;
        $servidor = servidor_db;
    //    mysql_set_charset('utf8',$conexionT);
    //    mysql_select_db($baseConecto,$conexionT);
//        mysqli_set_charset($conexionT,'utf8');
//        mysqli_select_db($conexionT,$baseConecto);
        $connV=mysqli_connect($servidor,"administranet","a7v8xx0805",$baseConecto,puerto_db);
        
        if(!$connV){
            echo 'error de conexion<pre>';
            echo mysqli_connect_error($connV);
            echo var_dump($servidor,"administranet","a7v8xx0805",$baseConecto,puerto_db);
            echo '</pre>';
        }
        //mysqli_select_db($connV,$baseConecto);
        mysqli_set_charset($connV,'utf8');
    
    
}else{

    header('Location: ../index.php?cartel=4');
    
}

