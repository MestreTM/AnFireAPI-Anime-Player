<?php
/*********************************************************************
 *                          AnFire API                               *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 *********************************************************************/
 
header('Content-Type: application/json');

// Defina a chave que a API espera na query string
$validApiKey = 'SUA_API_AQUI';

// Valida a chave
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $validApiKey) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid or missing API Key.']);
    exit;
}

// Se não houver anime_slug ou anime_link, retorna erro
if (!isset($_GET['anime_slug']) && !isset($_GET['anime_link'])) {
    echo json_encode(['error' => 'anime_slug or anime_link parameter is required.']);
    exit;
}

// Valida o formato do link
if (isset($_GET['anime_link'])) {
    $animeLink = $_GET['anime_link'];
    if (!preg_match('#^https://animefire\.plus/animes/.+#', $animeLink)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid anime_link format. The link must match "https://animefire.plus/animes/*"']);
        exit;
    }
}

// Obtém slug diretamente ou extrai de um link
$animeSlug = $_GET['anime_slug'] ?? null;
$animeTitle = null;
$animeTitle1 = null;
$animeImage = null;
$animeInfo = null;
$animeSynopsis = null;
$animeScore = null;
$animeVotes = null;
$youtubeTrailer = null;

if (!$animeSlug && isset($_GET['anime_link'])) {
    $animeLink = $_GET['anime_link'];
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
        echo json_encode(['error' => 'Unable to extract anime_slug from anime_link.']);
        exit;
    }
}

// Executa a verificação
$episodes = testEpisodes($animeSlug);

$response = [
    'anime_slug' => $animeSlug,

    'anime_title' => $animeTitle,

    'anime_title1' => $animeTitle1,

    'anime_image' => $animeImage,

    'anime_info' => $animeInfo,

    'anime_synopsis' => $animeSynopsis,

    'anime_score' => $animeScore,

    'anime_votes' => $animeVotes,

    'youtube_trailer' => $youtubeTrailer,

    'episodes'   => $episodes,

    'metadata'   => [
        'op_start' => null,

        'op_end'   => null
    ],

    'response'   => [
        'status' => '200',

        'text'   => 'OK'
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Funções abaixo
function fetchAnimeSlug(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

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

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $titleNode = $xpath->query("//h1[contains(@class, 'quicksand400')]")->item(0);

    return $titleNode ? trim($titleNode->nodeValue) : null;
}

function fetchAnimeTitle1(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $titleNode = $xpath->query("//h6[contains(@class, 'text-gray')]")->item(0);

    return $titleNode ? trim($titleNode->nodeValue) : null;
}

function fetchAnimeImage(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $imageNode = $xpath->query("//div[contains(@class, 'sub_animepage_img')]//img")->item(0);

    return $imageNode ? $imageNode->getAttribute('data-src') : null;
}

function fetchAnimeInfo(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

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

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $synopsisNode = $xpath->query("//div[contains(@class, 'divSinopse')]//span[contains(@class, 'spanAnimeInfo')]")->item(0);

    return $synopsisNode ? trim($synopsisNode->nodeValue) : null;
}

function fetchAnimeScore(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $scoreNode = $xpath->query("//h4[@id='anime_score']")->item(0);

    return $scoreNode ? trim($scoreNode->nodeValue) : null;
}

function fetchAnimeVotes(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $votesNode = $xpath->query("//h6[@id='anime_votos']")->item(0);

    return $votesNode ? trim($votesNode->nodeValue) : null;
}

function fetchYoutubeTrailer(string $animeLink): ?string
{
    $html = @file_get_contents($animeLink);

    if ($html === false) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    $trailerNode = $xpath->query("//div[@id='iframe-trailer']//iframe")->item(0);

    return $trailerNode ? $trailerNode->getAttribute('data-src') : null;
}

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
            $formattedData = array_map(function ($item) {
                return [
                    'url'        => formatUrl($item['src']),

                    'resolution' => $item['label'],

                    'status'     => 'ONLINE'
                ];
            }, $json['data']);

            $results[] = ['episode' => $episode, 'data' => $formattedData];
        } else {
            $results[] = ['episode' => $episode, 'data' => [], 'status' => 'OFFLINE'];
        }
    }

    return $results;
}

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

function formatUrl(string $url): string
{
    return str_replace(["\\/", "\\"], "/", $url);
}
