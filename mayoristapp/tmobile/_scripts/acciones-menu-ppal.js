    // js con acciones del menu principal mobil.
    var url = window.location.pathname;
    var filename = url.substring(url.lastIndexOf('/')+1);
    //console.warn('nombre de pagina',filename);

    // * funcion para la navegacion del menu
    function activarOpcionNav (idBoton=''){
            
            console.info('filename',filename);
 
             let menuOpciones = $('#nav > li');
             let opcionesVendedor = $('#datosVendedor');
 
             //console.log('capturando las opciones del menu::', menuOpciones);
             menuOpciones.removeClass('active');
             
             if(idBoton=='#iconoVendedor'){  
                 
                 $(idBoton).addClass('active');
                 opcionesVendedor.toggle('fast');
             }

             if(idBoton==''){
                 
                switch(filename){
                    case 'listado-clientes.php':
                        console.log('cliente',$('#iconoCliente'));
                        $('#iconoCliente').addClass('active');
                        break;
                    case 'menu_lista_premios.php':
                        $('#iconoPremios').addClass('active');
                        break;
                    case 'menu-lista-listados.php':
                        $('#iconoListados').addClass('active');
                        break;
                    case 'alta_pedido.php':
                        $('#iconoCarrito').addClass('active');
                        break;
                    case 'alta_devolucion.php':
                        $('#iconoCarrito').addClass('active');
                        break;
                    case 'alta_presupuesto.php':
                        $('#iconoCarrito').addClass('active');
                        break;  
    
                    case 'alta_remito_sistema.php':
                        $('#iconoCarrito').addClass('active');
                        break;  
                    case 'alta_remito_talonario.php':
                        $('#iconoCarrito').addClass('active');
                        break;  
                    case 'alta_presupuesto.php':
                        $('#iconoCarrito').addClass('active');
                        break;  
                    case 'menu-lista-informe-vendedor.php':
                        $('#iconoEstadisticas').addClass('active');
                        break;
                    case 'menu-lista-informe.php':
                        $('#iconoEstadisticasGerenciales').addClass('active');    
                        break;
                    
    
                }
            }
 
         }

         function salida() {
            Swal.fire({
                title: 'Seguro?',
                icon: 'info',
                text: 'Estás seguro que deseas salir?',
                confirmButtonText: 'Si!',
                confirmButtonColor: '#395aa2',
                showCancelButton: true,
                cancelButtonText: 'No'
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    location.href = "salida.inc.php";
                }
    
            });
        }    