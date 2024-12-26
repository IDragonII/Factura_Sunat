<?php
// Inicia la sesión
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión activa, redirige al login
    header("Location: login.html");
    exit();
}

// Si hay sesión, puedes mostrar la página protegida
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Estilo del encabezado */
        header {
            background-color: #333;
            /* Fondo oscuro */
            color: white;
            /* Texto blanco */
            padding: 10px 20px;
            /* Espaciado alrededor */
            display: flex;
            /* Utilizamos flexbox */
            justify-content: space-between;
            /* Espacio entre los elementos */
            align-items: center;
            /* Alineación vertical */
            position: sticky;
            /* Fijo en la parte superior */
            top: 0;
            /* Fijar en la parte superior de la pantalla */
            z-index: 1000;
            /* Asegura que esté encima de otros elementos */
        }

        /* Estilo para el logo */
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo a {
            color: white;
            text-decoration: none;
        }

        /* Estilo para la barra de navegación */
        nav {
            display: flex;
            gap: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #555;
        }

        /* Estilo para los botones de login y register */
        .auth-buttons a {
            background-color: #4CAF50;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .auth-buttons a:hover {
            background-color: #45a049;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 3%;
        }


        h1 {
            text-align: center;
            color: #333;
        }

        .form-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-group {
            flex: 1 1 calc(50% - 20px);
            margin-bottom: 10px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            padding: 8px;
            width: 100%;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Estilo para los cuadros de la tabla */
        .form-table input[type="text"],
        .form-table input[type="number"],
        .form-table input[type="date"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: #fff;
            width: 100%;
        }

        .form-table input[type="text"] {
            font-size: 14px;
        }

        .form-table input[type="number"] {
            font-size: 14px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        @media (max-width: 600px) {

            /* Ajustar el tamaño de las celdas y la fuente en pantallas pequeñas */
            th,
            td {
                font-size: 12px;
                padding: 8px;
            }
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .add-item-btn {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }

        .add-item-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <header>
        <!-- Logo -->
        <div class="logo">
            <a href="index.html">Yesi</a>
        </div>

        <!-- Barra de navegación -->

        <!-- Botones de login y register -->
        <div class="auth-buttons">
            <a href="logout.php">Cerrar session</a>
        </div>
    </header>

    <div class="container">
        <h1>Generacion de Factura con Sunat</h1>

        <form action="factura.php" method="POST">

            <!-- Datos del Cliente -->
            <div class="form-container">
                <div class="form-group">
                    <label for="cliente_nrodoc">RUC del Cliente:</label>
                    <input type="text" id="cliente_nrodoc" name="cliente_nrodoc" required>
                </div>

                <div class="form-group">
                    <label for="cliente_razon_social">Razón Social del Cliente:</label>
                    <input type="text" id="cliente_razon_social" name="cliente_razon_social" required>
                </div>

                <div class="form-group">
                    <label for="cliente_direccion">Dirección del Cliente:</label>
                    <input list="direcciones" id="cliente_direccion" name="cliente_direccion" required>
                    <datalist id="direcciones">
                        <option value="VIRTUAL">
                    </datalist>
                </div>
                <div class="form-group">
                    <label for="cliente_direccion">Monto del Cliente:</label>
                    <input list="pago" id="cliente_pago" name="cliente_pago" required>
                </div>

                <div class="form-group">
                    <label for="cliente_pais">País del Cliente:</label>
                    <input type="text" id="cliente_pais" name="cliente_pais" required>
                </div>
            </div>
            <div class="table-container">
                <!-- Tabla para productos -->
                <table id="productos-table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Importe Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="descripcion_1" required></td>
                            <td><input type="number" name="cantidad_1" class="cantidad" required
                                    oninput="calcularImporte(this)"></td>
                            <td><input type="number" name="precio_unitario_1" step="0.01" class="precio-unitario"
                                    required oninput="calcularImporte(this)"></td>
                            <td><input type="number" name="importe_total_1" step="0.01" class="importe-total" readonly>
                            </td>
                            <td><button type="button" class="remove-item-btn"
                                    onclick="removeItem(this)">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Botón para agregar más productos -->
            <button type="button" class="add-item-btn" onclick="addItem()">Agregar Ítem</button>

            <!-- Botón para enviar el formulario -->
            <button type="submit" class="submit-btn">Enviar Factura</button>

        </form>
    </div>

    <script>
        let itemCount = 1; // Contador para los ítems

        // Función para agregar un nuevo producto a la tabla
        function addItem() {
            itemCount++;
            const tableBody = document.querySelector('#productos-table tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td><input type="text" name="descripcion_${itemCount}" required></td>
                <td><input type="number" name="cantidad_${itemCount}" class="cantidad" required oninput="calcularImporte(this)"></td>
                <td><input type="number" name="precio_unitario_${itemCount}" step="0.01" class="precio-unitario" required oninput="calcularImporte(this)"></td>
                <td><input type="number" name="importe_total_${itemCount}" step="0.01" class="importe-total" readonly></td>
                <td><button type="button" class="remove-item-btn" onclick="removeItem(this)">Eliminar</button></td>
            `;

            tableBody.appendChild(newRow);
        }

        // Función para eliminar un producto de la tabla
        function removeItem(button) {
            const row = button.parentElement.parentElement;
            row.remove();
        }

        // Función para calcular el importe total
        function calcularImporte(input) {
            const row = input.parentElement.parentElement;
            const cantidad = row.querySelector('.cantidad').value;
            const precioUnitario = row.querySelector('.precio-unitario').value;
            const importeTotalField = row.querySelector('.importe-total');

            // Calcular el importe total si los valores son válidos
            const importeTotal = cantidad && precioUnitario ? cantidad * precioUnitario : 0;
            importeTotalField.value = importeTotal.toFixed(2);
        }
    </script>

</body>

</html>