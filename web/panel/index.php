<?php
session_start();
$root_dir = '../archivos/';
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '';

function safe_path($dir) {
    return str_replace(array('../', '..\\'), '', $dir);
}

$current_dir = safe_path($current_dir);
$full_path = $root_dir . $current_dir;

// Incluimos las acciones
include 'actions.php';

function format_size($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    elseif ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    elseif ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    else return $bytes . ' bytes';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Archivos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .navbar {
            background: #333;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
        }
        .file-list { border: 1px solid #ddd; padding: 10px; margin: 0 15px; }
        .file-item { padding: 8px; border-bottom: 1px solid #eee; }
        .file-item:hover { background: #f5f5f5; }
        .actions { 
            margin: 15px;
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
        }
        .file-actions a, .file-actions button { 
            margin: 0 3px;
            color: #555;
            font-size: 12px;
        }
        #moveModal { 
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .folder-tab {
            padding: 5px;
            margin: 2px 0;
            cursor: pointer;
            font-size: 13px;
        }
        .folder-tab:hover { background: #f0f0f0; }
        .message {
            padding: 10px;
            margin: 10px 15px;
            border-radius: 4px;
        }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <div class="navbar">
        <a href="../">🏠 Inicio</a>
        <a href="index.php">📁 Panel de Archivos</a>
        <a href="../otra-pagina.php">🔗 Otra Página</a>
    </div>

    <!-- Mensajes de éxito/error -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo strpos($_SESSION['message'], '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div style="margin: 0 15px;">
        <h1>📁 Panel de Archivos</h1>
        <p><strong>Ruta actual:</strong> /<?php echo htmlspecialchars($current_dir); ?></p>
        
        <div class="actions">
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <input type="submit" name="upload" value="Subir Archivo">
            </form>
            
            <form method="post" style="margin-top: 10px;">
                <input type="text" name="new_folder" placeholder="Nombre de carpeta" required>
                <input type="submit" name="create_folder" value="Crear Carpeta">
            </form>
        </div>

        <div class="file-list">
            <?php include 'list_files.php'; ?>
        </div>
    </div>

    <!-- Modal para mover archivos -->
    <div id="moveModal">
        <h3>Mover: <span id="movingItemName"></span></h3>
        <form method="post">
            <input type="hidden" name="item_to_move" id="item_to_move">
            <p><strong>Selecciona carpeta destino:</strong></p>
            <div id="folderTabs" style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 5px;"></div>
            <p style="margin-top: 10px;">O ingresa manualmente:</p>
            <input type="text" name="move_destination" id="move_destination" placeholder="ruta/destino/" style="width: 100%; padding: 5px; margin-bottom: 10px;">
            <div style="text-align: right;">
                <button type="button" onclick="hideModal()" style="background: #f44336; color: white; border: none; padding: 5px 10px; cursor: pointer;">Cancelar</button>
                <button type="submit" name="move_item" style="background: #4CAF50; color: white; border: none; padding: 5px 10px; cursor: pointer;">Mover</button>
            </div>
        </form>
    </div>

    <script>
    function showMoveModal(item) {
        document.getElementById('item_to_move').value = item;
        document.getElementById('movingItemName').textContent = item;
        document.getElementById('moveModal').style.display = 'block';
        loadFolderStructure();
    }
    
    function hideModal() {
        document.getElementById('moveModal').style.display = 'none';
    }
    
    function loadFolderStructure() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_folders.php?dir=<?php echo urlencode($current_dir); ?>', true);
        xhr.onload = function() {
            if (this.status == 200) {
                document.getElementById('folderTabs').innerHTML = this.responseText;
            }
        };
        xhr.send();
    }
    
    function selectFolder(path) {
        document.querySelectorAll('.folder-tab').forEach(function(tab) {
            tab.style.background = 'transparent';
        });
        event.target.style.background = '#e0e0e0';
        document.getElementById('move_destination').value = path;
    }
    </script>
</body>
</html>