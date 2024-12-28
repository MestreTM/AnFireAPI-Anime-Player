
# API de Verificação de Episódios de Animes

Esta API permite buscar informações sobre episódios de animes hospedados no site **AnimeFire**. A API pode testar até 200 episódios para determinar sua disponibilidade e suas respectivas resoluções.

---

## Recursos Principais

- **Restringir a API por Host**: Possibilidade de configurar a API para responder apenas a um domínio específico.
- **Obtenção de Slug Automática**: Extração automática do `anime_slug` a partir de um link completo de anime.
- **Testar Disponibilidade de Episódios**: Verifica se os episódios estão disponíveis e retorna informações como URL, resolução e status.
- **JSON Formatado**: Retorno dos dados em formato JSON, legível e sem barras invertidas extras nos links.

---

## Configuração

No topo do código, você pode configurar as seguintes opções:

```php
// Configuração para restringir a API a um host específico (opcional)
$restrictToHost = false; // Defina como true para ativar a restrição
$allowedHost = 'example.com'; // Substitua pelo host permitido
```

- **$restrictToHost**: Define se a API deve responder apenas para um host específico.
- **$allowedHost**: Caso a restrição esteja ativada, configure aqui o domínio permitido.

---

## Parâmetros Aceitos

A API aceita os seguintes parâmetros na URL:

- **anime_slug**: O slug único do anime (obrigatório se `anime_link` não for fornecido).
- **anime_link**: URL completa do anime. A API extrai automaticamente o slug (obrigatório se `anime_slug` não for fornecido).

Se nenhum dos dois parâmetros for passado, a API retornará um erro.

---

## Estrutura de Resposta

Exemplo de resposta JSON:

```json
{
    "anime_slug": "spy-x-family-season-2-dublado",
    "episodes": [
        {
            "episode": 1,
            "data": [
                {
                    "url": "https://s2.lightspeedst.net/s1/mp4/spy-x-family-season-2-dublado/sd/1.mp4",
                    "resolution": "360p",
                    "status": "ONLINE"
                },
                {
                    "url": "https://s2.lightspeedst.net/s1/mp4/spy-x-family-season-2-dublado/hd/1.mp4",
                    "resolution": "720p",
                    "status": "ONLINE"
                }
            ]
        },
        {
            "episode": 2,
            "data": []
        }
    ],
    "metadata": {
        "op_start": null,
        "op_end": null
    },
    "response": {
        "status": "200",
        "text": "OK"
    }
}
```

---

## Exemplo de Comandos `curl`

### Testando com `anime_slug`

```bash
curl "https://seu-dominio.com/api.php?anime_slug=spy-x-family-season-2-dublado"
```

### Testando com `anime_link`

```bash
curl "https://seu-dominio.com/api.php?anime_link=https://animefire.plus/animes/spy-x-family-season-2-dublado-todos-os-episodios"
```

### Com Restrição de Host (se ativada)

Certifique-se de que o domínio do host esteja configurado corretamente para que a API funcione.

---

## Exemplo de Uso com PHP

### Testando a API com `anime_slug`

```php
$url = "https://seu-dominio.com/api.php?anime_slug=spy-x-family-season-2-dublado";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data) {
    echo "Anime Slug: " . $data['anime_slug'] . "\n";
    foreach ($data['episodes'] as $episode) {
        echo "Episódio: " . $episode['episode'] . "\n";
        foreach ($episode['data'] as $info) {
            echo "  URL: " . $info['url'] . "\n";
            echo "  Resolução: " . $info['resolution'] . "\n";
            echo "  Status: " . $info['status'] . "\n";
        }
    }
}
```

### Testando a API com `anime_link`

```php
$url = "https://seu-dominio.com/api.php?anime_link=https://animefire.plus/animes/spy-x-family-season-2-dublado-todos-os-episodios";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data) {
    echo "Anime Slug: " . $data['anime_slug'] . "\n";
    foreach ($data['episodes'] as $episode) {
        echo "Episódio: " . $episode['episode'] . "\n";
        foreach ($episode['data'] as $info) {
            echo "  URL: " . $info['url'] . "\n";
            echo "  Resolução: " . $info['resolution'] . "\n";
            echo "  Status: " . $info['status'] . "\n";
        }
    }
}
```

---

## Erros Comuns e Como Resolver

### 1. **Erro: `anime_slug or anime_link parameter is required.`**
   - Certifique-se de passar pelo menos um dos dois parâmetros na URL.

### 2. **Erro: `Unable to extract anime_slug from the provided anime_link.`**
   - Verifique se o link do anime está correto e acessível.

### 3. **Erro: `Access restricted to a specific host.`**
   - Ative ou ajuste o domínio permitido na configuração do código.

---

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues ou enviar pull requests no repositório do GitHub.
