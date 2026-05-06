<?php
	
	//Defines, libreria y classes del panel

	// require_once('includes/defines.inc.php');
	// require_once('includes/sesion.inc.php');
	// require_once('includes/conex.inc.php');
	//require_once('../sesion.inc.php');
	//require_once('includes/rubros-subrubros.php');
	//require_once('includes/mas-vendidos.php');

	//require_once('includes/header-inicial.php');

?>

<?php
	/*
    $iconoSuper = "fa-user";
    $tituloPuesto = "Vendedor";
    if ($objVendedor->id_puesto == 1) {
        //supervisor
        $iconoSuper = "fa-users";
        $tituloPuesto = "Supervisor de Ventas";
    } else {
        // veo el permiso
        if ($objVendedor->permiso_supervisor_venta == 'Si') {
            $iconoSuper = "fa-users";
            $tituloPuesto = "Supervisor de Ventas";
        }
    }
	*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "DTD/xhtml1-strict.dtd">

<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang=""> <!--<![endif]-->

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php //echo NOMBRETIENDA; ?></title>

		<meta name="description" content="<?php echo TIENDADESCRIPCION; ?>" />
		<meta name="keywords" content="<?php echo TIENDAKEYWORDS; ?>" />
		<meta name="language" content="ES" />
		<meta name="geo.region" content="<?php echo GEOREGION;?>" />
		<meta name="geo.placename" content="<?php echo GEOPLACENAME;?>" />
		<meta name="geo.position" content="<?php echo GEOPOSITION;?>" />
		<meta name="ICBM" content="<?php echo GEOPOSITION;?>" />
		<meta name="author" content="administraNET" />

		<!-- administraNET -->
		<meta property="og:type" content="website" />
		<meta property="og:title" content="administraNET" />
		<meta property="og:description" content="Software de Facturación Electrónica y Stock Gratis - ERP - E-commerce - Factura Electrónica Gratis Free – Punto de Venta – Mendoza - Argentina" />
		<meta property="og:image" content="http://administranet.com.ar/images/header-small-grande-afip.png" />
		<meta property="og:url" content="http://administranet.com.ar/index.php" />
		<meta property="og:site_name" content="administranet.10" />
		<meta name="twitter:card" content="administraNET" />
		<meta name="twitter:site" content="@adm_gestion" />
		<meta name="twitter:creator" content="@adm_gestion" />

		<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "SoftwareApplication",
			"name": "administraNET",
			"operatingSystem": "WINDOWS",
			"applicationCategory": "https://schema.org/SoftwareApplication",
			"downloadUrl": "https://administranet.com.ar/descargas-archivos.php",
			"screenshot":"https://www.administranet.com.ar/images/screenshots/17-screen_abmproducto.png",
			"image":"https://administranet.com.ar/images/header-pv-grande.png",
			"description": "Software de Facturación Electrónica y Stock Gratis - ERP - E-commerce - Factura Electrónica Gratis Free – Punto de Venta – Mendoza - Argentina",
			"url":"https://administranet.com.ar",
			"aggregateRating": {
				"@type": "AggregateRating",
				"ratingValue": "5",
				"ratingCount": "10000"
			},
			"offers": {
				"@type": "Offer",
				"price": "100000",
				"priceCurrency": "ARS",
				"priceValidUntil": "2020-05-31"
			}
		}
		</script>

		<?php //require_once 'analyticstracking.php';?>

		<script src="https://kit.fontawesome.com/75ecccb04e.js" crossorigin="anonymous"></script>
		<!-- fin Administranet -->

		<!--  bootstrap  -->
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

		<!--  slick  -->
		<link rel="stylesheet" href="css/slick/slick.css">

		<!--  fancybox  -->
		<link rel="stylesheet" href="css/jquery.fancybox.css">

		<!--  NV css  -->
		<link rel="stylesheet" href="css/style-carousel.css">
		<link rel="stylesheet" href="css/style-footer.css">
		<link rel="stylesheet" href="css/style-header.css">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/font.css">
		<link rel="stylesheet" href="css/cropper.css">
	</head>

	<body>

		<header class="header-web container" id="header-web">

			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container-fluid">

					<a class="navbar-brand" href="index.php">
						<img id="imgLogo" src="../foto.php?origen=logo|0" alt="<?php //echo $_SESSION['nombre_empresa']; ?>" title="<?php //echo $_SESSION['nombre_empresa']; ?>" />
					</a>

				<!--
					<button class="navbar-toggler" 
						data-bs-toggle="collapse" 
						data-bs-target="#menuHeader">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="menuHeader">
						<div class="menu-web">
							<ul class="navbar-nav" id="menuGeneral">
								
								<a class="btn btn-primary btn-sm btn-inicio" href="../listado-clientes.php" role="button">volver al menu de inicio</a>

							</ul>
						</div>
					</div>
				-->

					<ul class="nav justify-content-end" id="menuGeneral">
						<li class="nav-item">
							<a class="nav-link" href="../listado-clientes.php" ><i class="fa-solid fa-house"></i><span class="d-none d-sm-none d-md-block"> Inicio</span></a>
						</li>
					</ul>

				</div>
			</nav>
			
		</header>

		<!-- Modal -->
		<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title fs-5" id="aboutModalLabel">Acerca de administraNET</h1>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="about">
							<div class="logo-about">
								<a class="navbar-brand" href="index.php"><img src="img/logo-administranet-footer.png"></a>
							</div>

							<div class="legales">
								<div><?php //echo date("Y", time()); ?> AdministraNET - Reservados todos los derechos.</div>
								<div>Queda prohibida la copia, utilización de este contenidos sin el consentimiento previo y explicito </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>