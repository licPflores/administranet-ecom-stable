<link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
<?php if($caminoDispo!=""):?>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
<?php endif;?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <link rel="preconnect" href="https://fonts.googleapis.com"> -->
<!-- <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> -->
<!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet"> -->

<link rel="stylesheet" type="text/css" href="<?php echo $caminoDispo;?>_css/font.css"  />

<link rel="stylesheet" type="text/css" href="<?php echo $caminoDispo;?>_css/main_styles.css"  />
<link rel="stylesheet" type="text/css" href="<?php echo $caminoDispo;?>_css/productos.css"  />


<link rel="stylesheet" type="text/css" media="screen, projection" href="<?php echo $caminoDispo;?>_css/jcart.css" />
<link rel='stylesheet' type='text/css' media='screen' href='<?php echo $caminoDispo;?>_css/basic.css'   />


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" type='text/javascript'></script>
<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> -->
<!-- 2.x snippet:
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
1.x snippet:
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script> -->

<!-- data tables 1.10
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.css"/> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-flash-1.5.1/b-html5-1.5.1/r-2.2.1/datatables.min.js"></script> -->
<!-- data tables 1.11 -->
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/> 
<script type="text/javascript" src="//cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<link href="<?php echo $caminoDispo;?>_css/tablas.css" rel="stylesheet" type="text/css" />


<script type='text/javascript' src='<?php echo $caminoDispo;?>_lib/jquery.simplemodal.js'></script>

<?php if(isset($_SESSION['formulario'])):?>
    <?php if($_SESSION['formulario']=='remitoSistema'||$_SESSION['formulario']=='remitoTalonario' ):?>
    
        <!-- <script type="text/javascript" src="<?php echo $caminoDispo;?>_scripts/jcart-rem.js"></script> -->
        <script type="text/javascript" src="_scripts/jcart-rem.js"></script>

    <?php else: ?>
        <!-- <script type="text/javascript" src="<?php echo $caminoDispo;?>_scripts/jcart.js"></script> -->
        <script type="text/javascript" src="_scripts/jcart.js"></script>

    <?php endif;?>
    
<?php else:?>
    <!-- <script type="text/javascript" src="<?php echo $caminoDispo;?>_scripts/jcart.js"></script> -->
    <script type="text/javascript" src="_scripts/jcart.js"></script>

<?php endif;?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script>var soyMobil="<?php echo $soyMovil;?>";</script>
<!-- funciones generales de js para todos -->
<script src="_scripts/principal.js"></script>

<!-- busqueda rapida general css -->
<link rel="stylesheet" href="_css/busqueda-rapida.css" />
<!-- acciones de carrito para comprobantes -->
<script src="_scripts/carrito.js"></script>
<!-- busqueda rapida acciones -->
<script src="_scripts/busqueda-rapida.js"></script>
<!-- stilo de estadisticas pero de la cabecera del articulo -->
<link rel="stylesheet" href="_css/menu-cliente.css">

<!-- acciones de cliente tablero -->
<script src="_scripts/acciones-panel-cliente.js"></script>

<script>

$(document).ready(function(){
    
   
// console.log('que viene del cliente',obtenerClienteSeleccionado());
    console.log('soy mobile=>',soyMobil);
    inicioAutoCompletar("articulo");

    
  //panel de busca rapida
    // $(".buscador").show();
    $("#contiene-tabla-comprobante").show();
    $("#panelBuscaRapido").show();

    if(soyMobil=='Si'){
        $(".headerCliente").hide();
        $("#sidebar").hide();
    }

    // if(soyMobil=='No'){
    //     console.log('soy desktop');
    //     $(".headerCliente").show();
    //     $("#sidebar").show();
    // }
    
    
    // geo localizacion.
    //=================
    // consulta por si esta activado geolocalizacion..
    <?php if(isset($_SESSION["usa_geolocalizacion"])&& $_SESSION["usa_geolocalizacion"]=="Si"):?>
    if (navigator.geolocation){
//        navigator.geolocation.getCurrentPosition(showPosition);
        navigator.geolocation.getCurrentPosition(onSuccessGeolocating,
                                         onErrorGeolocating,
                                         {
                                       		enableHighAccuracy: true,
                                       		maximumAge:         5000,
                                       		timeout:            10000
                                         });
                
    }else{ 
        console.log("Geolocation is not supported by this browser.");
    }
   
    <?php endif;?>

    


    
   
    
    // buscar por::
    $('input[name=queCampo]').hide();
    
    $('label[name=queCampoLabel]').on("click",function(){
        //console.log($(this).attr("for"));
        //console.log($(this).children("i"));
        $('input[name=queCampo]').prop("checked",false);
        $('label[name=queCampoLabel]>i').removeClass('fa-check-square');
        $('label[name=queCampoLabel]>i').removeClass('fa-square');
        $('label[name=queCampoLabel]>i').addClass('fa-square');
        var opcion=$($(this).attr("for")),
            i=$(this).children("i");
//        console.log(opcion);
//        console.log(i);
//        if(opcion.ch)
        opcion.prop("checked",true);
        i.removeClass('fa-square').addClass('fa-check-square');
        
        //fa-square
        //var queOpcion=$(this).attr("for");        
    });
    
    
    // busqueda con enter.
    $('#formBusca').on("submit",function(){
        console.log('formbusca click');
        event.preventDefault();
    });
    
   

        $("input[type=number]").on("click",function() {
            console.log("click en input" +$(this));
           $(this).select(); 
        });

        // $('#nombreBuscaRapido').click();
        $('#nombreBuscaRapido').focus();
});


    // let objCliente = document.getElementsByClassName('nombreCliente');
    // console.log('nombre del cliente a capturar',objCliente[0].textContent);
</script>