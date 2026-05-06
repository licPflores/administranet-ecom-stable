<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
?>


<div class='panelesBloqueInforme'>   
<div class="control">
<label for="estadodecheque" class="parametros">Estado:</label>
<select name="estadodecheque" id="estadodecheque">
<option value="Entregado">Entregado</option>
<option value="Rechazado">Rechazado</option>
<option value="En cartera">En cartera</option>
<option value="Depositado" selected>Depositado</option>
<option value="En nd cliente">En nd cliente</option>
<option value="En nd proveedor">En nd proveedor</option>
<option value="Todos" selected>Todos</option>

</select>

    
    
    
</div></div><?php
require_once  "fechabanco.php";
        

