<!--PANEL EFECTIVO-->
<div class="easyui-navpanel titulo-recibo" id="panelEfectivo">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title"> <i class="fas fa-money-bill fa-lg fa-fw"> </i> Efectivo pesos</div>
                <div class="m-left">
                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true" onclick="$.mobile.back();"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>
                </div>
            </div>


        </header>

        <!--CUERPO EFECTIVO PESOS-->
        <!--================================================================-->
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"> <i class="titulo-recibo-alt">$</i><span id="totalRecioPesos" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="a-cubrir-alt">$</i><span id="totalAcubrirPesos" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
                <div class="hijo-estado">
                    <p class="texto-estado"><i class="pesos-alt">$</i><span id="totalEfectivoPesosAlta" class="pesos-alt">0.00</span>
                        <br>
                        Efectivo
                    </p>
                </div>
            </div>

            <div>
                <h3 class="titulo-medios-cobro pesos-alt">Efectivo pesos</h3>
                <input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="efectivoCobro" min="0" decimalSeparator="," groupSeparator="." precision="2" label="<strong>Importe:</strong>" style="width:70%;text-align:right">

                <!-- <a href="javascript:void(0)" class="easyui-linkbutton secundario" id="boton-ok-efectivo" onclick="valida_pesos();"> Confirmar</a> -->
            </div>

            <!-- <div>

                <input class="easyui-numberbox" id="efectivoTotalCobro" min="0" decimalSeparator="," groupSeparator="." precision="2" prefix="$" label="Total:" readonly="true" prompt="$ total efectivo" style="width:100%">

            </div> -->

            <div style="padding:5px;text-align: center;">
                <a href="javascript:void(0)" class="easyui-linkbutton primario" id="efectivo-ok" onclick="acepta_efectivo($(this),'pesos')" style="width:49%" ><i class="fas fa-check fa-lg fa-fw"></i> Guardar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton secundario" id="efectivo-cancel" data-options="disabled:true" style="width:49%" onclick="borrar_efectivo('#efectivoCobro','pesos')"><i class="fas fa-trash fa-lg fa-fw"></i> Vaciar Pesos </a>
                
            </div>
        </div>
        <!-- <footer>
            <a href="javascript:void(0)" class="easyui-linkbutton" id="efectivo-cancel" data-options="plain:true,disabled:true" onclick="borrar_efectivo('#efectivoCobro','pesos')"><i class="fas fa-trash fa-lg fa-fw"></i> Deshacer </a>
            <a href="javascript:void(0)" class="easyui-linkbutton" id="efectivo-ok" onclick="acepta_efectivo($(this),'pesos')" data-options="disabled:true"><i class="fas fa-check fa-lg fa-fw"></i> Confirmar</a>

        </footer> -->
    </div>
    <!--FIN PANEL EFECTIVO PESOS-->

    <!-- PANEL EFECTIVO DOLAR -->
    <div class="easyui-navpanel titulo-recibo" id="panelEfectivoDolar">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title" style="padding-left: 20px;"><i class="fas fa-money-bill-alt fa-lg fa-fw"></i> Moneda extranjera</div>
                <div class="m-left">
                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true"  onclick="$.mobile.back();"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>
                </div>
            </div>


        </header>
        <!--PANEL CUERPO DOLARES-->
        <!--=================================================================-->
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"> <i class="titulo-recibo-alt">$</i><span id="totalRecioDolar" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="a-cubrir-alt">$</i><span id="totalAcubrirDolar" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
                <div class="hijo-estado">
                    <p class="texto-estado total-pesos"><i class="dolar-alt">$</i><span id="totalEfectivoDolarAlta" class="dolar-alt">0.00</span>
                        <br>
                        Efectivo
                    </p>
                </div>
            </div>

            <div>

                <h3 class="titulo-medios-cobro dolar-alt">Efectivo dolares
                    <p style="text-align: right; font-size:smaller;" class="detalle-mc">Cotización: $<span id="cotiDolarCobro" style="font-weight: bolder"></span></p>
                </h3>


                <input class="easyui-textbox" id="dolarCobro" min="0" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" label="<strong>Dólares:</strong>" decimalSeparator="," groupSeparator="." precision="2" style="width:60%;text-align:right;">



                <a href="javascript:void(0)" class="easyui-linkbutton" id="boton-calcula-dolar" onclick="dolar_a_peso();"><i class="fas fa-cog fa-lg fa-fw"></i> Calcular</a>

                <!-- <input class="easyui-numberbox" id="cotiDolarCobro" min="0" decimalSeparator="," groupSeparator="." precision="2" prefix="Cot: " prompt="1"
                style="width:22%">-->
            </div>
            <div>
                <input class="easyui-numberbox" id="dolarApeso" min="0" decimalSeparator="," groupSeparator="." precision="2" readonly="true" label="Pesos:" prompt="dolar a pesos" style="width:90%">
                <!-- <a href="javascript:void(0)" class="easyui-linkbutton" id="boton-ok-dolar" disabled="true" onclick="valida_dolar();">Aceptar</a> -->

            </div>
            <!-- <div>

                <input class="easyui-numberbox" id="dolarTotalCobro" min="0" decimalSeparator="," groupSeparator="." precision="2" prefix="$" label="Efectivo:" readonly="true" prompt="total efectivo $ " style="width:100%">

            </div> -->

            <div>

                
                <a href="javascript:void(0)" class="easyui-linkbutton primario" id="dolar-ok" onclick="acepta_efectivo($(this),'dolar')" disabled="true" style="width:49%"><i class="fas fa-check fa-lg fa-fw"></i> Confirmar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton secundario" id="dolar-cancel" data-options="disabled:true" onclick="borrar_efectivo('#dolarCobro','dolar')" style="width:49%"><i class="fas fa-trash fa-lg fa-fw"></i> Vaciar Dolar</a>
            </div>
        </div>
    </div>