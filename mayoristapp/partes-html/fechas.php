
<div class='panelesBloqueInforme'>


    <div class="titulo">
        Fechas
    </div>
   

        <div id="buscaFecha"  class="control w100p" >
            <label>Primario </label><br>    
            <label for="fechaDesde" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaDesde" id="fechaDesde" required="required" value="<?php //echo date('Y-m-d', strtotime('-1 years')); ?>"> </label>al 

            <label for="fechaHasta" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaHasta" id="fechaHasta" required="required" value="<?php //echo date('Y-m-d'); ?>" ></label>
        </div>
  

  
        <div id="buscaFecha"  class="control w100p">
            <label>Secundario</label><br>
            <label for="fechaDesdeDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaDesdeDos" id="fechaDesdeDos"  ></label> al

            <label for="fechaHastaDos" class="parametros"><i class="fa fa-calendar fa-lg fa-fw"></i> <input type="date" name="fechaHastaDos" id="fechaHastaDos" ></label>
        </div>

   


</div>