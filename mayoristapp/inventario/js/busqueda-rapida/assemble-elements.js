/** 
 * Crea las distintas partes del inventario
 * ----------------------------------------
 */

// Arma lista dragable de imagenes
function createFormImgList(name, imgArray) {

    try {
        let images = imgArray.length;
        name = name.replace("#", "");
        let card  = document.getElementById(name);

        let armoLista = document.createElement('div');
        armoLista.className = 'card-list';
        card.appendChild(armoLista);

        let list  = card.getElementsByClassName('card-list');
        let itemList = document.createElement('div');
        itemList.className = 'items-list';
        itemList.setAttribute("id", 'item-list-slide');

        let classPrincipal;

        for (i=0;i<images;i++) {
            if(imgArray[i].principal=='Si') { classPrincipal = 'item principal'; } else { classPrincipal = 'item' };
            let item = document.createElement('div');
            item.className = classPrincipal;
            item.setAttribute("id", 'actual');

            let arrow = document.createElement('div');
            arrow.className = 'arrow';
            arrow.innerHTML = '<i class="bi bi-arrows-expand"></i>';

            let imge = document.createElement('img');
            imge.className = 'image';
            imge.setAttribute("id", imgArray[i].id);
            imge.src = imgArray[i].url;

            let control = document.createElement('div');
            control.className = 'control';

            let buttonDeleteImg = document.createElement('button');
            buttonDeleteImg.className = 'btn btn-primary eliminar-img-form';
            buttonDeleteImg.type = 'button';
            buttonDeleteImg.setAttribute("onclick", 'deleteFoto('+imgArray[i].id+')');
            buttonDeleteImg.innerHTML = '<i class="bi bi-trash"></i> Borrar';

            let buttonHacerPrincipal = document.createElement('button');
            buttonHacerPrincipal.className = 'btn btn-primary hacer-principal';
            buttonHacerPrincipal.type = 'button';
            buttonHacerPrincipal.setAttribute("onclick", 'saveFotoPrincipal('+imgArray[i].id+')');
            buttonHacerPrincipal.innerHTML = '<i class="fa-solid fa-star"></i> Imagen principal';

            //item.appendChild(arrow);
            item.appendChild(imge);
            control.appendChild(buttonDeleteImg);
            control.appendChild(buttonHacerPrincipal);
            item.appendChild(control);
            //item.appendChild(arrow);
            itemList.appendChild(item);
        }

        list[0].appendChild(itemList);

    } catch (e) {
        console.error(e.name);
        console.error(e.message);
    } finally {
        return false;
    }

};

//Arma primera pantalla del inventario
function setCardIntro(origen) {

    let imgBtn;

    let datos;
    let inventario;
    let codigo;
    let fotos;

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    createCard({
        target: '#content-intro',
        id: 'card-Intro',
        content: [["header", "header-intro-1"], ["body", "body-intro-1"]]
    });
    
    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-intro-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })

    createButton({
        target: '#header-intro-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeSearch()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    // -- Genero los permisos
    switch (datosUsuario.permisos.accion_inventario) {
        case 'Todos':
            datos = true;
            inventario = true;
            codigo = true;
            fotos = true;
          break;
        case 'Carga inventario':
            datos = false;
            inventario = true;
            codigo = false;
            fotos = false;
          break;
        case 'Editar datos':
            datos = true;
            inventario = false;
            codigo = true;
            fotos = true;
          break;
    };

    // -- ver datos (solo desde escaneo de cámara) --
    if(origen == 'scan') {
        createDiv({
            target: '#body-intro-1',
            id: 'intro-ver',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-ver',
            name: 'ver-datos',
            type: 'button',
            id: 'button-ver-datos',
            class: 'close-button',
            onclick: 'setCardVerDatos()',
            value: '',
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-magnifying-glass"></i> Ver datos del producto</h6><span>Nombre - Precio - Stock</span>'
        });
    }
    // -- fin ver datos --

    // -- titulo -- ------------------------------------------------- 
    if(datos == true) {
        createDiv({
            target: '#body-intro-1',
            id: 'intro-titulo',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-titulo',
            name: 'detalle',
            type: 'button',
            id: 'button-detalle',
            class: 'close-button',
            onclick: 'setCardEditNombre()',
            value: '',
            //htmlText: '<i class="bi bi-pencil-square"></i> Editar'
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-box"></i> Datos del producto</h6><span>Nombre - Detalle</span>'
        });
    }
    // -- fin titulo --

    // -- stock -- -------------------------------------------------
    if(inventario == true) {
        createDiv({
            target: '#body-intro-1',
            id: 'intro-stock',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-stock',
            name: 'ajustes',
            type: 'button',
            id: 'button-ajustes',
            class: 'close-button',
            onclick: 'setCardProducto(depositos)',
            value: '',
            //htmlText: '<i class="bi bi-pencil-square"></i> Cargar'
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-cubes"></i> Movimiento de inventario</h6><span>Ajustes</span>'
        });
    }
    // -- fin stock --

    // -- codigo -- -------------------------------------------------
    if(codigo == true) {
        createDiv({
            target: '#body-intro-1',
            id: 'intro-codigos',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-codigos',
            name: 'codigos',
            type: 'button',
            id: 'button-codigos',
            class: 'close-button',
            onclick: 'setCardEditCodigo()',
            value: '',
            //htmlText: '<i class="bi bi-pencil-square"></i> Editar'
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-barcode"></i> Códigos de barra</h6><span>Producto - Display - Bulto</span>'
        });
    }
    // -- fin codigo --

    // -- imagen -- -------------------------------------------------
    if(fotos == true) {
        createDiv({
            target: '#body-intro-1',
            id: 'intro-formn',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-formn',
            name: 'editImg',
            type: 'button',
            id: 'button-editImg',
            class: 'close-button',
            onclick: 'setFormProducto()',
            value: '',
            //htmlText: '<i class="bi bi-pencil-square"></i> Editar'
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-image"></i> Imágenes</h6><span>Nuevas - Edición - Principal</span>'
        });
    }
    // -- fin imagen -- 
}

//Arma primera pantalla del inventario (no se usa porque va directo al producto)
function setCardIntroScan() {

    let imgBtn;

    let datos;
    let inventario;
    let codigo;
    let fotos;

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    createCard({
        target: '#content-intro',
        id: 'card-Intro',
        content: [["header", "header-intro-1"], ["body", "body-intro-1"]]
    });
    
    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-intro-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })

    createButton({
        target: '#header-intro-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeSearch()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    // -- Genero los permisos
    switch (datosUsuario.permisos.accion_inventario) {
        case 'Todos':
            datos = true;
            inventario = true;
            codigo = true;
            fotos = true;
          break;
        case 'Carga inventario':
            datos = false;
            inventario = true;
            codigo = false;
            fotos = false;
          break;
        case 'Editar datos':
            datos = true;
            inventario = false;
            codigo = true;
            fotos = true;
          break;
    };

    // -- ver datos producto 2025 -- ------------------------------------------------- 
        createDiv({
            target: '#body-intro-1',
            id: 'intro-ver',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between btn-content',
            html: '',
        });

        createButton({
            target: '#intro-ver',
            name: 'detalle',
            type: 'button',
            id: 'button-detalle',
            class: 'close-button',
            onclick: 'setCardVerDatos()',
            value: '',
            //htmlText: '<i class="bi bi-pencil-square"></i> Editar'
            htmlText: '<h6 class="mt-2"><i class="fa-solid fa-box"></i> Ver datos del producto</h6><span>Nombre - Detalle</span>'
        });
    // -- ver datos producto 2025 --
}

//arma lista de busqueda por palabra
function setCardListaProducto(arrLista, limite) {

    if (limite === undefined) { limite = 5; }

    let contentLista = [["header", "header-lista-1"], ["body", "body-lista-1"]];
    if (arrLista.length > limite) { contentLista.push(["footer", "footer-lista-1"]); }

    createCard({
        target: '#content-intro',
        id: 'card-lista',
        content: contentLista
    });

    createSimpleCardTitle({
        target: '#header-lista-1',
        id: 'titulo-lista',
        title: arrLista.length+' productos encontrados',
    })

    createButton({
        target: '#header-lista-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeLista()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    let totalMostrar = (arrLista.length > limite) ? limite : arrLista.length;

    for (var i = 0; i < totalMostrar; i++) {

        let img;
        let numero = i+1;

        let color1 = "#2C8A7B";
        let color2 = "#2A3E72";
        let porcentaje = (100/arrLista.length)*numero;

        let colorMezclado = mezclarColores(color1, color2, porcentaje);

        if (arrLista[i].fotos.length >= 1) {
            img = arrLista[i].fotos[0].url;
        } else {
            img = "img/no_image.jpg";
        }

        createButton({
            target: '#body-lista-1',
            name: 'pruducto',
            type: 'button',
            id: '',
            class: 'item-listado',
            onclick: 'getNewSearch("id", null, "'+arrLista[i].articulo.IDArt+'", null)',
            value: '',
            htmlText: '<div class="image"><img src="'+img+'" width="50" height="50"></div> <span class="numero-indicador" style="background-color:'+colorMezclado+';">'+numero+'</span> <div class="text-start pt-1"><span class="codigo">Cod: '+arrLista[i].articulo.IDArt+'</span><br> <span class="txt">'+arrLista[i].articulo.NombreArticulo+'</span></div>'
        }); 
        
    }

    if (arrLista.length > limite) {
        createButton({
            target: '#footer-lista-1',
            name: 'ver-todos',
            type: 'button',
            id: 'btn-ver-todos-lista',
            class: 'btn-primary w-100',
            onclick: 'expandirListaProductos()',
            value: '',
            htmlText: '<i class="bi bi-list-ul"></i> Ver todos los resultados ('+arrLista.length+')'
        });
    }
        
}

//expande la lista completa de productos al hacer clic en "ver todos"
function expandirListaProductos() {

    let bodyLista = document.getElementById('body-lista-1');
    while (bodyLista.firstChild) {
        bodyLista.removeChild(bodyLista.firstChild);
    }

    let footerLista = document.getElementById('footer-lista-1');
    if (footerLista) { footerLista.remove(); }

    let arrLista = search.listaArticulo;

    for (var i = 0; i < arrLista.length; i++) {

        let img;
        let numero = i+1;

        let color1 = "#2C8A7B";
        let color2 = "#2A3E72";
        let porcentaje = (100/arrLista.length)*numero;

        let colorMezclado = mezclarColores(color1, color2, porcentaje);

        if (arrLista[i].fotos.length >= 1) {
            img = arrLista[i].fotos[0].url;
        } else {
            img = "img/no_image.jpg";
        }

        createButton({
            target: '#body-lista-1',
            name: 'pruducto',
            type: 'button',
            id: '',
            class: 'item-listado',
            onclick: 'getNewSearch("id", null, "'+arrLista[i].articulo.IDArt+'", null)',
            value: '',
            htmlText: '<div class="image"><img src="'+img+'" width="50" height="50"></div> <span class="numero-indicador" style="background-color:'+colorMezclado+';">'+numero+'</span> <div class="text-start pt-1"><span class="codigo">Cod: '+arrLista[i].articulo.IDArt+'</span><br> <span class="txt">'+arrLista[i].articulo.NombreArticulo+'</span></div>'
        });

    }

    alturaListaProductos();
}

//Arma pantalla del inventario
function setCardProducto(depositos) {

    let depositoActual;
    let imgBtn;
    let permisoStock;

    createCard({
        target: '#content-form',
        id: 'card_' + search.articulo.IDArt,
        //content: [["header", "header-1"], ["body", "body-1"], ["body", "body-2"]]
        content: [["header", "header-1"], ["body", "body-2"]]
    });

    document.querySelector('#body-2').classList.add('unidad');

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })
    
    createButton({
        target: '#header-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeForm()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    for (let i = 0; i < depositos.length; i++) {
        if (search.articulo.id_deposito === depositos[i].id_deposito) {
            depositoActual = depositos[i].NombreDeposito;
        };
    };

    createDiv({
        target: '#body-2',
        id: '',
        class: 'card-title',
        html: '<h6>Movimientos de inventario</h6>',
    });

    createInput({
        target: '#body-2',
        type: 'hidden',
        id: 'id_producto',
        class: '',
        placeholder: '',
        value: search.articulo.IDArt,
        required: 'true',
        textLabel: ''
    });

    createInput({
        target: '#body-2',
        type: 'hidden',
        id: 'id_deposito',
        class: '',
        placeholder: '',
        value: search.articulo.id_deposito,
        required: 'true',
        textLabel: ''
    });

    if (datosUsuario.permisos.visualiza_stock_inventario == 'Si') {
        permisoStock = [['col-deposito', 6, 6], ['col-saldo', 6, 6]];
    } else {
        permisoStock = [['col-deposito', 12, 12]];
    }

    createRow({
        target: '#body-2',
        id: '',
        class: 'g-3',
        col: permisoStock,
    });

    createInput({
        target: '#col-deposito',
        type: 'text',
        id: 'producto_deposito',
        class: '',
        placeholder: 'stock',
        value: depositoActual,
        required: 'true',
        textLabel: 'Deposito:',
        extra: 'readonly'
    });

    let classSaldo = datosUsuario.permisos.tipo_cuenta_defecto;
    let valueSaldo;


    switch (classSaldo) {
        case 'Unidad':
            valueSaldo = Number(search.articulo.saldo);
          break;
        case 'Display':
            valueSaldo = Number(search.articulo.saldo)/search.presentacion.cantidadUnidadMinima;
          break;
        case 'bulto':
            valueSaldo = Number(search.articulo.saldo)/search.presentacion.cantidadUnidadMinima;
          break;
    }

    if (datosUsuario.permisos.visualiza_stock_inventario == 'Si') {
        createInput({
            target: '#col-saldo',
            type: 'text',
            id: 'producto_saldo',
            class: classSaldo,
            placeholder: 'saldo',
            value: valueSaldo,
            required: 'true',
            textLabel: 'Saldo:',
            extra: 'readonly'
        });
    }

    createRow({
        target: '#body-2',
        id: '',
        class: 'g-3',
        col: [['col-tipo-ajuste', 6, 6], ['col-motivo-ajuste', 6, 6]],
    });

    console.log(arrSelectMovimientos);

    createSelect({
        target: '#col-tipo-ajuste',
        id: 'tipo-ajuste',
        class: '',
        values: arrSelectMovimientos,
        //values: [["1", "Ajuste"], ["2", "Movimiento de entrada"], ["3", "Movimiento de salida"], ["4", "Rotura"]],
        textLabel: 'Tipo de ajuste',
        opSelected: null
    });

    createInput({
        target: '#col-motivo-ajuste',
        type: 'text',
        id: 'motivo-ajuste',
        class: 'dettalle-ajute',
        placeholder: 'Texto de detalle',
        value: '',
        required: '',
        textLabel: 'Detalle de ajuste...',
        extra: ''
    });

    createHtmlContent({
        target: '#body-2',
        html: '<h6>Contar Productos por:</h6>',
    })

    createButtonGroup({
        target: '#body-2',
        id: 'button-group-contador',
        class: 'w-100',
        arrInputs: [['button', 'radio_unidad', 'btn-outline-primary btn-sm active', 'showRadioVal(this.value)', 'Unidad', 'Unidad <i class="bi bi-check-lg"></i>'], 
                    ['button', 'radio_display', 'btn-outline-primary btn-sm', 'showRadioVal(this.value)', 'Display', 'Display '], 
                    ['button', 'radio_bulto', 'btn-outline-primary btn-sm', 'showRadioVal(this.value)', 'Bulto', 'Bulto ']]
    })

    createDiv({
        target: '#body-2',
        id: 'text-modo-conteo',
        class: 'text-modo-conteo mt-2',
        html: '<p>1 '+search.presentacion[0].nombrePresentacion+' contiene 1 unidad minima</p>',
    });

    createRow({
        target: '#body-2',
        id: 'existencia',
        class: 'w-100  d-none',
        col: [['col-texto-saldo', 6, 6], ['col-total-saldo', 6, 6]],
    });

    createDiv({
        target: '#col-texto-saldo',
        id: '',
        class: 'texto-saldo d-none',
        html: 'Existencia',
    });

    createDiv({
        target: '#col-total-saldo',
        id: '',
        class: 'total-saldo d-none',
        html: search.articulo.saldo,
    });

    nombrePresentacion = search.presentacion[0].nombrePresentacion;
    cantidadDisplay = search.presentacion[0].cantidadDisplay;
    cantidadUnidadMinima = search.presentacion[0].cantidadUnidadMinima;

    createInputGroup({
        target: '#body-2',
        type: 'number',
        id: 'conteo-actual',
        class: '',
        placeholder: '',
        inputmode: 'numeric',
        value: '',
        required: 'true',
        textLabel: 'Cantidad en <br>existencia:',
        extra: 'onkeyup="startConteo(this.value)" onchange="startConteo(this.value)" min="0" step="1" pattern="[0-9]"'
    });
    
    createDiv({
        target: '#body-2',
        id: '',
        class: 'mt-2',
        html: '<h6>Total:</h6>',
    });
    
    createRow({
        target: '#body-2',
        id: 'cuadro-existencia',
        class: 'mt-2',
        col: [['col-texto-unidad', 4, 4], ['col-texto-display', 4, 4], ['col-texto-bulto', 4, 4]],
    });

    let colTextoUnidad = document.querySelector('#col-texto-unidad');
    colTextoUnidad.classList.add('texto-activo');

    createDiv({
        target: '#col-texto-unidad',
        id: '',
        class: 'texto-unidad',
        html: '<div class="valor" id="valor-texto-unidad">0</div><div class="titulo">Unidad</div>',
    });

    createDiv({
        target: '#col-texto-display',
        id: '',
        class: 'texto-display',
        html: '<div class="valor" id="valor-texto-display">0</div><div class="titulo">Display</div>',
    });

    createDiv({
        target: '#col-texto-bulto',
        id: '',
        class: 'texto-bulto',
        html: '<div class="valor" id="valor-texto-bulto">0</div><div class="titulo">Bulto</div>',
    });

    createDiv({
        target: '#body-2',
        id: 'contador',
        class: '',
        html: '',
    });

    setContador(nombrePresentacion, cantidadDisplay, cantidadUnidadMinima);

    createButton({
        target: '#body-2',
        name: 'guardar',
        type: 'button',
        id: 'button-guardar',
        class: '',
        onclick: 'saveInventario()',
        value: '',
        htmlText: '<i class="bi bi-save2"></i><i class="bi bi-arrow-clockwise d-none" style="display:inline-block;"></i> <span class="txt">Guardar</span>'
    });   

    anime({
        targets: '#content-general',
        translateX: ['0%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    });

    switch (datosUsuario.permisos.tipo_cuenta_defecto) {
        case 'Unidad':
            $('#radio_unidad').trigger('click');
          break;
        case 'Display':
            $('#radio_display').trigger('click');
          break;
        case 'Bulto':
            $('#radio_bulto').trigger('click');
          break;
    };

    if ($('#valor-texto-unidad').length) { document.querySelector('#valor-texto-unidad').innerHTML = '0'; }
    if ($('#valor-texto-display').length) { document.querySelector('#valor-texto-display').innerHTML = '0'; }
    if ($('#valor-texto-bulto').length) { document.querySelector('#valor-texto-bulto').innerHTML = '0'; }

    getEnterKeyContador();
    activaSelectAjustes();
}

//Arma pantalla de edicion de fotos
function setFormProducto() {

    let imgBtn;
    document.getElementById('content-form').innerHTML = '';

    createCard({
        target: '#content-form',
        id: 'card_' + search.articulo.IDArt,
        //content: [["header", "header-1b"], ["slide", "slide-1b"], ["footer", "footer-1b"]]
        content: [["header", "header-1b"], ["slide", "slide-1b"]]
    });

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-1b',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })

    createButton({
        target: '#header-1b',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeForm()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    createDiv({
        target: '#slide-1b',
        id: '',
        class: 'card-title',
        html: '<h6>Imágenes</h6>',
    });

    createDiv({
        target: '#slide-1b',
        id: '',
        class: 'card-subtitle',
        html: '<h6>Nueva imagen</h6>',
    });

    createInputFile({
        target: '#slide-1b',
        fileId: 'upload_image',
        labelText: null,
        onChange: 'changeFile()'
    });

    createDiv({
        target: '#slide-1b',
        id: '',
        class: 'card-subtitle mt-4',
        html: '<h6>Lista de imágenes</h6>',
    });

    createFormImgList('#slide-1b', search.fotos);

    // saveImageInventario()
    /*createButton({
        target: '#footer-1b',
        name: 'guardar',
        type: 'button',
        id: 'button-guardar-img',
        class: '',
        onclick: 'saveImageInventario()',
        value: '',
        htmlText: '<i class="bi bi-save2"></i><i class="bi bi-arrow-clockwise d-none" style="display:inline-block;"></i> <span class="txt">Guardar</span>'
    });*/

    //includeImageButton();

    anime({
        targets: '#content-general',
        translateX: ['0%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    });
}

//Arma pantalla para cargar codigos de barra
function setCardEditCodigo() {

    let imgBtn;
    let arrSelect;

    createCard({
        target: '#content-form',
        id: 'card-editar-codigo',
        content: [["header", "header-code-1"], ["body", "body-code-1"]]
    });

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-code-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })
    
    createButton({
        target: '#header-code-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeForm()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    arrSelect = [
        ['NroCodBarra', 'Codigo de Producto - Unidad'],
        ['NroCodBarraF', 'Codigo del Fabricante'],
        ['nro_cod_barra_bulto', 'Codigo de producto - Bulto'],
        ['nro_cod_barra_display', 'Codigo de producto - Display']
    ];

    createDiv({
        target: '#body-code-1',
        id: '',
        class: 'card-title',
        html: '<h6>Editar códigos de barra</h6>',
    });

    createSelect({
        target: '#body-code-1',
        id: 'codigo-select',
        class: 'extraCalss',
        //values: [["value1", "label1"], ["value2", "label2"], ["value3", "label3"]],
        values: arrSelect,
        textLabel: 'Selecciona el tipo código',
        opSelected: '--'
    });

    createDiv({
        target: '#body-code-1',
        id: 'content-lector-codigo',
        class: 'col-sm-12 d-none',
        html: '<div class="form-floating mb-3" id="content-button-code"></div>',
    });

    createDiv({
        target: '#content-lector-codigo',
        id: 'div-content-lector-codigo',
        class: '',
        html: '',
    });

    createInput({
        target: '#div-content-lector-codigo',
        type: 'number',
        id: 'codigo-manual',
        class: '',
        placeholder: 'placeholder del input',
        value: '',
        required: 'true',
        textLabel: 'Código actual',
        extra: 'pattern="[0-9]"'
    });

    createButton({
        target: '#body-code-1',
        name: 'guardar',
        type: 'button',
        id: 'button-guardar',
        class: '',
        onclick: 'saveCode()',
        value: '',
        htmlText: '<i class="bi bi-save2"></i><i class="bi bi-arrow-clockwise d-none" style="display:inline-block;"></i> <span class="txt">Guardar</span>'
    });

    anime({
        targets: '#content-general',
        translateX: ['0%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    });

    activaSelectCodigo();

};

//Arma pantalla ver datos del producto
function setCardVerDatos() {

    let imgBtn;

	console.log(search);

    createCard({
        target: '#content-form',
        id: 'card_' + search.articulo.IDArt,
        content: [["header", "header-code-1"], ["body", "body-code-1"]]
    });

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-code-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })
    
    createButton({
        target: '#header-code-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeForm()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    createDiv({
        target: '#body-code-1',
        id: '',
        class: 'card-title',
        html: '<h6>Datos del producto</h6>',
    });

    createDiv({
        target: '#body-code-1',
        id: 'nombre-articulo',
        class: 'x_class',
        html: '<h6>Nombre del Producto</h6><p>' + search.articulo.NombreArticulo + '</p>',
    });

    createDiv({
        target: '#body-code-1',
        id: 'nombre-ecomm',
        class: 'x_class',
        html: '<h6>Nombre para e-commerce</h6><p>' + search.articulo.nombre_articulo_ecom + '</p>',
    });

    createDiv({
        target: '#body-code-1',
        id: 'detalle-web',
        class: 'x_class',
        html: '<h6>Detalle web</h6><p>' + search.articulo.detalle_web + '</p>',
    });

	//---------- datos de e-commerce adicionales 2025 -----------
	
	createDiv({
        target: '#body-code-1',
        id: 'detalle-web',
        class: 'x_class',
        html: '<h6>Neto: ' + formatearNumero(search.precios.netoCalc) + '</h6>',
    });

	createDiv({
        target: '#body-code-1',
        id: 'detalle-web',
        class: 'x_class',
        html: '<h6>Importe Iva: ' + formatearNumero(search.precios.importeIva) + ' | Iva Alic: ' + search.precios.ivaAlic + '</h6>',
    });

	createDiv({
        target: '#body-code-1',
        id: 'detalle-web',
        class: 'x_class',
        html: '<h6>Precio final: ' + formatearNumero(search.precios.precioFinal) + '</h6>',
    });

	let classSaldo = datosUsuario.permisos.tipo_cuenta_defecto;
    let valueSaldo;
	let keyPresentacion;

    switch (classSaldo) {
        case 'Unidad':
            valueSaldo = Number(search.articulo.saldo);
			keyPresentacion = 0;
          break;
        case 'Display':
            valueSaldo = Number(search.articulo.saldo)/search.presentacion[1].cantidadUnidadMinima;
			keyPresentacion = 1;
          break;
        case 'bulto':
            valueSaldo = Number(search.articulo.saldo)/search.presentacion[2].cantidadUnidadMinima;
			keyPresentacion = 2;
          break;
    }

    if (datosUsuario.permisos.visualiza_stock_inventario == 'Si') {
        createInput({
            target: '#body-code-1',
            type: 'text',
            id: 'producto_saldo',
            class: classSaldo,
            placeholder: 'saldo',
            value: valueSaldo,
            required: 'true',
            textLabel: 'Saldo ('+classSaldo+'):',
            extra: 'readonly'
        });
    }

	createDiv({
        target: '#body-code-1',
        id: 'text-modo-conteo',
        class: 'text-modo-conteo mt-2',
        html: '<p>1 '+search.presentacion[keyPresentacion].nombrePresentacion+' contiene '+search.presentacion[keyPresentacion].cantidadUnidadMinima+' unidad minima</p>',
    });

	//-----------------------------------------------------------

    anime({
        targets: '#content-general',
        translateX: ['0%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    });
}

//Arma pantalla para cargar editar el nombre
function setCardEditNombre() {

    let imgBtn;

    createCard({
        target: '#content-form',
        id: 'card_' + search.articulo.IDArt,
        content: [["header", "header-code-1"], ["body", "body-code-1"]]
    });

    if(search.fotos >= 0){
        imgBtn = 'img/no_image.jpg';
    } else {
        imgBtn = search.fotos[0].url;
    }

    let precioUnidadDisplay = search.precios.precioFinal/search.presentacion[1].cantidadUnidadMinima;

    createCardTitle({
        target: '#header-code-1',
        id: '',
        title: 'Cod: '+search.articulo.IDArt,
        subTitle: search.articulo.NombreArticulo,
        finalPrice: '$ '+formatearNumero(search.precios.precioFinal)+' <span style="font-size:.9rem;">('+search.presentacion[1].cantidadUnidadMinima+' un.)</span> | $ '+formatearNumero(precioUnidadDisplay)+ ' <span style="font-size:.9rem;">x un.</span>',
        image: imgBtn
    })
    
    createButton({
        target: '#header-code-1',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeForm()',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    createDiv({
        target: '#body-code-1',
        id: '',
        class: 'card-title',
        html: '<h6>Editar datos del producto</h6>',
    });

    createInput({
        target: '#body-code-1',
        type: 'text',
        id: 'nombre-articulo',
        class: '',
        placeholder: 'Nombre',
        value: search.articulo.NombreArticulo,
        required: 'true',
        textLabel: 'Nombre del Producto',
        extra: ''
    });

    createInput({
        target: '#body-code-1',
        type: 'text',
        id: 'nombre-ecomm',
        class: '',
        placeholder: 'Nombre',
        value: search.articulo.nombre_articulo_ecom,
        required: 'true',
        textLabel: 'Nombre para e-commerce',
        extra: ''
    });

    createTextarea({
        target: '#body-code-1',
        id: 'detalle-web',
        class: '',
        placeholder: 'Texto para el detalle web',
        rows: '10',
        textLabel: 'Detalle web',
        text: search.articulo.detalle_web
    });

    createButton({
        target: '#body-code-1',
        name: 'guardar',
        type: 'button',
        id: 'button-guardar',
        class: '',
        onclick: 'saveName()',
        value: '',
        htmlText: '<i class="bi bi-save2"></i><i class="bi bi-arrow-clockwise d-none" style="display:inline-block;"></i> <span class="txt">Guardar</span>'
    });

    anime({
        targets: '#content-general',
        translateX: ['0%', '-100%'],
        easing: 'easeInOutQuad',
        duration: 800
    });
}

function setContador(nombrePresentacion, cantidadDisplay, cantidadUnidadMinima) {

    createDiv({
        target: '#contador',
        id: '',
        class: 'mt-2 d-none',
        html: '<h6>Saldo Ajuste:</h6>',
    });

    createInput({
        target: '#contador',
        type: 'hidden',
        id: 'conteo_display',
        class: '',
        placeholder: '',
        value: cantidadDisplay,
        required: 'true',
        textLabel: ''
    });
    
    createInput({
        target: '#contador',
        type: 'hidden',
        id: 'conteo_unidad_minima',
        class: '',
        placeholder: '',
        value: cantidadUnidadMinima,
        required: 'true',
        textLabel: ''
    });

    createDiv({
        target: '#contador',
        id: 'total_unidad',
        class: 'totales_contado d-none',
        html: '',
    });
}

//inicio del lector
function startScann(title, type) {
    
    //hideBuscador();

    createCard({
        target: '#content-video',
        id: 'content-video-card',
        content: [["header", "header-cam"],["body", "body-title"], ["body", "body-cam"]]
    });

    document.getElementById('body-cam').classList.add('position-relative');

    createDiv({
        target: '#header-cam',
        id: '',
        class: 'card-head-title',
        html: '<h6>Lector de codigo</h6>',
    });
    
    createButton({
        target: '#header-cam',
        name: 'close',
        type: 'button',
        id: 'button-cerrar',
        class: 'close-button',
        onclick: 'closeScann("'+type+'")',
        value: '',
        htmlText: '<i class="bi bi-x-lg"></i>'
    });

    createDiv({
        target: '#body-title',
        id: 'card-type-title',
        class: 'card-type-title',
        html: title,
    });
}

// Formatea un numero a formato moneda
function formatearNumero(valor) {

  // Convertimos a string y quitamos espacios
  valor = String(valor).trim();

  // Si contiene coma decimal, convertimos a formato con punto decimal
  if (valor.includes(",")) {
    valor = valor.replace(/\./g, "").replace(",", ".");
  }

  // Convertimos a número
  const numero = Number(valor);

  if (isNaN(numero)) {
    return "Valor no numérico";
  }

  // Separamos entero y decimal
  const [entero, decimal] = numero.toFixed(2).split(".");

  // Ponemos puntos de miles
  const conSeparadorMiles = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

  return conSeparadorMiles + "," + decimal;
}


// Manejo del historial de navegación para controlar el flujo de pantallas
// Cada vez que avanzás un paso:
history.pushState({ paso: 'buscador' }, '', '#buscador');

// Cuando vuelve atrás:
window.addEventListener('popstate', (event) => {
    if (event.state && event.state.paso === 'escaneo') {
        mostrarPantallaAnterior();
    } else {
        // Evitás salir del flujo
        history.pushState({ paso: 'escaneo' }, '', '#escaneo');
        //alert("Usá el botón de la app para volver atrás.");
		Swal.fire({
			title: '¿Quieres salir del inventario?',
			html: 'Usá el botón de la app para volver atrás.',
			icon: 'warning',
			confirmButtonText: 'Volver',
			allowOutsideClick: false
		});
    }
});