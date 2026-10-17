<?php
// admin_archivos.php - Administrador de Archivos Completo Mejorado

session_start();

// Función para sanitizar nombres de archivos
function sanitizeFilename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    return $filename;
}

// Mostrar mensajes de sesión
function showMessage() {
    if (!empty($_SESSION['message'])) {
        echo '<div class="message" style="padding:10px;margin:10px 0;background:#dff0d8;color:#3c763d;border:1px solid #d6e9c6;border-radius:4px;">'.$_SESSION['message'].'</div>';
        unset($_SESSION['message']);
    }
}

// Procesar acciones
$base_dir = 'archivos';
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : $base_dir;
$current_dir = strpos(realpath($current_dir), realpath($base_dir)) === 0 ? $current_dir : $base_dir;

// Crear carpeta
if (isset($_POST['create_folder']) && isset($_POST['folder_name'])) {
    $new_folder = $current_dir . '/' . sanitizeFilename($_POST['folder_name']);
    if (!file_exists($new_folder)) {
        if (mkdir($new_folder, 0755)) {
            $_SESSION['message'] = '✅ Carpeta creada exitosamente: '.htmlspecialchars($_POST['folder_name']);
        } else {
            $_SESSION['message'] = '❌ Error al crear la carpeta';
        }
    } else {
        $_SESSION['message'] = '⚠️ La carpeta ya existe';
    }
    header("Location: ?dir=".urlencode($current_dir));
    exit;
}

// Subir archivo
if (isset($_FILES['upload_file'])) {
    $target_file = $current_dir . '/' . sanitizeFilename($_FILES['upload_file']['name']);
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target_file)) {
        $_SESSION['message'] = '✅ Archivo subido exitosamente: '.htmlspecialchars($_FILES['upload_file']['name']);
    } else {
        $_SESSION['message'] = '❌ Error al subir el archivo';
    }
    header("Location: ?dir=".urlencode($current_dir));
    exit;
}

// Eliminar archivo/carpeta
if (isset($_GET['delete'])) {
    $item = $current_dir . '/' . $_GET['delete'];
    if (file_exists($item)) {
        if (is_dir($item)) {
            if (deleteDirectory($item)) {
                $_SESSION['message'] = '✅ Carpeta eliminada exitosamente: '.htmlspecialchars($_GET['delete']);
            } else {
                $_SESSION['message'] = '❌ Error al eliminar la carpeta';
            }
        } else {
            if (unlink($item)) {
                $_SESSION['message'] = '✅ Archivo eliminado exitosamente: '.htmlspecialchars($_GET['delete']);
            } else {
                $_SESSION['message'] = '❌ Error al eliminar el archivo';
            }
        }
    }
    header("Location: ?dir=".urlencode($current_dir));
    exit;
}

// Función recursiva para eliminar directorios
function deleteDirectory($dir) {
    if (!file_exists($dir)) return false;
    
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

// Renombrar archivo/carpeta
if (isset($_POST['rename']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
    $old_path = $current_dir . '/' . $_POST['old_name'];
    $new_path = $current_dir . '/' . sanitizeFilename($_POST['new_name']);
    
    if (file_exists($old_path) && !file_exists($new_path)) {
        if (rename($old_path, $new_path)) {
            $_SESSION['message'] = '✅ Renombrado exitosamente a: '.htmlspecialchars($_POST['new_name']);
        } else {
            $_SESSION['message'] = '❌ Error al renombrar';
        }
    } else {
        $_SESSION['message'] = '⚠️ El nombre ya existe o el archivo no existe';
    }
    header("Location: ?dir=".urlencode($current_dir));
    exit;
}

// Mover archivo/carpeta
if (isset($_POST['move']) && isset($_POST['item_name']) && isset($_POST['target_dir'])) {
    $source = $current_dir . '/' . $_POST['item_name'];
    $target = $_POST['target_dir'] . '/' . $_POST['item_name'];
    
    if (file_exists($source) && !file_exists($target)) {
        if (rename($source, $target)) {
            $_SESSION['message'] = '✅ Movido exitosamente a: '.htmlspecialchars($_POST['target_dir']);
        } else {
            $_SESSION['message'] = '❌ Error al mover el archivo/carpeta';
        }
    } else {
        $_SESSION['message'] = '⚠️ El archivo no existe o el destino ya tiene un archivo con ese nombre';
    }
    header("Location: ?dir=".urlencode($current_dir));
    exit;
}

// Editar archivo de texto
if (isset($_POST['edit_file']) && isset($_POST['file_content']) && isset($_POST['file_path'])) {
    if (file_put_contents($_POST['file_path'], $_POST['file_content']) !== false) {
        $_SESSION['message'] = '✅ Archivo guardado exitosamente';
    } else {
        $_SESSION['message'] = '❌ Error al guardar el archivo';
    }
    header("Location: ?dir=".urlencode(dirname($_POST['file_path'])).'&edit='.urlencode($_POST['file_path']));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador de Archivos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .nav-menu {
            padding: 10px 10px;
            background: #2c3e50;
            display: flex;
            gap: 20px;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.3s;
        }
        .nav-menu a:hover {
            background: #34495e;
        }
        .main-content {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        .file-manager {
            width: 70%;
            padding: 20px;
            border-right: 1px solid #eee;
        }
        .editor-panel {
            width: 30%;
            padding: 5px;
            background: #fafafa;
        }
        .breadcrumb {
            padding: 5px;
            background: #f0f0f0;
            border-radius: 5px;
            margin-bottom: 5px;
            font-size: 25px;
        }
        .breadcrumb a {
            color: #4CAF50;
            text-decoration: none;
        }
        .file-list {
            border: 1px solid #ddd;
            border-radius: 3px;
            max-height: 500px;
            overflow-y: auto;
        }
        .file-item {
            padding: 3px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        .file-item:hover {
            background: #f9f9f9;
        }
        .file-icon {
            margin-right: 3px;
            width: 30px;
            text-align: center;
        }
        .file-name {
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-actions {
            display: flex;
            gap: 15px;
        }
        .file-actions a, .file-actions button {
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 3px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .folder {
            background: #e3f2fd;
        }
        .action-panel {
            margin: 2px 0;
            padding: 1px;
            background: #f5f5f5;
            border-radius: 3px;
        }
        .editor-container {
            height: 100%;
        }
        textarea {
            width: 100%;
            height: 70vh;
            font-family: monospace;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            resize: none;
        }
        .editor-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 600px;
        }
        .folder-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 10px 0;
            max-height: 200px;
            overflow-y: auto;
        }
        .folder-tab {
            padding: 5px 10px;
            background: #e0e0e0;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .folder-tab:hover {
            background: #d0d0d0;
        }
        .folder-tab.active {
            background: #4CAF50;
            color: white;
        }
        .empty-editor {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 70vh;
            color: #999;
            font-size: 18px;
            text-align: center;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background: #2196F3;
            color: white;
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Menú de navegación -->
        <div class="nav-menu">
            <a href="index.php">Reproductor Multimedia</a>
            <a href="admin_archivos.php">Administrador de Archivos</a>
            <a href="calendario.php">Calendario</a>
            <a href="flashcards.php">Flashcards</a>
        </div>
        
        <div class="main-content">
            <!-- Panel izquierdo - Gestor de archivos -->
            <div class="file-manager">
                <h2>Gestor de Archivos</h2>
                
                <?php showMessage(); ?>
                
                <!-- Navegación con breadcrumbs -->
                <div class="breadcrumb">
                    <?php
                    $path_parts = explode('/', trim($current_dir, '/'));
                    $current_path = '';
                    echo '<a href="?dir='.$base_dir.'">Inicio</a> / ';
                    foreach ($path_parts as $part) {
                        if ($part === $base_dir) continue;
                        $current_path .= '/' . $part;
                        echo '<a href="?dir='.$base_dir . urlencode($current_path).'">' . $part . '</a> / ';
                    }
                    ?>
                </div>
                
                <!-- Panel de acciones -->
                <div class="action-panel">
                    <h3>Acciones</h3>
                    <form method="post" style="display: flex; gap: 5px; align-items: center; margin-bottom: 5px;">
                        <input type="text" name="folder_name" placeholder="Nombre de carpeta" required style="flex-grow:1;padding:3px;">
                        <button type="submit" name="create_folder" class="btn btn-primary">Crear Carpeta</button>
                    </form>
                    
                    <form method="post" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                        <input type="file" name="upload_file" style="flex-grow:1;">
                        <button type="submit" class="btn btn-secondary">Subir Archivo</button>
                    </form>
                </div>
                
                <!-- Listado de archivos -->
                <div class="file-list">
                    <?php
                    $items = scandir($current_dir);
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') continue;
                        
                        $item_path = $current_dir . '/' . $item;
                        $is_dir = is_dir($item_path);
                        $icon = $is_dir ? '📁' : '📄';
                        $class = $is_dir ? 'folder' : '';
                        
                        echo '<div class="file-item ' . $class . '">';
                        echo '<div class="file-icon">' . $icon . '</div>';
                        echo '<div class="file-name" title="'.$item.'">' . $item . '</div>';
                        echo '<div class="file-actions">';
                        
                        if ($is_dir) {
                            echo '<a href="?dir=' . urlencode($item_path) . '" class="btn btn-primary" style="padding:3px 8px;">Abrir</a>';
                        } else {
                            // Verificar si es archivo de texto para editar
                            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                            $text_exts = array('txt', 'php', 'html', 'css', 'js', 'json', 'xml', 'md');
                            if (in_array($ext, $text_exts)) {
                                echo '<a href="?dir=' . urlencode($current_dir) . '&edit=' . urlencode($item_path) . '" class="btn btn-secondary" style="padding:3px 8px;">Editar</a>';
                            }
                        }
                        
                        echo '<a href="?dir=' . urlencode($current_dir) . '&delete=' . urlencode($item) . '" class="btn btn-danger" style="padding:3px 8px;" onclick="return confirm(\'¿Eliminar ' . $item . '?\')">Eliminar</a>';
                        
                        // Botón para renombrar (abre modal)
                        echo '<button onclick="showRenameModal(\''.htmlspecialchars($item).'\')" style="background: #FF9800; color: white; padding:3px 8px;">Renombrar</button>';
                        
                        // Botón para mover (abre modal)
                        if (!$is_dir) {
                            echo '<button onclick="showMoveModal(\''.htmlspecialchars($item).'\')" style="background: #9C27B0; color: white; padding:3px 8px;">Mover</button>';
                        }
                        
                        echo '</div></div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Panel derecho - Editor -->
            <div class="editor-panel">
                <h2>Editor</h2>
                
                <?php
                if (isset($_GET['edit']) && file_exists($_GET['edit'])) {
                    $file_path = $_GET['edit'];
                    $content = htmlspecialchars(file_get_contents($file_path));
                    $filename = basename($file_path);
                    
                    echo '<div class="editor-container">
                            <h3>Editando: ' . $filename . '</h3>
                            <form method="post">
                                <input type="hidden" name="file_path" value="' . $file_path . '">
                                <textarea name="file_content" id="editor">' . $content . '</textarea>
                                <div class="editor-actions">
                                    <button type="submit" name="edit_file" class="btn btn-primary">Guardar Cambios</button>
                                    <a href="?dir='.urlencode(dirname($file_path)).'" class="btn btn-danger">Cancelar</a>
                                </div>
                            </form>
                          </div>';
                } else {
                    echo '<div class="empty-editor">
                            <div>
                                <p>Selecciona un archivo para editarlo</p>
                                <p>Formatos soportados: .txt, .php, .html, .css, .js, .json, .xml, .md</p>
                            </div>
                          </div>';
                }
                ?>
            </div>
        </div>
        
        <!-- Modal para renombrar -->
        <div id="renameModal" class="modal">
            <div class="modal-content">
                <h3>Renombrar</h3>
                <form method="post" id="renameForm">
                    <input type="hidden" name="old_name" id="renameOldName">
                    <input type="text" name="new_name" placeholder="Nuevo nombre" required style="width:100%;padding:8px;margin:10px 0;">
                    <div style="display:flex;gap:10px;">
                        <button type="submit" name="rename" class="btn btn-primary">Renombrar</button>
                        <button type="button" onclick="document.getElementById('renameModal').style.display='none'" class="btn btn-danger">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal para mover -->
        <div id="moveModal" class="modal">
            <div class="modal-content">
                <h3>Mover a:</h3>
                <div class="folder-tabs" id="folderTabs">
                    <!-- Las pestañas de carpetas se cargarán aquí con JavaScript -->
                </div>
                <form method="post" id="moveForm">
                    <input type="hidden" name="item_name" id="moveItemName">
                    <input type="hidden" name="target_dir" id="targetDir">
                    <div style="display:flex;gap:10px;margin-top:10px;">
                        <button type="submit" name="move" class="btn btn-primary">Mover</button>
                        <button type="button" onclick="document.getElementById('moveModal').style.display='none'" class="btn btn-danger">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mostrar modal para renombrar
        function showRenameModal(itemName) {
            document.getElementById('renameOldName').value = itemName;
            document.getElementById('renameModal').style.display = 'block';
            document.querySelector('#renameModal input[name="new_name"]').focus();
        }
        
        // Mostrar modal para mover
        function showMoveModal(itemName) {
            document.getElementById('moveItemName').value = itemName;
            document.getElementById('moveModal').style.display = 'block';
            
            // Cargar carpetas disponibles
            fetch('get_folders.php?current=<?php echo urlencode($current_dir); ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('folderTabs').innerHTML = data;
                });
        }
        
        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
        
        // Seleccionar carpeta destino
        function selectFolder(path) {
            document.getElementById('targetDir').value = path;
            
            // Resaltar la pestaña seleccionada
            document.querySelectorAll('.folder-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        // Autoajustar altura del textarea
        function adjustTextareaHeight() {
            const editor = document.getElementById('editor');
            if (editor) {
                editor.style.height = 'auto';
                editor.style.height = (editor.scrollHeight) + 'px';
            }
        }
        
        // Ajustar altura inicial y en cambios
        window.addEventListener('load', adjustTextareaHeight);
        window.addEventListener('resize', adjustTextareaHeight);
    </script>
</body>
</html>