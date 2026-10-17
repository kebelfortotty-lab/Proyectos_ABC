<?php
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'archivos';

if (!is_dir($folder)) {
    die(json_encode(array()));
}

$files = array();
$dh = opendir($folder);
while (($file = readdir($dh)) !== false) {
    if ($file != '.' && $file != '..') {
        $path = $folder . '/' . $file;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        if (is_dir($path)) {
            $files[] = array(
                'name' => $file,
                'type' => 'folder',
                'path' => $path
            );
        } else {
            // Determinar tipo de archivo
            $type = 'other';
            $audioExts = array('mp3', 'wav', 'ogg', 'aac', 'wma', 'flac');
            $videoExts = array('mp4', 'webm', 'mkv', 'avi', 'mov', 'wmv', 'flv');
            $imageExts = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg');
            
            if (in_array($ext, $audioExts)) $type = 'audio';
            else if (in_array($ext, $videoExts)) $type = 'video';
            else if (in_array($ext, $imageExts)) $type = 'image';
            
            $files[] = array(
                'name' => $file,
                'type' => $type,
                'ext' => $ext,
                'path' => $path
            );
        }
    }
}
closedir($dh);

// Ordenar alfabéticamente
usort($files, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

echo json_encode($files);
?>