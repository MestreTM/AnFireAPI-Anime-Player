<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseURL = $protocol . "://" . $host;

define('HOST', $baseURL);

function fetchHTML($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

function extractSearchResults($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $results = [];
    $cards = $xpath->query("//div[contains(@class, 'divCardUltimosEps')]");

    foreach ($cards as $card) {
        $link = $xpath->query(".//a", $card)->item(0);
        $image = $xpath->query(".//img", $card)->item(0);
        $title = $xpath->query(".//h3[@class='animeTitle']", $card)->item(0);

        if ($link && $image && $title) {
            $results[] = [
                'url' => HOST . "/AnFire_Player.php?link=" . urlencode($link->getAttribute('href')),
                'image' => $image->getAttribute('data-src') ?? $image->getAttribute('src'),
                'title' => trim($title->nodeValue)
            ];
        }
    }

    return $results;
}

function generateSearchInterface($initialResults = []) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AnFire Browser</title>
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
            }

            h1 {
                font-size: 2.8rem;
                margin-bottom: 20px;
                color: #00e6e6;
                text-shadow: 0px 0px 8px rgba(0, 230, 230, 0.7), 0px 0px 15px rgba(0, 140, 140, 0.5);
            }

            .search-container {
                margin-top: 20px;
                text-align: center;
            }

            .search-input {
                padding: 10px;
                width: 300px;
                border-radius: 10px;
                border: 1px solid #555;
                background-color: #1e1e1e;
                color: #fff;
            }

            .search-button {
                padding: 10px 20px;
                border-radius: 10px;
                border: none;
                background-color: #5288e5;
                color: #000;
                cursor: pointer;
            }

            .results-container {
                margin-top: 20px;
                width: 90%;
                max-width: 1200px;
                text-align: center;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                justify-content: center;
                padding: 10px;
                border: 1px solid #333;
                border-radius: 10px;
                background-color: #1e1e1e;
            }

            .result-item {
                margin: 10px;
                display: inline-block;
                width: 200px;
            }

            .result-item img {
                width: 100%;
                border-radius: 10px;
            }

            .result-item p {
                transition: transform 0.3s;
                color: #ccc;
                font-size: 1rem;
            }

            .result-item:hover {
                transform: scale(1.1);
            }

            a {
                text-decoration: none;
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

            #loader-background {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                z-index: 9998;
            }

            #loader {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 9999;
                font-size: 1.5rem;
                color: #5288e5;
                animation: pulse 1.5s infinite;
            }

            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
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
        </style>
        <script>
            async function searchAnimes() {
                const query = document.getElementById('searchQuery').value.toLowerCase();
                if (!query) return alert('Digite algo para buscar.');

                const loader = document.getElementById('loader');
                const loaderBackground = document.getElementById('loader-background');
                loaderBackground.style.display = 'block';
                loader.style.display = 'block';

                try {
                    const response = await fetch(`?search=${encodeURIComponent(query)}`);
                    const results = await response.json();

                    const container = document.getElementById('resultsContainer');
                    container.innerHTML = '';
                    results.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'result-item';
                        div.innerHTML = `
                            <a href="${item.url}">
                                <img src="${item.image}" alt="${item.title}">
                                <p>${item.title}</p>
                            </a>
                        `;
                        container.appendChild(div);
                    });
                } catch (error) {
                    alert('Ocorreu um erro ao buscar os resultados.');
                } finally {
                    loader.style.display = 'none';
                    loaderBackground.style.display = 'none';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchQuery');
                searchInput.addEventListener('keypress', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchAnimes();
                    }
                });
            });
        </script>
        <div id="loader-background"></div>
        <div id="loader">Carregando...</div>
    </head>
    <body>
        <img src="https://i.imgur.com/YFFnp7E.png" width="200">
        <div class="search-container">
            <input type="text" id="searchQuery" class="search-input" placeholder="Buscar animes...">
            <button class="search-button" onclick="searchAnimes()">Buscar</button>
            <br>
            <a href='/' class='home-link'>Home</a>
        </div>
        <div id="resultsContainer" class="results-container">
            <?php foreach ($initialResults as $item): ?>
                <div class="result-item">
                    <a href="<?= $item['url'] ?>">
                        <img src="<?= $item['image'] ?>" alt="<?= $item['title'] ?>">
                        <p><?= $item['title'] ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <br>
        <footer>
            <a href="https://github.com/MestreTM/AnFireAPI/" target="_blank">
                <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png" alt="GitHub Logo"> AnFireAPI - ver projeto no GitHub.
            </a>
        </footer>
    </body>
    </html>
    <?php
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchUrl = "https://animefire.plus/pesquisar/" . urlencode($searchTerm);
    $html = fetchHTML($searchUrl);
    $results = extractSearchResults($html);

    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

$html = fetchHTML("https://animefire.plus/animes-atualizados");
$initialResults = extractSearchResults($html);
generateSearchInterface($initialResults);
?>
