// jCart v1.3
// http://conceptlogic.com/jcart/

$(function () {
  var JCART = (function () {
    // This script sends Ajax requests to config-loader.php and relay.php using the path below
    // We assume these files are in the 'jcart' directory, one level above this script
    // Edit as needed if using a different directory structure
    //agregado codigo del spinner
    $("#spinner")
      .bind("ajaxSend", function () {
        $(this).hide();
        $(this).show();
      })
      .bind("ajaxStop", function () {
        $(this).hide();
      })
      .bind("ajaxError", function () {
        $(this).hide();
      });
    $(".close").on("click", function () {
      $("#alertas").hide();
    });

    //$('#modal-stock-articulo').hide();
    $("#renglonlote").hide();
    //codigo del date value
    // inicializar las busquedas
    $("#divBuscaArticulo").show();

    $("#buscarArticuloR").show();
    $("#buscarArticulo").show();
    $("#divBuscaRubro").show();
    // agregado codigo de la busqueda rapida

    var path = "jcart",
      container = $("#jcart"),
      token = $("[name=jcartToken]").val(),
      tip = $("#jcart-tooltip");

    var config = (function () {
      var config = null;
      console.log("path:=>" + path + "/config-loader.php");
      $.ajax({
        url: path + "/config-loader.php",
        data: {
          ajax: "true",
        },
        dataType: "json",
        async: false,
        success: function (response) {
          config = response;
        },
        error: function (response) {
          console.log(response);
          alert(
            "Ajax error: Edit the path in jcart.js to fix.{" +
              path +
              "/config-loader.php}"
          );
        },
      });
      return config;
    })();

    var setup = (function () {
      if (config.tooltip === true) {
        tip.text(config.text.itemAdded);

        // Tooltip is added to the DOM on mouseenter, but displayed only after a successful Ajax request
        $(".jcart [type=submit]")
          .mouseenter(function (e) {
            var x = e.pageY + 25,
              y = e.pageX + -10;
            $("body").append(tip);
            tip.css({ top: y + "px", left: x + "px" });
          })
          .mousemove(function (e) {
            var y = e.pageY + 25,
              x = e.pageX + -10;
            tip.css({ top: y + "px", left: x + "px" });
          })
          .mouseleave(function () {
            tip.hide();
          });
      }

      // Remove the update and empty buttons since they're only used when javascript is disabled
      $("#jcart-buttons").remove();

      // Default settings for Ajax requests
      $.ajaxSetup({
        type: "POST",
        url: path + "/relay.php",
        success: function (response) {
          // Refresh the cart display after a successful Ajax request
          container.html(response);
          console.log($("#domicilio_entrega"));

          $("#jcart-buttons").remove();
          $("#domicilio_entrega").on("change", selecRuta);
          $("#domicilio_entrega  option:eq(1)").prop("selected", true);

          //                                        console.log("domicilio" +  $('#domicilio_entrega  option'));
        },
        // See: http://www.maheshchari.com/jquery-ajax-error-handling/
        error: function (x, e) {
          var s = x.status,
            m = "Ajax error: ";
          if (s === 0) {
            m += "Check your network connection.";
          }
          if (s === 404 || s === 500) {
            m += s;
          }
          if (e === "parsererror" || e === "timeout") {
            m += e;
          }
          alert(m);
        },
      });
    })();
    //activar y desactivar el boton TIPO DE BUSQUEDA
    $("input[name='tipoBusqueda']").change(function () {
      var valor = $("input[name='tipoBusqueda']:checked").val();
      //                    alert(valor);
      switch (valor) {
        case "0":
          //                    $('#divBuscaArticulo').css({display:"inline"});
          //                    $('#divBuscaRubro').css({display:"none"});
          //                    $('#buscarArticuloR').css({display:"inline"});
          //                    $('#buscarArticulo').css({display:"none"});

          $("#divBuscaArticulo").show();
          $("#divBuscaRubro").hide();
          $("#buscarArticuloR").show();
          $("#buscarArticulo").hide();
          //                            alert('dentro='+valor);
          break;
        case "1":
          //                    $('#divBuscaArticulo').css({display:"inline"});
          //                    $('#divBuscaRubro').css({display:"none"});
          //                    $('#buscarArticuloR').css({display:"none"});
          //                    $('#buscarArticulo').css({display:"none"});

          $("#divBuscaArticulo").show();
          $("#divBuscaRubro").hide();
          $("#buscarArticuloR").hide();
          $("#buscarArticulo").hide();
          //
          //                            alalert('dentro='+valor);ert('dentro='+valor);
          break;
        case "2":
          //                    $('#divBuscaArticulo').css({display:"none"});
          //                    $('#divBuscaRubro').css({display:"inline"});
          //                    $('#buscarArticuloR').css({display:"none"});
          //                    $('#buscarArticulo').css({display:"inline"});
          $("#divBuscaArticulo").hide();
          $("#divBuscaRubro").show();
          $("#buscarArticuloR").hide();
          $("#buscarArticulo").show();
          //                            alert('dentro='+valor);
          break;
      }
    });
    // funcion general para comprar el articulo
    var funcionComprarArt = function () {
      //alert('aca anduvo cuatro');

      var quien = $(this).attr("name");
      var descValido = $("#mi-desc-" + quien);
      //me salio!!
      var nombre = $("#mi-art-nombre" + quien).text(),
        id = quien,
        cantidad = $("#mi-cantidad-" + quien).val(),
        saldoP = $("#mi-saldo" + quien).val(),
        precio = $("#mi-art-precio" + quien).text(),
        tipoIvaP = $("#mi-tipo-iva" + quien).val(),
        ivaP = $("#mi-iva" + quien).val(),
        alicuotaP = $("#mi-alic-iva" + quien).val(),
        impIvaP = $("#mi-imp-iva" + quien).val(),
        impInternoP = $("#mi-imp-interno" + quien).val(),
        netoP = $("#mi-neto" + quien).val(),
        descP = $("#mi-desc-" + quien).val(),
        impInternoTasaP = $("#mi-imp-interno-tasa" + quien).val(),
        porcentaje = $("#mi-promoporc" + quien).val(),
        cantPromo = $("#mi-promocant" + quien).val(),
        promoP = $("#mi-promo" + quien).val(),
        cantBulto = $("#mi-bulto" + quien).val(),
        tipoPromoP = $("#mi-promotipo" + quien).val(),
        ensambladoVta = $('#mi-ensamblado-vta'+quien).val();
        console.log('ensambaldo en la venta=>',ensambladoVta);

      //                    tengo que hacer el traspaso de los datos que tengo a los que
      //                    se mandan por submit que ya estan preparametrizados.
      var form = $(".jcart");
      var qty = form.find('[name="my-item-qty"]'),
        idF = form.find('[name="my-item-id"]'),
        name = form.find('[name="my-item-name"]'),
        price = form.find('[name="my-item-price"]'),
        tipoIva = form.find('[name="my-item-tipoIva"]'),
        iva = form.find('[name="my-item-iva"]'),
        alicuota = form.find('[name="my-item-alicuota"]'),
        impIva = form.find('[name="my-item-impIva"]'),
        impInterno = form.find('[name="my-item-impInterno"]'),
        neto = form.find('[name="my-item-neto"]'),
        desc = form.find('[name="my-item-descPor"]'),
        promo = form.find('[name="my-item-promo"]'),
        promoCant = form.find('[name="my-item-promoCant"]'),
        promoPorc = form.find('[name="my-item-promoPorc"]'),
        promoTipo = form.find('[name="my-item-promoTipo"]'),
        usoBulto = form.find('[name="usa-bulto-promedio"]'),
        valorBulto = form.find('[name="my-item-url"]'),
        permSinStock = form.find('[name="permiso-sin-stock"]'),
        impInternoT = form.find('[name="my-item-impInternoTasa"]');

      console.log("MiCantidad:", isNaN(parseFloat(cantidad)));
      console.log("MiCantidad:", cantidad == "");
      console.log("MiDisponible:", saldoP);
      if (isNaN(parseFloat(cantidad)) || parseFloat(cantidad) == 0) {
        $("#mi-cantidad-" + quien)
          .select()
          .focus();
        alert("Cantidad incorrecta");
        return false;
      }

      tipoIva.val(tipoIvaP);
      iva.val(ivaP);
      alicuota.val(alicuotaP);
      impIva.val(impIvaP);
      impInterno.val(impInternoP);
      //                console.log("Permiso de Bulto:="+usoBulto.val());
      // si uso multiplico el bulto
      if (usoBulto.val() === "Si") {
        //                    if(cantBulto<1){
        //                        cantBulto =1;
        //                    }
        var total = parseFloat(cantidad * cantBulto);

        console.log(
          "Cantidad:=" +
            cantidad +
            " Bultito:=" +
            cantBulto +
            " total:=" +
            total
        );
        valorBulto.val(cantidad);
        cantidad = total;
      }
      //                valorBulto.val(cantBulto);
      // validar cantidad

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

      // stock no puede ser negativo para validar.
      if (parseFloat(saldoP) < 0) {
        saldoP = 0;
      }

      /* validar Stock   */
      if (parseFloat(cantidad) > parseFloat(saldoP)) {
        var exceso = "Si";
      } else {
        var exceso = "No";
      }

      if (exceso == "Si") {
        //alert("tengo permiso"+ permSinStock.val());
        if (permSinStock.val() == 'No'&&ensambladoVta=='No') {
          //no puedo operar sin stock
          alert(
            "Sin STOCK disponible, Cantidad a comprar:" +
              cantidad +
              " | Stock Disponible:" +
              saldoP
          );
          return false;
        }
      }

      /* vaiidar el descuento*/

      var valorDescuento = parseFloat(descValido.val()),
        minimo = parseFloat(descValido.attr("min")),
        maximo = parseFloat(descValido.attr("max"));
      if (valorDescuento < minimo || valorDescuento > maximo) {
        //console.log("te pasaste cabron.");
        descValido.val(maximo);
        return false;
      }

      form.submit();
      // quiero actualizar la cantidad del carrito.
      var totalCarrito = parseFloat($("#totalCarrito").text());
      //                console.log("total carrito=>"+$('#totalCarrito').text()+" ParseFloat=>"+parseFloat($('#totalCarrito').text()));
      totalCarrito = totalCarrito + 1;

      $("#totalCarrito").text(totalCarrito);
      console.log("agregando articulo nuevo.");
    };

    //funcion de la descripcion
    var funcionDescArt = function () {
      //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
      var codigoArticulo = $(this).attr("rel");
      $.ajax({
        type: "POST",
        url: "relay-art-rapido.php",
        data: {
          ajax: "true",
          idArticulo: codigoArticulo,
        },
        success: function (response) {
          $("#basic-modal-content").empty();
          $("#basic-modal-content").html(response);
          $("#basic-modal-content").modal({
            position: ["1.5%", "1%"],
            minWidth: 450,
            minHeight: 650,
            close: false,

            onShow: function () {
              $("#cerrarModalD").on("click", function (e) {
                e.preventDefault();
                $.modal.close();
              });
            },
          });

          //                                                $('#basic-modal-content').modal();

          return false;
        },
        error: function (x, e) {
          var s = x.status,
            m = "Ajax error: ";
          if (s === 0) {
            m += "Check your network connection.";
          }
          if (s === 404 || s === 500) {
            m += s;
          }
          if (e === "parsererror" || e === "timeout") {
            m += e;
          }
          alert(m);
        },
      });
    };

    /* funcion para la seleccion de rutas dinamicas*/
    var selecRuta = function () {
      // ir al relay a buscar la rutas

      var zona = $(this).val().split("|"),
        listaRuta = $("#hoja_ruta");
      //si no uso logistica la ruta no me sirve
      var idZona = zona[1];
      console.log(idZona);
      //                    console.log(listaRuta);
      // agregar al id del domicilio el id de la zona.
      if (listaRuta.length > 0 && idZona !== undefined) {
        $.ajax({
          type: "POST",
          url: "relay_ruta_logistica.php",
          data: {
            ajax: true,
            idZona: zona[1],
          },
          success: function (response) {
            // Refresh the cart display after a successful Ajax request
            console.log(response);
            var sit = response.indexOf("error:");
            //sit.split("error:");
            console.log(sit);
            if (sit < 0) {
              listaRuta.empty();
              listaRuta.html(response);
            } else {
              alert(
                "hubo un inconveniente con la zona del domicilio, zona valor:=>" +
                  zona[1] +
                  " respuesta=>" +
                  response
              );
            }
            $("#spinner").hide();
          },
          // See: http://www.maheshchari.com/jquery-ajax-error-handling/
          error: function (x, e) {
            var s = x.status,
              m = "Ajax error: ";
            if (s === 0) {
              m += "Check your network connection.";
            }
            if (s === 404 || s === 500) {
              m += s;
            }
            if (e === "parsererror" || e === "timeout") {
              m += e;
            }
            alert(m);
          },
        });
      } else {
        if (idZona === undefined) {
          alert(
            "El domicilio seleccionado no possee Zona, no puedo mostrar las rutas"
          );
        }
      }
    };

    //funcion para la busqueda rapida por boton
    //@buscarArticuloR = es el boton para buscar generico para todos
    // UNICA BUSQUEDA A PARTIR DE AHORA
    //==============================================================
    $("#buscarArticuloR").click(function () {
      // agregar categoria, rubro, subrubro marca modelo
      var contienes = $("#myTable"),
        buscaRapida = $("#queArticulo").val(),
        claseBusqueda = $("#claseBusqueda").val(),
        idArticulo = $("#queArticuloId").val(),
        cantidadOferta = $('[name="my-item-qty"]').val(),
        queCampo = $('input[name="queCampo"]:checked').val();

      // las opciones
      var categoria = $("#buscaCategoria").val(),
        rubro = $("#buscaRubro").val(),
        subrubro = $("#buscaSubRubro").val(),
        marca = $("#buscaMarca").val(),
        modelo = $("#buscaModelo").val(),
        misConsumos = $("#buscaMisConsumos"),
        promociones = $("#buscaPromo"),
        consumo = 0,
        promo = 0;
      if (cantidadOferta == "") {
        cantidadOferta = 1;
      }
      // mis consumos
      if (misConsumos.prop("checked") === true) {
        consumo = 1;
      }
      // mis promociones
      if (promociones.prop("checked") === true) {
        promo = 1;
      }

      //

      $.ajax({
        type: "POST",
        url: "relay-art.php",
        data: {
          ajax: "true",
          queArticulo: buscaRapida,
          buscarArticulo: "buscarArticulo",
          idArticulo: idArticulo,
          cantidadOferta: cantidadOferta,
          claseBusca: claseBusqueda,
          categoria: categoria,
          rubro: rubro,
          subrubro: subrubro,
          marca: marca,
          modelo: modelo,
          queCampo: queCampo,
          consumo: consumo,
          promo: promo,
          //                                "idOferta"      :   idOferta
        },
        success: function (response) {
          // Refresh the cart display after a successful Ajax request
          //alert(response);
          if (response == "0") {
            //alert('traje vacio');
            contienes.empty();
            contienes.html(
              "<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>"
            );
            $("#spinner").hide();
          } else {
            //alert(response);
            contienes.empty();
            if ($.fn.dataTable.isDataTable("#myTable")) {
              contienes.DataTable().destroy();
            }
            contienes.html(response);
            contienes.DataTable({
              language: {
                emptyTable: "No data available in table",
                info: "Viendo _START_ de _END_ de _TOTAL_ resultados",
                infoEmpty: "Viendo 0 de 0 de 0 resultados",
                infoFiltered: "(filtrado de _MAX_ resultados)",
                infoPostFix: "",
                thousands: "",
                lengthMenu: "Ver _MENU_ entradas",
                loadingRecords: "Loading...",
                processing: "Processing...",
                search: "Buscar:",
                zeroRecords: "No matching records found",
                paginate: {
                  first: "Primero",
                  last: "Ultimo",
                  next: "Siguiente",
                  previous: "Anterior",
                },
                aria: {
                  sortAscending: ": activate to sort column ascending",
                  sortDescending: ": activate to sort column descending",
                },
              },
              ordering: false,
            });
            $("#spinner").hide();
            $("#queArticuloId").val("");
            //mostrar la modal
            $("input[type='number']").on("click", function () {
              $(this).select();
            });
            $("#myTable tbody").on(
              "click",
              "td a.desc-articulo",
              funcionDescArt
            );

            //$(".desc-articulo").click(funcionDescArt);
            //                                         $('#myTable tbody').on("click","td i.tecompro", funcionComprarArt);
            //                                          $('#myTable tbody').on("click","td a.desc-articulo",funcionDescArt);

            //$(".desc-articulo").click(funcionDescArt);
            $("#myTable tbody").on("click", "td .tecompro", funcionComprarArt);
            //                                        $(".desc-articulo").click(funcionDescArt);
            //                                        $('.tecompro').click(funcionComprarArt);

            //$('#jcart-buttons').remove();
          } // el cierre del else del
        },
        // See: http://www.maheshchari.com/jquery-ajax-error-handling/
        error: function (x, e) {
          var s = x.status,
            m = "Ajax error: ";
          if (s === 0) {
            m += "Check your network connection.";
          }
          if (s === 404 || s === 500) {
            m += s;
          }
          if (e === "parsererror" || e === "timeout") {
            m += e;
          }
          alert(m);
        },
      });
    });

    //funcion de BUSQUEDA RAPIDA

    // Check hidden input value
    // Sent via Ajax request to jcart.php which decides whether to display the cart checkout button or the PayPal checkout button based on its value
    // We normally check against request uri but Ajax update sets value to relay.php

    // If this is not the checkout the hidden input doesn't exist and no value is set
    var isCheckout = $("#jcart-is-checkout").val();

    function add(form) {
      // Input values for use in Ajax post

      var itemQty = form.find("[name=" + config.item.qty + "]"),
        itemAdd = form.find("[name=" + config.item.add + "]");

      // Add the item and refresh cart display
      $.ajax({
        data: form.serialize() + "&" + config.item.add + "=" + itemAdd.val(),
        success: function (response) {
          // Momentarily display tooltip over the add-to-cart button
          if (itemQty.val() > 0 && tip.css("display") === "none") {
            tip.fadeIn("100").delay("400").fadeOut("100");
          }

          container.html(response);
          $("#domicilio_entrega").on("change", selecRuta);
          var opzion = $("#domicilio_entrega  option:eq(1)");
          //                                        console.log(opzion);
          opzion.prop("selected", true);
          $("#domicilio_entrega").change();
          $("#jcart-buttons").remove();
        },
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
        var updateTimer = window.setTimeout(function () {
          // Update the item and refresh cart display
          $.ajax({
            data: {
              jcartUpdate: 1, // Only the name in this pair is used in jcart.php, but IE chokes on empty values
              itemId: updateId,
              itemQty: newQty,
              jcartIsCheckout: isCheckout,
              jcartToken: token,
            },
          });
        }, 1000);
      }

      // If the visitor presses another key before the timer has expired, clear the timer and start over
      // If the timer expires before the visitor presses another key, update the item
      input.keydown(function (e) {
        if (e.which !== 9) {
          window.clearTimeout(updateTimer);
        }
      });
    }

    function remove(link) {
      // Get the query string of the link that was clicked
      var queryString = link.attr("href");
      queryString = queryString.split("=");

      // The id of the item to remove
      var removeId = queryString[1];

      // Remove the item and refresh cart display
      $.ajax({
        type: "GET",
        data: {
          jcartRemove: removeId,
          jcartIsCheckout: isCheckout,
        },
      });
      var totalCarrito = parseFloat($("#totalCarrito").text());
      //console.log($('#totalCarrito').text().split(''));
      totalCarrito = totalCarrito - 1;
      $("#totalCarrito").text(totalCarrito);
    }

    //                  buscamos el categoria
    $("#buscaCategoria").change(function () {
      var categoria = $(this).val();
      //alert($(this).val());
      $("#buscaRubro").empty();
      if (categoria !== "") {
        $.ajax({
          type: "get",
          url: "relay-rubro.php",
          data: {
            ajax: true,
            idcategoria: categoria,
          },
          dataType: "json",
          success: function (j) {
            //alert(j);
            var options = [],
              i = 0,
              o = null;
            //alert(j.length);
            for (i = 0; i < j.length; i++) {
              // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
              o = document.createElement("OPTION");
              //                            alert(j[i]['id']);
              o.value = typeof j[i] == "object" ? j[i]["id"] : j[i];
              o.text = typeof j[i] == "object" ? j[i]["name"] : j[i];
              //
              $("#buscaRubro").append(o);
            }
          },

          error: function (xhr, desc, er) {
            // add whatever debug you want here.
            alert("an error occurred" + xhr + desc + er);
          },
        });
      } else {
        var o = document.createElement("OPTION");
        //                            alert(j[i]['id']);
        o.value = "";
        o.text = "- todos -";
        //
        $("#buscaRubro").append(o);
        $("#buscaRubro").change();
      }
    });
    // buscar subrubro al seleccionar un rubro ddo

    $("#buscaRubro").change(function () {
      var rubro = $(this).val();
      console.log($(this).val());
      $("#buscaSubRubro").empty();
      if (rubro !== "") {
        $.ajax({
          type: "get",
          url: "relay-rubro.php",
          data: {
            ajax: true,
            idrubro: rubro,
          },
          dataType: "json",
          success: function (j) {
            console.log(j);
            var options = [],
              i = 0,
              o = null;
            //alert(j.length);
            for (i = 0; i < j.length; i++) {
              // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
              o = document.createElement("OPTION");
              //                            alert(j[i]['id']);
              o.value = typeof j[i] == "object" ? j[i]["id"] : j[i];
              o.text = typeof j[i] == "object" ? j[i]["name"] : j[i];
              //
              $("#buscaSubRubro").append(o);
            }
          },

          error: function (xhr, desc, er) {
            // add whatever debug you want here.
            alert("an error occurred" + xhr + desc + er);
          },
        });
      } else {
        // crear elemento vacio.
      }
    });

    // buscamos modelos
    $("#buscaMarca").change(function () {
      var marca = $(this).val();
      //alert($(this).val());
      $("#buscaModelo").empty();
      if (marca !== "") {
        $.ajax({
          type: "get",
          url: "relay-marca.php",
          data: {
            ajax: true,
            idmarca: marca,
          },
          dataType: "json",
          success: function (j) {
            //alert(j);
            var options = [],
              i = 0,
              o = null;
            //alert(j.length);
            for (i = 0; i < j.length; i++) {
              // required to get around IE bug (http://support.microsoft.com/?scid=kb%3Ben-us%3B276228)
              o = document.createElement("OPTION");
              //                            alert(j[i]['id']);
              o.value = typeof j[i] == "object" ? j[i]["id"] : j[i];
              o.text = typeof j[i] == "object" ? j[i]["name"] : j[i];
              //
              $("#buscaModelo").append(o);
            }
          },

          error: function (xhr, desc, er) {
            // add whatever debug you want here.
            alert("an error occurred" + xhr + desc + er);
          },
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
    //                        if(buscaRapida==='Ingrese nombre de Articulo'){
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
    //                                        contienes.html(response);
    //                                        contienes.DataTable({
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
    //                                        $('#myTable tbody').on("click","td a.desc-articulo", funcionDescArt);
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
    //                                            m += 'Check your network connection.';
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

    /* Logistica*/
    $("#domicilio_entrega").on("change", selecRuta);

    /* selecciono el primero*/

    var opzion = $("#domicilio_entrega  option:eq(1)");
    //                console.log(opzion);
    opzion.prop("selected", true);
    $("#domicilio_entrega").change();

    //                console.log($('#domicilio_entrega  option'));

    // Add an item to the cart
    $(".jcart").submit(function (e) {
      add($(this));
      e.preventDefault();
    });
    container.delegate("#jcart-form-checkout", "submit", function () {
      //alert("todo bien si muestro esto");
      var horario = $("#jcart-detalle");
      if (horario.val() == "") {
        alert("Debe completar el horario de entrega - detalle");
        horario.focus();
        return false;
      }
      var cliente = $(".nombrecliente a").text();
      cliente = cliente.replace("\n", "");
      cliente = cliente.replace("\r", "");
      //alert($('.nombrecliente').html());
      if (
        window.confirm(
          "¿Está seguro que desea GENERAR el comprobante para " + cliente + " ?"
        ) == true
      ) {
        return true;
      } else {
        return false;
      }
    });
    // Prevent enter key from submitting the cart
    container.keydown(function (e) {
      if (e.which === 13) {
        e.preventDefault();
      }
    });

    // Update an item in the cart
    container.delegate('[name="jcartItemQty[]"]', "keyup", function () {
      update($(this));
    });

    // Remove an item from the cart
    container.delegate(".jcart-remove", "click", function (e) {
      remove($(this));
      e.preventDefault();
    });
  })(); // End JCART namespace
  //consulta namespaces
}); // End the document ready function
