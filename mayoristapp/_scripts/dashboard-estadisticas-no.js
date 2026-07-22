

var graficosDashboard = {};

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

function crearGrafico(configuracion) {
    var canvas = document.getElementById(configuracion.id);
    if (!canvas) {
        return null;
    }

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
                display: tipo === 'doughnut' || tipo === 'pie',
                position: configuracion.leyenda || 'bottom'
            }
        }
    };

    if (tipo === 'bar') {
        opciones.indexAxis = configuracion.ejeHorizontal ? 'y' : 'x';
        opciones.scales = {
            x: {
                beginAtZero: true
            },
            y: {
                beginAtZero: true
            }
        };
    }

    if (tipo === 'doughnut') {
        opciones.cutout = configuracion.cutout || '62%';
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
                borderWidth: 1
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
        colores: datos.colores
    });
}

function renderizarRendimiento(estadisticas) {
    var rendimiento = estadisticas.rendimiento || {
        ventasNetas: estadisticas.importeVentasNetas || 0,
        descuentosOtorgados: estadisticas.importeDescuentosOtorgados || 0,
        porcentajeDescuento: estadisticas.porcentajeDescuentosOtorgados || 0,
        periodo: ''
    };

    $('#ventasNetas').text(formatearMoneda(rendimiento.ventasNetas));
    $('#descuentosOtorgados').text(formatearMoneda(rendimiento.descuentosOtorgados));
    $('#porcientoDescuento').text(formatearPorcentaje(rendimiento.porcentajeDescuento));

    if (rendimiento.periodo) {
        $('#fechaRendimiento').text(rendimiento.periodo);
    }

    crearGrafico({
        id: 'graficoRendimiento',
        tipo: 'doughnut',
        etiquetas: ['Ventas Netas', 'Descuentos'],
        valores: [rendimiento.ventasNetas, rendimiento.descuentosOtorgados],
        label: 'Rendimiento',
        colores: ['#2563eb', '#dc2626'],
        leyenda: 'bottom',
        cutout: '68%'
    });
}

// Función para animar pedidos de manera asíncrona
function animarPedidosAsync(cantidad, elemento) {
    return new Promise(function(resolve, reject) {
        // Supongamos que aquí se realiza alguna operación asíncrona, como animar pedidos
        animarPedidos(cantidad, elemento);
        resolve(); // Resolvemos la promesa ya que hemos terminado con la operación asíncrona
    });
}
// Función para animar pedidos de manera asíncrona
function animarDineroAsync(cantidad) {
    return new Promise(function(resolve, reject) {
        // Supongamos que aquí se realiza alguna operación asíncrona, como animar pedidos
        animarDinero(cantidad);
        resolve(); // Resolvemos la promesa ya que hemos terminado con la operación asíncrona
    });
}

    function mostrarFecha(){
        // JavaScript para mostrar la fecha actual y el mes
        

        // JavaScript para mostrar la fecha actual y el mes
        const fechaActual = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = fechaActual.toLocaleDateString('es-ES', options);

        // Agregar la fecha al div con id "informacion"
        $('#fecha').append(`${fechaFormateada}`);
    }

    function animarPedidos(cantidad, elemento) {
        var element = $(elemento);
        var duracionAnimacion = 2000;
        var intervalo = 50;
        var incremento = cantidad / (duracionAnimacion / intervalo);
        var valorActual = 0;
        var intervaloAnimacion;  // Declarar la variable antes de su primera referencia

        // Función para animar el incremento del valor
        function actualizarValor() {
            valorActual += incremento;

            // Detén la animación cuando alcances o superes la cantidad final
            if (valorActual >= cantidad) {
                valorActual = cantidad;
                clearInterval(intervaloAnimacion);
            }

            // Actualiza el contenido del elemento con el valor actual
            element.text(Math.round(valorActual));
        }

        // Inicia la animación usando setInterval
        intervaloAnimacion = setInterval(actualizarValor, intervalo);
    }


    function animarDinero(dinero) {
        // Elemento donde se mostrará el total de dinero
        var totalDineroElement = document.getElementById('dineroTotal');
        var dineroTotalLetra = document.getElementById('dineroTotalLetra');

        // Monto inicial y final
        var montoInicial = 0;
        var montoFinal = dinero;

        // Duración de la animación en milisegundos
        var duracion = 2000;

        // Número de pasos para la animación
        var pasos = 100;

        // Calcular el incremento en cada paso
        var incremento = (montoFinal - montoInicial) / pasos;

        // Función para formatear el número como moneda
        function formatearComoMoneda(monto) {
            // Definir los sufijos para los números grandes
            var sufijos = ["", "M", "MM", "B", "T"];
        
            // Encontrar el sufijo adecuado para el número
            var sufijoIndex = 0;
            while (monto >= 1000 && sufijoIndex < sufijos.length - 1) {
                monto /= 1000;
                sufijoIndex++;
            }
        
            // Formatear el número con el sufijo adecuado
            return ['$' + monto.toFixed(3).replace(/\./g, ',') , sufijos[sufijoIndex]];
        }

        // Función para actualizar el contenido del div en cada paso
        function actualizarTotalDinero() {

            resultado = formatearComoMoneda(montoInicial);
            totalDineroElement.textContent = resultado[0]
            dineroTotalLetra.textContent = resultado [1]

        }

        // Función principal de animación
        function animar() {
            if (montoInicial < montoFinal) {
            montoInicial += incremento;
            actualizarTotalDinero();
            setTimeout(animar, duracion / pasos);
            } else {
            // Asegurarse de que el monto final sea exacto al valor final
            montoInicial = montoFinal;
            actualizarTotalDinero();
            }
        }

// Iniciar la animación al cargar la página
animar();
};
