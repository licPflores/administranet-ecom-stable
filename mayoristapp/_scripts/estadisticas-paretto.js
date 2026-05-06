function crearSelect(opcionSeleccionada) {
    var opcionesDinamicas = document.getElementById('opcionesDinamicas');

    // Limpiar opciones dinámicas anteriores
    opcionesDinamicas.innerHTML = '';

    switch(opcionSeleccionada) {
        case 'anual':
            crearSelectAnual();
            break;
        case 'semestral':
            crearSelectSemestral();
            break;
        case 'trimestral':
            crearSelectTrimestral();
            break;
        case 'mensual':
            crearSelectMensual();
            break;
        default:
            break;
    }
}

function crearSelectAnual() {
    var selectAnual = document.createElement('select');
    var añoActual = new Date().getFullYear(); // Obtener el año actual
    for (var i = añoActual; i >= 1900; i--) {
        var option = document.createElement('option');
        option.text = i;
        option.value = i;
        selectAnual.add(option);
    }

    // Establecer el año actual como seleccionado por defecto
    selectAnual.value = añoActual;

    document.getElementById('opcionesDinamicas').appendChild(selectAnual);
}


function crearSelectSemestral() {
    crearSelectAnual();
        var selectSemestre = document.createElement('select');
        var option1 = document.createElement('option');
        var option2 = document.createElement('option');
        option1.text = 'Primer semestre';
        option1.value = '1';
        option2.text = 'Segundo semestre';
        option2.value = '2';
        selectSemestre.add(option1);
        selectSemestre.add(option2);
        document.getElementById('opcionesDinamicas').appendChild(selectSemestre);
    }

function crearSelectTrimestral() {
    crearSelectAnual();
    var selectTrimestre = document.createElement('select');
    for (var i = 1; i <= 4; i++) {
        var option = document.createElement('option');
        option.text = 'Trimestre ' + i;
        option.value = i;
        selectTrimestre.add(option);
    }
    document.getElementById('opcionesDinamicas').appendChild(selectTrimestre);
}

function crearSelectMensual() {
    crearSelectAnual();
    var selectMes = document.createElement('select');
    var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    for (var i = 0; i < meses.length; i++) {
        var option = document.createElement('option');
        option.text = meses[i];
        option.value = i + 1; // Sumar 1 porque los meses en JavaScript van de 0 a 11
        selectMes.add(option);
    }
    document.getElementById('opcionesDinamicas').appendChild(selectMes);
}






//Funciones para graficos paretto

let graficoBarra; // Variable para almacenar el gráfico de barras
let graficoTorta; // Variable para almacenar el gráfico de torta

function GraficoRubros(datos){    
  
  $('#contiene-tabla').show();
  $('#graficoBarraCategoria').hide();
  $('#graficoTorta').show();
  $('#graficoBarra').show();

      // Obtener contextos de los canvas
      const ctxBarra = document.getElementById('graficoBarra').getContext('2d');
      const ctxTorta = document.getElementById('graficoTorta').getContext('2d');

    function obtenerDatosParaGrafico(datos, campo) {
        const rubros = datos.map(dato => dato.rubro);
        const valores = datos.map(dato => dato[campo]);
        return { rubros, valores };
      }

      function actualizarGrafico(grafico, rubros, valores, campo) {
        grafico.data.labels = rubros;
        grafico.data.datasets[0].data = valores;
        grafico.data.datasets[0].label = campo;
        grafico.update();
    }
    
      // Función para crear un gráfico
      function crearGrafico(ctx, rubros, valores, tipoGrafico, campo) {

        const datasets = [{
          label: campo,
          data: valores,
          backgroundColor: tipoGrafico === 'bar' ? 'rgba(54, 162, 235, 0.2)' : [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
            // Puedes agregar más colores si tienes más rubros
          ],
          borderColor: tipoGrafico === 'bar' ? 'rgba(54, 162, 235, 1)' : [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
            // Puedes agregar más colores si tienes más rubros
          ],
          borderWidth: 1
        }];
    
        return new Chart(ctx, {
          type: tipoGrafico,
          data: {
            labels: rubros,
            datasets: datasets
          },
          options: {
            responsive: true,
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true,
                  callback: function(value, index, values) {
                    if (campo === 'Porcentaje Acumulado') {
                      return '%' + value.toLocaleString('es', { style: 'percent', minimumFractionDigits: 2 });
                    } else if (campo === 'Porcentaje Contribucion') {
                      return '%' + value.toLocaleString('es', { style: 'percent', minimumFractionDigits: 2 });
                    } else {
                      return value;
                    }
                  }
                }
              }]
            }
          }
        });
      }
    
      // Datos de ejemplo
     


      // Obtener datos para los gráficos
      const { rubros, valores: valoresBarra } = obtenerDatosParaGrafico(datos, 'porcentajeAcumulado');
      const { valores: valoresTorta } = obtenerDatosParaGrafico(datos, 'porcentajeContribucion');
    
      if (graficoBarra) {
          actualizarGrafico(graficoBarra, rubros, valoresBarra, 'Porcentaje Acumulado');
      } else {
          graficoBarra = crearGrafico(ctxBarra, rubros, valoresBarra, 'bar', 'Porcentaje Acumulado');
      }

      if (graficoTorta) {
          actualizarGrafico(graficoTorta, rubros, valoresTorta, 'Porcentaje Contribucion');
      } else {
          graficoTorta = crearGrafico(ctxTorta, rubros, valoresTorta, 'doughnut', 'Porcentaje Contribucion');
      }

      llenarTablaRubros(datos)

    

    // Llamar a la función de redimensionamiento cuando cambie el tamaño de la ventana
  

}

function GraficoClintes(datos){

  $('#contiene-tabla').show();
  $('#graficoBarraCategoria').hide();
  $('#graficoTorta').show();
  $('#graficoBarra').show();


  // Obtener contextos de los canvas
  const ctxBarra = document.getElementById('graficoBarra').getContext('2d');
  const ctxTorta = document.getElementById('graficoTorta').getContext('2d');

  function obtenerDatosParaGrafico(datos, campo) {
    const nombresCortos = datos.map(dato => dato.nombre.split(' ')[0])
    
    const nombresCompletos = datos.map(dato => dato.nombre);
    const valores = datos.map(dato => dato[campo]);
    
    
    return { nombresCortos, nombresCompletos, valores };
}

  function actualizarGrafico(grafico, clientes, valores, campo) {
    grafico.data.labels = clientes;
    grafico.data.datasets[0].data = valores;
    grafico.data.datasets[0].label = campo;
    grafico.update();
  }

  // Función para crear un gráfico
  function crearGrafico(ctx, clientes, valores, tipoGrafico, campo) {

    const datasets = [{
      label: campo,
      data: valores,
      backgroundColor: tipoGrafico === 'bar' ? 'rgba(54, 162, 235, 0.2)' : [
        'rgba(255, 99, 132, 0.2)',
        'rgba(54, 162, 235, 0.2)',
        'rgba(255, 206, 86, 0.2)',
        'rgba(75, 192, 192, 0.2)',
        'rgba(153, 102, 255, 0.2)',
        'rgba(255, 159, 64, 0.2)'
        // Puedes agregar más colores si tienes más rubros
      ],
      borderColor: tipoGrafico === 'bar' ? 'rgba(54, 162, 235, 1)' : [
        'rgba(255, 99, 132, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)'
        // Puedes agregar más colores si tienes más rubros
      ],
      borderWidth: 1
    }];

    return new Chart(ctx, {
      type: tipoGrafico,
      data: {
        labels: clientes,
        datasets: datasets
      },
      options: {
        responsive: true,
        scales: {
          xAxes: [{
            ticks: {
              callback: function(value, index, values) {
                // Aquí puedes personalizar el formato de las etiquetas
                // Para obtener el último elemento, puedes usar índices negativos
                const apellido = value.split(' ')[0]; // Obtiene el último elemento del array generado por split
                return apellido;
              },
              fontSize: 10, // Establece el tamaño de fuente para las etiquetas
              maxRotation: 0, // Evita la rotación de las etiquetas
              autoSkip: true, // Habilita el recorte automático de las etiquetas si no caben
              maxTicksLimit: 10 // Establece el número máximo de etiquetas para mostrar
            }
          }],
          yAxes: [{
            ticks: {
              beginAtZero: true,
              callback: function(value, index, values) {
                if (campo === 'Porcentaje Acumulado') {
                  return value.toLocaleString('es', { style: 'percent', minimumFractionDigits: 2 });
                } else if (campo === 'Porcentaje Contribucion') {
                  return  value.toLocaleString('es', { style: 'percent', minimumFractionDigits: 2 });
                } else {
                  return value;
                }
              }
            }
          }]
        }
      }
    });
  }

  // Datos de ejemplo
  // Obtener datos para los gráficos
  const { nombresCortos, nombresCompletos, valores: valoresBarra } = obtenerDatosParaGrafico(datos, 'porcentajeAcumulado');
const { valores: valoresTorta } = obtenerDatosParaGrafico(datos, 'porcentajeContribucion');


  if (graficoBarra) {
    actualizarGrafico(graficoBarra, nombresCortos, valoresBarra, 'Porcentaje Acumulado');
} else {
    graficoBarra = crearGrafico(ctxBarra, nombresCortos, valoresBarra, 'bar', 'Porcentaje Acumulado');
}

// Para el gráfico de torta
if (graficoTorta) {
    actualizarGrafico(graficoTorta, nombresCompletos, valoresTorta, 'Porcentaje Contribucion');
} else {
    graficoTorta = crearGrafico(ctxTorta, nombresCompletos, valoresTorta, 'doughnut', 'Porcentaje Contribucion');
}

  llenarTablaClientes(datos)
}


var grafico;
function GraficoCategoria(response){

          $('#contiene-tabla').show();
          $('#graficoBarraCategoria').show();
          $('#graficoTorta').hide();
          $('#graficoBarra').hide();


          const labels = ['Cobranzas', 'Ventas', 'Compras', 'Gastos', 'Pagos'];

          const data = {
              labels: labels,
              datasets: [{
                  label: 'Cobranzas y Ventas',
                  data: [response.cobranzas, response.ventas, null, null, null],
                  borderColor: 'rgba(69, 248, 84, 1)',
                  borderWidth: 1,
                  fill: true
              }, {
                  label: 'Compras, Gastos y Pagos',
                  data: [null, null, response.compras, response.gastos, response.pagos],
                  borderColor: 'rgba(248, 37, 37, 1)',
                  borderWidth: 1,
                  fill: true
              }]
          };

          if (grafico) {
              grafico.data.datasets.forEach((dataset, i) => {
                  dataset.data = data.datasets[i].data;
              });
              grafico.update();
          } else {
              const graph = document.querySelector("#graficoBarraCategoria");
              grafico = new Chart(graph, {
                  type: 'line',
                  data: data,
                  options: {
                      scales: {
                          y: {
                              ticks: {
                                  callback: function(value, index, values) {
                                      return '$' + (value / 1000000) + 'M'; // Formatear el valor a $X M
                                  }
                              }
                          }
                      }
                  }
              });
          }
          //llenar tabla
          //var contienes = $('#myTable');

          //actualizarValores(response.cobranzas, response.ventas,  response.compras, response.gastos, response.pagos);
          llenarTablaCategoria(response)
}

function llenarTablaCategoria(datos) {
  var tabla = document.getElementById('myTable');
  vaciarTabla()

        // Iterar sobre los datos y agregar cada fila
        for (var categoria in datos) {
          if (datos.hasOwnProperty(categoria)) {
              var fila = tabla.insertRow(-1); // Insertar fila al final de la tabla
              fila.classList.add("dt-nowrap"); // Agregar la clase "dt-nowrap" a la fila
  
              var categoriaCell = fila.insertCell(0); // Celda para la categoría
              var totalCell = fila.insertCell(1); // Celda para el total
  
              categoriaCell.textContent = capitalizeFirstLetter(categoria)
              totalCell.textContent = datos[categoria].toLocaleString('es-AR', {style: 'currency', currency: 'ARS'});
          }
      }
  }


  function llenarTablaRubros(datos) {
    var tabla = document.getElementById('myTable');
    vaciarTabla()
  
          // Iterar sobre los datos y agregar cada fila
          for (categoria in datos) {
            // if (datos.hasOwnProperty(key)) {
            //   var rubro = datos[key].rubro;
            //console.log(datos[categoria].rubro)
                var fila = tabla.insertRow(-1); // Insertar fila al final de la tabla
                fila.classList.add("dt-nowrap"); // Agregar la clase "dt-nowrap" a la fila
    
                var categoriaCell = fila.insertCell(0); // Celda para la categoría
                var totalCell = fila.insertCell(1); // Celda para el total
    
                let rubro = datos[categoria].rubro + ' (Cod:' + datos[categoria].codigo + ')'
                categoriaCell.textContent = capitalizeFirstLetter(rubro)

                var total = parseFloat(datos[categoria].totalCantidad);

                totalCell.textContent = total.toLocaleString('es-AR', {style: 'currency', currency: 'ARS'});
            }
          
    }
  
    
  function llenarTablaClientes(datos) {
    var tabla = document.getElementById('myTable');
    vaciarTabla()
  
          // Iterar sobre los datos y agregar cada fila
          for (categoria in datos) {
            // if (datos.hasOwnProperty(key)) {
            //   var rubro = datos[key].rubro;
            //console.log(datos[categoria].rubro)
                var fila = tabla.insertRow(-1); // Insertar fila al final de la tabla
                fila.classList.add("dt-nowrap"); // Agregar la clase "dt-nowrap" a la fila
    
                var categoriaCell = fila.insertCell(0); // Celda para la categoría
                var totalCell = fila.insertCell(1); // Celda para el total
    
                let rubro = datos[categoria].nombre + ' (Cod:' + datos[categoria].codigo + ')'
                categoriaCell.textContent = capitalizeFirstLetter(rubro)

                var total = parseFloat(datos[categoria].totalCantidad);

                totalCell.textContent = total.toLocaleString('es-AR', {style: 'currency', currency: 'ARS'});
            }
          
    }




function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function vaciarTabla() {
  var tabla = document.getElementById('myTable');
  // Eliminar todas las filas excepto la primera (encabezados)
  while (tabla.rows.length > 1) {
      tabla.deleteRow(1);
  }
}




