<?php
#TODO: crear constantes para ordernar configuracion datos MAYORISTA


//la ibero
//$servidor= "181.166.120.112:30804";
// servidor administranet.
//$servidor="192.168.0.1:3306";
//$servidor="190.15.209.173:30804";
//$servidor ="190.15.214.142:3306";
//$servidor="oficina.administranet.com.ar:3306";
//
//
// don orione 
//$servidor="201.181.0.172:30804";
//$servidor="209.13.155.34:30804";
//tankito
//$servidor="131.72.3.167:30804"; // tankito

//chapini cualquiera

//$servidor="chapinidorrego.dyndns.org:30804";

// gtn san juan
//$servidor="stnsanjuan.dyndns.org:30804";

// kranevitter
//$servidor="138.36.99.229:30804";

// fenix jayna
//$servidor = ":30804";#pepe
define('administranetLOCAL','192.168.0.1');
define('administranetEXTERNO','190.15.214.142'); // itc
define('administranetEXTERNOCLARO','190.3.87.143'); // itc
if(isset($_SESSION['servidor'])){
    $arrSrv= explode(':',$_SESSION['servidor']);
    
    define('servidor_db',$arrSrv[0]); #jayna
    if(key_exists(1,$arrSrv)){
    
        define('puerto_db',$arrSrv[1]); # puerto jayna
    }
}


if(isset($_SESSION['baseConecto'])){
    define('database_db',$_SESSION['baseConecto']);
}


if(!defined('servidor_db')){
    
    // define('servidor_db','190.15.193.181'); #jayna
    // define('servidor_db','chapinidorrego.dyndns.org'); 
   // define('servidor_db','201.181.0.172'); // don orione https://201.181.0.172:8090/administraweb/
    // define('servidor_db',administranetEXTERNO); #administranet afuera
    define('servidor_db',administranetEXTERNOCLARO); #administranet afuera claro
    // define('servidor_db','192.168.0.1'); #administranet adentro interno

    // define('servidor_db','190.15.204.148'); #inca
    // define('servidor_db','127.0.0.1'); #local 
    // define('servidor_db','190.3.87.55'); #amico
    // define('servidor_db','181.116.211.42'); #rodamientos    

    // define('servidor_db','179.41.6.130'); #manfiya
    // define('servidor_db','p1.administranet.com.ar'); // RDP la nacional
    // http://138.97.177.125/
    // define('servidor_db','138.97.177.125'); #martin represaentaciones
    // define('servidor_db','190.15.193.181'); #jayna
    // define('servidor_db','190.15.204.191'); #repuestos maldonad.
    // define('servidor_db','192.99.4.182'); # la nacional
    // define('servidor_db','181.116.127.95'); # angelita



}
if(!defined('puerto_db')){
    // define('puerto_db','30804'); # puerto nuevo local
    define('puerto_db','3306');#puerto administranet 
    // define('puerto_db','36000');#puerto RDP
    // define('puerto_db','12036');#puerto martin representaciones 
    //define('servidor_db','192.168.0.1'); #administranet adentro interno
     
}


define('usuario_db','administranet');
define('password_db','a7v8xx0805');






?>