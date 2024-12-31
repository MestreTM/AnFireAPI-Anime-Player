<?php
/*********************************************************************
 *                          AnFire Player                               *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 *********************************************************************/

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["anime_input"]) && !empty(trim($_POST["anime_input"]))) {
        $anime_input = trim($_POST["anime_input"]);

        // CONFIGURE SUA HOST E API!
        $api_key = "SUA_API_AQUI";
        $api_url = "http://localhost/api.php?api_key=$api_key&anime_link=" .urlencode($anime_input);

        // Preencha com o URL completo do embed.js do disqus para habilitar os comentarios ou deixe vazio para desativado.
        $disqusEmbedUrl = "";

        //
        if (!isset($_GET["link"]) || empty($_GET["link"])) {
            $disqusEmbedUrl = "";
        }

        $response = @file_get_contents($api_url);

        if ($response === false) {
            $error_message =
                "Falha ao acessar a API. Verifique a URL ou os parâmetros. Talvez este anime ainda não tenha episodios ?!";
        } else {
            $data = json_decode($response, true);
        }
    } else {
        $error_message = "Por favor, insira um link válido.";
    }
}
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AnFire Player</title>
        <style>
            body {
        font-family: "Roboto", Arial, sans-serif;
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
      input[type="text"].error {
        border: 2px solid red; /* Borda vermelha */
        background-color: #ffe6e6; /* Fundo levemente vermelho */
      }
      
      small {
        display: block;
        margin-top: 5px;
        font-size: 0.9rem;
        color: red;
      }
      
      .container {
        max-width: 960px;
        width: 90%;
        margin: 20px;
        padding: 30px;
        background: linear-gradient(145deg, #1a1a1a, #252525);
        border-radius: 20px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5),
          inset 0 -1px 5px rgba(255, 255, 255, 0.1);
        text-align: center;
      }
      
      h1 {
        font-size: 2.8rem;
        margin-bottom: 20px;
        color: #5288e5;
        text-shadow: 0px 0px 5px rgba(82, 136, 229, 255),
          0px 0px 15px rgba(0, 140, 140, 0.5);
      }
      
      label {
        display: block;
        font-size: 1.2rem;
        margin-bottom: 10px;
        color: #cccccc;
      }
      
      input[type="text"],
      select {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        color: #ffffff;
        font-size: 1rem;
        transition: all 0.3s;
        box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.5),
          inset -2px -2px 5px rgba(255, 255, 255, 0.1);
      }
      
      input::placeholder {
        color: #8e8e8e;
      }
      
      input:focus {
        border-color: #5288e5;
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
        background: linear-gradient(145deg, #5288e5, #1e3a69);
        color: #ffffff;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3),
          inset 0 -1px 5px rgba(255, 255, 255, 0.1);
        transition: transform 0.2s, box-shadow 0.3s;
      }
      
      button:hover {
        transform: scale(1.05);
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
      
        input[type="text"],
        select {
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
      #anime-container {
        display: flex; 
        align-items: flex-start; 
        gap: 20px; 
        margin-bottom: 20px;
        flex-wrap: nowrap; 
      }
      
      #anime-image {
        width: 200px; 
        height: auto; 
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 -2px 5px rgba(255, 255, 255, 0.1);
        flex-shrink: 0;
      }
      
      #anime-details {
        display: flex;
        flex-direction: column; 
        justify-content: flex-start; 
        flex: 1; 
        max-width: 100%; 
		text-align: left;
      }
      
      #anime-title {
        font-size: 1.8rem;
        color: #5288e5;
        word-wrap: break-word; 
      }
      
      #anime-synopsis {
        font-size: 1rem;
        line-height: 1.6;
        color: #ccc;
        overflow: hidden; 
        text-overflow: ellipsis; 
        display: -webkit-box; 
        -webkit-line-clamp: 4; 
        -webkit-box-orient: vertical;
      }
      
      @media (max-width: 768px) {
        #anime-container {
          flex-direction: column;
          align-items: center; 
        }
      
        #anime-image {
          width: 100%; 
          max-width: 300px;
          margin-bottom: 15px;
        }
      
        #anime-title {
          text-align: center;
        }
      
        #anime-synopsis {
          text-align: justify;
          -webkit-line-clamp: 2;
        }
      }
      #disqus_thread {
        width: 100%;
        max-width: 800px;
        margin: 20px auto;
        min-height: 400px;
      }

        </style>
    </head>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Atualizar o título da página dinamicamente
        const animeTitleElement = document.getElementById('anime-title');
        if (animeTitleElement && animeTitleElement.textContent.trim() !== '') {
            const animeTitle = animeTitleElement.textContent.trim();
            document.title = `AnFire Player - ${animeTitle}`;
        } else {
            document.title = 'AnFire Player';
        }

        // Configuração do Disqus para capturar o título atualizado
        var disqus_config = function () {
            this.page.url = window.location.href; 
            this.page.identifier = new URLSearchParams(window.location.search).get('link') || 'default'; // Identificador único
            this.page.title = document.title; 
        };

        // Recarregar o Disqus após atualizar o título
        (function () {
            var d = document, s = d.createElement('script');
            s.src = 'https://mestretm-cursos.disqus.com/embed.js'; 
            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
    });
</script>
<noscript>
    Please enable JavaScript to view the
    <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a>
</noscript>

<noscript>
    Please enable JavaScript to view the
    <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a>
</noscript>
    <body>
        <div class="container">
            <h1><a  href="/"> <img src="https://i.imgur.com/YFFnp7E.png" width="200"></a></h1>
			
            <form method="post">
                <label for="anime-input">Insira o anime_slug ou anime_link (*-todos-os-episodios):</label>
                <input type="text" id="anime-input" name="anime_input" placeholder="Exemplo: https://animefire.plus/animes/spy-x-family-season-2-dublado-todos-os-episodios" value="<?php echo htmlspecialchars(
                    $_POST["anime_input"] ?? ""
                ); ?>" style="<?php echo !empty($error_message)
    ? "border: 2px solid red;"
    : ""; ?>">
                <?php if (!empty($error_message)): ?>
                    <small style="color: red;"><?php echo htmlspecialchars(
                        $error_message
                    ); ?></small>
                    <?php endif; ?>
                        <button type="submit">Carregar Episódios</button>
            </form>
            <?php if (isset($data) && isset($data["episodes"])): ?>
                <div id="anime-container">
                    <?php if (isset($data["anime_image"])): ?>
                        <img id="anime-image" src="<?php echo htmlspecialchars(
                            $data["anime_image"]
                        ); ?>" alt="Imagem do Anime" />
                        <?php endif; ?>
                            <div id="anime-details">
                                <h2 id="anime-title"><?php echo htmlspecialchars(
                                    $data["anime_title1"] ??
                                        "Título não disponível"
                                ); ?></h2>
                                <p id="anime-synopsis">
                                    <?php echo htmlspecialchars(
                                        $data["anime_synopsis"] ??
                                            "Sinopse não disponível"
                                    ); ?>
                                </p>
                            </div>
                </div>
                <label for="quality">Selecione a qualidade:</label>
                <select id="quality">
                </select>
                <button id="generate-player">▷ Assistir no Player Online</button>
                </br>
                </br>
                <button id="view-api-response">⚙ Ver resposta da API</button>
                <button id="download-m3u">🗎 Baixar playlist M3U para VLC</button>
        </div>
        </div>
        <script>
		document.addEventListener('DOMContentLoaded', function () {
    const episodes = <?php echo json_encode($data["episodes"]); ?>;
    const qualitySelect = document.getElementById('quality');
	const generatePlayerButton = document.getElementById('generate-player');

    // Verifica se já existem opções no seletor
    if (qualitySelect.options.length > 0) {
        return; 
    }

    const resolutions = new Set();
    episodes.forEach(ep => {
        ep.data.forEach(resolutionData => {
            resolutions.add(resolutionData.resolution);
        });
    });

    if (resolutions.size === 0) {
        // Adicionar placeholder "Sem episódios disponíveis"
        const placeholderOption = document.createElement('option');
        placeholderOption.value = "";
        placeholderOption.textContent = "Sem episódios disponíveis";
        placeholderOption.disabled = true;
        placeholderOption.selected = true;
        qualitySelect.appendChild(placeholderOption);
        generatePlayerButton.style.display = "none";
        } else {

        resolutions.forEach(resolution => {
            const option = document.createElement('option');
            option.value = resolution;
            option.textContent = resolution;
        });
		generatePlayerButton.style.display = "block";
		generatePlayerButton.style.margin = "20px auto"; 
    }
	handleQualityOptions();
});

            document.addEventListener('DOMContentLoaded', function () {
                // Função para substituir o player no HTML
                function replacePlayerInHTML(html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
            
                    const videoContainer = doc.querySelector('.video-container');
                    if (videoContainer) {
                        const iframe = document.createElement('iframe');
                        iframe.src = ''; 
                        iframe.id = 'player-iframe'; 
                        iframe.width = '100%';
                        iframe.height = '400';
                        iframe.style.border = 'none';
                        iframe.allowFullscreen = true;
            
                        videoContainer.innerHTML = '';
                        videoContainer.appendChild(iframe);
            
                        console.log('Player substituído no HTML por um iframe com placeholder.');
                    } else {
                        console.warn('Player padrão não encontrado no HTML.');
                    }
            
                    return doc.documentElement.outerHTML;
                }
            
                // Sobrescrever a lógica de geração do blob
                const originalCreateObjectURL = URL.createObjectURL;
            
                window.URL.createObjectURL = function (blob) {
                    if (blob.type === 'text/html') {
                        const reader = new FileReader();
                        const newWindow = window.open('about:blank', '_blank');
                        reader.onload = function () {
                            const originalHTML = reader.result;
                            const updatedHTML = replacePlayerInHTML(originalHTML);
                            const updatedBlob = new Blob([updatedHTML], { type: 'text/html' });
                            const updatedBlobUrl = originalCreateObjectURL(updatedBlob);
                            if (newWindow) {
                                newWindow.location.href = updatedBlobUrl;
                                console.log('Blob atualizado gerado e carregado na nova aba:', updatedBlobUrl);
                            } else {
                                console.error('Não foi possível abrir a nova aba.');
                            }
                        };
            
                        reader.readAsText(blob);
                        return ''; 
                    }
            
                    return originalCreateObjectURL(blob);
                };
                document.addEventListener('click', function (event) {
                    if (event.target.tagName === 'BUTTON' && event.target.hasAttribute('onclick')) {
                        const onclickAttr = event.target.getAttribute('onclick');
                        const urlMatch = onclickAttr.match(/changeEpisode\\(['"](.*?)['"]\\)/);
                        if (urlMatch) {
                            const episodeUrl = urlMatch[1];
                            const iframe = document.getElementById('player-iframe');
                            if (iframe) {
                                iframe.src = episodeUrl; 
                                console.log('Atualizando iframe com URL:', episodeUrl);
                            }
                        }
                    }
                });
            
                console.log('Interceptação de blobs configurada e suporte para iframe configurado.');
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const episodes = <?php echo json_encode($data["episodes"]); ?>;
                const resolutions = new Set();
                episodes.forEach(ep => {
                    ep.data.forEach(resolutionData => {
                        resolutions.add(resolutionData.resolution);
                    });
                });
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
            <button onclick="changeEpisode('${resolutionData.url}')">
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
            margin: 0;
            background-color: var(--background-color);
            font-family: Arial, sans-serif;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .video-container {
            width: 100%;
            max-width: 800px;
            padding: 1rem;
            background-color: var(--background-color);
        }
        
        video {
            width: 100%;
            border-radius: 10px;
            object-fit: cover;
            background-color: black;
        }
        
        .episodes-container {
            width: 100%;
            max-width: 800px;
            padding: 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--container-color);
            box-shadow: 0 -2px 5px var(--shadow-color);
        }
        
        .episodes-container button {
            width: calc(48% - 0.5rem);
            background-color: var(--button-color);
            border: none;
            padding: 0.8rem;
            border-radius: 5px;
            color: var(--text-color);
            text-align: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        
        .episodes-container button:hover {
            background-color: var(--hover-color);
        }
        
        @media (max-width: 768px) {
            .episodes-container button {
                width: calc(100% - 0.5rem); /* Botões ocupam toda a largura em telas pequenas */
                font-size: 0.85rem;
                padding: 0.7rem;
            }
        }
        
                            </style>
                            <!-- Fluid Player Styles -->
                            <link rel="stylesheet" href="https://cdn.fluidplayer.com/v3/current/fluidplayer.min.css">
                        </head>
                        <body>
                            <div class="video-container">
                                <video id="player-video" controls>
                                    <source src="" type="video/mp4">
                                    Seu navegador não suporta o elemento de vídeo.
                                </video>
                            </div>
        					
        					<div class="episodes-container">
                                ${episodeButtons}
                            </div>
        
                            <!-- Fluid Player Script -->
                            <script src="https://cdn.fluidplayer.com/v3/current/fluidplayer.min.js"><\/script>
                            <script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentPlayer = null;
        const iframeElement = document.getElementById('player-iframe'); // Suporte ao iframe, importante para usar links blogger.com
        const videoElement = document.createElement('video'); 
        videoElement.setAttribute('id', 'player-video');
        videoElement.setAttribute('controls', 'controls');
        videoElement.style.width = '100%';
        videoElement.style.borderRadius = '10px';
        document.querySelector('.video-container').appendChild(videoElement);

        const episodeButtons = document.querySelectorAll('.episodes-container button');

        // Função para verificar se a URL é um link de vídeo direto ou uma página Blogger
        function isVideoUrl(url) {
            const videoExtensions = ['.mp4', '.webm', '.ogg', '.m3u8'];
            return videoExtensions.some(ext => url.includes(ext));
        }
        window.changeEpisode = function (url) {
            if (url.includes('blogger.com')) {
                // Carregar no iframe
                videoElement.style.display = 'none';
                iframeElement.style.display = 'block'; 
                iframeElement.src = url;
                console.log('URL carregada no iframe:', url);
            } else if (isVideoUrl(url)) {
                iframeElement.style.display = 'none';
                videoElement.style.display = 'block';
                videoElement.src = url;
                videoElement.load();
                if (currentPlayer) {
                    currentPlayer.play();
                } else {
                    currentPlayer = fluidPlayer('player-video', {
                        layoutControls: {
                            controlBar: {
                                autoHideTimeout: 3,
                                animated: true,
                                autoHide: true,
                            },
                            htmlOnPauseBlock: {
                                html: null,
                                height: null,
                                width: null,
                            },
                            autoPlay: false,
                            mute: true,
                            allowTheatre: true,
                            playPauseAnimation: true,
                            playbackRateEnabled: false,
                            allowDownload: true,
                            playButtonShowing: true,
                            fillToContainer: false,
                            primaryColor: "#5288e5",
                            posterImage: "https://i.imgur.com/9NtMX19.jpeg",
							posterImageSize: "cover",
                            roundedCorners:         10,
                            logo: {
                                imageUrl:           "https://i.imgur.com/fin0KDs.png",
                                imageMargin: '5px',
                                position:           'top left',
                                clickUrl:           null,
                                opacity:            0.3
                            },
                            miniPlayer: {
                                enabled: false,
                            },
                        },
                        vastOptions: {
                            adList: [],
                            adCTAText: false,
                            adCTATextPosition: "",
                        },
                    });
                }
                console.log('Episódio carregado no player de vídeo:', url);
            } else {
                console.error('URL não reconhecida como vídeo ou página compatível.');
            }
        };
        episodeButtons.forEach((button) => {
            button.addEventListener('click', function () {
                const url = button.getAttribute('onclick').match(/changeEpisode\(['"](.*?)['"]\)/)[1];
                window.changeEpisode(url);
            });
        });
        function simulateEpisode1Click() {
            const episode1Button = episodeButtons[0];
            if (episode1Button) {
                episode1Button.click();
            } else {
                console.error('Botão do episódio 1 não encontrado!');
            }
        }
        setTimeout(simulateEpisode1Click, 100);
    });
                            <\/script>
                        </body>
                        </html>
                    `.replace(/<\/script>/g, '<\/script>');
        
                    const blob = new Blob([blobContent], { type: 'text/html' });
                    const blobUrl = URL.createObjectURL(blob);
                });
        
                // Exibir a resposta da API em uma caixa de texto que pode ser fechada
                let textArea = null;
                document.getElementById('view-api-response').addEventListener('click', function () {
                    if (textArea) {
                        textArea.remove();
                        textArea = null;
                    } else {
                        const responseText = JSON.stringify(<?php echo json_encode(
                            $data
                        ); ?>, null, 2);
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
			document.addEventListener('DOMContentLoaded', function () {
    // Substituir botões com links do Blogger por iframes
    const replaceBloggerButtonsWithIframes = () => {
        const buttons = document.querySelectorAll('button');
        
        buttons.forEach(button => {
            const onClickAttr = button.getAttribute('onclick');
            if (onClickAttr && onClickAttr.includes('changeEpisode(')) {
                const urlMatch = onClickAttr.match(/changeEpisode\\(['\"](.*?)['\"]\\)/);
                if (urlMatch && urlMatch[1].includes('blogger.com/video.g?token=')) {
                    const bloggerUrl = urlMatch[1];

                    // Criar iframe substituindo o botão
                    const iframe = document.createElement('iframe');
                    iframe.src = bloggerUrl;
                    iframe.width = '100%';
                    iframe.height = '400';
                    iframe.style.border = 'none';
                    iframe.allowFullscreen = true;

                    // Substituir o botão pelo iframe
                    button.parentNode.replaceChild(iframe, button);
                }
            }
        });
    };

    // Executar a substituição após a geração inicial dos botões
    document.getElementById('generate-player').addEventListener('click', function () {
        setTimeout(replaceBloggerButtonsWithIframes, 100); // Pequeno delay para garantir que os botões foram criados
    });
});
document.addEventListener('DOMContentLoaded', function () {
    // Função para substituir botões com links do Blogger por iframes
    const replaceBloggerButtonsWithIframes = (parentNode) => {
        const buttons = parentNode.querySelectorAll('button');
        buttons.forEach(button => {
            const onClickAttr = button.getAttribute('onclick');
            if (onClickAttr && onClickAttr.includes('changeEpisode(')) {
                const urlMatch = onClickAttr.match(/changeEpisode\\(['\"](.*?)['\"]\\)/);
                if (urlMatch && urlMatch[1].includes('blogger.com/video.g?token=')) {
                    const bloggerUrl = urlMatch[1];

                    // Criar iframe substituindo o botão
                    const iframe = document.createElement('iframe');
                    iframe.src = bloggerUrl;
                    iframe.width = '100%';
                    iframe.height = '400';
                    iframe.style.border = 'none';
                    iframe.allowFullscreen = true;

                    // Substituir o botão pelo iframe
                    button.parentNode.replaceChild(iframe, button);
                }
            }
        });
    };

    // Configurar um MutationObserver para observar mudanças no DOM
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        replaceBloggerButtonsWithIframes(node);
                    }
                });
            }
        });
    });

    // Observar o body para mudanças no DOM
    observer.observe(document.body, { childList: true, subtree: true });
});
        </script>
		<div id="disqus_thread"></div>
<script>
    // Passar o valor da variável PHP para o JavaScript
    var disqusEmbedUrl = "<?php echo $disqusEmbedUrl; ?>";

    // Carregar o Disqus apenas se o URL não estiver vazio
    if (disqusEmbedUrl) {
        var disqus_config = function () {
            this.page.url = window.location.href; // URL atual da página
            this.page.identifier = new URLSearchParams(window.location.search).get('link') || 'default'; // Identificador único
        };

        (function () { // DON'T EDIT BELOW THIS LINE
            var d = document, s = d.createElement('script');
            s.src = disqusEmbedUrl; // Usa o URL completo definido no PHP
            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
    } else {
        console.warn('Disqus desativado: "disqusEmbedUrl" está vazio.');
        // Opcional: Remover ou esconder o contêiner do Disqus se ele não for usado
        const disqusContainer = document.getElementById('disqus_thread');
        if (disqusContainer) {
            disqusContainer.style.display = 'none';
        }
    }
</script>
<noscript>
    Please enable JavaScript to view the 
    <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a>
</noscript>


        <?php endif; ?>
            </div>
            <footer>
                <a href="https://github.com/MestreTM/AnFireAPI/" target="_blank">
                    <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo"> AnFireAPI - ver projeto no GitHub.
                </a>
            </footer>
    </body>

    </html>
<script type="text/javascript">
    // Função para definir cookies
    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
        document.cookie = `${name}=${value}; expires=${expires}; path=/`;
    }

    // Função para obter cookies
    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [key, value] = cookie.trim().split('=');
            if (key === name) {
                return value;
            }
        }
        return null;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const processedLink = getCookie('processedLink'); // Verificar link já processado

        if (params.has('link')) {
            const link = params.get('link');

            if (link !== processedLink && link.startsWith('https')) {
                document.getElementById("anime-input").value = link;
                
                const buttons = document.querySelectorAll('button[type="submit"]');
                let loadButton = null;

                // Procurar botão pelo texto "Carregar Episódios"
                buttons.forEach(button => {
                    if (button.textContent.trim() === "Carregar Episódios") {
                        loadButton = button;
                    }
                });

                if (loadButton) {
                    loadButton.click(); // Simula o clique no botão
                    setCookie('processedLink', link, 1); // Salvar o link como processado
                    console.log('Carregando episódio automaticamente:', link);
                } else {
                    console.error('Botão "Carregar Episódios" não encontrado.');
                }
            } else if (link === processedLink) {
                console.log('Link já processado anteriormente:', link);
            } else {
                console.warn('Link fornecido não é compatível:', link);
            }
        }
    });
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const processedLink = getCookie('processedLink'); // Verificar link já processado

        if (params.has('link')) {
            const link = params.get('link');

            // Ocultar input, label, e botão "Carregar Episódios"
            const label = document.querySelector('label[for="anime-input"]');
            const input = document.getElementById("anime-input");
            const loadButton = document.querySelector('button[type="submit"]');

            if (label) label.style.display = "none";
            if (input) input.style.display = "none";
            if (loadButton) loadButton.style.display = "none";

            // Criar botão "Voltar" dentro do contêiner, abaixo do título
            const backButton = document.createElement('button');
            backButton.textContent = "Voltar";
            backButton.style.display = "block";
            backButton.style.margin = "20px auto";
            backButton.style.padding = "10px 20px";
            backButton.style.backgroundColor = "#5288e5";
            backButton.style.color = "#ffffff";
            backButton.style.border = "none";
            backButton.style.borderRadius = "5px";
            backButton.style.cursor = "pointer";
            backButton.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
            backButton.addEventListener('click', function () {
                // Redirecionar para a página principal
                window.location.href = '/';
            });

            // Inserir o botão "Voltar" após o título
            const container = document.querySelector('.container');
            const title = container.querySelector('h1');
            if (title && container) {
                title.insertAdjacentElement('afterend', backButton);
            }

            // Preencher o input com o link e clicar no botão "Carregar Episódios"
            if (link !== processedLink && link.startsWith('https')) {
                document.getElementById("anime-input").value = link;
                setCookie('processedLink', link, 1); // Salvar o link como processado
                loadButton.click(); // Simula o clique no botão
                console.log('Carregando episódio automaticamente:', link);
            } else if (link === processedLink) {
                console.log('Link já processado anteriormente:', link);
            } else {
                console.warn('Link fornecido não é compatível:', link);
            }
        }
    });

    // Função para definir cookies
    function setCookie(name, value, days) {
        const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
        document.cookie = `${name}=${value}; expires=${expires}; path=/`;
    }

    // Função para obter cookies
    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [key, value] = cookie.trim().split('=');
            if (key === name) {
                return value;
            }
        }
        return null;
    }
</script>



















