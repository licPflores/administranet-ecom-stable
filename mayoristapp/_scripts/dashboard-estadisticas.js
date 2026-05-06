

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






function crearGrafico(datos){
    var ctx = document.getElementById(datos.id).getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: datos.nombresClientes,
            datasets: [{
                label: datos.label,
                data: datos.saldosClientes,
                backgroundColor: obtenerColores(datos.saldosClientes.length), 
                //borderColor: datos.borderColor, // Color del borde de las barras
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    }

    function obtenerColores(cantidad) {
        // Generar colores de manera dinámica (puedes personalizar esta lógica según tus preferencias)
        var colores = [];
        for (var i = 0; i < cantidad; i++) {
            colores.push(generarColorAleatorio());
        }
        return colores;
    }

    function generarColorAleatorio() {
        // Generar un color hexadecimal aleatorio
        return '#' + Math.floor(Math.random()*16777215).toString(16);
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
