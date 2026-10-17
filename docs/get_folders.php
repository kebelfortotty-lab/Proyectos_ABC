<?php
// get_folders.php - Devuelve las carpetas disponibles para mover archivos

$base_dir = 'archivos';
$current = isset($_GET['current']) ? $_GET['current'] : $base_dir;
$current = strpos(realpath($current), realpath($base_dir)) === 0 ? $current : $base_dir;

function getFolders($dir, $base, $current) {
    $folders = array();
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path) && $path !== $current) {
            $relative = substr($path, strlen($base) + 1);
            $folders[] = array(
                'path' => $path,
                'name' => $item,
                'relative' => $relative ? $relative : $item
            );
            
            // Obtener subcarpetas recursivamente
            $subfolders = getFolders($path, $base, $current);
            $folders = array_merge($folders, $subfolders);
        }
    }
    
    return $folders;
}

$folders = getFolders($base_dir, $base_dir, $current);

// Mostrar como pestañas
foreach ($folders as $folder) {
    echo '<div class="folder-tab" onclick="selectFolder(\''.htmlspecialchars($folder['path']).'\')">';
    echo htmlspecialchars($folder['relative']);
    echo '</div>';
}

if (empty($folders)) {
    echo '<p style="color:#666;padding:10px;">No hay otras carpetas disponibles</p>';
}
?>