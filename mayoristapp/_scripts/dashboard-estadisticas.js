var graficosDashboard = {};

// 1. FORMATEADORES AUXILIARES
function formatearMoneda(valor) {
    var numero = Number(valor || 0);
    return '$' + numero.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatearPorcentaje(valor) {
    var numero = Number(valor || 0);
    return numero.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }) + '%';
}

function formatearMonedaCorta(valor) {
    var num = Number(valor || 0);
    if (num >= 1000000) {
        return '$' + (num / 1000000).toFixed(1).replace('.', ',') + 'M';
    } else if (num >= 1000) {
        return '$' + (num / 1000).toFixed(0) + 'K';
    }
    return '$' + num.toFixed(0);
}

function obtenerPaletaGrafico(cantidad) {
    var paleta = [
        '#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#0f766e',
        '#7c3aed', '#db2777', '#0891b2', '#ea580c', '#64748b'
    ];
    var colores = [];
    for (var i = 0; i < cantidad; i++) {
        colores.push(paleta[i % paleta.length]);
    }
    return colores;
}

// 2. PLUGIN PERSONALIZADO PARA ESCRIBIR EL MONTO TOTAL DENTRO DEL DOUGHNUT
const doughnutCenterTextPlugin = {
    id: 'doughnutCenterText',
    beforeDraw: function(chart) {
        if (chart.config.type !== 'doughnut') return;
        
        var centerConfig = chart.config.options.plugins.centerText;
        if (!centerConfig || !centerConfig.display) return;

        var ctx = chart.ctx;
        var width = chart.width;
        var height = chart.height;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        var centerX = width / 2;
        var centerY = height / 2;

        // Subtexto (ej: "TOTAL BRUTO")
        if (centerConfig.subtext) {
            ctx.font = centerConfig.subtextFont || '600 11px sans-serif';
            ctx.fillStyle = centerConfig.subtextColor || '#64748b';
            ctx.fillText(centerConfig.subtext.toUpperCase(), centerX, centerY - 13);
        }

        // Cifra principal (ej: "$70,1M")
        if (centerConfig.text) {
            ctx.font = centerConfig.textFont || 'bold 18px sans-serif';
            ctx.fillStyle = centerConfig.textColor || '#0f172a';
            ctx.fillText(centerConfig.text, centerX, centerY + (centerConfig.subtext ? 10 : 0));
        }

        ctx.restore();
    }
};

// Registrar el plugin en Chart.js
Chart.register(doughnutCenterTextPlugin);

// 3. CREADOR GENÉRICO DE GRÁFICOS
function crearGrafico(configuracion) {
    var canvas = document.getElementById(configuracion.id);
    if (!canvas) return null;

    if (graficosDashboard[configuracion.id]) {
        graficosDashboard[configuracion.id].destroy();
    }

    var ctx = canvas.getContext('2d');
    var tipo = configuracion.tipo || 'bar';
    var etiquetas = configuracion.etiquetas || [];
    var datos = configuracion.valores || [];
    var colores = configuracion.colores || obtenerPaletaGrafico(datos.length);

    var opciones = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: configuracion.mostrarLeyenda !== undefined ? configuracion.mostrarLeyenda : false,
                position: configuracion.leyenda || 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var value = context.raw || 0;
                        var label = context.dataset.label || context.label || '';
                        if (configuracion.formatoTooltip === 'moneda') {
                            var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '';
                            return ' ' + label + ': ' + formatearMoneda(value) + (pct ? ' (' + pct + ')' : '');
                        }
                        return ' ' + label + ': ' + value;
                    }
                }
            }
        }
    };

    if (tipo === 'doughnut') {
        opciones.cutout = configuracion.cutout || '70%';
        if (configuracion.centerText) {
            opciones.plugins.centerText = configuracion.centerText;
        }
    }

    if (tipo === 'bar' || tipo === 'line') {
        opciones.indexAxis = configuracion.ejeHorizontal ? 'y' : 'x';
        opciones.scales = {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(val) {
                        return configuracion.formatoEjeX === 'moneda' ? formatearMonedaCorta(val) : val;
                    }
                }
            },
            y: { beginAtZero: true }
        };
    }

    graficosDashboard[configuracion.id] = new Chart(ctx, {
        type: tipo,
        data: {
            labels: etiquetas,
            datasets: [{
                label: configuracion.label || '',
                data: datos,
                backgroundColor: colores,
                borderColor: configuracion.borderColor || colores,
                borderWidth: configuracion.borderWidth || 1,
                pointRadius: configuracion.pointRadius || 0,
                pointHoverRadius: configuracion.pointHoverRadius || 0,
                pointBackgroundColor: configuracion.pointBackgroundColor || colores
            }]
        },
        options: opciones
    });

    return graficosDashboard[configuracion.id];
}

function construirGraficoClientes(datos) {
    return crearGrafico({
        id: datos.id,
        tipo: datos.tipo || 'bar',
        ejeHorizontal: true,
        etiquetas: datos.nombresClientes,
        valores: datos.saldosClientes,
        label: datos.label,
        colores: datos.colores,
        borderColor: datos.borderColor,
        borderWidth: datos.borderWidth,
        pointRadius: datos.pointRadius,
        pointHoverRadius: datos.pointHoverRadius,
        pointBackgroundColor: datos.pointBackgroundColor,
        formatoTooltip: 'moneda',
        formatoEjeX: 'moneda'
    });
}

function construirGraficoProductos(datos) {
    return crearGrafico({
        id: datos.id,
        tipo: 'bar',
        ejeHorizontal: true,
        etiquetas: datos.nombresProductos,
        valores: datos.cantidadesProductos,
        label: datos.label,
        colores: '#10b981' // Verde esmeralda para diferenciar de clientes
    });
}

// 4. RENDIMIENTO COMERCIAL
function renderizarRendimiento(estadisticas) {
    var rendimiento = estadisticas.rendimiento || {
        ventasNetas: estadisticas.importeVentasNetas || 0,
        descuentosOtorgados: estadisticas.importeDescuentosOtorgados || 0,
        porcentajeDescuento: estadisticas.porcentajeDescuentosOtorgados || 0,
        periodo: ''
    };

    var totalBruto = rendimiento.ventasNetas + rendimiento.descuentosOtorgados;
    var pctNetas = totalBruto > 0 ? ((rendimiento.ventasNetas / totalBruto) * 100).toFixed(1) : 0;
    var pctDesc = totalBruto > 0 ? ((rendimiento.descuentosOtorgados / totalBruto) * 100).toFixed(1) : 0;

    // Actualizar números en tarjetas y desglose
    $('#ventasNetas').text(formatearMoneda(rendimiento.ventasNetas));
    $('#descuentosOtorgados').text(formatearMoneda(rendimiento.descuentosOtorgados));
    $('#porcientoDescuento').text(formatearPorcentaje(rendimiento.porcentajeDescuento));

    $('#leyendaVentasNetas').text(formatearMoneda(rendimiento.ventasNetas));
    $('#leyendaDescuentos').text(formatearMoneda(rendimiento.descuentosOtorgados));
    $('#leyendaTotalBruto').text(formatearMoneda(totalBruto));

    $('#porcVentasNetas').text(pctNetas + '%');
    $('#porcDescuentos').text(pctDesc + '%');

    if (rendimiento.periodo) {
        $('#fechaRendimiento').text(rendimiento.periodo);
    }

    // GRAFICO DOUGHNUT CON TEXTO EN EL CENTRO
    crearGrafico({
        id: 'graficoRendimiento',
        tipo: 'doughnut',
        etiquetas: ['Ventas Netas', 'Descuentos'],
        valores: [rendimiento.ventasNetas, rendimiento.descuentosOtorgados],
        label: 'Monto',
        colores: ['#2563eb', '#dc2626'],
        cutout: '72%',
        formatoTooltip: 'moneda',
        centerText: {
            display: true,
            subtext: 'Total',
            subtextColor: '#64748b',
            text: formatearMonedaCorta(totalBruto),
            textColor: '#0f172a',
            textFont: 'bold 18px sans-serif'
        }
    });
}

// 5. CARGA ASÍNCRONA COMPLETA
function cargarEstadisticasAsync() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            type: 'GET',
            url: 'ajax/json-estadisticas-vendedor.php',
            data: { estadisticas: 'true' },
            success: function(response) {
                $('#spinner').addClass('d-none');
                var estadisticas = typeof response === 'string' ? JSON.parse(response) : response;

                renderizarRendimiento(estadisticas);

                animarPedidosAsync(estadisticas.cantidadPedidos, '#pedidosRealizados');
                animarPedidosAsync(estadisticas.pedidosCancelados, '#pedidosCancelados');
                animarPedidosAsync(estadisticas.pedidosFacturados, '#pedidosFacturados');
                animarDineroAsync(estadisticas.cantidadEfectivo);

                // TOP 5 MÁS
                var listaClientesMas = $('#clientesquemascompraron').empty();
                estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                    listaClientesMas.append(
                        $('<li></li>').addClass('cliente-item').append(
                            $('<span></span>').addClass('cliente-codigo').text(cliente.nombre + ' (Cod: ' + cliente.codigo + ')'),
                            $('<span></span>').addClass('cliente-saldo').text(formatearMoneda(cliente.saldo))
                        )
                    );
                });

                // TOP 5 MENOS
                var listaClientesMenos = $('#clientesquemenoscompraron').empty();
                estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                    listaClientesMenos.append(
                        $('<li></li>').addClass('cliente-item').append(
                            $('<span></span>').addClass('cliente-codigo').text(cliente.nombre + ' (Cod: ' + cliente.codigo + ')'),
                            $('<span></span>').addClass('cliente-saldo').text(formatearMoneda(cliente.saldo))
                        )
                    );
                });

                // CONFIGURACIÓN MÁS COMPRARON (BARRAS TRADICIONALES)
                var datosPrimeros = {
                    nombresClientes: [],
                    saldosClientes: [],
                    id: 'graficoClientesMas',
                    label: 'Saldos Clientes',
                    tipo: 'bar',
                    colores: '#2563eb'
                };

                estadisticas.cincoPrimerosClientes.forEach(function(cliente) {
                    datosPrimeros.nombresClientes.push(cliente.nombre.split(' ')[0]);
                    datosPrimeros.saldosClientes.push(cliente.saldo);
                });

                // CONFIGURACIÓN MENOS COMPRARON (DOT PLOT CON LÍNEA DELGADA Y PUNTOS)
                var datosUltimos = {
                    nombresClientes: [],
                    saldosClientes: [],
                    id: 'graficoClientesMenos',
                    label: 'Saldos Clientes',
                    tipo: 'line', // Usamos línea para crear efecto Dot Plot
                    colores: '#fcd34d', // Color de línea guía
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: '#d97706'
                };

                estadisticas.cincoUltimosClientes.forEach(function(cliente) {
                    datosUltimos.nombresClientes.push(cliente.nombre.split(' ')[0]);
                    datosUltimos.saldosClientes.push(cliente.saldo);
                });

                construirGraficoClientes(datosPrimeros);
                construirGraficoClientes(datosUltimos);


                // TOP PRODUCTOS MÁS VENDIDOS
                console.log('Productos más vendidos:', estadisticas.articulos);
                var productos = estadisticas.articulos || estadisticas.articulos || [];
                var listaProductos = $('#productosmasvendidos').empty();

                var datosProductos = {
                    nombresProductos: [],
                    cantidadesProductos: [],
                    id: 'graficoProductosMas',
                    label: 'Unidades Vendidas'
                };

                productos.forEach(function(prod) {
                    var cantidad = parseFloat(prod.Cantidad || 0);
                    var descripcion = prod.Descripcion || '';
                    var idArt = prod.IDArt || '';

                    // Formato de unidades en lista
                    var cantTexto = cantidad.toLocaleString('es-AR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }) + ' un.';

                    // Insertar item en la lista lateral
                    listaProductos.append(
                        $('<li></li>').addClass('cliente-item').append(
                            $('<span></span>').addClass('cliente-codigo').text(descripcion + ' (ID: ' + idArt + ')'),
                            $('<span></span>').addClass('cliente-saldo producto-cantidad').text(cantTexto)
                        )
                    );

                    // Cortar nombres muy largos para el eje del gráfico
                    var descCorta = descripcion.length > 18 ? descripcion.substring(0, 16) + '...' : descripcion;
                    datosProductos.nombresProductos.push(descCorta);
                    datosProductos.cantidadesProductos.push(cantidad);
                });

                construirGraficoProductos(datosProductos);
                // Marca de tiempo de última actualización
                var horaActual = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                $('#ultimaActualizacion').text('Actualizado: ' + horaActual);

                resolve();
                
            },
            error: function(x, e) {
                $('#spinner').addClass('d-none');
                reject(e);
            }
        });
    });
}
// ==========================================
// 6. TEMPORIZADOR DE AUTO-REFRESCO PERIÓDICO
// ==========================================
var timerAutoRefresco = null;
var INTERVALO_REFRESCO_MS = 2 * 60 * 1000; // Refresco cada 2 minutos

function iniciarAutoRefresco() {
    detenerAutoRefresco();

    timerAutoRefresco = setInterval(function() {
        if (document.visibilityState === 'visible') {
            console.log('[Dashboard] Auto-refrescando datos...');
            cargarEstadisticasAsync().catch(function(err) {
                console.error('[Dashboard] Error en auto-refresco:', err);
            });
        }
    }, INTERVALO_REFRESCO_MS);
}

function detenerAutoRefresco() {
    if (timerAutoRefresco) {
        clearInterval(timerAutoRefresco);
        timerAutoRefresco = null;
    }
}

// Detener cuando la pestaña no esté visible para ahorrar consumo de servidor
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        cargarEstadisticasAsync();
        iniciarAutoRefresco();
    } else {
        detenerAutoRefresco();
    }
});


function animarPedidosAsync(cantidad, elemento) {
    return new Promise(function(resolve) {
        var el = $(elemento);
        var duracion = 1500;
        var intervalo = 30;
        var pasos = duracion / intervalo;
        var inc = (cantidad || 0) / pasos;
        var actual = 0;

        var timer = setInterval(function() {
            actual += inc;
            if (actual >= cantidad) {
                actual = cantidad;
                clearInterval(timer);
            }
            el.text(Math.round(actual));
        }, intervalo);

        resolve();
    });
}

function animarDineroAsync(dinero) {
    return new Promise(function(resolve) {
        var totalDineroElement = document.getElementById('dineroTotal');
        var dineroTotalLetra = document.getElementById('dineroTotalLetra');
        if (!totalDineroElement) return resolve();

        var sufijos = ["", "K", "MM", "B"];
        var monto = dinero || 0;
        var sufijoIndex = 0;

        while (monto >= 1000 && sufijoIndex < sufijos.length - 1) {
            monto /= 1000;
            sufijoIndex++;
        }

        if (totalDineroElement) totalDineroElement.textContent = '$' + monto.toFixed(2).replace('.', ',');
        if (dineroTotalLetra) dineroTotalLetra.textContent = sufijos[sufijoIndex];
        
        resolve();
    });
}