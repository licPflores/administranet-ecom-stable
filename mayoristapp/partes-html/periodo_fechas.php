<div class="panelesBloqueInforme">
    <label for="campoPeriodo" class="parametros">Período: </label>
        <select name="campoPeriodo" id="campoPeriodo" required="required">
            
    <!--        <option value="dia">Diario</option>
            <option value="semana">Semanal</option>
            <option value="mes" selected="selected">Mensual</option>-->
            <?php  
            foreach ($val["opciones"] as $k => $option) {
                echo '<option value="' . $option["valor"] . '" ';
                if (isset($option["atributo"])) {
                    echo $option["atributo"];
                }
                echo '>' . $option["texto"];
                echo '</option>';
            }
            ?>
        </select>
   </div>