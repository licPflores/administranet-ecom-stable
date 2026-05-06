<!-- **********header********** -->
<?php require_once('header.php'); ?>


<section class="home" id="home">
	<div class="container">

		<!-- buscador-->
		<div class="row justify-content-center">

			<div class="col-12 col-md-10 col-lg-8" id="alert-content">
			</div>

			<div class="col-12 col-md-10 col-lg-8 buscador" id="content-buscador">

				<nav class="navbar navbar-expand-lg bg-light">
					<div class="container-fluid">
						
						<h4>Inventario</h4>

						<div class="menu-volver d-flex">
							<!--<a class="btn btn-primary" href="../listado-clientes.php" role="button">volver al menu de inicio</a>-->
						</div>

					</div>
				</nav>

				<div class="" id="select-depositos">
					<h6 class="d-none d-sm-block">Selecciona una fecha y deposito</h6>
				</div>

				<div class="row d-none" id="buscadores">

					<div class="col-12 mb-3">

						<div class="row">
							<div class="col-4" id="lector-codigo">
								<div class="d-grid gap-2">
									<button type="button" class="btn btn-primary" id="btn-buscar-codigo" onclick="buscarCodigo()"><i class="bi bi-upc-scan"></i> Lector BT</button>
								</div>
							</div>

							<div class="col-4" id="buscar-rapido">
								<div class="d-grid gap-2">
									<button type="button" class="btn btn-primary" id="btn-buscar-rapido" onclick="buscarRapido()"><i class="bi bi-search"></i> Manual</button>
								</div>
							</div>

							<div class="col-4" id="buscar-por-camara">
								<div class="d-grid gap-2">
									<button type="button" class="btn btn-primary" id="btn-buscar-rapido" onclick="leerCodigo('','busqueda')"><i class="fas fa-camera"></i> Cámara</button>
								</div>
							</div>
						</div>

					</div>

					<!-- <h6>Realiza tu búsqueda o mediante el lector de código de barra.</h6> -->
					
					<div class="col-12" id="buscador-categorias">
						<div class="autocomplete input-group mb-3 active-code" id="autocomplete-group">
							<input id="producto" class="form-control" type="search" name="producto" placeholder="Buscar producto…" autocomplete="off">
							<button id="botonBuscar" class="btn btn-primary" onclick="botonBuscar()" name="botonBusca">
								<i class="bi bi-search"></i> 
								<i class="bi bi-upc-scan"></i> 
								<i class="bi bi-arrow-clockwise d-none" style="display:inline-block;"></i> Buscar
							</button>
						</div>
					</div>

					<!--<div class="col-12 mb-3">

						<div class="row">
							<div class="col-12" id="buscar-rapido">
								<div class="d-grid gap-2">
									<button type="button" class="btn btn-primary" id="btn-buscar-rapido" onclick="leerCodigo('','busqueda')"><i class="fas fa-camera"></i> leer codigo con smartphone</button>
								</div>
							</div>
						</div>

					</div>-->

				</div>
			</div>

			<div class="col-12 col-md-10 col-lg-8 scanner">

				<div class="contenedor-general" id="contenedor-general">

					<div class="contenidos" id="content-general">

						<div id="content-intro"></div>

						<div id="content-form"></div>

						<div id="content-video"></div>

					</div>

				</div>

			</div>

			<div class="clonedInput" id="input1" style="display:none">
				<input type="file" name="image[]" id="image1" style="display:none" />
			</div>

		</div>              

	</div>
</section>

<div class="modal fade modalCrop" id="modal-img-cut"
	tabindex="-1"  
	aria-labelledby="modalLabel" 
	aria-hidden="true"
	data-bs-backdrop="static" 
	data-bs-keyboard="false">

<!--<div class="modal fade modalCrop" id="modal-img-cut" 
	tabindex="-1" 
	aria-labelledby="modalLabel" 
	aria-hidden="true" 
	data-backdrop="static" 
	data-keyboard="false">-->

	<div class="modal-dialog modal-lg modal-fullscreen-sm-down" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cortar la imagen desde la linea punteada</h5>
				<button type="button" class="btn close-button" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
			</div>
			<div class="modal-body">
				<div class="img-container">
					

						
							<div class="cropImageContainer">
								<img src="" id="cut_image" />
							</div>
						
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="crop" class="btn btn-primary">Guardar imagen</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
			</div>
		</div>
	</div>

</div>

<!-- **********footer********** -->
<?php require_once('footer.php'); ?>
