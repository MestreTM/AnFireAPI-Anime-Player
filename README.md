
# AnFire API - API de Verificação de Episódios de Animes

Este projeto consiste em dois componentes principais: `api.php` e `anfiretester.php`. Juntos, eles oferecem uma API para consultar informações sobre episódios de animes e uma interface de teste para interagir com a API.

---

## o api.php

O `api.php` é o backend responsável por processar requisições relacionadas a animes.

### Funcionamento
- **Chave de API**: O acesso é protegido por uma chave de API definida no código (`$validApiKey`). 
  - **Altere a Chave padrão para a sua favorita**: `Minha_API_Key`. 
- **Parâmetros aceitos**:
  - `anime_slug`: Slug único do anime.
  - `anime_link`: Link para uma página de anime, do qual o slug será extraído.

### Exemplo de Uso
1. Por slug:
   ```sh
   curl "https://seusite.com/api.php?api_key=159753&anime_slug=spy-x-family"
   ```
2. Por link:
   ```sh
   curl "https://seusite.com/api.php?api_key=159753&anime_link=https://animefire.plus/animes/spy-x-family/"
   ```

### Retorno
A resposta é um JSON contendo:
- `anime_slug`: O slug do anime consultado.
- `episodes`: Lista de episódios disponíveis, com informações como URL, resolução e status.
- `metadata`: Informações adicionais sobre o processamento.
- `response`: Status da requisição.

Exemplo de resposta:
```json
{
  "anime_slug": "spy-x-family",
  "episodes": [
    {
      "episode": 1,
      "data": [
        {
          "url": "https://video.animefire.plus/.../ep1.mp4",
          "resolution": "720p",
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

## o anfiretester.php

O `anfiretester.php` é uma interface web para interagir com a API `api.php`.

### Funcionalidades
1. **Entrada de Dados**:
   - Aceita `anime_slug` ou `anime_link` para realizar buscas.
2. **Exibição de Resultados**:
   - Mostra episódios disponíveis, suas qualidades e status.
3. **Interação Avançada**:
   - Geração de playlists M3U para episódios selecionados ou todos os episódios.
   - Download direto de episódios.
   - Reprodução de vídeos no navegador (dependendo de CORS).
4. **Proteção com Senha** (Opcional):
   - Pode proteger o acesso ao Tester.
   - Configure a senha alterando `$password` e ative a proteção com `$requirePassword = true`.

### Exemplo de Uso
1. Abra o arquivo no navegador.
2. Insira o slug ou link do anime no campo de entrada.
3. Clique em "Buscar" para visualizar os resultados.

> [!WARNING]
> o CORS pode proibir a exibição do vídeo diretamente do seu servidor, e possivelmente só funcionará em localhost.
---

## Configurações

### Alterando a Chave da API
No arquivo `api.php`, modifique o valor de `$validApiKey` para a sua chave personalizada:
```php
$validApiKey = 'sua-chave-personalizada';
```

No arquivo `anfiretester.php`, atualize a constante `API_KEY` para corresponder à chave personalizada:
```php
define('API_KEY', 'sua-chave-personalizada');
```

### Proteção com Senha (opicional)
1. Altere o valor de `$password` no `anfiretester.php` para definir a senha desejada.
2. Ative a proteção configurando:
   ```php
   $requirePassword = true;
   ```

---

## Requisitos do Sistema

1. **Servidor**:
   - PHP 7.4 ou superior.
2. **Extensões Necessárias**:
   - `DOM`
   - `file_get_contents` habilitado para acessar URLs externas.

3. **Permissões**:
   - Certifique-se de que os arquivos tenham permissões de leitura adequadas no servidor.

---

## Troubleshooting

### Erro 403 - Invalid API Key
- Certifique-se de que a chave fornecida na URL corresponde à chave definida em `api.php`.

### Problemas com CORS
- Os vídeos podem ser bloqueados devido a restrições de CORS. Utilize um player offline como VLC para acessar diretamente as URLs.

### Requisições Externas Bloqueadas
- Confirme que o PHP está configurado para permitir `file_get_contents` com URLs externas.

---

## Licença

Este projeto é open-source. Consulte o [GitHub](https://github.com/MestreTM/AnFireAPI) para mais detalhes.

---
