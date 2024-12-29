<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['anime_input'])) {
    $anime_input = $_POST['anime_input'];
    $api_key = 'SUA_API_AQUI';
    $api_url = "https://localhost.com/api.php?api_key=$api_key&anime_link=" . urlencode($anime_input);

    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
}
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Anime Player</title>
        <style>
            body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #121212;
            color: #f1f1f1;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .container {
            max-width: 960px;
            width: 90%;
            margin: 20px;
            padding: 30px;
            background: linear-gradient(145deg, #1a1a1a, #252525);
            border-radius: 20px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5), inset 0 -1px 5px rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            color: #00e6e6;
            text-shadow: 0px 0px 8px rgba(0, 230, 230, 0.7), 0px 0px 15px rgba(0, 140, 140, 0.5);
        }
        
        label {
            display: block;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #cccccc;
        }
        
        input[type="text"], select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.5), inset -2px -2px 5px rgba(255, 255, 255, 0.1);
        }
        
        input::placeholder {
            color: #8e8e8e;
        }
        
        input:focus {
            border-color: #00e6e6;
            outline: none;
            background: rgba(0, 230, 230, 0.1);
            box-shadow: 0 0 10px rgba(0, 230, 230, 0.7);
        }
        
        button {
            width: 45%;
            padding: 12px;
            margin: 5px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            background: linear-gradient(145deg, #007f85, #00ced1);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3), inset 0 -1px 5px rgba(255, 255, 255, 0.1);
            transition: transform 0.2s, box-shadow 0.3s;
        }
        
        button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 230, 230, 0.5), inset 0 -1px 10px rgba(255, 255, 255, 0.2);
        }
        
        button:active {
            transform: scale(0.95);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        
            h1 {
                font-size: 2rem;
            }
        
            input[type="text"], select {
                width: 100%;
            }
        
            button {
                width: 100%;
            }
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
        <div class="container">
            <h1>AnFire Player</h1>
            <form method="post">
                <label for="anime-input">Insira o anime_slug ou anime_link (*-todos-os-episodios):</label>
                <input type="text" id="anime-input" name="anime_input" placeholder="Exemplo: https://animefire.plus/animes/spy-x-family-season-2-dublado-todos-os-episodios">
                <button type="submit">Carregar Episódios</button>
                <script type="text/javascript">
                    const param = window.location.search;
                    const urlpa = new URLSearchParams(param);
                    if (urlpa.has('link')) {
                    const anl = urlpa.get('link');
                    document.getElementById("anime-input").value = anl;
                }
                </script>
            </form>

            <?php if (isset($data) && isset($data['episodes'])): ?>
                <div>
                    <label for="quality">Selecione a qualidade:</label>
                    <select id="quality">
                    </select>
                    <button id="generate-player">▷ Assistir no Player Online</button>
                    </br>
                    </br>
                    <button id="view-api-response">⚙ Ver resposta da API</button>
                    <button id="download-m3u">🗎 Baixar playlist M3U para VLC</button>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const episodes = <?php echo json_encode($data['episodes']); ?>;
                
                        // Obter todas as resoluções disponíveis
                        const resolutions = new Set();
                        episodes.forEach(ep => {
                            ep.data.forEach(resolutionData => {
                                resolutions.add(resolutionData.resolution);
                            });
                        });
                
                        // Preencher o seletor de qualidade
                        const qualitySelect = document.getElementById('quality');
                        resolutions.forEach(resolution => {
                            const option = document.createElement('option');
                            option.value = resolution;
                            option.textContent = resolution;
                            qualitySelect.appendChild(option);
                        });
                
                        // Gerar botões de episódios com base na qualidade selecionada
                        document.getElementById('generate-player').addEventListener('click', function () {
                            const selectedQuality = qualitySelect.value;
                
                            let episodeButtons = '';
                            episodes.forEach(ep => {
                                const resolutionData = ep.data.find(d => d.resolution === selectedQuality);
                                if (resolutionData) {
                                    episodeButtons += `
                                        <button onclick="document.getElementById('player-video').src='${resolutionData.url}'">
                                            Episódio ${ep.episode}
                                        </button>`;
                                }
                            });
                
                            const blobContent = `
                                <!DOCTYPE html>
                                <html lang="en">
                                <head>
                                    <meta charset="UTF-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <title>Player Externo</title>
                                    <style>
                    :root {
                        --background-color: #181818;
                        --container-color: #202020;
                        --button-color: #292929;
                        --hover-color: #444;
                        --text-color: #ffffff;
                        --shadow-color: rgba(0, 0, 0, 0.3);
                    }
                
                    body {
                        display: flex;
                        height: 100vh;
                        margin: 0;
                        overflow: hidden;
                        background-color: var(--background-color);
                        font-family: Arial, sans-serif;
                    }
                
                    .episodes-container {
                        width: 25%;
                        background-color: var(--container-color);
                        overflow-y: auto;
                        padding: 1rem;
                        box-shadow: 2px 0 5px var(--shadow-color);
                    }
                
                    .episodes-container button {
                        width: 100%;
                        background-color: var(--button-color);
                        margin-bottom: 0.5rem;
                        border: none;
                        padding: 0.8rem;
                        border-radius: 5px;
                        color: var(--text-color);
                        text-align: left;
                        cursor: pointer;
                        font-size: 1rem;
                        transition: background-color 0.3s ease;
                    }
                
                    .episodes-container button:hover {
                        background-color: var(--hover-color);
                    }
                
                    .video-container {
                        flex: 1;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 1rem;
                        background-color: var(--background-color);
                    }
                
                    video {
                        width: 100%;
                        max-width: 800px;
                        max-height: 90%;
                        background-color: black;
                        border-radius: 10px;
                    }
                
                    @media (max-width: 768px) {
                        .episodes-container {
                            width: 30%;
                        }
                
                        .episodes-container button {
                            font-size: 0.9rem;
                            padding: 0.6rem;
                        }
                
                        video {
                            max-width: 90%;
                        }
                    }
                
                    @media (max-width: 480px) {
                        .episodes-container {
                            width: 40%;
                        }
                
                        .episodes-container button {
                            font-size: 0.8rem;
                            padding: 0.5rem;
                        }
                    }
                </style>
                                </head>
                                <body>
                                    <div class="episodes-container">
                                        ${episodeButtons}
                                    </div>
                                    <div class="video-container">
                                        <video id="player-video" controls>
                                            <source src="" type="video/mp4">
                                            Seu navegador não suporta o elemento de vídeo.
                                        </video>
                                    </div>
                                </body>
                                </html>
                            `;
                
                            const blob = new Blob([blobContent], { type: 'text/html' });
                            const blobUrl = URL.createObjectURL(blob);
                            window.open(blobUrl, '_blank');
                        });
                
                        // Exibir a resposta da API em uma caixa de texto que pode ser fechada
                        let textArea = null;
                        document.getElementById('view-api-response').addEventListener('click', function () {
                            if (textArea) {
                                textArea.remove();
                                textArea = null;
                            } else {
                                const responseText = JSON.stringify(<?php echo json_encode($data); ?>, null, 2);
                                textArea = document.createElement('textarea');
                                textArea.style.width = '100%';
                                textArea.style.height = '300px';
                                textArea.value = responseText;
                                document.body.appendChild(textArea);
                            }
                        });
                
                        // Baixar a playlist M3U somente com os episódios da qualidade selecionada
                        document.getElementById('download-m3u').addEventListener('click', function () {
                            const selectedQuality = qualitySelect.value;
                            let m3uContent = '#EXTM3U\n';
                            episodes.forEach(ep => {
                                const resolutionData = ep.data.find(d => d.resolution === selectedQuality);
                                if (resolutionData) {
                                    m3uContent += `#EXTINF:-1, Episódio ${ep.episode} (${resolutionData.resolution})\n${resolutionData.url}\n`;
                                }
                            });
                
                            const blob = new Blob([m3uContent], { type: 'audio/mpegurl' });
                            const blobUrl = URL.createObjectURL(blob);
                
                            const downloadLink = document.createElement('a');
                            downloadLink.href = blobUrl;
                            downloadLink.download = `playlist_${selectedQuality}.m3u`;
                            downloadLink.click();
                        });
                    });
                </script>
                <?php endif; ?>
        </div>
		 <footer>
        <a href="https://github.com/seu-repositorio" target="_blank">
            <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo">
            AnFireAPI - ver projeto no GitHub.
        </a>
    </footer>
    </body>

    </html>
