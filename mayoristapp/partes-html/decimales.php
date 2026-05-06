<div class="panelesBloqueInforme">

<div class="control">
    <label for="decimales" class="parametros">Decimales:</label>
        <select name="decimales" id="decimales" required="required">
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
    </div>