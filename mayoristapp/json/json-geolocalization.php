<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<script type="javascript">
    // geo localizacion.
    //=================
    // consulta por si esta activado geolocalizacion..
    
    if (navigator.geolocation){
//        navigator.geolocation.getCurrentPosition(showPosition);
        navigator.geolocation.getCurrentPosition(onSuccessGeolocating,
                                         onErrorGeolocating,
                                         {
                                       		enableHighAccuracy: true,
                                       		maximumAge:         5000,
                                       		timeout:            10000
                                         });
                
    }else{ 
        console.log("Geolocation is not supported by this browser.");
    }
    // encontre geolocalizacion
    //===========================
    function onSuccessGeolocating(position){
       var latitud= position.coords.latitude,
           longitud= position.coords.longitude;    
       //conexion con ajax.
       console.log("latitud:"+latitud+" - longitud:"+longitud);
       alert("latitud:"+latitud+" - longitud:"+longitud);
        document.write('{"latitud":"'+latitud+'", "longitud":"'+longitud+'"}');
    }
    
    
    // error de geolocalizacion
    //================================
    
    function onErrorGeolocating(error){
        switch(error.code){
            case error.PERMISSION_DENIED:
                    console.log('ERROR: User denied access to track physical position!');
            break;

            case error.POSITION_UNAVAILABLE:
                    console.log("ERROR: There is a problem getting the position of the device!");
            break;

            case error.TIMEOUT:
                    console.log("ERROR: The application timed out trying to get the position of the device!");
            break;

            default:
                    console.log("ERROR: Unknown problem!");
            break;
        }                        
    }// error de geolocaliz 
    
    //FIN GEOLOCALIZACOIN=============
</script>    