<?php
if (isset($_GET['file'])) {
    $fileKey = $_GET['file'];
    $notesFile = 'notas/' . md5($fileKey) . '.json';
    
    if (file_exists($notesFile)) {
        $notes = json_decode(file_get_contents($notesFile), true);
        echo json_encode($notes);
    } else {
        echo '[]';
    }
} else {
    echo '[]';
}
?>