<? require_once 'sesion.inc.php';
   require_once 'conexion.inc.php';

if(isset($_GET["clave"])){
	$clave=$_GET["clave"];
}
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
		.active-controls-grid {width:320px;height: 300px; font: menu;background: white; border:1px solid #000000}

		.active-column-0 {width: 60px;background: white;}
		.active-column-1 {width: 200px;background: white;}
                .active-column-2 {width: 200px;background: white;}
		.active-column-3 {width: 0px;background: white;}
		.active-column-4 {width: 0px;background: white;}

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
		if(document.getElementById("quien").value=="")
		{
			alert("Debe seleccionar un subrubro ");
			return
		}else
		{
			//alert(document.getElementById("quien").value)
			//window.open("modcontenido.php?clave="+document.getElementById("quien").value,"pepe","resizable=no,scrollbars=yes,status=no,width=670,height=500");
			vuelta=window.showModalDialog("mod_subrubro1.php?clave="+document.getElementById("quien").value,"","help:no;status:no;dialogHeight:350px;dialogWidth:400px;");
			location.href="cuerpo_subrubro.php?clave=<?=$clave;?>";
		}
	}



	function eliminar()
	{
		if(document.getElementById("quien").value=="")
		{
			alert("Debe seleccionar un sub rubro");
			return
		}else
		{
			if(window.confirm("¿Esta seguro que desea eliminar el subrubro?")==true)
			{
				location.href="cuerpo_subrubro.php?clave=<?=$clave?>&eliminar=si&quien="+document.getElementById("quien").value;
			}
		}
	}
	function alta()
	{
		vuelta=window.showModalDialog("alta_subrubro1.php?clave=<?=$clave;?>","","help:no;status:no;dialogHeight:350px;dialogWidth:400px;");
		//window.open("alta_seccion1.php","pepe","resizable=no,scrollbars=yes,status=no,width=670,height=500");
		location.href="cuerpo_subrubro.php?clave=<?=$clave?>";
		//resizable=no,scrollbars=no,status=no,width=700,height=500
	}
	function pasar(valor)
	{


	}
</script>
<?
if($_GET["eliminar"] == "si"){
    /*
     *  buscando que no existan articulos con el subrubro cargado..
     */
    $sql = "SELECT COUNT(id_popup)as cuantos FROM popups WHERE id_subrubro=" . $_GET["quien"];
    $res = mysql_query($sql) or die("no puedo encontrar los articulos" .mysql_error());
    $hay = mysql_fetch_array($res);
    if($hay["cuantos"]>0){
        $cartel = 1;
    }
    else
    {
        $sqld = "DELETE FROM subrubro WHERE id_subrubro=".$_GET["quien"];
        $hacerlo = mysql_query($sqld) or die("No puedo dar de baja el subrubro".mysql_error());
        $cartel = 2;
    }

}

include("php/activewidgetsp.php");?>

<title>Documento sin t&iacute;tulo</title>
</head>



<body topmargin="0" leftmargin="0" rightmargin="0" bottommargin="0">
<div>
<h2 style="text-align:left">Sub Rubros</h2>
</div>
  <div class="buscador clear">
    <div class="icons">

        <input type="hidden" name="quien" id="quien">
    </div>

    <div class="icons">
        <p><a onClick="alta()" style="cursor:pointer"   title="Nueva"><img src="botones/btn_nuevo_24px.gif" alt="nueva " width="24" height="24" ></a></p>
    </div>
    <div class="icons">
        <p><a onClick="modificar()" style="cursor:pointer" title="Modificar">  <img src="botones/btn_editar_24px.gif" alt="Modificar " width="24" height="24" ></a></p>
    </div>
     <div class="icons">
        <p><a onClick="eliminar()" style="cursor:pointer"   title="Eliminar"><img src="botones/btn_borrar_24px.gif" alt="Eliminar" width="24" height="24" ></a></p>
    </div>

</div>



<div id="colPrincipal" >
<?
  $sql="SELECT
  subrubro.id_subrubro as Codigo,
  subrubro.nombre_subrubro as SubRubro,
 subrubro.nombre_subrubro_en as SubRubroIng
    	FROM subrubro
	WHERE id_rubro=$clave
   ORDER BY id_subrubro DESC";

    $id=0;
    $columna=1;
    $name="obj";
    $condicion=" qty == 0";
    //echo $sql;
    //$sql="select * from Articulo limit 0,20";
    $data=mysql_query($sql) or die ("no puedo traer los subrubros".mysql_error());
    $hay2=mysql_num_rows($data);
    if($hay2==0){

        echo "No se encontraron Sub Rubros";
    }
    else{

        echo activewidgets_grid($name, $data,$columna,$condicion,$id);

    }
?>
	
</div>

</body>
</html>
<? if(isset($cartel)){
    switch($cartel){
        case 1:
            echo "<script>alert('No se puede eliminar el sub rubro, contiene ARTICULOS cargados');</script>";
            break;
        case 2:
            echo "<script>alert('Operacion Realizada con exito')</script>";
            break;
    }
}

?>
