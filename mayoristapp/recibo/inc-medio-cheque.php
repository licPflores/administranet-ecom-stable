<div class="easyui-navpanel titulo-recibo" id="panelCuerpoCheque">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title"><i class="fa fa-money-check-alt fa-lg fa-fw"></i> Cheques</div>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true" onclick="$.mobile.go('#panelMediosCobro','fade','left');"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>


                </div>
            </div>


        </header>
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"><i class="titulo-recibo-alt">$</i><span id="totalReciboPanelCheques" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="">$</i><span id="aCubrirPanelCheques" class="">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>

                <div class="hijo-estado">
                    <p class="texto-estado total-cheque"><i class="cheque-alt">$</i><span id="totalPanelCheques" class="cheque-alt">0.00</span>
                        <br>
                        Total cheques
                    </p>
                </div>
            </div>


            <div style="height:70%;">
                <h3 class="titulo-medios-cobro cheque-alt">Listado de cheques </h3>
                <div>
                    <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="mostrar_panel_alta_cheque();"><i class="fas fa-plus fa-lg fa-fw"></i> Nuevo </a>
                    <a href="javascript:void(0)" class="easyui-linkbutton secundario" onclick="borrar_cheque();"><i class="fas fa-trash fa-lg fa-fw"></i>Borrar </a>
                </div>

                <table id="tblCheques"></table>
            </div>
        </div>
    </div>
    <!--PANEL ALTA DE CHEQUE-->

    <div class="easyui-navpanel titulo-recibo" id="panelAltaCheque">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title" style=" padding-left: 20px;">Nuevo cheque de tercero</div>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true"  onclick="$.mobile.back();"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>


                </div>

            </div>

        </header>
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"> <i class="titulo-recibo-alt">$</i><span id="totalReciboChequeAlta" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Total recibo
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i>$</i><span id="totalAcubrirChequeAlta">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
                <div class="hijo-estado">
                    <p class="texto-estado total-cheque"><i class="cheque-alt">$</i><span id="totalChequeAlta" class="cheque-alt">0.00</span>
                        <br>
                        Cheques
                    </p>
                </div>
            </div>
            <div>
                <select class="easyui-combobox" name="listaBancos" id="listaBancos" data-options=" valueField: 'id',
                        textField: 'text',panelMaxHeight:'70px',limitToList:'true',prompt:'seleccionar un banco',label:'Banco:'" style="width:100%;"></select>

            </div>
            <div>
                <!--<input class="easyui-textbox" id="chBancoCuit" data-options="readonly:true" style="width:70%" label="Bco CUIT:">-->
                <input class="easyui-maskedbox" id="chBancoCuit" data-options="readonly:true" promptChar="#" mask="99-99999999-9" label="Bco CUIT:" style="width:100%">
            </div>
            <div>
                <input class="easyui-numberbox" inputmode="numeric" pattern="[0-9]*" id="chNumero" min="0" required="true" missingMessage="Debe completar nro chque" prompt="nro cheque" style="width:80%" label="Nro:">
            </div>

            <div>
                <input class="easyui-textbox" inputmode="decimal" id="chImporte" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" min="0" decimalSeparator="," precision="2" groupSeparator="." required="true" missingMessage="importe cheque" prefix='$' prompt="0.00" style="text-align:right;width:80%" label="Importe:">
            </div>

            <div>
                <label for="m-buttongroup">Tipo:</label>
                <span class="m-buttongroup" style="padding-left:16%;">

                    <a href="javascript:void(0)" class="easyui-linkbutton tipoCheque" id="Normal" data-options="toggle:true,group:'gCheque',selected:'true'" style="width:80px;height:30px">Normal</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton tipoCheque" id="Electronico" data-options="toggle:true,group:'gCheque'" style="width:80px;height:30px">Electrónico</a>

                </span>
            </div>

            <div>

                <div style="float:left;width:49%">
                    <input class="easyui-datebox" id="chFechaEmision" required="true" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Emision:',labelPosition:'top'" style="width:100%">
                </div>
                <div style="float:left;width:49%">
                    <input class="easyui-datebox" id="chFechaCobro" required="true" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Cobro:',labelPosition:'top'" style="width:100%">
                </div>
            </div>

            <div>
                <input class="easyui-textbox" label="Librador:" id="chLibrador" prompt="librador" value="<?php echo  $clienteObj->cliente; ?>" style="width:100%">
            </div>
            <div>
                <!--<input class="easyui-textbox" label="CUIT:" id="chCuit" prompt="Nro cheque" style="width:100%">-->
                <input class="easyui-maskedbox" id="chCuitLibrador" mask="99-99999999-9" promptChar="#" value="<?php echo $cuit; ?>" label="CUIT:" style="width:100%">
            </div>
            <div style="text-align: center">
                <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="alta_cheque();" style="width:45%" id="botonGuardarCheque"><i class="fas fa-check fa-fw fa-lg"></i> Crear</a>
                <a href="javascript:void(0)" class="easyui-linkbutton secundario" onclick="mostrar_panel_cheques();" style="width:45%"><i class="fas fa-times fa-fw fa-lg"></i> Cancelar</a>
            </div>
        </div>
        <!-- <footer>


            <div style="padding:10px;text-align: center">
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="alta_cheque();" style="width:45%" id="botonGuardarCheque"><i class="fas fa-check fa-fw fa-lg"></i> Aceptar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$.mobile.go('#panelMediosCobro','fade','left');mostrar_panel_cheques();" style="width:45%"><i class="fas fa-times fa-fw fa-lg"></i> Cancelar</a>
            </div>

        </footer> -->


    </div>
    <!-- fin PANEL DE ALTA DE CHEQUE TERCERO -->