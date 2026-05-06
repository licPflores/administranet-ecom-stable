<?php
//conexion del vendedor
require_once 'conexion-general.inc.php';
// echo "<pre>" . var_dump($_REQUEST)."</pre>";
// echo var_dump($conexionT);
// echo "<pre>";
// print_r(get_defined_constants(true));
// echo "</pre>";
if(isset($_POST['empresa'])&& $_POST['empresa']!=""){
    
    $sqlBaseElegida = "SELECT 
                            base_empresa,
                            nombre_empresa,
                            id_empresa
                        FROM empresas 
                        WHERE id_empresa=".$_POST['empresa'];
    $hacerElegida= mysqli_query($conexionT,$sqlBaseElegida) or die(mysqli_error($conexionT));
    
    if($hacerElegida){
        $baseEncontrada = mysqli_fetch_object($hacerElegida);
        $baseConecto = $baseEncontrada->base_empresa;
        if(!defined('database_db')){
            define('database_db',$baseConecto);
        }
        $nombreEmpresa = $baseEncontrada->nombre_empresa;
        $idEmpresa= $baseEncontrada->id_empresa;
     
        mysqli_select_db($conexionT,$baseConecto);
        mysqli_set_charset($conexionT,'utf8');
    }else{
        header('Location: index.php?cartel=4');
    }
}else{
    //header('Location: index.php?cartel=3');
}
    
    
    