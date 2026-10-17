<?php
session_start();

$flashcards_folder = 'flashcards';

if (isset($_POST['card_id']) && isset($_POST['level'])) {
    $card_id = $_POST['card_id'];
    $new_level = intval($_POST['level']);
    
    $card_file = $flashcards_folder.'/'.$card_id.'.json';
    if (file_exists($card_file)) {
        $card = json_decode(file_get_contents($card_file), true);
        $card['level'] = $new_level;
        $card['last_review'] = date('Y-m-d H:i:s');
        file_put_contents($card_file, json_encode($card));
        echo 'OK';
    } else {
        echo 'Error: Flashcard no encontrada';
    }
} else {
    echo 'Error: Datos incompletos';
}
?>