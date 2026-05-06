<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
require_once("../sesion.inc.php");
$nCampos = Array();

if(!isset($_SESSION["columnas"]))die("No tengo columnas");
$columnas = $_SESSION["columnas"];

if(!isset($_SESSION["footer"]))die("No tengo Totales");
$totales = $_SESSION["footer"];

?><!DOCTYPE html>
<html>
  <head>
<link rel="stylesheet" href="../../sistema/_scripts/chartist.min.css">
<script src="../../sistema/_scripts/chartist.min.js"></script>
<script src="../_lib/chartist-plugin-threshold.min.js"></script>
<style>
#grafico0 {
    background-color:#ffffff;
    width: '100%';
    height: 350px;
    font-family: Arial,Lato, Helvetica-Neue, monospace;
}

.ct-series-a .ct-bar {
  /* Colour of your bars */
  stroke:  #2472a4;
  stroke-width: 30;
}

.ct-series-a .ct-bar {
    stroke: #2472a4;
    stroke-width: 45;
}

.ct-label {
  color: #2472a4;
}

</style>	
  </head>
  <div id=grafico0></div>
  <script>
  var data = {
  // A labels array that can contain any sort of values
  labels: [
	  <?php
for($x=2;$x<sizeof($columnas);$x++){
	if($columnas[$x]["title"]!="Id")
		if($columnas[$x]["title"]!="Codigo"){

	if($x<(sizeof($columnas)-1)){
			
			echo "'".$columnas[$x]["title"]."',";
			}else {
			echo "'".$columnas[$x]["title"]."'";	
	}
		
		}
	}
		?>
 
  ],

		
	
		  
  // Our series array that contains series objects or in this case series data arrays
  series: [
    [

	<?php 
		$x=0;
		foreach($totales as $valor){
			
			if(is_numeric($valor))echo $valor;
			if(!is_numeric($valor))echo  "'".$valor."'";
			
		if($x<(sizeof($totales)-1))echo ",";	
			$x++;
		}
		?>
 
	]
  ]
};


var options = {
  seriesBarDistance: 30
};
var responsiveOptions = [
  ['screen and (max-width: 640px)', {
    seriesBarDistance: 5,
    axisX: {
      labelInterpolationFnc: function (value) {
        return value[0];
      }
    }
  }]
];


new Chartist.Bar('#grafico0',data, options, responsiveOptions).on('draw', function(data) {
    if(data.type == 'bar') {
        data.element.animate({
            y2: {
                dur: '3.2s',
                from: data.y1,
                to: data.y2
            }
        });
    }
});

</script>


</html>