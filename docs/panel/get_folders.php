<?php
$root_dir = '../archivos/';
$base_dir = isset($_GET['dir']) ? str_replace(array('../', '..\\'), '', $_GET['dir']) : '';

function listFolders($path, $base = '', $indent = 0) {
    $html = '';
    $items = @scandir($path);
    if ($items) {
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $full_path = $path . '/' . $item;
            if (is_dir($full_path)) {
                $relative_path = ltrim($base . '/' . $item, '/');
                $html .= '<div class="folder-tab" onclick="selectFolder(\'' . $relative_path . '/\')">';
                $html .= str_repeat('&nbsp;&nbsp;', $indent) . '📁 ' . $item . '</div>';
                $html .= listFolders($full_path, $relative_path, $indent + 1);
            }
        }
    }
    return $html;
}

header('Content-Type: text/html');
echo listFolders($root_dir . $base_dir, $base_dir);
?>