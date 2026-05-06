<link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
<?php if($caminoDispo!=""):?>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
<?php endif;?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet"> -->
<!-- css de fuentes -->
<link href="<?php echo $caminoDispo;?>_css/font.css" rel="stylesheet" type="text/css" />
<!-- css principal  -->
<link href="<?php echo $caminoDispo;?>_css/main_styles.css" rel="stylesheet" type="text/css" />
<!-- font awesome kit  -->
<!-- <script src="https://kit.fontawesome.com/75ecccb04e.js" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script> -->

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type='text/javascript'></script>-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


<?php if($uTablas==1):?>

<!-- <link href="<?php echo $caminoDispo;?>_css/tablas.css" rel="stylesheet" type="text/css" /> -->


<!-- 2023 relativo -->
<!-- <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/> 
<script type="text/javascript" src="//cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> -->

<!-- 2023 nov  -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />  
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
<link href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/fc-4.3.0/fh-3.4.0/r-2.5.0/datatables.min.css" rel="stylesheet">
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/fc-4.3.0/fh-3.4.0/r-2.5.0/datatables.min.js"></script>

<!-- datatables 2024 - v2  - no compatible con informes -->

<!-- <link href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.1.8/b-3.1.2/b-html5-3.1.2/fh-4.0.1/r-3.0.3/datatables.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.1.8/b-3.1.2/b-html5-3.1.2/fh-4.0.1/r-3.0.3/datatables.min.js"></script> -->

<?php endif;?>

<link href='<?php echo $caminoDispo;?>_css/basic.css' rel='stylesheet' type='text/css' media='screen'    />
<script src='<?php echo $caminoDispo;?>_lib/jquery.simplemodal.js' type='text/javascript' ></script>


<?php if($uGui==1):?>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
  
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<?php endif;?>



<!--sweet alert DOS-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

<!-- funciones generales de js para todos -->
<script src="_scripts/principal.js"></script>


<!-- busqueda rapida general -->
<link rel="stylesheet" href="_css/busqueda-rapida.css" />
<script src="_scripts/busqueda-rapida.js"></script>
<!-- stilo de estadisticas pero de la cabecera del articulo -->
<link rel="stylesheet" href="_css/menu-cliente.css">
<!-- acciones de cliente tablero -->
<script src="_scripts/acciones-panel-cliente.js"></script>

<script>


jQuery(document).ready(function($){
  ///var laSesion =   obtenerUsuarioLogueado();
    
        // * cliente seleccionado ocultar busqueda rapida.
        <?php if (isset($_SESSION['cliente'])) : ?>
            $('#buscador-cliente').hide();
        <?php endif; ?>    

        <?php if ($caminoDispo != "") : ?>
            
            $("#sidebar").hide();
        <?php endif; ?>
    
});
</script>


