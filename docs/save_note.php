<?php
if (isset($_POST['file']) && isset($_POST['time']) && isset($_POST['text'])) {
    $fileKey = $_POST['file'];
    $note = array(
        'time' => $_POST['time'],
        'text' => $_POST['text']
    );
    
    $notesDir = 'notas';
    $notesFile = $notesDir . '/' . md5($fileKey) . '.json';
    
    // Crear directorio si no existe
    if (!file_exists($notesDir)) {
        mkdir($notesDir, 0777, true);
    }
    
    // Leer notas existentes
    $notes = array();
    if (file_exists($notesFile)) {
        $notes = json_decode(file_get_contents($notesFile), true);
    }
    
    // Añadir nueva nota
    $notes[] = $note;
    
    // Guardar
    file_put_contents($notesFile, json_encode($notes));
    echo "Nota guardada";
} else {
    echo "Error: Datos incompletos";
}
?>