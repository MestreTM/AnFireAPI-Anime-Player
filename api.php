<?php
/*********************************************************************
 *                          AnFire API                               *
 * ----------------------------------------------------------------- *
 * Nome do Projeto: API de Verificação de Episódios de Animes        *
 * Versão: 1.0                                                       *
 * Desenvolvedor: MestreTM                                           *
 * Data de Criação: 28/12/2024                                       *
 * ----------------------------------------------------------------- *
 * FUNÇÃO:                                                           *
 * Esta API foi projetada para verificar a disponibilidade de        *
 * episódios de animes no site AnimeFire. Com suporte para testar    *
 * até 200 episódios, ela retorna informações detalhadas em JSON.    *
 * ----------------------------------------------------------------- *
 * COMO UTILIZAR:                                                    *
 * Github do projeto: https://github.com/MestreTM/AnFireAPI/         *
 * ----------------------------------------------------------------- *
 *                            MESTRETM                               *
 *********************************************************************/

header('Content-Type: application/json');

// Configuração para restringir a API a um host específico (opcional).
$restrictToHost = false;
$allowedHost = 'example.com';

if ($restrictToHost && (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] !== $allowedHost)) {
    echo json_encode([
        'error' => 'Access restricted to a specific host.'
    ]);
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'error' => 'Only GET requests are allowed.'
    ]);
    http_response_code(405);
    exit;
}

if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mozilla|Chrome|Safari|Edge|Opera/', $_SERVER['HTTP_USER_AGENT'])) {
    echo json_encode([
        'error' => 'Direct access from browsers is not allowed.'
    ]);
    http_response_code(403);
    exit;
}

function fetchAnimeSlug($animeLink) {
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

function testEpisodes($animeSlug, $maxEpisodes = 200) {
    $results = [];

    for ($episode = 1; $episode <= $maxEpisodes; $episode++) {
        $url = "https://animefire.plus/video/$animeSlug/$episode";
        $response = file_get_contents($url);

        if ($response === false) {
            break;
        }

        $json = json_decode($response, true);

        if (isset($json['response']['status']) && $json['response']['status'] === "500") {
            break;
        }

        if (!empty($json['data'])) {
            $formattedData = array_map(function ($item) use ($episode) {
                return [
                    'url' => formatUrl($item['src']),
                    'resolution' => $item['label'],
                    'status' => 'ONLINE'
                ];
            }, $json['data']);

            $results[] = [
                'episode' => $episode,
                'data' => $formattedData
            ];
        } else {
            $results[] = [
                'episode' => $episode,
                'data' => [],
                'status' => 'OFFLINE'
            ];
        }
    }

    return $results;
}

function formatUrl($url) {
    return str_replace(["\\/", "\\"], "/", $url);
}

if (!isset($_GET['anime_slug']) && !isset($_GET['anime_link'])) {
    echo json_encode([
        'error' => 'anime_slug or anime_link parameter is required.'
    ]);
    exit;
}

$animeSlug = $_GET['anime_slug'] ?? null;

if (!$animeSlug && isset($_GET['anime_link'])) {
    $animeSlug = fetchAnimeSlug($_GET['anime_link']);

    if (!$animeSlug) {
        echo json_encode([
            'error' => 'Unable to extract anime_slug from the provided anime_link.'
        ]);
        exit;
    }
}

$episodes = testEpisodes($animeSlug);

$response = [
    'anime_slug' => $animeSlug,
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

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
