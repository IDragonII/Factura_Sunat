<?php
// Configuración de la base de datos
$host = "localhost"; // o tu servidor de base de datos
$dbname = "sunat";
$username = "root"; // tu usuario de base de datos
$password = ""; // tu contraseña de base de datos

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $pass_confirm = $_POST['password_confirm'];

    // Verifica que las contraseñas coincidan
    if ($pass !== $pass_confirm) {
        echo "Las contraseñas no coinciden.";
    } else {
        // Cifra la contraseña
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // Verifica si el usuario ya existe
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "El nombre de usuario ya está en uso.";
        } else {
            // Inserta el nuevo usuario
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed_password);
            if ($stmt->execute()) {
                // Redirige al usuario a login.html después de un registro exitoso
                header("Location: Login.html");
                exit(); // Termina el script para evitar que el código posterior se ejecute
            } else {
                echo "Error al registrar el usuario.";
            }
        }
    }
}

$conn->close();
?>
