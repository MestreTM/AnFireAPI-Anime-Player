<?php
/*********************************************************************
 *                       AnFire API Tester                           *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 *********************************************************************/
header('Content-Type: text/html; charset=utf-8');

// Defina a chave para enviar junto à chamada da API
define('API_KEY', 'Minha_API_Key');

// (Opcional) Protege o Tester com senha
$requirePassword = false;
$password = '12345'; 

if ($requirePassword) {
    if (!isset($_COOKIE['auth']) || $_COOKIE['auth'] !== hash('sha256', $password)) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            if ($_POST['password'] === $password) {
                setcookie('auth', hash('sha256', $password), time() + 3600, '/');
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $error = 'Senha incorreta!';
            }
        }
        echo '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnFireAPI Tester</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form class="password-form" method="POST">
        <h1>AnFire Tester</h1>
        <h2>Digite a senha</h2>';
        if (isset($error)) {
            echo '<div class="error">' . $error . '</div>';
        }
        echo '<input type="password" name="password" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>';
        exit;
    }
}

// Se o GET tiver anime_slug ou anime_link, chama a API com a chave
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['anime_slug']) || isset($_GET['anime_link']))) {
    $animeParam = isset($_GET['anime_slug'])
        ? 'anime_slug=' . urlencode($_GET['anime_slug'])
        : 'anime_link=' . urlencode($_GET['anime_link']);
// ALTERE SUA HOST AQUI
//
    $apiUrl = "https://seusite.com/api.php?api_key=" . urlencode(API_KEY) . "&" . $animeParam;
//
//
    $apiResponse = file_get_contents($apiUrl);
    header('Content-Type: application/json');
    echo $apiResponse;
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnFireAPI Tester</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="title">AnFireAPI Tester</div>
    <form class="form" id="search-form">
        <label for="anime-input">Digite o Slug ou Link do Anime:</label>
        <input type="text" id="anime-input" name="anime" placeholder="Ex: spy-x-family-season-2-dublado ou link" required>
        <button type="submit">Buscar</button>
    </form>

    <button id="view-json" style="display: none; background-color: #FFA500; border: none; padding: 10px 20px; border-radius: 5px; color: #ffffff; cursor: pointer; margin: 10px auto;">Ver JSON Cru</button>

    <div class="loading" id="loading">Carregando...</div>

    <div class="container" id="result-container">
        <label for="quality-select">Selecione a qualidade:</label>
        <select id="quality-select">
            <option value="">Selecione uma qualidade...</option>
        </select>
        <select id="episode-select">
            <option value="">Selecione um episódio...</option>
        </select>
        <p class="warning">Use as playlists em players como VLC.</p>
        <div class="playlist-buttons">
            <div style="display: flex; gap: 10px;">
                <button id="download-episode" disabled>Baixar Episódio Selecionado</button>
                <button id="generate-single-playlist" disabled>Playlist M3U do Episódio Selecionado</button>
            </div>
            <button id="generate-all-playlist" disabled>Playlist M3U com Todos os Episódios</button>
        </div>
        <div class="player-container">
            <h2>Player Direto</h2>
            <video id="video-player" controls class="video-player" style="display: none;"></video>
            <p class="warning">Pode não funcionar online devido a CORS.</p>
        </div>
    </div>

    <div class="json-modal" id="json-modal" style="display: none;">
        <div class="modal-content">
            <textarea id="json-content" readonly></textarea>
            <button id="close-json-modal">Fechar</button>
        </div>
    </div>

    <footer>
        <a href="https://github.com/seu-repositorio" target="_blank">
            <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo">
            AnFireAPI - ver projeto no GitHub.
        </a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.getElementById('search-form');
            const loading = document.getElementById('loading');
            const resultContainer = document.getElementById('result-container');
            const qualitySelect = document.getElementById('quality-select');
            const episodeSelect = document.getElementById('episode-select');
            const videoPlayer = document.getElementById('video-player');
            const generateAllPlaylistButton = document.getElementById('generate-all-playlist');
            const generateSinglePlaylistButton = document.getElementById('generate-single-playlist');
            const downloadEpisodeButton = document.getElementById('download-episode');
            const viewJsonButton = document.getElementById('view-json');
            const jsonModal = document.getElementById('json-modal');
            const jsonContent = document.getElementById('json-content');
            const closeJsonModalButton = document.getElementById('close-json-modal');

            let episodesData = [];
            let fetchedData = {};
            let selectedQuality = '';

            function updateButtonStates() {
                const qualitySelected = qualitySelect.value !== '';
                const episodeSelected = episodeSelect.value !== '';
                generateAllPlaylistButton.disabled = !qualitySelected;
                generateSinglePlaylistButton.disabled = !episodeSelected;
                downloadEpisodeButton.disabled = !episodeSelected;
                viewJsonButton.disabled = !Object.keys(fetchedData).length;
                generateAllPlaylistButton.classList.toggle('enabled', qualitySelected);
                generateSinglePlaylistButton.classList.toggle('enabled', episodeSelected);
                downloadEpisodeButton.classList.toggle('enabled', episodeSelected);
                viewJsonButton.style.display = Object.keys(fetchedData).length ? 'inline-block' : 'none';
            }

            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const animeInput = document.getElementById('anime-input').value;
                const paramType = animeInput.startsWith('http') ? 'anime_link' : 'anime_slug';
                loading.style.display = 'block';
                resultContainer.style.display = 'none';
                qualitySelect.innerHTML = '<option value="">Selecione uma qualidade...</option>';
                episodeSelect.innerHTML = '<option value="">Selecione um episódio...</option>';
                videoPlayer.style.display = 'none';
                fetchedData = {};
                updateButtonStates();

                fetch(`?${paramType}=${encodeURIComponent(animeInput)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = 'none';
                        fetchedData = data;
                        if (data.episodes) {
                            resultContainer.style.display = 'block';
                            episodesData = data.episodes;
                            const qualities = new Set();
                            data.episodes.forEach(ep => {
                                ep.data.forEach(info => qualities.add(info.resolution));
                            });
                            qualities.forEach(q => {
                                const option = document.createElement('option');
                                option.value = q;
                                option.textContent = q;
                                qualitySelect.appendChild(option);
                            });
                        }
                        updateButtonStates();
                    });
            });

            qualitySelect.addEventListener('change', function () {
                selectedQuality = this.value;
                episodeSelect.innerHTML = '<option value="">Selecione um episódio...</option>';
                videoPlayer.style.display = 'none';
                updateButtonStates();
                if (selectedQuality) {
                    episodesData.forEach(ep => {
                        const filtered = ep.data.filter(i => i.resolution === selectedQuality);
                        if (filtered.length > 0) {
                            const option = document.createElement('option');
                            option.value = JSON.stringify(filtered);
                            option.textContent = `Episódio ${ep.episode}`;
                            episodeSelect.appendChild(option);
                        }
                    });
                }
            });

            episodeSelect.addEventListener('change', function () {
                const selectedData = JSON.parse(this.value || '[]');
                if (selectedData.length > 0) {
                    videoPlayer.src = selectedData[0].url;
                    videoPlayer.style.display = 'block';
                } else {
                    videoPlayer.style.display = 'none';
                }
                updateButtonStates();
            });

            generateAllPlaylistButton.addEventListener('click', function () {
                const playlist = episodesData
                    .flatMap(ep => ep.data.filter(i => i.resolution === selectedQuality))
                    .map(i => i.url)
                    .join('\n');
                const blob = new Blob([playlist], { type: 'text/plain' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'playlist_all.m3u';
                link.click();
            });

            generateSinglePlaylistButton.addEventListener('click', function () {
                const selectedData = JSON.parse(episodeSelect.value || '[]');
                const playlist = selectedData.map(i => i.url).join('\n');
                const blob = new Blob([playlist], { type: 'text/plain' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'playlist_single.m3u';
                link.click();
            });

            downloadEpisodeButton.addEventListener('click', function () {
                const selectedData = JSON.parse(episodeSelect.value || '[]');
                if (selectedData.length > 0) {
                    const link = document.createElement('a');
                    link.href = selectedData[0].url;
                    link.download = `episodio_${selectedQuality}.mp4`;
                    link.click();
                }
            });

            viewJsonButton.addEventListener('click', function () {
                jsonContent.value = JSON.stringify(fetchedData, null, 2);
                jsonModal.style.display = 'flex';
            });

            closeJsonModalButton.addEventListener('click', function () {
                jsonModal.style.display = 'none';
            });
        });
    </script>
</body>
</html>
