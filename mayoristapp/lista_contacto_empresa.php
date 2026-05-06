<?  require_once 'sesion.inc.php';
    require_once 'conexion.inc.php';
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<!-- ActiveWidgets stylesheet and scripts -->
	<link href="grid/runtime/styles/xp/grid.css" rel="stylesheet" type="text/css" ></link>
    <link href="_css/main_styles.css" rel="stylesheet" type="text/css" />
	<script src="grid/runtime/lib/grid.js"></script>
	<script src="grid/patches/paging1.js"></script>

	<!-- grid format -->

<style>
		.active-controls-grid {width:700px;height: 390px; font: menu;background: white; border:1px solid #000000}

		.active-column-0 {width: 60px;background: white;}
                .active-column-1 {width: 50px;background: white;}
		.active-column-2 {width: 180px;background: white;}
		.active-column-3 {width: 200px;background: white;}
                .active-column-4 {width: 200px;background: white;}
                .active-column-5 {width: 200px;background: white;}
		.active-column-6 {width: 200px;background: white;}
		.active-column-7 {width: 0px;background: white;}


		.active-grid-column {border-right: 1px solid threedlightshadow;}
		.active-grid-row {border-bottom: 1px solid threedlightshadow;}
		.active-box-resize.gecko {font-size: 1px;}

	</style>
<script type="text/javascript" >

	function pasarvalor(valor)
	{
		document.getElementById("quien").value=valor;
	}

	function modificar()
	{
		var quien=document.getElementById("quien");
		if(quien.value=="")
		{
			alert("Debe seleccionar un contacto de empresa");
			return
		}else
		{
			//alert(document.getElementById("quien").value)
			var clave=document.getElementById("quien").value;
																				//window.open("modcontenido.php?clave="+document.getElementById("quien").value,"pepe","resizable=no,scrollbars=yes,status=no,width=670,height=500");
			vuelta=window.showModalDialog("mod_contacto_empresa1.php?clave="+document.getElementById("quien").value,"","help:no;status:no;dialogHeight:550px;dialogWidth:450px;");
			//window.open("mod_representacion1.php?clave="+clave,"pepe","resizable=no,scrollbars=yes,status=no,width=670,height=550");

			location.href="lista_contacto_empresa.php";
		}
	}



	function eliminar()
	{
		if(document.getElementById("quien").value=="")
		{
			alert("Debe seleccionar un contacto de la empresa");
			return
		}else
		{
			if(window.confirm("¿Esta seguro que desea eliminar el contacto de la empresa?")==true)
			{
				//var clave=document.getElementById("clave");

				location.href="lista_contacto_empresa.php?eliminar=si&quien="+document.getElementById("quien").value;
			}
		}
	}
	function alta()
	{
		//var clave=document.getElementById("clave");
		//window.open("alta_popup.php","pepe","resizable=no,scrollbars=yes,status=no,width=670,height=500");
		vuelta=window.showModalDialog("alta_contacto_empresa1.php","","help:no;status:no;dialogHeight:350px;dialogWidth:400px;");
		//window.open("alta_seccion1.php","pepe","resizable=no,scrollbars=yes,status=no,width=670,height=500");
		location.href="lista_contacto_empresa.php";
		//resizable=no,scrollbars=no,status=no,width=700,height=500
	}
	function pasar(valor)
	{


	}
</script>
<?
if($_GET["eliminar"]=="si")
{
	# eliminar primero los archivos que hubiere y luego el popup
	$quien=$_GET["quien"];
	# eliminando los archivos
	$sqle="DELETE FROM empresa WHERE id_empresa=$quien";
	$hacer=mysql_query($sqle) or die("No puedo eliminar el contacto de empresa".mysql_error());
	
}
include("php/activewidgetsp.php");

?>

<title>Documento sin t&iacute;tulo</title>
</head>



<body >
<div>
<h1 style="text-align:left">Contactos Empresa</h1>
</div>
  <div class="buscador clear">
    <div class="icons">
        <input type="hidden" name="quien" id="quien">
    </div>

    <div class="icons">

        <p><a onClick="alta()" style="cursor:pointer"   title="Nueva"><img src="botones/btn_nuevo_24px.gif" alt="nueva " width="24" height="24" ></a> </p>

    </div>
    <div class="icons">
        <p><a onClick="modificar()" style="cursor:pointer" title="Modificar"><img src="botones/btn_editar_24px.gif" alt="Modificar " width="24" height="24" ></a></p>
    </div>
     <div class="icons">
        <p><a onClick="eliminar()" style="cursor:pointer"   title="Eliminar"><img src="botones/btn_borrar_24px.gif" alt="Eliminar" width="24" height="24" ></a></p>
    </div>

</div>



<div id="colPrincipal" >
<?
  $sql="SELECT
                id_empresa AS Codigo,
                orden_empresa AS Orden,
                nombre_empresa AS Nombre,
                cargo_empresa AS Cargo,
                cargo_empresa_en AS Cargo_Ing,
                telefono_empresa AS Telefono,
                mail_empresa AS Mail

        FROM empresa
		
		ORDER BY empresa.id_empresa DESC";


	$id=0;
	$columna=1;
	$name="obj";
	$condicion=" qty == 0";
	//echo $sql;
	//$sql="select * from Articulo limit 0,20";
	$data=mysql_query($sql) or die ("no puedo traer los contactos de empresa".mysql_error());
	$hay2=mysql_num_rows($data);
	if($hay2==0)
	{

		echo "No se encontraron contactos de la empresa";
	}
	else
	{

		echo activewidgets_grid($name, $data,$columna,$condicion,$id);

	}
?>

</div>

</body>
</html>
