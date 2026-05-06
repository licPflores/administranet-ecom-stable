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


.ct-line.ct-threshold-above, .ct-point.ct-threshold-above, .ct-bar.ct-threshold-above {
  stroke: #2472a4;
}

.ct-line.ct-threshold-below, .ct-point.ct-threshold-below, .ct-bar.ct-threshold-below {
  stroke: #f51b05;
}

.ct-area.ct-threshold-above {
  fill: #f05b4f;
}

.ct-area.ct-threshold-below {
  fill: #59922b;
}

.ct-series-a .ct-bar.ct-threshold-above {
  stroke: #f05b4f;
}

.ct-series-a .ct-bar.ct-threshold-below {
  stroke: #59922b;
}

</style>	
  </head>
  <div class="ct-chart" id=grafico0></div>
  <script>
  var chart = new Chartist.Line('.ct-chart',	{
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
?>],
series: [
    [

	<?php 
		$x=0;
		foreach($totales as $valor){
			if($x>0){
			if(is_numeric($valor))echo intval($valor);
			if(!is_numeric($valor))echo  "'".$valor."'";
			
		if($x<(sizeof($totales)-1))echo ",";
			}
			$x++;
		}
		?>
 
	]
  ]
  },{
  showArea: true,
  axisY: {
    onlyInteger: true
  },
  plugins: [
    Chartist.plugins.ctThreshold({
      threshold: 4
    })
  ]
}
  
  );


  
  
// Let's put a sequence number aside so we can use it in the event callbacks
var seq = 0;

// Once the chart is fully created we reset the sequence
chart.on('created', function() {
  seq = 0;
});

// On each drawn element by Chartist we use the Chartist.Svg API to trigger SMIL animations
chart.on('draw', function(data) {
  if(data.type === 'point') {
    // If the drawn element is a line we do a simple opacity fade in. This could also be achieved using CSS3 animations.
    data.element.animate({
      opacity: {
        // The delay when we like to start the animation
        begin: seq++ * 80,
        // Duration of the animation
        dur: 50,
        // The value where the animation should start
        from: 0,
        // The value where it should end
        to: 1
      },
      x1: {
        begin: seq++ * 80,
        dur: 50,
        from: data.x - 10,
        to: data.x,
        // You can specify an easing function name or use easing functions from Chartist.Svg.Easing directly
        easing: Chartist.Svg.Easing.easeOutQuart
      }
    });
  }
});

// For the sake of the example we update the chart every time it's created with a delay of 8 seconds
chart.on('created', function() {
  if(window.__anim0987432598723) {
    clearTimeout(window.__anim0987432598723);
    window.__anim0987432598723 = null;
  }
  window.__anim0987432598723 = setTimeout(chart.update.bind(chart), 2000);
});


</script>


</html>