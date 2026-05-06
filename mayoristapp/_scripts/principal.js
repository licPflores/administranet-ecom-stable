// acciones principales comunes a todos
const precios = new Intl.NumberFormat("es-AR", {
  style: "currency",
  currency: "ARS",
  minimumFractionDigits: 2,
});

const porcientos = new Intl.NumberFormat("es-AR", {
  style: "percent",
  minimumFractionDigits: 0,
});
const dinero = new Intl.NumberFormat("es-AR", {
  style: "currency",
  currency: "ARS",
  minimumFractionDigits: 2,
});
const numerito = new Intl.NumberFormat("es-AR", {
  style: "decimal",
  minimumFractionDigits: 2,
});

let LogoEmpresa64;
(async function() {
  LogoEmpresa64 = await obtenerLogoBase64();
  // console.log(LogoEmpresa64); // Verifica que el logo se haya cargado correctamente
})();

function ReplaceNumberWithCommas(yourNumber) {
  //Seperates the components of the number
  var yourNumberF = Math.round(yourNumber * 100) / 100;
  var n = yourNumberF.toString().split(".");
  //Comma-fies the first part
  n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  //Combines the two sections
  return n.join(",");
  //return yourNumberF;
  //            return yourNumber;
}

//*** redondeo

function precise_round(num, dec) {
  if (typeof num !== "number" || typeof dec !== "number") return false;

  var num_sign = num >= 0 ? 1 : -1;
  //            console.log("numero{"+num+"} - decimal{"+dec+ "}");
  return (
    Math.round(num * Math.pow(10, dec) + num_sign * 0.0001) / Math.pow(10, dec)
  ).toFixed(dec);
}

function listaGetUrl() {
  var url_string = window.location.href;
  var url = new URL(url_string);
  return url;
}

function findGetParameter(parameterName) {
  var result = null,
    tmp = [];
  var items = location.search.substr(1).split("&");
  for (var index = 0; index < items.length; index++) {
    tmp = items[index].split("=");
    if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
  }
  return result;
}

function obtenerUsuarioLogueado() {
  // Verificar si la variable ya está presente en sessionStorage
  const usuarioExistente = sessionStorage.getItem("usuarioLogueado");
  //if (typeof usuario === 'object' && usuario !== null && usuario.nombre && usuario.email) {
   console.log("tipo de sesion usuario logueado", typeof usuarioExistente);
  if (typeof usuarioExistente === "object" && usuarioExistente !== null) {
    console.log("tengo el usuario completo");
    return JSON.parse(usuarioExistente); // Devolver los datos existentes
  } else {
    // Realizar la llamada AJAX solo si la variable no está presente
    
    $.ajax({
      url: "ajax/sesion-json.php",
      type: "GET",
      data: {
        usuarioLogin: 1,
      },
      dataType: "json",
      success: function (data) {
        console.log('vuleta de sesion json=> ',data);
        // Almacenar los datos del usuario en sessionStorage
        console.log("lo que obtengo de buscar el suaruio loguaedo data", data);
        if (data.estado == "ok") {
          
          sessionStorage.setItem("usuarioLogueado", JSON.stringify(data.data));
          let usuarioLogin = sessionStorage.getItem("usuarioLogueado");
          return usuarioLogin;
        }
        if (data.estado == "error") {
          Swal.fire({
            icon: "error",
            title: "Ocurrion un inconveniente, vuelva a ingresar",
          });
          location.href = "salida.inc.php";
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error al obtener el usuario logueado:", errorThrown);
        if (data.estado == "error") {
          Swal.fire({
            icon: "error",
            title: "Ocurrion un inconveniente, vuelva a ingresar",
          });
          location.href = "salida.inc.php";
        }
      },
    });
  }
}

// * obtener cliente seleccionado ( ver datos del cliente)

async function obtenerClienteSeleccionado() {
  const clienteSeleccionado = JSON.parse(
    sessionStorage.getItem("clienteSeleccionado")
  );
  //if (typeof usuario === 'object' && usuario !== null && usuario.nombre && usuario.email) {
  // console.log('sesion storage.getiem clienteSeleccionado=>',sessionStorage.getItem('clienteSeleccionado'));
  console.log("typeof clienteseleccionado", typeof clienteSeleccionado);
  if (typeof clienteSeleccionado === "object" && clienteSeleccionado !== null) {
    console.log(
      "tengo el cliente creado lo paso. completo",
      clienteSeleccionado
    );
    return clienteSeleccionado; // Devolver los datos existentes
  } else {
    // si no esta lo busco nuevamente
    try {
      const response = await $.ajax({
        url: "ajax/sesion-json.php",
        type: "GET",
        data: {
          traeDatosClienteSeleccionado: 1,
        },
        dataType: "json",
      });

      if (response.estado === "ok") {
        sessionStorage.setItem(
          "clienteSeleccionado",
          JSON.stringify(response.data)
        );
        let clienteVuelta = sessionStorage.getItem("clienteSeleccionado");
        //console.log('clienteVuelta ', clienteVuelta);
        return JSON.parse(clienteVuelta);
      } else if (response.estado === "error") {
        Swal.fire({
          icon: "error",
          title: "Ocurrion un inconveniente, vuelva a ingresar",
        });
        location.href = "salida.inc.php";
      }
    } catch (error) {
      console.error("Error al obtener el cliente logueado:", error);
      Swal.fire({
        icon: "error",
        title: "Ocurrion un inconveniente, vuelva a ingresar",
      });
      location.href = "salida.inc.php";
    }
  }
}

// espero que funciones
async function obtenerLogoBase64() {
  let sesion = sessionStorage.getItem("usuarioLogueado");
  //const usuarioExistente = sessionStorage.getItem('usuarioLogueado');

  let jsonSesion = JSON.parse(sesion);
  //console.log('la sesion de storage en obtener logo ',jsonSesion);
  // console.log('usuario login que hay', jsonSesion.cuit_empresa);
  // let cuitEmpresa = jsonSesion.cuit_empresa;
  // let urlLogo =  "_img/logo_" + cuitEmpresa + ".jpg";
  // console.log('url logo', urlLogo);

  // // Fetch the image from the URL
  // fetch(urlLogo)
  //   .then(response => response.blob())
  //   .then(blob => {
  //     const reader = new FileReader();
  //     reader.onload = function(event) {
  //         const base64String = event.target.result;
  //         console.log('logo base64', base64String);
  //     };
  //     reader.readAsDataURL(blob);
  //   })
  //   .catch(error => {
  //     console.error('Error fetching the image:', error);
  //   });
  // console.log('logo 64 total',jsonSesion.logo_base_64);
  //console.log('logo 64 total',typeof(null));

  return jsonSesion.logo_base_64;
}
// * salida
function salida() {
  Swal.fire({
    title: "Seguro que desea Salir del sistema?",
    icon: "info",

    confirmButtonText: "Si",
    showCancelButton: true,
    cancelButtonText: "No",
  }).then((accion) => {
    if (accion.isConfirmed) {
      const usuarioExistenteSalida = sessionStorage.getItem("usuarioLogueado");
      //vaciando local storage
      if (usuarioExistenteSalida) {
        //return JSON.parse(usuarioExistente); // Devolver los datos existentes
        sessionStorage.removeItem("usuarioLogueado");
      }

      location.href = "salida.inc.php";
    }
  });
}

// confirmacion general.
const Toast = Swal.mixin({
  toast: true,
  position: "center",
  showConfirmButton: false,
  timer: 5000,
  timerProgressBar: false,
  didOpen: (toast) => {
    toast.addEventListener("mouseenter", Swal.stopTimer);
    toast.addEventListener("mouseleave", Swal.resumeTimer);
  },
});

// meter cosas de jquery aca. generales.
jQuery(document).ready(function ($) {
  $("#datosVendedor").hide();

  $("#iconoVendedor").on("click", function () {
    // var icono= $('#iconoVendedor i');
    // icono.toggleClass('iconoAzul');
    $("#datosVendedor").toggle();
  });
  // cerar
  $(".cerrar-menu-vendedor").on("click", function () {
    $("#datosVendedor").hide();
  });

  $("#iconoCliente").on("click", function () {
    $(this).toggleClass("iconoActivo");
    $(".headerCliente").animate({ height: "toggle" }, 500);
  });
});

// * funcion para buscar productos lista de precios completa
// junto los dos tipos de busqueda para que funcione con la lista de precios
// el iva y la imgan son comunes a todo entonces debo sacarlo de abajo.
async function buscarProductosLp(paramTipoBusqueda =null) {
  const contienes = $("#myTable");
  const categoria = $("#buscaCategoria").val();
  const rubro = $("#buscaRubro").val();
  const subrubro = $("#buscaSubRubro").val();
  const marca = $("#buscaMarca").val();
  const idTipoCliente = $("#tipoCliente").val();
  const laboratorio = $("#buscaLaboratorio");
  const ivaIncluido = $("#buscaTipoIva").val();
  const listaDePrecio = $("#listaDePrecios").val();
  const imagenProducto = $("#imagenProducto").val();
  const proveedor = $("#buscaProveedor");
  const tacc = $("#buscaTacc");
  const queCliente = $("#cliente").val();
  let claseBusca = $("#claseBusca").val();
  let nombreArticulo = null;     
  let idArticulo = null;
  // compatibilidad busqueda rapida
  //claseBusca = "codigo",
  // paramTipoBusqueda  si viene con 1 activado busquea rapida.
  if(paramTipoBusqueda === 1){
    nombreArticulo = $('#nombreBuscaRapido').val();     
    idArticulo = $('#itemId').val();
    // buscco comodin
    if(idArticulo===""){
      claseBusca="texto";
    }
    // busco por codigo un solo producto.
    if(idArticulo!==""){
      // console.log(idArticulo);
      // console.log('dentro del articulo difrente a null')
      claseBusca = "codigo";
    }
  }

  let valProveedor = "";
  let valTacc = "";
  let misConsumos = 0;
  let textoMarca = "";
  let textoNegocio = "";
  let textoCategoria = "";
  let textoRubro = "";
  let textoSubRubro = "";
  let textoLab = "";
  let textoProveedor = "";
  let textoCliente = "";
  let textoTacc = "";
  let queLaboratorio = null;
  let tipoCliente = "si";
  let cabeceraExcel ="";

  if ($("#buscaMiConsumo:checked").val() !== undefined) {
    misConsumos = 1;
  }

  if (categoria) {
    textoCategoria = $("#buscaCategoria option:selected").text();
  }
  if (rubro) {
    textoRubro = $("#buscaRubro option:selected").text();
  }
  if (subrubro) {
    textoSubRubro = $("#buscaSubRubro option:selected").text();
  }
  if (laboratorio && laboratorio.val() !== undefined) {
    queLaboratorio = laboratorio.val();
    textoLab = $("#buscaLaboratorio option:selected").text();
  }
  if (proveedor) {
    valProveedor = proveedor.val();
    textoProveedor = $("#buscaProveedor option:selected").text();
  }
  if (tacc && tacc.val() !== "") {
    valTacc = tacc.val();
    textoTacc = $("#buscaTacc option:selected").text();
  }

  if (!queCliente) {
    queCliente = "cliente";
  }

  if (queCliente === "cliente") {
    const objClienteSeleccionado = await obtenerClienteSeleccionado();
    if (objClienteSeleccionado && objClienteSeleccionado.length > 0) {
      textoCliente = objClienteSeleccionado[0].cliente;
    } else {
      console.error("No se pudo obtener el cliente seleccionado.");
      textoCliente = "Cliente no encontrado";
    }
  }

  if (idTipoCliente !== "0") {
    textoNegocio = $("#tipoCliente option:selected").text();
  }
  if (marca) {
    textoMarca = $("#buscaMarca option:selected").text();
  }
  if (ivaIncluido === "No") {
    leyendaIva = "Los precios publicados NO incluyen IVA";
  }
  if (ivaIncluido === "Si") {
    leyendaIva = "Los precios publicados incluyen IVA";
  }

  const opcionesFormateadas = [];
  const colorGris = "#484848";
  const fontSizeChica = 11;
  const fontSizeGrande = 12;
  
  if (textoCliente) {
    cabeceraExcel += "Cliente: "+textoCliente + "\n";
    opcionesFormateadas.push(
      {
        text: "Cliente: ",
        fontSize: fontSizeGrande,
        bold: true,
        color: colorGris,
      },
      { text: textoCliente + "\n", fontSize: fontSizeGrande, color: colorGris }
    );
  }

  const categoriaRubroSubrubro = [];
  if (textoCategoria) {
    categoriaRubroSubrubro.push(textoCategoria);
  }
  if (textoRubro) {
    categoriaRubroSubrubro.push(textoRubro);
  }
  if (textoSubRubro) {
    categoriaRubroSubrubro.push(textoSubRubro);
  }

  if (categoriaRubroSubrubro.length > 0) {
    cabeceraExcel +="Categoría/Rubro/Subrubro: " + categoriaRubroSubrubro.join(", ") + "\t |";
    opcionesFormateadas.push(
      {
        text: "Categoría/Rubro/Subrubro: ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      {
        text: categoriaRubroSubrubro.join(", ") + "\n",
        fontSize: fontSizeChica,
        color: colorGris,
      }
    );
  }
  if (textoMarca) {
    cabeceraExcel +="Marca: " + textoMarca + "\t |";
    opcionesFormateadas.push(
      {
        text: "Marca: ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      { text: textoMarca + "\n", fontSize: fontSizeChica, color: colorGris }
    );
  }
  if (textoLab) {
    cabeceraExcel +="Laboratorio: " + textoLab + "\t |";
    opcionesFormateadas.push(
      {
        text: "Laboratorio: ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      { text: textoLab + "\n", fontSize: fontSizeChica, color: colorGris }
    );
  }
  if (textoTacc) {
    cabeceraExcel +="Sin TACC: " + textoTacc + "\t |";
    opcionesFormateadas.push(
      {
        text: "Sin TACC: ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      { text: textoTacc + "\n", fontSize: fontSizeChica, color: colorGris }
    );
  }
  if (textoNegocio) {
    cabeceraExcel +="Tipo Negocio: " + textoNegocio + "\t |";
    opcionesFormateadas.push(
      {
        text: "Tipo Negocio: ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      { text: textoNegocio + "\n", fontSize: fontSizeChica, color: colorGris }
    );
  }
  // leyenda del iva
  if(leyendaIva){
    cabeceraExcel += leyendaIva + "\t |";
    opcionesFormateadas.push(
      {
        text: "* ",
        fontSize: fontSizeChica,
        bold: true,
        color: colorGris,
      },
      { text: leyendaIva + "\n", fontSize: fontSizeChica, color: colorGris }
    );
  }
  //console.log("cabeceraExcel",cabeceraExcel);

  $("#spinner").show();
  $.ajax({
    type: "POST",
    url: "relay-art.php",
    data: {
      ajax: "true",
      queAccion: "listaPrecios",
      tipoCliente: tipoCliente,
      buscarProducto: 1,
      categoria: categoria,
      rubro: rubro,
      subrubro: subrubro,
      marca: marca,
      idTipoCliente: idTipoCliente,
      queCliente: queCliente,
      ivaIncluido: ivaIncluido,
      misConsumos: misConsumos,
      laboratorio: queLaboratorio,
      imagenProducto: imagenProducto,
      listaDePrecio: listaDePrecio,
      proveedor: valProveedor,
      tacc: valTacc,
      // compatibilidad busqueda rapida
      queArticulo       : nombreArticulo,                   
      idArticulo        : idArticulo,                   
      claseBusca        : claseBusca
      
      
    },
    success: function (response) {
      let leyendaIva = "";
      const logo = LogoEmpresa64;
      console.log("imagenProducto:",imagenProducto      );
      const empresa = $("#imgLogo").attr("title");

      const fecha = new Date();
      const optionsF = {
        year: "numeric",
        month: "long",
        day: "numeric",
      };
      const fechaFormateada =
        fecha.getFullYear() +
        "_" +
        ("0" + (fecha.getMonth() + 1)).slice(-2) +
        "_" +
        ("0" + fecha.getDate()).slice(-2) +
        "_" +
        ("0" + fecha.getHours()).slice(-2) +
        ("0" + fecha.getMinutes()).slice(-2) +
        ("0" + fecha.getSeconds()).slice(-2);

      const titulo =
        "Lista de precios al " + fecha.toLocaleDateString("es-ES", optionsF);

      

      if (response === "0") {
        contienes.empty();
        contienes.html(
          "<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontraron resultados </td></tr>"
        );
        $("#spinner").hide();
      } else {
        contienes.empty();
        if ($.fn.dataTable.isDataTable("#myTable")) {
          contienes.DataTable().destroy();
        }

        contienes.html(response);
        contienes.DataTable({
          searching: true,
          responsive: false,
          language: {
            emptyTable: "No data available in table",
            info: "Viendo _START_ de _END_ de _TOTAL_ resultados",
            infoEmpty: "Viendo 0 de 0 de 0 resultados",
            infoFiltered: "(filtered from _MAX_ total entries)",
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
          order: [],
          dom: "lBfrtip",
          buttons: [
            {
            //   extend: "excel",
            //   // messageTop: textoLab + " " + textoNegocio + " " + leyendaIva,
            //  messageTop: cabeceraExcel, 
            //  filename: "lista-precios-" + fechaFormateada,
            
            
            extend: "excelHtml5",
            messageTop: cabeceraExcel,
            title:empresa,
            autoFilter: true,
            filename: "lista-precios-" + fechaFormateada,
            exportOptions: {
              columns: imagenProducto === "Si" ? ':not(:nth-child(4))' : ':visible' // Suponiendo que la columna de imagen es la cuarta columna
            },
            customize: function (xlsx) {
             var sheet = xlsx.xl.worksheets['sheet1.xml'];

              // 1. Aplicar estilo a la celda A1 (tamaño grande y negrita)
              var cellA1 = $('c[r=A1]', sheet);               
               cellA1.attr('s', '51'); 
              

             
              // 2. Aplicar estilo a la celda B2 (negrita, alineación izquierda y tamaño grande)
              var cellB2 = $('c[r=A2]', sheet);              
              cellB2.attr('s', '50'); // Aplicar estilo 50 (Left aligned text)

              
              // Ajustar manualmente el tamaño de la fuente en B2
              

              // 3. Aplicar estilo a las cabeceras de la tabla (fondo gris oscuro y texto blanco)
              var headerCells = $('row[r=3] c', sheet); // Fila 3 es la cabecera
              headerCells.each(function () {
                $(this).attr('s', '7'); // Aplicar estilo 6 (White text, grey background)
              });
                 
            },
            
            },
            {
              extend: "pdfHtml5",
              orientation: "landscape",
              pageSize: "A4",
              filename: "lista-precios-" + fechaFormateada,
              title: titulo,
              customize: function (doc) {
                const data = contienes.DataTable().rows().data();

                doc.pageMargins = [10, 10, 10, 20];
                doc.content[1].table.dontBreakRows = true;

                if (
                  !doc.content ||
                  !Array.isArray(doc.content) ||
                  doc.content.length < 2 ||
                  !doc.content[1].table ||
                  !Array.isArray(doc.content[1].table.body)
                ) {
                  console.error(
                    "Error: Estructura del documento PDF no válida para la personalización."
                  );
                  return;
                }

                for (let i = 0; i < data.length; i++) {
                  const imgHtml = data[i][3];
                  const imgElement = $(imgHtml).filter(".fotoProducto")[0];

                  if (
                    imgElement &&
                    imgElement.src &&
                    imgElement.src.startsWith("data:image")
                  ) {
                    const imgData = imgElement.src;

                    if (
                      doc.content[1].table.body[i + 1] &&
                      doc.content[1].table.body[i + 1][3]
                    ) {
                      doc.content[1].table.body[i + 1][3] = {
                        image: imgData,
                        width: 40,
                        margin: [3, 3, 3, 3],
                      };
                    }
                  }
                }

                
                const logoValido = logo && logo.startsWith("data:image/");
                doc.content.unshift({
                  columns: [
                    logoValido
                      ? {
                          image: logo,
                          width: 100,
                        }
                      : null,
                    {
                      text: [
                        {
                          text: empresa + "\n",
                          fontSize: 14,
                          bold: true,
                          alignment: "center",
                          margin: [0, 0, 0, 15],
                        },
                        // {
                        //   text: titulo + "\n",
                        //   fontSize: 12,
                        //   bold: true,
                        //   alignment: "left",
                        //   margin: [0, 10, 0, 10],
                        // },
                        ...opcionesFormateadas,
                      ],
                      margin: [0, 0, 0, 0],
                    },
                  ].filter(Boolean),
                  margin: [0, 0, 0, 10],
                });
                doc.footer = function (currentPage, pageCount) {
                  return {
                    columns: [
                      {
                        text:
                          "Página " +
                          currentPage.toString() +
                          " de " +
                          pageCount,
                        alignment: "center",
                        style: "subheader",
                      },
                    ],
                    margin: [5, 5, 5, 5],
                  };
                };

              },
              exportOptions: {
                stripHtml: false,
                columns: ":visible",
                search: "applied",
                order: "applied",
              },
            },
          ],
        });

        $("#spinner").hide();
      }
    },
    error: function (x, e) {
      let m = "Ajax error: ";
      if (x.status === 0) {
        m += "Check your network connection.";
      }
      if (x.status === 404 || x.status === 500) {
        m += x.status;
      }
      if (e === "parsererror" || e === "timeout") {
        m += e;
      }
      alert(m);
    },
  });
}
// funcion para buscar productos en catalogo para busqueda rapida y completa
async function buscarProductosCatalogo(paramTipoBusqueda =null){
  event.preventDefault(); // Prevenir el comportamiento por defecto del formulario
  if(paramTipoBusqueda==null){
    // Crear un formulario temporal
    var form = $('<form>', {
        'action': 'ajax/json_listados.php',
        'method': 'POST',
        'target': '_blank'
    }).append($('<input>', {
        'name': 'categoria',
        'value': $('#buscaCategoria').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'rubro',
        'value': $('#buscaRubro').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'subrubro',
        'value': $('#buscaSubRubro').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'marca',
        'value': $('#buscaMarca').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'imagenProducto',
        'value': $('#imagenProducto').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'sizeFoto',
        'value': $('#sizeFoto').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'ordenarPor',
        'value': $('#ordenarPor').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'direccionOrden',
        'value': $('#direccionOrden').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'laboratorio',
        'value': $('#buscaLaboratorio').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'proveedor',
        'value': $('#buscaProveedor').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'tacc',
        'value': $('#buscaTacc').val(),
        'type': 'hidden'
    })).append($('<input>', {
        'name': 'listaCatalogoProducto',
        'value': 2,
        'type': 'hidden'
    }));
  }

  // si soy busqueda rapida
  if(paramTipoBusqueda==1){
   
      nombreArticulo = $('#nombreBuscaRapido').val();     
      idArticulo = $('#itemId').val();
      // buscco comodin
      if(idArticulo===""){
        claseBusca="texto";
      }
      // busco por codigo un solo producto.
      if(idArticulo!==""){
        // console.log(idArticulo);
        // console.log('dentro del articulo difrente a null')
        claseBusca = "codigo";
      }
    var form = $('<form>', {
      'action': 'ajax/json_listados.php',
      'method': 'POST',
      'target': '_blank'
  }).append($('<input>', {
      'name': 'claseBusca',
      'value':claseBusca,
      'type': 'hidden'
  })).append($('<input>', {
      'name': 'idArticulo',
      'value': idArticulo,
      'type': 'hidden'
  })).append($('<input>', {
      'name': 'nombreArticulo',
      'value': nombreArticulo,
      'type': 'hidden'
  
  })).append($('<input>', {
      'name': 'imagenProducto',
      'value': $('#imagenProducto').val(),
      'type': 'hidden'
  })).append($('<input>', {
      'name': 'sizeFoto',
      'value': $('#sizeFoto').val(),
      'type': 'hidden'
  })).append($('<input>', {
      'name': 'ordenarPor',
      'value': $('#ordenarPor').val(),
      'type': 'hidden'
  })).append($('<input>', {
      'name': 'direccionOrden',
      'value': $('#direccionOrden').val(),
      'type': 'hidden'
  
  })).append($('<input>', {
      'name': 'listaCatalogoProducto',
      'value': 2,
      'type': 'hidden'
  }));
  }

  // Añadir el formulario temporal al cuerpo y enviarlo
  form.appendTo('body').submit().remove();
  //vacio el idart y el nombre
  $('#itemId').val(''); 
  $('#nombreBuscaRapido').val('');
} 