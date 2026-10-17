<?php
session_start();

// Configuración
$decks_folder = 'flashcards/decks';
$media_folder = 'flashcards/media';

// Crear carpetas si no existen
if (!file_exists('flashcards')) mkdir('flashcards', 0755, true);
if (!file_exists($decks_folder)) mkdir($decks_folder, 0755, true);
if (!file_exists($media_folder)) mkdir($media_folder, 0755, true);

// Procesar acciones
if (isset($_POST['create_deck'])) {
    $deck_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['deck_name']);
    if (!empty($deck_name)) {
        mkdir($decks_folder.'/'.$deck_name, 0755);
        $_SESSION['message'] = 'Mazo creado correctamente';
    }
    header("Location: flashcards.php");
    exit;
}

if (isset($_GET['delete_deck'])) {
    $deck_name = $_GET['delete_deck'];
    if (is_dir($decks_folder.'/'.$deck_name)) {
        array_map('unlink', glob($decks_folder.'/'.$deck_name.'/*.*'));
        rmdir($decks_folder.'/'.$deck_name);
        $_SESSION['message'] = 'Mazo eliminado';
    }
    header("Location: flashcards.php");
    exit;
}

if (isset($_POST['save_card'])) {
    $deck_name = $_POST['deck_name'];
    $id = uniqid();
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    
    // Procesar archivos
    $question_file = processUpload('question_file', $deck_name);
    $answer_file = processUpload('answer_file', $deck_name);
    
    $card_data = array(
        'id' => $id,
        'question' => $question,
        'question_file' => $question_file,
        'answer' => $answer,
        'answer_file' => $answer_file,
        'created_at' => date('Y-m-d H:i:s')
    );
    
    file_put_contents($decks_folder.'/'.$deck_name.'/'.$id.'.json', json_encode($card_data));
    $_SESSION['message'] = 'Flashcard guardada';
    header("Location: flashcards.php?deck=".$deck_name);
    exit;
}

function processUpload($field, $deck_name) {
    global $media_folder;
    if (!empty($_FILES[$field]['name'])) {
        $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $filename = $deck_name.'_'.uniqid().'.'.$ext;
        move_uploaded_file($_FILES[$field]['tmp_name'], $media_folder.'/'.$filename);
        return $filename;
    }
    return '';
}

if (isset($_GET['delete_card'])) {
    $deck_name = $_GET['deck'];
    $card_id = $_GET['delete_card'];
    $card_file = $decks_folder.'/'.$deck_name.'/'.$card_id.'.json';
    
    if (file_exists($card_file)) {
        $card = json_decode(file_get_contents($card_file), true);
        if (!empty($card['question_file']) && file_exists($media_folder.'/'.$card['question_file'])) {
            unlink($media_folder.'/'.$card['question_file']);
        }
        if (!empty($card['answer_file']) && file_exists($media_folder.'/'.$card['answer_file'])) {
            unlink($media_folder.'/'.$card['answer_file']);
        }
        unlink($card_file);
        $_SESSION['message'] = 'Flashcard eliminada';
    }
    header("Location: flashcards.php?deck=".$deck_name);
    exit;
}

// Obtener mazos
$decks = array();
if (is_dir($decks_folder)) {
    $decks = array_diff(scandir($decks_folder), array('..', '.'));
}

// Filtrar mazos si hay búsqueda
$search_term = isset($_GET['search']) ? strtolower($_GET['search']) : '';
if ($search_term) {
    $decks = array_filter($decks, function($deck) use ($search_term) {
        return strpos(strtolower($deck), $search_term) !== false;
    });
}

// Obtener flashcards del mazo seleccionado
$current_deck = isset($_GET['deck']) ? $_GET['deck'] : '';
$cards = array();
if (!empty($current_deck) && is_dir($decks_folder.'/'.$current_deck)) {
    $card_files = array_diff(scandir($decks_folder.'/'.$current_deck), array('..', '.'));
    foreach ($card_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'json') {
            $cards[] = json_decode(file_get_contents($decks_folder.'/'.$current_deck.'/'.$file), true);
        }
    }
}

// Manejar el modo de estudio
$study_mode = isset($_GET['study']);
$current_card_index = isset($_GET['card']) ? intval($_GET['card']) : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Flashcards Avanzadas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #FAFAFA;
            color: #333;
        }
        body.dark-mode {
            background: #121212;
            color: #f1f1f1;
        }
        .nav-menu {
            background: #2c3e50;
            padding: 15px 20px;
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .nav-menu a:hover {
            background: #34495e;
        }
        .nav-menu a.active {
            background: #34495e;
        }
        .container {
            display: flex;
            min-height: calc(100vh - 53px);
        }
        .left-panel {
            width: 250px;
            background: white;
            padding: 15px;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .dark-mode .left-panel {
            background: #1e1e1e;
            border-right: 1px solid #444;
        }
        .center-panel {
            flex-grow: 1;
            padding: 20px;
            background: #f9f9f9;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .dark-mode .center-panel {
            background: #1e1e1e;
        }
        .right-panel {
            width: 350px;
            background: white;
            padding: 15px;
            border-left: 1px solid #ddd;
            overflow-y: auto;
        }
        .dark-mode .right-panel {
            background: #1e1e1e;
            border-left: 1px solid #444;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background: #dff0d8;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            color: #3c763d;
        }
        .dark-mode .message {
            background: #2d4d2d;
            border-color: #3a5a3a;
            color: #d4edda;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            background: white;
            color: #333;
        }
        .dark-mode input[type="text"],
        .dark-mode textarea {
            background: #333;
            border-color: #555;
            color: #f1f1f1;
        }
        textarea {
            height: 80px;
            min-height: 80px;
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .decks-vertical-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            max-height: 300px;
            padding-right: 5px;
        }
        .deck-box {
            width: 100%;
            background: #f0f0f0;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
            box-sizing: border-box;
        }
        .dark-mode .deck-box {
            background: #333;
        }
        .deck-box:hover {
            background: #e0e0e0;
        }
        .dark-mode .deck-box:hover {
            background: #444;
        }
        .deck-box.active {
            background: #d4edda;
        }
        .dark-mode .deck-box.active {
            background: #2d4d2d;
        }
        .deck-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
        }
        .deck-actions {
            margin-top: 5px;
            display: flex;
            gap: 5px;
        }
        .deck-actions a {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.8em;
        }
        .card-container-study {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .card-study {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .dark-mode .card-study {
            background: #252525;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .card-content-study {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            margin: 0 auto;
            max-width: 100%;
        }
        .card-question, .card-answer {
            font-size: 1.2em;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .card-media {
            margin: 15px 0;
            text-align: center;
        }
        .card-media img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 4px;
        }
        .card-answer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
            display: none;
        }
        .dark-mode .card-answer {
            border-top-color: #444;
        }
        .card-actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .cards-view-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }
        .card-view {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .dark-mode .card-view {
            background: #252525;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .card-content-fixed {
            max-height: 200px;
            overflow-y: auto;
            word-wrap: break-word;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .dark-mode .card-content-fixed {
            border-color: #444;
        }
        .file-preview {
            max-width: 100%;
            max-height: 300px;
            margin: 10px 0;
        }
        .text-file {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            text-align: left;
        }
        .dark-mode .text-file {
            background: #333;
        }
        .study-controls {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .study-progress {
            text-align: center;
            margin-top: 15px;
            font-size: 1.1em;
        }
        .completion-message {
            text-align: center;
            font-size: 1.5em;
            margin: 50px 0;
            color: #4CAF50;
        }
        .dark-mode .completion-message {
            color: #81c784;
        }
        .toggle-dark {
            background: #555;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .study-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }
        .dark-mode .study-btn {
            background: #388e3c;
        }
        .editor-toolbar {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .editor-toolbar button {
            background: #e0e0e0;
            color: #333;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .dark-mode .editor-toolbar button {
            background: #444;
            color: #f1f1f1;
        }
        .audio-player {
            width: 100%;
            margin: 15px 0;
        }
        .search-box {
            margin-bottom: 15px;
        }
        .search-box input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .dark-mode .search-box input {
            background: #333;
            border-color: #555;
            color: #f1f1f1;
        }
        .panel-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            gap: 15px;
        }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'on' ? 'dark-mode' : ''; ?>">
    <div class="nav-menu">
        <div>
            <a href="index.php">Reproductor</a>
            <a href="admin_archivos.php">Archivos</a>
            <a href="calendario.php">Calendario</a>
            <a href="flashcards.php" class="active">Flashcards</a>
        </div>
        <button class="toggle-dark" onclick="toggleDarkMode()">
            <?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'on' ? '☀️ Modo Claro' : '🌙 Modo Oscuro'; ?>
        </button>
    </div>

    <div class="container">
        <!-- Panel izquierdo - Mazos -->
        <div class="left-panel">
            <div class="panel-content">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <h2>Mis Mazos</h2>
                
                <div class="search-box">
                    <input type="text" id="deck-search" placeholder="Buscar mazos..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                
                <div class="decks-vertical-container" id="decks-container">
                    <?php if (empty($decks)): ?>
                        <p>No hay mazos creados todavía.</p>
                    <?php else: ?>
                        <?php foreach ($decks as $deck): ?>
                            <div class="deck-box <?php echo ($deck == $current_deck) ? 'active' : ''; ?>" 
                                 onclick="window.location='flashcards.php?deck=<?php echo $deck; ?>'">
                                <div class="deck-name">
                                    <?php echo htmlspecialchars($deck); ?>
                                </div>
                                <div class="deck-actions">
                                    <a href="flashcards.php?delete_deck=<?php echo $deck; ?>" 
                                       onclick="event.stopPropagation(); return confirm('¿Eliminar este mazo y todas sus flashcards?')">✖ Eliminar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <h3>Crear nuevo mazo</h3>
                <form method="post">
                    <div class="form-group">
                        <input type="text" name="deck_name" placeholder="Nombre del mazo" required>
                    </div>
                    <button type="submit" name="create_deck">Crear Mazo</button>
                </form>
                
                <?php if (!empty($current_deck)): ?>
                    <a href="flashcards.php?deck=<?php echo $current_deck; ?>&study" class="study-btn">
                        Estudiar este mazo
                    </a>
                    <a href="flashcards.php" class="study-btn" style="background: #6c757d;">
                        Volver a todos los mazos
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Panel central - Modo estudio o visualización -->
        <div class="center-panel">
            <div class="panel-content">
                <?php if ($study_mode && !empty($cards)): ?>
                    <?php if ($current_card_index < count($cards)): ?>
                        <?php $card = $cards[$current_card_index]; ?>
                        <div class="card-container-study">
                            <div class="card-study">
                                <div class="card-content-study">
                                    <div class="card-question">
                                        <?php echo nl2br(htmlspecialchars($card['question'])); ?>
                                        <?php if (!empty($card['question_file'])): ?>
                                            <div class="card-media">
                                                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $card['question_file'])): ?>
                                                    <img src="<?php echo $media_folder.'/'.$card['question_file']; ?>" class="file-preview">
                                                <?php elseif (preg_match('/\.txt$/i', $card['question_file'])): ?>
                                                    <div class="text-file">
                                                        <?php echo nl2br(htmlspecialchars(file_get_contents($media_folder.'/'.$card['question_file']))); ?>
                                                    </div>
                                                <?php elseif (preg_match('/\.(mp3|wav|ogg)$/i', $card['question_file'])): ?>
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $media_folder.'/'.$card['question_file']; ?>" type="audio/<?php echo pathinfo($card['question_file'], PATHINFO_EXTENSION); ?>">
                                                        Tu navegador no soporta el elemento de audio.
                                                    </audio>
                                                <?php else: ?>
                                                    <a href="<?php echo $media_folder.'/'.$card['question_file']; ?>" target="_blank">Ver archivo adjunto</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card-answer" id="current-answer">
                                        <?php echo nl2br(htmlspecialchars($card['answer'])); ?>
                                        <?php if (!empty($card['answer_file'])): ?>
                                            <div class="card-media">
                                                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $card['answer_file'])): ?>
                                                    <img src="<?php echo $media_folder.'/'.$card['answer_file']; ?>" class="file-preview">
                                                <?php elseif (preg_match('/\.txt$/i', $card['answer_file'])): ?>
                                                    <div class="text-file">
                                                        <?php echo nl2br(htmlspecialchars(file_get_contents($media_folder.'/'.$card['answer_file']))); ?>
                                                    </div>
                                                <?php elseif (preg_match('/\.(mp3|wav|ogg)$/i', $card['answer_file'])): ?>
                                                    <audio controls class="audio-player">
                                                        <source src="<?php echo $media_folder.'/'.$card['answer_file']; ?>" type="audio/<?php echo pathinfo($card['answer_file'], PATHINFO_EXTENSION); ?>">
                                                        Tu navegador no soporta el elemento de audio.
                                                    </audio>
                                                <?php else: ?>
                                                    <a href="<?php echo $media_folder.'/'.$card['answer_file']; ?>" target="_blank">Ver archivo adjunto</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-actions">
                                    <button onclick="showAnswer()">Mostrar Respuesta</button>
                                    <button onclick="nextCard()" id="next-btn" style="display: none;">Siguiente</button>
                                </div>
                            </div>
                            
                            <div class="study-progress">
                                Progreso: <?php echo ($current_card_index + 1); ?> / <?php echo count($cards); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="completion-message">
                            ¡Has completado este mazo!
                            <div style="margin-top: 20px;">
                                <a href="flashcards.php?deck=<?php echo $current_deck; ?>" class="button">Volver al mazo</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif (!empty($current_deck) && !empty($cards)): ?>
                    <div class="cards-view-container">
                        <h2>Mazo: <?php echo htmlspecialchars($current_deck); ?></h2>
                        
                        <?php foreach ($cards as $card): ?>
                            <div class="card-view">
                                <div class="card-content-fixed">
                                    <strong>Pregunta:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($card['question'])); ?>
                                    <?php if (!empty($card['question_file'])): ?>
                                        <div class="card-media">
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $card['question_file'])): ?>
                                                <img src="<?php echo $media_folder.'/'.$card['question_file']; ?>" class="file-preview">
                                            <?php elseif (preg_match('/\.txt$/i', $card['question_file'])): ?>
                                                <div class="text-file">
                                                    <?php echo nl2br(htmlspecialchars(file_get_contents($media_folder.'/'.$card['question_file']))); ?>
                                                </div>
                                            <?php elseif (preg_match('/\.(mp3|wav|ogg)$/i', $card['question_file'])): ?>
                                                <audio controls class="audio-player">
                                                    <source src="<?php echo $media_folder.'/'.$card['question_file']; ?>" type="audio/<?php echo pathinfo($card['question_file'], PATHINFO_EXTENSION); ?>">
                                                    Tu navegador no soporta el elemento de audio.
                                                </audio>
                                            <?php else: ?>
                                                <a href="<?php echo $media_folder.'/'.$card['question_file']; ?>" target="_blank">Ver archivo adjunto</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-content-fixed" style="display: none;">
                                    <strong>Respuesta:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($card['answer'])); ?>
                                    <?php if (!empty($card['answer_file'])): ?>
                                        <div class="card-media">
                                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $card['answer_file'])): ?>
                                                <img src="<?php echo $media_folder.'/'.$card['answer_file']; ?>" class="file-preview">
                                            <?php elseif (preg_match('/\.txt$/i', $card['answer_file'])): ?>
                                                <div class="text-file">
                                                    <?php echo nl2br(htmlspecialchars(file_get_contents($media_folder.'/'.$card['answer_file']))); ?>
                                                </div>
                                            <?php elseif (preg_match('/\.(mp3|wav|ogg)$/i', $card['answer_file'])): ?>
                                                <audio controls class="audio-player">
                                                    <source src="<?php echo $media_folder.'/'.$card['answer_file']; ?>" type="audio/<?php echo pathinfo($card['answer_file'], PATHINFO_EXTENSION); ?>">
                                                    Tu navegador no soporta el elemento de audio.
                                                </audio>
                                            <?php else: ?>
                                                <a href="<?php echo $media_folder.'/'.$card['answer_file']; ?>" target="_blank">Ver archivo adjunto</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-actions">
                                    <button onclick="toggleAnswer(this)">Mostrar Respuesta</button>
                                    <a href="flashcards.php?deck=<?php echo $current_deck; ?>&delete_card=<?php echo $card['id']; ?>" 
                                       onclick="return confirm('¿Eliminar esta flashcard?')" class="button btn-danger">Eliminar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (!empty($current_deck)): ?>
                    <h2>Mazo: <?php echo htmlspecialchars($current_deck); ?></h2>
                    <p>Este mazo no tiene flashcards todavía. ¡Agrega algunas en el panel de edición!</p>
                <?php else: ?>
                    <h2>Selecciona un mazo</h2>
                    <p>Selecciona un mazo del panel izquierdo para ver o editar sus flashcards.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Panel derecho - Edición -->
        <div class="right-panel">
            <?php if (!empty($current_deck)): ?>
                <h2>Editar Mazo: <?php echo htmlspecialchars($current_deck); ?></h2>
                
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="deck_name" value="<?php echo $current_deck; ?>">
                    
                    <div class="editor-toolbar">
                        <button type="button" onclick="formatText('bold', 'question')"><b>B</b></button>
                        <button type="button" onclick="formatText('underline', 'question')"><u>U</u></button>
                        <button type="button" onclick="formatText('strikethrough', 'question')"><s>S</s></button>
                        <button type="button" onclick="formatText('bold', 'answer')"><b>B</b> (Respuesta)</button>
                        <button type="button" onclick="formatText('underline', 'answer')"><u>U</u> (Respuesta)</button>
                        <button type="button" onclick="formatText('strikethrough', 'answer')"><s>S</s> (Respuesta)</button>
                    </div>
                    
                    <div class="form-group">
                        <label for="question">Pregunta:</label>
                        <textarea id="question" name="question" required></textarea>
                        <label>Archivo para pregunta (opcional):</label>
                        <input type="file" name="question_file">
                    </div>
                    
                    <div class="form-group">
                        <label for="answer">Respuesta:</label>
                        <textarea id="answer" name="answer" required></textarea>
                        <label>Archivo para respuesta (opcional):</label>
                        <input type="file" name="answer_file">
                    </div>
                    
                    <button type="submit" name="save_card">Guardar Flashcard</button>
                </form>
            <?php else: ?>
                <h2>Selecciona un mazo</h2>
                <p>Selecciona un mazo del panel izquierdo para editar sus flashcards.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Modo oscuro
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            var isDark = document.body.classList.contains('dark-mode');
            document.cookie = 'darkMode=' + (isDark ? 'on' : 'off') + '; path=/';
            document.querySelector('.toggle-dark').textContent = isDark ? '☀️ Modo Claro' : '🌙 Modo Oscuro';
        }
        
        // Mostrar/ocultar respuesta en modo normal
        function toggleAnswer(button) {
            var card = button.closest('.card-view');
            var answer = card.querySelector('.card-content-fixed:nth-child(2)');
            if (answer.style.display === 'none' || !answer.style.display) {
                answer.style.display = 'block';
                button.textContent = 'Ocultar Respuesta';
            } else {
                answer.style.display = 'none';
                button.textContent = 'Mostrar Respuesta';
            }
        }
        
        // Funciones para el modo estudio
        function showAnswer() {
            document.getElementById('current-answer').style.display = 'block';
            document.getElementById('next-btn').style.display = 'inline-block';
            document.querySelector('button[onclick="showAnswer()"]').style.display = 'none';
        }
        
        function nextCard() {
            var currentIndex = <?php echo $current_card_index; ?>;
            var totalCards = <?php echo count($cards); ?>;
            
            if (currentIndex + 1 < totalCards) {
                window.location = 'flashcards.php?deck=<?php echo $current_deck; ?>&study&card=' + (currentIndex + 1);
            } else {
                window.location = 'flashcards.php?deck=<?php echo $current_deck; ?>&study&card=' + totalCards;
            }
        }
        
        // Teclado rápido para modo estudio
        document.addEventListener('keydown', function(e) {
            <?php if ($study_mode): ?>
                if (e.key === ' ') {
                    e.preventDefault();
                    if (document.getElementById('current-answer').style.display === 'none') {
                        showAnswer();
                    } else {
                        nextCard();
                    }
                }
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    nextCard();
                }
            <?php endif; ?>
        });
        
        // Función para formatear texto
        function formatText(format, field) {
            var textField = document.getElementById(field);
            var start = textField.selectionStart;
            var end = textField.selectionEnd;
            var selectedText = textField.value.substring(start, end);
            
            var formattedText = '';
            switch(format) {
                case 'bold':
                    formattedText = '<b>' + selectedText + '</b>';
                    break;
                case 'underline':
                    formattedText = '<u>' + selectedText + '</u>';
                    break;
                case 'strikethrough':
                    formattedText = '<s>' + selectedText + '</s>';
                    break;
            }
            
            textField.value = textField.value.substring(0, start) + formattedText + textField.value.substring(end);
            
            // Restaurar la posición del cursor
            textField.selectionStart = start + formattedText.length;
            textField.selectionEnd = start + formattedText.length;
            textField.focus();
        }
        
        // Búsqueda de mazos
        document.getElementById('deck-search').addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var decks = document.querySelectorAll('.deck-box');
            
            decks.forEach(function(deck) {
                var deckName = deck.textContent.toLowerCase();
                if (deckName.includes(searchTerm)) {
                    deck.style.display = 'block';
                } else {
                    deck.style.display = 'none';
                }
            });
        });
        
        // Manejar búsqueda al presionar Enter
        document.getElementById('deck-search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var searchTerm = encodeURIComponent(this.value);
                window.location = 'flashcards.php?search=' + searchTerm;
            }
        });
        
        // Ajustar scroll para mostrar el mazo activo
        function scrollToActiveDeck() {
            const activeDeck = document.querySelector('.deck-box.active');
            if (activeDeck) {
                activeDeck.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        }
        
        // Ejecutar al cargar
        window.addEventListener('load', scrollToActiveDeck);
    </script>
</body>
</html>