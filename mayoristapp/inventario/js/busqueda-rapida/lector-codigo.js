// ---------------------------------- LECTOR CODIGO DE BARRA ---------------------------------
// -------------------------------------------------------------------------------------------
let result = []; //Quagga
let scannerSearchs = []; //buqueda por lector
// let selectCodigo; // ya esta declarada en la busqueda rapida, pero tambien se aplica aca.
let decodeReaders;
let flashEncendido = false;

function armarLectorCodigo(target) {
    let typeTarguet = '"'+target+'"';
    //console.log(typeTarguet);

    let codigos = [
        ['ean_reader', 'EAN', 'leerCodigo(this, '+typeTarguet+')'],
        ['ean_8_reader', 'EAN 8', 'leerCodigo(this, '+typeTarguet+')'],
		['code_128_reader', 'Code 128', 'leerCodigo(this, '+typeTarguet+')'],
        ['code_39_reader', 'Code 39', 'leerCodigo(this, '+typeTarguet+')'],
        ['code_39_vin_reader', 'Code 39 Extende', 'leerCodigo(this, '+typeTarguet+')'],
        //['codabar_reader', 'Codabar', 'leerCodigo(this, '+typeTarguet+')'],
        ['upc_reader', 'UPC', 'leerCodigo(this, '+typeTarguet+')'],
        ['upc_e_reader', 'UPC E', 'leerCodigo(this, '+typeTarguet+')'],
        //['i2of5_reader', 'Interleaved 2 of 5', 'leerCodigo(this, '+typeTarguet+')'],
        ['2of5_reader', 'Standard 2 of 5', 'leerCodigo(this, '+typeTarguet+')'],
        ['code_93_reader', 'Code 93', 'leerCodigo(this, '+typeTarguet+')']
    ];

    let imgCode = [
        'ean-13.gif',
        'ean-8.gif',
		'code-128.gif',
        'code-39.gif',
        'code-39-extended.gif',
        //'codabar.png',
        'upc.gif',
        'upc-e.gif',
        //'i2of5.png',
        '2of5.gif',
        'code-93.gif',
    ];

    for (let i = 0; i < codigos.length; i++) {
        //let scannOption  = document.querySelector("#" + codigos[i][0]);
        let nameCode = codigos[i][1];
        codigos[i][1] = '<img src="img/code/'+imgCode[i]+'"><span>' + nameCode + '</span>';
    };

    createDivDropdown({
        //target: '#content-video',
        target: '#body-title',
        id: 'select-code-scann',
        textButton: 'Seleccione el tipo de codigo de barra',
        class: 'd-grid gap-2',
        menu: codigos
    });

	createButton({
		target: '#body-title',
		name: 'close',
		type: 'button',
		id: 'flash-btn',
		class: 'btn-primary d-grid gap-2',
		onclick: 'encenderFlash("true")',
		value: '',
		htmlText: '<i class="far fa-lightbulb"></i>'
	});

	createDiv({
            target: '#content-video-card',
            id: 'flash-txt',
            class: 'w-100 border rounded p-2 mb-2 d-flex justify-content-between d-none',
            html: 'El flash se apaga automáticamente al cerrar el lector de código de barra.<span class="text-end"><i class="fas fa-info-circle"></i></span>',
        });

	//nuevo -> 2025-03-12
	/*anime({
        targets: '#content-general',
        translateX: ['0%', '-200%'],
        easing: 'easeInOutQuad',
        duration: 800
    });*/
}

//leer codigo de barra
function leerCodigo(decode, target) {

	scannerSearchs.length = 0;

    let cardTypeTitle = document.querySelector('#card-type-title');
    let estadoLectura;

    if (!decode || decode == 'null') { //inicia por primera vez
        //decodeReaders = 'code_128_reader';
		decodeReaders = 'ean_reader';
        //pongo card de la camara
        //startScann('<div class="decode-title"><img src="img/code/code-128.gif"><span>Code 128</span></div>', target);
		startScann('<div class="decode-title"><img src="img/code/ean-13.gif"><span>EAN</span></div>', target);
        estadoLectura = 0;
        //pongo select de decoders
        armarLectorCodigo(target);
    } else { //tenemos una lectura en curso
        decodeReaders = decode.id;
        //console.log(decode.innerHTML);
        Quagga.stop();
        cardTypeTitle.innerHTML = '<div class="decode-title">'+decode.innerHTML+'</div>';
        estadoLectura = 1;
    }

    //inicio la camara
	Quagga.init({
		inputStream: {
			name: 'Live',
			type: 'LiveStream',
			target: document.querySelector('#body-cam'), // Pasar el elemento del DOM
            constraints: {
                aspectRatio: 1 / 1,
				facingMode: 'environment',
				width: { ideal: 2160 },
				height: { ideal: 2160 },
            },
			area: { top: '30%', right: '10%', left: '10%', bottom: '30%' },
		},
		locator: {
			patchSize: 'large',
			halfSample: true
		},
		locate: false,
		numOfWorkers: Math.min(navigator.hardwareConcurrency, 4),
		decoder: {
			readers:  [ decodeReaders ],
			multiple: false
		}
	}, function (err) {
		if (err) {
			Swal.fire({
				title: 'Error!',
				html: 'Cámara no disponible.<br>Verificá los permisos y la conexión de la cámara.',
				icon: 'error',
				confirmButtonText: 'Volver',
				allowOutsideClick: false
			});

			return
		}
        if (target == 'busqueda' && estadoLectura == 0) { 
            hideBuscador();
            anime({
                targets: '#content-general',
                opacity: ['0', '1'],
                translateX: ['0%', '-200%'],
                easing: 'easeInOutQuad',
                duration: 800
            });
        }
        if (target == 'edicion' && estadoLectura == 0) { 
            anime({
                targets: '#content-general',
                opacity: ['0', '1'],
                translateX: ['0%', '-200%'],
                easing: 'easeInOutQuad',
                duration: 800
            });
        }

        Quagga.start();

		// 🔧 Mejora de rendimiento del canvas
		setTimeout(() => {
			const overlayCanvas = Quagga.canvas.dom.overlay;
			if (overlayCanvas) {
				const ctx = overlayCanvas.getContext('2d', { willReadFrequently: true });
				Quagga.canvas.ctx.overlay = ctx;
			}
		}, 500);

		// Esperamos un poco a que el stream esté disponible
		setTimeout(() => {
			//encenderFlash(true); // 🔹 Enciende el flash automáticamente
		}, 1000);
	});

	Quagga.onDetected((data) => {

        var searchDet = data.codeResult.code;

        function findOption(array, codigo) {

            var existeValor = false;
            opcion = {valor: 1, cod: codigo};

            for (let i = 0; i < array.length; i++) {
                element = array[i];
                if (element.cod === searchDet) {
                    existeValor = true;
                };
            };

            if (existeValor) {
                element.valor++;
            } else {
                array.push(opcion);
            };
        };

        findOption(scannerSearchs, searchDet);

        function searchDetected() {

            let targetButton;

            //Elimino elementos previos
            if(target == 'busqueda') {
                borrarMenuVideo = document.getElementById('content-intro');
            };
            if(target == 'edicion') {
                borrarMenuVideo = document.getElementById('content-video');
            }
            while (borrarMenuVideo.firstChild) {
                borrarMenuVideo.removeChild(borrarMenuVideo.firstChild);
            };

            //Armo el menu
            scannerSearchs = scannerSearchs.sort(function(a, b){return a.valor - b.valor});

            if(scannerSearchs.length > 4 || scannerSearchs[0].valor > 10) {

                Quagga.stop();
                document.getElementById('content-video').innerHTML = '';

                let targetCreateCard = '';
                if(target == 'busqueda') {
                    targetCreateCard = '#content-intro';
                };
                if(target == 'edicion') {
                    targetCreateCard = '#content-video';
                }

                createCard({
                    target: targetCreateCard,
                    id: 'content-video-list-button',
                    content: [["header", "header-list-button"], ["body", "body-list-button"]]
                });

                createDiv({
                    target: '#header-list-button',
                    id: 'menu-button-codebar',
                    class: 'card-head-title',
                    html: '<h6>Selecciona el código.</h6>',
                });

                createButton({
                    target: '#header-list-button',
                    name: 'close',
                    type: 'button',
                    id: 'button-cerrar',
                    class: 'close-button',
                    onclick: 'closeScannMenu("'+target+'")',
                    value: '',
                    htmlText: '<i class="bi bi-x-lg"></i>'
                });

				/*createButton({
					target: '#body-list-button',
					name: 'name-button',
					type: 'button',
					id: '',
					class: 'btn-primary star',
					onclick: 'getNewSearchScan("barra", null, null, "7790580126322")',
					value: '',
					htmlText: 'test: 7790580126322',
				});*/

                for (let i = 0; i < scannerSearchs.length; i++) {

                    codigo = scannerSearchs[i].cod;

                    if(target == 'busqueda') {
                        targetButton = 'getNewSearchScan("barra", null, null, "'+codigo+'")';
                    }

                    if(target == 'edicion') {
                        targetButton = 'getCodigoNuevo("'+search.articulo.IDArt+'", "'+selectCodigo+'", "'+codigo+'")';
                    }

					if (scannerSearchs[i].valor <= 3) { star = ''; }
					if (scannerSearchs[i].valor > 3 && scannerSearchs[i].valor <= 6) { star = '<i class="fas fa-star"></i>'; }
					if (scannerSearchs[i].valor > 6 && scannerSearchs[i].valor <= 9) { star = '<i class="fas fa-star"></i><i class="fas fa-star"></i>'; }
					if (scannerSearchs[i].valor > 9) { star = '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>'; }

                    createButton({
                        target: '#body-list-button',
                        name: 'name-button',
                        type: 'button',
                        id: '',
                        class: 'btn-primary star',
                        onclick: targetButton,
                        value: '',
                        htmlText: codigo + ' ' + star,
                    });
                }

                let animeTranslateX = [];
                if(target == 'busqueda') {
                    animeTranslateX = ['-200%', '0%'];

                    anime({
                        targets: '#content-general',
                        translateX: animeTranslateX,
                        easing: 'easeInOutQuad',
                        duration: 800
                    });
                };
            }
        }
        
        searchDetected(searchDet);

	});

	Quagga.onProcessed(function (result) {
		const drawingCtx = Quagga.canvas.ctx.overlay;
		const drawingCanvas = Quagga.canvas.dom.overlay;
		const video = Quagga.canvas.dom.image;

		// --- 🔧 Sincronizar el tamaño del canvas con el video ---
		/*const videoWidth = video.videoWidth;
		const videoHeight = video.videoHeight;

		// Asegura que el canvas tenga las mismas dimensiones que el video
		if (drawingCanvas.width !== videoWidth || drawingCanvas.height !== videoHeight) {
			drawingCanvas.width = videoWidth;
			drawingCanvas.height = videoHeight;
		}*/

		

		const currentWidth = drawingCanvas.width;
		const currentHeight = drawingCanvas.height;
		if (!currentWidth || !currentHeight) {
			drawingCanvas.width = video.videoWidth;
			drawingCanvas.height = video.videoHeight;
		}

		// --- Limpiar canvas antes de dibujar ---
		drawingCtx.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);

		// --- Espejo (si aplica) ---
		const isMirrored = video.style.transform && video.style.transform.includes('scaleX(-1)');
		if (isMirrored) {
			drawingCtx.save();
			drawingCtx.scale(-1, 1);
			drawingCtx.translate(-drawingCanvas.width, 0);
		}

		// --- 🔹 Dibujo de cajas ---
		if (result) {
			if (result.boxes) {
				result.boxes
					.filter(box => box !== result.box)
					.forEach(box => {
						Quagga.ImageDebug.drawPath(box, { x: 0, y: 1 }, drawingCtx, {
							color: "green",
							lineWidth: 6
						});
					});
			}

			/*if (result.box) {
				Quagga.ImageDebug.drawPath(result.box, { x: 0, y: 1 }, drawingCtx, {
					color: "#00F",
					lineWidth: 6
				});
			}*/

			if (result.codeResult && result.codeResult.code) {
				Quagga.ImageDebug.drawPath(result.line, { x: "x", y: "y" }, drawingCtx, {
					color: "red",
					lineWidth: 9
				});
			}
		}

		if (isMirrored) {
			drawingCtx.restore();
		}

	});
};

// Alterna o cambia el estado del flash
function encenderFlash(encender) {
	//let flashBtn = document.getElementById('flash-btn');
    const track = Quagga.CameraAccess.getActiveTrack();
    if (!track) {
        console.warn('⚠️ No hay cámara activa aún.');
        return;
    }

    const capabilities = track.getCapabilities();
    if (!capabilities.torch) {
        console.warn('⚠️ Este dispositivo no soporta flash (torch).');
        return;
    }

    // Si no se pasa parámetro, alterna el estado actual
    if (typeof encender === 'undefined') {
        encender = !flashEncendido;
    }

    track.applyConstraints({
        advanced: [{ torch: encender }]
		}).then(() => {
			flashEncendido = encender; // Actualiza el estado
			
			//flashBtn.toggleClass('activo');
			$('#flash-btn').toggleClass('activo');
			$('#flash-txt').toggleClass('d-none');
			//console.log(encender ? '🔦 Flash encendido' : '💡 Flash apagado');
			//alert(encender ? '🔦 Flash encendido' : '💡 Flash apagado');

		}).catch(err => {
			//console.error('Error al cambiar torch:', err);
			//alert('Error al cambiar torch:', err);
    });
}

//cierra el lector de codigo
function closeScann(type) {
    let borrarDiv = document.getElementById('content-video');
    while (borrarDiv.firstChild) {
        borrarDiv.removeChild(borrarDiv.firstChild);
    }

    Quagga.stop();

    if(type=='busqueda'){
        anime({
            targets: '#content-general',
            translateX: ['-200%', '0%'],
            easing: 'easeInOutQuad',
            duration: 800
        })
        hideBuscador();
    } 
    if(type=='edicion'){
        anime({
            targets: '#content-general',
            translateX: ['-200%', '-100%'],
            easing: 'easeInOutQuad',
            duration: 800
        })
    }
}

function closeScannMenu(type) {
    if(type=='busqueda'){
        let borrarDiv = document.getElementById('content-intro');
        while (borrarDiv.firstChild) {
            borrarDiv.removeChild(borrarDiv.firstChild);
        }
        hideBuscador();
    }
    if(type=='edicion'){
        let borrarDiv = document.getElementById('content-video');
        while (borrarDiv.firstChild) {
            borrarDiv.removeChild(borrarDiv.firstChild);
        }
        anime({
            targets: '#content-general',
            translateX: ['-200%', '-100%'],
            easing: 'easeInOutQuad',
            duration: 800
        })
    }
}