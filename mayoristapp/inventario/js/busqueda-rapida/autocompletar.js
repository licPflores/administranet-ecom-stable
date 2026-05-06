/** 
 * Funcion de autocompletar el articulo y buscar
 * ---------------------------------------------
 */    
let search = []; //busqueda rapida
let arrayArticulos = []; //array de artuculos en la busqueda rapida
let busquedaActual = ''; //es igual al input hiden que contiene el ide de busqueda.
let inputBusquedaRapida = document.getElementById('producto');
let depositos;
let selectDepositos;
let selectCodigo;
let elementoContadorActivo = 'Unidad';
let origenElementoContadorActivo = 'Unidad';
let inputContador = 0;

let arrImageToGet = [];

// recargar datos
let reloadTipo;
let reloadTexto;
let reloadCodigo;

//variables para enviar datos al back
let unidadesToSend;
let displayToSend;
let bultoToSend;
let presentacionToSend;

let arrSelectMovimientos = [];

let selectTipoAjuste;
let tipoAjuste;
let motivoAjuste;

let textModoConteo;
let valorTextoUnidad;
let valorTextoDisplay;
let valorTextoBulto;

// ------------------------------------- BUSCAR DEPOSITOS ------------------------------------
// -------------------------------------------------------------------------------------------
//traigo depositos
$.ajax({
    url: "ajax/stock-backend.php",
    type: "GET",
    data: {
        listarDepositos: 1,
      },
    dataType: 'json',
    success: function(data) {
        // hacer tu logica ya teniendo la informacion del json
        depositos = data;

        let arrValues = [];

        createRow({
            target: '#select-depositos',
            id: '',
            class: 'g-3',
            col: [['col-dep-fecha', 6, 6], ['col-dep-saldo', 6, 6]],
        });

        createInput({
            target: '#col-dep-fecha',
            type: 'date',
            id: 'producto_time',
            class: '',
            placeholder: '',
            value: '',
            required: 'true',
            textLabel: 'Fecha:'
        });

        for (let i=0;i<depositos.length;i++) {
            const value = [depositos[i].id_deposito, depositos[i].NombreDeposito, depositos[i].defecto];
            arrValues.push(value);
        }

        createSelect({
            target: '#col-dep-saldo',
            id: 'lista-depositos',
            class: '',
            values: arrValues,
            textLabel: 'Depositos',
            opSelected: '--'
        });

        activaSelectDeposito();

        //colocar la fecha
        let field = document.querySelector('#producto_time');
        field.valueAsDate = new Date();
    },
    error: function(data) {
        // logica si falla la carga
        Swal.fire({
            title: 'Error!',
            html: data.mensaje,
            icon: 'error',
            confirmButtonText: 'Volver',
            allowOutsideClick: false
        });
    }
});

// ------------------------------------- BUSCAR ARTICULO -------------------------------------
// -------------------------------------------------------------------------------------------
$.ajax({
    url: "ajax/stock-backend.php",
    type: "GET",
    data: {
        autocompletarArticulo: 1,
    },
    dataType: 'json',
    success: function(data) {
        // hacer tu logica ya teniendo la informacion del json
        console.log(data);
        arrayArticulos = data;
        autocomplete(inputBusquedaRapida, arrayArticulos);
        buscarCodigo();
    },
    error: function(data) {
        // logica si falla la carga
        Swal.fire({
            title: 'Error!',
            html: data.mensaje,
            icon: 'error',
            confirmButtonText: 'Volver',
            allowOutsideClick: false
        });
    }
});

//busco un articulo
function getNewSearch(tipo, texto, codigo, codigoBarra) {

    //reloadTipo = tipo;
    //reloadTexto = texto;
    //reloadCodigo = codigo;

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            buscarArticulo: 1,
            tipoBusqueda: tipo, // texto, id o barra
            codigoBusco: codigo,
            textoBusco: texto,
            codBarraBusco: codigoBarra,
            idDeposito: selectDepositos,
        },
        dataType: 'json',
        beforeSend: function(){
            loading('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            search = data;
            console.log(search);
            if(search.msg=='ok'){
                if(search.tipoResultado=='unico'){

                    reloadTipo = 'id';
                    reloadTexto = null;
                    reloadCodigo = search.articulo.IDArt;
                    elementoContadorActivo = datosUsuario.permisos.tipo_cuenta_defecto;

                    if (document.getElementById('card-lista') && document.getElementById('card-lista')!=null) {
                        document.getElementById('card-lista').remove();
                    }
                    setCardIntro();
                    hideBuscador();
                }
                if(search.tipoResultado=='lista') {
                    if (document.getElementById('card-lista')) {
                        document.getElementById('card-lista').remove();
                    }
                    setCardListaProducto(search.listaArticulo, 3);
                    alturaListaProductos();
                }
            } else {
                Swal.fire({
                    title: 'No se encontaron Artículos!',
                    text: search.mensaje,
                    icon: 'info',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
            loading('off');
        },
        error: function(data) {
            // logica si falla la carga
            console.log(data);
            if(tipo=='id'){
                Swal.fire({
					title: 'Error!',
					html: 'Cámara no disponible.<br>Verificá los permisos y la conexión de la cámara.',
					icon: 'error',
					confirmButtonText: 'Volver',
					allowOutsideClick: false
				});
            }
            loading('off');
        }
    });
}

//busco un articulo con el scanner
function getNewSearchScan(tipo, texto, codigo, codigoBarra) {

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            buscarArticulo: 1,
            tipoBusqueda: tipo, // texto, id o barra
            codigoBusco: codigo,
            textoBusco: texto,
            codBarraBusco: codigoBarra,
            idDeposito: selectDepositos,
        },
        dataType: 'json',
        beforeSend: function(){
            loading('on');
        },
        success: function(data) {
            search = data;
            if(search.msg=='ok'){
                if(search.tipoResultado=='unico'){

                    reloadTipo = 'id';
                    reloadTexto = null;
                    reloadCodigo = search.articulo.IDArt;
                    elementoContadorActivo = datosUsuario.permisos.tipo_cuenta_defecto;

                    if (document.getElementById('card-lista') && document.getElementById('card-lista')!=null) {
                        document.getElementById('card-lista').remove();
                    }
					closeListaVideoButton();
                    //setCardIntroScan();
					setCardIntro('scan');
                }
                if(search.tipoResultado=='lista') {
                    if (document.getElementById('card-lista')) {
                        document.getElementById('card-lista').remove();
                    }
                    setCardListaProducto(search.listaArticulo, 3);
                    alturaListaProductos();
                }
            } else {
                Swal.fire({
                    title: 'No se encontaron Artículos!',
                    text: search.mensaje,
                    icon: 'info',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
            loading('off');
        },
        error: function(data) {
            console.log(data);
            if(tipo=='id'){
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
            loading('off');
        }
    });
}

// --------------------------------------- RENOVAR DATA --------------------------------------
// -------------------------------------------------------------------------------------------
//renuevo la informacion del articulo
function reloadSearch(tipo, texto, codigo, callback) {

    console.log('inicia la renovacion de datos');

    reloadTipo = tipo;
    reloadTexto = texto;
    reloadCodigo = codigo;

    console.log(reloadTipo+' '+reloadTexto+' '+reloadCodigo+' '+selectDepositos);

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            buscarArticulo: 1,
            tipoBusqueda: reloadTipo, // texto, id o barra
            codigoBusco: reloadCodigo,
            textoBusco: reloadTexto,
            codBarraBusco: null,
            idDeposito: selectDepositos,
        },
        dataType: 'json',
        beforeSend: function(){
            //loading('on');
            console.log('el renovar data ejecuta beforeSend')
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            search = data;
            console.log(search);
            /*if(search.msg=='ok'){
                setCardIntro()
                hideBuscador();
            }*/
            if (callback == 'datos') {
                reloadVista('content-form');
                setCardEditNombre();
            }
            if (callback == 'contador') {
                reloadVista('content-form');
                setCardProducto(depositos);
            }
            if (callback == 'codigo') {
                reloadVista('content-form');
                setCardEditCodigo();
            }
            if (callback == 'imagen') {
                reloadVista('content-form');
                setFormProducto();
            }
            loading('off');
            console.log('termina la renovacion de datos con exito');
        },
        error: function(data) {
            // logica si falla la carga
            console.log(data);
            /*if(tipo=='id'){
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }*/
            loading('off');
            console.log('termina la renovacion de datos con error');
        }
    });
}

// ------------------------------------- MOTIVOS DE AJUSTE -------------------------------------
// -------------------------------------------------------------------------------------------
// traigo array con la lista de motivos de movimiento de stock
$.ajax({
    url: "ajax/stock-backend.php",
    type: "GET",
    data: {
        listarTipoMovimiento: 1,
    },
    dataType: 'json',
    success: function(data) {
        // hacer tu logica ya teniendo la informacion del json
        console.log(data);
        for(let i=0; i<data.length; i++){
            let value;
            if(i == 0) {
                value = [data[i], data[i], 'Si'];
            } else {
                value = [data[i], data[i], 'No'];
            }
            arrSelectMovimientos.push(value);
        }
    },
    error: function(data) {
        console.log(data);
        // logica si falla la carga
        Swal.fire({
            title: 'Error!',
            //html: data.mensaje,
            html: data.responseText,
            icon: 'error',
            confirmButtonText: 'Volver',
            allowOutsideClick: false
        });
    }
});


// --------------------------------------- INVENTARIO ----------------------------------------
// -------------------------------------------------------------------------------------------
//guardo el inventario (altaMovimiento)
function saveInventario() {  

    fechaInventario = document.querySelector('#producto_time').value;
    let dateArray=fechaInventario.split('-');
    fechaInventario=`${dateArray[1]}/${dateArray[2]}/${dateArray[0]}`;

    IdArticuloInventario = document.querySelector('#id_producto').value;
    idDepositoInventario = document.querySelector('#id_deposito').value;
    saldoDepositoInventario = search.articulo.saldo;
    let cantidadToSend;

    switch (presentacionToSend) {
        case 'Unidad':
            cantidadToSend = unidadesToSend;
          break;
        case 'Display':
            cantidadToSend = displayToSend;
          break;
        case 'Bulto':
            cantidadToSend = bultoToSend;
          break;
    }

    motivoAjuste = document.getElementById('motivo-ajuste').value;
    //if (motivoAjuste == '') { motivoAjuste = '--'; }

    console.log('idArticulo: '+IdArticuloInventario)
    console.log('fecha: '+fechaInventario)
    console.log('presentacion: '+presentacionToSend)
    console.log('cantidadContada: '+cantidadToSend)
    console.log('cantidadMinimaContada: '+unidadesToSend)
    console.log('saldoDeposito: '+saldoDepositoInventario)
    console.log('idDeposito: '+idDepositoInventario)
    console.log('usaLote: No')
    console.log('unidad: '+unidadesToSend)
    console.log('display: '+displayToSend)
    console.log('bulto: '+bultoToSend)
    console.log('tipoAjuste: '+tipoAjuste)
    console.log('detalleAjuste: '+motivoAjuste)

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            altaMovimiento: 1,
            idArticulo: IdArticuloInventario,
            fecha: fechaInventario,
            presentacion: presentacionToSend,
            cantidadContada: cantidadToSend,
            cantidadMinimaContada: unidadesToSend,
            saldoDeposito: saldoDepositoInventario,
            idDeposito: idDepositoInventario,
            usaLote: 'No',
            unidad: unidadesToSend,
            display: displayToSend,
            bulto: bultoToSend,
            tipoAjuste: tipoAjuste,
            detalleAjuste: motivoAjuste,
            //idSaldoLote: '',
            //idLote: '',
            //saldoLote: '',
        },
        dataType: 'json',
        beforeSend: function(){
            loadingSave('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            console.log(data);
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'contador');
                    }
                });
            };
            if(data.msg == 'error') {
                console.log(data);
                console.log('erro al salvar inventari -> ' + data.responseText);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
            loadingSave('off');
        },
        error: function(data) {
            // logica si falla la carga
            if(data.msg == 'error') {
                console.log(data);
                console.log('erro al salvar inventari -> ' + data.responseText);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
            loadingSave('off');
            
        }
    })
}

// ---------------------------------------- IMAGENES -----------------------------------------
// -------------------------------------------------------------------------------------------
// hacer imagen principal
function saveFotoPrincipal(idFoto) {

    let IdArticuloInventario = search.articulo.IDArt;

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            fotoPrincipal: 1,
            idArticulo: IdArticuloInventario,
            idArticuloFoto: idFoto
        },
        dataType: 'json',
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            console.log(data);
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'imagen');
                    }
                });
                //loadingSave('off');
            };
            if(data.msg == 'error') {
                console.log(data);
                console.log('erro al salvar inventari -> ' + data.responseText);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
        },
        error: function(data) {
            console.log(data);
            // logica si falla la carga
            Swal.fire({
                title: 'Error!',
                html: data.mensaje,
                icon: 'error',
                confirmButtonText: 'Volver',
                allowOutsideClick: false
            });
            //loadingSafe('off');
        }
    });
}

//Guardo imagene nueva del inventario
function saveFoto(nuevaFoto) { 

    //console.log(nuevaFoto);
    let IdArticuloInventario = search.articulo.IDArt;

    var form_data = new FormData();  
    form_data.append('guardarImagen', 1);
    form_data.append('idArticulo', IdArticuloInventario);                
    form_data.append('imagenNueva', nuevaFoto);

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "POST",
        /*data: {
            guardarImagen: 1,
            idArticulo: IdArticuloInventario,
            imagenNueva: nuevaFoto
        },*/
        data: form_data,
        dataType: 'text',
        //data: formData,
        beforeSend: function(){
            //loadingSave('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            console.log(data);
            data = JSON.parse(data);
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'imagen');
                    }
                });
                //loadingSave('off');
            }
            if(data.msg == 'error') {
                console.log(data);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
        },
        cache: false,
        contentType: false,
        processData: false,
        error: function(data) {
            // logica si falla la carga
            console.log(data);
            Swal.fire({
                title: 'Error!',
                html: data.mensaje,
                icon: 'error',
                confirmButtonText: 'Volver',
                allowOutsideClick: false
            });
            //loadingSafe('off');
        }
    })
}

//Guardo imagene nueva del inventario
function deleteFoto(idFoto) { 

    let IdArticuloInventario = search.articulo.IDArt;

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            borrarImagen: 1,
            idArticulo: IdArticuloInventario,
            idArticuloFoto: idFoto
        },
        dataType: 'json',
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            console.log(data);
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'imagen');
                    }
                });
                //loadingSave('off');
            };
            if(data.msg == 'error') {
                console.log(data);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
        },
        error: function(data) {
            // logica si falla la carga
            console.log(data);
            Swal.fire({
                title: 'Error!',
                html: data.mensaje,
                icon: 'error',
                confirmButtonText: 'Volver',
                allowOutsideClick: false
            });
            //loadingSafe('off');
        }
    })
}

//Guardo imagenes del inventario (carga por array)
function saveImageInventario(ev) { 

    let IdArticuloInventario = search.articulo.IDArt;
    itemListSlide = document.getElementById('item-list-slide').getElementsByClassName('item');
    arrImageToGet = [];

    //arma la lista ordenada
    for(let i=0; i<itemListSlide.length; i++){
        let id = itemListSlide[i].id
        let esPrincipal;
        let idImg;
        img = itemListSlide[i].getElementsByClassName('image');
        if( itemListSlide[i].classList.contains('principal') ){ esPrincipal = 'si'; } else { esPrincipal = 'no'; }
        if( img[0].src ){ idImg = img[0].id; } else { idImg = 'no'; }
        arrImageToGet.push({id: idImg, estado: id, foto: img[0].src, principal: esPrincipal})
    }

    //console.log(arrImageToGet)

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "POST",
        data: {
            guardarImagen: 1,
            idArticulo: IdArticuloInventario,
            arrImage: arrImageToGet
        },
        dataType: 'json',
        //dataType: 'html',
        beforeSend: function(){
            //loadingSave('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            console.log(data);
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'imagen');
                    }
                });
                loadingSave('off');
            };
            if(data.msg == 'error') {
                console.log(data);
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
        },
        cache: false,
        //contentType: false,
        //processData: false,
        error: function(data) {
            // logica si falla la carga
            console.log(data);
            Swal.fire({
                title: 'Error!',
                html: data.mensaje,
                icon: 'error',
                confirmButtonText: 'Volver',
                allowOutsideClick: false
            });
            loadingSafe('off');
        }
    })
}


// ------------------------------------- CODIGO DE BARRA -------------------------------------
// -------------------------------------------------------------------------------------------
// pone el codigo de barra escaneado en el input
function getCodigoNuevo(idArticulo, tipoCodigo, codigo) {
    let codigoManual = document.getElementById('codigo-manual');
    codigoManual.parentElement.classList.add('activo');
    codigoManual.value  = codigo;

    anime({
        targets: '#content-general',
        translateX: ['-200%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    })
}

// guardo el codigo de barra
function saveCode() {

    let codigoSelect = document.getElementById('codigo-select');
    let codigoManual = document.getElementById('codigo-manual');
    let IdArticuloInventario = search.articulo.IDArt;

    //valida si es un numero o no
    if(isNum(codigoManual.value) == false) {
        //console.log(isNum(codigoManual));
        createValidation({
            target: '#codigo-manual',
            type: 'invalid',
            html: "El valor " + codigoManual.value + " no es un número"
        })
    } else {
        if(codigoManual.classList.contains('is-invalid')) {
            codigoManual.classList.remove('is-invalid');
            borrarValidation = document.getElementById('codigo-manualFeedback');
            while (borrarValidation.firstChild) {
                borrarValidation.removeChild(borrarValidation.firstChild);
            }
        }
    }

    if(codigoSelect.value!='--' && codigoManual.value!='') {

        console.log('ejecuto el ajax con guardarCodigoBarra: 1');

        $.ajax({
            url: "ajax/stock-backend.php",
            type: "GET",
            data: {
                guardarCodigoBarra: 1,
                idArticulo: IdArticuloInventario,
                tipoCodigo: codigoSelect.value,
                codBarra: codigoManual.value
            },
            dataType: 'json',
            beforeSend: function(){
                loadingSave('on');
            },
            success: function(data) {
                // hacer tu logica ya teniendo la informacion del json
                console.log(data);
                if(data.msg == 'ok') {
                    Swal.fire({
                        title: 'Exito!',
                        html: data.mensaje,
                        icon: 'success',
                        confirmButtonText: 'Volver',
                        allowOutsideClick: false
                    }).then(resultado => {
                        if (resultado.value) {
                            reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'codigo');
                        }
                    });
                };
                if(data.msg == 'duplicado') {
                    Swal.fire({
                        title: 'Codigo duplicado!',
                        html: data.mensaje,
                        icon: 'warning',
                        confirmButtonText: 'volver',
                        allowOutsideClick: false
                    }).then(resultado => {
                        if (resultado.value) {
                            activarVaciarCodigo(data.mensaje);
                        }
                    });
                    loadingSave('off');
                };
                if(data.msg == 'error') {
                    console.log(data);
                    Swal.fire({
                        title: 'Error!',
                        html: data.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Volver',
                        allowOutsideClick: false
                    });
                }
            },
            error: function(data) {
                // logica si falla la carga
                console.log(data)
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
                loadingSave('off')
            }
        })
    } else {
        console.log('no entro a guardar');
    }
}

// vacia el codigo de barra para guardar uno nuevo
function vaciarCodigo() {

    let IdArticuloInventario = search.articulo.IDArt;
    let codigoSelect = document.getElementById('codigo-select');

    console.log('se ejecuta el ajax para vaciar codigo -> vaciarCodigoBarra: 1, idArticulo: '+IdArticuloInventario+', tipoCodigo: '+codigoSelect.value);

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "GET",
        data: {
            vaciarCodigoBarra: 1,
            idArticulo: IdArticuloInventario,
            tipoCodigo: codigoSelect.value,
        },
        dataType: 'json',
        beforeSend: function(){
            //loading('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
        },
        error: function(data) {
            // logica si falla la carga
            console.log('error ajax para vaciar codigo');
            console.log(data);
            //loading('off');
        }
    });
}

// ------------------------------------- DATOS DEL PRODUCTO ----------------------------------
// -------------------------------------------------------------------------------------------
// guardo el nombre
function saveName() {

    let IdArticuloInventario = search.articulo.IDArt;
    let inputNombreArticulo = document.getElementById('nombre-articulo').value;
    let inputNombreArticuloEComm = document.getElementById('nombre-ecomm').value;
    let inputDetalle = document.getElementById('detalle-web').value;

    console.log('nombre: '+inputNombreArticulo+
                ' nombre articulo: '+inputNombreArticuloEComm+
                ' detalle: '+inputDetalle);

    $.ajax({
        url: "ajax/stock-backend.php",
        type: "POST",
        data: {
            guardarDatosProducto: 1,
            idArticulo: IdArticuloInventario,
            nombreArticulo: inputNombreArticulo,
            nombreArticuloEComm: inputNombreArticuloEComm,
            detalle: inputDetalle
        },
        dataType: 'json',
        beforeSend: function(){
            loading('on');
        },
        success: function(data) {
            // hacer tu logica ya teniendo la informacion del json
            if(data.msg == 'ok') {
                Swal.fire({
                    title: 'Exito!',
                    html: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                }).then(resultado => {
                    if (resultado.value) {
                        reloadSearch(reloadTipo, reloadTexto, reloadCodigo, 'datos');
                    }
                });
                loadingSave('off');
            };
            if(data.msg == 'error') {
                Swal.fire({
                    title: 'Error!',
                    html: data.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Volver',
                    allowOutsideClick: false
                });
            }
        },
        error: function(data) {
            // logica si falla la carga
            Swal.fire({
                title: 'Error!',
                html: data.mensaje,
                icon: 'error',
                confirmButtonText: 'Volver',
                allowOutsideClick: false
            });
            loadingSave('off');
        }
    })
}

// -------------------------------------- AUTOCOMPLETAR --------------------------------------
// -------------------------------------------------------------------------------------------
function autocomplete(inp, arr) {

    var currentFocus = 0;

    inp.addEventListener("input", function(e) {
        var val = this.value;
        var maxAmostrar = 0;
    
        closeAllLists();

        //console.log('click')
    
        if (!val) { return false; } // si está vacío
        if (val && val.length < 2) { return false; } // si tiene menos de dos caracteres
    
        currentFocus = -1;
    
        var a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");
    
        this.parentNode.appendChild(a);
    
        var palabra = val.toUpperCase();

        let palabraKey = palabra.split(' ');
        palabraKey = palabraKey.filter(pKey => pKey != '');
    
        for (var i = 0; i < arr.length; i++) {
            var producto = arr[i];
            var id = producto.id.toString();
            if(producto.idManual){ var idmanual = producto.idManual.toString(); }
            var nombre = producto.articulo.toUpperCase();
            var cod = producto.id.toString();
            var imageUrl = producto.url;

            //let nombreKey = nombre.split(' ');
        
            // Verificar si hay coincidencias en id, nombre e idmanual
            if (buscarPalabras(nombre, palabraKey) || id.includes(palabra) || idmanual.includes(palabra)) {
                var div = document.createElement("DIV");
                div.className = 'item';
                div.setAttribute("id", 'item_' + cod);

                var strong = document.createElement("SPAN");

                var divImage = document.createElement("DIV");
                divImage.className = 'image';

                var xImage = document.createElement("IMG");

                if (imageUrl == null) {
                    xImage.setAttribute("src", "img/no_image.jpg");
                } else {
                    xImage.setAttribute("src", imageUrl);
                }

                xImage.setAttribute("width", "50");
                xImage.setAttribute("height", "50");
                xImage.setAttribute("alt", nombre);
                divImage.appendChild(xImage);

                // Resaltar las letras coincidentes en el nombre del producto
                var res = nombre;
                for (const palabra of palabraKey) {
                    if (nombre.indexOf(palabra) !== -1) {
                        var res = res.replace(new RegExp(palabra, "gi"), function(x) {
                            return '<strong>' + x.toUpperCase() + '</strong>';
                        })
                    }
                }
        
                strong.innerHTML = res;
                div.appendChild(divImage);
                div.appendChild(strong);
                div.innerHTML += "<input type='hidden' value='" + producto.id + "'>";
        
                div.addEventListener("click", function(e) {
                    
                    var lineaEncontrada = this.getElementsByTagName("input")[0].value;

                    const findElement = (array, id) => {
                        for (let i = 0; i < array.length; i++) {
                          const element = array[i];
                          if (element.id === id) {
                            return element;
                          }
                        }
                        return -1;
                    }

                    var nombreProducto = findElement(arr, lineaEncontrada);

                    inputBusquedaRapida.value = nombreProducto.articulo;
            
                    busquedaActual = nombreProducto.id;

                    getNewSearch('id', null, nombreProducto.id, null);

                    inputBusquedaRapida.disabled = true;

                    closeAllLists();
                    //hideBuscador();
                })
        
                maxAmostrar++;
                
                if (maxAmostrar < 20) {
                    a.appendChild(div);
                }
            }
        }
    
        var productoWidth = this.offsetWidth;
        a.style.width = productoWidth + "px";
    
        // Agrega las clases 'activo' para mostrar el resultado de búsqueda
        inputBusquedaRapida.classList.add('activo');
        document.getElementById('botonBuscar').classList.add('activo');
    
        // Realiza una animación de entrada
        anime({
            targets: '.autocomplete-items',
            height: 'max-content',
            easing: 'easeInOutQuad'
        })
    })
    //* ---- fin funcion

    //sin uso ----------------------------------
    function compararPalabras(lista, palabras) {
        let flag = true;
        for(i=0; i<palabras.length; i++) {
            if(!lista.includes(palabras[i])) {
                flag = false;
            }
        }
        return flag;
    }

    //busca palabras del input en el nombre
    function buscarPalabras(texto, palabras) {
        let coincidencias = [];
        let flag;
        
        for (const palabra of palabras) {
          if (texto.indexOf(palabra) !== -1) {
            coincidencias.push(palabra);
          }
        }

        if (coincidencias.length === palabras.length) {
            flag = true;
        } else {
            flag = false;
        }
      
        return flag;
    }

    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function(e) {
        var x = document.getElementById(this.id + "autocomplete-list");
        //console.log(currentFocus);
        if (x) x = x.getElementsByClassName("item");
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
    })

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
        inputBusquedaRapida.classList.remove('activo');
        document.getElementById('botonBuscar').classList.remove('activo');
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    })

    //enter en el input #producto - funciona con el codigo de barra
    inputBusquedaRapida.addEventListener("keydown", function(e) {
        let keycode = (e.keyCode ? e.keyCode : e.which);

        if ( 
            keycode == '13' && //si hizo enter en el input
            ///^([0-9])*$/.test(inputBusquedaRapida.value) && // y es un numero valido
            document.getElementById("autocomplete-group").classList.contains("active-code") // y esta activa la busqueda por codigo
        ) {
            //getNewSearch('texto', inputBusquedaRapida.value, null, null);
            getNewSearch('barra', null, null, inputBusquedaRapida.value);
            //alert("capturo el enter con el valor: "+inputBusquedaRapida.value);
            e.preventDefault();
            return false;
        }

    })
}

// -------------------------------- BUSQUEDA CODIGO DE BARRA ---------------------------------
// -------------------------------------------------------------------------------------------
function buscarCodigo() {
    inputBusquedaRapida.focus();

    if (document.getElementById("autocomplete-group").classList.contains("active-rapido")) {
        document.getElementById("autocomplete-group").classList.toggle('active-rapido');
    }

    document.getElementById("autocomplete-group").classList.add('active-code');
    document.querySelector('#botonBuscar .bi-search').classList.add('d-none');
    document.querySelector('#botonBuscar .bi-upc-scan').classList.remove('d-none');

    if (document.getElementById("autocomplete-group").classList.contains("active-code")) {
        inputBusquedaRapida.type = 'number';
        inputBusquedaRapida.setAttribute('inputmode', 'numeric' );
        inputBusquedaRapida.setAttribute('pattern', '[0-9]' );
        inputBusquedaRapida.setAttribute('step', '1' );
        inputBusquedaRapida.setAttribute('placeholder', 'Buscar por código de barra' );
        document.getElementById("btn-buscar-codigo").classList.toggle('btn-activo');
    } 
}

function buscarRapido() {
    inputBusquedaRapida.focus();

    if (document.getElementById("autocomplete-group").classList.contains("active-code")) {
        document.getElementById("autocomplete-group").classList.toggle('active-code');
    }

    document.getElementById("autocomplete-group").classList.add('active-rapido');
    document.querySelector('#botonBuscar .bi-search').classList.remove('d-none');
    document.querySelector('#botonBuscar .bi-upc-scan').classList.add('d-none');

    if (document.getElementById("autocomplete-group").classList.contains("active-rapido")) {
        inputBusquedaRapida.type = 'search';
        inputBusquedaRapida.setAttribute('inputmode', '' );
        inputBusquedaRapida.setAttribute('pattern', '' );
        inputBusquedaRapida.setAttribute('step', '' );
        inputBusquedaRapida.setAttribute('placeholder', 'Buscar por nombre o Id' );
        document.getElementById("btn-buscar-rapido").classList.toggle('btn-activo');

        if(inputBusquedaRapida.classList.contains('is-invalid')) {
            inputBusquedaRapida.classList.remove('is-invalid');
            borrarValidation = document.getElementById('productoFeedback');
            while (borrarValidation.firstChild) {
                borrarValidation.removeChild(borrarValidation.firstChild);
            }
        }
    }
}

// al escribir en el input #producto valida que sea un numero.
$('#producto').bind('change keydown',function (){
    //validacion
    if(!/^([0-9])*$/.test(inputBusquedaRapida.value) && document.getElementById("autocomplete-group").classList.contains("active-code")) {
        createValidation({
            target: '#producto',
            type: 'invalid',
            html: "El valor " + inputBusquedaRapida.value + " no es un número"
        })
    } else {
        if(inputBusquedaRapida.classList.contains('is-invalid')) {
            inputBusquedaRapida.classList.remove('is-invalid');
            borrarValidation = document.getElementById('productoFeedback');
            while (borrarValidation.firstChild) {
                borrarValidation.removeChild(borrarValidation.firstChild);
            }
        }
    }
})

// ---------------------------------- FUNCIONES DE SOPORTE -----------------------------------
// -------------------------------------------------------------------------------------------
// reload informacion de una vista
function reloadVista(vista) {
    let borrarVista = document.getElementById(vista);

    textModoConteo = '0';
    valorTextoUnidad = '0';
    valorTextoDisplay = '0';
    valorTextoBulto = '0';

    while (borrarVista.firstChild) {
        borrarVista.removeChild(borrarVista.firstChild);
    }
}

// boton buscar
async function botonBuscar() {
    let input = inputBusquedaRapida.value;

    if (!input) { return false; } // si esta vasio 
    if (input && input.length<2 ) { return false; } //si tiene un solo caracter
    if ($('#content-info').html()) { // si ya tiene una busqueda abierta
        createAlert({
            target: '#alert-content',
            typeAler: 'danger', //success, danger, info
            strong: 'Error',
            text: 'Aun estas en una búsqueda activa, cerrá tu búsqueda actual para realizar una nueva.'
        })
        return false;
    }

    if (isNum(input) && document.getElementById("autocomplete-group").classList.contains("active-code")) {
        // El valor es un número - genera la busqueda
        getNewSearch('barra', null, null, input);
    } else {
        // El valor no es un número - genera la busqueda
        getNewSearch('texto', input, null, null);
    }
}

// close y cancel usan la misma funcion
function closeForm() {
    let borrarForm = document.getElementById('content-form');
    let btnCerrar = document.querySelectorAll('#button-cerrar');
    for (var i=0;i<btnCerrar.length;i++) {
        btnCerrar[i].disabled = false;
    }

    if(document.getElementById('conteo-actual')) {
        document.getElementById('conteo-actual').value = '';
    }

    textModoConteo = '0';
    valorTextoUnidad = '0';
    valorTextoDisplay = '0';
    valorTextoBulto = '0';
    inputContador = '0';

    anime({
        targets: '#content-general',
        translateX: ['-100%', '0%'],
        easing: 'easeInOutQuad',
        duration: 800,
        changeComplete: function(anim) {
            while (borrarForm.firstChild) {
                borrarForm.removeChild(borrarForm.firstChild);
            }
        }
    })
}

// cierra la primer pantalla menu de un producto.
function closeSearch() {
    let borrarDiv = document.getElementById('content-intro');
    inputBusquedaRapida.value = '';
    inputBusquedaRapida.disabled = false;
    while (borrarDiv.firstChild) {
        borrarDiv.removeChild(borrarDiv.firstChild);
    }
    hideBuscador();
    buscarCodigo();
}

// cierra la vista del producto.
function closeVista() {
    let borrarDiv = document.getElementById('content-form');
    inputBusquedaRapida.value = '';
    inputBusquedaRapida.disabled = false;
    while (borrarDiv.firstChild) {
        borrarDiv.removeChild(borrarDiv.firstChild);
    }
    hideBuscador();
}

// Oculta el buscador
function hideBuscador() {
    $('#select-depositos').toggleClass('d-none');
    $('#buscadores').toggleClass('d-none');
}

function closeLista() {
    if (document.getElementById('card-lista')) {
        document.getElementById('card-lista').remove();
    }
}

function closeListaVideoButton() {
    if (document.getElementById('content-video-list-button')) {
        document.getElementById('content-video-list-button').remove();
    }
}

//-------------- funciones derivadas de la creacion de objetos ----------
//activa los select de deposito
function activaSelectDeposito() {
    let buscadores = document.getElementById('buscadores');
    let listaDepositos = document.getElementById('lista-depositos');

    if( listaDepositos.options[listaDepositos.selectedIndex].value !='--' ) { 
        buscadores.classList.remove('d-none'); 
        selectDepositos = listaDepositos.options[listaDepositos.selectedIndex].value;
    }

    $("#lista-depositos").on('change', function() {
        selectDepositos = $('option:selected', this).val();
        const isNumber = n => $.isNumeric(n);
        if (isNumber(selectDepositos)) {
            buscadores.classList.remove('d-none');
        } else {
            buscadores.classList.add('d-none'); 
        }
    })
}

//activa los select de los tipos de ajustes
function activaSelectAjustes() {

    var select = document.getElementById('tipo-ajuste');
    tipoAjuste = select.options[select.selectedIndex].value;
    console.log(tipoAjuste);

    $("#tipo-ajuste").on('change', function() {
        selectTipoAjuste = $('option:selected', this).val();
        tipoAjuste = selectTipoAjuste;
    })
}

// Activa los input de rango
function showVal() {
    $("input[type=range]").on("change input", function() {
        $("[name=range_"+ this.id +"]").val($(this).val())
    })
}

//Muesta datos de conteo y los prepara para guardarlos
function showRadioVal(valorDelBoton) { //Unidad, Display, Bulto
    let valuetoLowerCase = valorDelBoton.toLowerCase();

    // coloco la clase que activa
    let bodyContador = document.querySelector('#body-2');
    if(bodyContador.className != 'card-body '+valuetoLowerCase) {
        bodyContador.className = '';
        bodyContador.classList.add('card-body', valuetoLowerCase);
    }

    // coloco el active y check en los botones
    let arrayContadores = ['unidad', 'display', 'bulto'];
    let iconCheck = document.createElement('i');
    iconCheck.setAttribute('class','bi bi-check-lg');
    let iconCheckDel = document.querySelector('.bi.bi-check-lg');
    for (let i = 0; i < arrayContadores.length; i++) {
        if (arrayContadores[i] == valuetoLowerCase) {
            document.querySelector('#radio_'+valuetoLowerCase).classList.add('active');
            if( !document.querySelector('#radio_'+valuetoLowerCase).contains(iconCheckDel)){ document.querySelector('#radio_'+valuetoLowerCase).appendChild(iconCheck) }
        } else {
            document.querySelector('#radio_'+arrayContadores[i]).classList.remove('active');
            if( document.querySelector('#radio_'+arrayContadores[i]).contains(iconCheckDel)){ document.querySelector('#radio_'+arrayContadores[i]).removeChild(iconCheckDel) }
        }
    }

    textModoConteo = document.querySelector('#text-modo-conteo');
    valorTextoUnidad = document.querySelector('#valor-texto-unidad');
    valorTextoDisplay = document.querySelector('#valor-texto-display');
    valorTextoBulto = document.querySelector('#valor-texto-bulto');

    let unidadMinimaDisplay = Number(search.presentacion[1].cantidadUnidadMinima);
    let unidadMinimaBulto = Number(search.presentacion[2].cantidadUnidadMinima);

    const saldoValue = Number(search.articulo.saldo);
    let inputSaldo = document.querySelector('#producto_saldo');
    let lebelSaldo = document.querySelector('#col-saldo label');

    let textoAclaracion;

    //origenElementoContadorActivo -> de donde vengo unidad/display/bulto 
    elementoContadorActivo = valorDelBoton; //a donde voy unidad/display/bulto 

    //muestro datos
    switch (elementoContadorActivo) {
        case 'Unidad':
                //textoAclaracion = '<p>1 '+search.presentacion[0].nombrePresentacion+' contiene 1 unidad minima</p>';
                textoAclaracion = '<p>1 <strong>'+search.presentacion[0].nombrePresentacion+'</strong> = 1 unidad minima</p>';
                textModoConteo.innerHTML = textoAclaracion;

                valorTextoUnidad.innerHTML = inputContador;
                valorTextoDisplay.innerHTML = '0';
                valorTextoBulto.innerHTML = '0';

                unidadesToSend = inputContador;
                displayToSend = '0';
                bultoToSend = '0';
                presentacionToSend = elementoContadorActivo;

                if(!inputSaldo.classList.contains('Unidad')) { inputSaldo.classList.add('Unidad'); }
                if(inputSaldo.classList.contains('Display')) { inputSaldo.classList.remove('Display'); }
                if(inputSaldo.classList.contains('Bulto')) { inputSaldo.classList.remove('Bulto'); }
                inputSaldo.value = saldoValue;
                lebelSaldo.innerHTML = 'Saldo en Unidades';

                //declaro 
                origenElementoContadorActivo = valorDelBoton;
            break;

        case 'Display':
                //textoAclaracion = '<p>1 '+search.presentacion[1].nombrePresentacion+' contiene '+unidadMinimaDisplay+' unidades minimas</p>';
                textoAclaracion = '<p>1 <strong>'+search.presentacion[1].nombrePresentacion+'</strong> = '+unidadMinimaDisplay+' unidades minimas</p>';
                textModoConteo.innerHTML = textoAclaracion;

                valorTextoUnidad.innerHTML = inputContador*unidadMinimaDisplay;
                valorTextoDisplay.innerHTML = inputContador;
                valorTextoBulto.innerHTML = '0';

                unidadesToSend = inputContador*unidadMinimaDisplay;
                displayToSend = inputContador;
                bultoToSend = '0';
                presentacionToSend = elementoContadorActivo;

                if(inputSaldo.classList.contains('Unidad')) { inputSaldo.classList.remove('Unidad'); }
                if(!inputSaldo.classList.contains('Display')) { inputSaldo.classList.add('Display'); }
                if(inputSaldo.classList.contains('Bulto')) { inputSaldo.classList.remove('Bulto'); }
                inputSaldo.value = saldoValue/unidadMinimaDisplay;
                lebelSaldo.innerHTML = 'Saldo en Display';
            break;

        case 'Bulto':
                //textoAclaracion = '<p>1 '+search.presentacion[2].nombrePresentacion+' contiene '+unidadMinimaBulto/unidadMinimaDisplay+' Display de '+unidadMinimaDisplay+' unidades c/uno con un total de '+unidadMinimaBulto+' unidades minimas</p>';
                textoAclaracion = '<p>1 <strong>'+search.presentacion[2].nombrePresentacion+'</strong> = '+unidadMinimaBulto/unidadMinimaDisplay+' Display '+unidadMinimaDisplay+' uni. c/uno, '+unidadMinimaBulto+' unidades minimas</p>';
                textModoConteo.innerHTML = textoAclaracion;

                valorTextoUnidad.innerHTML = inputContador*unidadMinimaBulto;
                valorTextoDisplay.innerHTML = (unidadMinimaBulto/unidadMinimaDisplay)*inputContador;
                valorTextoBulto.innerHTML = inputContador;

                unidadesToSend = inputContador*unidadMinimaBulto;
                displayToSend = (unidadMinimaBulto/unidadMinimaDisplay)*inputContador;
                bultoToSend = inputContador;
                presentacionToSend = elementoContadorActivo;

                if(inputSaldo.classList.contains('Unidad')) { inputSaldo.classList.remove('Unidad'); }
                if(inputSaldo.classList.contains('Display')) { inputSaldo.classList.remove('Display'); }
                if(!inputSaldo.classList.contains('Bulto')) { inputSaldo.classList.add('Bulto'); }
                inputSaldo.value = saldoValue/unidadMinimaBulto;
                lebelSaldo.innerHTML = 'Saldo en Bultos';
            break;
    }
}

//validacion cuando haces inventario
function startConteo(value) { // teclas

    inputContador = value;

    $('#conteo-actual').bind('change keydown',function (){

        //validacion
        if(!/^([0-9])*$/.test(inputContador)) {
            createValidation({
                target: '#conteo-actual',
                type: 'invalid',
                html: "El valor " + inputContador + " no es un número"
            })
        } else {
            if(document.getElementById('conteo-actual').classList.contains('is-invalid')) {
                document.getElementById('conteo-actual').classList.remove('is-invalid');
                borrarValidation = document.getElementById('conteo-actualFeedback');
                while (borrarValidation.firstChild) {
                    borrarValidation.removeChild(borrarValidation.firstChild);
                }
            }
        }

    })

    showRadioVal(elementoContadorActivo);
}

// Activa la captura del enter en el contador
function getEnterKeyContador() {
    
    $('#conteo-actual').keypress(function(e) {

        let keycode = (e.keyCode ? e.keyCode : e.which);

        if ( keycode == '13' ) {
            //alert("capturo el enter en el contador y su valor es valor: "+inputContador);
            saveInventario();
            e.preventDefault();
            return false;
        }
    })
}

//situa el cursor sobre el input de codigo de barra
function setCodigoManual() {
    let inputCodigoManual = document.getElementById('codigo-manual');
    inputCodigoManual.focus();
}

// Activa la captura del enter en el input del codigo manual
function getEnterKeyCodigoManua() {

    let inputCodigoManual = document.getElementById('codigo-manual');
    
    $('#codigo-manual').keypress(function(e) {

        let keycode = (e.keyCode ? e.keyCode : e.which);

        if ( keycode == '13' ) {
            //alert("capturo el enter y su valor es valor: "+inputCodigoManual);
            saveCode();
            e.preventDefault();
            return false;
        }
    })
}

//muestra mensajes y boton para vaciar codigo de barra
function activarVaciarCodigo(mensaje) {

    let divContentLectorCodigo = document.getElementById('div-content-lector-codigo');
    let contentParent = document.getElementById('content-lector-codigo');

    if(mensaje == false) { 
        if(divContentLectorCodigo.classList.contains('vaciar')) { divContentLectorCodigo.classList.remove('vaciar'); }
        if (document.getElementById('mensaje-duplicado')) { document.getElementById('mensaje-duplicado').remove(); }
    } else {
        divContentLectorCodigo.classList.add('vaciar');

        let newMensage = document.createElement("div");
        newMensage.setAttribute("id", "mensaje-duplicado");
        newMensage.innerHTML = mensaje;

        contentParent.insertBefore(newMensage, divContentLectorCodigo);
    }
}

//incluye los botones de las imagenes
function includeImageButton() {

    let botonesHacerPrincipal = document.getElementsByClassName('hacer-principal');
    let listaImgPrincipal = document.querySelectorAll('#item-list-slide .item');

    for (var i=0;i<botonesHacerPrincipal.length;i++) {
        botonesHacerPrincipal[i].onclick = function() {

            for (var x=0;x<listaImgPrincipal.length;x++) {
                listaImgPrincipal[x].classList.remove('principal');
            }

            this.parentNode.parentNode.classList.add('principal');
        }
    }

    let eliminarImgForm = document.getElementsByClassName('eliminar-img-form');

    for (var i=0;i<eliminarImgForm.length;i++) {
        eliminarImgForm[i].onclick = function() {

            this.parentNode.parentNode.remove()

        }
    }
}

//activa el select para selecionar el tipo de codigo a modificar
function activaSelectCodigo() {
    let lectorCodigo = document.getElementById('content-lector-codigo');
    let codigoManual = document.getElementById('codigo-manual');
    let labelCodigoManual = document.getElementById('label-codigo-manual');
    let arrCodigos = [
                        search.articulo.NroCodBarra, 
                        search.articulo.NroCodBarraF, 
                        search.articulo.nro_cod_barra_bulto, 
                        search.articulo.nro_cod_barra_display
                    ];

    function armarTexto(texto) {
        let sendTexto
        if (texto != '') { sendTexto = texto; } else { sendTexto = 'sin datos';}
        return sendTexto;
    }

    $("#codigo-select").on('change', function() {
        selectCodigo = $('option:selected', this).val();
        const isNumber = n => $.isNumeric(n);

        if (selectCodigo) {
            lectorCodigo.classList.remove('d-none');

            switch (selectCodigo) {
                case 'NroCodBarra':
                    activarVaciarCodigo(false);
                    codigoManual.value = search.articulo.NroCodBarra;
                    codigoManual.setAttribute('placeholder','Nuevo código de barra');
                    document.getElementById('label-codigo-manual').innerHTML = 'Código actual: <strong>'+armarTexto(search.articulo.NroCodBarra)+'</strong>';
                  break;
                case 'NroCodBarraF':
                    activarVaciarCodigo(false);
                    codigoManual.value = search.articulo.NroCodBarraF;
                    codigoManual.setAttribute('placeholder','Nuevo código de barra');
                    document.getElementById('label-codigo-manual').innerHTML = 'Código actual: <strong>'+armarTexto(search.articulo.NroCodBarraF)+'</strong>';
                  break;
                case 'nro_cod_barra_bulto':
                    activarVaciarCodigo(false);
                    codigoManual.value = search.articulo.nro_cod_barra_bulto;
                    codigoManual.setAttribute('placeholder','Nuevo código de barra');
                    document.getElementById('label-codigo-manual').innerHTML = 'Código actual: <strong>'+armarTexto(search.articulo.nro_cod_barra_bulto)+'</strong>';
                  break;
                case 'nro_cod_barra_display':
                    activarVaciarCodigo(false);
                    codigoManual.value = search.articulo.nro_cod_barra_display;
                    codigoManual.setAttribute('placeholder','Nuevo código de barra');
                    document.getElementById('label-codigo-manual').innerHTML = 'Código actual: <strong>'+armarTexto(search.articulo.nro_cod_barra_display)+'</strong>';
                  break;
            }

            document.getElementById('codigo-manual').focus();
            document.getElementById('codigo-manual').select();

        } else {
            lectorCodigo.classList.add('d-none');
            codigoManual.value = '';
        }
    })

    codigoManual.addEventListener('keyup',()=>{

        let codigoManualValue = codigoManual.value;

        /*if(codigoManualValue!='') {
            if(codigoManualValue == arrCodigos[selectCodigo-1]) {
                codigoManual.parentElement.classList.remove('activo');
            } else {
                codigoManual.parentElement.classList.add('activo');
            }
        } else {
            codigoManual.parentElement.classList.remove('activo');
        }*/
    })
}

// inicia el slide de imagenes
function startSlideArticulo() {
    $('.slick-images').slick({
        autoplay: true,
        infinite: true,
        dots: false,
        slidesToShow: 1,
        slidesToScroll: 1
    })
}

// hace girar el icono de loading en boton buscar
var loadingButtonBuscar = anime({
    targets: '#botonBuscar .bi-arrow-clockwise',
    rotate: 360,
    easing: 'linear',
    duration: 1800,
    autoplay: false,
    loop: true
})

//controla el icono de loading en boton buscar
function loading(value) {

    let iconClock = document.querySelector('#botonBuscar .bi-arrow-clockwise');
    let iconSearch = document.querySelector('#botonBuscar .bi-search');
    let iconScan = document.querySelector('#botonBuscar .bi-upc-scan');
    let btnBuscar = document.querySelector('#botonBuscar');

    switch (value) {
        case 'on':
                loadingButtonBuscar.play();
                iconClock.classList.remove('d-none');
                iconSearch.classList.add('d-none');
                iconScan.classList.add('d-none');
                btnBuscar.disabled = true;
            break;

        case 'off':
                loadingButtonBuscar.pause();
                iconClock.classList.add('d-none');
                if (document.getElementById("autocomplete-group").classList.contains("active-code")) {
                    iconSearch.classList.add('d-none');
                    iconScan.classList.remove('d-none');
                }
            
                if (document.getElementById("autocomplete-group").classList.contains("active-rapido")) {
                    iconSearch.classList.remove('d-none');
                    iconScan.classList.add('d-none');
                }
                btnBuscar.disabled = false;
            break;
    }
}

// hace girar el icono de loading en boton guardar
var loadingButton = anime({
    targets: '#button-guardar .bi-arrow-clockwise',
    rotate: 360,
    easing: 'linear',
    duration: 1800,
    autoplay: false,
    loop: true
})

//controla el icono de loading en boton guardar
function loadingSave(value) {

    let iconClock = document.querySelector('#button-guardar .bi-arrow-clockwise');
    let iconGuardar = document.querySelector('#button-guardar .bi-save2');
    let txtGuardar = document.querySelector('#button-guardar .txt');
    let btnGuardar = document.querySelector('#button-guardar');
    let btnCerrar = document.querySelectorAll('#button-cerrar');

    switch (value) {
        case 'on':
                loadingButton.play();
                iconClock.classList.remove('d-none');
                iconGuardar.classList.add('d-none');
                txtGuardar.innerHTML = 'Espere...';
                btnGuardar.disabled = true;
                for (var i=0;i<btnCerrar.length;i++) {
                    btnCerrar[i].disabled = true;
                }
            break;

        case 'off':
                loadingButton.pause();
                iconClock.classList.add('d-none');
                iconGuardar.classList.remove('d-none');
                txtGuardar.innerHTML = 'Guardar';
                btnGuardar.disabled = false;
                for (var i=0;i<btnCerrar.length;i++) {
                    btnCerrar[i].disabled = false;
                }
            break;
    }
}

//Altura de la lista de productos
function alturaListaProductos() {
    let alturaHeader = document.getElementById('header-web').getBoundingClientRect().height;
    let alturaBuscador = document.getElementById('content-buscador').getBoundingClientRect().height;
    let altoPantalla = window.innerHeight;

    let alturaListaProductos = altoPantalla - (alturaHeader + alturaBuscador + 24);

    document.getElementById('card-lista').style.height = alturaListaProductos+'px';

    console.log(alturaListaProductos);
}

// Función para mezclar dos colores por porcentaje
function mezclarColores(color1, color2, porcentaje) {
    // Validar que el porcentaje esté en el rango de 0 a 100
    if (porcentaje < 0 || porcentaje > 100) {
      //throw new Error("El porcentaje debe estar entre 0 y 100");
    }
  
    // Convertir el porcentaje a un valor entre 0 y 1
    porcentaje /= 100;
  
    // Extraer los componentes de color de cada color
    const r1 = parseInt(color1.slice(1, 3), 16);
    const g1 = parseInt(color1.slice(3, 5), 16);
    const b1 = parseInt(color1.slice(5, 7), 16);
  
    const r2 = parseInt(color2.slice(1, 3), 16);
    const g2 = parseInt(color2.slice(3, 5), 16);
    const b2 = parseInt(color2.slice(5, 7), 16);
  
    // Calcular los nuevos componentes de color mezclados
    const r3 = Math.round(r1 * (1 - porcentaje) + r2 * porcentaje);
    const g3 = Math.round(g1 * (1 - porcentaje) + g2 * porcentaje);
    const b3 = Math.round(b1 * (1 - porcentaje) + b2 * porcentaje);
  
    // Convertir los nuevos componentes de color a formato hexadecimal
    const rHex = r3.toString(16).padStart(2, "0");
    const gHex = g3.toString(16).padStart(2, "0");
    const bHex = b3.toString(16).padStart(2, "0");
  
    // Crear y devolver el color mezclado en formato hexadecimal
    return `#${rHex}${gHex}${bHex}`;
}

//verifica si es un numero / true o false
function isNum(val){
    return !isNaN(val);
}