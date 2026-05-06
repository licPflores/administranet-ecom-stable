 <?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("../sesion.inc.php");

$sql="";
if(isset($_SESSION['SQL'])===true)$sql=$_SESSION['SQL'];


$sql="SELECT CONCAT(`cliente`.`nombre_cliente`,' (',cliente.codigo,')') AS NCLIENTE,
 ROUND( SUM(`cuentacliente`.`ImporteCobro`),2) as SALDO,
date_format(cuentacliente.Fecha,'%m-%Y')   AS FECHITA
FROM   `administranet`.`cliente` `cliente` 
 INNER JOIN `administranet`.`cuentacliente` `cuentacliente` ON `cliente`.`Codigo`=`cuentacliente`.`Codigo`
WHERE cuentacliente.Fecha  BETWEEN '2018-01-01' AND '2018-10-24' AND
  cuentacliente.TipoComprobante='REC' AND
 (cuentacliente.CondVenta ='Contado' or cuentacliente.CondVenta ='-' ) 
 AND cuentacliente.CodigoMovimiento <> 0 And cuentacliente.Anulado ='No'   
GROUP BY  cliente.Codigo,FECHITA
ORDER BY  cliente.Codigo,FECHITA
";

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
	//print_r(mysqli_fetch_field($result));
	/*
	while ($property = mysqli_fetch_field($result)) {
    $nombre	= $property->name;
    $tipo	= $property->type;
	print "Nombre: ".$nombre." Tipo: ".$tipo."<br>".PHP_EOL;
	}*/
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

	

		

?><html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();

<?php
			while ($property = mysqli_fetch_field($result)) {
    $nombre	= $property->name;
   // $tipo	= $property->type;
	switch ($property->type) {
    case 5:
        $tipo = 'number';
        break;
    case 253:
        $tipo = 'string';
        break;
    case 246:
        $tipo = 'number';
        break;
	 default:
	 $tipo = 'string';
	 break;
}

	//print "Nombre: ".$nombre." Tipo: ".$tipo." | ". $property->type."<br>".PHP_EOL;
	print "data.addColumn('".$tipo."', '".$nombre."');".PHP_EOL;
	}
		
		?>
        data.addRows([
		  <?php
$y=1;
$nRegistros=$result->num_rows;
					 while($row = $result->fetch_array(MYSQLI_NUM)){
					echo '[';

					for($x=0;$x<sizeof($row);$x++){
						
						if(is_numeric($row[$x]))$bandera = 1;
						if(!is_numeric($row[$x]))$bandera = 2;
						
						if($bandera==2)print "'";
									if(is_numeric($row[$x])){
									echo floor($row[$x]);
									}else
									{
									echo $row[$x];	
									}
						if($bandera==2)print "'";
						if($x<(sizeof($row)-1))print ',';
									
}
					echo ']';
					if($y<$nRegistros)echo ',';
					echo "\n";
					$y++;
			}
		
		
		
		  
		  ?>
		  
        ]);

        // Set chart options
        var options = {
			width: '100%',
                       'height':800};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
  </body>
</html>