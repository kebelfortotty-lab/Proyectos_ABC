<?php
// save_event.php - Guardar eventos del calendario

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    
    // Aquí deberías guardar en una base de datos
    // Esta es una simulación con sesión
    $_SESSION['message'] = '✅ Evento guardado exitosamente: '.htmlspecialchars($title);
}

header("Location: calendar.php");
exit;
?>