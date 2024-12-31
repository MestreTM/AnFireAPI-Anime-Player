<?php
/*********************************************************************
 *                          AnFire API                               *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 *********************************************************************/
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Configurações do Banco de Dados e Cache
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

define('USE_CACHE', false);       // Habilita ou desabilita o uso de cache (BANCO DE DADOS)

// Configuração da API Key
define('API_KEY', 'SUA_API_AQUI');     


 // Estabelece a conexão com o banco de dados usando PDO.

function getDatabaseConnection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()]);
        exit;
    }
}

 // Cria a tabela 'api_cache' se ela não existir.

function createCacheTableIfNotExists(PDO $pdo): void
{
    $sql = "
    CREATE TABLE IF NOT EXISTS `api_cache` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `anime_slug` VARCHAR(255) DEFAULT NULL,
        `anime_link` VARCHAR(512) DEFAULT NULL,
        `response` LONGTEXT NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_anime_slug` (`anime_slug`),
        INDEX `idx_anime_link` (`anime_link`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Falha ao criar a tabela api_cache: ' . $e->getMessage()]);
        exit;
    }
}

if (!isset($_GET['api_key']) || $_GET['api_key'] !== API_KEY) {
    http_response_code(403);
    echo json_encode(['error' => 'API Key inválida ou ausente.']);
    exit;
}

if (!isset($_GET['anime_slug']) && !isset($_GET['anime_link'])) {
    echo json_encode(['error' => 'Parâmetro anime_slug ou anime_link é obrigatório.']);
    exit;
}

if (isset($_GET['anime_link'])) {
    $animeLink = $_GET['anime_link'];
    if (!preg_match('#^https://animefire\.plus/animes/.+#', $animeLink)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato inválido para anime_link. Deve ser "https://animefire.plus/animes/*"']);
        exit;
    }
}

// Obtém o parâmetro 'force', padrão para false. Isto faz a api buscar e regravar os dados mesmo já existindo no banco de dados.

$force = isset($_GET['force']) ? filter_var($_GET['force'], FILTER_VALIDATE_BOOLEAN) : false;

$animeSlug = $_GET['anime_slug'] ?? null;
$animeTitle = null;
$animeTitle1 = null;
$animeImage = null;
$animeInfo = null;
$animeSynopsis = null;
$animeScore = null;
$animeVotes = null;
$youtubeTrailer = null;

function getApiResponse(array $params, bool $useCache, bool $force): array
{
    if ($useCache && !$force) {
        $pdo = getDatabaseConnection();
        createCacheTableIfNotExists($pdo);

        $animeSlug = $params['anime_slug'] ?? null;
        $animeLink = $params['anime_link'] ?? null;

        $cachedResponse = getCachedResponse($pdo, $animeSlug, $animeLink);
        if ($cachedResponse) {
            return json_decode($cachedResponse, true);
        }
    }

    // Processa a solicitação diretamente, sem cache
    $response = processApiRequest($params);

    if ($useCache && !isset($response['error'])) {
        $pdo = getDatabaseConnection();
        storeCacheResponse($pdo, $params['anime_slug'] ?? null, $params['anime_link'] ?? null, json_encode($response));
    }

    return $response;
}


function getCachedResponse(PDO $pdo, ?string $animeSlug, ?string $animeLink): ?string
{
    try {
        $sql = "SELECT response FROM api_cache WHERE anime_slug = :anime_slug OR anime_link = :anime_link LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anime_slug' => $animeSlug,
            ':anime_link' => $animeLink,
        ]);
        $result = $stmt->fetch();
        return $result ? $result['response'] : null;
    } catch (Exception $e) {
        error_log('Erro ao obter resposta do cache: ' . $e->getMessage());
        return null;
    }
}

function storeCacheResponse(PDO $pdo, ?string $animeSlug, ?string $animeLink, string $response): void
{
    try {
        $sql = "SELECT id FROM api_cache WHERE anime_slug = :anime_slug OR anime_link = :anime_link LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':anime_slug' => $animeSlug,
            ':anime_link' => $animeLink,
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            $updateSql = "UPDATE api_cache SET response = :response, updated_at = NOW() WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':response' => $response,
                ':id' => $existing['id'],
            ]);
        } else {
            $insertSql = "INSERT INTO api_cache (anime_slug, anime_link, response, created_at, updated_at) VALUES (:anime_slug, :anime_link, :response, NOW(), NOW())";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                ':anime_slug' => $animeSlug,
                ':anime_link' => $animeLink,
                ':response' => $response,
            ]);
        }
    } catch (Exception $e) {
        error_log('Erro ao armazenar resposta no cache: ' . $e->getMessage());
    }
}

function processApiRequest(array $params): array
{
    $animeSlug = $params['anime_slug'] ?? null;
    $animeLink = $params['anime_link'] ?? null;

    global $animeTitle, $animeTitle1, $animeImage, $animeInfo, $animeSynopsis, $animeScore, $animeVotes, $youtubeTrailer;

    if (!$animeSlug && isset($animeLink)) {
        $animeSlug = fetchAnimeSlug($animeLink);
        $animeTitle = cleanText(fetchAnimeTitle($animeLink));
        $animeTitle1 = cleanText(fetchAnimeTitle1($animeLink));
        $animeImage = fetchAnimeImage($animeLink);
        $animeInfo = cleanText(fetchAnimeInfo($animeLink));
        $animeSynopsis = cleanText(fetchAnimeSynopsis($animeLink));
        $animeScore = fetchAnimeScore($animeLink);
        $animeVotes = fetchAnimeVotes($animeLink);
        $youtubeTrailer = fetchYoutubeTrailer($animeLink);

        if (!$animeSlug) {
            return ['error' => 'Não foi possível extrair anime_slug do anime_link.'];
        }
    }

    $episodes = testEpisodes($animeSlug);

    return [
        'anime_slug' => $animeSlug,
        'anime_title' => $animeTitle,
        'anime_title1' => $animeTitle1,
        'anime_image' => $animeImage,
        'anime_info' => $animeInfo,
        'anime_synopsis' => $animeSynopsis,
        'anime_score' => $animeScore,
        'anime_votes' => $animeVotes,
        'youtube_trailer' => $youtubeTrailer,
        'episodes' => $episodes,
        'metadata' => [
            'op_start' => null,
            'op_end' => null
        ],
        'response' => [
            'status' => '200',
            'text' => 'OK'
        ]
    ];
}

$response = getApiResponse($_GET, USE_CACHE, $force);

// Define o código de resposta apropriado em caso de erro
if (isset($response['error'])) {
    http_response_code(400);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

function fetchAnimeSlug(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//div[contains(@class, 'div_video_list')]//a");

    foreach ($nodes as $node) {
        $href = $node->getAttribute('href');
        if (preg_match('#/animes/([^/]+)/#', $href, $matches)) {
            return $matches[1];
        }
    }

    return null;
}

function fetchAnimeTitle(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $titleNode = $xpath->query("//h1[contains(@class, 'quicksand400')]")->item(0);

    return $titleNode ? trim($titleNode->nodeValue) : null;
}

function fetchAnimeTitle1(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $titleNode = $xpath->query("//h6[contains(@class, 'text-gray')]")->item(0);

    return $titleNode ? trim($titleNode->nodeValue) : null;
}

function fetchAnimeImage(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $imageNode = $xpath->query("//div[contains(@class, 'sub_animepage_img')]//img")->item(0);

    return $imageNode ? $imageNode->getAttribute('data-src') : null;
}

function fetchAnimeInfo(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $infoNodes = $xpath->query("//div[contains(@class, 'animeInfo')]//a");

    $infoTexts = [];
    foreach ($infoNodes as $node) {
        $infoTexts[] = trim($node->nodeValue);
    }

    return implode(", ", $infoTexts);
}

function fetchAnimeSynopsis(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $synopsisNode = $xpath->query("//div[contains(@class, 'divSinopse')]//span[contains(@class, 'spanAnimeInfo')]")->item(0);

    return $synopsisNode ? trim($synopsisNode->nodeValue) : null;
}

function fetchAnimeScore(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $scoreNode = $xpath->query("//h4[@id='anime_score']")->item(0);

    return $scoreNode ? trim($scoreNode->nodeValue) : null;
}

function fetchAnimeVotes(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $votesNode = $xpath->query("//h6[@id='anime_votos']")->item(0);

    return $votesNode ? trim($votesNode->nodeValue) : null;
}

function fetchYoutubeTrailer(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $trailerNode = $xpath->query("//div[@id='iframe-trailer']//iframe")->item(0);

    return $trailerNode ? $trailerNode->getAttribute('data-src') : null;
}

 //Testa os episódios do anime até o máximo especificado. o presset é 200

function testEpisodes(string $animeSlug, int $maxEpisodes = 200): array
{
    $results = [];

    for ($episode = 1; $episode <= $maxEpisodes; $episode++) {
        $url = "https://animefire.plus/video/$animeSlug/$episode";
        $response = @file_get_contents($url);

        if ($response === false) {
            break;
        }

        $json = json_decode($response, true);

        if (isset($json['response']['status']) && $json['response']['status'] === "500") {
            break;
        }

        if (!empty($json['data'])) {
            $hasGoogleVideo = false;
            foreach ($json['data'] as $item) {
                if (strpos($item['src'], 'googlevideo.com') !== false) {
                    $hasGoogleVideo = true;
                    break;
                }
            }

            if ($hasGoogleVideo) {
                $episodePageUrl = "https://animefire.plus/animes/$animeSlug/$episode";
                $bloggerUrl = fetchBloggerIframeUrl($episodePageUrl);

                if ($bloggerUrl) {
                    $formattedData = array_map(function ($item) use ($bloggerUrl) {
                        if (strpos($item['src'], 'googlevideo.com') !== false) {
                            return [
                                'url' => $bloggerUrl,
                                'resolution' => $item['label'],
                                'status' => 'ONLINE'
                            ];
                        } else {
                            return [
                                'url' => formatUrl($item['src']),
                                'resolution' => $item['label'],
                                'status' => 'ONLINE'
                            ];
                        }
                    }, $json['data']);
                } else {
                    $formattedData = array_map(function ($item) {
                        if (strpos($item['src'], 'googlevideo.com') !== false) {
                            return [
                                'url' => null,
                                'resolution' => $item['label'],
                                'status' => 'OFFLINE'
                            ];
                        } else {
                            return [
                                'url' => formatUrl($item['src']),
                                'resolution' => $item['label'],
                                'status' => 'ONLINE'
                            ];
                        }
                    }, $json['data']);
                }
            } else {
                $formattedData = array_map(function ($item) {
                    return [
                        'url' => formatUrl($item['src']),
                        'resolution' => $item['label'],
                        'status' => 'ONLINE'
                    ];
                }, $json['data']);
            }

            $results[] = ['episode' => $episode, 'data' => $formattedData];
        } else {
            $results[] = ['episode' => $episode, 'data' => [], 'status' => 'OFFLINE'];
        }
    }

    return $results;
}

function fetchBloggerIframeUrl(string $episodePageUrl): ?string
{
    $html = @file_get_contents($episodePageUrl);
    if ($html === false) return null;

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);
    $iframeNode = $xpath->query("//iframe[contains(@src, 'blogger.com')]")->item(0);

    return $iframeNode ? $iframeNode->getAttribute('src') : null;
}

/**
 * Limpa o texto removendo caracteres indesejados.
 *
 * @param string $text
 * @return string
 */
function cleanText(string $text): string
{
    $unwanted = [
        'ç' => 'c', 'Ç' => 'C',
        'á' => 'a', 'Á' => 'A',
        'à' => 'a', 'À' => 'A',
        'ã' => 'a', 'Ã' => 'A',
        'â' => 'a', 'Â' => 'A',
        'é' => 'e', 'É' => 'E',
        'ê' => 'e', 'Ê' => 'E',
        'í' => 'i', 'Í' => 'I',
        'ó' => 'o', 'Ó' => 'O',
        'õ' => 'o', 'Õ' => 'O',
        'ô' => 'o', 'Ô' => 'O',
        'ú' => 'u', 'Ú' => 'U',
        'ü' => 'u', 'Ü' => 'U'
    ];

    return strtr($text, $unwanted);
}

/**
 * Formata a URL removendo barras invertidas.
 *
 * @param string $url
 * @return string
 */
function formatUrl(string $url): string
{
    return str_replace(["\\/", "\\"], "/", $url);
}
?>
