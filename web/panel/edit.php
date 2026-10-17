<?php
session_start();
$root_dir = '../archivos/';
$file_path = isset($_GET['file']) ? $root_dir . str_replace(array('../', '..\\'), '', $_GET['file']) : '';

if (isset($_POST['save']) && $file_path) {
    if (file_put_contents($file_path, $_POST['content']) !== false) {
        $_SESSION['message'] = "✅ Archivo guardado correctamente";
    } else {
        $_SESSION['message'] = "❌ Error al guardar el archivo";
    }
    $current_dir = dirname(str_replace($root_dir, '', $file_path));
    header("Location: index.php?dir=" . urlencode($current_dir));
    exit();
}

$content = '';
if ($file_path && file_exists($file_path)) {
    $content = htmlspecialchars(file_get_contents($file_path));
    $current_dir = dirname(str_replace($root_dir, '', $file_path));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editor - <?php echo htmlspecialchars(basename($file_path)); ?></title>
    <style>
        body { font-family: Arial; margin: 20px; }
        textarea { width: 100%; height: 400px; font-family: Consolas, monospace; }
        .editor-actions { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>✏️ Editando: <?php echo htmlspecialchars(basename($file_path)); ?></h1>
    <?php if (isset($_SESSION['message'])): ?>
        <div style="color: <?php echo strpos($_SESSION['message'], '✅') !== false ? 'green' : 'red'; ?>">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <textarea name="content"><?php echo $content; ?></textarea>
        <div class="editor-actions">
            <button type="submit" name="save">💾 Guardar</button>
            <a href="index.php?dir=<?php echo urlencode($current_dir); ?>">← Volver</a>
        </div>
    </form>
</body>
</html>