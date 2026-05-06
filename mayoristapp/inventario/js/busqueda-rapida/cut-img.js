
let arrBlobs = [];
var $modal = $('#modal-img-cut');
var image = document.getElementById('cut_image');
var fileInputElement = document.getElementById('image1');
var image_w = '800';
var image_h = '800';
var cropper;
var imageOrientation = 'landscape'; // 'portrait' | 'landscape'

image_w = parseInt(image_w, 10);
image_h = parseInt(image_h, 10);

aspectRatioDat = image_w + ':' + image_h;

/**
 * Pre-compone una imagen portrait sobre un canvas cuadrado con fondo blanco.
 * La imagen queda centrada horizontalmente; el alto del cuadrado = alto de la imagen.
 * Así Cropper.js recibe siempre una imagen cuadrada y no necesita escalarla.
 */
function portraitToSquare(dataUrl, origW, origH, callback) {
    var img = new Image();
    img.onload = function() {
        var size = origH; // cuadrado basado en la dimensión mayor (el alto)
        var canvas = document.createElement('canvas');
        canvas.width  = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        var x = Math.round((size - origW) / 2);
        ctx.drawImage(img, x, 0);
        console.log('[cut-img] Pre-composición portrait→cuadrado:', {
            canvasSize: size + 'x' + size,
            imagenOriginal: origW + 'x' + origH,
            offsetX: x
        });
        callback(canvas.toDataURL('image/jpeg', 0.95));
    };
    img.src = dataUrl;
}

function changeFile() {
    var files = event.target.files;

    var done = function(url) {
        var tempImg = new Image();
        tempImg.onload = function() {
            var w = tempImg.naturalWidth;
            var h = tempImg.naturalHeight;
            imageOrientation = h > w ? 'portrait' : 'landscape';

            var modalEl = document.getElementById('modal-img-cut');
            modalEl.classList.toggle('is-portrait', imageOrientation === 'portrait');
            modalEl.classList.toggle('is-landscape', imageOrientation === 'landscape');

            console.log('[cut-img] Imagen cargada:', {
                ancho: w,
                alto: h,
                orientacion: imageOrientation,
                accion: imageOrientation === 'portrait' ? 'pre-composicion cuadrada con fondo blanco' : 'directo al cropper'
            });

            if (imageOrientation === 'portrait') {
                // Fotos verticales (botellas, etc.): componer primero en cuadrado blanco
                portraitToSquare(url, w, h, function(squareUrl) {
                    image.src = squareUrl;
                    $modal.modal('show');
                });
            } else {
                // Fotos horizontales: directo al cropper
                image.src = url;
                $modal.modal('show');
            }
        };
        tempImg.src = url;
    };

    if(files && files.length > 0){
        reader = new FileReader();
        reader.onload = function(event) {
            done(reader.result);
        };
        reader.readAsDataURL(files[0]);
    }
}

$modal.on('shown.bs.modal', function(){
    // Portrait ya llega como cuadrado pre-compuesto → viewMode 1 (contain, sin recorte)
    // Landscape → viewMode 2 (cover, el usuario panea para elegir zona)
    var vMode = imageOrientation === 'portrait' ? 1 : 2;
    console.log('[cut-img] Iniciando Cropper con viewMode:', vMode, '| orientacion:', imageOrientation);
    cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: vMode,
        responsive: true,
    });
}).on('hidden.bs.modal', function(){
    cropper.destroy();
    cropper = null;
});

$('#crop').click(function(){
    canvas = cropper.getCroppedCanvas({
        width: image_w,
        height: image_h
    });

    $modal.modal('hide');

    canvas.toBlob(function(blob) {
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function(){
            let file = new File([blob], "img.jpg",{type:"image/jpeg", lastModified:new Date().getTime()});
            file.src = reader.result;
            console.log('Este es el file: ', file);
            saveFoto(file);
        }
      }, 'image/jpeg', 0.8);

    //console.log('Este es el blob: ', canvas.toDataURL("image/jpeg"));

    //saveFoto(canvas.toDataURL("image/jpeg"));


    /*canvas.toBlob(function(blob){
        var reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = function(){

            //saveFoto(reader.result);

            let file = new File([blob], "img.jpg",{type:"image/jpeg", lastModified:new Date().getTime()});
            file.src = reader.result;
            let container = new DataTransfer();
            container.items.add(file);
            fileInputElement.files = container.files;

            //carga la imagen en la lista
            /*
            let itemList  = document.querySelector('.items-list');
            let ix = 0;
            if(itemList.querySelector('.item')){
                ix = document.getElementById('item-list-slide').getElementsByClassName('item').length;
            } else { 
                ix = 0; 
            };

            let item = document.createElement('div');
            item.className = 'item';
            item.setAttribute("id", 'nueva');

            let arrow = document.createElement('div');
            arrow.className = 'arrow';
            arrow.innerHTML = '<i class="bi bi-arrows-expand"></i>';

            let imge = document.createElement('img');
            imge.className = 'image';
            imge.setAttribute("id", 'no');
            imge.src = reader.result;

            let control = document.createElement('div');
            control.className = 'control';

            let buttonDeleteImg = document.createElement('button');
            buttonDeleteImg.className = 'btn btn-primary eliminar-img-form';
            buttonDeleteImg.type = 'button';
            buttonDeleteImg.innerHTML = '<i class="bi bi-trash"></i> Borrar';

            let buttonHacerPrincipal = document.createElement('button');
            buttonHacerPrincipal.className = 'btn btn-primary hacer-principal';
            buttonHacerPrincipal.type = 'button';
            buttonHacerPrincipal.innerHTML = '<i class="fa-solid fa-star"></i> Imagen principal';

            //item.appendChild(arrow);
            item.appendChild(imge);
            control.appendChild(buttonDeleteImg);
            item.appendChild(control);
            control.appendChild(buttonHacerPrincipal);
            //item.appendChild(arrow);
            itemList.appendChild(item);
            //----fin de la carga-----
            */

            //arrBlobs.push({image: container.files, divId: 'imgeList-'+ix});

            //console.log('Este es el blob: ', fileInputElement.files[0]);

            //saveFoto(fileInputElement.files[0]);

            //includeImageButton();

    //    };
    //});

});
    