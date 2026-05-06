<?php if(defined('Filtros')===false)define("Filtros",'no');?> 
  <?php if(Filtros!='Si'){ echo '<!--'; }?>
  
  <div class='panelesBloqueInforme'>
<div class="control w100p">
	<label for="listaPv" class="parametros">
		<ul name="listaPv" id="listaPv" class="listaSeleccionado"></ul>
		<input type="hidden" name="pvSelec" id="pvSelec" value="" required="required">

	</label>
</div>
</div>
  <?php if(Filtros!='Si'){ echo '-->'; }?>