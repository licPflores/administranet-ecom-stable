<div class="easyui-navpanel titulo-recibo-gradiante" id="panelCuerpoTarjetasCredito">
        <header class=" titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title" style=" padding-left: 30px;"><i class="far fa-credit-card fa-lg fa-fw"></i> Tarjeta de crédito - débito</div>
                <div class="m-left">
                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true"  onclick="$.mobile.go('#panelMediosCobro','slide','left');"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>
                </div>

            </div>

        </header>
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"><i class="titulo-recibo-alt">$</i><span id="totalReciboPanelTarjeta" class=".titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="a-cubrir-alt">$</i><span id="aCubrirPanelTarjeta" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>

                <div class="hijo-estado">
                    <p class="texto-estado total-tarjeta"><i class="tarjeta-alt">$</i><span id="totalAltaTarjeta" class="tarjeta-alt">0.00</span>
                        <br>
                        Total tarjeta
                    </p>
                </div>
            </div>
            <div style="height:70%;">
                <h3 class="titulo-medios-cobro tarjeta-alt">Listado de tarjetas de crédito - débito </h3>
                <div>
                    <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="mostrar_panel_alta_tarjeta();"><i class="fas fa-plus fa-lg fa-fw"></i> Nuevo </a>
                    <a href="javascript:void(0)" class="easyui-linkbutton secundario" onclick="borrar_tarjeta();"><i class="fas fa-trash fa-lg fa-fw"></i> Borrar </a>
                </div>
                <table id="tblTarjetasCredito"></table>
            </div>



            <!-- <div style="padding:10px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$.mobile.back();" style="width:50%"><i class="fas fa-arrow-left fa-fw fa-lg"></i> Volver a medios de cobro</a>
            </div>    -->
        </div>


    </div>
 <!-- ALTA TARJETAS CREDITO / DEBITO  -->
 <div class="easyui-navpanel titulo-recibo-gradiante" id="panelAltaTarjeta">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">

                <div class="m-left">

                    <!-- <a href="javascript:void(0)" class="easyui-menubutton" data-options="plain:true,hasDownArrow:false,menu:'#mGeneralTarjeta',menuAlign:'left'"><i class="fas fa-bars"></i></a> -->
                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true"  onclick="mostrar_panel_tarjetas();"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>


                </div>



                <div class="m-title" style="padding-left:50px;"> <i class="far fa-credit-card fa-lg fa-fw"></i> Nueva tarjeta crédito / débito</div>
            </div>

        </header>
        <div class="contenedor-medios-cobro" style="height:100%">
            <div class="bloque-estado">

                
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"><i class="titulo-recibo-alt">$</i><span id="totalReciboTarjeta" class=".titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-a-cubrir"> <i class="a-cubrir-alt">$</i><span id="aCubrirTarjeta" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
                <div class="hijo-estado">
                    <p class="texto-estado total-tarjeta"><i class="tarjeta-alt">$</i><span id="totalAltaTarjeta" class="tarjeta-alt">0.00</span>
                        <br>
                        Total tarjeta
                    </p>
                </div>
            </div>

            <div>

                <input class="easyui-numberbox" inputmode="decimal" id="tarjetaImporteTotal" name="tarjetaImporteTotal" enterkeyhint="next" data-options="
                        min:'0',                        
                        decimalSeparator:',', 
                        precision:'2', 
                        groupSeparator:'.', 
                        required:'true', 
                        missingMessage:'Completar importe total', 
                        prefix:'$',        
                        prompt:'0.00',
                        height: '50px',                         
                        width:'100%',  
                        label:'<strong>Importe: </strong>',
                        labelWidth:'60px',                    
                        " style="text-align:right;">
            </div>

            <div>
                <input class="easyui-numberbox" type="number" inputmode="numeric" id="tarjetaNumero" oninput="" data-options="
                        label:'Número:',
                        labelWidth:'60px',                        
                        min:'0',
                        max:'9999',            
                        required:'true', 
                        missingMessage:'Ultimos 4 numeros tarjeta',            
                        prompt:'0000' 
                        " maxlength="4">
                <input id="tarjetaTipo" value="Credito">
            </div>



            <div style="margin-bottom:5px;">

                <select class="easyui-combobox" name="tarjetaLista" id="tarjetaLista" data-options="
                    valueField: 'id',
                    textField: 'text',
                    label:'Tarjeta:',
                    labelWidth:'60px',
                    editable: 'false',                    
                    prompt:'Seleccionar tarjeta...',
                    limitToList: 'true',
                    
                    " style="width:80%;margin-left:5px;">
                </select>
                <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"></svg>
                <img id="img-tarjeta" class="ccicon" style="display:none">


            </div>

            <div style="margin-bottom:5px;">
                <select class="easyui-combobox" name="tarjetaListaPlan" id="tarjetaListaPlan" data-options="
                    valueField: 'id',
                    textField: 'text',
                    prompt:'seleccionar un Plan',
                    label:'Plan:',
                    editable: 'false',
                    labelWidth:'60px'
                    " style="width:100%;"></select>




            </div>

            <h3 class="titulo-medios-cobro">Cuotas</h3>
            <div style="margin-bottom: 5px;">

                <input class="easyui-numberbox" id="tarjetaCuotas" inputmode="numeric" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type="number" name="TarjetaCuotas" maxlength="2" data-options="
                            missingMessage:'Debe completar cuotas', 
                            prompt:'1', 
                            required:'true', 
                            label:'Cant:',
                            labelWidth:'120px'
                            " style="width:50%">

                <input class="easyui-numberbox" id="tarjetaImporteCuota" name="tarjetaImporteCuota" inputmode="decimal" data-options="
                        min:'0',
                        decimalSeparator:',',
                        precision:'2',
                        groupSeparator:'.', 
                        required:'true', 
                        missingMessage:'importe cuota' ,
                        prefix:'$', 
                        prompt:'0.00',
                        label:'Valor:',
                        labelWidth:'60px',
                        labelAlign: 'right'
                        
            " style="width:49%;">


            </div>
            <div style="margin-bottom:5px;">
                <input class="easyui-numberbox" type="number" inputmode="numeric" id="tarjetaCupon" data-options="
            min:'0', 
            required:'true',
            missingMessage:'Debe completar cupon',
            prompt:'000000',
            label:'Cupón:',
            labelWidth:'60px'           
            " style="width:49%;">

                <input class="easyui-textbox" type="text" inputmode="decimal" id="tarjetaLote" data-options="
                min:'0', 
                required:'true',
                missingMessage:'Debe completar Lote',
                prompt:'000000',               
                label:'Lote:',
                labelWidth:'40px',  
                labelAlign: 'right'
            " style="width:50%">
            </div>
            <!-- <footer> -->


            <div style="padding:10px;text-align: center">
                <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="alta_tarjeta_credito();" style="width:45%" id="botonGuardarTarjeta"><i class="fas fa-check fa-fw fa-lg"></i> Aceptar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton secundario" onclick="mostrar_panel_tarjetas();" style="width:45%" id="botonCancelarTarjeta"><i class="fas fa-times fa-fw fa-lg"></i> Cancelar</a>
            </div>
        </div>
        <!-- </footer> -->


    </div>

    <!-- FIN ALTA TARJETAS CREDITO -->