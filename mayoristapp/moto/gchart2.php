<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("../sesion.inc.php");

$sql="";
if(isset($_SESSION['SQL'])===true)$sql=$_SESSION['SQL'];

if(empty($sql))$sql="SELECT IDArt AS Codigo , NombreArticulo AS NCLIENTE, PrecioCosto AS SALDO, CONCAT('$',FORMAT(PrecioCosto,'C','es_AR')) AS DINERO
 FROM articulo  order by RAND() limit 40;";


function connectDB(){
    global $servidor;
    global $baseConecto;
    
        $server = $servidor;
        $user = "administranet";
        $pass = "a7v8xx0805";
        $bd = $baseConecto;

    $conexion = mysqli_connect($server, $user, $pass,$bd);

        if($conexion){
    //        json_encode( 'La conexion de la base de datos se ha hecho satisfactoriamente');
        }else{
            json_encode( 'Ha sucedido un error inexperado en la conexion de la base de datos');
			return false;
        }

    return $conexion;
}

function disconnectDB($conexion){

    $close = mysqli_close($conexion);

        if($close){
         //   echo 'La desconexion de la base de datos se ha hecho satisfactoriamente';
        }else{
       //     echo 'Ha sucedido un error inexperado en la desconexion de la base de datos';
        }   

    return $close;
}


    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    $contar--;
	//print_r(mysqli_fetch_field($result));/*
	while ($property = mysqli_fetch_field($result)) {
    $nombre	= $property->name;
    $tipo	= $property->type;
	//print "Nombre: ".$nombre." Tipo: ".$tipo."<br>".PHP_EOL;
	}
	/*/
	/*
	switch ($i) {
    case 0:
        echo "i es igual a 0";
        break;
    case 1:
        echo "i es igual a 1";
        break;
    case 2:
        echo "i es igual a 2";
        break;
			}*/

	

if(!isset($_SESSION["columnas"]))die("No tengo columnas");
$columnas = $_SESSION["columnas"];

if(!isset($_SESSION["footer"]))die("No tengo Totales");
$totales = $_SESSION["footer"];

?><html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
<?php
for($x=1;$x<sizeof($columnas);$x++){
	if($columnas[$x]["title"]!="Id")
	if($columnas[$x]["title"]!="Codigo"){
		$valor= $totales[ $columnas[$x]["title"] ];
		if(!is_numeric($valor))$valor="'".$valor."'";
		echo "['".$columnas[$x]["title"]."',".$valor."],\n";
	}
}
		?>
]);

        var options = {
          is3D: true,
		  pieSliceText: 'label',
		  width: '100%',
          height:450,
		       animation:{
        duration: 1000,
        easing: 'out'
      }
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="piechart_3d"></div>
  </body>
</html>