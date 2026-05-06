// geo localizacion.
//=================
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
           console.log({latitud,longitud});
           $.ajax({
            type:   'POST',
            url:    'relay_geolocalizacion.php',
            data:{
                "ajax":"true",
                "geo_lat": latitud,
                "geo_long": longitud,
                "estado": "geolocacion: exitosa"
    
            },
            success: function(response){
                console.log('guarde los datos de geolocalizacion');
                console.log(response);
                
            },
            error: function(x, e) {
                var s = x.status, 
                m = 'Ajax error: ' ; 
                if (s === 0) {
                    m += 'Check your network connection.';
                }
                if (s === 404 || s === 500) {
                    m += s;
                }
                if (e === 'parsererror' || e === 'timeout') {
                    m += e;
                }
                console.log(m);
            }
        });
            
        }
        
        
        // error de geolocalizacion
        //================================
        
        function onErrorGeolocating(error){
            console.log('algo paso con la geolocalizacion.');
            var estado,latitud,longitud;
            latitud=0;
            longitud=0;
            switch(error.code){
                case error.PERMISSION_DENIED:
                        console.log('ERROR: User denied access to track physical position!');
                        estado='ERROR: Usuario denego la geolocalizacion!<br> debe habilitarla para continuar con el pedido<br> <strong>si necesita ayuda comunicarse con soporte de administraNET</strong> ';
                break;
    
                case error.POSITION_UNAVAILABLE:
                        console.log("ERROR: There is a problem getting the position of the device!");
                        estado='ERROR: inconveniente para obtener la geolocalizacion!';
                break;
    
                case error.TIMEOUT:
                        console.log("ERROR: The application timed out trying to get the position of the device!");
                        estado='ERROR: Tiempo de espera agotado para la geololizacion!';
                break;
    
                default:
                        console.log("ERROR: Unknown problem!");
                        estado="ERROR: problema desconocido.";
                break;
            }  
            // si hay error que lo cargue igual
           
            
            $.ajax({
                type:   'POST',
                url:    'relay_geolocalizacion.php',
                data:{
                    "ajax":"true",
                    "geo_lat": latitud,
                    "geo_long": longitud,
                    "estado": estado
    
                },
                success: function(response){
                    console.log('guarde los datos de geolocalizacion pero mande el error');
                    console.log(response);
    
                },
                error: function(x, e) {
                    var s = x.status, 
                    m = 'Ajax error: ' ; 
                    if (s === 0) {
                        m += 'Check your network connection.';
                    }
                    if (s === 404 || s === 500) {
                        m += s;
                    }
                    if (e === 'parsererror' || e === 'timeout') {
                        m += e;
                    }
                    console.log(m);
                }
            });
    
    
    
    
            $('#jcart-checkout').attr('disabled',true);
             Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        html: estado
    
                      });
                      
                    
        }// error de geolocalizacion 
      