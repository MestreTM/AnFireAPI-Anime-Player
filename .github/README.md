
# AnFire API - API de Verificação de Episódios de Animes
![imagem](https://i.imgur.com/YFFnp7E.png)


Este projeto consiste em três componentes principais: `index.php`, `api.php` e `AnFire_Player.php`. Basicamente faz uma varredura do site e coleta todas as informações gostosas. Com ele você pode ver animes sem anuncios e com custo zero.
Agora com uma interface (index) para encontrar os novos animes recém adicionados ou buscá-los, tudo diretamente do seu navegador!

---


![imagem](https://i.imgur.com/Xw3IbMd.png)

---
# API Documentation

## Descrição
Esta API foi desenvolvida para fornecer informações relacionadas a animes. Ela permite buscar informações de animes utilizando parâmetros específicos, como `anime_slug` ou `anime_link`.

---

## Endpoints Disponíveis

### Buscar informações de um anime
**Descrição**: Retorna detalhes de um anime com base no slug ou link fornecido.

**Método**: GET  
**URL**: `localhost/api.php`  
**Parâmetros**:
- `api_key` (Obrigatório): Chave de acesso para autenticação. Defina uma chave ou senha de sua escolha. Deve ser igual na `api.php` e no `AnFire_Player.php`
- `anime_slug` (Opcional): O slug do anime, por exemplo, `bleach-sennen-kessen-hen---soukoku-tan-todos-os-episodios`.
- `anime_link` (Opcional): Link completo do anime, por exemplo, `https://animefire.plus/animes/kono-subarashii-sekai-ni-shukufuku-wo-3-dublado-todos-os-episodios`.

**Nota**: É obrigatório fornecer ao menos um parâmetro entre `anime_slug` e `anime_link`.

---

## Exemplos de Requisições

### Usando `anime_slug`
```bash
curl "localhost/api.php?api_key=SUA_API_KEY&anime_slug=bleach-sennen-kessen-hen---soukoku-tan-todos-os-episodios"
```

### Usando `anime_link`
```bash
curl "localhost/api.php?api_key=SUA_API_KEY&anime_link=https://animefire.plus/animes/kono-subarashii-sekai-ni-shukufuku-wo-3-dublado-todos-os-episodios"
```

---

## Exemplo de Retorno Completo

```json
{
    "anime_slug": "kono-subarashii-sekai-ni-shukufuku-wo-3-dublado",
    "anime_title": "Kono Subarashii Sekai ni Shukufuku wo! 3",
    "anime_title1": "KonoSuba: God's Blessing on This Wonderful World! 3",
    "anime_image": "https://animefire.plus/img/animes/kono-subarashii-sekai-ni-shukufuku-wo-3-large.webp",
    "anime_info": "A14, Aventura, Comedia, Fantasia",
    "anime_synopsis": "Kazuma Satou, ex-NEET e atual aventureiro reencarnado, finalmente retorna para casa apos o incidente na vila Crimson Demon. Ele e acompanhado por seus companheiros sempre confiaveis: a deusa egocentrica Aqua,...",
    "anime_score": "8.37",
    "anime_votes": "175,442 votos",
    "youtube_trailer": "https://www.youtube.com/embed/Meo3mO98huE?enablejsapi=1&wmode=opaque&autoplay=1",
    "episodes": [
        {
            "episode": 1,
            "data": [
                {
                    "url": "https://s2.lightspeedst.net/s2/mp4/kono-subarashii-sekai-ni-shukufuku-wo-3-dublado/sd/1.mp4",
                    "resolution": "360p",
                    "status": "ONLINE"
                }
            ]
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

## Descrição dos Campos

- **anime_slug**: Identificador único do anime em formato slug.
- **anime_title**: Título principal do anime.
- **anime_title1**: Título alternativo em inglês.
- **anime_image**: URL da imagem promocional do anime.
- **anime_info**: Informações categóricas do anime, como gêneros.
- **anime_synopsis**: Sinopse detalhada do anime.
- **anime_score**: Pontuação média do anime.
- **anime_votes**: Número de votos que compõem a pontuação.
- **youtube_trailer**: URL do trailer do anime no YouTube.
- **episodes**: Lista de episódios disponíveis, contendo:
  - **episode**: Número do episódio.
  - **data**: Detalhes sobre o episódio, incluindo:
    - **url**: Link para o vídeo do episódio.
    - **resolution**: Resolução do vídeo.
    - **status**: Status do vídeo (ex: ONLINE).
- **metadata**: Informações adicionais (não preenchidas no exemplo).
  - **op_start**: Não utilizado.
  - **op_end**: Não utilizado.
- **response**: Detalhes da resposta da API.
  - **status**: Código de status HTTP retornado.
  - **text**: Mensagem de texto associada ao status.


---

---

# AnFire_Player.php - O front end mais CREMOSO para testes <3

### 1. Reprodução de Vídeos
- Seleção de episódios diretamente na interface.
- Suporte para múltiplas resoluções (ex: 360p, 720p, 1080p), com base nos dados retornados pela API.
- Verificação do status dos vídeos (ex: ONLINE).

### 2. Exibição de Detalhes do Anime
- **Título principal e alternativo**: Exibidos de forma destacada na interface.
- **Imagem do anime**: Carregada dinamicamente através da URL retornada pela API.
- **Sinopse**: Apresentação detalhada da descrição do anime.
- **Informações adicionais**: Exibição de gêneros, pontuação e número de votos.

### 3. Testes com a API

- Na interface você encontrará um botão dedicado para visualizar a requisição completa.
- Campos de entrada para `api_key`, `anime_slug` e `anime_link`.
- Exibição dos dados retornados pela API em tempo real.
- Estrutura simples para validar a resposta e explorar os campos retornados.

---

## Requisitos

1. **Servidor Local ou WEB**:
   - O arquivo deve ser executado em um ambiente que suporte PHP, como XAMPP ou WAMP.
   - Para WEB você deve deixar privado. Não me responsabilizo legalmente caso faça o uso publico.
2. **Dependência da API**:
   - Certifique-se de que o arquivo `api.php` está configurada corretamente.
   - lembre-se de usar `http` ou `https` para fazer as requisições.

---

## Como Usar

1. Copie o arquivo `AnFire_Player.php` para o diretório do seu servidor local.
2. Acesse a URL: `http://localhost/AnFire_Player.php`.
3. Preencha os seguintes campos:
   - **API Key**: Defina uma chave ou senha de sua escolha. Deve ser igual na `api.php` e no `AnFire_Player.php`
   - **Dominio**: Insira corretamente seu `localhost` ou `dominio` (lembre de usar http ou https para as requisições).


## Exemplo de Uso em um projeto PHP
```php
<?php
$apiKey = "SUA_API_KEY";
$animeSlug = "bleach-sennen-kessen-hen---soukoku-tan-todos-os-episodios";

// Configurando a URL da API
$url = "http://localhost/api.php?api_key=" . $apiKey . "&anime_slug=" . $animeSlug;

// Realizando a requisição
$response = file_get_contents($url);

// Decodificando o JSON retornado
$data = json_decode($response, true);

// Exibindo os dados
if ($data) {
    echo "Título do Anime: " . $data['title'] . "\n";
    echo "Descrição: " . $data['description'] . "\n";
} else {
    echo "Falha ao obter dados do anime.";
}
?>
```

---

## Erros Comuns

### 1. Falta de `api_key`
- **Descrição**: Quando o parâmetro `api_key` não é fornecido.
- **Mensagem**: `{"error": "API Key is required"}`

### 2. Parâmetros inválidos ou vazio
- **Descrição**: Nenhum parâmetro válido fornecido (`anime_slug` ou `anime_link`).
- **Mensagem**: `{"error": "At least one parameter (anime_slug or anime_link) is required"}`

---

## Dependências e Configuração

1. Certifique-se de que o servidor web (como Apache ou Nginx) está configurado para executar arquivos PHP.
2. O arquivo `api.php` deve estar acessível no diretório raiz do servidor.
3. Mesmo rodando em localhost, utilize o protocolo http:// para fazer as requisições.

---

## Licença

Este projeto é distribuído sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

---
