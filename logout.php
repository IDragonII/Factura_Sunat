<?php
// Inicia la sesión
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al usuario al login (o la página que prefieras)
header("Location: login.html");
exit();
?>
