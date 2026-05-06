
// jCart v1.3 REMITO
// http://conceptlogic.com/jcart/

$(function() {
     
	var JCART = (function() {
                
		// This script sends Ajax requests to config-loader.php and relay.php using the path below
		// We assume these files are in the 'jcart' directory, one level above this script
		// Edit as needed if using a different directory structure
		//agregado codigo del spinner
        $("#spinner").bind("ajaxSend", function() {
            $(this).hide();
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();

        }).bind("ajaxError", function() {
            $(this).hide();
        });
        $('.close').on("click",function(){
            $('#alertas').hide();
        });
        //$('#modal-stock-articulo').hide();        
        $('#renglonlote').hide();
                //codigo del date value
        // inicializar las busquedas
         $('label[for=queArticulo]').hide();
        $('#divBuscaArticulo').show();
        $('#divBarraArticulo').addClass("ochenta");
        $('#buscarArticuloR').show();
       // $('#buscarArticulo').hide(); 
        //$('#divBuscaRubro').hide();
        
                
        var path = 'tmobile/jcart',
			container = $('#jcart'),
			token = $('[name=jcartToken]').val(),
			tip = $('#jcart-tooltip');

		var config = (function() {
			var config = null;
			$.ajax({
				url: path + '/config-loader.php',
				data: {
					"ajax": "true"
				},
				dataType: 'json',
				async: false,
				success: function(response) {
					config = response;
				},
				error: function() {
					alert('Ajax error: Edit the path in jcart.js to fix.');
				}
			});
			return config;
		}());

		var setup = (function() {
			if(config.tooltip === true) {
				tip.text(config.text.itemAdded);
                //$( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });  
				// Tooltip is added to the DOM on mouseenter, but displayed only after a successful Ajax request
				$('.jcart [type=submit]').mouseenter(
					function(e) {
						var x = e.pageY + 25,
							y = e.pageX + -10;
						$('body').append(tip);
						tip.css({top: y + 'px', left: x + 'px'});
					}
				)
				.mousemove(
					function(e) {
						var y = e.pageY + 25,
						x = e.pageX + -10;
						tip.css({top: y + 'px', left: x + 'px'});
					}
				)
				.mouseleave(
					function() {
						tip.hide();
					}
				);
			}
                        

			// Remove the update and empty buttons since they're only used when javascript is disabled
			$('#jcart-buttons').remove();                           
                       
			// Default settings for Ajax requests
			$.ajaxSetup({
				type: 'POST',
				url: path + '/relay.php',
				success: function(response) {
					// Refresh the cart display after a successful Ajax request
					container.html(response);
					$('#jcart-buttons').remove();
                    //$( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });
                    
				},
				// See: http://www.maheshchari.com/jquery-ajax-error-handling/
				error: function(x, e) {
					var s = x.status, 
						m = 'Ajax error: ' ; 
					if (s === 0) {
						m += 'Check your network connection.1';
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
		}());
        //activar y desactivar el boton TIPO DE BUSQUEDA 
//        $("input[name='tipoBusqueda']").change(function(){
//            var valor = $("input[name='tipoBusqueda']:checked").val();
//    //                    alert(valor);
//            switch(valor){
//                case '0':
////                    $('#divBuscaArticulo').css({display:"inline"});
////                    $('#divBuscaRubro').css({display:"none"});
////                    $('#buscarArticuloR').css({display:"inline"});
////                    $('#buscarArticulo').css({display:"none"});
//                    
//                    $('#divBuscaArticulo').show();
//                    $('#divBuscaRubro').hide();
//                    $('#buscarArticuloR').show();
//                    $('#buscarArticulo').hide();
//    //                            alert('dentro='+valor);
//                    break;
//                case '1':
////                    $('#divBuscaArticulo').css({display:"inline"});
////                    $('#divBuscaRubro').css({display:"none"});
////                    $('#buscarArticuloR').css({display:"none"});
////                    $('#buscarArticulo').css({display:"none"});
//                    
//                    $('#divBuscaArticulo').show();
//                    $('#divBuscaRubro').hide();
//                    $('#buscarArticuloR').hide();
//                    $('#buscarArticulo').hide();
//    //              
//    //                            alalert('dentro='+valor);ert('dentro='+valor);
//                    break;
//                case '2':
////                    $('#divBuscaArticulo').css({display:"none"});
////                    $('#divBuscaRubro').css({display:"inline"});
////                    $('#buscarArticuloR').css({display:"none"});
////                    $('#buscarArticulo').css({display:"inline"});
//                    $('#divBuscaArticulo').hide();
//                    $('#divBuscaRubro').show();
//                    $('#buscarArticuloR').hide();
//                    $('#buscarArticulo').show();
//    //                            alert('dentro='+valor);
//                    break;
//            }
//
//
//        });
        // funciones para optimizar codigo
        // funcion para el boton de comprar articulo.
        /*
         * COMPRAR ARTICULO
         * =====================================================================
         * **/
        var funcionComprarArt = 
            function(){
                       
                        //alert('aca anduvo cuatro');

                        var quien = $(this).attr('name');
                        //me salio!!
                        var nombre              = $('#mi-art-nombre'+quien).text(),
                            id                  = quien,
                            cantidad            = $('#mi-cantidad-'+quien).val(),
                            precio              = $('#mi-art-precio'+quien).text(),
                            tipoIvaP            = $('#mi-tipo-iva'+quien).val(),
                            ivaP                = $('#mi-iva'+quien).val(),
                            alicuotaP           = $('#mi-alic-iva'+quien).val(),
                            impIvaP             = $('#mi-imp-iva'+quien).val(),
                            impInternoP         = $('#mi-imp-interno'+quien).val(),
                            netoP               = $('#mi-neto'+quien).val(),
                            descP               = $('#mi-desc-'+quien).val(),
                            impInternoTasaP     = $('#mi-imp-interno-tasa'+quien).val(),
                            porcentaje          = $('#mi-promoporc'+quien).val(),
                            cantPromo           = $('#mi-promocant'+quien).val(),
                            promoP              = $('#mi-promo'+quien).val(),
                            loteP               = $('#mi-lote'+quien).val(),
                            saldoP              = $('#mi-saldo'+quien).val(),
                            tipoPromoP          = $('#mi-promotipo'+quien).val(),   
                            controlCantFact     = $('#mi-cant-factura'+quien).val();    
                                //idDeposito          = $('#idDeposito').val();

    //                    tengo que hacer el traspaso de los datos que tengo a los que
    //                    se mandan por submit que ya estan preparametrizados.
                         var form =$('.jcart');   
                         var qty         = form.find('[name="my-item-qty"]'),
                            idF         = form.find('[name="my-item-id"]'),
                            name        = form.find('[name="my-item-name"]'),
                            price       = form.find('[name="my-item-price"]'),
                            tipoIva     = form.find('[name="my-item-tipoIva"]'),
                            iva         = form.find('[name="my-item-iva"]'),
                            alicuota    = form.find('[name="my-item-alicuota"]'),
                            impIva      = form.find('[name="my-item-impIva"]'),
                            impInterno  = form.find('[name="my-item-impInterno"]'),
                            neto        = form.find('[name="my-item-neto"]'),

                            desc        = form.find('[name="my-item-descPor"]'),
                            promo       = form.find('[name="my-item-promo"]'),
                            promoCant   = form.find('[name="my-item-promoCant"]'),
                            promoPorc   = form.find('[name="my-item-promoPorc"]'),
                            impInternoT = form.find('[name="my-item-impInternoTasa"]'),
                            lote        = form.find('[name="my-item-lote"]'),
                            idDeposito  = form.find('[name="my-item-idDeposito"]'),
                            saldo       = form.find('[name="my-item-saldo"]'),
                            promoTipo   = form.find('[name="my-item-promoTipo"]'),
                            entregado       = form.find('[name="my-item-entregado"]'),
                            permSinStock    = form.find('[name="permiso-sin-stock"]'),
                            qtyLabel        = form.find('[name="my-item-qty-label"]'),
                            idFLabel        = form.find('[name="my-item-id-label"]'),
                            nameLabel       = form.find('[name="my-item-name-label"]'),
                            saldoLabel      = form.find('[name="my-item-saldo-label"]'),
                            priceLabel      = form.find('[name="my-item-price-label"]');

                            tipoIva.val(tipoIvaP);
                            iva.val(ivaP); 
                            alicuota.val(alicuotaP); 
                            impIva.val(impIvaP); 
                            impInterno.val(impInternoP);
                            qty.val(cantidad); 
                            idF.val(id);
                            name.val(nombre);
                            price.val(precio);
                            neto.val(netoP);
                            desc.val(descP);
                            promo.val(promoP);
                            promoCant.val(cantPromo);
                            promoPorc.val(porcentaje);
                            promoTipo.val(tipoPromoP);
                            impInternoT.val(impInternoTasaP); 
                            lote.val(loteP);
                            saldo.val(saldoP);
                            qtyLabel.html(cantidad); 
                            idFLabel.html(id);
                            nameLabel.html(nombre);
                            priceLabel.html('<i class="fa fa-dollar"></i>' + precio);
                            saldoLabel.html(saldoP);

                // valido si la cantidad es mayoer que el saldo
//                alert('cantidad =>' +cantidad+' - Saldo>=' +saldoP+ " - PermSinStock:: "+permSinStock.val());
                            
                            //si no vengo de remito factura el valor sera CERO
                            // si el valor es distinto de CERO y menor que la cantidad
                            // es decor que estoy entregando de mas, 
                            // fuerzo a colocar lo que tengo diponible,
                            // lo voy a mostrar en el articulo.
                            console.log("cantidad que puse{"+cantidad+"} cantDisponible {"+controlCantFact+"}");
                            if(controlCantFact!==undefined){
                                // me excedi con lo que podia remitar
                                if(parseFloat(cantidad)>parseFloat(controlCantFact)){
                                    // fuerzo a que solo se entregue lo que esta disponible.
                                    console.log("estoy remitando de mas, fuerzo la remitada"+controlCantFact);
                                    cantidad=controlCantFact;
                                    qty.val(cantidad);
                                     $('#mi-cantidad-'+quien).val(cantidad);

                                }
                                // cantidad de menos o todo.
//                                if(parseFloat(cantidad)<= parseFloat(controlCantFact)){
//                                    var diferenciaFact = parseFloat(controlCantFact)-parseFloat(cantidad);
//                                // remite bien pero debo modificar mi filtro.
//                                    console.log("mi cantidad nueva validada.."+(diferenciaFact));
//                                    $('#mi-cant-factura'+quien).val(diferenciaFact);
//                                    $('#mi-cantidad-'+quien).val(diferenciaFact);
//                                    //qty.val(diferenciaFact);
//                                    if(diferenciaFact===0){
//                                        console.log("no tengo mas que remitar igual que cero");
//                                        
//                                    }
//                                }
                                $(this).hide(); 
                            }
                            
                            if(parseFloat(cantidad)>parseFloat(saldoP)){
                                var exceso ='Si';
                                entregado.val('');
                            }
                            else{
                                var exceso = 'No';

                                //si no me excedi entonces pregunto igual
                                entregado.val('');
                            }

                                
                            if(exceso=='Si'){
                                //alert("tengo permiso"+ permSinStock.val());
                                if(permSinStock.val()=='No'){
                                    //no puedo operar sin stock
                                   $('#sinStock').show();
                                   $('#artSinStock').hide();
                                   $('#artConStock').hide();
                                   $('#artSinPermiso').show();
                                   $('#envPedido').hide();
                                   $('#envRemito').hide();
                                   $('#envNo').show();
                                   $('#envNoLabel').show();
                                   $('#envNo').attr('checked',true);
                                   $('#envRemitoLabel').hide();
                                   $('#envPedidoLabel').hide();
                                   
                                }else{
                                    
                                   $('#sinStock').show();
                                   $('#artSinStock').show();
                                   $('#artConStock').hide();
                                   $('#artSinPermiso').hide();
                                   $('#envPedido').show();
                                   $('#envRemito').show();
                                   $('#envNo').hide();
                                   $('#envNoLabel').hide();
                                   $('#envRemitoLabel').show();
                                   $('#envPedidoLabel').show();
                                   
                                }
                            }else{
                                $('#sinStock').show();
                                $('#artSinStock').hide();
                                $('#artConStock').show();
                                $('#artSinPermiso').hide();
                                $('#envPedido').show();
                                $('#envRemito').show();
                                $('#envNo').hide();
                                $('#envNoLabel').hide();
                                $('#envRemitoLabel').show();
                                $('#envPedidoLabel').show();
                            }
                                             
                                /** lotes **/
                                   $('#selLote').hide();

                                   $.ajax({
                                       type:   'POST',
                                       url:    'relay-lote.php',
                                       data:{
                                               "ajax":"true",
                                               "idArt": id,
                                               "idDeposito": idDeposito.val()
                                       },
                                       success: function(response){
                                           $('#selLote').empty();
                                           $('#selLote').html(response);
                                           $('#selLote').show();
                                           
                                           $('#modal-stock-articulo').modal({
                                               position:        ["1%","0.5%"],
                                               //maxWidth:        400,
                                               close:           false,
                                               
                                                
                                               onShow: function(){
                                                        $('#renglonlote').show();
                                                        $('#cerrarModal').on("click",function(e){
                                                            e.preventDefault();
                                                           $.modal.close(); 
                                                        });
                                                    }
                                            });
                                            
                                           

                                           //return false;
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
                            //}  
           var totalCarrito = parseFloat($('#totalCarrito').text());
//                console.log($('#totalCarrito').text());
                totalCarrito = totalCarrito +1;
                $('#totalCarrito').text(totalCarrito);                  
                                
        };
        // funcion para mostrar la descripcion de un articulo ** -
        var functionDescArt = function(){
            //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                            var codigoArticulo = $(this).attr('rel');
                           $.ajax({
                                type:   'POST',
                                url:    'relay-art-rapido.php',
                                data:{
                                        "ajax":"true",
                                        "idArticulo": codigoArticulo
                                },
                                success: function(response){
                                    $('#basic-modal-content').empty();
                                    $('#basic-modal-content').html(response);
            //                                                $('#basic-modal-content').modal();
                                    $('#basic-modal-content').modal({
                                             position:        ["1.5%","1%"],
                                             minWidth: 290,
                                             minHeight:350,
                                             close:           false,
                                               
                                                
                                            onShow: function(){

                                                $('#cerrarModalD').on("click",function(e){
                                                    e.preventDefault();
                                                   $.modal.close(); 
                                                });
                                            }
                                        });
                                    return false;
                                },
                                error: function(x, e) {
                                        var s = x.status, 
                                                m = 'Ajax error: ' ; 
                                        if (s === 0) {
                                                m += 'Check your network connection.2';
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
                
                //funcion para la busqueda rapida por boton
                //@buscarArticuloR = es el boton para buscar generico para todos
                
                $('#buscarArticuloR').click(function(){
                     var contienes = $('#myTable'),   
                    buscaRapida = $('#queArticulo').val(),
//                        claseBusqueda = $('#claseBusqueda').val(),
                            //meti por defecto que busque en el incluye texto.
                        claseBusqueda = 1,
                        idArticulo  = $('#queArticuloId').val(),
                        cantidadOferta = $('[name="my-item-qty"]').val(),
                        queCampo= $('input[name="queCampo"]:checked').val();
                        
                        
                        // las opciones 
                    var categoria = $('#buscaCategoria').val(),
                        rubro = $('#buscaRubro').val(),
                        subrubro = $('#buscaSubRubro').val(),
                        marca =$('#buscaMarca').val(),
                        modelo=$('#buscaModelo').val(),
                        misConsumos=$('#buscaMisConsumos'),
                        promociones=$('#buscaPromo'),
                        consumo=0,
                        promo=0;
                        console.log('misconsumos::{'+misConsumos.prop("checked")+'}');
                        console.log('mis promociones::{'+promociones.prop("checked")+'}');        
//                        idOferta = $('[name="my-item-oferta-id"]').val();
                        if(cantidadOferta==""){
                            cantidadOferta = 1;
                        }
                        // mis consumos
                        if(misConsumos.prop("checked")===true){
                            consumo=1;
                        }
                        // mis promociones
                        if(promociones.prop("checked")===true){
                            promo=1;
                        }
                        // busqueda NORMAL
                   
                        $.ajax({
                            type: 'POST',
                            url: 'relay-art.php',
                            data:{                                
                                "ajax" : "true",
                                "queArticulo"       : buscaRapida,
                                "buscarArticulo"    : "buscarArticulo",
                                "idArticulo"        : idArticulo,
                                "cantidadOferta"    : cantidadOferta,
                                "claseBusca"        : claseBusqueda,
                                "categoria"         : categoria,
                                "rubro"             : rubro,
                                "subrubro"          : subrubro,
                                "marca"             : marca,
                                "modelo"            : modelo,
                                "queCampo"          : queCampo,
                                "consumo"           : consumo,
                                "promo"             : promo
                                
                            },
                            success: function(response) {
//                                console.log(response);
                                    // Refresh the cart display after a successful Ajax request
                                    //alert(response);
                                    if(response=='0'){
                                        //alert('traje vacio');
                                        contienes.empty();
                                        
                                        contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
                                        
                                        $("#spinner").hide();
                                    }
                                    else{
                                        //alert(response);
                                        contienes.empty();
                                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                                            contienes.DataTable().destroy();
                                        }
                                        contienes.html(response);
                                        contienes.DataTable({
                                            searching: false,
                                            responsive:false,
                                            "language": {
                                                "emptyTable":     "No data available in table",
                                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
                                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
                                                "infoPostFix":    "",
                                                "thousands":      "",
                                                "lengthMenu":     "Ver _MENU_ entradas",
                                                "loadingRecords": "Loading...",
                                                "processing":     "Processing...",
                                                "search":         "Buscar:",
                                                "zeroRecords":    "No matching records found",
                                                "paginate": {
                                                    "first":      "Primero",
                                                    "last":       "Ultimo",
                                                    "next":       "Siguiente",
                                                    "previous":   "Anterior"
                                                },
                                                "aria": {
                                                    "sortAscending":  ": activate to sort column ascending",
                                                    "sortDescending": ": activate to sort column descending"
                                                }
                                            },
                                            "ordering": false
                                        });
                                        $("#spinner").hide();
                                        $("input").on("click",function() {
                                            console.log("click en input" +$(this));
                                           $(this).select(); 
                                        });
                                        $("input[type='number']").on("focus",function() {
                                            console.log("click en focus" +$(this));
                                           $(this).select(); 
                                        });
                                        
                                        
                                        $('#queArticuloId').val('');
                                        //mostrar la modal
                                        $('#myTable tbody').on("click","td a.desc-articulo", functionDescArt);
                                         
                                        //$(".desc-articulo").click(funcionDescArt);                                    
                                         $('#myTable tbody').on("click","td .tecompro", funcionComprarArt);
//                                    $(".desc-articulo").click(functionDescArt);
//                                    $(".tecompro").click(funcionComprarArt); 
                                    
                                    
                                    //$('#jcart-buttons').remove();
                                }// el cierre del else del        
                            },
                            // See: http://www.maheshchari.com/jquery-ajax-error-handling/
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
                    
    
                
                });
                
                //funcion de BUSQUEDA RAPIDA
//                $('#queArticulo').keyup(function(){
//                
//                    var contienes = $('#myTable'),
//                        comoBusco = $("input[name='tipoBusqueda']:checked").val(),
//                        claseBusqueda   = $('#claseBusqueda').val(),
//                        buscaRapida = $('#queArticulo').val();
//                    if(comoBusco==1){
//                          if ( buscaRapida.length > 2 ) {
//                            $.ajax({
//                                type: 'POST',
//                                url: 'relay-art.php',
//                                data:{
//                                    "ajax" : "true",
//                                    "queArticulo" : buscaRapida,
//                                    "buscarArticulo": "buscarArticulo",
//                                    "claseBusca"        : claseBusqueda
//                                },
//                                success: function(response) {
//                                        // Refresh the cart display after a successful Ajax request
//    //                                    alert(response); 
//                                        if(response=='0'){
//                                            //alert('traje vacio');
//                                            contienes.empty();
//                                            contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
//                                            $("#spinner").hide();
//                                        }else{
//                                            contienes.empty();
//                                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
//                                            contienes.DataTable().destroy();
//                                        }
//                                        
//                                        contienes.html(response);
//                                        contienes.DataTable({
//                                            searching: false,
//                                            responsive:true,
//                                            "language": {
//                                                "emptyTable":     "No data available in table",
//                                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
//                                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
//                                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
//                                                "infoPostFix":    "",
//                                                "thousands":      "",
//                                                "lengthMenu":     "Ver _MENU_ entradas",
//                                                "loadingRecords": "Loading...",
//                                                "processing":     "Processing...",
//                                                "search":         "Buscar:",
//                                                "zeroRecords":    "No matching records found",
//                                                "paginate": {
//                                                    "first":      "Primero",
//                                                    "last":       "Ultimo",
//                                                    "next":       "Siguiente",
//                                                    "previous":   "Anterior"
//                                                },
//                                                "aria": {
//                                                    "sortAscending":  ": activate to sort column ascending",
//                                                    "sortDescending": ": activate to sort column descending"
//                                                }
//                                            },
//                                            "ordering": false
//                                        });
//                                        $("#spinner").hide();
//                                        $('#myTable tbody').on("click","td a.desc-articulo", functionDescArt);
//                                         
//                                        //$(".desc-articulo").click(funcionDescArt);                                    
//                                         $('#myTable tbody').on("click","td i.tecompro", funcionComprarArt);
//                                        
//
//                                        //$('#jcart-buttons').remove();
//                                    }//fin del else sobre retorno de valores cero     
//                                },
//                                // See: http://www.maheshchari.com/jquery-ajax-error-handling/
//                                error: function(x, e) {
//                                        var s = x.status, 
//                                                m = 'Ajax error: ' ; 
//                                        if (s === 0) {
//                                                m += 'Check your network connection.5';
//                                        }
//                                        if (s === 404 || s === 500) {
//                                                m += s;
//                                        }
//                                        if (e === 'parsererror' || e === 'timeout') {
//                                                m += e;
//                                        }
//                                        alert(m);
//                                }
//                            });
//                        }
//                    }else{
//                        return false;
//                    }
//    
//                });
		// Check hidden input value
		// Sent via Ajax request to jcart.php which decides whether to display the cart checkout button or the PayPal checkout button based on its value
		// We normally check against request uri but Ajax update sets value to relay.php

		// If this is not the checkout the hidden input doesn't exist and no value is set
		var isCheckout = $('#jcart-is-checkout').val();
                $( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });
        
		function add(form) {
			// Input values for use in Ajax post
                        
			var itemQty = form.find('[name=' + config.item.qty + ']'),
				itemAdd = form.find('[name=' + config.item.add + ']');
//            alert(form.serialize());                    
			// Add the item and refresh cart display
			$.ajax({
				data: form.serialize() + '&' + config.item.add + '=' + itemAdd.val(),
				success: function(response) {

					// Momentarily display tooltip over the add-to-cart button
					if (itemQty.val() > 0 && tip.css('display') === 'none') {
						tip.fadeIn('100').delay('400').fadeOut('100');
					}

					container.html(response);
                                        $('#jcart-buttons').remove();
                                        $( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });  
				}
			});
		}

		function update(input) {
			// The id of the item to update
			var updateId = input.parent().find('[name="jcartItemId[]"]').val();

			// The new quantity
			var newQty = input.val();

			// As long as the visitor has entered a quantity
			if (newQty) {

				// Update the cart one second after keyup
				var updateTimer = window.setTimeout(function() {

					// Update the item and refresh cart display
					$.ajax({
						data: {
							"jcartUpdate": 1, // Only the name in this pair is used in jcart.php, but IE chokes on empty values
							"itemId": updateId,
							"itemQty": newQty,
							"jcartIsCheckout": isCheckout,
							"jcartToken": token
						}
					});
				}, 1000);
			}

			// If the visitor presses another key before the timer has expired, clear the timer and start over
			// If the timer expires before the visitor presses another key, update the item
			input.keydown(function(e){
				if (e.which !== 9) {
					window.clearTimeout(updateTimer);
				}	
			});
            
		}

		function remove(link) {
			// Get the query string of the link that was clicked
			var queryString = link.attr('href');
			queryString = queryString.split('=');

			// The id of the item to remove
			var removeId = queryString[1];

			// Remove the item and refresh cart display
			$.ajax({
				type: 'GET',
				data: {
					"jcartRemove": removeId,
					"jcartIsCheckout": isCheckout
				}
                
                
			});
//            alert("que borro:: "+removeId);
            //$('.renglonCarro').click(funcionClikCarro); 
		}
                
//             buscamos el subrubro
                $('#buscaCategoria').change(function(){
                    var categoria = $(this).val();
                    //alert($(this).val());
                    $('#buscaRubro').empty();
                    if(categoria!==""){
                        $.ajax({
                            type: 'get' ,
                            url: 'relay-rubro.php',
                            data: {
                                "ajax" : true,
                                "idcategoria" : categoria
                            },
                            dataType: 'json',
                            success: function (j) {                   
                                //alert(j);
                                var options = [], i = 0, o = null;
                                //alert(j.length);
                                for(i = 0; i < j.length; i++) {
                                    // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                                    o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                                    o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                                    o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                                    $('#buscaRubro').append(o);
                                }
                            },

                            error: function (xhr, desc, er) {
                                // add whatever debug you want here.
                                alert("an error occurred"+xhr+desc+er);
                            }
                        });
                    }else{
                        var o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                        o.value = "";
                        o.text ="- todos -";
//                            
                        $('#buscaRubro').append(o);
                        $('#buscaRubro').change();
                    }
                    
                });
                // buscar subrubro al seleccionar un rubro ddo
                
                $('#buscaRubro').change(function(){
                    var rubro = $(this).val();
                    console.log($(this).val());
                    $('#buscaSubRubro').empty();
                    if (rubro!==""){
                        $.ajax({
                            type: 'get' ,
                            url: 'relay-rubro.php',
                            data: {
                                "ajax" : true,
                                "idrubro" : rubro
                            },
                            dataType: 'json',
                            success: function (j) {                   
                               console.log(j);
                                var options = [], i = 0, o = null;
                                //alert(j.length);
                                for(i = 0; i < j.length; i++) {
                                    // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                                    o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                                    o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                                    o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                                    $('#buscaSubRubro').append(o);
                                }
                            },

                            error: function (xhr, desc, er) {
                                // add whatever debug you want here.
                                alert("an error occurred"+xhr+desc+er);
                            }
                        });
                    }else{
                        // crear elemento vacio.
                    }
                });
            
                // buscamos modelos
                $('#buscaMarca').change(function(){
                    var marca = $(this).val();
                    //alert($(this).val());
                    $('#buscaModelo').empty();
                    if(marca!==""){
                        $.ajax({
                            type: 'get' ,
                            url: 'relay-marca.php',
                            data: {
                                "ajax" : true,
                                "idmarca" : marca
                            },
                            dataType: 'json',
                            success: function (j) {                   
                                //alert(j);
                                var options = [], i = 0, o = null;
                                //alert(j.length);
                                for(i = 0; i < j.length; i++) {
                                    // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
                                    o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
                                    o.value = typeof j[i] == 'object' ? j[i]['id'] : j[i];
                                    o.text = typeof j[i] == 'object' ? j[i]['name'] : j[i];
        //                            
                                    $('#buscaModelo').append(o);
                                }
                            },

                            error: function (xhr, desc, er) {
                                // add whatever debug you want here.
                                alert("an error occurred"+xhr+desc+er);
                            }
                        });
                    }
                });   
                
                // buscar un articulo
//                $('#buscarArticulo').click(function(){
//                    //voy a resetear la cantidad antes de buscar.
//                    var form =  $('.jcart');   
//                    var qty  =  form.find('[name="my-item-qty"]');
//                    qty.val(1);
//                    //alert(qty.val());
//                    var contienes       = $('#myTable'),
//                        buscaRapida     = $('#queArticulo').val(),
//                        rubro           = $('#buscaRubro').val(),
//                        subrubro        = $('#buscaSubRubro').val(),
//                        marca           = $('#buscaMarca').val(),
//                        modelo          = $('#buscaModelo').val();
//                        if(buscaRapida=='Ingrese nombre de Articulo'){
//                            buscaRapida = '';
//                        }
////                        alert(rubro + subrubro+ marca + modelo+buscaRapida );
//                    $.ajax({
//                            type: 'POST',
//                            url: 'relay-art.php',
//                            data:{
//                                "ajax" : true,
//                                "queArticulo" : buscaRapida,
//                                "buscaRubro" : rubro,
//                                "buscaSubRubro" : subrubro,
//                                "buscaMarca" : marca,
//                                "buscaModelo": modelo,
//                                "buscarArticulo": "buscarArticulo"
//                            },
//                            success: function(response) {
//                                    // Refresh the cart display after a successful Ajax request
////                                    alert(response); 
//                                    if(response=='0'){
//                                        //alert('traje vacio');
//                                        contienes.empty();
//                                        contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
//                                        $("#spinner").hide();
//                                    }
//                                    else{
//                                        contienes.empty();
//                                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
//                                            contienes.DataTable().destroy();
//                                        }
//                                        
//                                        contienes.html(response);
//                                        contienes.DataTable({
//                                            searching: false,
//                                            responsive:true,
//                                            "language": {
//                                                "emptyTable":     "No data available in table",
//                                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
//                                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
//                                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
//                                                "infoPostFix":    "",
//                                                "thousands":      "",
//                                                "lengthMenu":     "Ver _MENU_ entradas",
//                                                "loadingRecords": "Loading...",
//                                                "processing":     "Processing...",
//                                                "search":         "Buscar:",
//                                                "zeroRecords":    "No matching records found",
//                                                "paginate": {
//                                                    "first":      "Primero",
//                                                    "last":       "Ultimo",
//                                                    "next":       "Siguiente",
//                                                    "previous":   "Anterior"
//                                                },
//                                                "aria": {
//                                                    "sortAscending":  ": activate to sort column ascending",
//                                                    "sortDescending": ": activate to sort column descending"
//                                                }
//                                            },
//                                            "ordering": false
//                                        });
//                                        $("#spinner").hide();
//                                       $('#myTable tbody').on("click","td a.desc-articulo", functionDescArt);
//                                         
//                                        //$(".desc-articulo").click(funcionDescArt);                                    
//                                         $('#myTable tbody').on("click","td i.tecompro", funcionComprarArt);
//                                        
//                                    }
//                        },
//                            // See: http://www.maheshchari.com/jquery-ajax-error-handling/
//                            error: function(x, e) {
//                                    var s = x.status, 
//                                            m = 'Ajax error: ' ; 
//                                    if (s === 0) {
//                                            m += 'Check your network connection.8';
//                                    }
//                                    if (s === 404 || s === 500) {
//                                            m += s;
//                                    }
//                                    if (e === 'parsererror' || e === 'timeout') {
//                                            m += e;
//                                    }
//                                    alert(m);
//                            }
//                    });
//                });    
           var funcionClikCarro = function(){
               //alert('adentro');
                var codigoArticulo = $(this).attr('rel');
                var codigoLote = $(this).attr('lote');
               $.ajax({
                    type:   'POST',
                    url:    'relay-art-rapido.php',
                    data:{
                            "ajax":"true",
                            "idArticulo": codigoArticulo,
                            "idLote" : codigoLote
                    },
                    success: function(response){
                       
                        $('#basic-modal-content').empty();
                        $('#basic-modal-content').html(response);
                        $('#basic-modal-content').modal({
                            minHeight : 200,
                            minWidth : 290
                        });

                        return false;
                    },
                    error: function(x, e) {
                            var s = x.status, 
                                    m = 'Ajax error: ' ; 
                            if (s === 0) {
                                    m += 'Check your network connection.9';
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
         $('.renglonCarro').click(funcionClikCarro);       
	 	
        
        
        // Add an item to the cart
//        $("#but").click(function(){
//           alert("hago algo");
//           //$(".jcart").submit();
//        });
        /// Insertar item en la  modal
        
        $(".jcart").submit(function (e) {
            e.preventDefault();
            var errores = 0;
            //alert($(this));
            var form = $(this);
            //pedir el item lote y verificar si hay datos.
            var entregado = form.find('[name="my-item-entregado"]'),
                    hayLote = form.find('[name="my-item-lote"]').val(),
                    loteId = form.find('[name="my-item-idLote"]:checked');

//            alert(entregado.val());
            var respuesta = form.find('[name="entregarItem"]:checked');
            if (respuesta.val() == 2) {
                errores++;
            } else {
                if (entregado.val() == '') {
                    //pregunto si se ha seleccinado algo.

                    //alert(respuesta.val());
                    if (respuesta.val() == undefined) {
                        $('.texto-alerta').html('Debe seleccionar REMITO o PEDIDO');
                        $('#alertas').show();
//                        alert("Debe decidir que hacer con el articulo");
                        errores++;
                        //e.preventDefault();
                    } else {
                        if (respuesta.val() == 0) {
                            entregado.val("Si");
                        }
                        if (respuesta.val() == 1) {
                            entregado.val("No");
                        }
                    }

                    respuesta.prop("checked", false);

                }
            }

            if ((hayLote == "Si") && (respuesta.val() == 0)) {
                if (loteId.val() == undefined) {
                    //no eligio el lote
                    //alert("Debe seleccionar un Lote");
                    $('.texto-alerta').html('Debe seleccionar un LOTE');
                    $('#alertas').show();
                    errores++;
                    //e.preventDefault();
                }
            }


            // evaluar si existen los campos
            if ($('#jcart-fecha-rem').val()!== undefined) {
                // voy a recuperar los datos del carrito

                var fechaTalonario = $('#jcart-fecha-rem').val(),
                        pventaTalonario = $('#jcart-suc').val(),
                        nroTalonario = $('#jcart-nro-rem').val();
                //alert("Fecha Originial:=>"+fechaTalonario);

                // traspaso de datos del jcart al clone

                form.find('[name="jcart-fecha-rem-clon"]').val(fechaTalonario);
                form.find('[name="jcart-suc-clon"]').val(pventaTalonario);
                form.find('[name="jcart-nro-rem-clon"]').val(nroTalonario);
            }
            //alert("Clon=>"+form.find('[name="jcart-fecha-rem-clon"]').val());

            //form.find('[name=jcart-fecha-rem'')                        
            if (errores == 0) {
                add($(this));

                $('#selLote').empty();
                $('#renglonlote').hide();
                $.modal.close();
                $('#cartelItemAgregado').show("fast", "linear");
                $('#cartelItemAgregado').hide(4000, "linear");
                $('#queArticulo').focus();
            }
            
            
//            var pepe = $(this).find('[name="my-item-idLote"]')
//            alert(pepe.val());
			//e.preventDefault();
            //$(this). ver de inserta aqui que se validen datos. y se blanqueen cosas
                   
		});
        
        container.delegate('#jcart-form-checkout','submit',function(event){
            var errores = 0;
            
            //alert("todo bien si muestro esto");
            

            var cliente = $('.nombrecliente a').text();
            cliente = cliente.replace("\n","");
            cliente = cliente.replace("\r","");
//            console.log(cliente);
            if(window.confirm("¿Está seguro que desea GENERAR el remito para "+cliente+" ?")==false){
                errores++;
//                event.preventDefault();
            }else{
                if($('#jcart-fecha-rem').val()!=undefined){
                    //validar la fecha, el numero de sucursal y el nro de remito.
                    var fecha = $('#jcart-fecha-rem').val(),
                        nroSuc = $('#jcart-suc').val(),
                        nroRem = $('#jcart-nro-rem').val();

                    if(fecha=="" || nroSuc=="" || (nroRem==""||nroRem==0)){
                        alert('Debe completar todos los datos');
                        //event.preventDefault();
                        errores ++;
                    }
                }
            }
            if(errores!=0){
               event.preventDefault();
            }
        });
		// Prevent enter key from submitting the cart
		container.keydown(function(e) {
			if(e.which === 13) {
				e.preventDefault();
			}
		});

		// Update an item in the cart
		container.delegate('[name="jcartItemQty[]"]', 'keyup', function(){
			update($(this));
            $( "#jcart-fecha-rem" ).datepicker({ dateFormat: "yy-mm-dd" });        
		});

		// Remove an item from the cart
		container.delegate('.jcart-remove', 'click', function(e){
			remove($(this));
                  
			e.preventDefault();
              
            
		});
//                $('#jcart-checkout').click(function(){
//                              if(window.confim("Dale para adelatnte")==true){
//                                  return true;
//                              }else{
//                                  return false;
//                              }
//                           }); 
//       $('#jcart-form-checkout').submit(function(){
//           alert("todo bien si muestro esto");
//       });   
	
    }()); // End JCART namespace
        //consulta namespaces
                  

}); // End the document ready function