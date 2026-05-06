<?php 
// Datos simulados para motivos de no entrega
$motivos_no_entrega = array(
    "No se encuentra en domicilio",
    "Error de facturación",
    "Error de mercadería",
    "Mercadería defectuosa"
);

// Datos simulados para usuarios choferes
$choferes = array(
    array("id" => 1, "nombre" => "Juan Pérez"),
    array("id" => 2, "nombre" => "María López"),
    array("id" => 3, "nombre" => "Carlos García"),
    array("id" => 4, "nombre" => "Ana Fernández")
);

// Datos simulados para comprobantes
$comprobantes = array(
    array("fecha" => "2025-05-07", "factura" => "F001-12345", "remito" => "R001-54321", "cliente" => "Cliente A", "monto" => 1500, "entregado" => "No"),
    array("fecha" => "2025-05-06", "factura" => "F001-12346", "remito" => "R001-54322", "cliente" => "Cliente B", "monto" => 2000, "entregado" => "Sí")
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Comprobante</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h1 {
            text-align: center;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 500px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-btn {
            cursor: pointer;
            font-size: 20px;
            color: red;
        }

        .modal-body {
            margin-top: 20px;
        }

        .modal-body label {
            display: block;
            margin-bottom: 5px;
        }

        .modal-body select, .modal-body textarea, .modal-body input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .modal-footer {
            text-align: right;
        }

        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .modal-footer .save-btn {
            background-color: #28a745;
            color: white;
        }

        .modal-footer .cancel-btn {
            background-color: #dc3545;
            color: white;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <h1>Logística - Comprobantes por Ruta</h1>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Nro Comp Factura</th>
                <th>Nro Comp Remito</th>
                <th>Cliente</th>
                <th>Monto Factura</th>
                <th>Entregado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($comprobantes as $comprobante) {
                echo "<tr>
                    <td>{$comprobante['fecha']}</td>
                    <td>{$comprobante['factura']}</td>
                    <td>{$comprobante['remito']}</td>
                    <td>{$comprobante['cliente']}</td>
                    <td>{$comprobante['monto']}</td>
                    <td>{$comprobante['entregado']}</td>
                    <td><button onclick=\"abrirModal('{$comprobante['factura']}')\">Actualizar</button></td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Actualizar Comprobante</h2>
                <span class="close-btn" onclick="cerrarModal()">X</span>
            </div>
            <div class="modal-body">
                <form id="form-actualizar" action="procesar_actualizacion.php" method="POST">
                    <input type="hidden" name="id_comprobante" id="id_comprobante">
                    
                    <label for="chofer">Seleccionar Chofer</label>
                    <select name="chofer" id="chofer" class="select2" required>
                        <option value="">Seleccione un chofer</option>
                        <?php foreach ($choferes as $chofer): ?>
                            <option value="<?= $chofer['id'] ?>"><?= $chofer['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="entregado">¿Entregado?</label>
                    <select name="entregado" id="entregado" required>
                        <option value="Sí">Sí</option>
                        <option value="No">No</option>
                    </select>
                    <div id="motivo-no-entrega" style="display: none;">
                        <label for="motivo">Motivo de no entrega:</label>
                        <select name="motivo" id="motivo">
                            <?php foreach ($motivos_no_entrega as $motivo): ?>
                                <option value="<?= $motivo ?>"><?= $motivo ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label for="detalle">Detalle:</label>
                        <textarea name="detalle" id="detalle" rows="4"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="cerrarModal()">Cancelar</button>
                <button class="save-btn" onclick="document.getElementById('form-actualizar').submit()">Guardar</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script>
        function abrirModal(idComprobante) {
            document.getElementById('modal').style.display = 'flex';
            document.getElementById('id_comprobante').value = idComprobante;
        }

        function cerrarModal() {
            document.getElementById('modal').style.display = 'none';
        }

        document.getElementById('entregado').addEventListener('change', function() {
            const motivoSection = document.getElementById('motivo-no-entrega');
            motivoSection.style.display = this.value === 'No' ? 'block' : 'none';
        });

        // Inicializar Select2 para búsqueda rápida
        document.addEventListener('DOMContentLoaded', function () {
            $('.select2').select2();
        });
    </script>
</body>
</html>