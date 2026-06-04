<?php
require_once 'sesion.inc.php';
$uTablas = 1;
$uModal = 1;
$uSlider = 0;
$uGui = 1;
$iconoDisabled = 1;
$usaZoom = 0;

// Obtener depósitos permitidos para el usuario
$depositos = array();
$idDepositoUsuario = $_SESSION['deposito'];
$idUSuario =  $_SESSION['idusuario'];
$seleccionDeposito = $_SESSION["seleccion_deposito_inventario"];
//echo 'uso id manaual:'.$_SESSION['usa_id_manual'].PHP_EOL;
$where = "";
if ($seleccionDeposito == 'Seleccionado') {
    $where .= "AND depu.id_deposito = " . $idDepositoUsuario;
}

$sqlDepositos = "SELECT
                        depu.id_deposito AS id_deposito,
                        dep.NombreDeposito AS nombre,    
                        IF(dep.CodDeposito='" . $idDepositoUsuario . "','Si','No') AS defecto
                    FROM
                        deposito_usr AS depu
                    LEFT JOIN deposito AS dep ON dep.CodDeposito = depu.id_deposito
                    WHERE
                        depu.id_usuario = " . $idUSuario . "
                        " . $where . "
                        AND dep.anulado = 'No'    ";
$resDepositos = mysqli_query($connV, $sqlDepositos) or die(mysqli_error($connV));
while ($dep = mysqli_fetch_assoc($resDepositos)) {
    $depositos[] = $dep;
}
?>
<!DOCTYPE HTML>
<html lang="es">

<head>
    <title>Listado de Stock y Existencias</title>
    <?php require_once 'cabecera.php'; ?>
    <link rel="stylesheet" href="_css/productos.css?v=2">
    <!-- DataTables y Buttons ya incluidos en cabecera.php si $uTablas == 1 -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .etiqueta-deposito {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 0.95em;
            margin: 1px 2px;
            color: #fff;
            font-weight: bold;
        }

        .etiqueta-deposito-0 {
            background: #4c5b9b;
        }

        .etiqueta-deposito-1 {
            background: #22c55e;
        }

        .etiqueta-deposito-2 {
            background: #f97316;
        }

        .etiqueta-deposito-3 {
            background: #618edb;
        }

        .etiqueta-deposito-4 {
            background: #eab308;
        }

        .etiqueta-deposito-5 {
            background: #d946ef;
        }

        .etiqueta-deposito-6 {
            background: #0ea5e9;
        }

        .etiqueta-deposito-7 {
            background: #14b8a6;
        }

        .etiqueta-deposito-8 {
            background: #f43f5e;
        }

        .etiqueta-deposito-9 {
            background: #8b5cf6;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php
        require_once $barra;
        ?>
        <div class="paneles filtroInformes">

            <form id="formFiltroStock" class="form-inline" style="margin-bottom: 15px;">
                <div class="panelesBloqueInforme">
                    <div class="controlContainer">
                        <div class="control">
                            <label for="filtroDeposito" class="parametros">Depósito:</label>
                            <select id="filtroDeposito" name="filtroDeposito">
                                <option value="todos">Todos</option>
                                <?php foreach ($depositos as $i => $dep): ?>
                                    <option value="<?php echo  $dep['id_deposito'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="control" style="display: none !important;">
                            <label for="busquedaRapida" class="parametros">Producto:</label>
                            <input type="text" id="busquedaRapida" placeholder="Buscar producto, código o ID..." style="margin-left:10px;">
                        </div>
                        <div class="control">
                            <label for="ordenStock" class="parametros">Orden:</label>
                            <select id="ordenStock" name="ordenStock">
                                <option value="a.IDArt">Código</option>
                                <option value="a.NombreArticulo">Nombre</option>
                                <option value="a.id_manual">ID Manual</option>

                            </select>
                        </div>
                        <div class="control">
                            <label for="stockCero" class="parametros">Stock en Cero:</label>
                            <select id="stockCero" name="stockCero">
                                <option value="no">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                        <div class="control">
                            <button type="button" id="btnBuscar" class="botonNuevo">
                                <i class="fas fa-check fa-lg fa-fw"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
        <div class="paneles" id="contiene-tabla">
            <h1>Stock y Existencias</h1>
            <table id="tablaStock" class="display" style="width:100%"></table>

        </div>
    </div>
    <?php require_once 'footer.php'; ?>
    <!-- jQuery, DataTables, Buttons, JSZip y pdfmake ya incluidos en cabecera.php -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        let tablaStock = null;

        function escapeHtml(texto) {
            return String(texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function obtenerDepositosParaTabla(callback) {
            $.ajax({
                url: 'relay-stock-existencias.php',
                type: 'POST',
                dataType: 'json',
                data: { obtenerDepositos: 1 },
                success: function(resp) {
                    var depositos = resp.depositos || [];
                    var filtroDeposito = $('#filtroDeposito').val();

                    if (filtroDeposito !== 'todos') {
                        depositos = depositos.filter(function(dep) {
                            return String(dep.id_deposito) === String(filtroDeposito);
                        });
                    }

                    callback(depositos);
                },
                error: function() {
                    callback([]);
                }
            });
        }

        function construirEncabezadoTabla(depositos) {
            var html = '';
            html += '<thead>';
            html += '<tr>';
            html += '<th rowspan="2">Código Sistema</th>';
            html += '<th rowspan="2">ID Manual</th>';
            html += '<th rowspan="2">Producto</th>';

            if (!depositos.length) {
                html += '<th rowspan="2">Stock</th>';
                html += '<th rowspan="2">Disponible</th>';
            } else {
                depositos.forEach(function(dep) {
                    html += '<th colspan="2">' + escapeHtml(dep.nombre) + '</th>';
                });
            }
            html += '</tr>';

            html += '<tr>';
            if (!depositos.length) {
                html += '<th>Stock</th>';
                html += '<th>Disponible</th>';
            } else {
                depositos.forEach(function() {
                    html += '<th>Stock</th>';
                    html += '<th>Disponible</th>';
                });
            }
            html += '</tr>';
            html += '</thead>';
            $('#tablaStock').html(html);
        }

        function construirColumnasTabla(depositos) {
            var columnas = [
                { data: 'codigo', title: 'Código Sistema' },
                { data: 'id_manual', title: 'ID Manual' },
                { data: 'nombre', title: 'Producto' }
            ];

            function crearColumnaDeposito(dataKey, titulo, indiceDeposito) {
                var claseBadge = 'etiqueta-deposito etiqueta-deposito-' + (indiceDeposito % 10);
                var claseCelda = 'text-center etiqueta-deposito-celda etiqueta-deposito-celda-' + (indiceDeposito % 10);
                return {
                    data: dataKey,
                    title: titulo,
                    defaultContent: 0,
                    className: claseCelda,
                    render: function(data) {
                        if (data === null || typeof data === 'undefined' || data === '') {
                            data = 0;
                        }
                        return '<span class="' + claseBadge + '">' + data + '</span>';
                    }
                };
            }

            if (!depositos.length) {
                columnas.push(crearColumnaDeposito('stock', 'Stock', 0));
                columnas.push(crearColumnaDeposito('disponible', 'Disponible', 0));
                return columnas;
            }

            depositos.forEach(function(dep, indiceDeposito) {
                columnas.push(crearColumnaDeposito('stock_' + dep.id_deposito, dep.nombre + ' Stock', indiceDeposito));
                columnas.push(crearColumnaDeposito('disp_' + dep.id_deposito, dep.nombre + ' Disponible', indiceDeposito));
            });

            return columnas;
        }

        function inicializarTablaStock() {
            var fecha = new Date();
            var fechaFormateada = fecha.getFullYear() + '-' +
                ('0' + (fecha.getMonth() + 1)).slice(-2) + '-' +
                ('0' + fecha.getDate()).slice(-2) + '-' +
                ('0' + fecha.getHours()).slice(-2) +
                ('0' + fecha.getMinutes()).slice(-2) +
                ('0' + fecha.getSeconds()).slice(-2);

            obtenerDepositosParaTabla(function(depositos) {
                construirEncabezadoTabla(depositos);

                if ($.fn.dataTable.isDataTable('#tablaStock')) {
                    $('#tablaStock').DataTable().destroy();
                    $('#tablaStock').empty();
                    construirEncabezadoTabla(depositos);
                }

                tablaStock = $('#tablaStock').DataTable({
                    ajax: {
                        url: 'relay-stock-existencias.php',
                        type: 'POST',
                        data: function(d) {
                            d.buscarStockSinPaginacion = 1;
                            d.deposito = $('#filtroDeposito').val();
                            d.stockCero = $('#stockCero').val();
                            d.orden = $('#ordenStock').val();
                            var prodId = $('#busquedaRapida').data('producto-id');
                            if (prodId) {
                                d.producto_id = prodId;
                            } else {
                                d.busqueda = $('#busquedaRapida').val();
                            }
                        },
                        dataSrc: 'data'
                    },
                    columns: construirColumnasTabla(depositos),
                    dom: 'Blfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: 'Exportar a Excel',
                        title: 'listado-stock-disponible_' + fechaFormateada,
                        filename: 'listado-stock-disponible_' + fechaFormateada,
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'all',
                                search: 'applied'
                            },
                            format: {
                                body: function(data, row, column, node) {
                                    if (typeof data !== 'string') {
                                        return data;
                                    }
                                    var a = document.createElement('div');
                                    a.innerHTML = data;
                                    var textoLimpio = a.textContent || a.innerText || '';
                                    return textoLimpio.replace(/\s+/g, ' ').trim();
                                }
                            }
                        }
                    }],
                    language: {
                        emptyTable: 'Sin datos disponibles',
                        info: 'Viendo _START_ de _END_ de _TOTAL_ resultados',
                        infoEmpty: 'Viendo 0 de 0 de 0 resultados',
                        infoFiltered: '(filtrado de _MAX_ resultados)',
                        infoPostFix: '',
                        thousands: '',
                        lengthMenu: 'Ver _MENU_ entradas',
                        loadingRecords: 'Cargando...',
                        processing: 'Procesando...',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron resultados',
                        paginate: {
                            first: 'Primero',
                            last: 'Ultimo',
                            next: 'Siguiente',
                            previous: 'Anterior'
                        }
                    },
                    deferLoading: 0
                });
            });
        }

        $(document).ready(function() {
            // Autocompletar para productos/artículos
            $('#busquedaRapida').autocomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: 'relay-stock-existencias.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            term: request.term,
                            deposito: $('#filtroDeposito').val()
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    // Al seleccionar un producto, buscar solo ese
                    $('#busquedaRapida').val(ui.item.label);
                    $('#busquedaRapida').data('producto-id', ui.item.id);
                    if (tablaStock) tablaStock.ajax.reload();
                    return false;
                },
                focus: function(event, ui) {
                    $('#busquedaRapida').val(ui.item.label);
                    return false;
                }
            });


            // Iniciar DataTable pero sin server side es poco practico.

            // fin consulta sin server side

            // Inicializar DataTable sin cargar datos automáticamente server side
            // tablaStock = $('#tablaStock').DataTable({
            //     processing: true,
            //     serverSide: true,
            //     ajax: {
            //         url: 'relay-stock-existencias.php',
            //         type: 'POST',
            //         data: function(d) {
            //             d.deposito = $('#filtroDeposito').val();
            //             // Si hay un producto seleccionado por autocompletar, buscar solo ese
            //             d.buscarStock = 1;
            //             var prodId = $('#busquedaRapida').data('producto-id');
            //             if (prodId) {
            //                 d.producto_id = prodId;
            //             } else {
            //                 d.busqueda = $('#busquedaRapida').val();
            //             }
            //         },
            //         dataSrc: function(json) {
            //             // Si no se ha hecho clic en buscar, no mostrar datos
            //             if (!window._stockConsultaIniciada) return [];
            //             return json.data;
            //         }
            //     },
            //     columns: [{
            //             data: 'codigo'
            //         },
            //         {
            //             data: 'id_manual'
            //         },
            //         {
            //             data: 'nombre'
            //         },

            //         {
            //             data: 'stock',
            //             render: renderEtiquetas
            //         },
            //         {
            //             data: 'disponible',
            //             render: renderEtiquetas
            //         }
            //     ],
            //     dom: 'Blfrtip',
            //     buttons: [{
            //             extend: 'excelHtml5',
            //             text: 'Exportar a Excel',
            //             exportOptions: {
            //                 columns: ':visible',

            //                 // --- INICIO DE LA MODIFICACIÓN ---
            //                 // Esta sección es la clave para exportar TODAS las filas.
            //                 modifier: {
            //                     page: 'all',      // Le decimos a DataTables que use todas las páginas.
            //                     search: 'applied' // Opcional: respeta el filtro de búsqueda activo.
            //                                     // Si quieres exportar todo sin importar el filtro, usa search: 'none'.
            //                 },
            //                 // --- FIN DE LA MODIFICACIÓN ---

            //                 format: {
            //                     body: function(data, row, column, node) {
            //                         // (Este código ya está corregido y funciona bien)
            //                         if (typeof data !== 'string') {
            //                             return data;
            //                         }
            //                         var a = document.createElement('div');
            //                         a.innerHTML = data;
            //                         let textoLimpio = a.textContent || a.innerText || "";
            //                         return textoLimpio.replace(/\s+/g, ' ').trim();
            //                     }
            //                 }
            //             }
            //             // customizeData: function(data) {
            //             //     // (Este código ya está corregido y funciona bien)
            //             //     data.body.forEach(function(row) {
            //             //         [3, 4].forEach(function(idx) {
            //             //             if (row[idx] && typeof row[idx] === 'string') {
            //             //                 row[idx] = row[idx].replace(/[^0-9.,-]+/g, '').replace(',', '.');
            //             //             }
            //             //         });
            //             //     });
            //             //     return data;
            //             // }
            //         },
            //         {
            //             extend: 'pdfHtml5',
            //             text: 'Exportar a PDF',
            //             orientation: 'landscape',
            //             pageSize: 'A4',
            //             exportOptions: {
            //                 columns: ':visible',
            //                 format: {
            //                     body: function(data, row, column, node) {
            //                         return $(data).text().replace(/\s+/g, ' ').trim();
            //                     }
            //                 }
            //             }
            //         }
            //     ],
            //     language: {
            //         url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            //         search: 'Buscar:',
            //         lengthMenu: 'Ver _MENU_ entradas',
            //         info: 'Viendo _START_ de _END_ de _TOTAL_ resultados',
            //         paginate: {
            //             first: 'Primero',
            //             last: 'Último',
            //             next: 'Siguiente',
            //             previous: 'Anterior'
            //         }
            //     },
            //     deferLoading: 0 // No cargar datos al inicio
            // });
            // fin consulta con paginacion server side


            $('#btnBuscar').on('click', function() {
                $('#busquedaRapida').removeData('producto-id');
                inicializarTablaStock();
            });

            $('#filtroDeposito').on('change', function() {
                if (tablaStock) {
                    tablaStock.destroy();
                    $('#tablaStock').empty();
                }
                inicializarTablaStock();
            });

            $('#busquedaRapida').on('keyup', function(e) {
                if (e.keyCode == 13) {
                    $('#busquedaRapida').removeData('producto-id');
                    if (tablaStock) tablaStock.ajax.reload();
                }
                });

            inicializarTablaStock();
        });
    </script>
</body>

</html>