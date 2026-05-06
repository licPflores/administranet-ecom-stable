function mostrarContenido(id){
        var pagina="";
        switch(id){
            case 14:
                pagina="index.php";
                break;
            case 15:
                pagina="seccion_contenido.php";
                break;
            case 16:
                pagina="seccion_representaciones.php";
                break
            case 17:
                pagina="contacto.php";
                break;
        }
        location.href=pagina+'?seccion='+id+"&language="+varPre;
    }
function mostrarContenidoI(id,idioma){
        var pagina="";
        switch(id){
            case 14:
                pagina="index.php";
                break;
            case 15:
                pagina="seccion_contenido.php";
                break;
            case 16:
                pagina="seccion_representaciones.php";
                break
            case 17:
                pagina="contacto.php";
                break;
        }
        location.href=pagina+'?seccion='+id+"&language="+idioma;
    }
    function mostrarArticulo(id){
        var pagina="seccion_articulo.php";
        switch(id){
            case 19:

                break;
            case 20:

                break;
            case 21:

                break;
        }
        location.href=pagina+'?seccion='+id+"&language="+varPre;
    }

function filtraRubro(rubro,seccion){
    location.href="seccion_articulo.php?seccion="+seccion+"&rubro="+rubro+"&language="+varPre;
}
function filtraSubrubro(rubro,subrubro,seccion){
    location.href="seccion_articulo.php?seccion="+seccion+"&rubro="+rubro+"&subrubro="+subrubro+"&language="+varPre;
}
