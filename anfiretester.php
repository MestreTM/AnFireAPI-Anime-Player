<?php
/*********************************************************************
 *                       AnFire API Tester                           *
 * ----------------------------------------------------------------- *
 * Nome do Projeto: API de Verificação de Episódios de Animes        *
 * Versão: 1.0                                                       *
 * Desenvolvedor: MestreTM                                           *
 * Data de Criação: 28/12/2024                                       *
 * ----------------------------------------------------------------- *
 * FUNÇÃO:                                                           *
 * Arquivo basico em PHP para fazer requisições testes a api.php.    *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 * ----------------------------------------------------------------- *
 *                            MESTRETM                               *
 *********************************************************************/
// 
// Sistema simples de senha (opcional)
// Mude para TRUE e altere a senha para utilizar.
//
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnFireAPI Tester</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .password-form {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
            width: 300px;
        }
        .password-form input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2c2c2c;
            color: #ffffff;
        }
        .password-form button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
            font-size: 16px;
        }
        .password-form button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #ff0000;
            margin-bottom: 15px;
        }
    </style>
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
//
// Configuração domínio WEB
//
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['anime_slug']) || isset($_GET['anime_link']))) {
    $animeParam = isset($_GET['anime_slug']) ? "anime_slug=" . urlencode($_GET['anime_slug']) : "anime_link=" . urlencode($_GET['anime_link']);
//
// Altere aqui para seu domínio web ou localhost.
//
	$apiUrl = "http://localhost/api.php?$animeParam";
    $apiResponse = file_get_contents($apiUrl);
    header('Content-Type: application/json');
    echo $apiResponse;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnFireAPI Tester</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form {
            width: 90%;
            max-width: 600px;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }
        .form input[type="text"] {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2c2c2c;
            color: #ffffff;
            font-size: 16px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.5);
        }
        .form input[type="text"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px #007bff;
        }
        .form button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }
        .form button:hover {
            background-color: #0056b3;
        }
        .container {
            width: 90%;
            max-width: 600px;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
            display: none;
        }
        .loading {
            display: none;
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .result {
            margin-top: 20px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: #2c2c2c;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            display: none;
        }
        .quality-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .quality-buttons button {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }
        .quality-buttons button.selected {
            background-color: #0056b3;
        }
        .json-button {
            display: none;
            margin: 20px auto;
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .json-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
        }
        .json-modal .content {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 8px;
            max-height: 80%;
            overflow-y: auto;
            width: 80%;
            max-width: 600px;
        }
        .json-modal .content textarea {
            width: 100%;
            height: 300px;
            font-family: monospace;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            resize: none;
            background: #2c2c2c;
            color: #ffffff;
        }
        .json-modal .close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
        }
        video {
            width: 100%;
            margin-top: 20px;
            display: none;
        }
        footer {
            margin-top: auto;
            text-align: center;
            padding: 20px;
            background: #1e1e1e;
            color: #ffffff;
            font-size: 14px;
        }
        footer a {
            color: #007bff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        footer a:hover {
            text-decoration: underline;
        }
        footer img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="title">AnFireAPI Tester</div>
    <form class="form" id="search-form">
        <label for="anime-input">Digite o Slug ou Link do Anime:</label>
        <input type="text" id="anime-input" name="anime" placeholder="Ex: spy-x-family-season-2-dublado ou link" required>
        <button type="submit">Buscar</button>
    </form>

    <div class="loading" id="loading">Carregando...</div>

    <div class="container" id="result-container">
        <div class="quality-buttons" id="quality-buttons"></div>
        <select id="episode-select">
            <option value="">Selecionar...</option>
        </select>
        <video id="video-player" controls></video>
    </div>

    <button class="json-button" id="json-button">Ver JSON Cru</button>

    <div class="json-modal" id="json-modal">
        <div class="close" id="close-modal">&times;</div>
        <div class="content">
            <textarea id="json-content" readonly></textarea>
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
            const qualityButtons = document.getElementById('quality-buttons');
            const episodeSelect = document.getElementById('episode-select');
            const videoPlayer = document.getElementById('video-player');
            const jsonButton = document.getElementById('json-button');
            const jsonModal = document.getElementById('json-modal');
            const jsonContent = document.getElementById('json-content');
            const closeModal = document.getElementById('close-modal');

            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const animeInput = document.getElementById('anime-input').value;
                const paramType = animeInput.startsWith('http') ? 'anime_link' : 'anime_slug';

                loading.style.display = 'block';
                resultContainer.style.display = 'none';
                qualityButtons.innerHTML = '';
                episodeSelect.innerHTML = '<option value="">Selecionar...</option>';
                episodeSelect.style.display = 'none';
                videoPlayer.style.display = 'none';
                videoPlayer.src = '';
                jsonButton.style.display = 'none';

                fetch(`?${paramType}=${encodeURIComponent(animeInput)}`)
                    .then(response => response.json())
                    .then(data => {
                        loading.style.display = 'none';

                        if (data.episodes) {
                            resultContainer.style.display = 'block';
                            jsonButton.style.display = 'block';
                            jsonContent.value = JSON.stringify(data, null, 2);

                            const resolutions = new Set();
                            data.episodes.forEach(episode => {
                                episode.data.forEach(info => {
                                    resolutions.add(info.resolution);
                                });
                            });

                            resolutions.forEach(resolution => {
                                const button = document.createElement('button');
                                button.textContent = resolution;
                                button.addEventListener('click', function () {
                                    document.querySelectorAll('.quality-buttons button').forEach(btn => btn.classList.remove('selected'));
                                    button.classList.add('selected');
                                    episodeSelect.innerHTML = '<option value="">Selecionar...</option>';
                                    episodeSelect.style.display = 'block';
                                    data.episodes.forEach(episode => {
                                        const filteredData = episode.data.filter(info => info.resolution === resolution);
                                        if (filteredData.length > 0) {
                                            const option = document.createElement('option');
                                            option.value = filteredData[0].url;
                                            option.textContent = `Episódio ${episode.episode}`;
                                            episodeSelect.appendChild(option);
                                        }
                                    });
                                });
                                qualityButtons.appendChild(button);
                            });
                        }
                    });
            });

            episodeSelect.addEventListener('change', function () {
                const selectedUrl = this.value;
                if (selectedUrl) {
                    videoPlayer.style.display = 'block';
                    videoPlayer.src = selectedUrl;
                } else {
                    videoPlayer.style.display = 'none';
                    videoPlayer.src = '';
                }
            });

            jsonButton.addEventListener('click', function () {
                jsonModal.style.display = 'flex';
            });

            closeModal.addEventListener('click', function () {
                jsonModal.style.display = 'none';
            });

            jsonModal.addEventListener('click', function (e) {
                if (e.target === jsonModal) {
                    jsonModal.style.display = 'none';
                }
            });

            jsonContent.addEventListener('click', function () {
                this.select();
            });
        });
    </script>
</body>
</html>
