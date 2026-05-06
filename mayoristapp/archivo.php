<? include("conexion.inc.php");
$clave=$_GET["quien"];
$tipo=$_GET["tipo"];

if($tipo=="archivo")
{
	$sql="SELECT  
  				archivo_adjunto as archivo, 
				tipo_adjunto as tipo,
				titulo_adjunto as nombre
		FROM adjuntos
		WHERE id_adjunto=$clave;"; 
}
else
{
	$sql="SELECT  
		foto_adjunto as archivo,
  		tipo_adjunto as tipo,
		titulo_adjunto as nombre
		FROM adjuntos
		WHERE
		id_adjunto=$clave";
}
$hacer=mysql_query($sql) or die("No puedo recuperar el archivo".mysql_error());
$tt=mysql_fetch_array($hacer);





header("Content-type: ".$tt["tipo"]); 
header("Content-Disposition: attachment; filename=".$tt["nombre"]); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
echo $tt["archivo"];	
?>