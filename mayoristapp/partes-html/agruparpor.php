<div class='panelesBloqueInforme'>
    <div class="control">
        <label for="agrupoPor" class="parametros">Informes:  </label>
            <select name="agrupoPor" id="agrupoPor" required="required">
                <option value=""> - seleccionar -</option>
                <?php
                foreach ($val["opciones"] as $k => $option) {
                    echo '<option value="' . $option["valor"] . '" ';
                    if (isset($option["atributo"])) {
                        echo $option["atributo"];
                    }
                    
                    echo ' motor="'.$option["motor"].'"';
                    echo ' >' . $option["texto"];
                    echo '</option>';
                }
                ?>


            </select>
    
    </div>
</div>