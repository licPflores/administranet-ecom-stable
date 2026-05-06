$.ajax({
    url: "ajax/stock-backend.php",
    type: "GET",
    data: {
        traeMisDatos: 1,
      },
    dataType: 'json',
    success: function(data) {
        // hacer tu logica ya teniendo la informacion del json
        datosUsuario = data;

        console.log(datosUsuario);

        if(datosUsuario.msg=='ok'){
            createHtmlContent({
                target: '#menuGeneral',
                html: `
                    <li class="nav-item">
                        <a class="nav-link" id="nav-item-user" href="#"
                            data-bs-html="true"
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="${datosUsuario.usuario.puesto}"
                            title="${datosUsuario.usuario.puesto}"
                        >
                            <i class="fa-solid fa-image-portrait d-none d-sm-none d-md-block"></i>
                            <span class="d-sm-block d-md-none sm-mode">${datosUsuario.usuario.nombre_usuario.charAt(0).toUpperCase()}${datosUsuario.usuario.apellido_usuario.charAt(0).toUpperCase()}</span>
                            <span class="d-none d-sm-none d-md-block"> ${datosUsuario.usuario.nombre_usuario} ${datosUsuario.usuario.apellido_usuario}</span>
                        </a>
                    </li>
                    `,
            })
        };

        if(datosUsuario.msg=='sinSesion'){
            Swal.fire({
                title: 'Sin sesion!',
                text: search.mensaje,
                icon: 'info',
                confirmButtonText: 'Salir',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href ="../salida.inc.php";
                } else if (result.isDenied) {
                    location.href ="../salida.inc.php";
                }
              });
        };
    },
    error: function(data) {
        // logica si falla la carga
        datosUsuario = data;
        console.log(datosUsuario);
        /*Swal.fire({
            title: 'Error!',
            text: datosUsuario.mensaje,
            icon: 'error',
            confirmButtonText: 'Salir',
            allowOutsideClick: false
        });*/
    }
});