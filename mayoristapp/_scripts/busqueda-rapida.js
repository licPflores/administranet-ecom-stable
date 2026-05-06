// autocompletar busqueda rapida para clientes, productos x ahora.
// todo: posibilidad de imagen si es producto? quizas..
// todo: usar funcion mejorada por ia. buscar por varios campos autocompletar el escrito.

var accionAutoCompletarClick;

// lipieza del buscador 
const limpiarBuscaRapido = function(){
    //    console.log('soy un search');
        
    let inputIdArt = document.getElementById("itemId");
    inputIdArt.value="";
};

// esta funcion hace la busequeda por texto.
const buscarClienteBotonClick = function (){
   // console.log('hola soy buscar por texto')
   
       
    //e.preventDefault();
    $("#clienteOk").hide();
  

    var contienes = $('#myTable'),
        comoBusco = 0,
        claseBusca = "texto",
        siBusco = 0,
        nombreCliente = $('#nombreBuscaRapido').val(),
       
        idCliente = $('#itemId').val();


        

        //console.log('boton de buscar dentro del cliente',botonBusca);
       
    // clase busca, "codigo" si tiene un id de cliente o "texto" si viene de la busqueda por nombre.
    if (nombreCliente === "") {
        //console.warn('nombre cliente  viene vacio',nombreCliente);
        return false;
    }
    let botonBusca = document.getElementById("botonBuscarRapido");
    botonBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';



    $.ajax({
        type: 'POST',
        url: 'relay-clientes.php',
        data: {
            "buscarCliente": 1,
            "codigo": "",
            "queCliente": nombreCliente,
            "claseBusqueda": claseBusca


        },
        success: function(response) {
            //console.log(response);
            botonBusca.disabled = false;
            botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
            $('#contiene-tabla').show();
            contienes.empty();
            if ($.fn.dataTable.isDataTable('#myTable')) {
                contienes.DataTable().destroy();
            }

            contienes.html(response);

            contienes.DataTable({
                searching: false,
                responsive: false,
                paging: false,
                ordering: false,
                "pageLength": 5,
                "language": {
                    "emptyTable": "No data available in table",
                    "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                    "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": "",
                    "lengthMenu": "Ver _MENU_ entradas",
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                    "search": "Buscar:",
                    "zeroRecords": "No matching records found",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": activate to sort column ascending",
                        "sortDescending": ": activate to sort column descending"
                    }
                },
                columnDefs: [{
                    targets: [1],
                    class: "wrap-no",
                }, ],

            });
            // alta de cliente
            //==================================================
            $('#myTable tbody').on("click", "td .selCliente", function() {
                //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                var codigoCliente = $(this).attr('rel');

                //                                        alert(codigoCliente);
                $.ajax({
                    type: 'post',
                    url: 'seleccionar-cliente.php',
                    data: {
                        "ajax": "true",
                        "codCliente": codigoCliente

                    },
                    success: function(response) {
                        //console.log(response);
                        // puedo no tener domicilios no puedo dejar seguir.
                        vuelta=JSON.parse(response);
                        if(vuelta.msg=='ok'){
                            $("#clienteOk").show();
                            Swal.fire({
                                icon: 'success',
                                toast: true,
                                position: 'top',
                                text: 'El cliente fue seleccionado con exito!',
                                showConfirmButton: false
                            });
                            var oferta = $("#buscaOferta").val();

                            if (oferta === '') {
                                location.href = 'listado-clientes.php';
                            } else {
                                var idArt = $("#IDArt").val();
                                var cantidad = $("#cant").val();
                                var urlLink = "alta_pedido.php?buscaOferta=" + oferta + "&IDArt=" + idArt + "&cant=" + cantidad;
                                location.href = urlLink;
                            }
                        }

                        if(vuelta.msg=='error'){
                            Swal.fire({
                                icon: 'error',
                                toast: true,
                                position: 'top',
                                text: vuelta.desc,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(x, e) {
                        var s = x.status,
                            m = 'Ajax error: ';
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
            //mostrar la modal
            // editar el cliente

            //==================================================
            $('#myTable tbody').on("click", "td .editCliente", function() {
                //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                var codigoCliente = $(this).attr('rel');
                console.log("hice click en el edicion:=> " + codigoCliente);
                location.href = "mod-cliente-rapido.php?id=" + codigoCliente;

            });
            $('#myTable tbody').on("click", "td .editDomicilios", function() {
                //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                var codigoCliente = $(this).attr('rel');

                location.href = "abm-cliente-domicilios.php?id=" + codigoCliente;

            });


            //                            contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
            //                            contienes.tablesorterPager({container: $("#pager"),size:10});

            //$('#jcart-buttons').remove();

        },
        // See: http://www.maheshchari.com/jquery-ajax-error-handling/
        error: function(x, e) {
            var s = x.status,
                m = 'Ajax error: ';
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


}


// * obtener la lista de json tanto cliente como el que no quiere tener.
// tipo: cliente, articulo, etc
function inicioAutoCompletar(tipo){
    let listado;
    let buscaRapido = document.getElementById("nombreBuscaRapido");
    let nombreBoton= "botonBuscarRapido";
    let botonBuscar = document.getElementById("botonBuscarRapido");
    let botonFiltroBuscar  = document.getElementById("botonBuscarFiltrar");
    buscaRapido.addEventListener("search",limpiarBuscaRapido);
    
    if(tipo==="cliente"){
        accionAutoCompletarClick = buscarAutoCompletarCliente;
        botonBuscar.addEventListener("click",buscarClienteBotonClick);
    }
    if(tipo==="articulo"){
        accionAutoCompletarClick = buscarAutoCompletarArticulo;
        botonBuscar.addEventListener("click",buscarArticuloBotonClick);
        botonFiltroBuscar.addEventListener("click",buscarArticuloFiltroBotonClick);

    }
    // listados donde se usen productos 
    if(tipo=="articuloListaPrecio"){
        accionAutoCompletarClick = buscarAutoCompletarArticuloListaPrecio;
        botonBuscar.addEventListener("click",buscarArticuloListaPrecioBotonClick);
    }
   // listados donde se usen productos 
    if(tipo=="catalogoProducto"){
        accionAutoCompletarClick = buscarAutoCompletarArticuloCatalogo;
        botonBuscar.addEventListener("click",buscarArticuloCatalogoBotonClick);
    }
    //$('#botonBuscarRapido').click(funcionBuscaRapidaCliente);
//if(tipo==='cliente'){

    //let clienteStorage = localStorage.getItem('listaClientes');
    //  console.log('cliente Storage',clienteStorage);
    // storage Vacio buscar con ajax...
    //if(clienteStorage==null){
//     console.log('vacio storage');
//     buscar// no he guardado nada    en storage
        $.ajax({
            // ruta relativa o absoluta de donde se encuentra el fichero o archivo
            url: "json/autocompletar-json.php",
            type: "GET",
            data:{
                tipo:tipo
            },
            dataType: 'json',
            success: function(data) {
                // hacer tu logica ya teniendo la informacion del json
                //  console.log({data});
                listado= data; // instancio la vuelta

                autocomplete(buscaRapido,listado,nombreBoton);
                
                //localStorage.setItem('listaClientes',listado);// guardo en local storage
            },
            error: function(data) {
                // logica si falla la carga
                console.log('no habia para cargar');
            }
        });

     

   // }
    // tengo storage.
    //if(clienteStorage!==null){
        // console.log('storage completo lo parseo con json.');
        //var clientes =clienteStorage;
    //}

    //return listado;
    
}
// decido que busco y puedo hacer otras cosas


// busco clientes con autocompletar. o sea solo x id
const buscarAutoCompletarCliente = function(){
    console.log('hola soy un autocompletar cliente')
   
       
        //e.preventDefault();
        $("#clienteOk").hide();
        $("#spinner").hide();

        var contienes = $('#myTable'),
            comoBusco = 0,
            claseBusca = "codigo",
            siBusco = 0,
            nombreCliente = $('#nombreBuscaRapido').val(),           
            idCliente = $('#itemId').val();
    //console.log('boton de buscar dentro del cliente',botonBusca);
        // clase busca, "codigo" si tiene un id de cliente o "texto" si viene de la busqueda por nombre.
        if (idCliente === "") {
            //console.warn('idCliente viene vacio',idCliente);
            return false;
        }

        let botonBusca = document.getElementById("botonBuscarRapido");
        botonBusca.disabled = true;
        botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';


        $.ajax({
            type: 'POST',
            url: 'relay-clientes.php',
            data: {
                "buscarCliente": 1,
                "codigo": idCliente,
                "queCliente": nombreCliente,
                "claseBusqueda": claseBusca


            },
            success: function(response) {
                // console.log(response);
                botonBusca.disabled = false;
                botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
                $('#contiene-tabla').show();
                contienes.empty();
                if ($.fn.dataTable.isDataTable('#myTable')) {
                    contienes.DataTable().destroy();
                }

                contienes.html(response);

                contienes.DataTable({
                    searching: false,
                    responsive: false,
                    paging: false,
                    ordering: false,
                    "pageLength": 4,
                    "language": {
                        "emptyTable": "No data available in table",
                        "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                        "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "infoPostFix": "",
                        "thousands": "",
                        "lengthMenu": "Ver _MENU_ entradas",
                        "loadingRecords": "Loading...",
                        "processing": "Processing...",
                        "search": "Buscar:",
                        "zeroRecords": "No matching records found",
                        "paginate": {
                            "first": "Primero",
                            "last": "Ultimo",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        },
                        "aria": {
                            "sortAscending": ": activate to sort column ascending",
                            "sortDescending": ": activate to sort column descending"
                        }
                    },
                    columnDefs: [{
                        targets: [1],
                        class: "wrap-no",
                    }, ],

                });
                // alta de cliente
                //==================================================
                $('#myTable tbody').on("click", "td .selCliente", function() {
                    //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                    var codigoCliente = $(this).attr('rel');

                    //                                        alert(codigoCliente);
                    $.ajax({
                        type: 'post',
                        url: 'seleccionar-cliente.php',
                        data: {
                            "ajax": "true",
                            "codCliente": codigoCliente

                        },
                        success: function(response) {
                            //console.log(response);
                            $("#clienteOk").show();
                            Swal.fire({
                                icon: 'success',
                                toast: true,
                                position: 'top',
                                text: 'El cliente fue seleccionado con exito!',
                                showConfirmButton: false
                            });
                            var oferta = $("#buscaOferta").val();

                            if (oferta === '') {
                                location.href = 'listado-clientes.php';
                            } else {
                                var idArt = $("#IDArt").val();
                                var cantidad = $("#cant").val();
                                var urlLink = "alta_pedido.php?buscaOferta=" + oferta + "&IDArt=" + idArt + "&cant=" + cantidad;
                                location.href = urlLink;
                            }
                        },
                        error: function(x, e) {
                            var s = x.status,
                                m = 'Ajax error: ';
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
                //mostrar la modal
                // editar el cliente

                //==================================================
                $('#myTable tbody').on("click", "td .editCliente", function() {
                    //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                    var codigoCliente = $(this).attr('rel');
                    console.log("hice click en el edicion:=> " + codigoCliente);
                    location.href = "mod-cliente-rapido.php?id=" + codigoCliente;

                });
                $('#myTable tbody').on("click", "td .editDomicilios", function() {
                    //alert('<h3>Probando la modal '+ $(this).attr('rel')+'</h3>');
                    var codigoCliente = $(this).attr('rel');

                    location.href = "abm-cliente-domicilios.php?id=" + codigoCliente;

                });


                //                            contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
                //                            contienes.tablesorterPager({container: $("#pager"),size:10});

                //$('#jcart-buttons').remove();

            },
            // See: http://www.maheshchari.com/jquery-ajax-error-handling/
            error: function(x, e) {
                var s = x.status,
                    m = 'Ajax error: ';
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

    
}
// * funcion que activa la busqueda rapida al hacer click en el boton buscar.
const buscarArticuloBotonClick= function (){
    // console.log('buscando el articulo boton busca rapida con click');

        var contienes = $('#myTable'),   
        buscaRapida = $('#nombreBuscaRapido').val(),
//                        claseBusqueda = $('#claseBusqueda').val(),
               //meti por defecto que busque en el incluye texto.
          
           claseBusqueda = "texto",
           idArticulo  = $('#itemId').val(),
           cantidadOferta = $('[name="my-item-qty"]').val();

           //queCampo= $('input[name="queCampo"]:checked').val();
           
           
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
        //    console.log('misconsumos::{'+misConsumos.prop("checked")+'}');
        //    console.log('mis promociones::{'+promociones.prop("checked")+'}');   
           
           // categorias , rubro subrubro y promociones son buscan sin dar bola a texto...
//                        idOferta = $('[name="my-item-oferta-id"]').val();

        let botonBusca = document.getElementById("botonBuscarRapido");
        let inputBusca = document.getElementById("nombreBuscaRapido");
        botonBusca.disabled = true;
        inputBusca.disabled = true;
        botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';

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
           
      
           $.ajax({
               type: 'POST',
               url: 'relay-art.php',
               data:{
                   "ajax" : "true",
                   "queArticulo"       : buscaRapida,
                   "buscarProducto"    : 1,
                   "idArticulo"        : "",
                   "cantidadOferta"    : cantidadOferta,
                   "claseBusca"        : claseBusqueda,
                   "categoria"         : categoria,
                   "rubro"             : rubro,
                   "subrubro"          : subrubro,
                   "marca"             : marca,
                   "modelo"            : modelo,
                   "queCampo"          : "",
                   "consumo"           : consumo,
                   "promo"             : promo
//                                "idOferta"      :   idOferta
                   
               },
               success: function(response) {
                       // Refresh the cart display after a successful Ajax request
                    //    console.log('vuelta de la busqueda por cliek',response);
                       botonBusca.disabled = false;
                       inputBusca.disabled = false;
                       botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
                       if(response=='0'){
                           //alert('traje vacio');
                           contienes.empty();
                           contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
                           
                        //    $("#spinner").hide();
                       }
                       else{
                           //alert(response);
                           contienes.empty();
                           if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                               contienes.DataTable().destroy();
                           }
                           
                           contienes.html(response);
                           contienes.DataTable({
                               searching:false,
                               info:false,
                               lengthMenu: [ [5, 10, 15, -1], [5, 10, 15, "All"] ],
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

                        //    $("#spinner").hide();
                           
                        //    $('#queArticuloId').val('');
                           //mostrar la modal
                           $("input[type='number']").on("click", function () {
                               $(this).select();
                            });
                            // $('#myTable tbody').on("click","td .ver-mas",funcionDescArt);
                            $('.ver-mas').on("click",funcionDescArt);
                            
                           //$(".desc-articulo").click(funcionDescArt);                                    
                            $('#myTable tbody').on("click","td .tecompro", funcionComprarArt);
                           //$('.tecompro').click(funcionComprarArt); 
                           
//                                        contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
//                                        contienes.tablesorterPager({container: $("#pager")});
                       
                       //$('#jcart-buttons').remove();
                       }// el cierre del else del        
               },
               // See: http://www.maheshchari.com/jquery-ajax-error-handling/
               error: function(x, e) {
                        botonBusca.disabled = false;
                        inputBusca.disabled = false;
                       botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
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
       

   
  
}
const buscarArticuloFiltroBotonClick= function (){
    // console.log('buscando el articulo boton busca rapida con click');

        var contienes = $('#myTable'),   
        buscaRapida = $('#nombreBuscaRapido').val(),
//                        claseBusqueda = $('#claseBusqueda').val(),
               //meti por defecto que busque en el incluye texto.
          
           claseBusqueda = "filtro",
           idArticulo  = $('#itemId').val(),
           cantidadOferta = $('[name="my-item-qty"]').val();

           //queCampo= $('input[name="queCampo"]:checked').val();
           
           
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
        //    console.log('misconsumos::{'+misConsumos.prop("checked")+'}');
        //    console.log('mis promociones::{'+promociones.prop("checked")+'}');   
           
           // categorias , rubro subrubro y promociones son buscan sin dar bola a texto...
//                        idOferta = $('[name="my-item-oferta-id"]').val();

        let botonBusca = document.getElementById("botonBuscarRapido");
        let botonFiltro = document.getElementById("botonBuscarFiltrar");
        let inputBusca = document.getElementById("nombreBuscaRapido");
        // botonBusca.disabled = true;
        inputBusca.disabled = true;
        // botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';
        botonFiltro.disabled= true;
        botonFiltro.innerHTML ='<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';

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
           
      
           $.ajax({
               type: 'POST',
               url: 'relay-art.php',
               data:{
                   "ajax" : "true",
                   "queArticulo"       : buscaRapida,
                   "buscarProducto"    : 1,
                   "idArticulo"        : "",
                   "cantidadOferta"    : cantidadOferta,
                   "claseBusca"        : claseBusqueda,
                   "categoria"         : categoria,
                   "rubro"             : rubro,
                   "subrubro"          : subrubro,
                   "marca"             : marca,
                   "modelo"            : modelo,
                   "queCampo"          : "",
                   "consumo"           : consumo,
                   "promo"             : promo
//                                "idOferta"      :   idOferta
                   
               },
               success: function(response) {
                       // Refresh the cart display after a successful Ajax request
                    //    console.log('vuelta de la busqueda por cliek',response);
                    //    botonBusca.disabled = false;
                       inputBusca.disabled = false;
                    //    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
                       botonFiltro.disabled= false;
                       botonFiltro.innerHTML ='<i class="fas fa-sliders-h"></i> Aplicar';

                       if(response=='0'){
                           //alert('traje vacio');
                           contienes.empty();
                           contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
                           
                        //    $("#spinner").hide();
                       }
                       else{
                           //alert(response);
                           contienes.empty();
                           if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                               contienes.DataTable().destroy();
                           }
                           
                           contienes.html(response);
                           contienes.DataTable({
                               searching:false,
                               info:false,
                               lengthMenu: [ [5, 10, 15, -1], [5, 10, 15, "All"] ],
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

                        //    $("#spinner").hide();
                           
                        //    $('#queArticuloId').val('');
                           //mostrar la modal
                           $("input[type='number']").on("click", function () {
                               $(this).select();
                            });
                            // $('#myTable tbody').on("click","td .ver-mas",funcionDescArt);
                            $('.ver-mas').on("click",funcionDescArt);
                            
                           //$(".desc-articulo").click(funcionDescArt);                                    
                            $('#myTable tbody').on("click","td .tecompro", funcionComprarArt);
                           //$('.tecompro').click(funcionComprarArt); 
                           
//                                        contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
//                                        contienes.tablesorterPager({container: $("#pager")});
                       
                       //$('#jcart-buttons').remove();
                       }// el cierre del else del        
               },
               // See: http://www.maheshchari.com/jquery-ajax-error-handling/
               error: function(x, e) {
                        // botonBusca.disabled = false;
                        inputBusca.disabled = false;
                    //    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
                        botonFiltro.disabled= false;
                       botonFiltro.innerHTML ='<i class="fas fa-sliders-h"></i> Aplicar';
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
       

   
  
}

// * funcion que toma el clik del la lista de precios al buscar rapido.
const buscarArticuloListaPrecioBotonClick = async function() {
    console.log('hola buscarAutoCompletarArticuloBotonClick ');
    let botonBusca = document.getElementById("botonBuscarRapido");
    let inputBusca = document.getElementById("nombreBuscaRapido");
    botonBusca.disabled = true;
    inputBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';
    await buscarProductosLp(1);
    botonBusca.disabled = false;
    inputBusca.disabled = false;
    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
};


// const buscarArticuloListaPrecioBotonClick= function (){
//     // manejo de botonera
//     let botonBusca = document.getElementById("botonBuscarRapido");
//     let inputBusca = document.getElementById("nombreBuscaRapido");
//     botonBusca.disabled = true;
//     inputBusca.disabled = true;
//     botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';

  
    
// //                        claseBusqueda = $('#claseBusqueda').val(),
//                //meti por defecto que busque en el incluye texto.
          
              

//            var contienes       = $('#myTable'),
//            categoria       = $('#buscaCategoria').val(),
//            rubro           = $('#buscaRubro').val(),
//            subrubro        = $('#buscaSubRubro').val(),
//            marca           = $('#buscaMarca').val(),
//            idTipoCliente   = $('#tipoCliente').val(),
//            laboratorio     = $('#buscaLaboratorio'),
//            ivaIncluido     = $('#buscaTipoIva').val(),
//            imagenProducto = $('#imagenProducto').val();
//            proveedor = $('#buscaProveedor'),
//            valProveedor="",
//            tacc = $('#buscaTacc'),
//            valTacc  = "",
//            misConsumos     =0,
//            claseBusqueda = "texto",
//            idArticulo  = $('#itemId').val(),           
//            textoMarca ="",            
//            textoNegocio    ="",
//            textoCategoria  ="",
//            buscaRapida = $('#nombreBuscaRapido').val(),
//            textoRubro      ="",
//            textoSubRubro   ="",
//            textoLab        ="",
//            textoProveedor ="",
//            textoTacc="",
//            queLaboratorio  =null,
//            textoVigencia   ="",
//            queCliente      = $('#cliente').val(),
//            tipoCliente     ='si';
//            //console.log($("#buscaMiConsumo:checked").val());
           
//            if($("#buscaMiConsumo:checked").val()!==undefined){
               
//                misConsumos=1;
//            }

//            if(laboratorio!==undefined){
//                queLaboratorio=laboratorio.val(); // indice
//                textoLab = " Laboratorio: "+$('#buscaLaboratorio option:selected').text()+"\n";
//            }
//            if(proveedor!=undefined){
//                valProveedor=proveedor.val(); // indice
//                textoProveedor = " Proveedor: "+$('#buscaProveedor option:selected').text()+"\n";
//            }
//            if(tacc!=undefined){
//                valTacc=tacc.val(); // indice
//                textoTacc = " Producto con TACC: "+$('#buscaTacc option:selected').text()+"\n";
//            }
           
//            if(queCliente===undefined){
//                queCliente="cliente";
//            }
//            if(idTipoCliente!==0){
//                textoNegocio="Tipo Negocio: "+$('#tipoCliente option:selected').text()+"\n";
//            }
//            if(marca!=""){
//                textoMarca="Marca: "+$('#buscaMarca option:selected').text()+"\n";
//            }

//            // armar las cabeceras en dos columnas?
//            // obtener el logo en base 64
//            //console.log('los textos', textoLab, textoProveedor,textoTacc,textoNegocio,textoMarca);
           
      
//            $.ajax({
//             type: 'POST',
//             url: 'relay-art.php',
//             data:{
//                 "ajax" : "true",
//                 "queAccion": "listaPrecios",
//                 "tipoCliente"   : tipoCliente,
//                 "buscarProducto": 1,
//                 "queArticulo"   : buscaRapida,
//                 "idArticulo"    : "",
//                 "categoria"     : categoria,    
//                 "rubro"         : rubro,
//                 "subrubro"      : subrubro,
//                 "marca"         : marca,
//                 "idTipoCliente" : idTipoCliente,
//                 "claseBusca"    : claseBusqueda,
//                 "idArticulo"        : "",                
//                 "queCliente"    : queCliente,
//                 "ivaIncluido"   : ivaIncluido,
//                 "misConsumos"   : misConsumos,
//                 "laboratorio"   : queLaboratorio,
//                 "imagenProducto": imagenProducto,
//                 "proveedor" : valProveedor,
//                 "tacc":valTacc,
//             },
//             success: function(response) {
//                 botonBusca.disabled = false;
//                 inputBusca.disabled = false;
//                 botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
//                     var titulo=$("#tituloListaP").text(),
//                         leyendaIva='',
//                         fotoLogo=$("#imgLogo").attr("base64"),
//                         nombreLogo=$("#imgLogo").attr("title");
//                 if(ivaIncluido=='No'){
//                     leyendaIva ='Los precios publicados NO incluyen IVA';
//                 }
//                 if(ivaIncluido=='Si'){
//                     leyendaIva ='Los precios publicados incluyen IVA';
//                 }

// //                        console.log($(".cartelAdvertencia").text());
// //                        console.log($("#imgLogo").attr("base64"));
// //                        console.log($("#imgLogo").attr("title"));
//                     if(response=='0'){
//                         //alert('traje vacio');
//                         contienes.empty();
//                         contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
//                         $("#spinner").hide();
//                     }
//                     else{
//                         //alert(response);
//                         contienes.empty();
//                         if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
//                             contienes.DataTable().destroy();
//                         }
                        
//                         contienes.html(response);
//                         contienes.DataTable({
//                            searching:true,
//                            responsive:false,
                            
//                                 "language": {
//                                     "emptyTable":     "No data available in table",
//                                     "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
//                                     "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
//                                     "infoFiltered":   "(filtered from _MAX_ total entries)",
//                                     "infoPostFix":    "",
//                                     "thousands":      "",
//                                     "lengthMenu":     "Ver _MENU_ entradas",
//                                     "loadingRecords": "Loading...",
//                                     "processing":     "Processing...",
//                                     "search":         "Buscar:",
//                                     "zeroRecords":    "No matching records found",
//                                     "paginate": {
//                                         "first":      "Primero",
//                                         "last":       "Ultimo",
//                                         "next":       "Siguiente",
//                                         "previous":   "Anterior"
//                                     },
//                                     "aria": {
//                                         "sortAscending":  ": activate to sort column ascending",
//                                         "sortDescending": ": activate to sort column descending"
//                                         }
//                                     },
//                                     "order": [],
//                                     "dom": 'lBfrtip',
//                                     buttons: [
//                                         {
//                                             extend:'excel',
//                                             //messageTop:textoLab+" "+textoNegocio+" "+ leyendaIva
//                                         }, 
//                                         {
//                                             extend: 'pdfHtml5',
//                                             orientation: 'landscape',
//                                             customize: function(doc) {
//                                                 var data = contienes.DataTable().rows().data();
//                                                 doc.pageMargins = [0, 0, 0, 0];
//                                                 // console.warn('quetrae doc=>>>>>>>>>>>>>>>>>>');
//                                                 // console.log(doc);
//                                                 if (!doc.content || !Array.isArray(doc.content) || doc.content.length < 2 || !doc.content[2].table || !Array.isArray(doc.content[2].table.body)) {
//                                                         console.error('Error: Estructura del documento PDF no válida para la personalización.');
//                                                         return;
//                                                     }

//                                                 for (var i = 0; i < data.length; i++) {
//                                                     var imgHtml = data[i][3]; // Columna 4 (índice 3) contiene la imagen
//                                                     var imgElement = $(imgHtml).filter('.fotoProducto')[0];

//                                                     // if (imgElement && imgElement.src.startsWith('data:image')) {
//                                                     if (imgElement && imgElement.src!='') {
//                                                         var imgData = imgElement.src;
//                                                         if (doc.content[2].table.body[i + 1]) { // i + 1 para ajustar al índice de la tabla en PDF
//                                                             doc.content[2].table.body[i + 1][3] = {
//                                                                 image: imgData,
//                                                                 width: 50
//                                                             };
//                                                         }
//                                                     }
//                                                 }

//                                                 doc.footer = function(currentPage, pageCount) {
//                                                     return {
//                                                         text: leyendaIva,
//                                                         alignment: 'center'
//                                                     };
//                                                 };
//                                             },
//                                             exportOptions: {
//                                                 stripHtml: false,
//                                                 columns: ':visible',
//                                                 search: 'applied',
//                                                 order: 'applied'
//                                             },
//                                             //messageTop: textoLab + " " + textoNegocio + " " + leyendaIva + "\n" + textoVigencia
//                                         }
//                                                                     // codigo anterior de pdf
//                                         // {
//                                         //     extend: 'pdf',
//                                         //     orientation: 'landscape',
//                                         //     customize:  function(doc) {
                                                
//                                         //         // // Agregar encabezado
//                                         //         // doc.content.splice(0, 0, {
//                                         //         //   text: 'IVA incluido',
//                                         //         //   style: 'header'
//                                         //         // });

//                                         //         // Agregar pie de página a cada página
//                                         //         doc.footer = function(currentPage, pageCount) {
//                                         //           return {
//                                         //             text: leyendaIva,
//                                         //             alignment: 'center'
//                                         //           };
//                                         //         };
//                                         //       } , 
//                                         //     messageTop:textoLab+" "+textoNegocio+" "+leyendaIva+"\n"+textoVigencia
//                                         // }
//                                     ]

//                                 });
                                

                        
//                         $("#spinner").hide();

//                     //$('#jcart-buttons').remove();
//                     }// el cierre del else del        
//             },
//             // See: http://www.maheshchari.com/jquery-ajax-error-handling/
//             error: function(x, e) {
//                 botonBusca.disabled = false;
//                 inputBusca.disabled = false;
//                 botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
//                     var s = x.status, 
//                             m = 'Ajax error: ' ; 
//                     if (s === 0) {
//                             m += 'Check your network connection.';
//                     }
//                     if (s === 404 || s === 500) {
//                             m += s;
//                     }
//                     if (e === 'parsererror' || e === 'timeout') {
//                             m += e;
//                     }
//                     alert(m);
//             }

//         });
       

   
  
// }

// busco articulos con autocompletar o sea solo x id
// buscar autocompletar para la lista de precios 
const buscarAutoCompletarArticuloListaPrecio = async function() {
    console.log('hola buscarAutoCompletarArticuloBotonClick ');
    let botonBusca = document.getElementById("botonBuscarRapido");
    let inputBusca = document.getElementById("nombreBuscaRapido");
    botonBusca.disabled = true;
    inputBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';
    await buscarProductosLp(1);
    botonBusca.disabled = false;
    inputBusca.disabled = false;
    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
}

// buscar articulos pero sin lista de precio.
const buscarAutoCompletarArticulo= function (){
     console.log('hola buscarAutoCompletarArticulo ');
        var contienes = $('#myTable'),   
        comoBusco = 0,
        claseBusca = "codigo",
        siBusco = 0,
        nombreArticulo = $('#nombreBuscaRapido').val(),   
        cantidadOferta=1,        
        idArticulo = $('#itemId').val();
    //console.log('boton de buscar dentro del cliente',botonBusca);
    // clase busca, "codigo" si tiene un id de cliente o "texto" si viene de la busqueda por nombre.
    if (idArticulo === "") {
        //console.warn('idCliente viene vacio',idCliente);
        return false;
    }

    let botonBusca = document.getElementById("botonBuscarRapido");
    let inputBusca = document.getElementById("nombreBuscaRapido");
    botonBusca.disabled = true;
    inputBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';

       
           // solo busco por id o codigo.
           $.ajax({
               type: 'POST',
               url: 'relay-art.php',
               data:{
                   "ajax" : "true",
                   "buscarProducto"    : 1,                               
                   "queArticulo"       : nombreArticulo,                   
                   "idArticulo"        : idArticulo,                   
                   "claseBusca"        : claseBusca,
                   
               },
               success: function(response) {
                       // Refresh the cart display after a successful Ajax request
                    //    console.log('buscando rapidamente articulo.',response);
                       botonBusca.disabled = false;
                       inputBusca.disabled = false;
                       botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
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
                               searching:false,
                               info:false,
                               paging:true,
                               lengthMenu: [ [5, 10, 15, -1], [5, 10, 15, "All"] ],
                            //    "lengthMenu": [ [5, 10, 15, -1], [5, 10, 15, "All"] ],
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

                        //    $("#spinner").hide();
                           
                          // $('#queArticuloId').val('');
                           //mostrar la modal
                           $("input[type='number']").on("click", function () {
                               $(this).select();
                            });

                            $('.ver-mas').on("click",funcionDescArt);
                            // $('#myTable tbody').on("click",".ver-mas", funcionDescArt);
                           //$(".desc-articulo").click(funcionDescArt);                                    
                            // $('#myTable tbody').on("click",".tecompro", funcionComprarArt);
                            $('.tecompro').on("click",funcionComprarArt);

                            
                           //$('.tecompro').click(funcionComprarArt); 
                           
//                                        contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
//                                        contienes.tablesorterPager({container: $("#pager")});
                       
                       //$('#jcart-buttons').remove();
                       }// el cierre del else del        
               },
               // See: http://www.maheshchari.com/jquery-ajax-error-handling/
               error: function(x, e) {
                    botonBusca.disabled = false;
                    inputBusca.disabled = false;
                    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
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
       

   
 
}
// buscar articulos autocompletar para lista de precios.
// const buscarAutoCompletarArticuloListaPrecio= function (){
//     console.log('hola soy autocmpletar articulo click en lista rprecio');
//     let botonBusca = document.getElementById("botonBuscarRapido");
//     let inputBusca = document.getElementById("nombreBuscaRapido");
//     botonBusca.disabled = true;
//     inputBusca.disabled = true;
//     botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';

//     var contienes = $('#myTable'),   
//     comoBusco = 0,
//     claseBusca = "codigo",
//     siBusco = 0,
//     nombreArticulo = $('#nombreBuscaRapido').val(),   
//     cantidadOferta=1,
//     ivaIncluido     = $('#buscaTipoIva').val(),
//     imagenProducto = $('#imagenProducto').val();        
//     idArticulo = $('#itemId').val();
//     //console.log('boton de buscar dentro del cliente',botonBusca);
//     // clase busca, "codigo" si tiene un id de cliente o "texto" si viene de la busqueda por nombre.
//     if (idArticulo === "") {
//         //console.warn('idCliente viene vacio',idCliente);
//         return false;
//     }

    

       
//            // solo busco por id o codigo. pero necesito opciones por ser unalista de precios.
//            $.ajax({
//                type: 'POST',
//                url: 'relay-art.php',
//                data:{
//                    "ajax" : "true",
//                    "buscarProducto"    : 1,                               
//                    "queArticulo"       : nombreArticulo,                   
//                    "idArticulo"        : idArticulo,                   
//                    "claseBusca"        : claseBusca,
//                    "queAccion": "listaPrecios",
//                    "imagenProducto": imagenProducto,
//                    "ivaIncluido"   : ivaIncluido,
                   
//                },
//                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
//                     //    console.log('buscando rapidamente articulo.',response);
//                        botonBusca.disabled = false;
//                        inputBusca.disabled = false;
//                        botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
                       
//                        var titulo=$("#tituloListaP").text(),
//                             leyendaIva='',
//                             fotoLogo=$("#imgLogo").attr("base64"),
//                             nombreLogo=$("#imgLogo").attr("title");
//                     if(ivaIncluido=='No'){
//                         leyendaIva ='Los precios publicados NO incluyen IVA';
//                     }
//                     if(ivaIncluido=='Si'){
//                         leyendaIva ='Los precios publicados incluyen IVA';
//                     }

// //                        console.log($(".cartelAdvertencia").text());
// //                        console.log($("#imgLogo").attr("base64"));
// //                        console.log($("#imgLogo").attr("title"));
//                         if(response=='0'){
//                             //alert('traje vacio');
//                             contienes.empty();
//                             contienes.html("<tr><td class='cartelSinResultados'><i class='fa fa-warning fa-lg'></i> No se encontaron resultados </td></tr>");
//                             $("#spinner").hide();
//                         }
//                         else{
//                             //alert(response);
//                             contienes.empty();
//                             if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
//                                 contienes.DataTable().destroy();
//                             }
                            
//                             contienes.html(response);
//                             contienes.DataTable({
//                                searching:true,
//                                responsive:false,
                                
//                                     "language": {
//                                         "emptyTable":     "No data available in table",
//                                         "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
//                                         "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
//                                         "infoFiltered":   "(filtered from _MAX_ total entries)",
//                                         "infoPostFix":    "",
//                                         "thousands":      "",
//                                         "lengthMenu":     "Ver _MENU_ entradas",
//                                         "loadingRecords": "Loading...",
//                                         "processing":     "Processing...",
//                                         "search":         "Buscar:",
//                                         "zeroRecords":    "No matching records found",
//                                         "paginate": {
//                                             "first":      "Primero",
//                                             "last":       "Ultimo",
//                                             "next":       "Siguiente",
//                                             "previous":   "Anterior"
//                                         },
//                                         "aria": {
//                                             "sortAscending":  ": activate to sort column ascending",
//                                             "sortDescending": ": activate to sort column descending"
//                                             }
//                                         },
//                                         "order": [],
//                                         "dom": 'lBfrtip',
//                                         buttons: [
//                                             {
//                                                 extend:'excel',
//                                                 //messageTop:textoLab+" "+textoNegocio+" "+ leyendaIva
//                                             }, 
//                                             {
//                                                 extend: 'pdfHtml5',
//                                                 orientation: 'landscape',
//                                                 customize: function(doc) {
//                                                     var data = contienes.DataTable().rows().data();
//                                                     doc.pageMargins = [0, 0, 0, 0];
//                                                     // console.warn('quetrae doc=>>>>>>>>>>>>>>>>>>');
//                                                     // console.log(doc);
//                                                     if (!doc.content || !Array.isArray(doc.content) || doc.content.length < 2 || !doc.content[2].table || !Array.isArray(doc.content[2].table.body)) {
//                                                             console.error('Error: Estructura del documento PDF no válida para la personalización.');
//                                                             return;
//                                                         }

//                                                     for (var i = 0; i < data.length; i++) {
//                                                         var imgHtml = data[i][3]; // Columna 4 (índice 3) contiene la imagen
//                                                         var imgElement = $(imgHtml).filter('.fotoProducto')[0];

//                                                         // if (imgElement && imgElement.src.startsWith('data:image')) {
//                                                         if (imgElement && imgElement.src!='') {
//                                                             var imgData = imgElement.src;
//                                                             if (doc.content[2].table.body[i + 1]) { // i + 1 para ajustar al índice de la tabla en PDF
//                                                                 doc.content[2].table.body[i + 1][3] = {
//                                                                     image: imgData,
//                                                                     width: 50
//                                                                 };
//                                                             }
//                                                         }
//                                                     }

//                                                     doc.footer = function(currentPage, pageCount) {
//                                                         return {
//                                                             text: leyendaIva,
//                                                             alignment: 'center'
//                                                         };
//                                                     };
//                                                 },
//                                                 exportOptions: {
//                                                     stripHtml: false,
//                                                     columns: ':visible',
//                                                     search: 'applied',
//                                                     order: 'applied'
//                                                 },
//                                                 //messageTop: textoLab + " " + textoNegocio + " " + leyendaIva + "\n" + textoVigencia
//                                             }
//                                                                         // codigo anterior de pdf
//                                             // {
//                                             //     extend: 'pdf',
//                                             //     orientation: 'landscape',
//                                             //     customize:  function(doc) {
													
//                                             //         // // Agregar encabezado
//                                             //         // doc.content.splice(0, 0, {
//                                             //         //   text: 'IVA incluido',
//                                             //         //   style: 'header'
//                                             //         // });
  
//                                             //         // Agregar pie de página a cada página
//                                             //         doc.footer = function(currentPage, pageCount) {
//                                             //           return {
//                                             //             text: leyendaIva,
//                                             //             alignment: 'center'
//                                             //           };
//                                             //         };
//                                             //       } , 
//                                             //     messageTop:textoLab+" "+textoNegocio+" "+leyendaIva+"\n"+textoVigencia
//                                             // }
//                                         ]

//                                     });
                                    

                            
//                             $("#spinner").hide();

//                         //$('#jcart-buttons').remove();
//                         }// el cierre del else del        
//                 },
//                 // See: http://www.maheshchari.com/jquery-ajax-error-handling/
//                 error: function(x, e) {
//                         var s = x.status, 
//                                 m = 'Ajax error: ' ; 
//                         if (s === 0) {
//                                 m += 'Check your network connection.';
//                         }
//                         if (s === 404 || s === 500) {
//                                 m += s;
//                         }
//                         if (e === 'parsererror' || e === 'timeout') {
//                                 m += e;
//                         }
//                         alert(m);
//                 }

//             });
       

   
 
// }

const buscarArticuloCatalogoBotonClick = async function() {
    console.log('hola buscarAutoCompletarArticuloBotonClick ');
    let botonBusca = document.getElementById("botonBuscarRapido");
    let inputBusca = document.getElementById("nombreBuscaRapido");
    botonBusca.disabled = true;
    inputBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';
    await buscarProductosCatalogo(1);
    botonBusca.disabled = false;
    inputBusca.disabled = false;
    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
};
const buscarAutoCompletarArticuloCatalogo = async function() {
    console.log('hola buscarAutoCompletarArticuloBotonClick ');
    let botonBusca = document.getElementById("botonBuscarRapido");
    let inputBusca = document.getElementById("nombreBuscaRapido");
    botonBusca.disabled = true;
    inputBusca.disabled = true;
    botonBusca.innerHTML =  '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere';
    await buscarProductosCatalogo(1);
    botonBusca.disabled = false;
    inputBusca.disabled = false;
    botonBusca.innerHTML ='<i class="fab fa-sistrix"></i> Buscar';
};

//* autocompletar generico para todos.
function autocomplete(inp, arr,botonAccion) {
    
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/
//    console.log('los items',arr);
   // console.log('el input',inp);
  
   var inputId= document.getElementById("itemId"); 
   inputId.value="";
   
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function(e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) {
            return false;
        }
        // limite de 3 letras minimo...
        // if (val && val.length < 3) {
        //     return false;
        // }
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        let nombreBuscar = "";
        let palabra = val.toUpperCase();
        let resultado=0;
        /*for each item in the array...*/
        for (i = 0; i < arr.length; i++) {
            //console.warn('control de falla',arr[i]);
            nombreBuscar = arr[i]['nombre'].toUpperCase();
            /*check if the item starts with the same letters as the text field value:*/
            //if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            //if (nombreBuscar.substr(0, palabra.length)==palabra||( nombreBuscar.indexOf(palabra)) !== -1) {
            //console.log(nombreBuscar.indexOf(palabra,0),palabra);    
            // todo busqueda rapida, si palabra se dividde con un espacion buscar cada valor que se escriba.
            if (( nombreBuscar.indexOf(palabra)) !== -1) {
                //create a DIV element for each matching element:*/
                // console.warn('hola soy un resultado',resultado);
                if(resultado==8){
                    return;
                }
                var res = nombreBuscar.replace(new RegExp(palabra, "gi"), function(x) {
                    return '<strong>' + x.toUpperCase() + '</strong>';
                });
                /*create a DIV element for each matching element:*/
                b = document.createElement("DIV");
                /*make the matching letters bold:*/
                //b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                //b.innerHTML += arr[i].substr(val.length);
                b.innerHTML = res;
                //insert a input field that will hold the current array item's value
                b.innerHTML += "<input type='hidden' rel-codigo='"+arr[i]['id']+"' rel-nombre-item='"+arr[i]['nombre']+"' value='" + arr[i]['nombre'] + "'>";
                //execute a function when someone clicks on the item value (DIV element):
                b.addEventListener("click", function(e) {
                    // voy a sacar el cod: para que solo ponga el nombre.
                    //insert the value for the autocomplete text field:
                   var input = this.getElementsByTagName("input")[0];
                   var lineaEncontada = this.getElementsByTagName("input")[0].value;
                   var id= input.getAttribute('rel-codigo');

                   var nombre = input.getAttribute('rel-nombre-item');
                    //var lineaEncontada = this.getElementsByTagName("input");
                 //   console.log('linea encontrada',{lineaEncontada});
                    //var arrLineaEncontrada = lineaEncontada.split(' Cod:');
                    
                //    console.log('id:',id,'nombre:',nombre) ;
                    // console.log('arrlinea encontrada',arrLineaEncontrada);
                    //console.log('arr i',arr[i]);


                    // inp.value = arrLineaEncontrada[0];
                    inputId.value = id;
                    inp.value= nombre;
                    closeAllLists();

                    // document.getElementById("botonBuscar").click();
                    //console.log('botonaccion',botonAccion);
                    // let elBoton  =  document.getElementById(botonAccion);
                    // console.log('objBoton', elBoton);
                    // elBoton.click();
                    // funcionBuscarRapida();
                    accionAutoCompletarClick();
                    //funcionBuscaRapida();
                    // llamar a la funcion que va a buscar por ID de la seleccion. siempre no importa si muestro el id manual
                    //buscarAutocompletarId(arr[i]['id']);
                });
                
                    a.appendChild(b);
                    resultado++;
                
            }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 38) { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 13) {
            /*If the ENTER key is pressed, prevent the form from being submitted,*/
            e.preventDefault();
            if (currentFocus > -1) {
                /*and simulate a click on the "active" item:*/
                if (x) x[currentFocus].click();
            }
        }
    });

    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-active");
    }

    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
        except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function(e) {
        closeAllLists(e.target);
    });

    
}

