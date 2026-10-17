<?php
// calendario.php - Calendario con visualización de imágenes y notas

session_start();

// Configuración
$calendar_folder = 'calendario';
$notes_folder = 'notas_calendario';
$image_notes_folder = 'notas_imagenes';

// Crear carpetas si no existen
if (!file_exists($calendar_folder)) {
    mkdir($calendar_folder, 0755, true);
}
if (!file_exists($notes_folder)) {
    mkdir($notes_folder, 0755, true);
}
if (!file_exists($image_notes_folder)) {
    mkdir($image_notes_folder, 0755, true);
}

// Obtener mes y año de la URL o usar el actual
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Ajustar si el mes está fuera de rango
if ($month < 1) {
    $month = 12;
    $year--;
} elseif ($month > 12) {
    $month = 1;
    $year++;
}

// Procesar acciones
if (isset($_POST['add_note'])) {
    $note_content = strip_tags($_POST['note_content']);
    $note_type = $_POST['note_type'];
    $target_folder = ($note_type == 'image') ? $image_notes_folder : $notes_folder;
    $note_file = $target_folder.'/nota_'.date('Y-m-d_H-i-s').'.txt';
    file_put_contents($note_file, $note_content);
    $_SESSION['message'] = 'Nota guardada correctamente';
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

if (isset($_POST['edit_note'])) {
    $note_file = $_POST['note_file'];
    $note_content = strip_tags($_POST['note_content']);
    if (file_exists($note_file)) {
        file_put_contents($note_file, $note_content);
        $_SESSION['message'] = 'Nota actualizada correctamente';
    }
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

if (isset($_POST['delete_note'])) {
    $note_file = $_POST['note_file'];
    if (file_exists($note_file)) {
        unlink($note_file);
        $_SESSION['message'] = 'Nota eliminada correctamente';
    }
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

if (isset($_POST['add_event'])) {
    $event_date = $_POST['event_date'];
    $event_text = strip_tags($_POST['event_text']);
    
    $event_file = $calendar_folder.'/eventos.txt';
    $event_data = $event_date.'|'.$event_text."\n";
    file_put_contents($event_file, $event_data, FILE_APPEND);
    $_SESSION['message'] = 'Evento agregado al calendario';
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

if (isset($_POST['delete_event'])) {
    $event_date = $_POST['event_date'];
    $event_text = $_POST['event_text'];
    
    $event_file = $calendar_folder.'/eventos.txt';
    if (file_exists($event_file)) {
        $events_content = file_get_contents($event_file);
        $events_lines = explode("\n", trim($events_content));
        $new_events = array();
        
        foreach ($events_lines as $line) {
            if (!empty($line)) {
                list($date, $text) = explode('|', $line);
                if (!($date == $event_date && $text == $event_text)) {
                    $new_events[] = $line;
                }
            }
        }
        
        file_put_contents($event_file, implode("\n", $new_events));
        $_SESSION['message'] = 'Evento eliminado correctamente';
    }
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

if (isset($_POST['edit_event'])) {
    $old_date = $_POST['old_date'];
    $old_text = $_POST['old_text'];
    $new_date = $_POST['event_date'];
    $new_text = strip_tags($_POST['event_text']);
    
    $event_file = $calendar_folder.'/eventos.txt';
    if (file_exists($event_file)) {
        $events_content = file_get_contents($event_file);
        $events_lines = explode("\n", trim($events_content));
        $new_events = array();
        
        foreach ($events_lines as $line) {
            if (!empty($line)) {
                list($date, $text) = explode('|', $line);
                if ($date == $old_date && $text == $old_text) {
                    $new_events[] = $new_date.'|'.$new_text;
                } else {
                    $new_events[] = $line;
                }
            }
        }
        
        file_put_contents($event_file, implode("\n", $new_events));
        $_SESSION['message'] = 'Evento actualizado correctamente';
    }
    header("Location: calendario.php?month=$month&year=$year");
    exit;
}

// Obtener eventos existentes
$events = array();
$event_file = $calendar_folder.'/eventos.txt';
if (file_exists($event_file)) {
    $events_content = file_get_contents($event_file);
    $events_lines = explode("\n", trim($events_content));
    foreach ($events_lines as $line) {
        if (!empty($line)) {
            list($date, $text) = explode('|', $line);
            $events[$date][] = $text;
        }
    }
}

// Obtener notas del calendario
$calendar_notes = array();
if (is_dir($notes_folder)) {
    $note_files = scandir($notes_folder);
    foreach ($note_files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'txt') {
            $calendar_notes[] = array(
                'file' => $notes_folder.'/'.$file,
                'content' => file_get_contents($notes_folder.'/'.$file),
                'date' => date('Y-m-d H:i', filemtime($notes_folder.'/'.$file))
            );
        }
    }
    // Ordenar notas por fecha (más reciente primero)
    usort($calendar_notes, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
}

// Obtener notas de imágenes
$image_notes = array();
if (is_dir($image_notes_folder)) {
    $note_files = scandir($image_notes_folder);
    foreach ($note_files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'txt') {
            $image_notes[] = array(
                'file' => $image_notes_folder.'/'.$file,
                'content' => file_get_contents($image_notes_folder.'/'.$file),
                'date' => date('Y-m-d H:i', filemtime($image_notes_folder.'/'.$file))
            );
        }
    }
    // Ordenar notas por fecha (más reciente primero)
    usort($image_notes, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
}

// Obtener imágenes
$images = array();
if (is_dir($calendar_folder)) {
    $files = scandir($calendar_folder);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && $file != 'eventos.txt') {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                $images[] = array(
                    'name' => $file,
                    'path' => $calendar_folder.'/'.$file
                );
            }
        }
    }
}

// Verificar si mostrar imagen seleccionada
$selected_image = null;
if (isset($_GET['view_image']) && file_exists($_GET['view_image'])) {
    $selected_image = $_GET['view_image'];
}

// Nombres de los meses
$month_names = array(
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario Personal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .left-panel {
            width: 20%;  /* Más estrecho */
            padding: 5px;
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .right-panel {
            width: 80%;  /* Más ancho */
            padding: 10px;
            background: #f9f9f9;
            overflow-y: auto;
        }
        .nav-menu {
            background: #2c3e50;
            padding: 15px 20px;
            display: flex;
            gap: 10px;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 5px;
            border-radius: 3px;
        }
        .nav-menu a:hover {
            background: #34495e;
        }
        .nav-menu a.active {
            background: #34495e;
        }
        .section {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
        }
        .file-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 8px;
            margin-bottom: 12px;
        }
        .file-item {
            padding: 6px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 0.9em;
        }
        .file-item:hover {
            background: #f0f0f0;
        }
        .icon-image {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23666"><path d="M8.5 13.5l2.5 3 3.5-4.5 4.5 6H5m16 1V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z"/></svg>');
            background-size: contain;
            margin-right: 8px;
        }
        .image-preview {
            max-width: 100%;
            max-height: 400px;
            display: block;
            margin: 10px auto;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .month-navigator {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .month-navigator button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .month-navigator button:hover {
            background: #45a049;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }
        .calendar-day-header {
            text-align: center;
            font-weight: bold;
            padding: 5px;
            background: #f0f0f0;
            font-size: 0.8em;
        }
        .calendar-day {
            min-height: 90px;
            border: 1px solid #eee;
            padding: 5px;
            font-size: 0.8em;
        }
        .calendar-day.empty {
            background: #f9f9f9;
        }
        .calendar-day.today {
            background: #e3f2fd;
        }
        .day-number {
            font-weight: bold;
            margin-bottom: 3px;
            text-align: right;
        }
        .event {
            font-size: 0.95em;
            background: #e8f5e9;
            padding: 2px 4px;
            margin: 2px 0;
            border-radius: 2px;
            display: flex;
            justify-content: space-between;
        }
        .event-actions {
            display: none;
        }
        .event:hover .event-actions {
            display: inline;
        }
        .event-actions a {
            color: #666;
            margin-left: 3px;
            font-size: 0.7em;
            text-decoration: none;
        }
        .note-form textarea {
            width: 100%;
            height: 70px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 0.9em;
        }
        .note-item {
            padding: 8px;
            border-bottom: 1px solid #eee;
            margin-bottom: 8px;
            position: relative;
            font-size: 0.9em;
        }
        .note-date {
            font-size: 0.7em;
            color: #666;
        }
        .note-actions {
            position: absolute;
            top: 8px;
            right: 8px;
        }
        .note-actions a {
            color: #666;
            margin-left: 5px;
            text-decoration: none;
            font-size: 0.8em;
        }
        .form-group {
            margin-bottom: 12px;
        }
        input[type="text"], input[type="date"], textarea, select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            font-size: 0.9em;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9em;
        }
        button:hover {
            background: #45a049;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .btn-info {
            background: #2196F3;
        }
        .btn-info:hover {
            background: #0b7dda;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 15px;
            border: 1px solid #888;
            width: 90%;
            max-width: 450px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .icon {
            display: inline-block;
            width: 14px;
            height: 14px;
            background-size: contain;
            background-repeat: no-repeat;
            vertical-align: middle;
            margin-right: 3px;
        }
        .icon-edit {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23666"><path d="M20.71 7.04c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.37-.39-1.02-.39-1.41 0l-1.84 1.83 3.75 3.75M3 17.25V21h3.75L17.81 9.93l-3.75-3.75L3 17.25z"/></svg>');
        }
        .icon-delete {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23666"><path d="M19 4h-3.5l-1-1h-5l-1 1H5v2h14M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12z"/></svg>');
        }
        .notes-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .notes-tab {
            padding: 8px 15px;
            cursor: pointer;
            background: #f0f0f0;
            margin-right: 5px;
            border-radius: 3px 3px 0 0;
        }
        .notes-tab.active {
            background: white;
            border: 1px solid #ddd;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        .notes-content {
            display: none;
        }
        .notes-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Menú de navegación -->
    <div class="nav-menu">
        <a href="index.php">Reproductor</a>
        <a href="admin_archivos.php">Archivos</a>
        <a href="calendario.php" class="active">Calendario</a>
        <a href="flashcards.php">Flashcards</a>
    </div>

    <div class="container">
        <!-- Panel izquierdo - Archivos -->
        <div class="left-panel">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            
            <div class="section">
                <h2>Imágenes</h2>
                <?php if (!empty($images)): ?>
                    <div class="file-list">
                        <?php foreach ($images as $image): ?>
                            <div class="file-item" onclick="window.location.href='calendario.php?view_image=<?php echo urlencode($image['path']); ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>'">
                                <span class="icon-image"></span>
                                <?php echo htmlspecialchars($image['name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No hay imágenes en la carpeta del calendario.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Panel derecho - Calendario/Imagen/Notas -->
        <div class="right-panel">
            <?php if ($selected_image): ?>
                <div class="section">
                    <h2>Vista de Imagen</h2>
                    <a href="calendario.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" style="display:inline-block;margin-bottom:15px;">← Volver al calendario</a>
                    <img src="<?php echo htmlspecialchars($selected_image); ?>" class="image-preview" alt="Imagen seleccionada">
                    
                    <!-- Notas para imágenes -->
                    <div class="section">
                        <h2>Notas de la Imagen</h2>
                        <form method="post" class="note-form">
                            <input type="hidden" name="note_type" value="image">
                            <textarea name="note_content" placeholder="Escribe una nota sobre esta imagen..." required></textarea>
                            <button type="submit" name="add_note">Guardar Nota</button>
                        </form>
                        
                        <h3>Notas anteriores:</h3>
                        <?php if (!empty($image_notes)): ?>
                            <?php foreach ($image_notes as $note): ?>
                                <div class="note-item">
                                    <div class="note-date"><?php echo $note['date']; ?></div>
                                    <div class="note-actions">
                                        <a href="#" onclick="editNote('<?php echo $note['file']; ?>')"><span class="icon icon-edit"></span></a>
                                        <a href="#" onclick="deleteNote('<?php echo $note['file']; ?>')"><span class="icon icon-delete"></span></a>
                                    </div>
                                    <div class="note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay notas para esta imagen.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Calendario -->
                <div class="section">
                    <div class="calendar-header">
                        <h2><?php echo $month_names[$month].' '.$year; ?></h2>
                        <div class="month-navigator">
                            <button onclick="window.location.href='calendario.php?month=<?php echo $month-1; ?>&year=<?php echo ($month == 1) ? $year-1 : $year; ?>'">&lt;</button>
                            <button onclick="window.location.href='calendario.php?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>'">Hoy</button>
                            <button onclick="window.location.href='calendario.php?month=<?php echo $month+1; ?>&year=<?php echo ($month == 12) ? $year+1 : $year; ?>'">&gt;</button>
                        </div>
                    </div>
                    
                    <div class="calendar-header">
                        <form method="post" style="display:flex; gap:8px; width:100%;">
                            <input type="date" name="event_date" required style="flex:1;">
                            <input type="text" name="event_text" placeholder="Nuevo evento" required style="flex:2;">
                            <button type="submit" name="add_event">Agregar</button>
                        </form>
                    </div>
                    
                    <?php
                    // Generar calendario
                    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
                    $first_day = date('N', mktime(0, 0, 0, $month, 1, $year));
                    $today = (date('n') == $month && date('Y') == $year) ? date('j') : 0;
                    
                    // Nombres de los días
                    $day_names = array('Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom');
                    ?>
                    
                    <div class="calendar-grid">
                        <?php foreach ($day_names as $day): ?>
                            <div class="calendar-day-header"><?php echo $day; ?></div>
                        <?php endforeach; ?>
                        
                        <?php
                        // Espacios vacíos al inicio
                        for ($i = 1; $i < $first_day; $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }
                        
                        // Días del mes
                        for ($day = 1; $day <= $days_in_month; $day++) {
                            $current_date = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
                            $is_today = ($day == $today) ? 'today' : '';
                            
                            echo '<div class="calendar-day '.$is_today.'">';
                            echo '<div class="day-number">'.$day.'</div>';
                            
                            // Mostrar eventos para este día
                            if (isset($events[$current_date])) {
                                foreach ($events[$current_date] as $event) {
                                    echo '<div class="event">';
                                    echo htmlspecialchars($event);
                                    echo '<div class="event-actions">';
                                    echo '<a href="#" onclick="editEvent(\''.$current_date.'\', \''.htmlspecialchars($event, ENT_QUOTES).'\')"><span class="icon icon-edit"></span></a>';
                                    echo '<a href="#" onclick="deleteEvent(\''.$current_date.'\', \''.htmlspecialchars($event, ENT_QUOTES).'\')"><span class="icon icon-delete"></span></a>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            
                            echo '</div>';
                            
                            // Saltar a la siguiente línea después de domingo
                            if (($day + $first_day - 1) % 7 == 0 && $day != $days_in_month) {
                                echo '</div><div class="calendar-grid">';
                            }
                        }
                        
                        // Espacios vacíos al final
                        $last_day = date('N', mktime(0, 0, 0, $month, $days_in_month, $year));
                        for ($i = $last_day; $i < 7; $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Notas -->
                <div class="section">
                    <h2>Notas</h2>
                    
                    <div class="notes-tabs">
                        <div class="notes-tab active" onclick="showNotesTab('calendar')">Notas del Calendario</div>
                        <div class="notes-tab" onclick="showNotesTab('image')">Notas de Imágenes</div>
                    </div>
                    
                    <div id="calendar-notes" class="notes-content active">
                        <form method="post" class="note-form">
                            <input type="hidden" name="note_type" value="calendar">
                            <textarea name="note_content" placeholder="Escribe una nueva nota para el calendario..." required></textarea>
                            <button type="submit" name="add_note">Guardar Nota</button>
                        </form>
                        
                        <h3>Notas anteriores:</h3>
                        <?php if (!empty($calendar_notes)): ?>
                            <?php foreach ($calendar_notes as $note): ?>
                                <div class="note-item">
                                    <div class="note-date"><?php echo $note['date']; ?></div>
                                    <div class="note-actions">
                                        <a href="#" onclick="editNote('<?php echo $note['file']; ?>')"><span class="icon icon-edit"></span></a>
                                        <a href="#" onclick="deleteNote('<?php echo $note['file']; ?>')"><span class="icon icon-delete"></span></a>
                                    </div>
                                    <div class="note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay notas del calendario guardadas todavía.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="image-notes" class="notes-content">
                        <?php if (!empty($image_notes)): ?>
                            <?php foreach ($image_notes as $note): ?>
                                <div class="note-item">
                                    <div class="note-date"><?php echo $note['date']; ?></div>
                                    <div class="note-actions">
                                        <a href="#" onclick="editNote('<?php echo $note['file']; ?>')"><span class="icon icon-edit"></span></a>
                                        <a href="#" onclick="deleteNote('<?php echo $note['file']; ?>')"><span class="icon icon-delete"></span></a>
                                    </div>
                                    <div class="note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay notas de imágenes guardadas todavía.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para editar evento -->
    <div id="editEventModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editEventModal').style.display='none'">&times;</span>
            <h2>Editar Evento</h2>
            <form method="post" id="editEventForm">
                <input type="hidden" name="old_date" id="old_date">
                <input type="hidden" name="old_text" id="old_text">
                <input type="hidden" name="month" value="<?php echo $month; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <div class="form-group">
                    <label for="event_date">Fecha:</label>
                    <input type="date" name="event_date" id="event_date" required>
                </div>
                <div class="form-group">
                    <label for="event_text">Evento:</label>
                    <input type="text" name="event_text" id="event_text" required>
                </div>
                <button type="submit" name="edit_event" class="btn-info">Actualizar Evento</button>
                <button type="button" onclick="document.getElementById('editEventModal').style.display='none'" class="btn-danger">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para editar nota -->
    <div id="editNoteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editNoteModal').style.display='none'">&times;</span>
            <h2>Editar Nota</h2>
            <form method="post" id="editNoteForm">
                <input type="hidden" name="note_file" id="note_file">
                <input type="hidden" name="month" value="<?php echo $month; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <div class="form-group">
                    <textarea name="note_content" id="note_content" required></textarea>
                </div>
                <button type="submit" name="edit_note" class="btn-info">Actualizar Nota</button>
                <button type="button" onclick="document.getElementById('editNoteModal').style.display='none'" class="btn-danger">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Formulario oculto para eliminar nota -->
    <form method="post" id="deleteNoteForm" style="display:none;">
        <input type="hidden" name="note_file" id="delete_note_file">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="delete_note">
    </form>

    <!-- Formulario oculto para eliminar evento -->
    <form method="post" id="deleteEventForm" style="display:none;">
        <input type="hidden" name="event_date" id="delete_event_date">
        <input type="hidden" name="event_text" id="delete_event_text">
        <input type="hidden" name="month" value="<?php echo $month; ?>">
        <input type="hidden" name="year" value="<?php echo $year; ?>">
        <input type="hidden" name="delete_event">
    </form>

    <script>
        // Función para editar evento
        function editEvent(date, text) {
            document.getElementById('old_date').value = date;
            document.getElementById('old_text').value = text;
            document.getElementById('event_date').value = date;
            document.getElementById('event_text').value = text;
            document.getElementById('editEventModal').style.display = 'block';
            return false;
        }

        // Función para eliminar evento
        function deleteEvent(date, text) {
            if (confirm('¿Estás seguro de que quieres eliminar este evento?')) {
                document.getElementById('delete_event_date').value = date;
                document.getElementById('delete_event_text').value = text;
                document.getElementById('deleteEventForm').submit();
            }
            return false;
        }

        // Función para editar nota
        function editNote(file) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('note_file').value = file;
                    document.getElementById('note_content').value = xhr.responseText;
                    document.getElementById('editNoteModal').style.display = 'block';
                }
            };
            xhr.open('GET', file, true);
            xhr.send();
            return false;
        }

        // Función para eliminar nota
        function deleteNote(file) {
            if (confirm('¿Estás seguro de que quieres eliminar esta nota?')) {
                document.getElementById('delete_note_file').value = file;
                document.getElementById('deleteNoteForm').submit();
            }
            return false;
        }

        // Función para mostrar pestañas de notas
        function showNotesTab(tab) {
            document.querySelectorAll('.notes-tab').forEach(function(element) {
                element.classList.remove('active');
            });
            document.querySelectorAll('.notes-content').forEach(function(element) {
                element.classList.remove('active');
            });
            
            document.querySelector('.notes-tab[onclick="showNotesTab(\'' + tab + '\')"]').classList.add('active');
            document.getElementById(tab + '-notes').classList.add('active');
        }

        // Cerrar modales al hacer clic fuera de ellos
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>