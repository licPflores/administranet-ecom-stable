var puntitos; 



function Consolidar(){
	var Mensaje;
	var Cana;
	
        $.ajax({
                        async: false,
                        dataType: "json",
                        url: "./json/puntosdeusuarios.php",
                        beforeSend: function(){
                                console.log("Verificando puntos de Usuario");
                        },
        success: function( data, textStatus, jqXHR ) {
                puntitos = data;
        },
        error: function (jqXHR, textStatus, errorThrown) {

                console.log("error al verificar puntos del usuario "+textStatus);
                        }
        });

    $.ajax({
                    async: false,
                    dataType: "json",
                    url: "./json/canasta.php",
    success: function( data, textStatus, jqXHR ) 
    {
            console.log("canasta traida con exito");
            Cana = data;
    },
    error: function (jqXHR, textStatus, errorThrown) {

            console.log("error al verificar canasta "+textStatus);
                    }
    });


    if(Cana){
        var vector = Cana.datos;
        Mensaje ="<p>";
        var Saldo=0;
        var TusPuntos = puntitos.rows[0].saldo_premios;
        var porcion;
        var Acumulador = 0;
        var Cantidad = 0;
        var subTotal=0;
        var puntitos=0;
        //alert('el vector que estoy mirando');
        console.log({vector});
        for(x=0;x<vector.length;x++){
            console.log("Vector en la posicion "+x);
            console.log(vector[x]);
            Mensaje+="<p>"+vector[x].Premio+": ";

//            porcion=vector[x].Cantidad;
//            porcion = porcion.toString();
//            porcion= porcion.replace(/<[^>]*>/g, '');
//            porcion = Number(porcion);
//            Cantidad = porcion;
            Cantidad =vector[x].Cantidad;

//            porcion=vector[x].Total;
//            porcion = porcion.toString();
//            porcion= porcion.replace(/<[^>]*>/g, '');
//            porcion = Number(porcion);
//            puntitos = porcion;
            puntitos= vector[x].Puntos;
            subTotal=vector[x].Total;
            Mensaje +=" <strong>"+Cantidad+"</strong> x ";
            Mensaje +="\t<strong>"+puntitos+"</strong> pts\t</p>";
            
            Saldo+=(subTotal);
        }
        Mensaje+="<hr>";
        Mensaje+="Total de Puntos: ";
            //for(var espacios=0;espacios<12;espacios++)Mensaje+="&nbsp;";

        Mensaje+="<b>"+Saldo+"</b> y tienes <b>"+TusPuntos+"</b><br>";
        if((TusPuntos-Saldo)>0)Mensaje+="Te quedarán "+(TusPuntos-Saldo)+" puntos";
        if((TusPuntos-Saldo)<0)Mensaje+="No son suficientes, faltan "+(Saldo-TusPuntos)+" puntos";

        if(TusPuntos<Saldo){
            Mensaje+="<h3>Te faltan puntos por canjear</h3>";
        }

        Mensaje = "<p>Esta seguro de Canjear?</p>\n"+Mensaje;

        $.messager.confirm('Finalizar Canje',Mensaje,function(r){
            if (r){
                $.ajax({

                                dataType: "json",
                                url: "./json/canjear.php",
                                beforeSend: function() {

                                // lista.html("<img src='../_lib/sw/loader.gif' >");
                                }
                })
                .done(function( data, textStatus, jqXHR ) {
//                       $.messager.confirm({
//                            title: 'administraNET',
//                            msg: data.Mensaje,
//                            fn: function(r){
//                                 if(r){
//                                  //location.reload(true);
//                                     
//                                 }
//                             }
//                        });
                    //alert('El canje se realizo correctamente');
                    //swal("Hecho!", "El canje se hizo correctamente", "success");
                   
                    swal("Hecho!", "El canje se hizo correctamente", "success")
                    .then((value) => {
                       location.href="catalogo.php";
                    });
                    
                })
                .fail(function( jqXHR, textStatus, errorThrown ) {
                    if ( console && console.log ) {
                        console.log( "La solicitud a fallado: " +  textStatus);

                    }
               });

            }
        }
        );
    }

}

// hace el canje 
function consolidar_canje(){
    var Mensaje;
    var Cana;
    
    $.ajax({
                    async: false,
                    dataType: "json",
                    url: "./json/puntosdeusuarios.php",
                    beforeSend: function(){
                        $('#spinner').show('fast');
                            console.log("Verificando puntos de Usuario");
                    },
        success: function( data, textStatus, jqXHR ) {
                puntitos = data;
        },
        error: function (jqXHR, textStatus, errorThrown) {

                console.log("error al verificar puntos del usuario "+textStatus);
        },

        complete: function(){
           // $('#spinner').hide('fast');
        }
            
    });

    $.ajax({
        async: false,
        dataType: "json",
        url: "./json/canasta.php",
    beforeSend: function(){
        //$('#spinner').show('fast');
    },    
    success: function( data, textStatus, jqXHR ) 
    {
            console.log("canasta traida con exito");
            Cana = data;
    },
    error: function (jqXHR, textStatus, errorThrown) {

            console.log("error al verificar canasta "+textStatus);
    },
    complete: function(){
       // $('#spinner').hide('fast');
    }
    });


    if(Cana){
        var vector = Cana.datos;
        Mensaje ="";
        var Saldo=0;
        var TusPuntos = puntitos.rows[0].puntos_premios;
        var porcion;
        var Acumulador = 0;
        var Cantidad = 0;
        var subTotal=0;
        var puntitos=0;
        //alert('el vector que estoy mirando');
        console.log({vector});
        for(x=0;x<vector.length;x++){
            console.log("Vector en la posicion "+x);
            console.log(vector[x]);
            Mensaje+="<p>"+vector[x].Premio+": ";
            Cantidad =vector[x].Cantidad;
            puntitos= vector[x].Puntos;
            subTotal=parseInt(vector[x].Total);
            Mensaje +=" <strong>"+Cantidad+"</strong> x ";
            Mensaje +="\t<strong>"+puntitos+"</strong> pts\t</p>";
            
            Saldo+=(subTotal);
        }
        
        Mensaje="A canjear: ";
            //for(var espacios=0;espacios<12;espacios++)Mensaje+="&nbsp;";

        Mensaje+='<strong>'+Saldo+'pts</strong><br> Dispone de: <strong>'+TusPuntos+' pts</strong><br> Saldo: <strong>'+(TusPuntos-Saldo)+' pts</strong>';
//        if((TusPuntos-Saldo)>0)Mensaje+="Te quedarán "+(TusPuntos-Saldo)+" puntos";
//        if((TusPuntos-Saldo)<0)Mensaje+="No son suficientes, faltan "+(Saldo-TusPuntos)+" puntos";
//
//        if(TusPuntos<Saldo){
//            Mensaje+="<h3>Te faltan puntos por canjear</h3>";
//        }
//
//        Mensaje = "<p>Esta seguro de Canjear?</p>\n"+Mensaje;
        $('#spinner').hide('fast');  
          Swal.fire({
             position:'top', 
            title: "Confirma el canje?",
            html: Mensaje,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-check fa-lg"></i> Si!',
            cancelButtonText: '<i class="fas fa-times fa-lg"></i> No'
            
        })
        .then((result) => {
            if (result.value) {
              $.ajax({

                    dataType: "json",
                    url: "./json/canjear.php",
                beforeSend: function(){
                    $('#spinner').show('fast');
                }
                })
                .done(function( data, textStatus, jqXHR ) {
                    // volvio el canje 
                    console.log({data});
                    $('#spinner').hide('fast');  
                    if(data.estado==="ok"){
                        Swal.fire({
                        position: 'top',    
                        title:"Hecho!", 
                        html: "El canje se hizo correctamente"+data.Mensaje, 
                        icon:"success"})
                        .then((value) => {
                            
                           location.href="catalogo.php";
                        });
                    }
                    if(data.estado==="error"){
                        console.log({data});
                        Swal.fire({title:"Oops!", html:"ocurrió: "+JSON.stringify(data), icon:"error"});
                        console.log(data);
                    }
                    
                })
                .fail(function( jqXHR, textStatus, errorThrown ) {
                    if ( console && console.log ) {
                        console.log({data});
                        Swal.fire({title:"Oops!", html:"ocurrió: "+JSON.stringify(textStatus), icon:"error"});

                    }
               }).complete(function(){
                 $('#spinner').hide('fast');  
               });
            }
          });
        
//        
//        .then((willDelete) => {
//          if (willDelete) {
//              $.ajax({
//
//                    dataType: "json",
//                    url: "./json/canjear.php",
//                beforeSend: function(){
//                    $('#spinner').show('fast');
//                }
//                })
//                .done(function( data, textStatus, jqXHR ) {
//                    // volvio el canje 
//                    console.log({data});
//                    if(data.estado==="ok"){
//                        Swal.fire({
//                        position: 'top',    
//                        title:"Hecho!", 
//                        text: "El canje se hizo correctamente"+data.Mensaje, 
//                        icon:"success"})
//                        .then((value) => {
//                            
//                           location.href="catalogo.php";
//                        });
//                    }
//                    if(data.estado==="error"){
//                        Swal.fire("Oops!", "ocurrió: "+data.Mensaje, "error");
//                    }
//                    
//                })
//                .fail(function( jqXHR, textStatus, errorThrown ) {
//                    if ( console && console.log ) {
//                         Swal.fire("Oops!", "ocurrio un error: "+textStatus, "error");
//
//                    }
//               }).complete(function(){
//                 $('#spinner').hide('fast');  
//               });
//          }  
//          else {
//            //swal("Your imaginary file is safe!");
//          }
//        }); 
       
    }
}



function ObtenCantidad(id){
	
	var x = $.ajax({
		async: false,
		dataType: "json",
		data: { id: id },
		url: "./json/cantidad.php"
}).responseText;

return x;
}

function ObtenPuntos(id){
	
	var x = $.ajax({
		async: false,
		dataType: "json",
		data: { id: id },
		url: "./json/puntosxproducto.php"
}).responseText;

return x;
}

