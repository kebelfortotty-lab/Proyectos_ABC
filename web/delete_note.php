<?php
if (isset($_POST['file']) && isset($_POST['index'])) {
    $fileKey = $_POST['file'];
    $index = (int)$_POST['index'];
    $notesFile = 'notas/' . md5($fileKey) . '.json';
    
    if (file_exists($notesFile)) {
        $notes = json_decode(file_get_contents($notesFile), true);
        
        if (isset($notes[$index])) {
            array_splice($notes, $index, 1);
            file_put_contents($notesFile, json_encode($notes));
            echo "Nota eliminada";
        } else {
            echo "Error: Índice de nota no válido";
        }
    } else {
        echo "Error: Archivo de notas no encontrado";
    }
} else {
    echo "Error: Datos incompletos";
}
?>