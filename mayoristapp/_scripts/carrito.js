// // * obtener datos de la sesion.
// var laSesionCarrito = obtenerUsuarioLogueado();
// //console.log('datos=>>',laSesion.caminoDisp);
// console.log('laSesin=>',laSesionCarrito);
// var soyMobilCarrito ='No';
//  if(laSesion.caminoDisp!=undefined){
//      soyMobilCarrito="Si";
//  }
// console.log()

// console.log('SoyMobil==>',soyMobil);
// * funcion uniad display bulto para articulos
// funcion que cambia el precio segun el bulto promedio cambia todos los precios. display bulto o demas
function cambiaPrecioTipoUnidad(idArt,comoCuento,checkClick){
    // idArt para buscar los datos y cambiarlos.
    // comoCuento para saber que hacer con el calculo del precio y demas.
    // let checkClick = document.getElementsByClassName('cantidad-unidad');
    // console.log('soy el objeto this',checkClick)
    // console.log('check id',checkClick.id);
    // console.log()
    // let iconoCheck = '<i class="fas fa-check-circle fa-lg"></i>';
    let iconoCheck ='<i class="fas fa-check-circle fa-lg" aria-hidden="true"></i>'
    let miLabel = document.querySelector("label[for='"+checkClick.id+"']");
    // console.log(miLabel);
    // console.log('limpiando todo--->');
    limpiarCheckTipoUnidad(idArt);
    
    miLabel.classList.add("elegida");
    //miLabel.classList.add(claseElegida);
    miLabel.innerHTML = miLabel.innerHTML +' '+  iconoCheck;

    let jsonArticulo = document.getElementById('mi-json'+idArt);
    console.warn('como cuento=>=',comoCuento);
    // hacer funcion que haga que elimine la clase elegida de todos los labels de los input type por nombre , luego hacer addclase en este clase.
    //console.log('los datos de json del articulo',jsonArticulo);
    let objArt = JSON.parse(jsonArticulo.value);
    // console.table('objArt',objArt);
    let tipoUnidadArticulo,precioNeto, impuestoInterno,precioFinalCIva,precioFinalSinIva;
    let precioNetoNuevo,importeIvaNuevo,impuestoInternoNuevo,descuentoFinalNuevo,precioIva;

    let precioNetoTexto,precioIvaTexto,impuestoInternoTexto,descuentoTexto,precioFinalCivaTexto;
    
    //$jsonArt['importeIva'] = $importeIva;
    //$jsonArt['importeInterno'] = $importeInterno;
    //        $jsonArt['precioNeto'] = $precioNeto;
    //        $jsonArt['promo'] = $promo;
    //        $jsonArt['promoCant'] = $promoCant;
    //        $jsonArt['descFinal'] = $descFinal;
    // recupero valores 
    tipoUnidadArticulo ='Unidad';
    cantidadUnidadDisplay = 1;
    cantidadDisplayBulto = 1;
    console.log('stoy tipo precio unidad objeto:',objArt.tipoPrecioUnidad);
    if(objArt.tipoPrecioUnidad!=null){
        tipoUnidadArticulo = objArt.tipoPrecioUnidad; // Unidad - Display -Bulto
        cantidadUnidadDisplay = objArt.cantidad_unidad_display;
        cantidadDisplayBulto = objArt.cantidad_display_bulto;
    }
    //si tipo de unidad no tiene datos.
    precioNeto = parseFloat(objArt.precioNeto);
    importeIva = objArt.importeIva;
    impuestoInterno = objArt.importeInterno;
    descFinal = objArt.precioNetoDesc;   
    


    // console.log(cantidadUnidadDisplay,cantidadDisplayBulto);
    
    // precios recalculados.
    precioNetoNuevo = precioNeto;
    importeIvaNuevo = importeIva;
    impuestoInternoNuevo = impuestoInterno;
    descuentoFinalNuevo = descFinal;
    precioIva = precioNeto+importeIva+impuestoInterno;


    // console.log({'precioNetoNuevo':precioNetoNuevo,'importeIvaNuevo':importeIvaNuevo,'impuestoInternoNuevo':impuestoInternoNuevo,'descuentoFinalNuevo':descuentoFinalNuevo,'precioFinal':precioIva});

    if(comoCuento=="Unidad"){
        // si tipoUnidadArticulo ==Unidad Precios *1 o queda como estas
        // if(tipoUnidadArticulo=='Unidad'){}

        // si tipoUnidadARticulo == Display ( si unidad_display==1 -> precio como esta )( si unidad_display>1 -> precios / unidad_display)
        if(tipoUnidadArticulo=='Display'){
            let divisor = parseInt(cantidadUnidadDisplay);// cuantas unidades tengo 
            if(divisor == 0) divisor=1;
            precioNetoNuevo = precioNeto /divisor;
            importeIvaNuevo = importeIva /divisor;
            impuestoInternoNuevo = impuestoInterno /divisor;
            descuentoFinalNuevo  = descFinal /divisor;
        }

        // si tipoUnidadArticulo == Bulto ( calcular la cantidad unidad_dipslay * la cantidad unidad display_ bulto -> precios /calculo )

        if(tipoUnidadArticulo=='Bulto'){
            let divisor = parseInt(cantidadUnidadDisplay*cantidadDisplayBulto);// cuantas unidades tengo 
            if(divisor == 0) divisor=1;
            precioNetoNuevo = precioNeto /divisor;
            importeIvaNuevo = importeIva /divisor;
            impuestoInternoNuevo = impuestoInterno /divisor;
            descuentoFinalNuevo  = descFinal /divisor;

        }
    }
    if(comoCuento=="Display"){
        // si tipoUnidadArticulo ="Unidad" (Precios por unidad_display)
        if(tipoUnidadArticulo=='Unidad'){
            let divisor = parseInt(cantidadUnidadDisplay);// cuantas unidades tengo 
            if(divisor == 0) divisor=1;
            precioNetoNuevo = precioNeto /divisor;
            importeIvaNuevo = importeIva /divisor;
            impuestoInternoNuevo = impuestoInterno /divisor;
            descuentoFinalNuevo  = descFinal /divisor;

        }
        // si tipoUnidadArticulo = Display (Precio como esta) no toco nada
        // si tipoUnidadArticulo ==Bulto ( Precio / la cantidad unidad display bulto)
        if(tipoUnidadArticulo=='Bulto'){
            let divisor = parseInt(cantidadDisplayBulto);// cuantas unidades tengo 
            if(divisor == 0) divisor=1;
            precioNetoNuevo = precioNeto /divisor;
            importeIvaNuevo = importeIva /divisor;
            impuestoInternoNuevo = impuestoInterno /divisor;
            descuentoFinalNuevo  = descFinal /divisor;

        }

    }
    if(comoCuento=="Bulto"){
        // si tipoUnidadArticulo == Unidad (precios  * ( calculo de cantidad dislay y cantidad display bulto))
        if(tipoUnidadArticulo=='Unidad'){
            let multiplicador = parseInt(cantidadUnidadDisplay*cantidadDisplayBulto);// cuantas unidades tengo 
            if(multiplicador == 0) multiplicador=1;
            precioNetoNuevo = precioNeto *multiplicador;
            importeIvaNuevo = importeIva *multiplicador;
            impuestoInternoNuevo = impuestoInterno *multiplicador;
            descuentoFinalNuevo  = descFinal *multiplicador;

        }
        // si tipoUnidadArticulo == Display ( precios * cantidad unidad display bulto)
        if(tipoUnidadArticulo=='Display'){
            let multiplicador = parseInt(cantidadDisplayBulto);// cuantas unidades tengo 
            if(multiplicador == 0) multiplicador=1;
            precioNetoNuevo = precioNeto *multiplicador;
            importeIvaNuevo = importeIva *multiplicador;
            impuestoInternoNuevo = impuestoInterno *multiplicador;
            descuentoFinalNuevo  = descFinal *multiplicador;

        }
        // si tipoUnidadArticulo == Bulto (precios como esta.)
    }
    // el precio final se vuelve a calcular 
    precioIva = precioNetoNuevo+importeIvaNuevo+impuestoInternoNuevo;

    // pasando a texto 
    precioNetoTexto = precios.format(precioNetoNuevo);
    precioIvaTexto = precios.format(importeIvaNuevo);
    impuestoInternoTexto = precios.format(impuestoInternoNuevo);
    descuentoTexto = precios.format(descuentoFinalNuevo);
    precioFinalCivaTexto = precios.format(precioIva);

    // console.warn('cambie precios que tendre oculto?====>');
    // console.table({'precioNetoNuevo':precioNetoNuevo,'importeIvaNuevo':importeIvaNuevo,'impuestoInternoNuevo':impuestoInternoNuevo,'descuentoFinalNuevo':descuentoFinalNuevo,'precioFinalcIva':precioIva});
    // console.warn('valores con precios de texto lo que se pueda hacer o mpostrar... puede cambiar, adaptar el php a lo que se muestre enjavascipt');
    console.table({
        'neto':precioNetoTexto,
        'iva':precioIvaTexto,
        'interno': impuestoInternoTexto,
        'descuento':descuentoTexto,
        'precioCiva': precioFinalCivaTexto
    });
// debo mostarar los precios recalculado a dos o cuatro decimales, y ponerlos donden van sin tocar el jsonpara reacular luego ver la funcion comprar.
// cambiar los valores en los inputs, poner el precio con dos decimales en texto
// armar js con las funciones de articulo.
// pasar los datos de display y demas a jcart
// guardar el pedido.
let objNeto ,objImpIva,objImpInterno,objNetoTexto,objImpIvaTexto,objImpInternoTexto,objPrecioFinalCIvaTexto,objComoCuento,objDescuento;
// valores de los inputx hidden de cada articulo.
 objNeto = document.getElementById('mi-neto'+idArt);
//  objImpIva = document.getElementById('mi-iva');
 objImpInterno = document.getElementById('mi-imp-interno'+idArt);
 objComoCuento = document.getElementById('mi-como-cuento'+idArt);
objDescuento = document.getElementById('precio-descuento-texto-'+idArt);

// necesito ids para esto.
objNetoTexto = document.getElementById('precio-neto-texto-'+idArt);
//objImpIvaTexto = document.getElementById('');
objImpInternoTexto = document.getElementById('impuesto-interno-texto-'+idArt);
objPrecioFinalCIvaTexto = document.getElementById('precio-final-texto-'+idArt);
// label o span usan textcontent
objNetoTexto.textContent=precioNetoTexto;
// impuest interno
if(objImpInternoTexto!=null){
    objImpInternoTexto.textContent=impuestoInternoTexto;
}

objPrecioFinalCIvaTexto.textContent=precioFinalCivaTexto;
objNeto.value=precioNetoNuevo;
// console.log('objdescuentos que soy:',objDescuento);
if(objDescuento!=null){
    objDescuento.textContent = descuentoTexto;
}
objImpInterno.value=impuestoInternoNuevo;
objComoCuento.value=comoCuento;



}

function limpiarCheckTipoUnidad (id){
    // let icono='<i class="fas fa-check-circle fa-lg"></i>';
    let icono='<i class="fas fa-check-circle fa-lg" aria-hidden="true"></i>';
    let labelUnidad = document.querySelector("label[for='tipoUnidadUnidad"+id+"']");
    let labelDisplay = document.querySelector("label[for='tipoUnidadDisplay"+id+"']");
    let labelBulto = document.querySelector("label[for='tipoUnidadBulto"+id+"']");
    let contentDisplay = labelDisplay.innerHTML;
    let contentBulto = labelBulto.innerHTML;
    let contentUnidad = labelUnidad.innerHTML;
    console.log('vaciando los checks-->',labelUnidad,labelDisplay,labelBulto);
    if(labelUnidad!=null){
        labelUnidad.classList.remove("elegida");
    }

    labelDisplay.classList.remove("elegida");
    labelBulto.classList.remove("elegida");

    if(contentUnidad.includes('fa-check-circle')){
        
        contentUnidad = contentUnidad.replace(icono, '');
        labelUnidad.innerHTML = contentUnidad.trim();
    }
    console.log('contentBulto:',contentBulto,'contente Display:',contentDisplay);
    if(contentDisplay.includes('fa-check-circle')){
        
        contentDisplay = contentDisplay.replace(icono, '');
        labelDisplay.innerHTML = contentDisplay.trim();
    }
    // if(contentBulto.includes('fa-check')){
    if(contentBulto.includes('fa-check-circle')){

        contentBulto = contentBulto.replace(icono, '');
        labelBulto.innerHTML = contentBulto.trim();

    }
    // console.log('listo limpiar')
}


const funcionDescArt = 
                    function(){
                        
                       console.log('soy la descripcion tendre datos de sesion',laSesion);
                       console.log('soy mobil?=>',soyMobil);
                                var codigoArticulo = $(this).attr('rel');
                                var jsonProducto = $('#mi-json'+codigoArticulo).val();
                                // console.log('estoy viendo la descripcion codgio',codigoArticulo);
                                // console.log('estoin viendo el json de pdestalle',jsonProducto);
                               $.ajax({
                                    type:   'GET',
                                    url:    'relay-art-rapido.php',
                                    data:{
                                            "ajax":"true",
                                            "idArticulo": codigoArticulo,
                                            "jsonProducto": jsonProducto
                                    },
                                    success: function(response){
                                        // console.log('soy el click en el detalle',response);
                                        $('#basic-modal-content').empty(); 
                                        $('#basic-modal-content').html(response);
                                        if(soyMobil=='Si'){
                                            // si soy mobil

                                            $('#basic-modal-content').modal({
                                                maxWidth:360,
                                                minWidth: 360,
                                                minHeight:400,
                                                close:           false,
                                                
                                                    
                                                onShow: function(){

                                                    $('#cerrarModalD').on("click",function(e){
                                                        e.preventDefault();
                                                    $.modal.close(); 
                                                    });
                                                }
                                            });
                                        }

                                        // soy desktop
                                        if(soyMobil=='No'){
                                            $('#basic-modal-content').modal({
                                                maxWidth:600,
                                                minWidth: 600,
                                                minHeight:500,
                                                close:           false,
                                                position: ["30%","10%"],
                                                
                                                    
                                                onShow: function(){

                                                    $('#cerrarModalD').on("click",function(e){
                                                        e.preventDefault();
                                                    $.modal.close(); 
                                                    });
                                                }
                                            });
                                        }
                                       
//                                                $('#basic-modal-content').modal();

                                        return false;
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
                                            alert(m);
                                    }
                               });
                           };

        const funcionComprarArt = 
            function(){
                         
        //    console.log('hola hiciste click en comprar.')
            var quien = $(this).attr('name');
			var descValido = $('#mi-desc-'+quien);
            //me salio!!
            var nombre              ,
                    id              = quien,
                    cantidad        = $('#mi-cantidad-'+quien).val(),
                    saldoP          = $('#mi-saldo'+quien).val(),
                    precio          = $('#mi-art-precio'+quien).text(),
                    tipoIvaP        = $('#mi-tipo-iva'+quien).val(),
                    ivaP            = $('#mi-iva'+quien).val(),
                    alicuotaP       = $('#mi-alic-iva'+quien).val(),
                    impIvaP         = $('#mi-imp-iva'+quien).val(),
                    impInternoP     = $('#mi-imp-interno'+quien).val(),
                    netoP           = $('#mi-neto'+quien).val(),
                    descP           = $('#mi-desc-'+quien).val(),
                    impInternoTasaP = $('#mi-imp-interno-tasa'+quien).val(),
                    
                    porcentaje      = $('#mi-promoporc'+quien).val(),
                    cantPromo       = $('#mi-promocant'+quien).val(),
                    promoP          = $('#mi-promo'+quien).val(),
                    cantBulto       = $('#mi-bulto'+quien).val(),
                    ensambladoVta   = $('#mi-ensamblado-vta'+quien).val(),
                    tipoPromoP      = $('#mi-promotipo'+quien).val(),   
                    comoCuento      = $('#mi-como-cuento'+quien).val();   

                    let jsonArticulo = JSON.parse($('#mi-json'+quien).val());  
                    nombre = jsonArticulo.NombreArticulo;

                    let valorUnidadDisplay = 1;
                    let valorDisplayBulto = 1;
                    let precioCosto = jsonArticulo.PrecioCosto;
                   
                   if(jsonArticulo.cantidad_unidad_display!=null){
                    valorUnidadDisplay = jsonArticulo.cantidad_unidad_display;
                   }
                   if(jsonArticulo.cantidad_display_bulto!=null){
                    valorDisplayBulto = jsonArticulo.cantidad_display_bulto;
                   }
                    // colocar los campos a pasar de unidad,display y bulto.
                    // como conte
                    // cantidad contada
                    // multiplicador
                    // cantidadUnidadDisplay = objArt.cantidad_unidad_display;
                    //cantidadDisplayBulto = objArt.cantidad_display_bulto;
                    //console.table(jsonArticulo);        

//                    tengo que hacer el traspaso de los datos que tengo a los que
//                    se mandan por submit que ya estan preparametrizados.
            var form =$('.jcart');   
            var qty         = form.find('[name="my-item-qty"]'),
                idF         = form.find('[name="my-item-id"]'),
                idManual    = form.find('[name="my-item-id-manual"]'),
                name        = form.find('[name="my-item-name"]'),
                price       = form.find('[name="my-item-price"]'),
                tipoIva     = form.find('[name="my-item-tipoIva"]'),
                iva         = form.find('[name="my-item-iva"]'),
                alicuota    = form.find('[name="my-item-alicuota"]'),
                impIva      = form.find('[name="my-item-impIva"]'),
                impInterno  = form.find('[name="my-item-impInterno"]'),
                impInternoT = form.find('[name="my-item-impInternoTasa"]'),
                impInternoDescripcion   = form.find('[name="my-item-impInternoDescripcion"]'),
                impInternoTipo          = form.find('[name="my-item-impInternoTipo"]'),
                impInternoPorcentaje    = form.find('[name="my-item-impInternoPorcentaje"]'),
                impInternoMontoFijo     = form.find('[name="my-item-impInternoMontoFijo"]'),
                impInternoPesoCalculado = form.find('[name="my-item-impInternoPesoCalculado"]'),
                impInternoPagoMinimo    = form.find('[name="my-item-impInternoPagoMinimo"]'),
                impInternoIdUnimed      = form.find('[name="my-item-impInternoIdUnimed"]'),
                neto        = form.find('[name="my-item-neto"]'),   
                costo       = form.find('[name="my-item-costo"]'),                                         
                desc        = form.find('[name="my-item-descPor"]'),
                promo       = form.find('[name="my-item-promo"]'),
                promoCant   = form.find('[name="my-item-promoCant"]'),
                promoPorc   = form.find('[name="my-item-promoPorc"]'),
                promoTipo   = form.find('[name="my-item-promoTipo"]'),
                usoBulto    = form.find('[name="usa-bulto-promedio"]'),
                valorBulto  = form.find('[name="my-item-url"]'),
                permSinStock    = form.find('[name="permiso-sin-stock"]'),
                impInternoT = form.find('[name="my-item-impInternoTasa"]'),
                saldo       = form.find('[name="my-item-saldo"]'),


                valorDivisor = form.find('[name="my-item-cantidad-dividir"]'),
                cantidadMinimaContada = form.find('[name="my-item-cantidad-minima-contada"]'),
                textoComoCuento = form.find('[name="my-item-tipo-unidad-contada"]'),
                cantidadUnidadDisplay = form.find('[name="my-item-cantidad-unidad-display"]');
                console.log('como cuento?',comoCuento);

                var calculoCantidadMinimaContada;
                var divisor;
                // analisis de la cantidas display seleccionada.
                calculoCantidadMinimaContada = cantidad;
                divisor=1;

                if(comoCuento=='Display'){
                    calculoCantidadMinimaContada = cantidad * valorUnidadDisplay;
                    divisor=valorUnidadDisplay;
                }

                if(comoCuento=='Bulto'){
                    calculoCantidadMinimaContada = cantidad * valorUnidadDisplay * valorDisplayBulto;
                    divisor = valorUnidadDisplay*valorDisplayBulto;
                }

                // * cantidad Unidad display Bulto 
                // variables para pasar , como cuento, y el "divisor o cantidad Display x cnatidadDisplay Bulto y la cantidad minima."
               
                // convertir la cantidad a cantidad minima contada y para evaluar estado.
               
                // cantidad contada en falso o viene mal o no viene...
                if(isNaN(parseFloat(calculoCantidadMinimaContada))||parseFloat(calculoCantidadMinimaContada)==0){
                    $('#mi-cantidad-'+quien).select().focus();
                    alert("Cantidad incorrecta");
                    return false;
                }


                tipoIva.val(tipoIvaP);
                iva.val(ivaP); 
                alicuota.val(alicuotaP); 
                impIva.val(impIvaP); 
                
                impInterno.val(impInternoP);
                impInternoT.val(impInternoTasaP); 
                //  ** nuevo impuesto interno.
                if(jsonArticulo.interno_descripcion!=undefined){
                    impInternoDescripcion.val(jsonArticulo.interno_descripcion);
                    impInternoTipo.val(jsonArticulo.interno_tipo);
                    impInternoPorcentaje.val(jsonArticulo.interno_porcentaje);
                    impInternoMontoFijo.val(jsonArticulo.interno_monto_fijo);
                    impInternoPesoCalculado.val(jsonArticulo.interno_peso_calculado);
                    impInternoPagoMinimo.val(jsonArticulo.interno_pago_minimo);
                    impInternoIdUnimed.val(jsonArticulo.interno_id_unimed);
                }
                
//                console.log("Permiso de Bulto:="+usoBulto.val());
                // si uso multiplico el bulto
//                console.log("Permiso de Bulto:="+usoBulto.val());
                // si uso multiplico el bulto
                if(usoBulto.val()==="Si"){
//                    if(cantBulto<1){
//                        cantBulto =1;
//                    }
                    var total = parseFloat(cantidad*cantBulto);
                    // como tengo configuracion bulto promedio debo recalcular por el bulto. 
                    // console.log('dentro delbulto calculo cantida minimacontada,cantBulto,cantidad',calculoCantidadMinimaContada,cantBulto,cantidad)
                    calculoCantidadMinimaContada = calculoCantidadMinimaContada *cantBulto;
//                    console.log("Cantidad:="+cantidad+" Bultito:="+cantBulto+" total:="+total);
                    valorBulto.val(cantidad);
                    cantidad=total;

                }
               
                // stock no puede ser negativo para validar.
                if(parseFloat(saldoP)<0){
                    saldoP=0;
                }

                qty.val(cantidad); 
                saldo.val(saldoP);
                idF.val(id);
                idManual.val(jsonArticulo.id_manual);
                name.val(nombre);
                price.val(precio);
                neto.val(netoP);
                desc.val(descP);
                promo.val(promoP);
                promoCant.val(cantPromo);
                promoPorc.val(porcentaje);
                costo.val(precioCosto);
                promoTipo.val(tipoPromoP);
                impInternoT.val(impInternoTasaP); 
                

                /* validar Stock   */ 
                if(parseFloat(calculoCantidadMinimaContada)>parseFloat(saldoP)){
                        var exceso ='Si';
                }
                else{
                    var exceso = 'No';
    
                }

                                
                if(exceso=='Si'){
                    //alert("tengo permiso"+ permSinStock.val());
                    if (permSinStock.val() == 'No'&&ensambladoVta=='No') {
                        //no puedo operar sin stock
                    alert('Sin STOCK disponible, Cantidad a comprar:'+cantidadMinimaContada+ ' | Stock Disponible:'+saldoP);
                    return false;  
                    }
                }

                // pasar datos de unidad display...
                valorDivisor.val(divisor);
                cantidadMinimaContada.val(calculoCantidadMinimaContada);
                textoComoCuento.val(comoCuento);
                cantidadUnidadDisplay.val(valorUnidadDisplay);


				// validar descuentos              
                var valorDescuento=parseFloat(descValido.val()),
                    minimo=parseFloat(descValido.attr("min")),
                    maximo=parseFloat(descValido.attr("max"));
                if(valorDescuento<minimo || valorDescuento>maximo){
                   
                    descValido.val(maximo);
                    return false;
                }
				
                form.submit();
                var totalCarrito = parseFloat($('#totalCarrito').text());
//                console.log($('#totalCarrito').text());
                totalCarrito = totalCarrito +1;
                $('#totalCarrito').text(totalCarrito);
            };
// geo localizacion.
//=================
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

function mostrarBuscarProductos(){
    console.log('soy Mobil mostrar buscar productos=>',soyMobil);
    if(soyMobil=='Si'){
        $("#panelBuscaRapido").animate({ height: "show" }, 300 );
        $("#contiene-tabla-comprobante").animate({ height: "show" }, 300 );                  
        $("#sidebar").animate({ height: "hide" }, 300 );
    }
}

function mostrarCarrito(){
    console.log('soy Mobil mostrar carrito=>',soyMobil);
    if(soyMobil=='Si'){
        $("#panelBuscaRapido").animate({ height: "hide" }, 300 );
        $("#contiene-tabla-comprobante").animate({ height: "hide" }, 300 );
        $("#sidebar").animate({ height: "show" }, 300 );
    }
}