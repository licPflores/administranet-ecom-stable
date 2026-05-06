<?php 
//echo "<pre>";
//print_r($val["opciones"]);
//echo "</pre>";
//foreach($val["opciones"] as $k=>$option){
//                echo "<option ><pre>".print_r($option)."</option><br>";
//            }
?>

<div class="panelesBloqueInforme">
<label for="verInforme" class="parametros">Valores por:</label>
    <select name="verInforme" id="verInforme" required="required">
        <?php
            foreach($val["opciones"] as $k=>$option){
                echo '<option value="'.$option["valor"].'" ';
                if(isset($option["atributo"])){
                    echo $option["atributo"];
                }
                echo '>'.$option["texto"];
                echo '</option>';
            }
        ?>
    </select>
</div>