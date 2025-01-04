<?php
session_start();

/*********************************************************************
 *                          AnFire Player                           *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 *********************************************************************/

// CONFIGURAÇÕES DA API
define('API_KEY', 'SUA_API_KEY');
define('API_URL', 'http://localhost/api.php');

/**
 * Função para chamar a API com o link fornecido.
 *
 * @param string $link O link do anime.
 * @param bool $update Indica se deve forçar a atualização.
 * @return array|false Retorna os dados da API ou false em caso de falha.
 */
function chamarAPI($link, $update = false) {
    $url = API_URL . "?api_key=" . urlencode(API_KEY) . "&anime_link=" . urlencode($link);
    if ($update) {
        $url .= "&update=true";
    }
    $response = @file_get_contents($url);

    if ($response !== false) {
        return json_decode($response, true);
    }
    return false;
}

/**
 * Função para validar URLs HTTPS.
 *
 * @param string $url A URL a ser validada.
 * @return bool Retorna true se a URL for válida e HTTPS, caso contrário, false.
 */
function validarURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) && strpos($url, 'https') === 0;
}

/**
 * Função para redirecionar para a mesma página sem o parâmetro 'link'.
 */
function redirecionarSemLink() {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $url = $scheme . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

    // Manter outros parâmetros de consulta, exceto 'link'
    $query = $_GET;
    unset($query['link']);
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    header("Location: $url");
    exit();
}

// Inicializar mensagens de erro, atualização e a flag $from_link
$error_message = "";
$update_message = "";
$from_link = false;

// 1. Processamento Exclusivo no Servidor para o parâmetro ?link=
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["link"]) && !empty(trim($_GET["link"]))) {
    $link = trim($_GET["link"]);

    if (validarURL($link)) {
        $data = chamarAPI($link);

        if ($data !== false) {
            // Armazenar os dados na sessão
            $_SESSION['data'] = $data;
            $_SESSION['from_link'] = true;
            $_SESSION['anime_link'] = $link; // Armazenar o link na sessão
        } else {
            // Armazenar mensagem de erro na sessão
            $_SESSION['error_message'] = "Falha ao acessar a API, verifique os parâmetros. Talvez este anime ainda não tenha episódios!?";
        }

        // Redirecionar para a mesma URL sem o parâmetro ?link=
        redirecionarSemLink();
    } else {
        // Link inválido, armazenar mensagem de erro na sessão
        $_SESSION['error_message'] = "Por favor, insira um link válido.";

        // Redirecionar para a mesma página sem o parâmetro ?link=
        redirecionarSemLink();
    }
}

// 2. Processamento de requisições POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["anime_input"]) && !empty(trim($_POST["anime_input"]))) {
        // Ação de "Carregar Episódios"
        $anime_input = trim($_POST["anime_input"]);

        if (validarURL($anime_input)) {
            $data = chamarAPI($anime_input);

            if ($data !== false) {
                // Armazenar os dados na sessão
                $_SESSION['data'] = $data;
                $_SESSION['from_link'] = true;
                $_SESSION['anime_link'] = $anime_input; // Armazenar o link na sessão
            } else {
                // Armazenar mensagem de erro na sessão
                $_SESSION['error_message'] = "Falha ao acessar a API. Verifique a URL ou os parâmetros. Talvez este anime ainda não tenha episódios!";
            }

            // Redirecionar para a mesma URL sem o parâmetro ?link=
            redirecionarSemLink();
        } else {
            // Link inválido, armazenar mensagem de erro na sessão
            $_SESSION['error_message'] = "Por favor, insira um link válido.";

            // Redirecionar para a mesma página sem o parâmetro ?link=
            redirecionarSemLink();
        }
    } elseif (isset($_POST['update']) && $_POST['update'] === 'true') {
        // Ação de "Buscar Novos Episódios"

        if (isset($_SESSION['anime_link'])) {
            $link = $_SESSION['anime_link'];

            if (validarURL($link)) {
                // Gerar nome único para o cookie usando hash MD5 do anime_link
                $cookie_name = 'buscar_novos_' . md5($link);

                if (isset($_COOKIE[$cookie_name])) {
                    // O usuário já buscou novos episódios para este anime hoje
                    // Não enviar update=true
                    $data = chamarAPI($link, false); // Buscar sem forçar

                    if ($data !== false) {
                        // Atualizar os dados na sessão
                        $_SESSION['data'] = $data;
                        $_SESSION['from_link'] = true;

                        // Definir mensagem de que o usuário já buscou hoje
                        $_SESSION['update_message'] = "Você já buscou por novos episódios hoje, tente amanhã.";
                    } else {
                        // Armazenar mensagem de erro na sessão
                        $_SESSION['error_message'] = "Falha ao acessar a API. Verifique a URL ou os parâmetros.";
                    }
                } else {
                    // Permitir a busca e definir o cookie
                    $data = chamarAPI($link, true); // Forçar atualização

                    if ($data !== false) {
                        // Atualizar os dados na sessão
                        $_SESSION['data'] = $data;
                        $_SESSION['from_link'] = true;

                        // Definir o cookie para expirar em 24 horas
                        setcookie($cookie_name, '1', time() + 86400, "/"); // 86400 segundos = 1 dia

                        // Verificar 'new_episodes' na resposta
                        if (isset($data['new_episodes']) && is_array($data['new_episodes'])) {
                            if (!empty($data['new_episodes'])) {
                                // Ordenar os novos episódios
                                sort($data['new_episodes']);
                                $new_eps = implode(', ', $data['new_episodes']);
                                $_SESSION['update_message'] = "Novos episódios encontrados! - Ep " . $new_eps . " ";
                            } else {
                                $_SESSION['update_message'] = "Nenhum episódio novo foi encontrado.";
                            }
                        } else {
                            $_SESSION['update_message'] = "Nenhum episódio novo foi encontrado.";
                        }
                    } else {
                        // Armazenar mensagem de erro na sessão
                        $_SESSION['error_message'] = "Falha ao acessar a API com update=true. Verifique a URL ou os parâmetros.";
                    }
                }

                // Redirecionar para a mesma URL sem o parâmetro ?link=
                redirecionarSemLink();
            } else {
                // Link armazenado é inválido
                $_SESSION['error_message'] = "Link armazenado é inválido.";

                // Redirecionar para a mesma página sem o parâmetro ?link=
                redirecionarSemLink();
            }
        } else {
            // Nenhum link armazenado
            $_SESSION['error_message'] = "Nenhum link armazenado para buscar novos episódios.";

            // Redirecionar para a mesma página sem o parâmetro ?link=
            redirecionarSemLink();
        }
    } else {
        // Ação de POST desconhecida
        $error_message = "Ação de POST desconhecida. Verifique o link ou comando e tente novamente.";
    }
}

// 3. Recuperação dos Dados da Sessão após Redirecionamento
if (isset($_SESSION['data'])) {
    $data = $_SESSION['data'];
    $from_link = $_SESSION['from_link'] ?? false;
    unset($_SESSION['data'], $_SESSION['from_link']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['update_message'])) {
    $update_message = $_SESSION['update_message'];
    unset($_SESSION['update_message']);
}

// Definir o título do anime com fallback
if (!empty($data["anime_title1"])) {
    $anime_title = $data["anime_title1"];
} elseif (!empty($data["anime_title"])) {
    $anime_title = $data["anime_title"];
} else {
    $anime_title = "";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Atualizado para incluir o título do anime com fallback -->
    <title>AnFire Player - <?php echo htmlspecialchars($anime_title ?: "Título do Anime"); ?></title>
    <style>
        /* Seu CSS permanece inalterado */
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
            border: 2px solid red;
            background-color: #ffe6e6;
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

        /* Atualização do container para layout side-by-side */
        #anime-container {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            /* Mantendo flex-direction: row para side-by-side */
        }

        /* Novo container para imagem e botão */
        .anime-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 200px; /* Mesma largura da imagem */
        }

        #anime-image {
            width: 200px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5), 0 -2px 5px rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
        }

        /* Estilo para o novo botão "Buscar Novos Episódios" */
        .buscar-novos-button {
            width: 100%; /* Mesma largura da imagem dentro do container */
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            background: linear-gradient(145deg, #e55e5e, #9e1e1e);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3),
                inset 0 -1px 5px rgba(255, 255, 255, 0.1);
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .buscar-novos-button:hover {
            transform: scale(1.05);
        }

        .buscar-novos-button:active {
            transform: scale(0.95);
        }

        @media (max-width: 768px) {
            .anime-image-container {
                width: 100%; /* Ajustar para 100% em telas menores */
                max-width: 300px; /* Limite máximo de largura */
            }

            .buscar-novos-button {
                width: 100%; /* 100% dentro do container flexível */
                max-width: 300px;
            }
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
            #anime-details {
                text-align: center; /* Alinhar centralizado em telas menores */
            }

            #anime-synopsis {
                text-align: justify;
                -webkit-line-clamp: 2;
            }
        }

        /* Classe para ocultar elementos */
        .hidden {
            display: none;
        }

        .home-link {
            display: inline-block;
            margin: 15px 0;
            font-size: 1rem;
            font-weight: bold;
            color: #5288e5;
            text-decoration: none;
            padding: 5px 5px;
            border: 2px solid #5288e5;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .home-link:hover {
            background-color: #5288e5;
            color: #000;
        }

        /* Estilo para a mensagem de atualização */
        .update-message {
            margin: 10px 0;
            padding: 10px;
            background-color: #3160b0;
            border-radius: 5px;
            color: #f1f1f1;
            font-size: 1rem;
            text-align: center;
        }
		.error-message {
            margin: 10px 0;
            padding: 10px;
            background-color: #330000;
            border-radius: 5px;
            color: #ffcccc;
            font-size: 1rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><a href="/"> <img src="https://i.imgur.com/YFFnp7E.png" width="200" alt="AnFire Logo"></a></h1>
        <a href='/' class='home-link'>Home</a>

        <!-- Formulário: Ocultar se $from_link for verdadeiro -->
        <form method="post" <?php echo $from_link ? 'class="hidden"' : ''; ?>>
            <label for="anime-input">Insira o anime_slug ou anime_link (*-todos-os-episodios):</label>
            <input type="text" id="anime-input" name="anime_input" placeholder="Exemplo: https://animefire.plus/animes/spy-x-family-season-2-dublado-todos-os-episodios" value="<?php echo htmlspecialchars(
                $_POST["anime_input"] ?? ""
            ); ?>" <?php echo !empty($error_message) ? 'class="error"' : ''; ?>>
            <?php if (!empty($error_message)): ?>
                <small class="error-message"><?php echo htmlspecialchars($error_message); ?></small>
            <?php endif; ?>
            <button type="submit" <?php echo $from_link ? 'class="hidden"' : ''; ?>>Carregar Episódios</button>
        </form>

        <?php if (isset($data) && isset($data["episodes"])): ?>
            <div id="anime-container">
                <!-- Novo Container: Imagem e Botão -->
                <div class="anime-image-container">
                    <?php if (isset($data["anime_image"])): ?>
                        <img id="anime-image" src="<?php echo htmlspecialchars(
                            $data["anime_image"]
                        ); ?>" alt="Imagem do Anime" />
                    <?php endif; ?>

                    <!-- Novo Botão: Buscar Novos Episódios -->
                    <form method="post">
                        <input type="hidden" name="update" value="true">
                        <button type="submit" class="buscar-novos-button">⟳ Buscar Novos Episódios</button>
                    </form>
                </div>

                <div id="anime-details">
                    <h2 id="anime-title"><?php echo htmlspecialchars(
                        $anime_title ?: "Título do Anime"
                    ); ?></h2>
                    <p id="anime-synopsis">
                        <?php echo htmlspecialchars(
                            $data["anime_synopsis"] ?? "Sinopse não disponível"
                        ); ?>
                    </p>
                </div>
            </div>

            <!-- Exibir mensagem de atualização, se houver -->
            <?php if (!empty($update_message)): ?>
                <p class="update-message"><?php echo htmlspecialchars($update_message); ?></p>
            <?php endif; ?>

            <label for="quality">Selecione a qualidade:</label>
            <select id="quality">
                <!-- Opções serão adicionadas via JavaScript -->
            </select>
            <button id="generate-player">▷ Assistir no Player Online</button>
            <br><br>
            <button id="view-api-response">⚙ Ver resposta da API</button>
            <button id="download-m3u">🗎 Baixar playlist M3U para VLC</button>
    </div>

    <script>
        var animeImage = "<?php echo htmlspecialchars($data['anime_image'] ?? ''); ?>";
        var anime_title = "<?php echo htmlspecialchars($anime_title); ?>";

        document.addEventListener('DOMContentLoaded', function () {
            const episodes = <?php echo json_encode($data["episodes"] ?? []); ?>;
            const qualitySelect = document.getElementById('quality');
            const generatePlayerButton = document.getElementById('generate-player');
            const downloadM3uButton = document.getElementById('download-m3u');

            // Verifica se já existem opções no seletor
            if (qualitySelect.options.length === 0) {
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
                        qualitySelect.appendChild(option);
                    });
                    generatePlayerButton.style.display = "block";
                    generatePlayerButton.style.margin = "20px auto";
                }
            }

            // 2. Manipulador de Evento para Download da Playlist M3U com Formato Personalizado
            document.getElementById('download-m3u').addEventListener('click', function () {
                const selectedQuality = qualitySelect.value;
                let m3uContent = '#EXTM3U\n';

                episodes.forEach(ep => {
                    const resolutionData = ep.data.find(d => d.resolution === selectedQuality);
                    if (resolutionData) {
                        // Linha EXTINF corrigida
                        m3uContent += `#EXTINF:-1 tvg-id="${anime_title}" tvg-logo="${animeImage}" group-title="${anime_title}",Episodio ${ep.episode} ${anime_title}\n${resolutionData.url}\n`;
                    }
                });

                const blob = new Blob([m3uContent], { type: 'audio/mpegurl' });
                const blobUrl = URL.createObjectURL(blob);

                const downloadLink = document.createElement('a');
                downloadLink.href = blobUrl;
                downloadLink.download = `playlist_${anime_title}.m3u`;
                downloadLink.click();
            });

            // ... resto do seu código existente ...
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
                    const urlMatch = onclickAttr.match(/changeEpisode\(['"](.*?)['"]\)/);
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

        document.addEventListener('DOMContentLoaded', function () {
            const episodes = <?php echo json_encode($data["episodes"] ?? []); ?>;
            const resolutions = new Set();
            episodes.forEach(ep => {
                ep.data.forEach(resolutionData => {
                    // Resolução já coletada anteriormente
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
                        roundedCorners: 10,
                        logo: {
                            imageUrl: "https://i.imgur.com/fin0KDs.png",
                            imageMargin: '5px',
                            position: 'top left',
                            clickUrl: null,
                            opacity: 0.3
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
var session = null;

$( document ).ready(function(){
        var loadCastInterval = setInterval(function(){
                if (chrome.cast.isAvailable) {
                        console.log('Cast has loaded.');
                        clearInterval(loadCastInterval);
                        initializeCastApi();
                } else {
                        console.log('Unavailable');
                }
        }, 1000);
});

function initializeCastApi() {
        var applicationID = chrome.cast.media.DEFAULT_MEDIA_RECEIVER_APP_ID;
        var sessionRequest = new chrome.cast.SessionRequest(applicationID);
        var apiConfig = new chrome.cast.ApiConfig(sessionRequest,
                sessionListener,
                receiverListener);
        chrome.cast.initialize(apiConfig, onInitSuccess, onInitError);
};
function sessionListener(e) {
        session = e;
        console.log('New session');
        if (session.media.length != 0) {
                console.log('Found ' + session.media.length + ' sessions.');
        }
 }

function receiverListener(e) {
        if( e === 'available' ) {
                console.log("Chromecast was found on the network.");
        }
        else {
                console.log("There are no Chromecasts available.");
        }
}

function onInitSuccess() {
        console.log("Initialization succeeded");
}

function onInitError() {
        console.log("Initialization failed");
}

$('#castme').click(function(){
        launchApp();
});

function launchApp() {
        console.log("Launching the Chromecast App...");
        chrome.cast.requestSession(onRequestSessionSuccess, onLaunchError);
}

function onRequestSessionSuccess(e) {
        console.log("Successfully created session: " + e.sessionId);
        session = e;
}

function onLaunchError() {
        console.log("Error connecting to the Chromecast.");
}

function onRequestSessionSuccess(e) {
        console.log("Successfully created session: " + e.sessionId);
        session = e;
        loadMedia();
}

function loadMedia() {
        if (!session) {
                console.log("No session.");
                return;
        }
        
        var videoSrc = document.getElementById("myVideo").src;
        var mediaInfo = new chrome.cast.media.MediaInfo(videoSrc);
        mediaInfo.contentType = 'video/mp4';
  
        var request = new chrome.cast.media.LoadRequest(mediaInfo);
        request.autoplay = true;

        session.loadMedia(request, onLoadSuccess, onLoadError);
}

function onLoadSuccess() {
        console.log('Successfully loaded video.');
}

function onLoadError() {
        console.log('Failed to load video.');
}

$('#stop').click(function(){
        stopApp();
});

function stopApp() {
        session.stop(onStopAppSuccess, onStopAppError);
}

function onStopAppSuccess() {
        console.log('Successfully stopped app.');
}

function onStopAppError() {
        console.log('Error stopping app.');
}
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
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Função para substituir botões com links do Blogger por iframes
            const replaceBloggerButtonsWithIframes = (parentNode) => {
                const buttons = parentNode.querySelectorAll('button');
                buttons.forEach(button => {
                    const onClickAttr = button.getAttribute('onclick');
                    if (onClickAttr && onClickAttr.includes('changeEpisode(')) {
                        const urlMatch = onClickAttr.match(/changeEpisode\(['"](.+?)['"]\)/);
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
    <?php endif; ?>
    </div>
    <footer>
        <a href="https://github.com/MestreTM/AnFireAPI/" target="_blank">
            <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo"> AnFireAPI - ver projeto no GitHub.
        </a>
    </footer>
</body>

</html>
