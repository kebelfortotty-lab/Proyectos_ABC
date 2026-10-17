<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reproductor Multimedia</title>
    <style>
        :root {
            --bg-color: #f0f0f0;
            --text-color: #333;
            --panel-bg: white;
            --border-color: #e0e0e0;
            --primary-color: #4CAF50;
            --navbar-bg: #2c3e50;
            --highlight-bg: #f5f5f5;
            --time-color: #666;
            --note-bg: #f9f9f9;
            --note-border: #4CAF50;
            --favorite-bg: #ffbb33;
            --favorite-hover: #ff8800;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }

        .dark-mode {
            --bg-color: #121212;
            --text-color: #f1f1f1;
            --panel-bg: #1e1e1e;
            --border-color: #444;
            --primary-color: #81c784;
            --navbar-bg: #1a1a1a;
            --highlight-bg: #333;
            --time-color: #aaa;
            --note-bg: #252525;
            --note-border: #81c784;
            --favorite-bg: #ffaa00;
            --favorite-hover: #ff9500;
            --danger-color: #ff4444;
            --warning-color: #ffab00;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        body {
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            font-size: 13px;
        }

        .nav-menu {
            background: var(--navbar-bg);
            padding: 10px 15px;
            display: flex;
            gap: 10px;
            position: sticky;
            top: 0;
            z-index: 100;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 4px;
            white-space: nowrap;
        }

        .nav-menu a:hover, .nav-menu a.active {
            background: #34495e;
        }

        .theme-toggle {
            background: #555;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: auto;
            font-weight: bold;
            white-space: nowrap;
        }

        .player-container {
            max-width: 100%;
            margin: 0 auto;
            background: var(--panel-bg);
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .browser-header {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        select, input {
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: var(--panel-bg);
            color: var(--text-color);
        }

        .folder-nav {
            display: flex;
            gap: 5px;
            margin-bottom: 8px;
            flex-wrap: wrap;
            font-size: 12px;
        }

        .folder-nav span {
            cursor: pointer;
            color: var(--primary-color);
        }

        .folder-nav span:hover {
            text-decoration: underline;
        }

        .folder-path {
            color: var(--time-color);
            margin-bottom: 10px;
            font-size: 12px;
        }

        .file-list {
            border: 1px solid var(--border-color);
            border-radius: 4px;
            height: 300px;
            overflow-y: auto;
        }

        .file-item {
            padding: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            min-height: 44px;
        }

        .file-item:hover {
            background: var(--highlight-bg);
        }

        .file-icon {
            margin-right: 10px;
            width: 24px;
            text-align: center;
            font-size: 16px;
        }

        .file-name {
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-actions {
            margin-left: 10px;
            color: var(--time-color);
        }

        .file-actions span {
            margin-left: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .file-actions span:hover {
            color: var(--primary-color);
        }

        #mediaContainer {
            width: 100%;
            text-align: center;
            background: #000;
            border-radius: 4px;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #mediaPlayer {
            max-width: 100%;
            max-height: 100%;
        }

        .controls {
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        button {
            padding: 10px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            min-height: 44px;
        }

        button:hover {
            opacity: 0.9;
        }

        .favorites-btn {
            background: var(--favorite-bg);
        }

        .favorites-btn:hover {
            background: var(--favorite-hover);
        }

        #currentTime {
            color: var(--time-color);
            font-size: 14px;
        }

        .notes-section {
            margin-top: 15px;
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
        }

        .note {
            margin: 10px 0;
            padding: 10px;
            background: var(--note-bg);
            border-left: 3px solid var(--note-border);
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            border-radius: 0 4px 4px 0;
        }

        .note-text {
            flex-grow: 1;
        }

        .note-time {
            color: var(--primary-color);
            cursor: pointer;
            margin-right: 10px;
            white-space: nowrap;
        }

        .note-delete {
            color: var(--danger-color);
            cursor: pointer;
            margin-left: 10px;
            font-weight: bold;
        }

        .mover-btn {
            background: var(--warning-color);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 14px;
        }

        .eliminar-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
        }

        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
        }

        .dark-mode .mensaje {
            background: #2d4d2d;
            color: #d4edda;
        }

        /* Scrollbar personalizada */
        .file-list::-webkit-scrollbar,
        .comentarios-container::-webkit-scrollbar {
            width: 8px;
        }

        .file-list::-webkit-scrollbar-track,
        .comentarios-container::-webkit-scrollbar-track {
            background: var(--bg-color);
            border-radius: 4px;
        }

        .file-list::-webkit-scrollbar-thumb,
        .comentarios-container::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        /* Responsive para tablets (iPad) */
        @media (min-width: 768px) {
            .player-container {
                flex-direction: row;
                padding: 20px;
            }
            
            .left-panel {
                width: 35%;
                min-width: 300px;
            }
            
            .file-list {
                height: 500px;
            }
            
            #mediaContainer {
                min-height: 350px;
            }
        }

        /* Responsive para pantallas grandes */
        @media (min-width: 1024px) {
            .player-container {
                max-width: 1200px;
            }
            
            #mediaContainer {
                min-height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <div class="nav-menu">
        <div>
            <a href="index.php" class="active">Reproductor</a>
			<a href="portadas/index.php">Portadas</a>
            <a href="panel/index.php">Admin archivos</a>
            <a href="calendario.php">Calendario</a>
            <a href="flashcards.php">Flashcards</a>
        </div>
        <button class="theme-toggle" onclick="toggleDarkMode()">
            🌙 Tema Oscuro
        </button>
    </div>
    
    <!-- Contenido principal -->
    <div class="player-container">
        <!-- Panel izquierdo (explorador de archivos) -->
        <div class="left-panel">
            <h2 style="margin-bottom: 10px; font-size: 16px;">Explorador de Archivos</h2>
            
            <!-- Navegación y filtros -->
            <div class="browser-header">
                <select id="filterType" onchange="loadFiles()" style="width: 120px;">
                    <option value="all">Todos</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audios</option>
                    <option value="image">Imágenes</option>
                    <option value="favorite">Favoritos</option>
                </select>
                <input type="text" id="searchInput" placeholder="Buscar archivos..." oninput="loadFiles()" style="flex-grow: 1;">
            </div>
            
            <!-- Navegación por carpetas -->
            <div class="folder-nav" id="folderNav">
                <span onclick="changeFolder('archivos')">Inicio</span>
            </div>
            <div class="folder-path" id="folderPath">archivos</div>
            
            <!-- Lista de archivos -->
            <div class="file-list" id="fileList"></div>
        </div>
        
        <!-- Panel derecho (reproductor y notas) -->
        <div class="right-panel">
            <h2 style="margin-bottom: 10px; font-size: 16px;">Reproductor</h2>
            
            <!-- Reproductor -->
            <div id="mediaContainer">
                <div style="color: #999;">Selecciona un archivo para reproducir</div>
            </div>
            
            <!-- Controles -->
            <div class="controls">
                <button onclick="toggleRepeat()" id="repeatBtn">🔁 Repetir: OFF</button>
                <button onclick="toggleFavorite()" id="favoriteBtn" class="favorites-btn">⭐ Añadir a Favoritos</button>
                <span id="currentTime">Tiempo: 0:00 / 0:00</span>
            </div>
            
            <div class="media-info" id="nowPlaying"></div>
            
            <!-- Notas para el archivo actual -->
            <div class="notes-section">
                <h3 style="margin-bottom: 8px; font-size: 14px;">Notas</h3>
                <button onclick="addNote()">➕ Añadir Nota</button>
                <div id="notesList"></div>
            </div>
        </div>
    </div>

    <script>
        // Variables
        var currentPlayer = null;
        var currentFile = '';
        var currentFilePath = '';
        var currentFolder = 'archivos';
        var repeat = false;
        var favorites = JSON.parse(localStorage.getItem('mediaFavorites')) || {};
        
        // Cargar preferencia de tema al iniciar
        if (localStorage.getItem('darkMode') === 'on') {
            document.body.classList.add('dark-mode');
            document.querySelector('.theme-toggle').textContent = '☀️ Tema Claro';
        }
        
        // Cambiar tema oscuro/claro
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark ? 'on' : 'off');
            document.querySelector('.theme-toggle').textContent = isDark ? '☀️ Tema Claro' : '🌙 Tema Oscuro';
        }
        
        // Cargar archivos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadFiles();
            updateFavoriteButton();
            adjustForTouch();
        });
        
        // Ajustar para pantallas táctiles
        function adjustForTouch() {
            if ('ontouchstart' in window) {
                // Aumentar áreas clickables en móvil
                const buttons = document.querySelectorAll('button, .file-item');
                buttons.forEach(btn => {
                    btn.style.minHeight = '44px';
                    btn.style.padding = '12px 15px';
                });
            }
        }
        
        // Cambiar de carpeta
        function changeFolder(folder) {
            currentFolder = folder;
            updateFolderNav();
            loadFiles();
        }
        
        // Actualizar navegación de carpetas
        function updateFolderNav() {
            var parts = currentFolder.split('/');
            var nav = document.getElementById('folderNav');
            nav.innerHTML = '<span onclick="changeFolder(\'archivos\')">Inicio</span>';
            
            var path = 'archivos';
            for (var i = 1; i < parts.length; i++) {
                path += '/' + parts[i];
                nav.innerHTML += ' / <span onclick="changeFolder(\'' + path + '\')">' + parts[i] + '</span>';
            }
            
            document.getElementById('folderPath').textContent = currentFolder;
        }
        
        // Cargar archivos desde el servidor
        function loadFiles() {
            var filterType = document.getElementById('filterType').value;
            var searchText = document.getElementById('searchInput').value.toLowerCase();
            
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_files.php?folder=' + encodeURIComponent(currentFolder), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var files = JSON.parse(xhr.responseText);
                    displayFiles(files, filterType, searchText);
                }
            };
            xhr.send();
        }
        
        // Mostrar archivos en la lista
        function displayFiles(files, filterType, searchText) {
            var fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            
            // Ordenar: carpetas primero, luego archivos
            files.sort(function(a, b) {
                if (a.type === 'folder' && b.type !== 'folder') return -1;
                if (a.type !== 'folder' && b.type === 'folder') return 1;
                return a.name.localeCompare(b.name);
            });
            
            files.forEach(function(file) {
                var isFavorite = favorites[file.path] === true;
                var showAsFavorite = filterType === 'favorite' && isFavorite;
                
                var typeMatch = filterType === 'all' || 
                               (filterType === 'video' && file.type === 'video') ||
                               (filterType === 'audio' && file.type === 'audio') ||
                               (filterType === 'image' && file.type === 'image') ||
                               (filterType === 'all' && file.type === 'folder') ||
                               showAsFavorite;
                
                var nameMatch = file.name.toLowerCase().includes(searchText);
                
                if (typeMatch && nameMatch) {
                    var item = document.createElement('div');
                    item.className = 'file-item';
                    
                    var icon = file.type === 'folder' ? '📁' : getFileIcon(file.ext);
                    var star = isFavorite ? '★' : '☆';
                    
                    item.innerHTML = `
                        <div class="file-icon">${icon}</div>
                        <div class="file-name" title="${file.name}">${file.name}</div>
                        <div class="file-actions">
                            <span onclick="event.stopPropagation(); toggleFileFavorite('${file.path}')">${star}</span>
                        </div>
                    `;
                    
                    if (file.type === 'folder') {
                        item.onclick = function() { changeFolder(file.path); };
                    } else {
                        item.onclick = function() { loadFile(file.path, file.ext, false); };
                    }
                    
                    fileList.appendChild(item);
                }
            });
        }
        
        // Obtener icono según extensión
        function getFileIcon(ext) {
            var icons = {
                // Audio
                'mp3': '🎵', 'wav': '🎵', 'ogg': '🎵', 'aac': '🎵', 'wma': '🎵', 'flac': '🎵',
                // Video
                'mp4': '🎬', 'webm': '🎬', 'mkv': '🎬', 'avi': '🎬', 'mov': '🎬', 'wmv': '🎬', 'flv': '🎬',
                // Imágenes
                'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️', 'bmp': '🖼️', 'webp': '🖼️', 'svg': '🖼️'
            };
            return icons[ext] || '📄';
        }
        
        // Cargar archivo (sin autoplay)
        function loadFile(file, ext, autoplay = false) {
            currentFilePath = file;
            currentFile = file.split('/').pop();
            document.getElementById('nowPlaying').textContent = currentFile;
            
            var container = document.getElementById('mediaContainer');
            container.innerHTML = '';
            
            // Tipos de audio
            var audioExts = ['mp3', 'wav', 'ogg', 'aac', 'wma', 'flac'];
            // Tipos de video
            var videoExts = ['mp4', 'webm', 'mkv', 'avi', 'mov', 'wmv', 'flv'];
            // Tipos de imagen
            var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
            
            if (audioExts.includes(ext)) {
                // Audio
                container.innerHTML = '<audio id="mediaPlayer" controls></audio>';
                currentPlayer = document.getElementById('mediaPlayer');
                currentPlayer.src = file;
                if (autoplay) currentPlayer.play();
                setupPlayerEvents();
            } 
            else if (videoExts.includes(ext)) {
                // Video
                container.innerHTML = '<video id="mediaPlayer" controls></video>';
                currentPlayer = document.getElementById('mediaPlayer');
                currentPlayer.src = file;
                if (autoplay) currentPlayer.play();
                setupPlayerEvents();
            }
            else if (imageExts.includes(ext)) {
                // Imagen
                container.innerHTML = '<img id="mediaPlayer" src="'+file+'" style="max-height: 100%;">';
                currentPlayer = null;
                document.getElementById('currentTime').innerHTML = 'Imagen: ' + currentFile;
            }
            
            // Actualizar botón de favoritos
            updateFavoriteButton();
            // Cargar notas para este archivo
            loadNotesForFile();
        }
        
        // Configurar eventos del reproductor
        function setupPlayerEvents() {
            if (!currentPlayer) return;
            
            currentPlayer.addEventListener('timeupdate', updateTime);
            currentPlayer.addEventListener('loadedmetadata', function() {
                updateTime();
                document.getElementById('currentTime').innerHTML = 
                    'Tiempo: ' + formatTime(currentPlayer.currentTime) + ' / ' + formatTime(currentPlayer.duration);
            });
        }
        
        // Actualizar tiempo
        function updateTime() {
            if (!currentPlayer) return;
            document.getElementById('currentTime').innerHTML = 
                'Tiempo: ' + formatTime(currentPlayer.currentTime) + ' / ' + formatTime(currentPlayer.duration);
        }
        
        // Formatear tiempo (MM:SS)
        function formatTime(seconds) {
            var minutes = Math.floor(seconds / 60);
            var secs = Math.floor(seconds % 60);
            return minutes + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        // Convertir tiempo (MM:SS) a segundos
        function parseTime(timeStr) {
            var parts = timeStr.split(':');
            return parseInt(parts[0]) * 60 + parseInt(parts[1]);
        }
        
        // Botón de repetir
        function toggleRepeat() {
            if (!currentPlayer) return;
            repeat = !repeat;
            currentPlayer.loop = repeat;
            document.getElementById('repeatBtn').innerHTML = repeat ? '🔁 Repetir: ON' : '🔁 Repetir: OFF';
        }
        
        // Manejar favoritos
        function toggleFavorite() {
            if (!currentFilePath) return;
            
            favorites[currentFilePath] = !favorites[currentFilePath];
            localStorage.setItem('mediaFavorites', JSON.stringify(favorites));
            updateFavoriteButton();
            loadFiles(); // Actualizar la lista para mostrar los cambios
        }
        
        function toggleFileFavorite(filePath) {
            favorites[filePath] = !favorites[filePath];
            localStorage.setItem('mediaFavorites', JSON.stringify(favorites));
            loadFiles(); // Actualizar la lista
        }
        
        function updateFavoriteButton() {
            var btn = document.getElementById('favoriteBtn');
            if (favorites[currentFilePath]) {
                btn.innerHTML = '★ Quitar de Favoritos';
                btn.style.backgroundColor = 'var(--favorite-hover)';
            } else {
                btn.innerHTML = '⭐ Añadir a Favoritos';
                btn.style.backgroundColor = 'var(--favorite-bg)';
            }
        }
        
        // Cargar notas para el archivo actual
        function loadNotesForFile() {
            if (!currentFilePath) return;
            
            var notesList = document.getElementById('notesList');
            notesList.innerHTML = '';
            
            // Cargar notas desde el servidor
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_notes.php?file=' + encodeURIComponent(currentFile), true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var notes = JSON.parse(xhr.responseText);
                    notes.forEach(function(note, index) {
                        addNoteToUI(note.time, note.text, index);
                    });
                }
            };
            xhr.send();
        }
        
        // Añadir nota
        function addNote() {
            if (!currentPlayer && !currentFilePath.match(/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i)) {
                alert('Primero selecciona un archivo multimedia');
                return;
            }
            
            var noteText = prompt("Escribe tu nota:");
            if (noteText) {
                var timestamp = currentPlayer ? formatTime(currentPlayer.currentTime) : '00:00';
                
                // Guardar nota en el servidor
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'save_note.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        loadNotesForFile(); // Recargar todas las notas
                    }
                };
                xhr.send('file=' + encodeURIComponent(currentFile) + 
                         '&time=' + encodeURIComponent(timestamp) + 
                         '&text=' + encodeURIComponent(noteText));
            }
        }
        
        // Eliminar nota
        function deleteNote(index) {
            if (!confirm('¿Eliminar esta nota?')) return;
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_note.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    loadNotesForFile(); // Recargar todas las notas
                }
            };
            xhr.send('file=' + encodeURIComponent(currentFile) + 
                     '&index=' + encodeURIComponent(index));
        }
        
        // Añadir nota a la interfaz
        function addNoteToUI(time, text, index) {
            var notesList = document.getElementById('notesList');
            var noteDiv = document.createElement('div');
            noteDiv.className = 'note';
            
            noteDiv.innerHTML = `
                <div class="note-text">
                    <span class="note-time" onclick="jumpToTime('${time}')">[${time}]</span>
                    ${text.replace(/</g, '&lt;').replace(/>/g, '&gt;')}
                </div>
                <span class="note-delete" onclick="deleteNote(${index})">✖</span>
            `;
            
            notesList.appendChild(noteDiv);
        }
        
        // Saltar al tiempo de la nota
        function jumpToTime(timeStr) {
            if (!currentPlayer) return;
            var seconds = parseTime(timeStr);
            currentPlayer.currentTime = seconds;
            if (currentPlayer.paused) {
                currentPlayer.play();
            }
        }
    </script>
</body>
</html>