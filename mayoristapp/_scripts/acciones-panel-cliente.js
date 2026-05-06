// acciones del panel del cliente y ventanas modales de resumenes
jQuery(document).ready(function ($) {
  $("#modal-ncancelados-cliente").hide();
  $("#modal-consumos-cliente").hide();
  $("#datosVendedor").hide();

  $("#icono-ver-fact").on("click", function () {
    var wPantalla = $(document).width();

    var contienes = $("#tablaCancelados"),
      ventanita = $("#modal-ncancelados-cliente"),
      campoBusca = $("#campoBusca").val(),
      fechaDesde = $("#fechaDesde").val(),
      fechaHasta = $("#fechaHasta").val(),
      numeroComp = $("#numeroComp").val(),
      estadoPedido = $("#estadoPedido").val();
    $("formBusca").submit();
    $("#modal-ncancelados-cliente").show();

    $.ajax({
      type: "POST",
      url: "relay-comp-no-cancelados-resumen.php",
      //                 url: 'relay-comprobantes-ncancelados.php',
      data: {
        ajax: "true",
        campoBusca: campoBusca,
        fechaDesde: fechaDesde,
        fechaHasta: fechaHasta,
        numeroComp: numeroComp,
        estadoPedido: estadoPedido,
      },
      success: function (response) {
        //                        // Refresh the cart display after a successful Ajax request
        ////                                    alert(response);
        //                        console.log(response);
        contienes.empty();
        if ($.fn.dataTable.isDataTable("#tablaCancelados")) {
          contienes.DataTable().destroy();
        }
        contienes.html(response);
        contienes.DataTable({
          searching: false,
          responsive: true,
          paging: false,
          info: false,
          language: {
            emptyTable: "No hay datos disponibles ",
            info: "Viendo _START_ de _END_ de _TOTAL_ resultados",
            infoEmpty: "Viendo 0 de 0 de 0 resultados",
            infoFiltered: "(filtrado de _MAX_ resultados)",
            infoPostFix: "",
            thousands: "",
            lengthMenu: "Ver _MENU_ entradas",
            loadingRecords: "Loading...",
            processing: "Processing...",
            search: "Buscar:",
            zeroRecords: "No se encontraron Registros",
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
        });
        var wVentana = 0,
          hVentana = 0;
        if (wPantalla > 480) {
          wVentana = 450;
          hVentana = 650;
        } else {
          wVentana = 290;
          hVentana = 350;
        }
        /* ventana modal*/
        ventanita.modal({
          minWidth: wVentana,

          minHeight: 100,
          maxHeight: hVentana,
          close: false,
          onShow: function () {
            $("#cierroNcanc").on("click", function (e) {
              e.preventDefault();
              $("#modal-ncancelados-cliente").hide();
              var contienes2 = $("#tablaCancelados");

              $.modal.close();
              contienes2.DataTable().destroy();
            });
          },
        });
      },
      error: function (x, e) {
        var s = x.status,
          m = "Ajax error: ";
        if (s === 0) {
          m += "Check your network connection." + x.status + e;
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

  /**
   * ver consumos del ultimo año. solo 20
   * */
  $("#icono-ver-consumos").on("click", function () {
    var wPantalla = $(document).width();
    var contienes = $("#tablaConsumos"),
      ventanita = $("#modal-consumos-cliente");
console.log('ancho de pantalla:',wPantalla);
    $.ajax({
      type: "POST",
      url: "relay-consumos-resumen.php",
      //                 url: 'relay-comprobantes-ncancelados.php',
      data: {
        ajax: "true",
      },
      success: function (response) {
        //                        // Refresh the cart display after a successful Ajax request
        ////                                    alert(response);
        //                        console.log(response);
        contienes.empty();
        if ($.fn.dataTable.isDataTable("#tablaConsumos")) {
          contienes.DataTable().destroy();
        }
        contienes.html(response);
        contienes.DataTable({
          searching: false,
          responsive: true,
          paging: false,
          info: false,
          ordering: false,
          language: {
            emptyTable: "No hay datos disponibles ",
            info: "Viendo _START_ de _END_ de _TOTAL_ resultados",
            infoEmpty: "Viendo 0 de 0 de 0 resultados",
            infoFiltered: "(filtrado de _MAX_ resultados)",
            infoPostFix: "",
            thousands: "",
            lengthMenu: "Ver _MENU_ entradas",
            loadingRecords: "Loading...",
            processing: "Processing...",
            search: "Buscar:",
            zeroRecords: "No se encontraron Registros",
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
            order: [[3, "desc"]],
          },
        });
        // reviso el tamaño de la pantalla.
        var wVentana = 0,
          hVentana = 0;
        if (wPantalla > 680) {
          wVentana = 650;
          hVentana = 650;
        } else {
          wVentana = 290;
          hVentana = 350;
        }
        /* ventana modal*/
        ventanita.modal({
          minWidth: wVentana,

          minHeight: hVentana,
          maxHeight: hVentana,
          close: false,
          onShow: function () {
            $("#cierroConsumos").on("click", function (e) {
              e.preventDefault();
              var contienes2 = $("#tablaConsumos");

              $.modal.close();
              contienes2.DataTable().destroy();
            });
          },
        });
      },
      error: function (x, e) {
        var s = x.status,
          m = "Ajax error: ";
        if (s === 0) {
          m += "Check your network connection." + x.status + e;
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

  /*
   * Editar domicilio del cliente seleccionado.
   */
  $("#editarClienteH").on("click", function () {
    var codigoCliente = $(this).attr("rel");
    console.log("hice click en el edicion:=> " + codigoCliente);
    location.href = "mod-cliente-rapido.php?id=" + codigoCliente;
  });

  /*
   * Editar domicilios del cliente
   */

  $("#domicilioClienteH").on("click", function () {
    //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
    var codigoCliente = $(this).attr("rel");

    location.href = "abm-cliente-domicilios.php?id=" + codigoCliente;
  });

  //* aciones nuevas del panel de clientes.
  $(".ver-opciones-cliente").on("click", function () {
    // console.warn('soy las opcines cliente ');

    let botoni = $(this).find("i");
    //console.warn('botoni',botoni);
    let mostrar = botoni.hasClass("fa-angle-down");
    //console.warn('mostrar',mostrar);
    let panelDatosCliente = $(".datos-cliente");
    if (mostrar == true) {
      // mostrar datos tengo la clase flecha abajo.

      botoni.removeClass("fa-angle-down").addClass("fa-angle-up");
    }
    if (mostrar == false) {
      botoni.removeClass("fa-angle-up").addClass("fa-angle-down");
    }
    panelDatosCliente.toggle(400);
  });

  // * mostrar botonera del cliente.
  $(".ver-menu-cliente").on("click", function () {
    let botonera = $("#botoneraCliente");
    botonera.toggle();
  });

  // * mostrar busqueda de cliente y cambiarlo
  $(".cambiar-cliente").on("click", function () {
    let busqueda = $("#buscador-cliente");
    // let tarjeta = $("#tarjeta-cliente");
    // let comprobantes = $("#clienteOk");

    Swal.fire({
      title: "Seguro?",
      icon: "info",
      text: "Estás seguro que deseas cambiar de cliente?",
      confirmButtonText: "Si!",
      confirmButtonColor: "#395aa2",
      showCancelButton: true,
      cancelButtonText: "No",
    }).then((resultado) => {
      if (resultado.isConfirmed) {
        if (busqueda.length) {
          // El elemento existe en el DOM
          console.log("El elemento existe en el DOM");
          cambiarCliente();
        } else {
          location.href = "listado-clientes.php?accion=cambiar";
        }
      }
    });
  });
});

function cambiarCliente() {
  let busqueda = $("#buscador-cliente");
  let tarjeta = $("#tarjeta-cliente");
  let estadisticas = $("#dashboard-container");
  let comprobantes = $("#clienteOk");
  tarjeta.hide();
  comprobantes.hide();
  estadisticas.hide();
  busqueda.show();

}

function miFormulario(frm) {
  $.ajax({
    type: "GET",
    url: "relay-clientes.php",
    data: {
      ajax: "true",
      seleccionarComprobante: 1,
      frm: frm,
    },
    success: function (vuelta) {
      console.warn("que formulario elegi", vuelta);
      if (vuelta.estado == "ok") {
        location.href = vuelta.url;
      }
      if (vuelta.estado != "ok") {
        console.warn("no puede seleccionar comprobante.");
      }
    },
  });
}
