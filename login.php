<?php
// Inicia la sesión
session_start();
if (isset($_SESSION['user_id'])) {
    // Redirige a la página principal si ya hay una sesión activa
    header("Location: pagina.php");
    exit();
}

// Configuración de la base de datos
$host = "localhost";
$dbname = "sunat";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Verifica si el usuario existe
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verifica la contraseña
        if (password_verify($pass, $row['password'])) {
            // Almacena el id del usuario en la sesión
            $_SESSION['user_id'] = $row['id'];
            
            // Redirige a pagina.html
            header("Location: pagina.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
}

$conn->close();
?>
