Private Modo As Integer

Public ID_Art As Double 'ID de Articulo
Public tiene_lote As String ' Si el articulo tiene lote
Public CodigoArticulo As String 'Codigo de Articulo Cadena
Public PrecioCostoxU As Double ' Precio de Costo del Articulo
Public IDAlicuota As Double ' Alicuota IVA
Public IDAlicuotaIB As Double ' Alicuota IB
Public TipoIVA As String ' Tipo de IVA
Public tipo_art As String 'Tipo de Articulo
Public id_manual As String ' ID Manual del Articulo
Public id_cuerpostock As Double ' Variable que guarda el id de la tabla cuerpostock para modificar el renglon
Public mod_renglon As String ' Modifica o no un renglon

Public contador As Double ' Variables para guardar el Codigo de Movimiento de la factura para ser utilizado en el REC automatico
Public Error_conta As String
Public IdEjer As Double 'Permiso selec_ejer_per_cont = Si -> Obtengo a traves de ventana con_abmEjercicio
Public IdPer As Double  'Permiso selec_ejer_per_cont = Si -> Obtengo a traves de ventana con_abmEjercicio

Public ID_Proyecto As Double

Public Nro As String

Public Seleccion_Manual_Articulo As String
Public Proceso_Llamante As String ' Proceso de para ejecutar en formulario

' Declaro Objetos
Dim conn As New ADODB.Connection

Public Sub Inicial()
Menu
    
' Capturamos el ERROR
On Error GoTo captura
    
    ' Eliminacion de tabla temporal cuerpostock
    Elimina_Temporal
    
    'Asigno Fecha de Sistema
    Fecha = Principal.Fecha
    
    No_Es_Transferencia
    
    ' Propiedades de la Grid
    GridArticulos.AllowRowSizing = False
    GridArticulos.MarqueeStyle = dbgDottedRowBorder
    
    ' Permisos de Usuario
    
    ' Si permite modificar deposito o no
    If Principal.cambia_deposito = "Si" Then
        ' Cuando el usuario tiene permiso de cambiar deposito trayendo todos
        DataDepositoO.ConnectionString = IngresoUsuario.Conex
        DataDepositoO.CommandType = adCmdUnknown
        
        conn.ConnectionString = IngresoUsuario.Conex
        conn.CursorLocation = adUseClient
        conn.Open
        
        Dim rs_depo As New ADODB.Recordset
        
        rs_depo.Open "SELECT * FROM deposito_usr WHERE id_usuario = " & Principal.idUsuario & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        
        If rs_depo.RecordCount > 0 Then
            'Depositos x Usuario
            DataDepositoO.RecordSource = "SELECT * FROM deposito " & _
            "INNER JOIN deposito_usr ON (deposito_usr.id_deposito = deposito.codDeposito)" & _
            "WHERE deposito_usr.id_usuario = " & Principal.idUsuario & " AND deposito.anulado = 'No' ORDER BY CodDeposito"
        Else
            DataDepositoO.RecordSource = "SELECT * FROM Deposito Where Anulado = 'No' ORDER BY CodDeposito"
        End If
        
        rs_depo.Close
        conn.Close
        
        DataDepositoO.Refresh
        DataDepositoO.Recordset.MoveFirst
    Else
        ' Cuando el usuario no tiene permiso de cambiar deposito trayendo el asignado para el usuario
        DataDepositoO.ConnectionString = IngresoUsuario.Conex
        DataDepositoO.CommandType = adCmdUnknown
        DataDepositoO.RecordSource = "SELECT * FROM deposito where CodDeposito = " & Principal.id_deposito & " AND deposito.anulado = 'No' ORDER BY CodDeposito"
        DataDepositoO.Refresh
        DataDepositoO.Recordset.MoveFirst
        
        DepositoOrigen = DataDepositoO.Recordset.Fields!CodDeposito
    End If
    
    ' Por defecto el deposito asignado
    DepositoOrigen.BoundText = DataDepositoO.Recordset.Fields!CodDeposito
    
    'Destino
    DataDepositoD.ConnectionString = IngresoUsuario.Conex
    DataDepositoD.CommandType = adCmdUnknown
    
    ' Conexiones ADO
    conn.ConnectionString = IngresoUsuario.Conex
    
    ' Conexiones DAO
    CuerpoStock.ConnectionString = IngresoUsuario.Conex
    
    ' Defecto no modifica renglon
    mod_renglon = "No"
    
    ' Si usa proyecto visible el frame
    If Principal.activ_proyecto = "Si" Then
        frame_proyecto.Visible = True
        ' Por default proyecto Ninguno
        conn.Open
        conn.CursorLocation = adUseClient
        Dim rs_proyecto As New ADODB.Recordset
        
        rs_proyecto.Open "SELECT * FROM erp_proyecto where id_proyecto = 1 ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        
        If rs_proyecto.RecordCount = 1 Then
            ID_Proyecto = rs_proyecto.Fields!ID_Proyecto
            nombre_proyecto = rs_proyecto.Fields!nombre_proyecto
        End If
        conn.Close
        
    Else
        frame_proyecto.Visible = False
    End If
    
    ' Validacion si puede acceder a todas las referencia o solo la seleccionada en el permiso del puesto
    If Principal.acceso_ref_movstock = "Todos" Then
        ' Traigo por defecto referencias para movimientos de stock
        data_ref_movstock.ConnectionString = IngresoUsuario.Conex
        data_ref_movstock.CommandType = adCmdUnknown
        data_ref_movstock.RecordSource = "SELECT * FROM ref_movstock ORDER BY nombre_ref_movstock"
        data_ref_movstock.Refresh
    Else
        ' Traigo por la seleccionada para el puesto
        data_ref_movstock.ConnectionString = IngresoUsuario.Conex
        data_ref_movstock.CommandType = adCmdUnknown
        data_ref_movstock.RecordSource = "SELECT * FROM ref_movstock WHERE id_ref_movstock = " & Principal.id_refmovstock
        data_ref_movstock.Refresh
    End If
    
    ' Por defecto sin referencia
    If data_ref_movstock.Recordset.RecordCount > 0 Then
        Referencia.BoundText = data_ref_movstock.Recordset.Fields!id_ref_movstock
    End If
    
    ' Validacion de acceso a lista motivo segun el puesto
    If Principal.acceso_motivo_movstock = "Todos" Then
        Motivo.Clear
        Motivo.AddItem "Stock Inicial", 0
        Motivo.AddItem "Ajuste", 1
        Motivo.AddItem "Faltante Mercaderia", 2
        Motivo.AddItem "Sobrante Mercaderia", 3
        Motivo.AddItem "Rotura", 4
        Motivo.AddItem "Transferencia", 5
        Motivo.AddItem "Mov. Interno Salida", 6
        Motivo.AddItem "Mov. Interno Entrada", 7
        Motivo.AddItem "Armado", 8
        Motivo.AddItem "Desarmado", 9
    End If
    
'    If Principal.acceso_motivo_movstock = "Movimiento interno E/S" Then
'        Motivo.Clear
'        Motivo.AddItem "Mov. Interno Salida", 0
'        Motivo.AddItem "Mov. Interno Entrada", 1
'    End If
'
'    If Principal.acceso_motivo_movstock = "Ajuste" Then
'        Motivo.Clear
'        Motivo.AddItem "Ajuste", 0
'        Motivo.AddItem "Faltante Mercaderia", 1
'        Motivo.AddItem "Sobrante Mercaderia", 2
'        Motivo.AddItem "Rotura", 3
'    End If
'
'    If Principal.acceso_motivo_movstock = "Transferencia" Then
'        Motivo.Clear
'        Motivo.AddItem "Transferencia", 0
'    End If
    
    ' Por defecto el primer elemento de la lista Motivo
    Motivo.ListIndex = 0
    
'    GridArticulos.Columns(5).HeadAlignment = dbgRight
'    GridArticulos.Columns(5).Alignment = dbgRight
'    GridArticulos.Columns(8).HeadAlignment = dbgRight
'    GridArticulos.Columns(8).Alignment = dbgRight
'    GridArticulos.Columns(9).HeadAlignment = dbgRight
'    GridArticulos.Columns(9).Alignment = dbgRight
    
    'Conf. Grilla Articulo
    conn.Open
    conn.CursorLocation = adUseClient
    Dim rs_grilla As New ADODB.Recordset
    
    rs_grilla.Open "SELECT * FROM conf_grilla_final_puesto where nombre_grilla ='Grilla Mov Stock' AND id_puesto = " & Principal.idpuesto, conn, adOpenDynamic, adLockOptimistic
    
        Do While Not rs_grilla.EOF

            If rs_grilla.Fields!activa = -1 Then
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).Visible = True
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).Alignment = rs_grilla.Fields!alineacion
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).HeadAlignment = rs_grilla.Fields!alineacion
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).order = rs_grilla.Fields!orden
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).Width = rs_grilla.Fields!ancho
                            
            Else
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).Visible = False
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).Alignment = rs_grilla.Fields!alineacion
                GridArticulos.Columns(CInt(rs_grilla.Fields!index_campo)).HeadAlignment = rs_grilla.Fields!alineacion
            End If
            
            rs_grilla.MoveNext
        Loop
        
    conn.Close
    
    If Principal.mov_stock_utiliza_cbarra = "Si" Then
        Articulo.Locked = False
        Cantidad.Text = 1
    Else
        Articulo.Locked = True
        Cantidad.Text = 0
    End If
    
    If Principal.mov_stock_utiliza_cbarra = "Si" Then
        Seleccion_Manual_Articulo = "No"
    End If


If Proceso_Llamante = "Stock_Consulta_Avanzada" Then
    ' Calculo stock saldo
    If Principal.calculo_stock_saldo = "Si" Then
        calculo_saldo_directo.TabIndex = 0
    End If
End If

Exit Sub
captura:
        
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Articulo_KeyPress(KeyAscii As Integer)

    If KeyAscii = 13 Then
        If Principal.mov_stock_utiliza_cbarra = "Si" Then
        
            'Desarme
            If Motivo.ListIndex = 9 Then
                If cantDesarme.Text <= 0 Then
                    MsgBox "La cantidad de desarme debe ser mayor a cero ", vbInformation, "ATENCION"
                    cantDesarme.Enabled = True
                    cantDesarme.SetFocus
                    Exit Sub
                Else
                    cantDesarme.Enabled = False
                End If
            End If
            
            If KeyAscii = 13 And Busqueda = "" Then
                Cantidad.SetFocus
            End If
        
            If KeyAscii = 13 And Articulo <> "" And mod_renglon = "No" Then
            
                If DepositoDestino.Visible = True And DepositoDestino = "" Then

                    MsgBox "El deposito destino es obligatorio", vbInformation, "ATENCION"
                    Exit Sub
                    
                End If
            
                If Motivo.ListIndex < 8 Then
                    busqueda_articulo
                End If
                
                'Ensamble/Desarme
                If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
                    busqueda_articulo_ensamble
                End If
                
            End If
            
'            If Principal.tipo_balanza = "Bascula" And Principal.usa_multiplica_bulto_promedio = "Si" Then
'
'                Carga_Unidad_Peso.id_articulo = IDArt
'                Carga_Unidad_Peso.tipo_comprobante = "MOVSTOCK"
'                Carga_Unidad_Peso.unidades = 1
'                Carga_Unidad_Peso.Inicial
'                Carga_Unidad_Peso.Show
'
'            End If
            
        End If
    End If

End Sub

Private Sub calculo_saldo_directo_GotFocus()
    calculo_saldo_directo.SelStart = 0
    calculo_saldo_directo.SelLength = Len(calculo_saldo_directo.Text)
End Sub

Private Sub calculo_saldo_directo_KeyPress(KeyAscii As Integer)
KeyAscii = Principal.SoloNumeros(KeyAscii, calculo_saldo_directo)
If KeyAscii = 13 Then
    AgregarRenglon.SetFocus
End If
End Sub

Private Sub calculo_saldo_directo_LostFocus()

' Capturamos el ERROR
On Error GoTo captura

If calculo_saldo_directo.Text = "" Then  ' calculo_saldo_directo.Text = "0" Or
    MsgBox "Debe ingresar una cantidad", vbCritical + vbOKOnly, "ATENCION"
    calculo_saldo_directo.SetFocus
    Exit Sub
End If


' Calculo de cantidad para generar el ajuste deseado
' Obtengo el saldo del articulo segun el deposito

If Principal.utiliza_embalaje = "Si" Then
    id_proveedor = Obtener_Datos_Articulo(ID_Art, "id_proveedor")
    multiplicador_compra = Obtener_Presentacion_Articulo_Proveedor(ID_Art, "multiplicador_comp", id_proveedor)
    saldo_articulo_actual = Obtener_Saldo_Articulo(CDbl(DepositoOrigen.BoundText), ID_Art) / CDbl(multiplicador_compra)
Else
    saldo_articulo_actual = Obtener_Saldo_Articulo(CDbl(DepositoOrigen.BoundText), ID_Art)
End If

' Si es mayor el saldo a ingresar que el saldo del articulo
' Ej: 30 > 10
If CDbl(calculo_saldo_directo) > saldo_articulo_actual Then
    ' 30 - 10 = 20
    diferencia = calculo_saldo_directo - saldo_articulo_actual
    ' Debo sumar para llegar a la cantidad (Entrada)
    ' 20 + 10 (Saldo) Actual
    Cantidad = diferencia
    ES.ListIndex = 0
    
    AgregarRenglon.SetFocus
End If

' Ej: 10 < 30
If CDbl(calculo_saldo_directo) < saldo_articulo_actual Then
    ' 30 - 10 = 20
    diferencia = saldo_articulo_actual - calculo_saldo_directo
    ' Debo restar para llegar a la cantidad (Salida)
    ' 20 - 10 (Saldo) Actual
    Cantidad = diferencia
    ES.ListIndex = 1
    
    AgregarRenglon.SetFocus
    
'    AgregarRenglon_Click
End If

If CDbl(calculo_saldo_directo) = saldo_articulo_actual Then

    Cantidad = calculo_saldo_directo
    
    AgregarRenglon.SetFocus

End If

Exit Sub
captura:
        
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)

End Sub

Private Sub cantDesarme_KeyPress(KeyAscii As Integer)

    KeyAscii = Principal.SoloNumeros(KeyAscii, cantDesarme)

    If KeyAscii = 13 Then
    
        If Principal.mov_stock_utiliza_cbarra = "Si" Then
            Articulo.SetFocus
        Else
            ListaArticulos.SetFocus
        End If
        
    End If
    
End Sub

Private Sub GridArticulos_GotFocus()
    GridArticulos.MarqueeStyle = 4
End Sub

Private Sub Aceptar_Click()
' Capturamos el ERROR
On Error GoTo captura

Dim rs_consulta_articulo As New ADODB.Recordset

''''''''''''''''''''''''''''''''''''''''
'Asignacion manual de Ejer/Per contable'
''''''''''''''''''''''''''''''''''''''''
If Principal.activ_contabilidad = "Si" Then
    If Principal.selec_ejer_per_cont = "Si" Then
    
            '25/04/2016 Ajuste
            If Motivo.ListIndex <> 1 Then
            
            'Inicio
            IdEjer = 0
            IdPer = 0
            Cont_AbmEjercicio.Accion = "CargaMovStock"
            Cont_AbmEjercicio.Caption = " Selección de ejercicio y periodo contable"
            Cont_AbmEjercicio.Show vbModal
            
            End If
    End If
End If

'''''''
'Serie'
'''''''
conn.ConnectionString = IngresoUsuario.Conex
conn.CursorLocation = adUseClient
conn.Open
If ESerie = True Then
    If ValCantSerie = True Then
        'Mensaje cant <> cant serie
        MsgBox "La cantidad de números de serie no coincide con la cantidad de artículos seriados. ", vbInformation, "ATENCION"
        conn.Close
        Exit Sub
    End If
End If
conn.Close

If MsgBox("¿Desea generar el Movimiento de Stock?", vbYesNo + vbQuestion, "ATENCION") = vbYes Then
    
    
   
    If CuerpoStock.Recordset.RecordCount = 0 Then
    
        MsgBox "Debe agregar al menos 1 renglón de artículos", vbCritical, "ATENCION"
        Exit Sub
    
    End If
    
                                'Ensamble/Desarme
    If Motivo = "Transferencia" Or Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
        If DepositoOrigen = "" Or DepositoDestino = "" Then
            MsgBox "Complete los depósitos de origen y destino", vbCritical, "ATENCION"
            Exit Sub
        End If
    End If
    

    ' Formulario de espera
    form_espera.label_mje = "Espere por favor procesando datos...Generando comprobante"
    form_espera.Show
    
'    Empieza_GIF ("Generando comprobante. Espere por favor...")
    
    DoEvents
                     
    form_espera.ProgressBar.Value = 50

    ' Abro Conexion
    conn.Open
                
    Dim rs_codmov As New ADODB.Recordset
    Dim rs_stock As New ADODB.Recordset
    Dim rs_saldo_stock As New ADODB.Recordset
    Dim rs_stock_deposito As New ADODB.Recordset
    Dim rs_nro_comp As New ADODB.Recordset
    Dim rs_lote As New ADODB.Recordset
    Dim rs_movimiento_stock As New ADODB.Recordset
    Dim rs_movstock_pedi As New ADODB.Recordset
    Dim rs_pedi As New ADODB.Recordset
    
    'Dim contador As Double
    
    ' Inicio Transaccion de CodMov
    conn.CursorLocation = adUseClient
    conn.BeginTrans
    conn.Execute "SET AUTOCOMMIT=0"
                   
    ' Actualizo el numero de movimiento
    rs_codmov.Open "SELECT * FROM codmov where codigo = 1", conn, adOpenDynamic, adLockPessimistic
    contador = rs_codmov.Fields!CodigoMovimiento
    contador = contador + 1
    rs_codmov.Fields!CodigoMovimiento = contador
    CodMov = contador
    ' Control error
    control_error = "CodMov"
    rs_codmov.Update
    rs_codmov.Close
    
    ' Cierro transaccion de CodMov
    If conn.State = 1 Then
        conn.CommitTrans
    End If
    
    ' Asigno a la variable para guardar el Codigo del movimiento para la FA
    CodigoMovInf = CStr(contador)
    
    ' Inicio Transaccion
    conn.CursorLocation = adUseClient
    conn.BeginTrans
    conn.Execute "SET AUTOCOMMIT=0"
                                                   
    rs_nro_comp.Open "select * from talonarios where id_punto_venta = " & Principal.id_punto_ventac & " And TipoComprobante = 'MSTOCK'", conn, adOpenDynamic, adLockOptimistic
                                                                       
    ' Nro Comprobante
    NroComp = CDbl(rs_nro_comp.Fields!Nro)
    
    Ceros_Nro_Comp = Principal.Ceros_Nro_Comp(rs_nro_comp.Fields!Nro)
            
    ' Nro PV
    ceros_pv = Principal.Ceros_Nro_pv(rs_nro_comp.Fields!PV)
    Nro = ceros_pv & rs_nro_comp.Fields!PV & "-" & Ceros_Nro_Comp & rs_nro_comp.Fields!Nro
         
    ' Asigno el Nro para la busqueda
    NroBusq = NroComp
         
    ' Numeracion de MSTOCK
    ContadorComp = CDbl(rs_nro_comp.Fields!Nro)
    ContadorComp = ContadorComp + 1
    rs_nro_comp.Fields!Nro = ContadorComp
    ' Control error
    control_error = "Numero Comp"
    rs_nro_comp.Update
    rs_nro_comp.Close
    
    ' Actualizo el Stock
    rs_stock.Open "SELECT * FROM stock where CodigoMovimiento = 1", conn, adOpenDynamic, adLockOptimistic
    
    CuerpoStock.Recordset.MoveFirst
    Do While Not CuerpoStock.Recordset.EOF
        
        rs_stock.AddNew
        
        ' Consulto el saldo en la tabla stock_deposito origen
        rs_saldo_stock.Open "SELECT * FROM stock_deposito WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And id_deposito = " & DepositoOrigen.BoundText & "", conn, adOpenDynamic, adLockOptimistic
                   
        'Ensamble
        If Motivo.ListIndex = 8 Then
        
            If DepositoOrigen.BoundText <> DepositoDestino.BoundText Then
        
                rs_saldo_stock.Close
            
                If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
                    
                    rs_saldo_stock.Open "SELECT * FROM stock_deposito " & _
                                        "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And " & _
                                        "id_deposito = " & DepositoDestino.BoundText & "", conn, adOpenDynamic, adLockOptimistic
                Else
                
                    'Insumos
                    rs_saldo_stock.Open "SELECT * FROM stock_deposito " & _
                                        "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And " & _
                                        "id_deposito = " & DepositoOrigen.BoundText & "", conn, adOpenDynamic, adLockOptimistic
                End If
            
            End If
        
        End If
        
        'Desarme
        If Motivo.ListIndex = 9 Then
        
            If DepositoOrigen.BoundText <> DepositoDestino.BoundText Then
        
                rs_saldo_stock.Close
            
                If CuerpoStock.Recordset.Fields!ES = "Salida" Then
                    
                    rs_saldo_stock.Open "SELECT * FROM stock_deposito " & _
                                        "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And " & _
                                        "id_deposito = " & DepositoOrigen.BoundText & "", conn, adOpenDynamic, adLockOptimistic 'cambio por readonly
                Else
                
                    'Insumos
                    rs_saldo_stock.Open "SELECT * FROM stock_deposito " & _
                                        "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And " & _
                                        "id_deposito = " & DepositoDestino.BoundText & "", conn, adOpenDynamic, adLockOptimistic 'cambio por readonly
                End If
            
            End If
        
        End If
        
        'Variables para los reportes
        Fecha_Informe = Format(Fecha, "short date")
        Detalle_Informe = Detalle.Text
        NroMovStock_Informe = Nro

        rs_stock.Fields!Fecha = Format(Fecha, "short date")
        rs_stock.Fields!CodigoArticulo = CuerpoStock.Recordset.Fields!CodigoArticulo
        rs_stock.Fields!Descripcion = CuerpoStock.Recordset.Fields!Descripcion
        
        rs_stock.Fields!PrecioCostoxU = CuerpoStock.Recordset.Fields!PrecioCostoxU
        rs_stock.Fields!PrecioCostoxR = CuerpoStock.Recordset.Fields!PrecioCostoxR
        
        '20/03/2018 VALIDAR SI EL ARTICULO ES EN DOLAR Y HACER LA CONVERSION A PESOS POR EL TIPO DE CAMBIO ACTUAL
        rs_consulta_articulo.Open "SELECT articulo.moneda FROM articulo WHERE IDArt = " & CuerpoStock.Recordset.Fields!IDArt & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        
        If rs_consulta_articulo.RecordCount = 1 Then
            
            'Precio Costo
            If rs_consulta_articulo.Fields!Moneda = "Dolar" Then
                CuerpoStock.Recordset.Fields!PrecioCostoxR = CuerpoStock.Recordset.Fields!PrecioCostoxU * CuerpoStock.Recordset.Fields!Cantidad * Principal.cotizacion
                CuerpoStock.Recordset.Fields!PrecioCostoxU = CuerpoStock.Recordset.Fields!PrecioCostoxU * Principal.cotizacion
            End If
        
        End If
        
        rs_consulta_articulo.Close
                
        rs_stock.Fields!Alicuota = CuerpoStock.Recordset.Fields!Alicuota
        rs_stock.Fields!AlicuotaIB = CuerpoStock.Recordset.Fields!AlicuotaIB
        
'Cantidad

        ' Funcionalidad Bulto Cerrado / Display
        If (Principal.utiliza_bulto_cerrado = "Si" Or Principal.utiliza_display = "Si") Then

        End If
        
        rs_stock.Fields!Cantidad = CuerpoStock.Recordset.Fields!Cantidad
                                    
        ' Salida
        If IsNull(CuerpoStock.Recordset.Fields!Salida) = True Then
            rs_stock.Fields!Salida = 0
        Else
            rs_stock.Fields!Saldo = rs_saldo_stock.Fields!Saldo - CuerpoStock.Recordset.Fields!Cantidad
            rs_stock.Fields!Salida = CuerpoStock.Recordset.Fields!Salida
        End If
        
        ' Entrada
        If IsNull(CuerpoStock.Recordset.Fields!Entrada) = True Then
            rs_stock.Fields!Entrada = 0
        Else
            rs_stock.Fields!Saldo = CuerpoStock.Recordset.Fields!Cantidad + rs_saldo_stock.Fields!Saldo
            rs_stock.Fields!Entrada = CuerpoStock.Recordset.Fields!Entrada
        End If
        
        'Desarme
        If Motivo.ListIndex = 9 Then

            'Obtener formula
'            Dim rs_formula As New ADODB.Recordset
'
'            rs_formula.Open "SELECT en_abm_formula.cantidad_articulo " & _
'                            "From en_abm_formula " & _
'                            "WHERE en_abm_formula.id_en_abm_formula = " & CuerpoStock.Recordset.Fields!id_en_abm_formula & " "
'
'            If rs_formula.RecordCount = 0 Then
'                MsgBox "ERROR - Artículo sin formula definida. ", vbInformation, "ATENCION"
'
'                If conn.State = 1 Then
'                    conn.RollbackTrans
'                    conn.Close
'                End If
'
'                Exit Sub
'            End If
            
            ' Entrada - Insumos
            If IsNull(CuerpoStock.Recordset.Fields!Entrada) = True Then
                rs_stock.Fields!Entrada = 0
            Else
            
                If cantDesarme.Text <> 0 Then
            
                    rs_stock.Fields!Saldo = rs_saldo_stock.Fields!Saldo + (cantDesarme.Text * CuerpoStock.Recordset.Fields!Cantidad / 100)
                    
                    rs_stock.Fields!Entrada = (cantDesarme.Text * CuerpoStock.Recordset.Fields!Entrada / 100)
    
    'Cantidad   <- Para los insumos sobreescribo las cantidades
                    rs_stock.Fields!Cantidad = (cantDesarme.Text * CuerpoStock.Recordset.Fields!Entrada / 100)
                
                Else
                
                    rs_stock.Fields!Saldo = rs_saldo_stock.Fields!Saldo + (1 * CuerpoStock.Recordset.Fields!Cantidad)
                    
                    rs_stock.Fields!Entrada = (1 * CuerpoStock.Recordset.Fields!Entrada)
    
    'Cantidad   <- Para los insumos sobreescribo las cantidades
                    rs_stock.Fields!Cantidad = (1 * CuerpoStock.Recordset.Fields!Entrada)
                
                
                End If
                
            End If
            
        End If
        
        '''''''''''''''
        'Multiplicador' Compra
        '''''''''''''''
        rs_stock.Fields!multiplicador_comp = CuerpoStock.Recordset.Fields!multiplicador_comp
        rs_stock.Fields!cantidad_uni = CuerpoStock.Recordset.Fields!cantidad_uni
        
        'Multiplicador Vta
        rs_stock.Fields!multiplicador_vta = CuerpoStock.Recordset.Fields!multiplicador_vta
            
        rs_stock.Fields!orden = CuerpoStock.Recordset.Fields!orden
        rs_stock.Fields!CodViajante = CuerpoStock.Recordset.Fields!CodViajante
        rs_stock.Fields!CodLaboratorio = CuerpoStock.Recordset.Fields!CodLaboratorio
        rs_stock.Fields!CodigoMovimiento = contador
        
        rs_stock.Fields!CodDeposito = DepositoOrigen.BoundText
        
        'Ensamble/Desarme
        If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
            rs_stock.Fields!CodDeposito = IdDeposito
        End If
        
        rs_stock.Fields!IDArt = CuerpoStock.Recordset.Fields!IDArt
        rs_stock.Fields!Detalle = Detalle
        
        If Not IsNull(CuerpoStock.Recordset.Fields!id_manual) Then
            rs_stock.Fields!id_manual = CuerpoStock.Recordset.Fields!id_manual
        End If
                            
        If Not IsNull(CuerpoStock.Recordset.Fields!nro_pedi) Then
            rs_stock.Fields!nro_pedi = CuerpoStock.Recordset.Fields!nro_pedi
            rs_stock.Fields!codmov_nro_pedi = CuerpoStock.Recordset.Fields!codmov_nro_pedi
        End If
        
        ' Validacion de stock en articulos sin lote
        ' Permite salidas sin stock - Valida en salidas                                                    Ensamble/Desarme
        If Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6 Or Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
            If IsNull(CuerpoStock.Recordset.Fields!Lote) Or CuerpoStock.Recordset.Fields!Lote = "No" Then ' Articulo sin lote
    
                If Principal.salida_sin_stock = "No" Then ' Si no permite salida sin stock
                    
                    ' Si es nulo es movimiento de Armado o Desarmado
                    If Not IsNull(CuerpoStock.Recordset.Fields!Salida) Then
                    
                        If CDec(rs_saldo_stock.Fields!Saldo) < CDec(CuerpoStock.Recordset.Fields!Salida) Then
        
                            MsgBox "No hay stock suficiente del artículo: " & CuerpoStock.Recordset.Fields!Descripcion & " su saldo es: " & rs_saldo_stock.Fields!Saldo & "", vbInformation, "ATENCION"
        
                            If conn.State = 1 Then
                                conn.RollbackTrans
                                conn.Close
                            End If
                                
                            ' Descargo formulario de espera
                            Unload form_espera
        
                            Exit Sub
                        
                        End If
        
                    End If
                End If
            End If
        End If
        
'[loteS]'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

        '# por cada stock que tenga lote, grabo el id de lote y disminuyo la lote
        If CuerpoStock.Recordset.Fields!Lote = "Si" Then
        
            'Ajuste
            If Motivo.ListIndex = 1 Then
                'Tiene lote y motivo es ajuste
                MsgBox "No se puede realizar ajuste de articulos con lote", vbInformation, "ATENCION"
                If conn.State = 1 Then
                    conn.RollbackTrans
                    conn.Close
                End If
                Unload form_espera
                Exit Sub
            End If
        
            'Ensamble/Desarme - Lote
            If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
                
                Dim id_lote_ed As Double
                Dim stock_lote_deposito_ed As Double
                
                id_lote_ed = 0
                stock_lote_deposito_ed = 0
                
                'Procedimiento
                Lote_ed id_lote_ed, stock_lote_deposito_ed
                
                rs_stock.Fields!id_lote = id_lote_ed
                rs_stock.Fields!stock_lote_deposito = stock_lote_deposito_ed
                
            End If
            
            '# dependiendo del tipo de movimiento si es stock inicial, tengo que dar de alta el lote caso contrario descuento.
            If Motivo.ListIndex = 0 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 3 Then ' Stock Inicial o Mov. Interno Entrada o Sobrante Mercaderia
                        
                ' Consulto si el cod de lote ya existe, en ese caso actualizo los valores del lote correspondiente
                rs_lote.Open "SELECT * FROM lote WHERE cod_lote = '" & CuerpoStock.Recordset.Fields!cod_lote & "' AND id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & "", conn, adOpenDynamic, adLockOptimistic
                
                ' Si el lote es existente
                If rs_lote.RecordCount = 1 Then
    
                    rs_lote.Fields!stock_total_lote = rs_lote.Fields!stock_total_lote + CuerpoStock.Recordset.Fields!Cantidad
                    rs_lote.Update
    
                    Dim rs_lotestock As New ADODB.Recordset
    
                    rs_lotestock.Open "Select * From lote_stock where id_lote = " & rs_lote.Fields!id_lote & " And " & _
                    "id_deposito = " & DepositoOrigen.BoundText & " ", conn, adOpenDynamic, adLockOptimistic
    
                    If rs_lotestock.RecordCount = 1 Then
                        rs_lotestock.Fields!stock_lote = rs_lotestock.Fields!stock_lote + CuerpoStock.Recordset.Fields!Cantidad
                        rs_lotestock.Update
                        
                    ElseIf rs_lotestock.RecordCount = 0 Then
                        
                        'Nuevo registro en lotestock
                        rs_lotestock.AddNew
                        
                            rs_lotestock.Fields!id_lote = rs_lote.Fields!id_lote
                            rs_lotestock.Fields!stock_lote = CuerpoStock.Recordset.Fields!Cantidad
                            rs_lotestock.Fields!id_deposito = DepositoOrigen.BoundText
                        
                        rs_lotestock.Update
                        
                    End If

                    '# finalmente guardo el id del lote en el stock.
                    rs_stock.Fields!id_lote = rs_lote.Fields!id_lote
                    rs_stock.Fields!stock_lote_deposito = CuerpoStock.Recordset.Fields!Cantidad
    
                    rs_lotestock.Close
                    rs_lote.Close
                
                ' Sino existe el lote agrego uno nuevo
                Else
                        
                     '# creo lote nuevo.
                     rs_lote.Close
                     rs_lote.Open "SELECT * FROM lote where id_lote = 0", conn, adOpenDynamic, adLockOptimistic
                     rs_lote.AddNew
                     rs_lote.Fields!cod_lote = CuerpoStock.Recordset.Fields!cod_lote
                     rs_lote.Fields!fecha_vto_lote = CuerpoStock.Recordset.Fields!vto_lote
                     rs_lote.Fields!id_articulo = CuerpoStock.Recordset.Fields!IDArt
                     rs_lote.Fields!tipo_lote = "No Seriada"
                     rs_lote.Fields!stock_total_lote = CuerpoStock.Recordset.Fields!Cantidad
                     rs_lote.Fields!anulado = "No"
                     rs_lote.Fields!cod_movimiento_entrada = CodMov
                     rs_lote.Fields!id_proveedor = CuerpoStock.Recordset.Fields!CodViajante
                    ' Control error
                    control_error = "cracion lote stock"
                     rs_lote.Update
                     rs_lote.Close
                    
                    '# recupero el id del lote insert
                     rs_lote.Open "SELECT last_insert_id() as id_lote", conn, adOpenDynamic, adLockOptimistic
                     idlote = rs_lote.Fields!id_lote
                     rs_lote.Close
                                                             
                     '# finalmente guardo el id del lote en el stock.
                     rs_stock.Fields!id_lote = idlote
                     rs_stock.Fields!stock_lote_deposito = CuerpoStock.Recordset.Fields!Cantidad
                     
                     'agrego lote deposito en lote_stock
                    rs_lote.Open "SELECT * from lote_stock where id_lote_stock=0"
                    rs_lote.AddNew
                    rs_lote.Fields!id_lote = idlote
                    rs_lote.Fields!stock_lote = CuerpoStock.Recordset.Fields!Cantidad
                    rs_lote.Fields!id_deposito = DepositoOrigen.BoundText
                    ' Control error
                    control_error = "deposito lote stock"
                    rs_lote.Update
                    rs_lote.Close
                    
                End If
                
            End If

            If Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6 Then
                                        
                    'Si es una transferencia debe hacer el mismo procedimiento que una salida
                    rs_lote.Open "SELECT * From Lote " & _
                    "INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) " & _
                    "Where lote.id_lote = " & CuerpoStock.Recordset.Fields!id_lote & " AND " & _
                    "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " AND " & _
                    "lote.anulado = 'No'", conn, adOpenDynamic, adLockOptimistic
                       
                    If rs_lote.Fields!stock_lote >= CuerpoStock.Recordset.Fields!Cantidad Then
                       
                           'Actuliza stock por deposito
                           rs_lote.Fields!stock_lote = rs_lote.Fields!stock_lote - CuerpoStock.Recordset.Fields!Cantidad
                           
                           'Actualiza stock total siempre que no sea una transferencia, en ese caso queda igual
                           If Motivo.ListIndex <> 5 Then 'TRANSFERENCIA
                                
                                rs_lote.Fields!stock_total_lote = rs_lote.Fields!stock_total_lote - CuerpoStock.Recordset.Fields!Cantidad
                           
                           End If
                           
                           rs_lote.Update
                           
                           '# finalmente guardo el id del lote en el stock.
                           rs_stock.Fields!id_lote = CuerpoStock.Recordset.Fields!id_lote
                           rs_stock.Fields!stock_lote_deposito = rs_lote.Fields!stock_lote
                           
                           rs_lote.Close
                    Else
                           
                            sepaso = CuerpoStock.Recordset.Fields!Cantidad - rs_lote.Fields!stock_lote
                           
                            'La cantidad solicitada del articulo xxx se sobrepasa en xxx unidades respercto al stock del deposito"
                            
                            MsgBox "La cantidad solicitada del articulo: " & Chr(34) & " " & CuerpoStock.Recordset.Fields!Descripcion & " " & Chr(34) & " del lote: " & CuerpoStock.Recordset.Fields!cod_lote & "  se sobrepasa en " & sepaso & " unidad/es respecto al stock del deposito ", vbInformation, "ATENCION"
    
                            If conn.State = 1 Then
                               conn.RollbackTrans
                               conn.Close
                            End If
                            
                            ' Descargo formulario de espera
                            Unload form_espera
                            
'                            Nro.Caption = ""
                        
                            Exit Sub
                            
                    End If

            End If
        
    End If 'Fin Lote = "Si"
                                                  
'[loteS]''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
                                                  
        rs_stock.Fields!codSucursal = Principal.codSucursal
        rs_stock.Fields!idUsuario = Principal.idUsuario
        rs_stock.Fields!TipoIVA = CuerpoStock.Recordset.Fields!TipoIVA
        rs_stock.Fields!Tipo = "Movimiento Stock"
        rs_stock.Fields!TipoComp = Motivo.Text
        rs_stock.Fields!anulado = "No"
        rs_stock.Fields!Comprobante = "MSTOCK"
        rs_stock.Fields!NroComprobante = Nro

        ' Guardo el saldo en la tabla stock_deposito
        If rs_saldo_stock.RecordCount > 0 Then
            rs_saldo_stock.Fields!Saldo = rs_stock.Fields!Saldo
            ' Control error
            control_error = "Calculo saldo stock"
            rs_saldo_stock.Update
            rs_saldo_stock.Close
        End If

        rs_stock.Fields!id_ref_movstock = Referencia.BoundText

        ' Control error
        control_error = "Guarda en tabla stock deposito origen"
         
        'Serie
        
        '''''''''''''
        'Serie Stock'
        '''''''''''''
        If CuerpoStock.Recordset.Fields!serie = "Si" Then
            'rs_stock.Fields!id_serie_entrada = CuerpoStock.Recordset.Fields!id_serie_entrada
            rs_stock.Fields!desc_serie = CuerpoStock.Recordset.Fields!desc_serie
            rs_stock.Fields!serie = "Si"
        End If
        
        '06/04/2019
        If Lista_entidad.Visible = True And Lista_entidad.Text <> "" Then
            rs_stock.Fields!CodigoCP = Lista_entidad.BoundText
        End If
        
        ''''''''''''''''''''''''''''
        'UPDATE DE ENTRADA O SALIDA'
        ''''''''''''''''''''''''''''
        rs_stock.Update
                                    
        ' Si es una Transferencia genero el movimiento del deposito de destino
        If Motivo.ListIndex = 5 Then 'TRANSFERENCIA
                                          
            ' Consulto el saldo en la tabla stock_deposito del deposito destino
            rs_saldo_stock.Open "SELECT * FROM stock_deposito WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " And id_deposito = " & DepositoDestino.BoundText & "", conn, adOpenDynamic, adLockOptimistic
                                        
            rs_stock.AddNew
            rs_stock.Fields!Fecha = Format(Fecha, "short date")
            rs_stock.Fields!CodigoArticulo = CuerpoStock.Recordset.Fields!CodigoArticulo
            rs_stock.Fields!Descripcion = CuerpoStock.Recordset.Fields!Descripcion
            
            rs_stock.Fields!PrecioCostoxU = CuerpoStock.Recordset.Fields!PrecioCostoxU
            rs_stock.Fields!PrecioCostoxR = CuerpoStock.Recordset.Fields!PrecioCostoxR
            
            '20/03/2018 VALIDAR SI EL ARTICULO ES EN DOLAR Y HACER LA CONVERSION A PESOS POR EL TIPO DE CAMBIO ACTUAL
            rs_consulta_articulo.Open "SELECT articulo.moneda FROM articulo WHERE IDArt = " & CuerpoStock.Recordset.Fields!IDArt & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
            
            If rs_consulta_articulo.RecordCount = 1 Then
                
                'Precio Costo
                If rs_consulta_articulo.Fields!Moneda = "Dolar" Then
                    CuerpoStock.Recordset.Fields!PrecioCostoxR = CuerpoStock.Recordset.Fields!PrecioCostoxU * CuerpoStock.Recordset.Fields!Cantidad * Principal.cotizacion
                    CuerpoStock.Recordset.Fields!PrecioCostoxU = CuerpoStock.Recordset.Fields!PrecioCostoxU * Principal.cotizacion
                End If
            
            End If
            
            rs_consulta_articulo.Close
            
            rs_stock.Fields!Alicuota = CuerpoStock.Recordset.Fields!Alicuota
            rs_stock.Fields!AlicuotaIB = CuerpoStock.Recordset.Fields!AlicuotaIB
            
            If rs_saldo_stock.RecordCount > 0 Then
                rs_stock.Fields!Saldo = CuerpoStock.Recordset.Fields!Cantidad + rs_saldo_stock.Fields!Saldo
            Else
                rs_stock.Fields!Saldo = 0
            End If
            
            rs_stock.Fields!Entrada = CuerpoStock.Recordset.Fields!Cantidad
            
            rs_stock.Fields!Cantidad = CuerpoStock.Recordset.Fields!Cantidad
            rs_stock.Fields!orden = CuerpoStock.Recordset.Fields!orden
            rs_stock.Fields!CodViajante = CuerpoStock.Recordset.Fields!CodViajante
            rs_stock.Fields!CodLaboratorio = CuerpoStock.Recordset.Fields!CodLaboratorio
            rs_stock.Fields!CodigoMovimiento = contador
            rs_stock.Fields!CodDeposito = DepositoDestino.BoundText
            rs_stock.Fields!IDArt = CuerpoStock.Recordset.Fields!IDArt
            rs_stock.Fields!Detalle = Detalle
            
            If Not IsNull(CuerpoStock.Recordset.Fields!id_manual) Then
                rs_stock.Fields!id_manual = CuerpoStock.Recordset.Fields!id_manual
            End If
            
            '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
            'TRANSFERENCIA Y TIENE LOTE'
            ''''''''''''''''''''''''''''
            Dim rs_lote_origen As New ADODB.Recordset
            
            '#****[lote]*****
            '# En el caso de la transferencia de un deposito a otro, tengo que agregar el lote
            '# como nuevo en caso de que no exista en el deposito destino
            '#o bien aumentar(sumar) el stock de lote a tranferir en el lote existente.
            If CuerpoStock.Recordset.Fields!Lote = "Si" Then
            
                If rs_lote.State = adStateOpen Then
                    rs_lote.Close
                End If

                'Pregunto si el lote seleccionado existe en el deposito destino
                rs_lote.Open "SELECT * FROM lote_stock WHERE id_lote ='" & CuerpoStock.Recordset.Fields!id_lote & "' AND id_deposito=" & DepositoDestino.BoundText, conn, adOpenDynamic, adLockOptimistic

                '# valido que haya una lote
                If rs_lote.RecordCount > 0 Then
                
                    '# situacion 1 el lote existe - le aumento el stock.
                        
                        'Sumo al Destino
                        rs_lote.Fields!stock_lote = rs_lote.Fields!stock_lote + CuerpoStock.Recordset.Fields!Cantidad
                            
                        'Dejo resgistro en Tabla Stock
                        rs_stock.Fields!id_lote = rs_lote.Fields!id_lote
                        rs_stock.Fields!stock_lote_deposito = rs_lote.Fields!stock_lote
                            
                        ' Control error
                        control_error = "Transferencia de lote calculo saldo"
                            
                        rs_lote.Update
                        rs_lote.Close
                    
                Else
                    '# situacion 2 el lote no existe en el deposito destino - doy alta
                        
                        'Doy alta en el Destino
                        rs_lote.AddNew
                        rs_lote.Fields!id_lote = CuerpoStock.Recordset.Fields!id_lote
                        rs_lote.Fields!id_deposito = DepositoDestino.BoundText
                        rs_lote.Fields!stock_lote = CuerpoStock.Recordset.Fields!Cantidad
                        
                        'Dejo resgistro en Tabla Stock
                        rs_stock.Fields!id_lote = CuerpoStock.Recordset.Fields!id_lote
                        rs_stock.Fields!stock_lote_deposito = rs_lote.Fields!stock_lote
                        
                         ' Control error
                        control_error = "Si el lote no existe, Transferencia de lote calculo saldo"
                        
                        rs_lote.Update
                        rs_lote.Close
                End If
            End If
'               '#****[loteS]****
                                            
            rs_stock.Fields!codSucursal = Principal.codSucursal
            rs_stock.Fields!idUsuario = Principal.idUsuario
            rs_stock.Fields!TipoIVA = CuerpoStock.Recordset.Fields!TipoIVA
            rs_stock.Fields!Tipo = "Movimiento Stock"
            rs_stock.Fields!TipoComp = Motivo.Text
            rs_stock.Fields!anulado = "No"
            rs_stock.Fields!Comprobante = "MSTOCK"
            rs_stock.Fields!NroComprobante = Nro

            ' Guardo el saldo en la tabla stock_deposito
'            If rs_saldo_stock.RecordCount > 0 Then
                rs_saldo_stock.Fields!Saldo = rs_stock.Fields!Saldo
                ' Control error
                control_error = "Saldo stock_deposito en transferencia"
                rs_saldo_stock.Update
'            Else
'                rs_saldo_stock.AddNew
'                rs_saldo_stock.Fields!Saldo = rs_stock.Fields!Saldo
'                rs_saldo_stock.Fields!id_articulo = CuerpoStock.Recordset.Fields!IDArt
'                rs_saldo_stock.Fields!id_deposito = DepositoDestino.BoundText
'                rs_saldo_stock.Update
'                ' Control error
'                control_error = "Saldo stock_deposito en transferencia"
'            End If
            
            '''''''''''''''''''''''''''''''''''''''
            'UPDATE DEL ADDNEW DE LA TRANSFERENCIA'
            '''''''''''''''''''''''''''''''''''''''
            
            If EsSerie(CuerpoStock.Recordset.Fields!IDArt) = True Then
                rs_stock.Fields!serie = "Si"
            End If
            
            rs_stock.Update

            rs_saldo_stock.Close
        
        End If  'Fin Transferencia
                            
        CuerpoStock.Recordset.MoveNext
                                   
    Loop '# Fin del while de cuerpostock

    rs_stock.Close
                            
    ' Guardo la info en la tabla movimiento_stock
    rs_movimiento_stock.Open "select * from movimiento_stock where codigo_movimiento = 1", conn, adOpenDynamic, adLockOptimistic
    rs_movimiento_stock.AddNew
    
    rs_movimiento_stock.Fields!Fecha = Fecha
    rs_movimiento_stock.Fields!motivo_movimiento = Motivo
    rs_movimiento_stock.Fields!Detalle = Detalle
    
    rs_movimiento_stock.Fields!Deposito_origen = DepositoOrigen.BoundText
    
    If Motivo.ListIndex = 5 Then    'TRANSFERENCIA
        rs_movimiento_stock.Fields!deposito_destino = DepositoDestino.BoundText
    Else
        rs_movimiento_stock.Fields!deposito_destino = DepositoOrigen.BoundText
    End If
    
    rs_movimiento_stock.Fields!codigo_movimiento = contador
    rs_movimiento_stock.Fields!nro_comprobante = Nro
    rs_movimiento_stock.Fields!nro_comprobante_busq = NroBusq
    rs_movimiento_stock.Fields!id_sucursal = Principal.codSucursal
    rs_movimiento_stock.Fields!id_usuario = Principal.idUsuario
    rs_movimiento_stock.Fields!id_pv = Principal.id_punto_ventac
    rs_movimiento_stock.Fields!id_pv = Principal.id_punto_ventac
    rs_movimiento_stock.Fields!tipo_comprobante = "MSTOCK"
    rs_movimiento_stock.Fields!id_ref_movstock = Referencia.BoundText
    
    ' Si usa proyecto
    If Principal.activ_proyecto = "Si" Then
        rs_movimiento_stock.Fields!ID_Proyecto = ID_Proyecto
    End If
    
    'Desarme
    rs_movimiento_stock.Fields!cant_Desarme = cantDesarme.Text

    ' Control error
    control_error = "movimiento_stock"
    
    '20/03/2018
    rs_movimiento_stock.Fields!CotiDolar = CDbl(Format(Principal.cotizacion, "##,###.00"))
    
    '19/04/2018
    'Mov interno E/S
    If Motivo.ListIndex = 6 Or Motivo.ListIndex = 7 Then
        If Lista_entidad.Text = "" Then
            rs_movimiento_stock.Fields!id_cliente = 0
        Else
            rs_movimiento_stock.Fields!id_cliente = Lista_entidad.BoundText
        End If
    End If
    
    '25/03/2019
    'Mov interno E/S - Transferencia entre depositos
    If Motivo.ListIndex = 6 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 5 Then
        If ListaVendedor.Text = "" Then
            rs_movimiento_stock.Fields!id_vendedor = 0
        Else
            rs_movimiento_stock.Fields!id_vendedor = ListaVendedor.BoundText
        End If
    End If

    rs_movimiento_stock.Update
    rs_movimiento_stock.Close
                              
    ' Actualizo estado del pedido interno  a "Completado" (Se pueden crear mov de stock con pedidos completos o parciales, de ambas formas se marca el pedido interno "Completado")
    ' Guardo la relación Mov Stock - Pedido interno
                                
     CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE visualiza = 'No' AND cuerpostock_mstock.Codusuario = " & Principal.idUsuario
    'CuerpoStock.RecordSource = "SELECT DISTINCT CodigoMovimiento,nro_pedi,codmov_nro_pedi FROM cuerpostock WHERE cuerpostock.Codusuario = " & Principal.idusuario
    CuerpoStock.Refresh
                                    
    If CuerpoStock.Recordset.RecordCount > 0 And Not IsNull(CuerpoStock.Recordset.Fields!nro_pedi) Then
        rs_movstock_pedi.Open "SELECT * FROM movstock_pedi WHERE id_movstock_pedi = 0", conn, adOpenDynamic, adLockOptimistic
        CuerpoStock.Recordset.MoveFirst
        Do While Not CuerpoStock.Recordset.EOF
                                        
        ' Comprobar si el articulo es uno que se agrego nuevo. si no marcarlo en el pedido interno
            If Not IsNull(CuerpoStock.Recordset.Fields!CodigoMovimiento) Then
                                                        
                rs_pedi.Open "SELECT * FROM comp_ped WHERE CodigoMovimiento = " & CuerpoStock.Recordset.Fields!CodigoMovimiento & " And  TipoComprobante = 'PEDI'", conn, adOpenDynamic, adLockOptimistic
                rs_pedi.Fields!Estado = "Completo"
                rs_pedi.Update
                rs_pedi.Close
                            
                ' Guardo la relacion Mov Stock - Pedido interno
                rs_movstock_pedi.AddNew
                rs_movstock_pedi.Fields!codmov_movstock = contador
                rs_movstock_pedi.Fields!codmov_pedi = CuerpoStock.Recordset.Fields!codmov_nro_pedi
                rs_movstock_pedi.Fields!anulado = "No"
                ' Control error
                control_error = "Relacion Mov Stock - Pedido Interno"

                rs_movstock_pedi.Update
                            
            End If
                CuerpoStock.Recordset.MoveNext
        Loop
    
    End If
    
        '''''''
        'Serie'     Insert serie_entrada
        '''''''
    
        GuardarSerie
    
''''''''''''''''''''''''''''''''''
        'GENERAR ASIENTO CONTABLE'
        ''''''''''''''''''''''''''
        If Principal.activ_contabilidad = "Si" Then
        'Si la contabilidad esta activa entonces

            'inicializo variable
            Error_conta = "No"

            generar_asiento_cont

            If Error_conta = "Si" Then
                GoTo captura
            End If

        End If
''''''''''''''''''''''''''''''''''

      If conn.State = 1 Then
        conn.CommitTrans
        conn.Close
      End If
    
    ' Valido si imprime el comprobante
    If Principal.NombImpMSTOCK <> "Sin Impresion" Then
        
        If Proceso_Llamante <> "Stock_Consulta_Avanzada" Then
        
            'Impresion de Comprobante
            If MsgBox("Desea imprimir el comprobante de Movimiento de Stock?", vbQuestion + vbYesNo, "ATENCION") = vbYes Then
                
                DoEvents
                   
                Dim crApp As New CRAXDDRT.Application
                Dim Report As CRAXDDRT.Report
                Dim tbl As CRAXDDRT.DatabaseTable
                Dim Sub_Report_encabezado As CRAXDDRT.Report
                
                ' Sub reporte Logo
                Dim Sub_Report_Logo As CRAXDDRT.Report
                
                ' Conexion
                crApp.LogOnServerEx "pdsodbc.dll", Principal.DSN, Principal.base, "administranet", "a7v8xx0805", , IngresoUsuario.Conex
                    
                'Para loguearse en cualquier BD
                Set Report = crApp.OpenReport(Principal.RutaInformes & "\comp_mov_stock.rpt")
             
                Report.Database.LogOnServerEx "pdsodbc.dll", Principal.DSN, Principal.base, "administranet", "a7v8xx0805"
            
                cfg = Report.Database.Tables.Count
            
                sserver = Principal.DSN 'dsn name
                sDatabase = Principal.base
                sDBUserName = "administranet"
                sDBPassword = "a7v8xx0805"
                For Each tbl In Report.Database.Tables
                    Msdd = tbl.name
                    tbl.SetLogOnInfo sserver, sDatabase, sDBUserName, sDBPassword
                    sTblName = Replace(tbl.name, Mid(tbl.name, 1, 1), UCase(Mid(tbl.name, 1, 1)))
                    tbl.Location = sDatabase & "." & sTblName
                    If Not tbl.TestConnectivity Then
                        '  <can't connect error processing>
                        Exit For
                    End If
                
                Next
            
                ' Actualizar encabezado_empresa_grande.rpt
                Set Sub_Report_encabezado = Report.OpenSubreport("encabezado_empresa_grande.rpt")
            
                sserver = Principal.DSN 'dsn name
                sDatabase = Principal.base
                sDBUserName = "administranet"
                sDBPassword = "a7v8xx0805"
                For Each tbl In Sub_Report_encabezado.Database.Tables
                    Msdd = tbl.name
                    tbl.SetLogOnInfo sserver, sDatabase, sDBUserName, sDBPassword
                    sTblName = Replace(tbl.name, Mid(tbl.name, 1, 1), UCase(Mid(tbl.name, 1, 1)))
                    tbl.Location = sDatabase & "." & sTblName
                    If Not tbl.TestConnectivity Then
                        '  <can't connect error processing>
                        Exit For
                    End If
                Next
                
                ' Parametros de sucursal
                If Sub_Report_encabezado.ParameterFields.Count = 2 Then
                   Sub_Report_encabezado.ParameterFields.GetItemByName("domicilio").AddCurrentValue Principal.sucursal_domicilio
                   Sub_Report_encabezado.ParameterFields.GetItemByName("telefono_email").AddCurrentValue Principal.sucursal_tel_email
                End If
                
                ' Actualizar logo.rpt
                Set Sub_Report_Logo = Report.OpenSubreport("logo.rpt")
                
                For Each tbl In Sub_Report_Logo.Database.Tables
                    Msdd = tbl.name
                    tbl.SetLogOnInfo sserver, sDatabase, sDBUserName, sDBPassword
                    sTblName = Replace(tbl.name, Mid(tbl.name, 1, 1), UCase(Mid(tbl.name, 1, 1)))
                    tbl.Location = sDatabase & "." & sTblName
                    If Not tbl.TestConnectivity Then
                        '  <can't connect error processing>
                        Exit For
                    End If
                Next
                
                ' Impresion y parametros
                Info.Crystal.ReportSource = Report
                Info.Caption = " Comprobante de Movimiento de Inventario y Stock"
                Report.FormulaSyntax = crCrystalSyntaxFormula
                                
                'Parametros
                Report.ParameterFields.GetItemByName("Fecha").AddCurrentValue CDate(Fecha_Informe)
                Report.ParameterFields.GetItemByName("NroMovStock").AddCurrentValue CStr(NroMovStock_Informe)
                Report.ParameterFields.GetItemByName("Usuario").AddCurrentValue CStr(Principal.Codusuario)
                Report.ParameterFields.GetItemByName("dep_origen").AddCurrentValue CStr(DepositoOrigen)
                Report.ParameterFields.GetItemByName("referencia").AddCurrentValue CStr(Referencia.Text)
                Report.ParameterFields.GetItemByName("Detalle").AddCurrentValue CStr(Detalle.Text)
                
                '10/04/2019
                If Lista_entidad.Visible = True And Lista_entidad.Text <> "" Then
                    Report.ParameterFields.GetItemByName("CampoVariable1").AddCurrentValue CStr(Lista_entidad.Text)
                Else
                    Report.ParameterFields.GetItemByName("CampoVariable1").AddCurrentValue "-"
                End If
                
                If ListaVendedor.Visible = True And ListaVendedor.Text <> "" Then
                    Report.ParameterFields.GetItemByName("CampoVariable2").AddCurrentValue CStr(ListaVendedor.Text)
                Else
                    Report.ParameterFields.GetItemByName("CampoVariable2").AddCurrentValue "-"
                End If
                
                If DepositoDestino.Visible = True Then
                    Report.ParameterFields.GetItemByName("dep_destino").AddCurrentValue CStr(DepositoDestino)
                Else
                    Report.ParameterFields.GetItemByName("dep_destino").AddCurrentValue "-"
                End If
                
                'Consulta
                Report.RecordSelectionFormula = " {cuerpostock_mstock.CodUsuario} = " & Principal.idUsuario & " AND {cuerpostock_mstock.visualiza} = 'No'"
                
                'Impresion
                Report.SelectPrinter Principal.NombImpMSTOCK, Principal.NombImpMSTOCK, Principal.puerto_ImpMSTOCK
                Report.PaperSize = Principal.Size_Paper_CR(Principal.tipo_hoja_crystal_ImpMSTOCK)
                Report.PaperOrientation = CInt(Principal.hoja_orientacion_crystal_ImpMSTOCK)
                
                ''''''''''''''''''''
                ' Exportacion a PDF'
                ''''''''''''''''''''
                Info.nombre_informe = ""
                Info.TipoComp = "Movimiento Stock"
                Info.NroComp = Nro
                Info.id_cliente = 0
                Info.NomCliente = ""
                Info.id_proveedor = 0
                Info.id_contacto = 0
                
                'Impresion
                Info.Show
                Info.Crystal.ViewReport
                Set Report = Nothing
                Set crApp = Nothing
                
            End If
    
        End If
    
    End If
    
    form_espera.ProgressBar.Value = 100

    Unload form_espera

'    Termina_GIF
    
     ' Genera comprobante interno (COMODATO)
     If Principal.genera_comp_interno = "Si" Then
        
        ' Si es movimiento de salida de cliente
        If Motivo.ListIndex = 6 And Lista_entidad.Text <> "" Then
      
            ' Valido si hay articulos tipos bienes de uso para generar comprobante COMODATO
            Consulta_Art_Bien_de_uso contador
            
            If Consulta_Art_Bien_de_uso_Variable = "Si" Then
         
                ' Llamo a guardar encabezado de comprobante interno
                Guarda_Comprobante_Interno contador, Fecha, "CI-COMOD", "Comprobante entrega de articulos de bienes de uso", CDbl(Lista_entidad.BoundText), "Entregado"
                
                ' LLamo a impresion de informe de comprobante interno
                Impresion_Comp_Interno contador, Cliente & " - " & Lista_entidad.BoundText, "COMODATO", "Impresion"
        
            End If
        
        End If
    
    End If
   
    
    ' Imprimo Comprobante de Movimiento de Stock
    MsgBox "Se generó el comprobante MSTOCK - " & Nro & " ", vbInformation, "ATENCION"
    
    If Proceso_Llamante = "Stock_Consulta_Avanzada" Then
        Stock_Consulta_Avanzada.Consulta_Busqueda_Avanzada
        Stock_Consulta_Avanzada.Busqueda_Item_Data CStr(CuerpoStock.Recordset.Fields!IDArt)
    End If
    
    Unload Me
    
    ' Si es diferente de Stock_Consulta_Avanzada
    If Proceso_Llamante <> "Stock_Consulta_Avanzada" Then
        CargaMovStock.Show
        Inicial
        
''''''''''''''''''''''''''''''''''''
        'VISUALIZA ASIENTO CONTABLE'
        ''''''''''''''''''''''''''''
        If Principal.activ_contabilidad = "Si" Then
'            Balancea_asiento (contador)
        End If
        
        visualiza_asiento_cont (contador)
        
''''''''''''''''''''''''''''''''''
            
    End If
    
    
    Proceso_Llamante = ""
    
            
End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description & " / Mje error: " & control_error, Me.Caption, Err.Number)
                
        If conn.State = 1 Then
            conn.RollbackTrans
            conn.Close
        End If
        
        Unload form_espera
        
        Unload Me
        CargaMovStock.Show
        Inicial
End Sub

'Private Sub Ajuste_KeyPress(KeyAscii As Integer)
'If KeyAscii = 13 Then
'    Efectivo.SetFocus
'End If
'End Sub

Private Sub AgregarRenglon_Click()
' Capturamos el ERROR
On Error GoTo captura

Dim rs_consul As New ADODB.Recordset

Dim rs_multiV As New ADODB.Recordset
Dim rs_multiC As New ADODB.Recordset

If conn.State <> 1 Then
    conn.ConnectionString = IngresoUsuario.Conex
    conn.CursorLocation = adUseClient
    conn.Open
End If

    'Desarme
'    If Motivo.ListIndex = 9 Then
'        If cantDesarme.Text <= 0 Then
'            MsgBox "La cantidad de desarme debe ser mayor a cero ", vbInformation, "ATENCION"
'            cantDesarme.SetFocus
'            If conn.State = 1 Then
'                conn.Close
'            End If
'            Exit Sub
'        Else
'            cantDesarme.Enabled = False
'        End If
'    End If

' Version 3.4.45 - No se puede cambiar deposito de origen una vez ingresado un registro, debe eliminar todos los articulso
'No se puede cambiar deposito en las salidas o en transferencia
'    If Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6 Then
        'Si es ajuste o transferencia
        DepositoOrigen.Enabled = False
'    Else
'        DepositoOrigen.Enabled = True
'    End If

    ' Si es transferencia
    If Motivo.ListIndex = 5 And DepositoDestino <> "" Then
        DepositoDestino.Enabled = False
    End If
    
    'Ensamble/Desarme
    If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 And DepositoDestino <> "" Then
        DepositoDestino.Enabled = False
    End If

' Validacion para agregar registro
    If ID_Art = 0 Or Articulo = "" Or ES = "" Or Cantidad = 0 Or Cantidad = "" Then
        MsgBox "Debe completar todos los campos", vbInformation, "ATENCION"
        If conn.State = 1 Then
            conn.Close
        End If
        Exit Sub
    End If
    
'Validacion de Transferencia    'Ensamble/Desarme
    If (Motivo.ListIndex = 5 Or Motivo.ListIndex = 8 Or Motivo.ListIndex = 9) And DepositoDestino = "" Then
        MsgBox "Debe completar el deposito destino", vbInformation, "ATENCION"
        DepositoDestino.SetFocus
        
        If conn.State = 1 Then
            conn.Close
        End If
        Exit Sub
    End If
     
'Validacion de lote
    If frame_lote.Visible = True Then
        'Transferencia
        If Principal.mov_stock_utiliza_cbarra = "Si" And (Motivo.ListIndex = 5 Or Motivo.ListIndex = 2 Or Motivo.ListIndex = 6) Then
            'Magia
            EsperaMiliseg 5
        End If
        If lote_articulo = "" Then
            MsgBox "Debe seleccionar un lote", vbCritical + vbOKOnly, "ATENCION"
            lote_articulo.SetFocus
            
             If conn.State = 1 Then
                conn.Close
            End If
            
            Exit Sub
        End If
    End If

    If Lote.Visible = True Then
        ' No ingrese como fecha_vto la actual o anterior
        If Principal.valida_venc_lote = "Si" Then
            If fecha_vto <= Principal.Fecha Then
                MsgBox "La fecha de vencimiento tiene que ser mayor a la fecha actual", vbCritical + vbOKOnly, "ATENCION"
                fecha_vto.SetFocus
                If conn.State = 1 Then
                    conn.Close
                End If
                Exit Sub
            End If
        End If
        
        'codLote vacio
        If nro_lote = "" Then
            MsgBox "Ingrese Nro de lote", vbCritical + vbOKOnly, "ATENCION"
            nro_lote.SetFocus
            If conn.State = 1 Then
                conn.Close
            End If
            Exit Sub
        End If
        
        
'If conn.State <> 1 Then
'    conn.ConnectionString = IngresoUsuario.Conex
'    conn.CursorLocation = adUseClient
'    conn.Open
'End If

        rs_consul.Open "select * from articulo where idart = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        
        'Guardo codigoproveedor del articulo
        CodigoProv = rs_consul.Fields!codigoproveedor

        rs_consul.Close

'        'Consulto si el cod de lote ya existe
'        rs_consul.Open "SELECT * FROM lote WHERE cod_lote = '" & nro_lote & "' AND id_proveedor = " & CodigoProv & "", conn, adOpenDynamic, adLockOptimistic
'
'        If rs_consul.RecordCount > 0 Then
'            MsgBox "El lote ya existe para el proveedor seleccionado, ingrese uno nuevo", vbCritical + vbOKOnly, "ATENCION"
'            nro_lote.SetFocus
'            rs_consul.Close
'            conn.Close
'            Exit Sub
'        End If
'
'        rs_consul.Close
    End If
    
If mod_renglon = "No" Then

        ''''''
        'Alta'
        ''''''
        
        'Ensamble/Desarme
        If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
            ensamble_desarme
            conn.Close
            Exit Sub
        End If
              
        CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
        "Codusuario = " & Principal.idUsuario & " AND " & _
        "CodigoMovimiento =1"
        CuerpoStock.Refresh
        
        ' Agrego registros en Tabla CuerpoStock Temporal (Renglon) para despues guardar el registro en la tabla Stock
        
        CuerpoStock.Recordset.AddNew
        CuerpoStock.Recordset.Fields!IDArt = ID_Art
        CuerpoStock.Recordset.Fields!CodigoArticulo = CodigoArticulo
        CuerpoStock.Recordset.Fields!Descripcion = Articulo
        
        If Principal.utiliza_embalaje = "Si" Then
            
            'Muestro en la grid la cantidad com pres_c
            CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cantidad.Text)

            ' Funcionalidad Bulto Cerrado / Display
            If (Principal.utiliza_bulto_cerrado = "No" Or Principal.utiliza_display = "No") Then

                rs_multiV.Open "SELECT multiplicador_vta, CodigoProveedor FROM articulo WHERE IDArt = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
                If rs_multiV.RecordCount = 1 Then
    
                    If Not IsNull(rs_multiV.Fields!codigoproveedor) Then
                    
                        valor_cantidad = Principal.CambiarComaPunto(Cantidad.Text)
    
                        rs_multiC.Open "SELECT multiplicador_comp, (" & valor_cantidad & " * multiplicador_comp) as Cantidad_pres_comp, cantidad_uni " & _
                                        "FROM articulo_prov WHERE IDArt = " & ID_Art & " AND CodProveedor = " & rs_multiV.Fields!codigoproveedor & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
                        If rs_multiC.RecordCount = 1 Then
                            CuerpoStock.Recordset.Fields!Cantidad = CDbl(rs_multiC.Fields!cantidad_pres_comp)  '*multiV / MultiC
                            GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                            'GridArticulos.Columns(4).Caption = "Cantidad Pres. C"
                            If ES.Text = "Entrada" Then
                                CuerpoStock.Recordset.Fields!Entrada = CDbl(rs_multiC.Fields!cantidad_pres_comp)
                            End If
                            If ES.Text = "Salida" Then
                                CuerpoStock.Recordset.Fields!Salida = CDbl(rs_multiC.Fields!cantidad_pres_comp)
                            End If
                            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * rs_multiC.Fields!cantidad_pres_comp)
                            
                            'Multiplicador Vta
                            CuerpoStock.Recordset.Fields!multiplicador_vta = rs_multiV.Fields!multiplicador_vta
                    
                            'Multiplicador Compra
                            CuerpoStock.Recordset.Fields!multiplicador_comp = rs_multiC.Fields!multiplicador_comp
                            CuerpoStock.Recordset.Fields!cantidad_uni = rs_multiC.Fields!cantidad_uni
                        Else
                            'Cod anterior a presentacion
                            CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
                            GridArticulos.Columns(4).DataField = "Cantidad"
                            'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
                            If ES.Text = "Entrada" Then
                                CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
                            End If
                            If ES.Text = "Salida" Then
                                CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
                            End If
                            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
                        End If
                        
                    End If
    
                End If
            
            End If

            ' Funcionalidad Bulto Cerrado / Display
            If (Principal.utiliza_bulto_cerrado = "Si" Or Principal.utiliza_display = "Si") Then

                    If Obtener_Datos_Articulo_Mayorista(ID_Art, "precio_unidad") = "Display" Then
                        CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_unidad_display")
'                        CuerpoStock.Recordset.Fields!cantidad_dividir = Calculo_Cantidad_Multiplicar_Diplay_Bulto(ID_Art, "Display", "cantidad_dividir", CDbl(Cantidad.Text))
'                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text) * CuerpoStock.Recordset.Fields!cantidad_dividir
                        CuerpoStock.Recordset.Fields!cantidad_bulto = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        
                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text) * CuerpoStock.Recordset.Fields!cantidad_unidad_display * CuerpoStock.Recordset.Fields!cantidad_bulto
                        CuerpoStock.Recordset.Fields!tipo_unidad = "Display"
                    
                        GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                        GridArticulos.Columns(4).Caption = "Cant. Display/Bulto"
                        If ES.Text = "Entrada" Then
                            CuerpoStock.Recordset.Fields!Entrada = CDbl(CuerpoStock.Recordset.Fields!Cantidad)
                        End If
                        If ES.Text = "Salida" Then
                            CuerpoStock.Recordset.Fields!Salida = CDbl(CuerpoStock.Recordset.Fields!Cantidad)
                        End If
                        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * CuerpoStock.Recordset.Fields!cantidad_pres_comp)
                        
                        'Multiplicador Vta
                        CuerpoStock.Recordset.Fields!multiplicador_vta = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_vta")
                
                        'Multiplicador Compra
                        CuerpoStock.Recordset.Fields!multiplicador_comp = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        CuerpoStock.Recordset.Fields!cantidad_uni = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_uni")
                    
                    Else
                        
                        CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_unidad_display")
                        CuerpoStock.Recordset.Fields!cantidad_dividir = Calculo_Cantidad_Multiplicar_Diplay_Bulto(ID_Art, "Display", "cantidad_dividir", CuerpoStock.Recordset.Fields!Cantidad)
                        CuerpoStock.Recordset.Fields!tipo_unidad = "Unidad"
                        
                        'Cod anterior a presentacion
                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
                        GridArticulos.Columns(4).DataField = "Cantidad"
                        'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
                        If ES.Text = "Entrada" Then
                            CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
                        End If
                        If ES.Text = "Salida" Then
                            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
                        End If
                        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
                    
                        'Multiplicador Vta
                        CuerpoStock.Recordset.Fields!multiplicador_vta = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_vta")
                
                        'Multiplicador Compra
                        CuerpoStock.Recordset.Fields!multiplicador_comp = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        CuerpoStock.Recordset.Fields!cantidad_uni = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_uni")
                    
                    End If
            
            End If

        Else
            'Cod anterior a presentacion
            CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
            
            GridArticulos.Columns(4).DataField = "Cantidad"
            'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
            If ES.Text = "Entrada" Then
                CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
            End If
            If ES.Text = "Salida" Then
                CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
            End If
            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
            
            'Cantidad y cantidad_pres_comp quedan iguales
            CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cantidad.Text)
        End If
    
        CuerpoStock.Recordset.Fields!PrecioCostoxU = CDbl(Format(PrecioCostoxU, Principal.Decimales))
        
        'Cod anterior a presentacion
'        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
        
        CuerpoStock.Recordset.Fields!tipo_art = tipo_art
        
        If id_manual <> "" Then
            CuerpoStock.Recordset.Fields!id_manual = id_manual
        End If
        
'        'Cod anteriro a presentacion
'        If ES.Text = "Entrada" Then
'            CuerpoStock.Recordset.Fields!entrada = CDbl(Cantidad)
'        End If
'
'        If ES.Text = "Salida" Then
'            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
'        End If

        CuerpoStock.Recordset.Fields!ES = ES
              
'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
'LOTE'
''''''
        
        'Consulto si el articulo tiene lote
        rs_consul.Open "select * from articulo where idart = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_consul.Fields!Lote = "Si" Then

            If Motivo.ListIndex = 0 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 3 Then     'Stock Inicial o Mov. Interno Entrada

                CuerpoStock.Recordset.Fields!cod_lote = nro_lote.Text
                CuerpoStock.Recordset.Fields!vto_lote = fecha_vto.Value
                CuerpoStock.Recordset.Fields!Lote = "Si"

                'incializo los controles
                fecha_vto.Text = Principal.Fecha
                nro_lote.Text = ""
                frame_lote.Visible = False
                Lote.Visible = False
                
             ElseIf Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6 Then
             
                CuerpoStock.Recordset.Fields!id_lote = lote_articulo.BoundText
                CuerpoStock.Recordset.Fields!cod_lote = lote_articulo.Columns(0).Text
                CuerpoStock.Recordset.Fields!vto_lote = lote_articulo.Columns(1).Text
                CuerpoStock.Recordset.Fields!Lote = "Si"

                lote_articulo.BoundText = ""
                'stock_lote.Caption = ""
                
            Else
                'Ajuste
                CuerpoStock.Recordset.Fields!Lote = "Si"
                
            End If
            
        End If
        
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
            
            CuerpoStock.Recordset.Fields!TipoIVA = TipoIVA
            CuerpoStock.Recordset.Fields!Alicuota = IDAlicuota
            CuerpoStock.Recordset.Fields!Alicuota = IDAlicuotaIB
            CuerpoStock.Recordset.Fields!Codusuario = Principal.idUsuario
            CuerpoStock.Recordset.Fields!Detalle = Detalle.Text

            If Motivo.ListIndex = 6 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 5 Then
                If ListaVendedor.Text = "" Then
                    CuerpoStock.Recordset.Fields!CodViajante = 1
                Else
                    CuerpoStock.Recordset.Fields!CodViajante = ListaVendedor.BoundText
                End If
            End If
            
            CuerpoStock.Recordset.Fields!CodLaboratorio = 1
            CuerpoStock.Recordset.Fields!CodDeposito = DepositoOrigen.BoundText
            CuerpoStock.Recordset.Fields!Visualiza = "No"
            
            ' Calculo stock saldo
            If Principal.calculo_stock_saldo = "Si" And calculo_saldo_directo <> "" Then
                CuerpoStock.Recordset.Fields!CantidadOr = calculo_saldo_directo
            End If
                        
            '''''''
            'Serie'
            '''''''
            If EsSerie(CuerpoStock.Recordset.Fields!IDArt) = True Then
                CuerpoStock.Recordset.Fields!serie = "Si"
            End If
                
            ' Insertar marca / id_marca para comprobantes
            CuerpoStock.Recordset.Fields!id_marca = Obtener_Datos_Marca(CuerpoStock.Recordset.Fields!IDArt, "id_marca")
            CuerpoStock.Recordset.Fields!Marca = Obtener_Datos_Marca(CuerpoStock.Recordset.Fields!IDArt, "marca")
                
            ' Funcionalidad Bulto Cerrado / Display
            If (Principal.utiliza_bulto_cerrado = "Si" Or Principal.utiliza_display = "Si") Then
                CuerpoStock.Recordset.Fields!tipo_unidad = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "precio_unidad")
                CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "cantidad_unidad_display")
                CuerpoStock.Recordset.Fields!cantidad_bulto = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "multiplicador_comp")
            End If
                
            CuerpoStock.Recordset.Update
            
        '''''''
        'Serie'
        '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

        'Alta
        If Not IsNull(CuerpoStock.Recordset.Fields!orden) Then

            Serie_carga.orden = CuerpoStock.Recordset.Fields!orden

        End If
        '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
        
        '''''''
        'Serie'
        '''''''
        AgregarRenglonSerie
        
        CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
        "cuerpostock_mstock.Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' ORDER BY Orden "
        CuerpoStock.Refresh
        GridArticulos.Refresh
        
'Como estaba
'        ' Pongo en blanco los campos para agregar un nuevo registro
'        Cantidad = 0
'        Articulo = ""
'        ID_Art = 0
'
'        'Paso el foco a seleccion de articulo
'        ListaArticulos.SetFocus
'
'        If Principal.mov_stock_utiliza_cbarra = "Si" Then
'            Articulo.SetFocus
'            Cantidad = 1
'        End If
'
'        ' Inhabilito la Combo Motivo para que el USR no pueda cambiar el Motivo una vez agregado renglones
'        If CuerpoStock.Recordset.RecordCount > 0 Then
'            Motivo.Enabled = False
'        End If
End If

If mod_renglon = "Si" Then
        
        ''''''''''''''
        'Modificacion'
        ''''''''''''''
        
        'Ensamble/Desarme
        If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
            'No se permiten modificar cantidade en insumos
            Cantidad.Locked = False
            frame_lote.Visible = False
            Lote.Visible = False
        End If
        
        If Principal.utiliza_embalaje = "Si" Then
            
            'Muestro en la grid la cantidad com pres_c
            CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cantidad.Text)

            ' Funcionalidad Bulto Cerrado / Display
            If (Principal.utiliza_bulto_cerrado = "No" Or Principal.utiliza_display = "No") Then

                rs_multiV.Open "SELECT multiplicador_vta, CodigoProveedor FROM articulo WHERE IDArt = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
                If rs_multiV.RecordCount = 1 Then
    
                    If Not IsNull(rs_multiV.Fields!codigoproveedor) Then
                        
                        valor_cantidad = Principal.CambiarComaPunto(Cantidad.Text)
                        
                        rs_multiC.Open "SELECT multiplicador_comp, (" & valor_cantidad & " * multiplicador_comp) as Cantidad_pres_comp, cantidad_uni " & _
                                       "FROM articulo_prov WHERE IDArt = " & ID_Art & " AND CodProveedor = " & rs_multiV.Fields!codigoproveedor & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
                        If rs_multiC.RecordCount = 1 Then
                            CuerpoStock.Recordset.Fields!Cantidad = CDbl(rs_multiC.Fields!cantidad_pres_comp)  '*multiV / MultiC
                            GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                            'GridArticulos.Columns(4).Caption = "Cantidad Pres. C"
                            If ES.Text = "Entrada" Then
                                CuerpoStock.Recordset.Fields!Entrada = CDbl(rs_multiC.Fields!cantidad_pres_comp)
                                CuerpoStock.Recordset.Fields!Salida = Null
                            End If
                            If ES.Text = "Salida" Then
                                CuerpoStock.Recordset.Fields!Salida = CDbl(rs_multiC.Fields!cantidad_pres_comp)
                                CuerpoStock.Recordset.Fields!Entrada = Null
                            End If
                            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * rs_multiC.Fields!cantidad_pres_comp)
                            
                            'Multiplicador Vta
                            CuerpoStock.Recordset.Fields!multiplicador_vta = rs_multiV.Fields!multiplicador_vta
                    
                            'Multiplicador Compra
                            CuerpoStock.Recordset.Fields!multiplicador_comp = rs_multiC.Fields!multiplicador_comp
                            CuerpoStock.Recordset.Fields!cantidad_uni = rs_multiC.Fields!cantidad_uni
                        Else
                            'Cod anterior a presentacion
                            CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
                            GridArticulos.Columns(4).DataField = "Cantidad"
                            'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
                            If ES.Text = "Entrada" Then
                                CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
                                CuerpoStock.Recordset.Fields!Salida = Null
                            End If
                            If ES.Text = "Salida" Then
                                CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
                                CuerpoStock.Recordset.Fields!Entrada = Null
                            End If
                            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
                        End If
                        
                    End If
    
                End If
            
            End If

            ' Funcionalidad Bulto Cerrado / Display
            If (Principal.utiliza_bulto_cerrado = "Si" Or Principal.utiliza_display = "Si") Then

                    If Obtener_Datos_Articulo_Mayorista(ID_Art, "precio_unidad") = "Display" Then
                        CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_unidad_display")
'                        CuerpoStock.Recordset.Fields!cantidad_dividir = Calculo_Cantidad_Multiplicar_Diplay_Bulto(ID_Art, "Display", "cantidad_dividir", CuerpoStock.Recordset.Fields!Cantidad)
'                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text) * CuerpoStock.Recordset.Fields!cantidad_dividir
'                        CuerpoStock.Recordset.Fields!Cantidad = CuerpoStock.Recordset.Fields!cantidad_dividir
                        CuerpoStock.Recordset.Fields!cantidad_bulto = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text) * CuerpoStock.Recordset.Fields!cantidad_unidad_display * CuerpoStock.Recordset.Fields!cantidad_bulto
                        
                        CuerpoStock.Recordset.Fields!tipo_unidad = "Display"
                    
                        GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                        GridArticulos.Columns(4).Caption = "Cant. Display/Bulto"
                        If ES.Text = "Entrada" Then
                            CuerpoStock.Recordset.Fields!Entrada = CDbl(CuerpoStock.Recordset.Fields!Cantidad)
                        End If
                        If ES.Text = "Salida" Then
                            CuerpoStock.Recordset.Fields!Salida = CDbl(CuerpoStock.Recordset.Fields!Cantidad)
                        End If
                        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * CuerpoStock.Recordset.Fields!cantidad_pres_comp)
                        
                        'Multiplicador Vta
                        CuerpoStock.Recordset.Fields!multiplicador_vta = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_vta")
                
                        'Multiplicador Compra
                        CuerpoStock.Recordset.Fields!multiplicador_comp = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        CuerpoStock.Recordset.Fields!cantidad_uni = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_uni")
                    
                    Else
                        
                        CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_unidad_display")
                        CuerpoStock.Recordset.Fields!cantidad_dividir = Calculo_Cantidad_Multiplicar_Diplay_Bulto(ID_Art, "Display", "cantidad_dividir", CuerpoStock.Recordset.Fields!Cantidad)
                        CuerpoStock.Recordset.Fields!tipo_unidad = "Unidad"
                        
                        'Cod anterior a presentacion
                        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
                        GridArticulos.Columns(4).DataField = "Cantidad"
                        'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
                        If ES.Text = "Entrada" Then
                            CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
                        End If
                        If ES.Text = "Salida" Then
                            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
                        End If
                        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
                    
                        'Multiplicador Vta
                        CuerpoStock.Recordset.Fields!multiplicador_vta = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_vta")
                
                        'Multiplicador Compra
                        CuerpoStock.Recordset.Fields!multiplicador_comp = Obtener_Datos_Articulo_Mayorista(ID_Art, "multiplicador_comp")
                        CuerpoStock.Recordset.Fields!cantidad_uni = Obtener_Datos_Articulo_Mayorista(ID_Art, "cantidad_uni")
                    
                    End If
            
            End If

        Else
            'Cod anterior a presentacion
            CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
            
            GridArticulos.Columns(4).DataField = "Cantidad"
            'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
            If ES.Text = "Entrada" Then
                CuerpoStock.Recordset.Fields!Entrada = CDbl(Cantidad)
                CuerpoStock.Recordset.Fields!Salida = Null
            End If
            If ES.Text = "Salida" Then
                CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
                CuerpoStock.Recordset.Fields!Entrada = Null
            End If
            CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
        End If
        
'        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cantidad.Text)
        CuerpoStock.Recordset.Fields!PrecioCostoxU = CDbl(Format(PrecioCostoxU, Principal.Decimales))
'        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)
        
        ''''''
        'LOTE'
        ''''''
        If CuerpoStock.Recordset.Fields!Lote = "Si" Then
            If CuerpoStock.Recordset.Fields!id_lote <> "" Then
                CuerpoStock.Recordset.Fields!id_lote = lote_articulo.BoundText
                CuerpoStock.Recordset.Fields!cod_lote = lote_articulo.Columns(0).Text
                CuerpoStock.Recordset.Fields!vto_lote = lote_articulo.Columns(1).Text
            Else
                'Alta
                CuerpoStock.Recordset.Fields!cod_lote = nro_lote
                CuerpoStock.Recordset.Fields!vto_lote = fecha_vto
            End If
        End If

'        If ES.Text = "Entrada" Then
'            CuerpoStock.Recordset.Fields!entrada = CDbl(Cantidad)
'            CuerpoStock.Recordset.Fields!Salida = Null
'        End If
'
'        If ES.Text = "Salida" Then
'            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
'            CuerpoStock.Recordset.Fields!entrada = Null
'        End If

        CuerpoStock.Recordset.Fields!ES = ES
        CuerpoStock.Recordset.Fields!Visualiza = "No"
    
        ' Calculo stock saldo
        If Principal.calculo_stock_saldo = "Si" And calculo_saldo_directo <> 0 Then
            CuerpoStock.Recordset.Fields!CantidadOr = calculo_saldo_directo
        End If
        
        ' Insertar marca / id_marca para comprobantes
        CuerpoStock.Recordset.Fields!id_marca = Obtener_Datos_Marca(ID_Art, "id_marca")
        CuerpoStock.Recordset.Fields!Marca = Obtener_Datos_Marca(ID_Art, "marca")
        
        ' Funcionalidad Bulto Cerrado / Display
        If (Principal.utiliza_bulto_cerrado = "Si" Or Principal.utiliza_display = "Si") Then
            CuerpoStock.Recordset.Fields!tipo_unidad = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "precio_unidad")
            CuerpoStock.Recordset.Fields!cantidad_unidad_display = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "cantidad_unidad_display")
            CuerpoStock.Recordset.Fields!cantidad_bulto = Obtener_Datos_Articulo_Mayorista(CuerpoStock.Recordset.Fields!IDArt, "multiplicador_comp")
        End If
        
        CuerpoStock.Recordset.Update
        
        '''''''
        'Serie'
        '''''''
        AgregarRenglonSerie
        
        CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
        "cuerpostock_mstock.Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' ORDER BY Orden "

        CuerpoStock.Refresh
        GridArticulos.Refresh
        
'Como estaba
'        ' Pongo en blanco los campos para agregar un nuevo registro
'        Cantidad = 0
'        Articulo = ""
'        ID_Art = 0
'
'        'Paso el foco a seleccion de articulo
'        ListaArticulos.SetFocus
'
'        If Principal.mov_stock_utiliza_cbarra = "Si" Then
'            Articulo.SetFocus
'            Cantidad = 1
'        End If
'
'        ' Inhabilito la Combo Motivo para que el USR no pueda cambiar el Motivo una vez agregado renglones
'        If CuerpoStock.Recordset.RecordCount > 0 Then
'            Motivo.Enabled = False
'        End If
End If
    
    CuerpoStock.Refresh
    
    ' Pongo en blanco los campos para agregar un nuevo registro
    Cantidad = 0
    Articulo = ""
    ID_Art = 0
    calculo_saldo_directo = 0
    
    ' Si viene de Stock_Consulta_Avanzada
    If CargaMovStock.Proceso_Llamante <> "Stock_Consulta_Avanzada" Then
        'Paso el foco a seleccion de articulo
        ListaArticulos.SetFocus
    End If

    If Principal.mov_stock_utiliza_cbarra = "Si" Then
        Articulo.SetFocus
        Cantidad = 1
    End If
    
    ' Inhabilito la Combo Motivo para que el USR no pueda cambiar el Motivo una vez agregado renglones
    If CuerpoStock.Recordset.RecordCount > 0 Then
        Motivo.Enabled = False
    End If

'''''''''''

'Si se hace un movimiento de ajuste solo se puede ajustar o solo entradas o salidas
    If Principal.activ_contabilidad = "Si" Then     'Si la contabilidad esta activa entonces
        
        If Motivo.ListIndex = 1 Then                ' Si es movimiento por ajuste
        
            If CuerpoStock.Recordset.RecordCount > 0 Then
            
                ES.Enabled = False
            
            End If
        
        End If
        
    End If
    
If mod_renglon = "Si" Then

    Buscar_Articulo_Grilla CStr(id_cuerpostock)
    
    GridArticulos.SetFocus

End If


''''''''''''''''''''
'REINICIO CONTROLES'
''''''''''''''''''''
'Defecto no modifica renglon
mod_renglon = "No"

'Reinicio el caption Stock_lote
stock_lote.Caption = ""

nro_lote = ""
fecha_vto = Principal.Fecha
frame_lote.Visible = False
Lote.Visible = False

If conn.State = 1 Then
    conn.Close
End If

' Si viene de Stock_Consulta_Avanzada
If CargaMovStock.Proceso_Llamante = "Stock_Consulta_Avanzada" Then

    ListaArticulos.Enabled = False
    EliminarRenglon.Enabled = False

End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
        
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Private Sub AgregarRenglonSerie()

    '''''''
    'Serie'
'''''''''''

    ' Entrada
    If ES.ListIndex = 0 Then
    
        If EsSerie(CDbl(ID_Art)) = True Then
    
            If mod_renglon = "Si" Then
                Serie_abm.IDArt = CuerpoStock.Recordset.Fields!IDArt
                Serie_abm.frmTipo = "Mstock"
                Serie_abm.Cant = Cantidad.Text
                Serie_abm.orden = CuerpoStock.Recordset.Fields!orden
                
                'Deposito
'                Serie_abm.id_deposito = CuerpoStock.Recordset.Fields!CodDeposito
                Serie_abm.id_deposito = DepositoOrigen.BoundText
                
                Serie_abm.Show vbModal
            Else
    
                'Alta
                Serie_carga.id_serie_entrada_temp = 0
    
                Serie_carga.IDArt = CuerpoStock.Recordset.Fields!IDArt
                Serie_carga.frmTipo = "Mstock"
                Serie_carga.Cant = Cantidad.Text
                'Buscar siguiente Tag en procedimiento - Todavia no se tiene hasta el Update
    '                    Serie_carga.orden = 0
    
                'Deposito
                Serie_carga.id_deposito = DepositoOrigen.BoundText
    
                Serie_carga.lblPagina.Caption = "1" & "/" & Cantidad.Text
    
                Serie_carga.Show vbModal
            End If
            
        End If
        
    End If
    
    ' Salida
    If ES.ListIndex = 1 Then
        
        If EsSerie(CDbl(ID_Art)) = True Then
        
            If ModRenglon = "Si" Then
                'Modificacion
                Serie_salida.banderaAlta = 1
                Serie_salida.IDArt = CuerpoStock.Recordset.Fields!IDArt
                Serie_salida.frmTipo = "Mstock"
                Serie_salida.orden = CuerpoStock.Recordset.Fields!orden
                Serie_salida.Cant = CuerpoStock.Recordset.Fields!Cantidad
                
                'Deposito
                Serie_salida.id_deposito = CuerpoStock.Recordset.Fields!CodDeposito
                
                Serie_salida.Show vbModal
            Else
                Serie_salida.banderaAlta = 0
                Serie_salida.IDArt = CuerpoStock.Recordset.Fields!IDArt
                Serie_salida.frmTipo = "Mstock"
                'Orden se pasa despues de cuerpostock.update
                Serie_salida.orden = CuerpoStock.Recordset.Fields!orden
                Serie_salida.Cant = Cantidad.Text
                
                'Deposito
                Serie_salida.id_deposito = DepositoOrigen.BoundText
                
                Serie_salida.Show vbModal
             End If
            
        End If
        
    End If

End Sub

Private Sub AgregarRenglon_GotFocus()
If Cantidad.Text = "" Or Cantidad.Text = "0" Then
    MsgBox "Ingresa una cantidad", vbCritical + vbOKOnly, "ATENCION"
    Cantidad.SetFocus
End If
End Sub

Private Sub Busca_PEDI_Click()
' Capturamos el ERROR
On Error GoTo captura

' Validacion si el motivo es transferencia
If Motivo.Text <> "Transferencia" Then
    MsgBox "El motivo debe ser transferencia para poder utilizar la busqueda de pedidos internos", vbExclamation, "ATENCION"
    Exit Sub
End If

' Validacion si selecciona un deposito de destino
If DepositoDestino.Text = "" Then
    MsgBox "Debe seleccionar el deposito de destino", vbCritical, "ATENCION"
    DepositoDestino.SetFocus
    Exit Sub
End If

' Validacion si el cliente tiene pedidos pendientes
conn.CursorLocation = adUseClient
conn.Open

Dim rs_validacion As New ADODB.Recordset

    With rs_validacion
        .ActiveConnection = conn
        .CursorType = adOpenDynamic
        .Source = "SELECT comp_ped.Anulado,comp_ped.TipoComprobante,comp_ped.Estado,movimiento_stock.deposito_destino,movimiento_stock.codigo_movimiento FROM comp_ped,movimiento_stock WHERE comp_ped.Anulado = 'No' AND " & _
        "comp_ped.CodigoMovimiento = movimiento_stock.codigo_movimiento AND " & _
        "comp_ped.TipoComprobante IN ('PEDI') AND comp_ped.tipo_pedido_interno = 'A deposito' AND " & _
        "movimiento_stock.deposito_origen = " & DepositoDestino.BoundText & " AND " & _
        "comp_ped.Estado = 'Pendiente'"
        .Open
    End With

If rs_validacion.RecordCount = 0 Then
    MsgBox "No existen pedidos internos pendientes", vbInformation, "ATENCION"
    conn.Close
    Exit Sub
End If

conn.Close

Lista_Comp_Gral.TipoComprobante = "Pedido interno"
Lista_Comp_Gral.GridComprobante.Columns(3).Visible = False
Lista_Comp_Gral.GridRenglon.Columns(3).Visible = False
Lista_Comp_Gral.GridComprobante.ToolTipText = "Presione ENTER y seleccione todos los items del pedido interno"
Lista_Comp_Gral.CodigoCP = 1
Lista_Comp_Gral.Label_CP.Caption = "Deposito"
Lista_Comp_Gral.NombreCP = CargaMovStock.DepositoDestino
Lista_Comp_Gral.Inicial
Lista_Comp_Gral.Caption = " Lista de pedidos internos por depósito"
Lista_Comp_Gral.Show

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
        
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Private Sub Cancelar_Click()
    If MsgBox("¿Desea cancelar la generación del movimiento de stock?", vbYesNo + vbQuestion, "ATENCION") = vbYes Then
        ' Elimina tabla Temporal cuerpostock
        Elimina_Temporal
        Unload Me
    End If
End Sub

Private Sub DepositoDestino_KeyPress(KeyAscii As Integer)
If KeyAscii = 13 Then
    Detalle.SetFocus
End If
End Sub

Private Sub DepositoDestino_Click(Area As Integer)
    ' Capturamos el ERROR
On Error GoTo captura

If DepositoOrigen <> "" And DepositoDestino.BoundText = "" Then
    If Motivo.ListIndex = 5 Then
        '# voy a buscar los depositos que no esten en el deposito de origen
        DataDepositoD.RecordSource = "SELECT * FROM deposito WHERE deposito.anulado = 'No' AND  CodDeposito <> " & DepositoOrigen.BoundText
        DataDepositoD.Refresh
        
        If DataDepositoD.Recordset.RecordCount > 0 Then
            DepositoDestino.BoundText = DataDepositoD.Recordset.Fields!CodDeposito
        Else
            MsgBox "No existen otros depositos para transferir", vbCritical, "ATENCION"
        End If
    End If
End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub DepositoDestino_LostFocus()
    If Motivo = "Transferencia" Then
        Detalle = "Transferencia de " & DepositoOrigen & " a " & DepositoDestino.Text & ""
    Else
        Detalle = ""
    End If
End Sub

Private Sub DepositoOrigen_KeyPress(KeyAscii As Integer)

    If KeyAscii = 13 Then
                                       'Ensamble/Desarme
        If Motivo = "Transferencia" Or Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
            DepositoDestino.SetFocus
        Else
            Detalle.SetFocus
        End If
        
    End If

End Sub

Private Sub DepositoOrigen_change()
' Capturamos el ERROR
On Error GoTo captura

    If Not IsNull(DepositoOrigen.BoundText) Then
        If Motivo = "Transferencia" Then
            '# voy a buscar los depositos que no esten en el deposito de origen
            DataDepositoD.RecordSource = "SELECT * FROM deposito WHERE deposito.anulado = 'No' AND CodDeposito <> " & DepositoOrigen.BoundText
            DataDepositoD.Refresh
    
            If DataDepositoD.Recordset.RecordCount > 0 Then
                DepositoDestino.BoundText = ""
                DepositoDestino.SetFocus
            Else
                MsgBox "No existen otro depositos para transferir", vbCritical, "ATENCION"
    
            End If
        Else
            'Detalle.SetFocus
        End If
    End If
    
    'Lote
    If Articulo <> "" Then
    
        If Principal.ventana_busqueda_art = "Avanzada" Then
            lote_consulta = ABMArticulo_seleccion.DataABMArt.Recordset.Fields!Lote
        Else
            lote_consulta = ABMArticulo_seleccion_simple.DataABMArt.Recordset.Fields!Lote
        End If
    
    
            '# Comprobacion de un lote
            If lote_consulta = "Si" Then
                DataLote.ConnectionString = IngresoUsuario.Conex
                DataLote.CommandType = adCmdUnknown
                DataLote.RecordSource = "SELECT * FROM lote " & _
                "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                "WHERE lote.id_articulo = " & ID_Art & " AND " & _
                "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
            
                'Debug.Print CargaMovStock.DataLote.Recordset.RecordCount
                CargaMovStock.DataLote.Refresh
            
                    If DataLote.Recordset.RecordCount > 0 Then
                        
                        DataLote.Recordset.MoveFirst
                        'Coloco en la combo lote_articulo el lote y completo stock_lote.text
                        lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
                        stock_lote = DataLote.Recordset.Fields!stock_lote
                    Else
                        lote_articulo.BoundText = ""
                        stock_lote = ""
                    
                    End If
            End If
    End If
    
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Detalle_KeyPress(KeyAscii As Integer)
    If KeyAscii = 13 Then
        Referencia.SetFocus
    End If
End Sub

Private Sub EliminarRenglon_Click()
' Capturamos el ERROR
On Error GoTo captura

    If GridArticulos.EOF Then
        Exit Sub
    End If

    If CuerpoStock.Recordset.RecordCount > 1 Then
    
        If MsgBox("¿Desea eliminar el renglon?", vbQuestion + vbYesNo, "ATENCION") = vbYes Then
        
            
            'Ensamble
'            If Motivo.ListIndex = 8 Then
'                If CuerpoStock.Recordset.Fields!ES = "Salida" Then
'                    MsgBox "No se puede eliminar un artículo que es parte de la formula." & vbCrLf & "Solo se pueden elminar articulos de Entrada.", vbInformation, "ATENCION"
'                End If
'            End If
            
            'Desarme
'            If Motivo.ListIndex = 9 Then
'                If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
'                    MsgBox "No se puede eliminar un artículo que es parte de la formula." & vbCrLf & "Solo se pueden elminar articulos de Salida.", vbInformation, "ATENCION"
'                End If
'            End If
            
            
            id_cuerpostock = Label_ID_cuerpostock
            
            ' Elimino el renglon del articulo seleccionado
            conn.ConnectionString = IngresoUsuario.Conex
            conn.CursorLocation = adUseClient
            conn.Open
            
                'Serie
                eliminarRenglonSerie
            
                conn.Execute "DELETE FROM cuerpostock_mstock WHERE Orden = " & id_cuerpostock

            conn.Close
            
            CalculoTotales
            
            Articulo = ""
            Cantidad = 0
            
            If Principal.mov_stock_utiliza_cbarra = "Si" Then
                Cantidad = 1
            End If
            
            'Si quedo visible lote
            If frame_lote.Visible = True Or Lote.Visible = True Then
                frame_lote.Visible = False
                Lote.Visible = False
            End If
            
        End If
        
    ElseIf CuerpoStock.Recordset.RecordCount = 1 Then
    
        If MsgBox("¿Desea eliminar el renglon?", vbQuestion + vbYesNo, "ATENCION") = vbYes Then
             
             id_cuerpostock = Label_ID_cuerpostock
            
            ' Elimino el renglon del articulo seleccionado
            conn.ConnectionString = IngresoUsuario.Conex
            conn.CursorLocation = adUseClient
            conn.Open
            
                'Serie
                eliminarRenglonSerie
            
                conn.Execute "DELETE FROM cuerpostock_mstock WHERE Orden = " & id_cuerpostock
                
            conn.Close
            
            CalculoTotales
            
            DepositoOrigen.Enabled = True
            
            ' Si es transferencia
            If Motivo.ListIndex = 5 Then
                DepositoDestino.Enabled = True
            End If
            
            Articulo = ""
            Cantidad = 0
            
            If Principal.mov_stock_utiliza_cbarra = "Si" Then
                Cantidad = 1
            End If
            
            'Si quedo visible lote
            If frame_lote.Visible = True Or Lote.Visible = True Then
                frame_lote.Visible = False
                Lote.Visible = False
            End If
            
        End If
        
            'POR SER EL ULTIMO A ELIMINAR HABILITO MOTIVO
            Motivo.Enabled = True
            
            'Ensamble/Desarme
            If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
                'Desarme
                cantDesarme.Enabled = True
                DepositoDestino.Enabled = True
            End If
            
            'Si no hay Item vuelvo a habilitar
            If Principal.activ_contabilidad = "Si" Then     'Si la contabilidad esta activa entonces
                
                If Motivo.ListIndex = 1 Then                ' Si es movimiento por ajuste
                
                    If CuerpoStock.Recordset.RecordCount = 0 Then
                    
                        ES.Enabled = True
                    
                    End If
                
                End If
                
            End If
            
    End If
    
    'Reinicio para evitar error. Alta despues
    'de eliminar una modificacion.
    mod_renglon = "No"

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
        
        If conn.State = 1 Then
            conn.Close
        End If
End Sub


Private Sub eliminarRenglonSerie()

    '''''''
    'Serie'
    '''''''
'    conn.ConnectionString = IngresoUsuario.Conex
'    conn.CursorLocation = adUseClient
'    conn.Open

        'Entrada
'        If ES.ListIndex = 0 Then
            conn.Execute "DELETE serie_entrada_temp.* FROM serie_entrada_temp " & _
                         "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " AND " & _
                         "visualiza = 'No' AND id_usuario = " & Principal.idUsuario & " AND " & _
                         "tipo_comprobante = 'Mstock' AND " & _
                         "orden = " & CuerpoStock.Recordset.Fields!orden & " "
'        End If
        
        'Salida
'        If ES.ListIndex = 1 Then
            conn.Execute "DELETE serie_salida_temp.* FROM serie_salida_temp " & _
                         "WHERE id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " AND " & _
                         "visualiza = 'No' AND id_usuario = " & Principal.idUsuario & " AND " & _
                         "tipo_comprobante = 'Mstock' AND " & _
                         "orden = " & CuerpoStock.Recordset.Fields!orden & " "
'        End If

'    conn.Close

End Sub

Private Sub ES_KeyPress(KeyAscii As Integer)
If KeyAscii = 13 Then
    Cantidad.SetFocus
End If
End Sub

Private Sub Fecha_KeyUp(KeyCode As Integer, Shift As Integer)
If KeyCode = 13 Then
    If Motivo.Enabled = True Then
        Motivo.SetFocus
    Else
        DepositoOrigen.SetFocus
    End If
End If
End Sub

Private Sub fecha_vto_KeyPress(KeyAscii As Integer)
 If KeyAscii = 13 Then
        Cantidad.SetFocus
    End If
End Sub

Private Sub Form_Load()
    ' Deshabilitar el botón de cerrar el formulario
    RemoveCancelMenuItem Me
    
    'Por defecto se ocultan los frame
    frame_lote.Visible = False
    Lote.Visible = False
    
    fecha_vto = Principal.Fecha
    
    'Ensamble/Desarme
    ID_Art = 0
    
    
    '19/04/2018
    data_entidad.ConnectionString = IngresoUsuario.Conex
    data_entidad.CommandType = adCmdUnknown
    data_entidad.RecordSource = "SELECT codigo, nombre_cliente FROM cliente " & _
                                "WHERE cliente.codigo <> 1 ORDER BY nombre_cliente "
    data_entidad.Refresh
    
    '25/03/2019
    dataVendedor.ConnectionString = IngresoUsuario.Conex
    dataVendedor.CommandType = adCmdUnknown
    dataVendedor.RecordSource = "SELECT codViajante, nombre " & _
                                "FROM viajantes " & _
                                "WHERE codViajante <> 1 AND " & _
                                "anulado = 'No' " & _
                                "ORDER BY nombre "
    dataVendedor.Refresh
    
    ListaVendedor.Columns(0).Visible = False

'        Lista_entidad.Columns(0).DataField = Codigo
    Lista_entidad.Columns(1).DataField = "nombre_cliente"
    Lista_entidad.ListField = "nombre_cliente"
    
    Actualiza_Fecha_MySQL
    
    If Proceso_Llamante = "Stock_Consulta_Avanzada" Then
        ' Calculo stock saldo
        If Principal.calculo_stock_saldo = "Si" Then
            calculo_saldo_directo.TabIndex = 0
        End If
    End If
    
    With StatusBar
    .Panels.Add , "Empresa", "Empresa: " & Principal.nombre_empresa & "", sbrText, LoadPicture(App.Path & "\Iconos\Empresa_16.ico")
    'Agregamos el Panel4 y mostramos un texto cualquiera con una imagen
    .Panels.Add , "Sucursal", "Sucursal: " & Principal.nombre_sucursal & "", sbrText, LoadPicture(App.Path & "\Iconos\Sucursal_16.ico")
    'Agregamos el Panel6 y mostramos un texto cualquiera con una imagen
    .Panels.Add , "Usuario", "Usuario: " & Principal.NombreUsuario & "", sbrText, LoadPicture(App.Path & "\Iconos\Usuario_16.ico")
    End With
    
    StatusBar.Panels(1).MinWidth = Me.Width / 3
    StatusBar.Panels(2).MinWidth = Me.Width / 3
    StatusBar.Panels(3).MinWidth = Me.Width / 3
               
    If Principal.tipo_balanza = "Bascula" And Principal.usa_multiplica_bulto_promedio = "Si" Then
    
        lista_unidad_art_peso.Visible = True
    
    Else
    
        lista_unidad_art_peso.Visible = False
    
    End If
    
    Cambio_Fuente_Formulario
        
'    calculo_saldo_directo.ListIndex = 0

'    ' Carga de GIF
'    GIF.fileName = App.Path + "\Archivos\carga-chico2.gif"
'    frame_progreso.Visible = False

End Sub


Private Sub Cambio_Fuente_Formulario()
    Dim i As Integer
    Dim ctl As Control
    i = 1
    For Each ctl In Controls
    
        Debug.Print ctl.name
        
        If Not TypeOf ctl Is Image And Not ctl.name = "LabelComp" And Not ctl.name = "lblDescrip" And Not TypeOf ctl Is OsenXPButton Then
            ctl.Font.size = Principal.fuente_tamano
            ctl.Font.name = Principal.fuente_nombre
'            ' Color objetos
'            ctl.BackColor = &HFFFFFF
        End If
    
        If TypeOf ctl Is TDBGrid Then
            ctl.HeadFont.size = Principal.fuente_tamano
            ctl.HeadFont.name = Principal.fuente_nombre
        End If
                
    
        i = i + 1
    Next ctl

End Sub

'' Centrar GIF
'Private Sub Form_Resize()
'frame_progreso.Left = (Me.ScaleWidth - frame_progreso.Width) / 2
'frame_progreso.Top = (Me.ScaleHeight - frame_progreso.Height) / 2
'End Sub

Private Sub Lista_Proyecto_Click()
' Capturamos el ERROR
On Error GoTo captura

    ' Validacion si existen proyectos asignados al cliente en estado inicial para presupuestar
    conn.ConnectionString = IngresoUsuario.Conex
    conn.CursorLocation = adUseClient
    conn.Open
    Dim rs_proyecto As New ADODB.Recordset
    rs_proyecto.Open "SELECT erp_proyecto.*,erp_zona.nombre_zona,cliente.nombre_cliente as nombre_cliente FROM erp_proyecto " & _
    "LEFT JOIN erp_zona ON erp_zona.id_zona = erp_proyecto.id_zona " & _
    "LEFT JOIN cliente ON cliente.Codigo = erp_proyecto.id_cliente " & _
    "WHERE id_proyecto <> 1 AND " & _
    "estado_proyecto = 'En curso'", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

    '"id_cliente = " & CodigoCliente, conn, adOpenDynamic, adLockOptimistic
    If rs_proyecto.RecordCount = 0 Then
        MsgBox "No existen proyectos asignados al cliente", vbExclamation, "ATENCION"
        conn.Close
        Exit Sub
    End If
    rs_proyecto.Close
    conn.Close
    
    Erp_ABM_Proyecto.Accion = "CargaMovStock"
    Erp_ABM_Proyecto.Show

Exit Sub
'GoTo captura
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
                
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Private Sub btnBuscaCli_Click()
    ABMCliente.Accion = "CargaMovStock"
    ABMCliente.Opcion_Busqueda = "Normal"
    ABMCliente.Inicial
    ABMCliente.Show
End Sub

Private Sub lista_unidad_art_peso_Click()
    Carga_Unidad_Peso.id_articulo = IDArt
    Carga_Unidad_Peso.tipo_comprobante = "MOVSTOCK"
    Carga_Unidad_Peso.unidades = unidad_art_peso
    Carga_Unidad_Peso.Inicial
    Carga_Unidad_Peso.Show
End Sub

Private Sub ModificarRenglon_Click()
' Capturamos el ERROR
On Error GoTo captura

If GridArticulos.EOF Then
    Exit Sub
End If

If CuerpoStock.Recordset.RecordCount > 0 Then

    id_cuerpostock = Label_ID_cuerpostock

    'Ensamble/Desarme
    If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
        'No permite modificar cantidades
        Cantidad.Locked = True
    End If

    Articulo = CuerpoStock.Recordset.Fields!Descripcion
    ID_Art = CuerpoStock.Recordset.Fields!IDArt
    
    'Cod anterior a presentacion
'    Cantidad = CuerpoStock.Recordset.Fields!Cantidad
    
    mod_renglon = "Si"
    
    Cantidad = CuerpoStock.Recordset.Fields!Cantidad
    
    If Principal.calculo_stock_saldo = "Si" And calculo_saldo_directo.Visible = True Then
'        calculo_saldo_directo = GridArticulos.Columns(4).Value
        calculo_saldo_directo = CuerpoStock.Recordset.Fields!Cantidad
        calculo_saldo_directo.SetFocus
    Else
        Cantidad.SetFocus
    End If
        
    ' Si es ajuste permite modificar ES
    If Motivo.ListIndex = 1 Then
        ES.Enabled = True
    End If
    
    
    'Ensamble/Desarme
    'Modificacion de lote
    If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
    
        If CuerpoStock.Recordset.Fields!Lote = "Si" Then
        
            If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
            
                nro_lote = CuerpoStock.Recordset.Fields!cod_lote
                fecha_vto = CuerpoStock.Recordset.Fields!vto_lote
                Lote.Visible = True
                frame_lote.Visible = False
            
            End If
                
            If CuerpoStock.Recordset.Fields!ES = "Salida" Then
                
                If CuerpoStock.Recordset.Fields!id_lote <> "" Then
                    lote_articulo.BoundText = CuerpoStock.Recordset.Fields!id_lote
                    frame_lote.Visible = True
                    Lote.Visible = False
                End If
            
            End If
            
            Exit Sub
        End If
    
    End If
    
    
    If CuerpoStock.Recordset.Fields!Lote = "Si" Then
        ES.Enabled = False
        If CuerpoStock.Recordset.Fields!id_lote <> "" Then
            lote_articulo.BoundText = CuerpoStock.Recordset.Fields!id_lote
            frame_lote.Visible = True
            Lote.Visible = False
            
            'Deposito origen nunca puede estar vacio
            DataLote.ConnectionString = IngresoUsuario.Conex
            DataLote.CommandType = adCmdUnknown
            DataLote.RecordSource = "SELECT * FROM lote " & _
            "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
            "WHERE lote.id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & " AND " & _
            "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
            "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
            DataLote.Refresh

            If DataLote.Recordset.RecordCount > 0 Then
                DataLote.Recordset.MoveFirst
                'Coloco en la combo cargamovstock.lote_articulo el lote y completo stock_lote.text
                lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
                stock_lote = DataLote.Recordset.Fields!stock_lote
            End If

        Else
            'Alta
            nro_lote = CuerpoStock.Recordset.Fields!cod_lote
            fecha_vto = CuerpoStock.Recordset.Fields!vto_lote
            Lote.Visible = True
            frame_lote.Visible = False
        End If
    End If
    
End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Motivo_Click()
' Capturamos el ERROR
On Error GoTo captura

Permiso_Motivo_Puesto

'Desarme
If Motivo.ListIndex = 9 Then
    lblCantDesarme.Visible = True
    cantDesarme.Visible = True
Else
    lblCantDesarme.Visible = False
    cantDesarme.Visible = False
End If

''''''''''''''''''''''''''''''''''''''''''''''''''''''
'Selecciono un articulo desde el boton de busqueda   '
'Y despues al usuario se le ocurre cambiar el motivo.'
'Lote''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    'Consulto si tengo que agregar un lote nuevo o seleccionar uno ya existente
    
    If Principal.mov_stock_utiliza_cbarra = "No" Then

        If ID_Art > 0 Then
        
'            If Principal.ventana_busqueda_art = "Avanzada" Then
'                lote_consulta = ABMArticulo_seleccion.DataABMArt.Recordset.Fields!Lote
'            Else
'                lote_consulta = ABMArticulo_seleccion_simple.DataABMArt.Recordset.Fields!Lote
'            End If
            
            lote_consulta = Obtener_Datos_Articulo(ID_Art, "lote")
        
            If Not IsNull(lote_consulta) And lote_consulta = "Si" Then
    
                If (Motivo.ListIndex = 0 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 3) Then
    
                    'Entrada
                    'Carga de lote nuevo
                    CargaMovStock.Lote.Visible = True
                    CargaMovStock.frame_lote.Visible = False
                    CargaMovStock.fecha_vto.Value = Principal.Fecha
    
                ElseIf (Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6) Then
    
                    'Selecciono lote existente
                    If DepositoOrigen <> "" Then
    
                        '# Comprobacion de un lote
                        If lote_consulta = "Si" Then
                            DataLote.ConnectionString = IngresoUsuario.Conex
                            DataLote.CommandType = adCmdUnknown
                            DataLote.RecordSource = "SELECT * FROM lote " & _
                                                    "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                                                    "WHERE lote.id_articulo = " & ID_Art & " AND " & _
                                                    "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                                                    "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
    
                            'Debug.Print CargaMovStock.DataLote.Recordset.RecordCount
                            CargaMovStock.DataLote.Refresh
    
                                If DataLote.Recordset.RecordCount > 0 Then
    
                                    DataLote.Recordset.MoveFirst
                                    'Coloco en la combo lote_articulo el lote y completo stock_lote.text
                                    lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
                                    stock_lote = DataLote.Recordset.Fields!stock_lote
    
                                End If
                        End If
    
                    End If
    
                    CargaMovStock.Lote.Visible = False
                    CargaMovStock.frame_lote.Visible = True
                Else
                    'Ajuste
                    CargaMovStock.Lote.Visible = False
                    CargaMovStock.frame_lote.Visible = False
                End If
    
            End If  'Lote
        
        End If  'ID_Art > 0

    End If  'cbarra = "No"

''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    If Motivo = "Transferencia" Then
        Es_Transferencia
    Else
        No_Es_Transferencia
    End If

    If Motivo = "Transferencia" Or Motivo = "Faltante Mercaderia" Or Motivo = "Rotura" Or Motivo = "Mov. Interno Salida" Then
        ES.ListIndex = 1
        ES.Enabled = False

        If Motivo = "Transferencia" And DepositoOrigen <> "" Then
            '# voy a buscar los depositos que no esten en el deposito de origen
            DataDepositoD.RecordSource = "SELECT * FROM Deposito WHERE deposito.anulado = 'No' AND CodDeposito <>" & DepositoOrigen.BoundText
            DataDepositoD.Refresh
            
            If DataDepositoD.Recordset.RecordCount > 0 Then
                DepositoDestino.BoundText = ""
                'DepositoDestino.SetFocus
            Else
                MsgBox "No existen otros depositos para transferir", vbCritical, "ATENCION"
                
            End If
        'Else
            'Detalle.SetFocus
        End If
    End If

    If Motivo = "Stock Inicial" Or Motivo = "Sobrante Mercaderia" Or Motivo = "Mov. Interno Entrada" Then
        ES.ListIndex = 0
        ES.Enabled = False
    End If

    If Motivo = "Ajuste" Then
        ES.Enabled = True
    End If

    If Motivo <> "Transferencia" Then
        Detalle = ""
    End If
    
    'Ensamble/Desarme
    If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then

        Es_Transferencia
        Label_Busca_PEDI.Visible = False
        Busca_PEDI.Visible = False
        
        'Mismos depositos que en el origen
        DataDepositoD.RecordSource = DataDepositoO.RecordSource
        DataDepositoD.Refresh
        
        If DataDepositoD.Recordset.RecordCount > 0 Then
            DepositoDestino.BoundText = ""
        Else
            MsgBox "No existen depositos", vbCritical, "ATENCION"
        End If
        
        ''''''''''''''''''''''''''''''''''''''''''''''''''''''
        'Selecciono un articulo desde el boton de busqueda   '
        'Y despues al usuario se le ocurre cambiar el motivo.'
        ''''''''''''''''''''''''''''''''''''''''''''''''''''''
        If ID_Art > 0 Then

            'Lote'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
            Dim rs_lot As New ADODB.Recordset
    
            conn.ConnectionString = IngresoUsuario.Conex
            conn.CursorLocation = adUseClient
            conn.Open
    
            rs_lot.Open "SELECT articulo.lote, articulo.IDArt " & _
                        "From En_abm " & _
                        "INNER JOIN articulo ON (articulo.id_en_abm = en_abm.id_en_abm) " & _
                        "WHERE en_abm.id_en_abm = " & ID_Art & " AND " & _
                        "articulo.lote = 'Si' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
            If rs_lot.RecordCount = 1 Then
            
                Dim id_articulo As Double
                id_articulo = rs_lot.Fields!IDArt
                
                'Armado
                If (Motivo.ListIndex = 8) Then
    
                    'Entrada - Carga de lote nuevo
                    CargaMovStock.Lote.Visible = True
                    CargaMovStock.frame_lote.Visible = False
                    CargaMovStock.fecha_vto.Value = Principal.Fecha
    
                'Desarmado
                ElseIf (Motivo.ListIndex = 9) Then
    
                    'Selecciono lote existente
                    If DepositoOrigen <> "" Then
    
                        '# Comprobacion de un lote
    
                        DataLote.ConnectionString = IngresoUsuario.Conex
                        DataLote.CommandType = adCmdUnknown
                        DataLote.RecordSource = "SELECT * FROM lote " & _
                                                "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                                                "WHERE lote.id_articulo = " & id_articulo & " AND " & _
                                                "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                                                "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
    
                        'Debug.Print CargaMovStock.DataLote.Recordset.RecordCount
                        CargaMovStock.DataLote.Refresh
    
                        If DataLote.Recordset.RecordCount > 0 Then
    
                            DataLote.Recordset.MoveFirst
                            'Coloco en la combo lote_articulo el lote y completo stock_lote.text
                            lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
                            stock_lote = DataLote.Recordset.Fields!stock_lote
    
                        End If
    
                        CargaMovStock.Lote.Visible = False
                        CargaMovStock.frame_lote.Visible = True
    
                    End If
    
                End If
    
            End If
    
            rs_lot.Close
            conn.Close
            
            'Lote'''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
        
        End If
        
    End If 'Fin motivo
    

    '19/04/2018
    'Mov interno E/S
    If Motivo.ListIndex = 6 Or Motivo.ListIndex = 7 Then
        frameCliente.Visible = True
        Motivo.SetFocus
    Else
        frameCliente.Visible = False
'        Motivo.SetFocus
    End If
    
    '25/03/2019
    'Mov interno E/S - Se agrega transferencia
    If Motivo.ListIndex = 6 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 5 Then
        frameVendedor.Visible = True
'        ListaVendedor.SetFocus
    Else
        frameVendedor.Visible = False
'        Motivo.SetFocus
    End If
    
' Calculo saldo directo
' Solo Ajuste
If Motivo.ListIndex = 1 And Principal.calculo_stock_saldo = "Si" Then
    Label_calculo_saldo_directo.Visible = True
    calculo_saldo_directo.Visible = True
    GridArticulos.Columns(5).Visible = True
Else
    Label_calculo_saldo_directo.Visible = False
    calculo_saldo_directo.Visible = False
    GridArticulos.Columns(5).Visible = False
End If

    
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Permiso_Motivo_Puesto()
    
'        Motivo.Clear
'        Motivo.AddItem "Stock Inicial", 0
'        Motivo.AddItem "Ajuste", 1
'        Motivo.AddItem "Faltante Mercaderia", 2
'        Motivo.AddItem "Sobrante Mercaderia", 3
'        Motivo.AddItem "Rotura", 4
'        Motivo.AddItem "Transferencia", 5
'        Motivo.AddItem "Mov. Interno Salida", 6
'        Motivo.AddItem "Mov. Interno Entrada", 7
'        Motivo.AddItem "Armado", 8
'        Motivo.AddItem "Desarmado", 9
    
    If Principal.acceso_motivo_movstock = "Movimiento interno E/S" Then
        Select Case Motivo.ListIndex
        
            Case 0, 1, 2, 3, 4, 5, 8, 9
            
'                MsgBox "Tiene permiso para Movimiento interno E/S", vbExclamation, "ATENCION"
                Motivo.ListIndex = 6
            
        End Select
        
    End If
    
    If Principal.acceso_motivo_movstock = "Ajuste" Then
        Motivo.Clear
        
        Select Case Motivo.ListIndex
        
            Case 0, 5, 6, 7, 8, 9
            
'                MsgBox "Tiene permiso para Movimiento interno E/S", vbExclamation, "ATENCION"
                Motivo.ListIndex = 1
            
        End Select
        
        
    End If

    If Principal.acceso_motivo_movstock = "Transferencia" Then
        Select Case Motivo.ListIndex
        
            Case 0, 1, 2, 3, 4, 6, 7, 8, 9
            
'                MsgBox "Tiene permiso para Transferencia", vbExclamation, "ATENCION"
                Motivo.ListIndex = 5
            
        End Select
    End If
End Sub


Private Sub Motivo_KeyPress(KeyAscii As Integer)
    If KeyAscii = 13 Then
        DepositoOrigen.SetFocus
    End If
End Sub

Private Sub Menu()

    With MenuPrincipal.MenuItems
        .Add 0, "keyArchivo", , "&Archivo"
        .Add "keyArchivo", "keySalir", smiPicture, "&Salir", , , vbKeyEscape
        .Add "keyArchivo", "keyGuardar", smiNone, , , , vbKeyF12
        .Add "keyArchivo", "keyBuscar", smiNone, , , , vbKeyF2
        .Add "keyArchivo", "keyAceptarRenglon", smiNone, , , , vbKeyF3
        .Add "keyArchivo", "keyModificaRenglon", smiNone, , , , vbKeyF4
        .Add "keyArchivo", "keyEliminar", smiNone, , , , vbKeyF5
        .Add "keyArchivo", "keyPEDI", smiNone, , , , vbKeyF6
        .Add "keyArchivo", "keySerie", smiNone, , , , vbKeyF7
    End With
    
End Sub

Private Sub MenuPrincipal_Click(ByVal ID As Long)
    With MenuPrincipal.MenuItems
        Select Case .key(ID)
            Case "keySalir"
                Cancelar_Click
            Case "keyGuardar"
                Aceptar_Click
            Case "keyBuscar"
                ListaArticulos_Click
            Case "keyAceptarRenglon"
                AgregarRenglon_Click
            Case "keyEliminar"
                EliminarRenglon_Click
            Case "keyPEDI"
                Busca_PEDI_Click
            Case "keyModificaRenglon"
                ModificarRenglon_Click
            Case "keySerie"
                ABMSerie_Click
        End Select
    End With
End Sub

Private Sub Es_Transferencia()
LabelDestino.Visible = True
DepositoDestino.Visible = True
Busca_PEDI.Visible = True
Label_Busca_PEDI.Visible = True
End Sub

Private Sub No_Es_Transferencia()
LabelDestino.Visible = False
DepositoDestino.Visible = False
Busca_PEDI.Visible = False
Label_Busca_PEDI.Visible = False
End Sub

Private Sub ListaArticulos_Click()
   On Error GoTo captura
   
    'Ensamble/Desarme
    If Motivo.ListIndex = 8 Or Motivo.ListIndex = 9 Then
        
'        En_abm.Visualiza = "Si"
        En_abm.Show
        En_abm.Accion = "CargaMovStock"
        Exit Sub
        
    End If

    'Como estaba
    
    If Principal.ventana_busqueda_art = "Avanzada" Then
        ABMArticulo_seleccion.Busqueda.Text = ""

        If Principal.calculo_stock_saldo = "Si" And Motivo.ListIndex = 1 Then
            ABMArticulo_seleccion.calculo_saldo = "Si"
        Else
            ABMArticulo_seleccion.calculo_saldo = "No"
        End If

        ABMArticulo_seleccion.Inicial
        ABMArticulo_seleccion.DataABMArt.RecordSource = "SELECT * from articulo where idart=0"
        ABMArticulo_seleccion.DataABMArt.Refresh
        ABMArticulo_seleccion.TipoArt_Busqueda = "CargaMovStock"
        ABMArticulo_seleccion.Accion = "CargaMovStock"
        ABMArticulo_seleccion.Show
        ABMArticulo_seleccion.Busqueda.SetFocus
    
    Else
    
    'Prueba
        ABMArticulo_seleccion_simple.Busqueda.Text = ""

        If Principal.calculo_stock_saldo = "Si" And Motivo.ListIndex = 1 Then
            ABMArticulo_seleccion_simple.calculo_saldo = "Si"
        Else
            ABMArticulo_seleccion_simple.calculo_saldo = "No"
        End If
        
        ABMArticulo_seleccion_simple.Inicial
        ABMArticulo_seleccion_simple.DataABMArt.RecordSource = "SELECT * from articulo where idart=0"
        ABMArticulo_seleccion_simple.DataABMArt.Refresh
        ABMArticulo_seleccion_simple.TipoArt_Busqueda = "CargaMovStock"
        ABMArticulo_seleccion_simple.Accion = "CargaMovStock"
        ABMArticulo_seleccion_simple.Show
        ABMArticulo_seleccion_simple.Busqueda.SetFocus
    
    End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Cantidad_GotFocus()
    Cantidad.SelStart = 0
    Cantidad.SelLength = Len(Cantidad.Text)
    'AgregarRenglon.Enabled = False
End Sub

Private Sub Cantidad_KeyPress(KeyAscii As Integer)
' Capturamos el ERROR
On Error GoTo captura

    KeyAscii = Principal.SoloNumeros(KeyAscii, Cantidad)
    
    If KeyAscii = 13 Then
        
            If Principal.tipo_balanza = "Bascula" And Principal.usa_multiplica_bulto_promedio = "Si" Then

                Carga_Unidad_Peso.id_articulo = IDArt
                Carga_Unidad_Peso.tipo_comprobante = "MOVSTOCK"
                Carga_Unidad_Peso.unidades = 1
                Carga_Unidad_Peso.Inicial
                Carga_Unidad_Peso.Show
                
            Else
            
                AgregarRenglon.SetFocus

            End If
        
    End If
    
    If Principal.mov_stock_utiliza_cbarra = "Si" And Principal.tipo_balanza <> "Bascula" Then
    
        If KeyAscii = 13 And Articulo = "" Then
            Articulo.SetFocus
        End If
        
        If KeyAscii = 13 And Articulo <> "" Then
            AgregarRenglon.SetFocus
        End If
    
    End If
    
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Cantidad_LostFocus()
If Cantidad.Text = "0" And Cantidad.Text = "" Then
    MsgBox "Debe ingresar la cantidad de artículos", vbCritical + vbOKOnly, "ATENCION"
    Cantidad.SetFocus
End If
End Sub

Private Sub nro_lote_KeyPress(KeyAscii As Integer)
' Capturamos el ERROR
On Error GoTo captura

    If KeyAscii = 13 Then

        fecha_vto.SetFocus
    
    End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub lote_articulo_Click()
    stock_lote.Caption = lote_articulo.Columns(2).Text
End Sub

Private Sub lote_articulo_SelChange(Cancel As Integer)
    stock_lote.Caption = lote_articulo.Columns(2).Text
End Sub

Private Sub lote_articulo_KeyPress(KeyAscii As Integer)
If KeyAscii = 13 Then
    Cantidad.SetFocus
End If
End Sub

Public Sub Elimina_Temporal()

    If conn.State = 1 Then
        conn.Close
    End If
    
    ' Elimino la tabla temporal cuerpostock que corresponde al usuario
    conn.ConnectionString = IngresoUsuario.Conex
    conn.CursorLocation = adUseClient
    conn.Open
    
        conn.Execute "delete from cuerpostock_mstock where Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' "
    
        '''''''
        'Serie'
        conn.Execute "DELETE serie_entrada_temp.* FROM serie_entrada_temp " & _
                     "WHERE id_usuario = " & Principal.idUsuario & " AND  " & _
                     "visualiza = 'No' AND tipo_comprobante = 'Mstock' "
        
        conn.Execute "DELETE serie_salida_temp.* FROM serie_salida_temp " & _
                     "WHERE id_usuario = " & Principal.idUsuario & " AND  " & _
                     "visualiza = 'No' AND tipo_comprobante = 'Mstock' "
        
    conn.Close

End Sub

Public Sub CalculoTotales()
' Capturamos el ERROR
On Error GoTo captura

    CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
    "cuerpostock_mstock.Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' ORDER BY Orden "
    CuerpoStock.Refresh
    GridArticulos.Refresh
    
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
End Sub

Private Sub Referencia_KeyPress(KeyAscii As Integer)

    If KeyAscii = 13 Then
        ListaArticulos.SetFocus
    End If
    
    If Principal.mov_stock_utiliza_cbarra = "Si" Then
        Articulo.SetFocus
    End If
    
End Sub

Private Sub generar_asiento_cont()

Dim rs_ejercicio As New ADODB.Recordset
Dim rs_periodo As New ADODB.Recordset
Dim rs_newasiento As New ADODB.Recordset
Dim rs_nroasiento As New ADODB.Recordset
'Dim rs_renglon As New ADODB.Recordset
Dim rs_AsientoCtas As New ADODB.Recordset
'Dim rs_codmov As New ADODB.Recordset
Dim rs_SaldoCtaCont As New ADODB.Recordset
Dim rs_config As New ADODB.Recordset
Dim idEjercicio As Double
Dim idPeriodo As Double
Dim rs_InfoCta As New ADODB.Recordset
Dim rs_iva1 As New ADODB.Recordset
Dim rs_iva2 As New ADODB.Recordset
Dim rs_vta As New ADODB.Recordset
Dim rs_dscto As New ADODB.Recordset
Dim rs_caja As New ADODB.Recordset
Dim SumDebe As Double
Dim SumHaber As Double
Dim i As Integer
Dim j As Integer
Dim K As Integer
Dim L As Integer

        rs_config.Open "SELECT activ_contabilidad from configuracion", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            If rs_config.Fields!activ_contabilidad <> "Si" Then

                Exit Sub

            End If

        rs_config.Close
        
        
        '25/04/2016
        'Por desicion del jefe los movimientos de AJUSTE(Entrada/Salida) dejan de registrar asientos contables
        'En caso de arrepentimiento buscar por tag Fecha y sacar el codigo
        If Motivo.ListIndex = 1 Then
            Exit Sub
        End If
        

        '''''''''''''''''''''''''''''''''''''''''''''''''''''''
        'Por Codigo - Obtengo las cuentas del asiento contable'
        '''''''''''''''''''''''''''''''''''''''''''''''''''''''

        'Col -> |id_pc |Debe|Haber|
        Dim MatAsiento(10, 3) As Double

        ''''''''''''''''''''''''
        'CONFIGURAR MANUALMENTE'
        ''''''''''''''''''''''''

        'DEBE MatAsiento(i, 2) / HABER MatAsiento(i, 3)

        'VARIABLES

i = 0

        Dim total As Double

        '0   Stock Inicial
'        If Motivo.ListIndex = 0 Then

        '    If CuerpoStock.Recordset.RecordCount > 0 Then
        '
        '        CuerpoStock.Recordset.MoveFirst
        '
        '        Do While Not CuerpoStock.Recordset.EOF
        '
        '            total = total + CuerpoStock.Recordset.Fields!PrecioCostoxR
        '
        '        Loop
        '
        '    End If

            '''''''''''''
            'Mercaderias'
'''''''''''''''''''''''''

            'SI UTILIZA CONTABILIDAD POR ARTICULO

             '''''''''''''''''''''''''''
             'CONTABILIDAD POR ARTICULO'
             '''''''''''''''''''''''''''

              Dim VectTemp(10, 2) As Double
              Dim rs_vect As New ADODB.Recordset

              ''''''''''''''''''''''''''''
              'Lleno vector con los id_pc'
              ''''''''''''''''''''''''''''

             If CuerpoStock.Recordset.RecordCount > 0 Then

                 CuerpoStock.Recordset.MoveFirst

                 L = 0  'Ultima pos cargada en el vector

                 Do While Not CuerpoStock.Recordset.EOF

                     rs_vect.Open "SELECT * from articulo where idart = " & CuerpoStock.Recordset.Fields!IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                         'Pos 1
                         K = 1   'Recorre el vector
                         flagRepetido = "No"

                         If Not IsNull(rs_vect.Fields!id_pc_comp) And rs_vect.Fields!id_pc_comp <> 0 Then

                             'No esta ya cargado en la matriz?
                             Do Until K > UBound(VectTemp, 1)

                             '''UBound(VectTemp,1)

                                 ' LBound = posicion del primer item de un arreglo
                                 ' UBound = Posicion del ultimo item de un arreglo

                                 If VectTemp(K, 1) = rs_vect.Fields!id_pc_comp Then


                                        ''''''''''''''''''''''''
                                        'Acumulo en dimension 2'
                                        ''''''''''''''''''''''''

                                        VectTemp(K, 2) = VectTemp(K, 2) + CDec(CuerpoStock.Recordset.Fields!PrecioCostoxR)

                                        flagRepetido = "Si"


                                 End If

                                 K = K + 1

                             Loop


                             'Las cuentas tienen id_pc_comp y es un renglon que no esta repetido. Doy de alta en el vect temp
                             If flagRepetido <> "Si" Then

                                 L = L + 1

                                 VectTemp(L, 1) = rs_vect.Fields!id_pc_comp
                                 VectTemp(L, 2) = CDec(CuerpoStock.Recordset.Fields!PrecioCostoxR)

                             End If

                         Else

                             ''''''''''''''''''''''''''''''''''''''''''
                             'id_pc_vta NULO -> Acumulo en Mercaderias'
                             ''''''''''''''''''''''''''''''''''''''''''

                             rs_vta.Open "SELECT * from cont_paramatriz where id_paramatriz='13'", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                             If rs_vta.RecordCount = 1 Then

                                 If IsNull(rs_vta.Fields!id_pc) Then

                                     rs_vta.Close
                                     'MsgBox "venta con id_pc NULL, Error Interno", vbInformation
                                     Error_conta = "Si"
                                     Exit Sub

                                 End If

                             End If

                             'Idpc de la cuenta es nulo entonces utilizo el id de cont_paramatriz
                             'Ademas el id de cont_paramatriz ya se encuentra cargado en vect temp
                             Do Until K > UBound(VectTemp, 1)

                                 If VectTemp(K, 1) = rs_vta.Fields!id_pc Then

                                        ''''''''''''''''''''''''
                                        'Acumulo en dimension 2'
                                        ''''''''''''''''''''''''

                                        VectTemp(K, 2) = VectTemp(K, 2) + CDec(CuerpoStock.Recordset.Fields!PrecioCostoxR)

                                        flagRepetido = "Si"


                                 End If

                                 K = K + 1

                             Loop


                             If flagRepetido <> "Si" Then

                                 L = L + 1

                                 VectTemp(L, 1) = rs_vta.Fields!id_pc
                                 VectTemp(L, 2) = CuerpoStock.Recordset.Fields!PrecioCostoxR

                             End If

                             rs_vta.Close

                         End If

                     rs_vect.Close

                     CuerpoStock.Recordset.MoveNext

                 Loop

             Else

                 'ERROR sin articulos
                 Error_conta = "Si"
                 Exit Sub

             End If
        
        '0 Stock Inicial
        If Motivo.ListIndex = 0 Then
        
             ''''''''''''''''''''''''''''''''''''''''''''''''''''''''
             'Verifico llenado correcto de la matriz
             'Completo la matriz con el vector temporal
             K = 1

             Do Until K > UBound(VectTemp, 1)

                     'Debug.Print k, m

                     If VectTemp(K, 1) <> 0 Then

                         i = i + 1

                         MatAsiento(i, 1) = VectTemp(K, 1)
                         MatAsiento(i, 2) = CDec(VectTemp(K, 2))

'''''''''''''''''''''''''''''''''
                         'PROBAR'
                         ''''''''

                         Dim TotalTemp As Double

                         TotalTemp = TotalTemp + CDec(VectTemp(K, 2))

                         'Suma de los renglones
                         MatAsiento(i, 3) = CDec(0)     'Con IVA

                     End If

                 K = K + 1

             Loop

            ''''''''''''''''''
            'A capital social'
''''''''''''''''''''''''''''''

            'Utilizo la cuenta contable definida en la matriz
            Dim rs_CapitalMat As New ADODB.Recordset

            rs_CapitalMat.Open "SELECT * from cont_paramatriz where id_paramatriz = 46", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            i = i + 1

                If rs_CapitalMat.RecordCount = 1 And Not IsNull(rs_CapitalMat.Fields!id_pc) Then

                    MatAsiento(i, 1) = rs_CapitalMat.Fields!id_pc
                    MatAsiento(i, 2) = CDec(0)
                    MatAsiento(i, 3) = CDec(TotalTemp)

                Else

                    rs_CapitalMat.Close
                    'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                    Error_conta = "Si"
                    Exit Sub

                End If

            rs_CapitalMat.Close

        End If


'        '1   Ajuste
'        If Motivo.ListIndex = 1 Then
'
'            If ES.Text = "Entrada" Then
'
'
'            Else    'Salida
'
'
'            End If
'
'        End If


        '2   Faltante de Mercaderia o Ajuste por salida
        If Motivo.ListIndex = 2 Or (Motivo.ListIndex = 1 And ES.Text = "Salida") Then

            ''''''''''''''''''''''''
            'Faltante de Mercaderia'
''''''''''''''''''''''''''''''''''''

             i = i + 1

            Dim rs_faltant As New ADODB.Recordset

            rs_faltant.Open "SELECT * from cont_paramatriz where id_paramatriz= 34 ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            If rs_faltant.RecordCount = 1 And Not IsNull(rs_faltant.Fields!id_pc) Then

                MatAsiento(i, 1) = rs_faltant.Fields!id_pc
            
                TotalTemp = 0
                
                If CuerpoStock.Recordset.RecordCount > 0 Then
                
                    CuerpoStock.Recordset.MoveFirst
                    
                    Do While Not CuerpoStock.Recordset.EOF
                    
                        TotalTemp = TotalTemp + CuerpoStock.Recordset.Fields!PrecioCostoxR
                    
                        CuerpoStock.Recordset.MoveNext
                    
                    Loop
                    
                Else
                    rs_faltant.Close
                    'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                    Error_conta = "Si"
                    Exit Sub
                End If
                
                MatAsiento(i, 2) = CDec(TotalTemp)
                MatAsiento(i, 3) = CDec(0)

            Else

                rs_faltant.Close
                'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                Error_conta = "Si"
                Exit Sub

            End If

            rs_faltant.Close

            '''''''''''''''
            'A Mercaderias'
'''''''''''''''''''''''''''

             ''''''''''''''''''''''''''''''''''''''''''''''''''''''''
             'Verifico llenado correcto de la matriz
             'Completo la matriz con el vector temporal
             K = 1

             Do Until K > UBound(VectTemp, 1)

                     'Debug.Print k, m

                     If VectTemp(K, 1) <> 0 Then

                         i = i + 1

                         MatAsiento(i, 1) = VectTemp(K, 1)
                         MatAsiento(i, 2) = CDec(0)

                         'Suma de los renglones
                         MatAsiento(i, 3) = CDec(VectTemp(K, 2))

                     End If

                 K = K + 1

             Loop

        End If

        '3   Sobrante de Mercaderia - Ajuste por entrada
        If Motivo.ListIndex = 3 Or (Motivo.ListIndex = 1 And ES.Text = "Entrada") Then
        
            '''''''''''''
            'Mercaderias'
'''''''''''''''''''''''''

             ''''''''''''''''''''''''''''''''''''''''''''''''''''''''
             'Verifico llenado correcto de la matriz
             'Completo la matriz con el vector temporal
             K = 1

             Do Until K > UBound(VectTemp, 1)

                     'Debug.Print k, m

                     If VectTemp(K, 1) <> 0 Then

                         i = i + 1

                         MatAsiento(i, 1) = VectTemp(K, 1)
                         MatAsiento(i, 2) = CDec(VectTemp(K, 2))

                         'Suma de los renglones
                         MatAsiento(i, 3) = CDec(0)

                     End If

                 K = K + 1

             Loop
        
            ''''''''''''''''''''''''''
            'A Sobrante de Mercaderia'
''''''''''''''''''''''''''''''''''''''

             i = i + 1

            Dim rs_sobrante As New ADODB.Recordset

            rs_sobrante.Open "SELECT * from cont_paramatriz where id_paramatriz= 33 ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            If rs_sobrante.RecordCount = 1 And Not IsNull(rs_sobrante.Fields!id_pc) Then

                MatAsiento(i, 1) = rs_sobrante.Fields!id_pc
            
                TotalTemp = 0
                
                If CuerpoStock.Recordset.RecordCount > 0 Then
                
                    CuerpoStock.Recordset.MoveFirst
                    
                    Do While Not CuerpoStock.Recordset.EOF
                    
                        TotalTemp = TotalTemp + CuerpoStock.Recordset.Fields!PrecioCostoxR
                    
                        CuerpoStock.Recordset.MoveNext
                    
                    Loop
                    
                Else
                    rs_sobrante.Close
                    'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                    Error_conta = "Si"
                    Exit Sub
                End If
                
                MatAsiento(i, 2) = CDec(0)
                MatAsiento(i, 3) = CDec(TotalTemp)

            Else

                rs_sobrante.Close
                'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                Error_conta = "Si"
                Exit Sub

            End If

            rs_sobrante.Close

        End If

        '4   Rotura
        If Motivo.ListIndex = 4 Then
        
            ''''''''''''''''''''''
            'Rotura de Mercaderia'
''''''''''''''''''''''''''''''''''

             i = i + 1

            Dim rs_rotura As New ADODB.Recordset

            rs_rotura.Open "SELECT * from cont_paramatriz where id_paramatriz= 35 ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            If rs_rotura.RecordCount = 1 And Not IsNull(rs_rotura.Fields!id_pc) Then

                MatAsiento(i, 1) = rs_rotura.Fields!id_pc
            
                TotalTemp = 0
                
                If CuerpoStock.Recordset.RecordCount > 0 Then
                
                    CuerpoStock.Recordset.MoveFirst
                    
                    Do While Not CuerpoStock.Recordset.EOF
                    
                        TotalTemp = TotalTemp + CuerpoStock.Recordset.Fields!PrecioCostoxR
                    
                        CuerpoStock.Recordset.MoveNext
                    
                    Loop
                    
                Else
                    rs_rotura.Close
                    'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                    Error_conta = "Si"
                    Exit Sub
                End If
                
                MatAsiento(i, 2) = CDec(TotalTemp)
                MatAsiento(i, 3) = CDec(0)

            Else

                rs_rotura.Close
                'MsgBox "ImpInt con id_pc NULL, Error Interno", vbInformation
                Error_conta = "Si"
                Exit Sub

            End If

            rs_rotura.Close

            '''''''''''''''
            'A Mercaderias'
'''''''''''''''''''''''''''

             ''''''''''''''''''''''''''''''''''''''''''''''''''''''''
             'Verifico llenado correcto de la matriz
             'Completo la matriz con el vector temporal
             K = 1

             Do Until K > UBound(VectTemp, 1)

                     'Debug.Print k, m

                     If VectTemp(K, 1) <> 0 Then

                         i = i + 1

                         MatAsiento(i, 1) = VectTemp(K, 1)
                         MatAsiento(i, 2) = CDec(0)

                         'Suma de los renglones
                         MatAsiento(i, 3) = CDec(VectTemp(K, 2))

                     End If

                 K = K + 1

             Loop

        End If

        '5   transferencia
        If Motivo.ListIndex = 5 Then

        End If

        '6   Mov interno de salida
        If Motivo.ListIndex = 6 Then

        End If
        
        '7   Mov interno de entrada
        If Motivo.ListIndex = 7 Then
            'No genera asiento
        End If

'''''''''BANDERA dentro de la matriz para señalar ultima pos cargada
        i = i + 1
        MatAsiento(i, 1) = CDec(0)

        'rs_AsientoCtas.Close

        'CDbl(Format(txtimporte, "##,###.00"))

        '''''''''''''''''''''''''''''''''''''''
        'Valida que el asiento este balanceado'
        '''''''''''''''''''''''''''''''''''''''

        i = 1
        j = 2

        Do While Not MatAsiento(i, 1) = 0

            If j = 2 Then

                SumDebe = SumDebe + MatAsiento(i, j)

             End If

            'Debug.Print i; j

            i = i + 1

        Loop

        SumDebe = Format(CDbl(SumDebe), "##,###.00")
        SumDebe = Truncar(SumDebe)

        i = 1
        j = 3

        Do While Not MatAsiento(i, 1) = 0

            If j = 3 Then

                SumHaber = SumHaber + MatAsiento(i, j)

            End If

            'Debug.Print i; j

            i = i + 1

        Loop

        SumHaber = Format(CDbl(SumHaber), "##,###.00")
        SumHaber = Truncar(SumHaber)

        If SumDebe <> SumHaber Then

            'ERROR ASIENTO NO BALANCEADO
            MsgBox "El Asiento no esta balanceado", vbInformation, "ATENCION"
            Error_conta = "Si"
            Exit Sub

        End If

        ''''''''''''''''''''''''''''''''''''
        'Valida que exista ejercicio activo'
        ''''''''''''''''''''''''''''''''''''

        If Principal.selec_ejer_per_cont = "Si" And IdEjer <> 0 Then
            rs_ejercicio.Open "Select * from cont_ejercicio where id_ejercicio= " & IdEjer & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        Else
            rs_ejercicio.Open "Select * from cont_ejercicio where activo_ejercicio= 'Si' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        End If

            If rs_ejercicio.RecordCount = 0 Then
                MsgBox "No hay ningun ejercicio activo, por favor active o genere uno para registrar el asieno contable", vbInformation, "ATENCION"
                rs_ejercicio.Close
                Error_conta = "Si"
                Exit Sub

            ElseIf rs_ejercicio.RecordCount = 1 Then

                idEjercicio = rs_ejercicio.Fields!id_ejercicio

            End If

        'rs_ejercicio.Close

        If Principal.selec_ejer_per_cont = "Si" And IdPer <> 0 Then
            rs_periodo.Open "Select * from cont_periodo where id_periodo = " & IdPer & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        Else
            rs_periodo.Open "Select * from cont_periodo where activo_periodo = 'Si' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
        End If

            If rs_periodo.RecordCount = 1 Then

                idPeriodo = rs_periodo.Fields!id_periodo

            End If

        rs_periodo.Close
        
        ''''''''''''''''''''''''''''''''''''''''
        'Verifico q el ejer/per no este cerrado'
        ''''''''''''''''''''''''''''''''''''''''
        If Principal.ContCerrado(idEjercicio, idPeriodo) = True Then
            MsgBox "No se puede generar el asiento debido a que el Ejer/Per se encuentra cerrado contablemente", vbInformation, "ATENCION"
            Error_conta = "Si"
            Exit Sub
        End If

        ''''''''''''''''''''''''''
        'Add New Asiento Contable'
        ''''''''''''''''''''''''''

        rs_newasiento.Open "SELECT * from cont_asiento  WHERE id_asiento = 1", conn, adOpenDynamic, adLockOptimistic

        'Numeracion de asiento contable
        If Principal.selec_ejer_per_cont = "Si" And IdEjer <> 0 Then
            rs_nroasiento.Open "select * from cont_ejercicio where id_ejercicio = " & IdEjer & " ", conn, adOpenDynamic, adLockOptimistic
        Else
            rs_nroasiento.Open "select * from cont_ejercicio where activo_ejercicio = 'Si' ", conn, adOpenDynamic, adLockOptimistic
        End If

        If rs_nroasiento.RecordCount = 1 Then

            NroAsientoCont = CDbl(rs_nroasiento.Fields!Nro_asiento_ejercicio)

            ' Actualizo Numeracion
            contadorasiento = CDbl(rs_nroasiento.Fields!Nro_asiento_ejercicio)
            contadorasiento = contadorasiento + 1

            rs_nroasiento.Fields!Nro_asiento_ejercicio = contadorasiento

            rs_nroasiento.Update

        End If

        rs_nroasiento.Close

        i = 1
        j = 1

        Do While Not MatAsiento(i, 1) = 0

            rs_newasiento.AddNew

                ' Guardo el Codigo de movimiento
                rs_newasiento.Fields!codigo_movimiento = CStr(contador)

                'Guardo el nro de asiento
                rs_newasiento.Fields!nro_asiento = CStr(NroAsientoCont)

                '''''''''''''''''''''''''''
                'Traigo saldo de la cuenta'
                '''''''''''''''''''''''''''
                rs_InfoCta.Open "SELECT * from cont_pc where id_pc = " & MatAsiento(i, 1) & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                If rs_InfoCta.RecordCount = 0 Then

                    'MsgBox "No se pudo traer la informacion de la Cta Cont, Error Interno", vbInformation
                    Error_conta = "Si"
                    Exit Sub

                End If

                ''''''''''''''''''''''
                ' Ejercicio / Periodo'
                ''''''''''''''''''''''
                If Not IsNull(idPeriodo) And idPeriodo <> 0 Then

                    rs_newasiento.Fields!id_periodo = idPeriodo
                    rs_newasiento.Fields!id_ejercicio = idEjercicio

                    '''''''''''''''''
                    'Saldo Ejercicio'
                    '''''''''''''''''

                    rs_SaldoCtaCont.Open "SELECT * FROM cont_ejercicio_saldo_cta where id_pc = " & MatAsiento(i, 1) & " AND " & _
                    "id_ejercicio = " & idEjercicio & " ", conn, adOpenDynamic, adLockOptimistic

                    If rs_SaldoCtaCont.RecordCount = 1 Then

                        'Guardo saldo en saldo_ejercicio_cta

                        'Pregunto saldo Deudor/Acreedor de la cta

                        If Not IsNull(rs_InfoCta.Fields!saldo_pc) Then

                            If rs_InfoCta.Fields!saldo_pc = "Deudor" Then

                                rs_SaldoCtaCont.Fields!saldo_ejercicio_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_ejercicio_cta + CDbl(Format(MatAsiento(i, 2), "##,###.00")) - CDbl(Format(MatAsiento(i, 3), "##,###.00")))

                            ElseIf rs_InfoCta.Fields!saldo_pc = "Acreedor" Then

                                rs_SaldoCtaCont.Fields!saldo_ejercicio_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_ejercicio_cta - CDbl(Format(MatAsiento(i, 2), "##,###.00")) + CDbl(Format(MatAsiento(i, 3), "##,###.00")))

                            End If

                        Else

                            'MsgBox "Cuenta con saldo NULL, Error Interno", vbOKOnly, "ATENCION"
                            Error_conta = "Si"
                            Exit Sub

                        End If

                    rs_SaldoCtaCont.Update

                    End If

                    rs_SaldoCtaCont.Close

                    '''''''''''''''
                    'Saldo Periodo'
                    '''''''''''''''

                    rs_SaldoCtaCont.Open "SELECT * FROM cont_periodo_saldo_cta where id_pc = " & MatAsiento(i, 1) & " AND " & _
                    "id_ejercicio = " & idEjercicio & " AND " & _
                    "id_periodo = " & idPeriodo & " ", conn, adOpenDynamic, adLockOptimistic

                    If rs_SaldoCtaCont.RecordCount = 1 Then

                        'Guardo saldo en saldo_periodo_cta

                        'Pregunto saldo Deudor/Acreedor de la cta

                        If rs_InfoCta.Fields!saldo_pc = "Deudor" Then

                            rs_SaldoCtaCont.Fields!saldo_periodo_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_periodo_cta + CDbl(Format(MatAsiento(i, 2), "##,###.00")) - CDbl(MatAsiento(i, 3)))

                        ElseIf rs_InfoCta.Fields!saldo_pc = "Acreedor" Then

                            rs_SaldoCtaCont.Fields!saldo_periodo_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_periodo_cta - CDbl(Format(MatAsiento(i, 2), "##,###.00")) + CDbl(Format(MatAsiento(i, 3), "##,###.00")))

                        End If

                        'SALDO EN TABLA CONT_ASIENTO
                        rs_newasiento.Fields!saldo_asiento = rs_SaldoCtaCont.Fields!saldo_periodo_cta

                    End If

                    rs_SaldoCtaCont.Update
                    rs_SaldoCtaCont.Close

                Else

                '''''''''''
                'Ejercicio'
                '''''''''''

                    rs_newasiento.Fields!id_ejercicio = idEjercicio

                    '''''''
                    'Saldo'
                    '''''''

                    rs_SaldoCtaCont.Open "SELECT * FROM cont_ejercicio_saldo_cta where id_pc = " & MatAsiento(i, 1) & " AND " & _
                    "id_ejercicio= " & idEjercicio & " ", conn, adOpenDynamic, adLockOptimistic

                    If rs_SaldoCtaCont.RecordCount = 1 Then

                        'Guardo saldo en saldo_ejercicio_cta

                        'PREGUNTO SI EL SALDO DE LA CUENTA ES DEUDOR O ACREEDOR
                        'DEPENDIENDO SUMO POR EL DEBE O RESTO POR EL DEBE

                        If Not IsNull(rs_InfoCta.Fields!saldo_pc) Then

                            If rs_InfoCta.Fields!saldo_pc = "Deudor" Then

                                '''''''''''''''''''''''''''''''
                                'DEPENDE DEBE O HABER ARREGLAR'
                                '''''''''''''''''''''''''''''''

                                rs_SaldoCtaCont.Fields!saldo_ejercicio_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_ejercicio_cta + CDbl(Format(MatAsiento(i, 2), "##,###.00")) - CDbl(Format(MatAsiento(i, 3), "##,###.00")))

                            ElseIf rs_InfoCta.Fields!saldo_pc = "Acreedor" Then

                                rs_SaldoCtaCont.Fields!saldo_ejercicio_cta = CDbl(rs_SaldoCtaCont.Fields!saldo_ejercicio_cta - CDbl(Format(MatAsiento(i, 2), "##,###.00")) + CDbl(Format(MatAsiento(i, 3), "##,###.00")))

                            End If

                        Else

                            'MsgBox "Cuenta con saldo NULL, Error Interno", vbOKOnly, "ATENCION"
                            Error_conta = "Si"
                            Exit Sub

                        End If

                        'SALDO EN TABLA CONT_ASIENTO
                        rs_newasiento.Fields!saldo_asiento = rs_SaldoCtaCont.Fields!saldo_ejercicio_cta

                    End If

                    rs_SaldoCtaCont.Update
                    rs_SaldoCtaCont.Close

                End If

                rs_InfoCta.Close

                '''''''
                'FECHA'
                '''''''

                'Valido que la fecha del asiento se encuentre dentro del ejercicio
                If Fecha >= rs_ejercicio.Fields!fecdesde_ejercicio And Fecha <= rs_ejercicio.Fields!fechasta_ejercicio Then

                    ''''''''''''''''''''''
                    'PREGUNTO POR PERIODO'
                    ''''''''''''''''''''''

                    rs_periodo.Open "Select * from cont_periodo where activo_periodo = 'Si' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                    If rs_periodo.RecordCount = 1 Then

                        If Fecha < rs_periodo.Fields!fecdesde_periodo Or Fecha > rs_periodo.Fields!fechasta_periodo Then

                            MsgBox "La fecha se encuentra fuera del intervalo del periodo activo", vbCritical + vbOKOnly, "ATENCION"
                            rs_periodo.Close
                            Error_conta = "Si"
                            Exit Sub

                        End If

                    End If

                    rs_periodo.Close

                    rs_newasiento.Fields!Fecha_Asiento = Fecha

                Else

                    MsgBox "La fecha se encuentra fuera del intervalo del ejercicio activo", vbCritical + vbOKOnly, "ATENCION"
                    rs_ejercicio.Close
                    Error_conta = "Si"
                    Exit Sub

                End If

                ''''''''''''''''''''
                'Segun DEBE O HABER'
                ''''''''''''''''''''
                rs_newasiento.Fields!debe_asiento = CDbl(MatAsiento(i, 2))
                rs_newasiento.Fields!haber_asiento = Truncar(Format(MatAsiento(i, 3), "##,###.00"))

                rs_newasiento.Fields!id_pc = MatAsiento(i, 1)

                'Descripcion por renglon de asiento
                'rs_newasiento.Fields!desc_renglon_asiento = "Asiento contable por proceso"

                '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
                'CONFIGURAR MANUALMENTE'
                ''''''''''''''''''''''''

                    'Nombre del concepto
                    Dim rs_concepto As New ADODB.Recordset
                    
                    rs_concepto.Open "SELECt desc_concepto_asiento from cont_concepto_asiento where id_concepto_asiento = 31 ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
                    
                    If rs_concepto.RecordCount = 1 Then
                        'Concepto
                        rs_newasiento.Fields!desc_concepto_asiento = rs_concepto.Fields!desc_concepto_asiento
                    Else
                        rs_concepto.Close
                        'MsgBox "Descuento con id_pc NULL, Error Interno", vbInformation
                        Error_conta = "Si"
                        Exit Sub
                    End If
                    
                    rs_concepto.Close
                    
                    rs_newasiento.Fields!id_concepto_asiento = 31

                '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

                rs_newasiento.Fields!balanceado_asiento = "Si"
                rs_newasiento.Fields!id_usuario = Principal.idUsuario

                'Nota al pie del asiento
                rs_newasiento.Fields!desc_asiento = "" & Motivo.Text & " "

            rs_newasiento.Update

            i = i + 1
            j = j + 1

        Loop

        rs_ejercicio.Close

'        nroasiento = rs_newasiento.Fields!nro_asiento

        rs_newasiento.Close

        ' Mensaje al usr de generacion de asiento
        'MsgBox "Se generó el asiento Nro - " & nroasiento & " ", vbInformation, "ATENCION"

End Sub

Private Function Truncar(Trunc As Double) As Double

    'Trunca a dos decimales

    Dim i As Integer, Cadena As String
    Cadena = Trunc
    i = InStr(Cadena, ",")
    If i Then Trunc = Left(Cadena, i + 2)

    Truncar = Trunc

End Function

Private Sub visualiza_asiento_cont(contador As Double)
' Capturamos el ERROR
On Error GoTo captura

        If Principal.activ_contabilidad = "Si" Then
        'Si la contabilidad esta activa entonces
        
                Dim rs_CtaCC As New ADODB.Recordset
                
                conn.Open
                conn.CursorLocation = adUseClient
    
                rs_CtaCC.Open "SELECT cont_asiento.id_pc, cont_asiento.codigo_movimiento, cont_pc.asig_cc " & _
                           "From cont_asiento " & _
                           "INNER JOIN cont_pc ON (cont_pc.id_pc = cont_asiento.id_pc) " & _
                           "WHERE cont_asiento.codigo_movimiento = " & contador & " AND cont_pc.asig_cc = 'Si' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
                
                If Not rs_CtaCC.RecordCount > 0 Then
                    rs_CtaCC.Close
                    conn.Close
                    Exit Sub
                End If
                                
                'No previsualiza asiento al terminar de generar el comprobante
                'pero si tiene CC si lo hace
                If Not IsNull(contador) And Principal.cont_prev_asiento = "No" Then
    
                    If rs_CtaCC.RecordCount > 0 Then
                        Cont_CargaAsientoM.Accion = "ProcAsientoAsigCCFact"
                        Cont_CargaAsientoM.CodigoMovimiento = CStr(contador)
                        Cont_CargaAsientoM.Caption = "Asignacion de centros de costos"
                        Cont_CargaAsientoM.Show
                    End If
    
                End If
    
                'Previsualizacion de asientos contables activada
                If Principal.cont_prev_asiento = "Si" Then
    
                    'Visualiza asiento -> Completo la temporal con el asiento
        
                    If rs_CtaCC.RecordCount > 0 Then
                        Cont_CargaAsientoM.Accion = "ProcAsientoAsigCCFact"
                        Cont_CargaAsientoM.CodigoMovimiento = CStr(contador)
                        Cont_CargaAsientoM.Caption = "Asignacion de centros de costos"
                        Cont_CargaAsientoM.Aceptar.ToolTipText = "Asignar centro de costos"
                        
                        Cont_CargaAsientoM.Aceptar.Visible = True
'                        Cont_CargaAsientoM.Aceptar.Move 4380, 7320
                        Cont_CargaAsientoM.Cancelar.Visible = True
                        
                        Cont_CargaAsientoM.Show
                                                
                    ElseIf Not IsNull(contador) Then
                        Cont_CargaAsientoM.Accion = "ProcAsientoAsigCCFact"
                        Cont_CargaAsientoM.CodigoMovimiento = CStr(contador)
                        Cont_CargaAsientoM.Caption = "Visualizacion de asiento contable"
                        
                        Cont_CargaAsientoM.Aceptar.Visible = False
                        Cont_CargaAsientoM.Cancelar.Move 4380, 7320
                        Cont_CargaAsientoM.Cancelar.Visible = True
                        
                        Cont_CargaAsientoM.cmbCtroCost.Enabled = False
                        
                        Cont_CargaAsientoM.Show
                        Cont_CargaAsientoM.Cancelar.SetFocus
                    End If
    
                End If
            
        
            rs_CtaCC.Close

            conn.Close

        End If
        
        
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
                
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Public Sub busqueda_articulo()
On Error GoTo captura
        
    Dim rs_articulo As New ADODB.Recordset

    conn.Open
    
    If Seleccion_Manual_Articulo = "Si" Then
        
        With rs_articulo
            .ActiveConnection = conn
            .CursorType = adOpenDynamic
            .Source = "SELECT articulo.promocion_vigencia_hasta,articulo.promocion_vigencia_desde,articulo.impuesto_interno,articulo.promocion_cant,articulo.promocion_tipo,articulo.promocion_listaoficial,articulo.promocion_lista1,articulo.promocion_lista2,articulo.promocion_lista3,articulo.promocion_lista4,articulo.promocion_lista5,articulo.promocion,articulo.promocion_por,articulo.CodLaboratorio,articulo.id_manual,Articulo.tipo_art,Articulo.Alicuota,Articulo.AlicuotaIB,Articulo.PrecioCosto,Articulo.lote,Articulo.IDArt,Articulo.Precio1V,Articulo.Precio2V,Articulo.Precio3V,Articulo.Precio4V,Articulo.Precio5V,Articulo.PNOficial, Articulo.IDSubRubro, Articulo.CodigoSubRubro,Articulo.CodigoRubro,Articulo.Moneda,Articulo.CodigoArticuloT,Articulo.NombreArticulo,Articulo.PFOficial,Articulo.Precio1VI,Articulo.Precio2VI,Articulo.Precio3VI,Articulo.Precio4VI,Articulo.Precio5VI, Articulo.tipoIVA,IVA.Alicuota as Alic " & _
            "FROM articulo, iva WHERE " & _
            "articulo.Alicuota = iva.id And " & _
            "articulo.IDArt = " & ID_Art & ""
            .Open
            
            '''''''''''''''''''''''''''''''''''''''''''''''''
            'Validacion que no permite facurar art con lotes'
            '''''''''''''''''''''''''''''''''''''''''''''''''
            '"articulo.Lote = 'No' And " & _

        End With
            
    Else
            
        With rs_articulo
            .ActiveConnection = conn
            .CursorType = adOpenDynamic
            .Source = "SELECT articulo.promocion_vigencia_hasta,articulo.promocion_vigencia_desde,articulo.limVtaxArt, articulo.impuesto_interno,articulo.promocion_cant,articulo.promocion_tipo,articulo.promocion_listaoficial,articulo.promocion_lista1,articulo.promocion_lista2,articulo.promocion_lista3,articulo.promocion_lista4,articulo.promocion_lista5,articulo.promocion,articulo.promocion_por,articulo.CodLaboratorio,articulo.id_manual,Articulo.tipo_art,Articulo.Alicuota,Articulo.AlicuotaIB,Articulo.PrecioCosto,Articulo.lote,Articulo.IDArt,Articulo.Precio1V,Articulo.Precio2V,Articulo.Precio3V,Articulo.Precio4V,Articulo.Precio5V,Articulo.PNOficial, Articulo.IDSubRubro, Articulo.CodigoSubRubro,Articulo.CodigoRubro,Articulo.Moneda,Articulo.CodigoArticuloT,Articulo.NombreArticulo,Articulo.PFOficial,Articulo.Precio1VI,Articulo.Precio2VI,Articulo.Precio3VI,Articulo.Precio4VI,Articulo.Precio5VI, Articulo.tipoIVA,IVA.Alicuota as Alic " & _
            "FROM articulo, iva WHERE " & _
            "articulo.Alicuota = iva.id And " & _
            "articulo.Discontinuo = 'No' And " & _
            "(articulo.id_manual = '" & Articulo.Text & "' OR articulo.CodigoArticuloT = '" & Articulo.Text & "' OR articulo.IDArt = '" & Articulo.Text & "' OR articulo.NombreArticulo = '" & Articulo.Text & "' Or articulo.CodArtProv = '" & Articulo.Text & "' Or articulo.NroCodBarra = '" & Articulo.Text & "' Or articulo.NroCodBarraF = '" & Articulo.Text & "' )"
            .Open
            
            '''''''''''''''''''''''''''''''''''''''''''''''''
            'Validacion que no permite facurar art con lotes'
            '''''''''''''''''''''''''''''''''''''''''''''''''
            '"articulo.Lote = 'No' And " & _

        End With
    
    End If
    
    If rs_articulo.RecordCount > 0 Then
        
        ID_Art = rs_articulo.Fields!IDArt
        Articulo.Text = rs_articulo.Fields!NombreArticulo
                
        ''''''
        'Lote'
        '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         If rs_articulo.Fields!Lote = "Si" Then

            'Deposito origen nunca puede estar vacio
            DataLote.ConnectionString = IngresoUsuario.Conex
            DataLote.CommandType = adCmdUnknown
            DataLote.RecordSource = "SELECT * FROM lote " & _
            "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
            "WHERE lote.id_articulo = " & ID_Art & " AND " & _
            "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
            "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
            DataLote.Refresh

            If DataLote.Recordset.RecordCount > 0 Then
                
                DataLote.Recordset.MoveFirst
                'Coloco en la combo cargamovstock.lote_articulo el lote y completo stock_lote.text
                lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
                stock_lote = DataLote.Recordset.Fields!stock_lote
'                data_renglon_tpv.Recordset.Fields!vto_lote = rs_lot.Fields!fecha_vto_lote
'                data_renglon_tpv.Recordset.Fields!Lote = "Si"
                
            End If
            
            'Pregunto por motivo para saber que frame habilitar
            If Motivo.ListIndex = 0 Or Motivo.ListIndex = 7 Or Motivo.ListIndex = 3 Then
                'Entradas   <- Obligo la carga del lote
                Lote.Visible = True
                nro_lote.SetFocus
                
                conn.Close
                Exit Sub
            ElseIf Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 5 Or Motivo.ListIndex = 6 Then
                'Salidas
                frame_lote.Visible = True
                lote_articulo.SetFocus
                
                If DataLote.Recordset.RecordCount = 0 Then
                    MsgBox "No existen lotes disponibles para el articulo ", vbCritical, "ATENCION"
                    conn.Close
                    Exit Sub
                End If
            Else
                ''''''''
                'Ajuste'
                ''''''''
                frame_lote.Visible = False
                Lote.Visible = False
                Cantidad.SetFocus
            End If
                
         Else
'            Cantidad.SetFocus
            'ARTICULO SIN LOTE
            Lote.Visible = False
            frame_lote.Visible = False
         End If
         '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
         
        AgregarRenglon_Click
        
    End If
        
        
    ' Pongo vacio en la text de busqueda
    Articulo.Text = ""
        
    ' Cantidad a valor 1 por defecto
    Cantidad = 1
        
    Seleccion_Manual_Articulo = "No"
        
    If conn.State = 1 Then
        conn.Close
    End If
        
Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
        
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Public Sub busqueda_articulo_ensamble()
On Error GoTo captura

   Dim rs_articulo As New ADODB.Recordset

    conn.Open
    
    
'"SELECT en_abm.*, articulo.IDArt, articulo.id_manual " & _
'                                "FROM En_abm " & _
'                                "LEFT JOIN articulo ON (articulo.id_en_abm = en_abm.id_en_abm) " & _
'                                "WHERE  articulo.Discontinuo = 'No' AND en_abm.anulado='No' AND en_abm.Nombre_en_abm LIKE '" & Busqueda.Text & "%' OR articulo.IDArt LIKE '" & Busqueda.Text & "%' OR articulo.id_manual " & _
'                                "LIKE '" & Busqueda.Text & "%' ORDER BY Nombre_en_abm LIMIT " & Principal.LimiteTotal
    
    If Seleccion_Manual_Articulo = "Si" Then
        
         rs_articulo.Open "SELECT en_abm.*, articulo.IDArt, " & _
                          "articulo.promocion_vigencia_hasta,articulo.promocion_vigencia_desde,articulo.impuesto_interno,articulo.promocion_cant,articulo.promocion_tipo,articulo.promocion_listaoficial,articulo.promocion_lista1,articulo.promocion_lista2,articulo.promocion_lista3,articulo.promocion_lista4,articulo.promocion_lista5,articulo.promocion,articulo.promocion_por,articulo.CodLaboratorio,articulo.id_manual,Articulo.tipo_art,Articulo.Alicuota,Articulo.AlicuotaIB,Articulo.PrecioCosto,Articulo.lote,Articulo.IDArt,Articulo.Precio1V,Articulo.Precio2V,Articulo.Precio3V,Articulo.Precio4V,Articulo.Precio5V,Articulo.PNOficial, Articulo.IDSubRubro, Articulo.CodigoSubRubro,Articulo.CodigoRubro,Articulo.Moneda,Articulo.CodigoArticuloT,Articulo.NombreArticulo,Articulo.PFOficial,Articulo.Precio1VI,Articulo.Precio2VI,Articulo.Precio3VI,Articulo.Precio4VI,Articulo.Precio5VI, Articulo.tipoIVA,IVA.Alicuota as Alic " & _
                          "FROM articulo " & _
                          "LEFT JOIN iva ON (articulo.Alicuota = iva.id) " & _
                          "RIGHT JOIN en_abm ON (en_abm.id_en_abm = articulo.id_en_abm) " & _
                          "WHERE " & _
                          "articulo.IDArt = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    Else
            
         rs_articulo.Open "SELECT en_abm.*, articulo.IDArt, " & _
                          "articulo.promocion_vigencia_hasta,articulo.promocion_vigencia_desde,articulo.limVtaxArt, articulo.impuesto_interno,articulo.promocion_cant,articulo.promocion_tipo,articulo.promocion_listaoficial,articulo.promocion_lista1,articulo.promocion_lista2,articulo.promocion_lista3,articulo.promocion_lista4,articulo.promocion_lista5,articulo.promocion,articulo.promocion_por,articulo.CodLaboratorio,articulo.id_manual,Articulo.tipo_art,Articulo.Alicuota,Articulo.AlicuotaIB,Articulo.PrecioCosto,Articulo.lote,Articulo.IDArt,Articulo.Precio1V,Articulo.Precio2V,Articulo.Precio3V,Articulo.Precio4V,Articulo.Precio5V,Articulo.PNOficial, Articulo.IDSubRubro, Articulo.CodigoSubRubro,Articulo.CodigoRubro,Articulo.Moneda,Articulo.CodigoArticuloT,Articulo.NombreArticulo,Articulo.PFOficial,Articulo.Precio1VI,Articulo.Precio2VI,Articulo.Precio3VI,Articulo.Precio4VI,Articulo.Precio5VI, Articulo.tipoIVA,IVA.Alicuota as Alic " & _
                          "FROM articulo " & _
                          "LEFT JOIN iva ON (articulo.Alicuota = iva.id) " & _
                          "RIGHT JOIN en_abm ON (en_abm.id_en_abm = articulo.id_en_abm) " & _
                          "WHERE " & _
                          "articulo.Discontinuo = 'No' And " & _
                          "(articulo.id_manual = '" & Articulo.Text & "' OR articulo.CodigoArticuloT = '" & Articulo.Text & "' OR articulo.IDArt = '" & Articulo.Text & "' OR articulo.NombreArticulo = '" & Articulo.Text & "' Or articulo.CodArtProv = '" & Articulo.Text & "' Or articulo.NroCodBarra = '" & Articulo.Text & "' Or articulo.NroCodBarraF = '" & Articulo.Text & "' )", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
    End If
    
    If rs_articulo.RecordCount <= 0 Then
    
        rs_articulo.Close
        conn.Close
        Articulo.Text = ""
        Cantidad = 1
        Seleccion_Manual_Articulo = "No"
        Exit Sub
    
    End If
    
            
    ID_Art = rs_articulo.Fields!IDArt
    Articulo.Text = rs_articulo.Fields!NombreArticulo
            
    ''''''
    'Lote'
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
     If rs_articulo.Fields!Lote = "Si" Then

        'Deposito origen nunca puede estar vacio
        DataLote.ConnectionString = IngresoUsuario.Conex
        DataLote.CommandType = adCmdUnknown
        DataLote.RecordSource = "SELECT * FROM lote " & _
                                "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                                "WHERE lote.id_articulo = " & ID_Art & " AND " & _
                                "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                                "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
        DataLote.Refresh

        If DataLote.Recordset.RecordCount > 0 Then
            
            DataLote.Recordset.MoveFirst
            
            'Coloco en la combo cargamovstock.lote_articulo el lote y completo stock_lote.text
            lote_articulo.BoundText = DataLote.Recordset.Fields!id_lote
            stock_lote = DataLote.Recordset.Fields!stock_lote
            
        End If
        
        'Armado
        If Motivo.ListIndex = 8 Then
            If nro_lote.Text = "" Then
                'Entradas   <- Obligo la carga del lote
                Lote.Visible = True
                nro_lote.SetFocus
                conn.Close
                Exit Sub
            End If
        End If
            
        'Desarmado
        If Motivo.ListIndex = 9 Then
        
            'Magia
            EsperaMiliseg 5
            
            'Salidas
            frame_lote.Visible = True
            lote_articulo.SetFocus
            
            If DataLote.Recordset.RecordCount = 0 Then
                MsgBox "No existen lotes disponibles para el articulo ", vbCritical, "ATENCION"
                Exit Sub
            End If
        End If
       
     Else
        'ARTICULO SIN LOTE
        Lote.Visible = False
        frame_lote.Visible = False
     End If
     '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
     
    AgregarRenglon_Click

        
    ' Pongo vacio en la text de busqueda
    Articulo.Text = ""
        
    ' Cantidad a valor 1 por defecto
    Cantidad = 1
        
    Seleccion_Manual_Articulo = "No"
        
    If conn.State = 1 Then
        conn.Close
    End If

Exit Sub
captura:
        Call Principal.Guardar_Error(Err.Description, Me.Caption, Err.Number)
        
        If conn.State = 1 Then
            conn.Close
        End If
End Sub

Sub EsperaMiliseg(ByVal Tiempo As Double)
  Dim HoraActual As Double
    HoraActual = Timer
    Do Until Timer >= HoraActual + (Tiempo / 1000)
      DoEvents
    Loop
End Sub

Private Function ValCantSerie() As Boolean

    Dim rs_valC As New ADODB.Recordset
    
    'No Existen articulos seriados cargados en el comprobante
    If ESerie = False Then
        conn.Close
        Exit Function
    End If
    
    'Entrada
    If ES.ListIndex = 0 Then
        rs_valC.Open "SELECT * FROM " & _
                    "cuerpostock_mstock " & _
                    "INNER JOIN articulo ON (articulo.IDArt = cuerpostock_mstock.IDArt AND articulo.serie = 'Si') " & _
                    "WHERE " & _
                    "cuerpostock_mstock.CodUsuario = " & Principal.idUsuario & " AND " & _
                    "cuerpostock_mstock.visualiza = 'No' AND " & _
                    "cuerpostock_mstock.Cantidad <> (SELECT count(*) FROM serie_entrada_temp WHERE " & _
                                              "serie_entrada_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                                              "serie_entrada_temp.visualiza = 'No' AND " & _
                                              "serie_entrada_temp.tipo_comprobante = 'Mstock' AND " & _
                                              "serie_entrada_temp.id_articulo = cuerpostock_mstock.IDArt AND " & _
                                              "serie_entrada_temp.orden = cuerpostock_mstock.orden) ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    End If
    
    'Salida
    If ES.ListIndex = 1 Then
        rs_valC.Open "SELECT * FROM " & _
                    "cuerpostock_mstock " & _
                    "INNER JOIN articulo ON (articulo.IDArt = cuerpostock_mstock.IDArt AND articulo.serie = 'Si') " & _
                    "WHERE " & _
                    "cuerpostock_mstock.CodUsuario = " & Principal.idUsuario & " AND " & _
                    "cuerpostock_mstock.visualiza = 'No' AND " & _
                    "cuerpostock_mstock.Cantidad <> (SELECT count(*) FROM serie_salida_temp WHERE " & _
                                              "serie_salida_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                                              "serie_salida_temp.visualiza = 'No' AND " & _
                                              "serie_salida_temp.tipo_comprobante = 'Mstock' AND " & _
                                              "serie_salida_temp.id_articulo = cuerpostock_mstock.IDArt AND " & _
                                              "serie_salida_temp.orden = cuerpostock_mstock.orden) ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

    End If

    If rs_valC.RecordCount > 0 Then
        ValCantSerie = True
    Else
        ValCantSerie = False
    End If

    rs_valC.Close
    
End Function

Private Sub ABMSerie_Click()

    If GridArticulos.EOF Then
        Exit Sub
    End If

    '''''''
    'Serie'
    '''''''
    conn.ConnectionString = IngresoUsuario.Conex
    conn.CursorLocation = adUseClient
    conn.Open

        If EsSerie(CuerpoStock.Recordset.Fields!IDArt) = False Then
            conn.Close
            Exit Sub
        End If
        
        'Entrada
        If ES.ListIndex = 0 Then
            Serie_abm.IDArt = CuerpoStock.Recordset.Fields!IDArt
            Serie_abm.frmTipo = "Mstock"
            Serie_abm.Cant = CuerpoStock.Recordset.Fields!Cantidad
            Serie_abm.orden = CuerpoStock.Recordset.Fields!orden
            
            'Deposito
'            Serie_abm.id_deposito = CuerpoStock.Recordset.Fields!CodDeposito
            Serie_abm.id_deposito = DepositoOrigen.BoundText

            Serie_abm.Show vbModal
        End If
        
        'Salida
        If ES.ListIndex = 1 Then
            'Mod
            Serie_salida.banderaAlta = 1
            Serie_salida.IDArt = CuerpoStock.Recordset.Fields!IDArt
            Serie_salida.frmTipo = "Mstock"
            Serie_salida.Cant = CuerpoStock.Recordset.Fields!Cantidad
            Serie_salida.orden = CuerpoStock.Recordset.Fields!orden
            
            'Deposito
            Serie_salida.id_deposito = CuerpoStock.Recordset.Fields!id_deposito
            
            Serie_salida.Show vbModal
        End If
        
    conn.Close

End Sub

Private Function ESerie() As Boolean

    Dim rs_existe As New ADODB.Recordset
    
'    rs_existe.Open "SELECT serie " & _
                   "From cuerpostock_mstock " & _
                   "WHERE codUsuario = " & Principal.idUsuario & " AND " & _
                   "visualiza = 'No' AND " & _
                   "serie = 'Si' ", conn, adOpenDynamic, adLockOptimistic
                   
    rs_existe.Open "SELECT * FROM " & _
                   "cuerpostock_mstock " & _
                   "INNER JOIN articulo ON (articulo.IDArt = cuerpostock_mstock.IDArt AND articulo.serie = 'Si') " & _
                   "WHERE " & _
                   "cuerpostock_mstock.CodUsuario = " & Principal.idUsuario & " AND " & _
                   "cuerpostock_mstock.visualiza = 'No' ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
    If rs_existe.RecordCount > 0 Then
        ESerie = True
    Else
        ESerie = False
    End If
    
End Function

Private Function EsSerie(IDArt As Double) As Boolean
    
    Dim rs_s As New ADODB.Recordset
    
'    conn.ConnectionString = IngresoUsuario.Conex
'    conn.CursorLocation = adUseClient
'    conn.Open
    
    rs_s.Open "SELECT articulo.serie, articulo.IDArt " & _
              "FROM articulo " & _
              "WHERE " & _
              "IDArt = " & IDArt & "  ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
    If rs_s.RecordCount = 1 Then
            
        If rs_s.Fields!serie = "Si" Then
            EsSerie = True
        Else
            EsSerie = False
        End If
            
    End If
    
    rs_s.Close
    
'    conn.Close
    
End Function

Private Sub GuardarSerie()

    If ESerie = False Then
        Exit Sub
    End If
    
    '''''''''
    'Entrada'
    ''''''''
    If ES.ListIndex = 0 Then

        conn.Execute "INSERT INTO serie_entrada " & _
                        "(serie_entrada.anulado, serie_entrada.codigo_mov_entrada, serie_entrada.desc_serie, " & _
                         "serie_entrada.disponible, serie_entrada.fecha, serie_entrada.id_articulo, " & _
                         "serie_entrada.nro_serie, serie_entrada.tipo_comprobante, serie_entrada.vto_serie, " & _
                         "serie_entrada.id_deposito) " & _
                    "SELECT 'No', " & contador & ", serie_entrada_temp.desc_serie, " & _
                           "'Si', '" & Fecha.Year & "-" & Fecha.Month & "-" & Fecha.Day & "', " & _
                           "serie_entrada_temp.id_articulo, " & _
                           "serie_entrada_temp.nro_serie , serie_entrada_temp.tipo_comprobante, serie_entrada_temp.vto_serie, " & _
                           "serie_entrada_temp.id_deposito " & _
                    "From serie_entrada_temp " & _
                    "WHERE serie_entrada_temp.visualiza = 'No' AND " & _
                          "serie_entrada_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                          "serie_entrada_temp.tipo_comprobante = 'Mstock' " & _
                          "ORDER BY id_serie_entrada_temp "
                          
        'serie_movimiento
        conn.Execute "INSERT INTO serie_movimiento " & _
                    "(serie_movimiento.anulado, serie_movimiento.codigo_mov_mstock, serie_movimiento.desc_serie, " & _
                     "serie_movimiento.fecha, " & _
                     "serie_movimiento.nro_serie, serie_movimiento.tipo_comprobante, serie_movimiento.vto_serie, " & _
                     "id_serie_entrada, id_articulo, tipo_comp_desc, " & _
                     " comprobante,  modificado, id_stock, id_deposito, nro_comprobante) " & _
                    "SELECT 'No', " & contador & ", serie_entrada.desc_serie, " & _
                           "'" & Fecha.Year & "-" & Fecha.Month & "-" & Fecha.Day & "', " & _
                           "serie_entrada.nro_serie , serie_entrada.tipo_comprobante, serie_entrada.vto_serie, " & _
                           "serie_entrada.id_serie_entrada, serie_entrada.id_articulo, 'MSTOCK Entrada', " & _
                           "'MSTOCK', 'No', id_stock, id_deposito, '" & Nro & "' " & _
                    "From serie_entrada " & _
                    "INNER JOIN stock ON (stock.codigoMovimiento = serie_entrada.codigo_mov_entrada AND " & _
                                         "stock.idart = serie_entrada.id_articulo AND " & _
                                         "stock.anulado = 'No') " & _
                    "WHERE serie_entrada.codigo_mov_entrada = " & contador & " AND " & _
                          "serie_entrada.tipo_comprobante = 'Mstock'"
                        
                    'id_proveedor, nro_comprobante  <- No tiene
    End If
    
    ''''''''
    'SALIDA'
    ''''''''
    If ES.ListIndex = 1 Then
        

        conn.Execute "INSERT INTO serie_movimiento " & _
                        "(serie_movimiento.anulado, serie_movimiento.codigo_mov_mstock, serie_movimiento.desc_serie, " & _
                         "serie_movimiento.fecha, serie_movimiento.id_articulo, " & _
                         "serie_movimiento.nro_serie, serie_movimiento.tipo_comprobante, serie_movimiento.vto_serie, " & _
                         "serie_movimiento.id_serie_entrada, tipo_comp_desc, " & _
                         "comprobante, modificado, serie_movimiento.id_stock, id_deposito, nro_comprobante) " & _
                    "SELECT 'No', " & contador & ", serie_salida_temp.desc_serie, " & _
                           "'" & Fecha.Year & "-" & Fecha.Month & "-" & Fecha.Day & "', " & _
                           "serie_salida_temp.id_articulo, " & _
                           "serie_salida_temp.nro_serie , serie_salida_temp.tipo_comprobante, serie_salida_temp.vto_serie, " & _
                           "serie_salida_temp.id_serie_entrada , 'MSTOCK Salida', " & _
                           "'MSTOCK', 'No',stock.id_stock, id_deposito, '" & Nro & "' " & _
                    "From serie_salida_temp " & _
                    "INNER JOIN stock ON (stock.codigoMovimiento = " & contador & " AND " & _
                                             "stock.idart = serie_salida_temp.id_articulo AND " & _
                                             "stock.anulado = 'No' AND Salida > 0) " & _
                    "WHERE serie_salida_temp.visualiza = 'No' AND " & _
                          "serie_salida_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                          "serie_salida_temp.tipo_comprobante = 'Mstock' " & _
                          "ORDER BY id_serie_salida_temp"
        
        'Faltante                  Rotura                  Ajuste
'        If Motivo.ListIndex = 2 Or Motivo.ListIndex = 4 Or Motivo.ListIndex = 1 Then
        
            'Los que salen dejan de estar disponibles en la entrada
            conn.Execute "UPDATE serie_entrada " & _
                         "SET disponible = 'No' " & _
                         "WHERE id_serie_entrada IN " & _
                            "(SELECT id_serie_entrada " & _
                            "From serie_salida_temp " & _
                            "WHERE serie_salida_temp.visualiza = 'No' AND " & _
                            "serie_salida_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                            "serie_salida_temp.tipo_comprobante = 'Mstock' )"
            
'        End If
        
        'Transferencia
        If Motivo.ListIndex = 5 Then
        
            'Entrada del movimiento
            conn.Execute "INSERT INTO serie_movimiento " & _
                            "(serie_movimiento.anulado, serie_movimiento.codigo_mov_mstock, serie_movimiento.desc_serie, " & _
                             "serie_movimiento.fecha, serie_movimiento.id_articulo, " & _
                             "serie_movimiento.nro_serie, serie_movimiento.tipo_comprobante, serie_movimiento.vto_serie, " & _
                             "serie_movimiento.id_serie_entrada, tipo_comp_desc, " & _
                             "comprobante, modificado, serie_movimiento.id_stock, id_deposito, nro_comprobante) " & _
                        "SELECT 'No', " & contador & ", serie_salida_temp.desc_serie, " & _
                               "'" & Fecha.Year & "-" & Fecha.Month & "-" & Fecha.Day & "', " & _
                               "serie_salida_temp.id_articulo, " & _
                               "serie_salida_temp.nro_serie , serie_salida_temp.tipo_comprobante, serie_salida_temp.vto_serie, " & _
                               "serie_salida_temp.id_serie_entrada , 'MSTOCK Entrada', " & _
                               "'MSTOCK', 'No', stock.id_stock, " & DepositoDestino.BoundText & ", '" & Nro & "' " & _
                        "From serie_salida_temp " & _
                        "INNER JOIN stock ON (stock.codigoMovimiento = " & contador & " AND " & _
                                                 "stock.idart = serie_salida_temp.id_articulo AND " & _
                                                 "stock.anulado = 'No' AND Entrada > 0) " & _
                        "WHERE serie_salida_temp.visualiza = 'No' AND " & _
                              "serie_salida_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                              "serie_salida_temp.tipo_comprobante = 'Mstock' " & _
                              "ORDER BY id_serie_salida_temp"
        
            conn.Execute "INSERT INTO serie_entrada " & _
                            "(serie_entrada.anulado, serie_entrada.codigo_mov_entrada, serie_entrada.desc_serie, " & _
                            "serie_entrada.disponible, serie_entrada.fecha, serie_entrada.id_articulo, " & _
                            "serie_entrada.id_deposito, " & _
                            "serie_entrada.id_serie_salida, serie_entrada.nro_serie, serie_entrada.tipo_comprobante, " & _
                            "serie_entrada.vto_serie) " & _
                        "SELECT " & _
                            "serie_entrada.anulado, serie_entrada.codigo_mov_entrada, serie_entrada.desc_serie, " & _
                            "'Si', serie_entrada.fecha, serie_entrada.id_articulo, " & _
                            "" & DepositoDestino.BoundText & ", " & _
                            "serie_entrada.id_serie_salida, serie_entrada.nro_serie, serie_entrada.tipo_comprobante, " & _
                            "serie_entrada.vto_serie " & _
                        "From serie_entrada " & _
                        "WHERE id_serie_entrada IN " & _
                            "(SELECT id_serie_entrada " & _
                            "From serie_salida_temp " & _
                            "WHERE serie_salida_temp.visualiza = 'No' AND " & _
                            "serie_salida_temp.id_usuario = " & Principal.idUsuario & " AND " & _
                            "serie_salida_temp.tipo_comprobante = 'Mstock' ) "

        
        End If
        
        
    End If

End Sub

Private Sub ensamble_desarme()

    Dim rs_idEn As New ADODB.Recordset
    Dim rs_enAbm As New ADODB.Recordset
    Dim CantArt As Double
    Dim rs_enVta As New ADODB.Recordset
    
    'Es articulo ensamblado?
    rs_idEn.Open "SELECT ensamblado, IDArt FROM articulo " & _
                 "WHERE " & _
                 "id_en_abm = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
    If rs_idEn.RecordCount > 0 Then
        
        If rs_idEn.Fields!ensamblado = "No" Then
            rs_idEn.Close
            Exit Sub
        Else
            'Actualizo id_en_abm por IDArt
            ID_Art = rs_idEn.Fields!IDArt
        End If
    
    End If
    
    rs_idEn.Close
    
    'Tiene la formula definida
    rs_idEn.Open "SELECT id_en_abm FROM articulo " & _
                 "WHERE IDArt = " & ID_Art & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

    If rs_idEn.RecordCount = 1 And rs_idEn.Fields!id_en_abm > 0 Then
        
        
        'Ensambla en la vta?
        rs_enVta.Open "SELECT descuenta_en FROM en_abm " & _
                      "WHERE " & _
                      "id_en_abm = " & rs_idEn.Fields!id_en_abm & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_enVta.Fields!descuenta_en <> "Mstock" Then
            
            MsgBox "Error: El artículo no esta definido para ser utilizado por este proceso", vbInformation, "ATENCION"
            rs_enVta.Close
            Exit Sub
            
        End If
        
        rs_enVta.Close
        
        rs_enAbm.Open "SELECT id_articulo, cantidad_articulo " & _
                      "FROM en_abm_formula " & _
                      "WHERE id_en_abm = " & rs_idEn.Fields!id_en_abm & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_enAbm.RecordCount = 0 Then
        
            'Error articulo sin formula definida
            MsgBox "Error: El artículo no tiene definido una formula de ensamblaje", vbInformation, "ATENCION"

            Exit Sub
            
        End If
        
        rs_enAbm.Close
    
        Dim id_stock_en_abm As Double   'Se obtiene en procedimiento MstockE
        
        ''''''''''
        'Ensamble'
        ''''''''''
        If Motivo.ListIndex = 8 Then
        
            'ENTRADA
            MstockE CDbl(ID_Art), Cantidad.Text, id_stock_en_abm, DepositoDestino.BoundText
            
            'SALIDA
            rs_enAbm.Open "SELECT id_articulo, cantidad_articulo " & _
                          "FROM en_abm_formula " & _
                          "WHERE id_en_abm = " & rs_idEn.Fields!id_en_abm & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
            If rs_enAbm.RecordCount > 0 Then
    
                rs_enAbm.MoveFirst
    
                'Por insumo
                Do While Not rs_enAbm.EOF
                                               
                    CantArt = Cantidad.Text * rs_enAbm.Fields!cantidad_articulo
                    CantForm = CDbl(rs_enAbm.Fields!cantidad_articulo)
                    
                    MstockS rs_enAbm.Fields!id_articulo, CantArt, CantForm, id_stock_en_abm, DepositoOrigen.BoundText
    
                    rs_enAbm.MoveNext
                Loop
                
            End If
            
        End If
''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

        '''''''''
        'Desarme'
        '''''''''
        If Motivo.ListIndex = 9 Then
        
        
            '31/12/2016 Se invierte a primero la salida y despues la entrada para
            'Respetar el lote seleccionado por el usuario para el desarme
            
            'SALIDA
            rs_enAbm.Open "SELECT IDArt " & _
                          "FROM articulo " & _
                          "WHERE id_en_abm = " & rs_idEn.Fields!id_en_abm & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

            
            If rs_enAbm.RecordCount > 0 Then
    
                rs_enAbm.MoveFirst

                CantForm = 1
                
                MstockS CDbl(rs_enAbm.Fields!IDArt), Cantidad.Text, CantForm, id_stock_en_abm, DepositoOrigen.BoundText

            End If

            rs_enAbm.Close
        
            'ENTRADA - Insumos
            rs_enAbm.Open "SELECT id_articulo, cantidad_articulo " & _
                          "FROM en_abm_formula " & _
                          "WHERE id_en_abm = " & rs_idEn.Fields!id_en_abm & "", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
            If rs_enAbm.RecordCount > 0 Then
    
                rs_enAbm.MoveFirst
    
                'Por insumo
                Do While Not rs_enAbm.EOF
                                               
                    CantArt = Cantidad.Text * rs_enAbm.Fields!cantidad_articulo
'                    CantForm = CDbl(rs_enAbm.Fields!cantidad_articulo)
                    
                    MstockE rs_enAbm.Fields!id_articulo, CantArt, id_stock_en_abm, DepositoDestino.BoundText
    
                    rs_enAbm.MoveNext
                    
                Loop
                
            End If
            
            rs_enAbm.Close
        
        End If 'Fin motivo Desarme

    End If 'Fin formula definida

    rs_idEn.Close
    
End Sub

Private Sub MstockE(IDArt As Double, Cant As Double, id_en_abm_formula As Double, id_depositoE As Double)

'    Dim rs_stock As New ADODB.Recordset
'    Dim rs_saldo_stock As New ADODB.Recordset
'    Dim rs_stock_deposito As New ADODB.Recordset
'    Dim rs_lote As New ADODB.Recordset

    Dim rs_consul As New ADODB.Recordset
    
    Dim rs_multiV As New ADODB.Recordset
    Dim rs_multiC As New ADODB.Recordset
    
    Dim rs_Art As New ADODB.Recordset
    
    rs_Art.Open "SELECT * FROM  articulo " & _
                "WHERE IDArt = " & IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
                
    If rs_Art.RecordCount = 0 Then
        rs_Art.Close
        MsgBox "Error, Insumo no declarado como artículo ", vbInformation, "ATENCION"
        Exit Sub
    End If
            
    CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock " & _
                               "WHERE CodigoMovimiento =1"
    CuerpoStock.Refresh


    ' Agrego registros en Tabla CuerpoStock Temporal (Renglon) para despues guardar el registro en la tabla Stock
    CuerpoStock.Recordset.AddNew
    CuerpoStock.Recordset.Fields!IDArt = IDArt
    CuerpoStock.Recordset.Fields!CodigoArticulo = rs_Art.Fields!CodigoArticulo
    CuerpoStock.Recordset.Fields!Descripcion = rs_Art.Fields!NombreArticulo

    If Principal.utiliza_embalaje = "Si" Then

        'Muestro en la grid la cantidad com pres_c
        CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cant)

        rs_multiV.Open "SELECT multiplicador_vta, CodigoProveedor FROM articulo WHERE IDArt = " & IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_multiV.RecordCount = 1 Then

            If Not IsNull(rs_multiV.Fields!codigoproveedor) Then

                valor_cantidad = Principal.CambiarComaPunto(CStr(Cant))

                rs_multiC.Open "SELECT multiplicador_comp, (" & valor_cantidad & " * multiplicador_comp) as Cantidad_pres_comp, cantidad_uni " & _
                                "FROM articulo_prov WHERE IDArt = " & IDArt & " AND CodProveedor = " & rs_multiV.Fields!codigoproveedor & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                If rs_multiC.RecordCount = 1 Then
                    CuerpoStock.Recordset.Fields!Cantidad = CDbl(rs_multiC.Fields!cantidad_pres_comp)  '*multiV / MultiC
                    GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                    'GridArticulos.Columns(4).Caption = "Cantidad Pres. C"
                    
                    'Entrada
                    CuerpoStock.Recordset.Fields!Entrada = CDbl(rs_multiC.Fields!cantidad_pres_comp)

                    CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * rs_multiC.Fields!cantidad_pres_comp)

                    'Multiplicador Vta
                    CuerpoStock.Recordset.Fields!multiplicador_vta = rs_multiV.Fields!multiplicador_vta

                    'Multiplicador Compra
                    CuerpoStock.Recordset.Fields!multiplicador_comp = rs_multiC.Fields!multiplicador_comp
                    CuerpoStock.Recordset.Fields!cantidad_uni = rs_multiC.Fields!cantidad_uni
                Else
                    'Cod anterior a presentacion
                    CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cant)
                    GridArticulos.Columns(4).DataField = "Cantidad"
                    'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
                    
                    'Entrada
                    CuerpoStock.Recordset.Fields!Entrada = CDbl(Cant)
                    
                    CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * Cant)
                End If

            End If

        End If

    Else
        'Cod anterior a presentacion
        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cant)

        GridArticulos.Columns(4).DataField = "Cantidad"
        'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
        
        'Entrada
        CuerpoStock.Recordset.Fields!Entrada = CDbl(Cant)
        
        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * Cant)

        'Cantidad y cantidad_pres_comp quedan iguales
        CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cant)
    End If

    CuerpoStock.Recordset.Fields!PrecioCostoxU = CDbl(Format(rs_Art.Fields!PrecioCosto, Principal.Decimales))

    '14/03/2017
    CuerpoStock.Recordset.Fields!PrecioVentaxR = rs_Art.Fields!Precio1VI     'PrecioVentaxR
    CuerpoStock.Recordset.Fields!PrecioBrutoxR = rs_Art.Fields!Precio1V      'PrecioBrutoxR


    'Cod anterior a presentacion
'        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)

    CuerpoStock.Recordset.Fields!tipo_art = tipo_art

    If id_manual <> "" Then
        CuerpoStock.Recordset.Fields!id_manual = rs_Art.Fields!id_manual
    End If

'        'Cod anteriro a presentacion
'        If ES.Text = "Entrada" Then
'            CuerpoStock.Recordset.Fields!entrada = CDbl(Cantidad)
'        End If
'
'        If ES.Text = "Salida" Then
'            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
'        End If

    CuerpoStock.Recordset.Fields!ES = "Entrada"

    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    'LOTE'
    ''''''

        'Consulto si el articulo tiene lote
        rs_consul.Open "select * from articulo where idart = " & IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
        If rs_consul.Fields!Lote = "Si" Then
    
            'Entrada
    
            CuerpoStock.Recordset.Fields!cod_lote = nro_lote.Text
            CuerpoStock.Recordset.Fields!vto_lote = fecha_vto.Value
            CuerpoStock.Recordset.Fields!Lote = "Si"
    
            'incializo los controles
            fecha_vto.Text = Principal.Fecha
            nro_lote.Text = ""
            frame_lote.Visible = False
            Lote.Visible = False
            
            'Salida
            If Motivo.ListIndex = 9 Then
            
                'Por IDArt buscar el lote mas viejo y asignarlo por defecto
                
                Dim rslot As New ADODB.Recordset
                
                rslot.Open "SELECT * FROM lote " & _
                                        "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                                        "WHERE lote.id_articulo = " & IDArt & " AND " & _
                                        "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                                        "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly
    
                If rslot.RecordCount > 0 Then
                    
                    rslot.MoveFirst
                    
                    CuerpoStock.Recordset.Fields!id_lote = rslot.Fields!id_lote
                    CuerpoStock.Recordset.Fields!cod_lote = rslot.Fields!cod_lote
                    CuerpoStock.Recordset.Fields!vto_lote = rslot.Fields!fecha_vto_lote
                    CuerpoStock.Recordset.Fields!Lote = "Si"
                
                End If
                
                rslot.Close

            End If  'Fin motivo 9
            
        End If 'Fin lote

    ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

        CuerpoStock.Recordset.Fields!TipoIVA = rs_Art.Fields!TipoIVA
        CuerpoStock.Recordset.Fields!Alicuota = rs_Art.Fields!Alicuota
        CuerpoStock.Recordset.Fields!Alicuota = rs_Art.Fields!AlicuotaIB
        CuerpoStock.Recordset.Fields!Codusuario = Principal.idUsuario
        CuerpoStock.Recordset.Fields!Detalle = Detalle.Text
        CuerpoStock.Recordset.Fields!CodViajante = rs_Art.Fields!codigoproveedor
        CuerpoStock.Recordset.Fields!CodLaboratorio = 1
        CuerpoStock.Recordset.Fields!CodDeposito = id_depositoE     'DepositoOrigen.BoundText
        CuerpoStock.Recordset.Fields!Visualiza = "No"

        '''''''
        'Serie'
        '''''''
        If EsSerie(CuerpoStock.Recordset.Fields!IDArt) = True Then
            CuerpoStock.Recordset.Fields!serie = "Si"
        End If
        
        'Marco insumos en movimiento de desarme
'        If Motivo.ListIndex = 9 Then
'            CuerpoStock.Recordset.Fields!id_en_abm_formula = id_en_abm_formula
'        End If
        
        
        CuerpoStock.Recordset.Update

    '''''''
    'Serie'
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    'Alta
    If Not IsNull(CuerpoStock.Recordset.Fields!orden) Then

        Serie_carga.orden = CuerpoStock.Recordset.Fields!orden

    End If
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    '''''''
    'Serie'
    '''''''
    AgregarRenglonSerie
    
    rs_Art.Close

    CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
    "cuerpostock_mstock.Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' ORDER BY Orden "
    CuerpoStock.Refresh
    GridArticulos.Refresh
    
End Sub

Private Sub MstockS(IDArt As Double, Cant As Double, ByVal CantForm As Double, id_en_abm_formula As Double, id_depositoS As Double)

'    Dim rs_stock As New ADODB.Recordset
'    Dim rs_saldo_stock As New ADODB.Recordset
'    Dim rs_stock_deposito As New ADODB.Recordset
'    Dim rs_lote As New ADODB.Recordset
'
'    Dim rs_deposito As New ADODB.Recordset
'    Dim id_deposito As Double

    Dim rs_consul As New ADODB.Recordset
    
    Dim rs_multiV As New ADODB.Recordset
    Dim rs_multiC As New ADODB.Recordset
    
    Dim rs_Art As New ADODB.Recordset
    
    rs_Art.Open "SELECT * FROM articulo WHERE IDArt = " & IDArt, conn, adOpenDynamic, adLockReadOnly
    'cambio por readonly
                
    If rs_Art.RecordCount = 0 Then
        MsgBox "Error, Articulo de formula no encontrado", vbInformation, "ATENCION"
        rs_Art.Close
        Exit Sub
    End If
    
'    rs_art.Close
    
    CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock " & _
                               "WHERE CodigoMovimiento =1"
    CuerpoStock.Refresh

    ' Agrego registros en Tabla CuerpoStock Temporal (Renglon) para despues guardar el registro en la tabla Stock
    CuerpoStock.Recordset.AddNew
    CuerpoStock.Recordset.Fields!IDArt = IDArt
    CuerpoStock.Recordset.Fields!CodigoArticulo = rs_Art.Fields!CodigoArticulo
    CuerpoStock.Recordset.Fields!Descripcion = rs_Art.Fields!NombreArticulo

    If Principal.utiliza_embalaje = "Si" Then

        'Muestro en la grid la cantidad com pres_c
        CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cant)

        rs_multiV.Open "SELECT multiplicador_vta, CodigoProveedor FROM articulo WHERE IDArt = " & IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_multiV.RecordCount = 1 Then

            If Not IsNull(rs_multiV.Fields!codigoproveedor) Then

                valor_cantidad = Principal.CambiarComaPunto(CDbl(Cant))

                rs_multiC.Open "SELECT multiplicador_comp, (" & valor_cantidad & " * multiplicador_comp) as Cantidad_pres_comp, cantidad_uni " & _
                                "FROM articulo_prov WHERE IDArt = " & IDArt & " AND CodProveedor = " & rs_multiV.Fields!codigoproveedor & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

                If rs_multiC.RecordCount = 1 Then
                    CuerpoStock.Recordset.Fields!Cantidad = CDbl(rs_multiC.Fields!cantidad_pres_comp)  '*multiV / MultiC
                    GridArticulos.Columns(4).DataField = "Cantidad_pres_comp"
                    'GridArticulos.Columns(4).Caption = "Cantidad Pres. C"

                    'Salida
                    CuerpoStock.Recordset.Fields!Salida = CDbl(rs_multiC.Fields!cantidad_pres_comp)
                   
                    CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * rs_multiC.Fields!cantidad_pres_comp)

                    'Multiplicador Vta
                    CuerpoStock.Recordset.Fields!multiplicador_vta = rs_multiV.Fields!multiplicador_vta

                    'Multiplicador Compra
                    CuerpoStock.Recordset.Fields!multiplicador_comp = rs_multiC.Fields!multiplicador_comp
                    CuerpoStock.Recordset.Fields!cantidad_uni = rs_multiC.Fields!cantidad_uni
                Else
                    'Cod anterior a presentacion
                    CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cant)
                    GridArticulos.Columns(4).DataField = "Cantidad"
                    'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"

                    'Salida
                    CuerpoStock.Recordset.Fields!Salida = CDbl(Cant)
                    
                    CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * Cant)
                End If

            End If

        End If

    Else
        'Cod anterior a presentacion
        CuerpoStock.Recordset.Fields!Cantidad = CDbl(Cant)

        GridArticulos.Columns(4).DataField = "Cantidad"
        'GridArticulos.Columns(4).Caption = "Cantidad Pres. V"
        
        'Salida
        CuerpoStock.Recordset.Fields!Salida = CDbl(Cant)
        
        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(rs_Art.Fields!PrecioCosto * Cant)

        'Cantidad y cantidad_pres_comp quedan iguales
        CuerpoStock.Recordset.Fields!cantidad_pres_comp = CDbl(Cant)
    End If

    CuerpoStock.Recordset.Fields!PrecioCostoxU = CDbl(Format(rs_Art.Fields!PrecioCosto, Principal.Decimales))

    '14/03/2017
'    lstPrecio = "precio" & CuerpoStock.Recordset.Fields!Lista_Precio & "vi"
'    PIVA = Format(rs_Art.Fields(lstPrecio).Value, Principal.Decimales)
'    CuerpoStock.Recordset.Fields!PrecioVentaxR = PIVA * Cant
'
'    lstPrecio = "precio" & Lista_Precio & "v"
'    PIVA = Format(rs_Art.Fields(lstPrecio).Value, Principal.Decimales)
'    CuerpoStock.Recordset.Fields!PrecioBrutoxR = PIVA * Cant

    'Cod anterior a presentacion
'        CuerpoStock.Recordset.Fields!PrecioCostoxR = CDbl(PrecioCostoxU * Cantidad)

    CuerpoStock.Recordset.Fields!tipo_art = rs_Art.Fields!tipo_art

    If rs_Art.Fields!id_manual <> "" Then
        CuerpoStock.Recordset.Fields!id_manual = rs_Art.Fields!id_manual
    End If

'        'Cod anteriro a presentacion
'        If ES.Text = "Entrada" Then
'            CuerpoStock.Recordset.Fields!entrada = CDbl(Cantidad)
'        End If
'
'        If ES.Text = "Salida" Then
'            CuerpoStock.Recordset.Fields!Salida = CDbl(Cantidad)
'        End If

    CuerpoStock.Recordset.Fields!ES = "Salida"

    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    'LOTE'
    ''''''

        'Consulto si el articulo tiene lote
        rs_consul.Open "select * from articulo where idart = " & IDArt & " ", conn, adOpenDynamic, adLockReadOnly 'cambio por readonly

        If rs_consul.Fields!Lote = "Si" Then
    
            'Ensamle/Desarme - En la salida siempre se debe seleccionar un lote
            
            '31/12/2016 - Se descomenta Motivo = 8
            If Motivo.ListIndex = 8 Then

                'Deposito origen nunca puede estar vacio
                DataLote.ConnectionString = IngresoUsuario.Conex
                DataLote.CommandType = adCmdUnknown
                DataLote.RecordSource = "SELECT * FROM lote " & _
                                        "INNER JOIN lote_stock on (lote.id_lote = lote_stock.id_lote) " & _
                                        "WHERE lote.id_articulo = " & IDArt & " AND " & _
                                        "lote.anulado ='No' AND lote_stock.stock_lote <> 0 AND " & _
                                        "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " order by lote.fecha_vto_lote ASC"
                
                DataLote.Refresh
    
                If DataLote.Recordset.RecordCount > 0 Then
                    
                    DataLote.Recordset.MoveFirst
                    'Coloco en la combo cargamovstock.lote_articulo el lote y completo stock_lote.text
                    lote_articulo.BoundText = CargaMovStock.DataLote.Recordset.Fields!id_lote
                    stock_lote = CargaMovStock.DataLote.Recordset.Fields!stock_lote
                    
                    CuerpoStock.Recordset.Fields!id_lote = lote_articulo.BoundText
                    CuerpoStock.Recordset.Fields!cod_lote = lote_articulo.Columns(0).Text
                    CuerpoStock.Recordset.Fields!vto_lote = lote_articulo.Columns(1).Text
                    CuerpoStock.Recordset.Fields!Lote = "Si"
                    
                End If
            
            End If
            
            '31/12/2016
            'Respeto lote seleccionado para Desarme
            If Motivo.ListIndex = 9 Then
                
                'El usuario busco un producto desde el boton
                'Luego selecciono un lote
                If frame_lote.Visible = True And lote_articulo.Text <> "" Then
                    
                    CuerpoStock.Recordset.Fields!id_lote = lote_articulo.BoundText
                    CuerpoStock.Recordset.Fields!cod_lote = lote_articulo.Columns(0).Text
                    CuerpoStock.Recordset.Fields!vto_lote = lote_articulo.Columns(1).Text
                    CuerpoStock.Recordset.Fields!Lote = "Si"

                End If
            
            End If
    
        End If
        
        rs_consul.Close

    ''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

        CuerpoStock.Recordset.Fields!TipoIVA = rs_Art.Fields!TipoIVA
        CuerpoStock.Recordset.Fields!Alicuota = rs_Art.Fields!Alicuota
        CuerpoStock.Recordset.Fields!Alicuota = rs_Art.Fields!AlicuotaIB
        CuerpoStock.Recordset.Fields!Codusuario = Principal.idUsuario
        CuerpoStock.Recordset.Fields!Detalle = rs_Art.Fields!Detalle
        CuerpoStock.Recordset.Fields!CodViajante = rs_Art.Fields!codigoproveedor
        CuerpoStock.Recordset.Fields!CodLaboratorio = 1
        CuerpoStock.Recordset.Fields!CodDeposito = id_depositoS     'DepositoOrigen.BoundText
        CuerpoStock.Recordset.Fields!Visualiza = "No"

        
        rs_Art.Close


        '''''''
        'Serie'
        '''''''
        If EsSerie(CuerpoStock.Recordset.Fields!IDArt) = True Then
            CuerpoStock.Recordset.Fields!serie = "Si"
        End If

        CuerpoStock.Recordset.Update

    '''''''
    'Serie'
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    'Alta
    If Not IsNull(CuerpoStock.Recordset.Fields!orden) Then

        Serie_carga.orden = CuerpoStock.Recordset.Fields!orden

    End If
    '''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''

    '''''''
    'Serie'
    '''''''
    AgregarRenglonSerie

    CuerpoStock.RecordSource = "SELECT * FROM cuerpostock_mstock WHERE " & _
    "cuerpostock_mstock.Codusuario = " & Principal.idUsuario & " AND visualiza = 'No' ORDER BY Orden "
    CuerpoStock.Refresh
    GridArticulos.Refresh

End Sub

Private Sub Lote_ed(ByRef id_lote_ed As Double, ByRef stock_lote_deposito_ed As Double)

    Dim rs_lote As New ADODB.Recordset

    '[loteS]''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
    
    '# dependiendo del tipo de movimiento si es stock inicial, tengo que dar de alta el lote caso contrario descuento.
    If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
                
        ' Consulto si el cod de lote ya existe, en ese caso actualizo los valores del lote correspondiente
        rs_lote.Open "SELECT * FROM lote WHERE cod_lote = '" & CuerpoStock.Recordset.Fields!cod_lote & "' AND id_articulo = " & CuerpoStock.Recordset.Fields!IDArt & "", conn, adOpenDynamic, adLockOptimistic
        
        ' Si el lote es existente
        If rs_lote.RecordCount = 1 Then
    
            rs_lote.Fields!stock_total_lote = rs_lote.Fields!stock_total_lote + CuerpoStock.Recordset.Fields!Cantidad
            rs_lote.Update
    
            Dim rs_lotestock As New ADODB.Recordset
    
            rs_lotestock.Open "Select * From lote_stock where id_lote = " & rs_lote.Fields!id_lote & " And " & _
            "id_deposito = " & DepositoOrigen.BoundText & " ", conn, adOpenDynamic, adLockOptimistic
    
            If rs_lotestock.RecordCount = 1 Then
                rs_lotestock.Fields!stock_lote = rs_lotestock.Fields!stock_lote + CuerpoStock.Recordset.Fields!Cantidad
                rs_lotestock.Update
                
            ElseIf rs_lotestock.RecordCount = 0 Then
                
                'Nuevo registro en lotestock
                rs_lotestock.AddNew
                
                    rs_lotestock.Fields!id_lote = rs_lote.Fields!id_lote
                    rs_lotestock.Fields!stock_lote = CuerpoStock.Recordset.Fields!Cantidad
                    rs_lotestock.Fields!id_deposito = DepositoOrigen.BoundText
                
                rs_lotestock.Update
                
            End If
    
            '# finalmente guardo el id del lote en el stock.
            id_lote_ed = rs_lote.Fields!id_lote
            stock_lote_deposito_ed = CuerpoStock.Recordset.Fields!Cantidad
    
            rs_lotestock.Close
            rs_lote.Close
        
        ' Sino existe el lote agrego uno nuevo
        Else
                
             '# creo lote nuevo.
             rs_lote.Close
             rs_lote.Open "SELECT * FROM lote where id_lote = 0", conn, adOpenDynamic, adLockOptimistic
             rs_lote.AddNew
             rs_lote.Fields!cod_lote = CuerpoStock.Recordset.Fields!cod_lote
             rs_lote.Fields!fecha_vto_lote = CuerpoStock.Recordset.Fields!vto_lote
             rs_lote.Fields!id_articulo = CuerpoStock.Recordset.Fields!IDArt
             rs_lote.Fields!tipo_lote = "No Seriada"
             rs_lote.Fields!stock_total_lote = CuerpoStock.Recordset.Fields!Cantidad
             rs_lote.Fields!anulado = "No"
             rs_lote.Fields!cod_movimiento_entrada = CodMov
             rs_lote.Fields!id_proveedor = CuerpoStock.Recordset.Fields!CodViajante
            ' Control error
            control_error = "cracion lote stock"
             rs_lote.Update
             rs_lote.Close
            
            '# recupero el id del lote insert
             rs_lote.Open "SELECT last_insert_id() as id_lote", conn, adOpenDynamic, adLockOptimistic 'cambio por readonly
             idlote = rs_lote.Fields!id_lote
             rs_lote.Close
                                                     
             '# finalmente guardo el id del lote en el stock.
             id_lote_ed = idlote
             stock_lote_deposito_ed = CuerpoStock.Recordset.Fields!Cantidad
             
             'agrego lote deposito en lote_stock
            rs_lote.Open "SELECT * from lote_stock where id_lote_stock=0"
            rs_lote.AddNew
            rs_lote.Fields!id_lote = idlote
            rs_lote.Fields!stock_lote = CuerpoStock.Recordset.Fields!Cantidad
            rs_lote.Fields!id_deposito = DepositoOrigen.BoundText
            ' Control error
            control_error = "deposito lote stock"
            rs_lote.Update
            rs_lote.Close
            
        End If
    
    ElseIf CuerpoStock.Recordset.Fields!ES = "Salida" Then
                                
            'Si es una transferencia debe hacer el mismo procedimiento que una salida
            rs_lote.Open "SELECT * From Lote " & _
                         "INNER JOIN lote_stock ON (lote.id_lote = lote_stock.id_lote) " & _
                         "Where lote.id_lote = " & CuerpoStock.Recordset.Fields!id_lote & " AND " & _
                         "lote_stock.id_deposito = " & DepositoOrigen.BoundText & " AND " & _
                         "lote.anulado = 'No'", conn, adOpenDynamic, adLockOptimistic
               
            If rs_lote.Fields!stock_lote >= CuerpoStock.Recordset.Fields!Cantidad Then
               
                   'Actuliza stock por deposito
                   rs_lote.Fields!stock_lote = rs_lote.Fields!stock_lote - CuerpoStock.Recordset.Fields!Cantidad
                   
                   'Actualiza stock total siempre que no sea una transferencia, en ese caso queda igual
                   If Motivo.ListIndex <> 5 Then 'TRANSFERENCIA
                        
                        rs_lote.Fields!stock_total_lote = rs_lote.Fields!stock_total_lote - CuerpoStock.Recordset.Fields!Cantidad
                   
                   End If
                   
                   rs_lote.Update
                   
                   '# finalmente guardo el id del lote en el stock.
                   id_lote_ed = CuerpoStock.Recordset.Fields!id_lote
                   stock_lote_deposito_ed = rs_lote.Fields!stock_lote
                   
                   rs_lote.Close
            Else
                   
                    sepaso = CuerpoStock.Recordset.Fields!Cantidad - rs_lote.Fields!stock_lote
                   
                    'La cantidad solicitada del articulo xxx se sobrepasa en xxx unidades respercto al stock del deposito"
                    
                    MsgBox "La cantidad solicitada del articulo: " & Chr(34) & " " & CuerpoStock.Recordset.Fields!Descripcion & " " & Chr(34) & " del lote: " & CuerpoStock.Recordset.Fields!cod_lote & "  se sobrepasa en " & sepaso & " unidad/es respecto al stock del deposito ", vbInformation, "ATENCION"
    
                    If conn.State = 1 Then
                       conn.RollbackTrans
                       conn.Close
                    End If
                    
                    ' Descargo formulario de espera
                    Unload form_espera
'                    Termina_GIF
                    
    '                            Nro.Caption = ""
                
                    Exit Sub
                    
            End If
    
    Else
        'Tiene lote y motivo es ajuste
        MsgBox "No se puede realizar ajuste de articulos con lote", vbInformation, "ATENCION"
        If conn.State = 1 Then
            conn.RollbackTrans
            conn.Close
        End If
        Unload form_espera
'        Termina_GIF
        Exit Sub
    End If
    
                                          
    '[loteS]''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''''
                        
End Sub

Private Function IdDeposito()
        
        'Ensamble
        If Motivo.ListIndex = 8 Then
        
'            If DepositoOrigen.BoundText <> DepositoDestino.BoundText Then
        
                If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
                    id_depo = DepositoDestino.BoundText
                Else
                    'Insumos
                    id_depo = DepositoOrigen.BoundText
                End If
                
'            Else
'
'                ' Version 3.4.45
'                ' Validacion sino dejaba ID Deposito en 0 cuando seleccionaba el mismo deposito
'
'                If CuerpoStock.Recordset.Fields!ES = "Entrada" Then
'                    id_depo = DepositoDestino.BoundText
'                Else
'                    'Insumos
'                    id_depo = DepositoOrigen.BoundText
'                End If
'
'            End If
        
        End If
        
        'Desarme
        If Motivo.ListIndex = 9 Then
        
'            If DepositoOrigen.BoundText <> DepositoDestino.BoundText Then
        
                If CuerpoStock.Recordset.Fields!ES = "Salida" Then
                    id_depo = DepositoOrigen.BoundText
                Else
                    'Insumos
                    id_depo = DepositoDestino.BoundText
                End If
                
'            End If
        
        End If
        
        IdDeposito = id_depo
        
End Function

Sub Empieza_GIF(texto_label As String)
    frame_progreso.Visible = True
    Label_Progreso = texto_label
    Label_Progreso.Visible = True
    GIF.Visible = True
End Sub

Sub Termina_GIF()
    frame_progreso.Visible = False
    Label_Progreso.Visible = False
    GIF.Visible = False
End Sub

Private Sub Buscar_Articulo_Grilla(strSearchCodigo As String) ' strSearchCodigo se pasa como parametro de busqueda, como string.

    ' Lee el primer registro
    CuerpoStock.Recordset.MoveFirst
    CuerpoStock.Recordset.Find "orden = '" & strSearchCodigo & "'"
                           
End Sub
