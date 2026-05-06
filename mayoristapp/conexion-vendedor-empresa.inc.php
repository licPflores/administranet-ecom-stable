<?php
// viene de control.php
   // $baseConecto = $_SESSION['baseConecto'];
    //$servidor    = $_SESSION['servidor'];
    

    //$servidor    = servidor_db;
    //$puerto     = puerto_db;
    if(!defined('database_db')){
        define('database_db',$baseConecto);
    }
    
    $connV=@mysqli_connect(servidor_db,usuario_db,password_db,database_db,puerto_db);
    if(!$connV){
        
            $connV =@mysqli_connect(administranetLOCAL,usuario_db,password_db,database_db,puerto_db);
        
    }
    //echo var_dump(servidor_db,usuario_db,password_db,database_db,puerto_db);
//    mysqli_select_db($connV,$baseConecto);
    mysqli_set_charset($connV,'utf8');