
<div class='panelesBloqueInforme'>
<div class="titulo">
    Punto de Venta
</div>
<div class="control w100p">
    <label for="puntoVenta" class="parametros">P venta:</label>
        <select name="puntoVenta" id="puntoVenta">
            <option value="|Todos"> - todos - </option>
            <?php echo $_SESSION["lista_pv_opc"]; ?>
        </select>
    
    <button name="addPv" id="addPv" type="button" class="botonNuevo chico azul"><i class="fas fa-plus fa-lg fa-fw"></i> </button>

</div>
</div>

<div class='panelesBloqueInforme'>
<div class="control w100p">
    <label for="listaPv" class="parametros">
        <ul name="listaPv" id="listaPv" class="listaSeleccionado"></ul>
        <input type="hidden" name="pvSelec" id="pvSelec" value="" required="required">

    </label>
</div>
</div>