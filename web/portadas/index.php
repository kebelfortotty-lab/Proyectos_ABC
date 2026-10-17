<?php
session_start();

// Configuración básica
$notas_dir = dirname(__FILE__) . '/notas/';
$portadas = array('1', '2', '3', '4');
$portada_actual = isset($_GET['portada']) && in_array($_GET['portada'], $portadas) ? $_GET['portada'] : '1';

// Manejar cambio de tema
if (isset($_GET['tema'])) {
    $_SESSION['tema'] = $_GET['tema'] == 'oscuro' ? 'oscuro' : 'claro';
    header("Location: index.php?portada=" . $portada_actual);
    exit();
}

// Tema actual (predeterminado: claro)
$tema_actual = isset($_SESSION['tema']) ? $_SESSION['tema'] : 'claro';

// Crear carpeta de notas si no existe
if (!file_exists($notas_dir)) {
    @mkdir($notas_dir, 0755, true);
}

// Procesar guardado de notas
if (isset($_POST['guardar_nota'])) {
    $comentario = trim($_POST['comentario']);
    if (!empty($comentario)) {
        $archivo = $notas_dir . 'portada_' . $portada_actual . '.txt';
        @file_put_contents($archivo, $comentario . "\n", FILE_APPEND);
        $_SESSION['mensaje'] = 'Comentario agregado';
    }
    header("Location: index.php?portada=" . $portada_actual);
    exit();
}

// Procesar eliminación de nota
if (isset($_GET['eliminar'])) {
    $linea = (int)$_GET['eliminar'];
    $archivo = $notas_dir . 'portada_' . $portada_actual . '.txt';
    if (file_exists($archivo)) {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (isset($lineas[$linea])) {
            unset($lineas[$linea]);
            @file_put_contents($archivo, implode("\n", $lineas));
            $_SESSION['mensaje'] = 'Comentario eliminado';
        }
    }
    header("Location: index.php?portada=" . $portada_actual);
    exit();
}

// Procesar reordenamiento
if (isset($_GET['mover'])) {
    list($direccion, $indice) = explode('-', $_GET['mover']);
    $archivo = $notas_dir . 'portada_' . $portada_actual . '.txt';
    if (file_exists($archivo)) {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $indice = (int)$indice;
        
        if ($direccion == 'arriba' && $indice > 0) {
            $temp = $lineas[$indice-1];
            $lineas[$indice-1] = $lineas[$indice];
            $lineas[$indice] = $temp;
        } elseif ($direccion == 'abajo' && $indice < count($lineas)-1) {
            $temp = $lineas[$indice+1];
            $lineas[$indice+1] = $lineas[$indice];
            $lineas[$indice] = $temp;
        }
        
        @file_put_contents($archivo, implode("\n", $lineas));
    }
    header("Location: index.php?portada=" . $portada_actual);
    exit();
}

// Cargar notas existentes
$notas = array();
$archivo_notas = $notas_dir . 'portada_' . $portada_actual . '.txt';
if (file_exists($archivo_notas)) {
    $notas = file($archivo_notas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Álbum Digital</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* Estilos base */
        body {
            line-height: 1.6;
        }
        
        /* Tema claro */
        body.tema-claro {
            background: #f5f5f5;
            color: #333;
        }
        
        .tema-claro .navbar {
            background: #2c3e50;
        }
        
        .tema-claro .portada-title {
            color: #2c3e50;
        }
        
        .tema-claro .comentarios-container {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .tema-claro .comentario:nth-child(odd) {
            background-color: #f9f9f9;
        }
        
        .tema-claro .comentario {
            border-bottom: 1px solid #eee;
        }
        
        .tema-claro .comentario-input {
            border: 1px solid #ddd;
        }
        
        .tema-claro .mensaje {
            background: #d4edda;
            color: #155724;
        }
        
        /* Tema oscuro */
        body.tema-oscuro {
            background: #121212;
            color: #e0e0e0;
        }
        
        .tema-oscuro .navbar {
            background: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        
        .tema-oscuro .portada-title {
            color: #bb86fc;
        }
        
        .tema-oscuro .comentarios-container {
            background: #1e1e1e;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        
        .tema-oscuro .comentario:nth-child(odd) {
            background-color: #252525;
        }
        
        .tema-oscuro .comentario {
            border-bottom: 1px solid #333;
        }
        
        .tema-oscuro .comentario-input {
            background: #2d2d2d;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        
        .tema-oscuro .mensaje {
            background: #004d40;
            color: #b2dfdb;
        }
        
        .tema-oscuro .portada-btn,
        .tema-oscuro .btn {
            background: #bb86fc;
        }
        
        .tema-oscuro .mover-btn {
            background: #03dac6;
        }
        
        .tema-oscuro .eliminar-btn {
            background: #cf6679;
        }
        
        /* Estilos comunes */
        .navbar {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .portada-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .portada-img {
            max-width: 100%;
            max-height: 400px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            object-fit: contain;
        }
        
        .portada-title {
            font-size: 1.5rem;
            margin: 10px 0;
        }
        
        .portada-controls {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .portada-btn {
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .portada-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .comentarios-container {
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .comentario {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            word-break: break-word;
        }
        
        .comentario:last-child {
            border-bottom: none;
        }
        
        .comentario-controles {
            display: flex;
            gap: 5px;
        }
        
        .mover-btn {
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 12px;
        }
        
        .eliminar-btn {
            color: white;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
        }
        
        .nuevo-comentario {
            margin-top: 20px;
        }
        
        .comentario-input {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            min-height: 80px;
            resize: vertical;
        }
        
        .btn {
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .tema-btn {
            background: transparent;
            border: 1px solid white;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        /* Scrollbar personalizada */
        .comentarios-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .comentarios-container::-webkit-scrollbar-track {
            border-radius: 4px;
        }
        
        .tema-claro .comentarios-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .tema-oscuro .comentarios-container::-webkit-scrollbar-track {
            background: #2d2d2d;
        }
        
        .comentarios-container::-webkit-scrollbar-thumb {
            border-radius: 4px;
        }
        
        .tema-claro .comentarios-container::-webkit-scrollbar-thumb {
            background: #3498db;
        }
        
        .tema-claro .comentarios-container::-webkit-scrollbar-thumb:hover {
            background: #2980b9;
        }
        
        .tema-oscuro .comentarios-container::-webkit-scrollbar-thumb {
            background: #bb86fc;
        }
        
        .tema-oscuro .comentarios-container::-webkit-scrollbar-thumb:hover {
            background: #9a67ea;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .portada-controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .portada-btn {
                width: 100%;
            }
            
            .comentarios-container {
                max-height: 200px;
            }
            
            .portada-img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body class="tema-<?php echo $tema_actual; ?>">
    <!-- Barra de navegación -->
    <div class="navbar">
        <div>
            <a href="../">🏠 Inicio</a>
            <a href="../panel/index.php">📁 Panel</a>
        </div>
        <div>
            <span style="color: white;">Portada <?php echo $portada_actual; ?></span>
            <button class="tema-btn" onclick="cambiarTema()">
                <?php echo $tema_actual == 'claro' ? '🌙 Oscuro' : '☀️ Claro'; ?>
            </button>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="main-container">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje">
                <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Portada actual -->
        <div class="portada-container">
            <img src="../archivos/portadas/port_<?php echo $portada_actual; ?>.jpg" alt="Portada <?php echo $portada_actual; ?>" class="portada-img">
            
            <div class="portada-title">
                Portada <?php echo $portada_actual; ?>
            </div>
            
            <div class="portada-controls">
                <button class="portada-btn" onclick="cambiarPortada('anterior')" <?php echo ($portada_actual == '1') ? 'disabled' : ''; ?>>
                    ◀ Anterior
                </button>
                <button class="portada-btn" onclick="cambiarPortada('siguiente')" <?php echo ($portada_actual == '4') ? 'disabled' : ''; ?>>
                    Siguiente ▶
                </button>
            </div>
        </div>
        
        <!-- Comentarios existentes -->
        <div class="comentarios-container">
            <h3>Comentarios</h3>
            <?php if (empty($notas)): ?>
                <p>No hay comentarios aún.</p>
            <?php else: ?>
                <?php foreach ($notas as $indice => $texto): ?>
                    <div class="comentario">
                        <span><?php echo htmlspecialchars($texto); ?></span>
                        <div class="comentario-controles">
                            <?php if ($indice > 0): ?>
                                <a href="?portada=<?php echo $portada_actual; ?>&mover=arriba-<?php echo $indice; ?>" class="mover-btn" title="Mover arriba">↑</a>
                            <?php endif; ?>
                            <?php if ($indice < count($notas)-1): ?>
                                <a href="?portada=<?php echo $portada_actual; ?>&mover=abajo-<?php echo $indice; ?>" class="mover-btn" title="Mover abajo">↓</a>
                            <?php endif; ?>
                            <a href="?portada=<?php echo $portada_actual; ?>&eliminar=<?php echo $indice; ?>" class="eliminar-btn" title="Eliminar">×</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Formulario para nuevo comentario -->
        <div class="nuevo-comentario">
            <form method="post">
                <textarea name="comentario" class="comentario-input" placeholder="Escribe tu comentario aquí..." required></textarea>
                <button type="submit" name="guardar_nota" class="btn">Agregar Comentario</button>
            </form>
        </div>
    </div>
    
    <script>
    function cambiarPortada(direccion) {
        var portadas = ['1', '2', '3', '4'];
        var actual = '<?php echo $portada_actual; ?>';
        var indexActual = portadas.indexOf(actual);
        var nuevaPortada;
        
        if (direccion === 'anterior' && indexActual > 0) {
            nuevaPortada = portadas[indexActual - 1];
        } else if (direccion === 'siguiente' && indexActual < portadas.length - 1) {
            nuevaPortada = portadas[indexActual + 1];
        } else {
            return;
        }
        
        window.location.href = 'index.php?portada=' + nuevaPortada;
    }
    
    function cambiarTema() {
        var temaActual = '<?php echo $tema_actual; ?>';
        var nuevoTema = temaActual === 'claro' ? 'oscuro' : 'claro';
        window.location.href = 'index.php?portada=<?php echo $portada_actual; ?>&tema=' + nuevoTema;
    }
    
    // Navegación con teclado
    document.onkeydown = function(e) {
        e = e || window.event;
        if (e.keyCode == 37) cambiarPortada('anterior');
        if (e.keyCode == 39) cambiarPortada('siguiente');
    };
    </script>
</body>
</html>