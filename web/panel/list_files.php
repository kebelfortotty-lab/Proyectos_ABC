<?php
if (!file_exists($full_path)) {
    @mkdir($full_path, 0755, true);
}

echo '<div style="margin-bottom:15px; background:#e9e9e9; padding:10px;">';
if ($current_dir !== '') {
    $parent_dir = dirname($current_dir);
    if ($parent_dir == '.') $parent_dir = '';
    echo '📁 <a href="?dir=' . urlencode($parent_dir) . '">Volver</a>';
} else {
    echo '📁 <strong>Raíz de archivos</strong>';
}
echo '</div>';

if (is_dir($full_path)) {
    $items = @scandir($full_path);
    if ($items !== false) {
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $item_path = $full_path . '/' . $item;
            $is_dir = is_dir($item_path);
            $icon = $is_dir ? '📁' : '📄';
            $ruta_relativa = ltrim($current_dir . '/' . $item, '/');
            
            echo '<div class="file-item">';
            echo $icon . ' ';
            
            if ($is_dir) {
                echo '<a href="?dir=' . urlencode($ruta_relativa) . '">' . htmlspecialchars($item) . '</a>';
            } else {
                echo '<a href="' . htmlspecialchars($root_dir . $ruta_relativa) . '" download>' . htmlspecialchars($item) . '</a>';
            }
            
            echo '<div style="float:right;" class="file-actions">';
            echo '<small style="color:#666;">(' . ($is_dir ? 'Carpeta' : format_size(filesize($item_path))) . ')</small> ';
            
            // Eliminar
            echo '<a href="?dir=' . urlencode($current_dir) . '&delete=' . urlencode($item) . '" onclick="return confirm(\'¿Eliminar?\')">🗑️</a> ';
            
            // Mover
            echo '<a href="#" onclick="showMoveModal(\'' . htmlspecialchars($item) . '\')">🚚</a> ';
            
            // Renombrar
            echo '<form method="post" style="display:inline;">
                    <input type="hidden" name="old_name" value="' . htmlspecialchars($item) . '">
                    <input type="text" name="new_name" placeholder="Nuevo nombre" size="12" required>
                    <button type="submit" name="rename">✏️</button>
                  </form> ';
            
            // Editar (solo archivos de texto)
            $extensiones_editables = array('txt', 'php', 'html', 'js', 'css');
            if (!$is_dir && in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), $extensiones_editables)) {
                echo '<a href="edit.php?file=' . urlencode($ruta_relativa) . '">✍️</a>';
            }
            
            echo '</div>';
            echo '<div style="clear:both;"></div>';
            echo '</div>';
        }
    }
}
?>