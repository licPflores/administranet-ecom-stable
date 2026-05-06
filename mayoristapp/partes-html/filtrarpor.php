<div class="panelesBloqueInforme">

    <div class="separador25px"></div>
    <div class="titulo">
        Filtros
    </div>

    <div class="control w100p">

        <label for="filtrarPor" class="parametros">Tipo:
            <select name="filtrarPor" id="filtrarPor">                
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
        </label>
    </div>

    <div class="control w100p">

        <label for="seleccionFiltro" class="parametros">Valor a filtrar: </label>
        <input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
        <button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fas fa-plus fa-lg fa-fw"></i> </button>
    </div>
    <div class="separador"></div>
    <div class="control w100p">
        <label for="listaFiltro" class="parametros">
            <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
            <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">

        </label>
    </div>

</div>