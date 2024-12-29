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

// Obtém slug diretamente ou extrai de um link
$animeSlug = $_GET['anime_slug'] ?? null;
if (!$animeSlug && isset($_GET['anime_link'])) {
    $animeSlug = fetchAnimeSlug($_GET['anime_link']);
    if (!$animeSlug) {
        echo json_encode(['error' => 'Unable to extract anime_slug from anime_link.']);
        exit;
    }
}

// Executa a verificação
$episodes = testEpisodes($animeSlug);
$response = [
    'anime_slug' => $animeSlug,
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

function formatUrl(string $url): string
{
    return str_replace(["\\/", "\\"], "/", $url);
}
