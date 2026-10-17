<?php
session_start();

// Subir archivo
if (isset($_POST['upload'])) {
    $target_file = $full_path . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $_SESSION['message'] = "✅ Archivo subido correctamente";
    } else {
        $_SESSION['message'] = "❌ Error al subir el archivo";
    }
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}

// Crear carpeta
if (isset($_POST['create_folder'])) {
    $new_folder = $full_path . '/' . safe_path($_POST['new_folder']);
    if (!file_exists($new_folder)) {
        mkdir($new_folder, 0755);
        $_SESSION['message'] = "✅ Carpeta creada correctamente";
    } else {
        $_SESSION['message'] = "❌ La carpeta ya existe";
    }
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}

// Eliminar
if (isset($_GET['delete'])) {
    $item = $full_path . '/' . safe_path($_GET['delete']);
    if (is_dir($item)) {
        @rmdir($item);
    } else {
        @unlink($item);
    }
    $_SESSION['message'] = "✅ Elemento eliminado";
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}

// Renombrar
if (isset($_POST['rename'])) {
    $old_name = $full_path . '/' . safe_path($_POST['old_name']);
    $new_name = $full_path . '/' . safe_path($_POST['new_name']);
    if (!file_exists($new_name)) {
        rename($old_name, $new_name);
        $_SESSION['message'] = "✅ Renombrado correctamente";
    } else {
        $_SESSION['message'] = "❌ Ya existe un elemento con ese nombre";
    }
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}

// Mover
if (isset($_POST['move_item'])) {
    $item_to_move = $full_path . '/' . safe_path($_POST['item_to_move']);
    $destination = $root_dir . safe_path($_POST['move_destination']);
    $new_location = rtrim($destination, '/') . '/' . basename($item_to_move);
    
    if (!file_exists($new_location)) {
        if (rename($item_to_move, $new_location)) {
            $_SESSION['message'] = "✅ Movido correctamente a: " . htmlspecialchars($destination);
        } else {
            $_SESSION['message'] = "❌ Error al mover el archivo";
        }
    } else {
        $_SESSION['message'] = "❌ Ya existe un elemento con ese nombre en el destino";
    }
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}
?>