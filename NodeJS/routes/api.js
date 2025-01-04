// routes/api.js
const express = require('express');
const router = express.Router();
const ApiCache = require('../models/api_cache');
const axios = require('axios');
const cheerio = require('cheerio');

// Middleware para tratar requisições GET na rota principal
router.get('/', async (req, res) => {
  const { api_key, anime_slug, anime_link, force, update } = req.query;

  // Validação da API Key
  if (api_key !== process.env.API_KEY) {
    return res.status(403).json({ error: 'API Key inválida ou ausente.' });
  }

  if (!anime_slug && !anime_link) {
    return res.status(400).json({ error: 'Parâmetro anime_slug ou anime_link é obrigatório.' });
  }

  if (anime_link) {
    const linkPattern = /^https:\/\/animefire\.plus\/animes\/.+$/;
    if (!linkPattern.test(anime_link)) {
      return res.status(400).json({ error: 'Formato inválido para anime_link. Deve ser "https://animefire.plus/animes/*"' });
    }
  }

  // Parse dos parâmetros booleanos
  const forceUpdate = force === 'true';
  const incrementalUpdate = update === 'true';

  try {
    let responseData;

    if (process.env.USE_CACHE === 'false') {
      // Verifica se existe cache
      const cached = await ApiCache.findOne({
        where: {
          [anime_slug ? 'anime_slug' : 'anime_link']: anime_slug || anime_link
        }
      });

      if (cached && !forceUpdate) {
        let cachedData = JSON.parse(cached.response);

        if (incrementalUpdate) {
          // Lógica de atualização incremental
          let lastEpisode = 0;
          if (cachedData.episodes && cachedData.episodes.length > 0) {
            lastEpisode = Math.max(...cachedData.episodes.map(ep => ep.episode));
          }

          const newEpisodes = await fetchEpisodes(cachedData.anime_slug, lastEpisode + 1);

          if (newEpisodes.length > 0) {
            cachedData.episodes = cachedData.episodes.concat(newEpisodes);
            cachedData.new_episodes = newEpisodes.map(ep => ep.episode);

            // Atualiza o cache
            cached.response = JSON.stringify(cachedData);
            await cached.save();

            responseData = cachedData;
          } else {
            cachedData.new_episodes = [];
            responseData = cachedData;
          }
        } else {
          // Retorna dados do cache
          responseData = cachedData;
        }
      }

      if (!cached || forceUpdate) {
        // Busca dados atualizados
        responseData = await processApiRequest({ anime_slug, anime_link });
        if (!responseData.error && process.env.USE_CACHE === 'true') {
          // Armazena ou atualiza o cache
          if (cached) {
            cached.response = JSON.stringify(responseData);
            await cached.save();
          } else {
            await ApiCache.create({
              anime_slug: anime_slug || null,
              anime_link: anime_link || null,
              response: JSON.stringify(responseData)
            });
          }
        }
      }
    } else {
      // Sem uso de cache, processa diretamente
      responseData = await processApiRequest({ anime_slug, anime_link });
    }

    // Trata a resposta
    if (responseData.error) {
      res.status(400).json(responseData);
    } else {
      res.json(responseData);
    }
  } catch (error) {
    console.error('Erro ao processar a requisição:', error);
    res.status(500).json({ error: 'Erro interno do servidor.' });
  }
});

// Função para processar a requisição da API
async function processApiRequest(params) {
  let { anime_slug, anime_link } = params;
  let animeData = {};

  if (!anime_slug && anime_link) {
    // Busca dados a partir do anime_link
    anime_slug = await fetchAnimeSlug(anime_link);
    if (!anime_slug) {
      return { error: 'Não foi possível extrair anime_slug do anime_link.' };
    }

    animeData.anime_slug = anime_slug;
    animeData.anime_title = cleanText(await fetchAnimeTitle(anime_link));
    animeData.anime_title1 = cleanText(await fetchAnimeTitle1(anime_link));
    animeData.anime_image = await fetchAnimeImage(anime_link);
    animeData.anime_info = cleanText(await fetchAnimeInfo(anime_link));
    animeData.anime_synopsis = cleanText(await fetchAnimeSynopsis(anime_link));
    animeData.anime_score = await fetchAnimeScore(anime_link);
    animeData.anime_votes = await fetchAnimeVotes(anime_link);
    animeData.youtube_trailer = await fetchYoutubeTrailer(anime_link);
  } else {
    // Caso apenas anime_slug seja fornecido, adapte conforme necessário
    animeData.anime_slug = anime_slug;
    // Pode ser necessário buscar anime_link a partir do anime_slug ou outras informações
  }

  // Busca os episódios
  animeData.episodes = await fetchEpisodes(anime_slug);

  // Adiciona metadados e status
  animeData.metadata = {
    op_start: null,
    op_end: null
  };
  animeData.response = {
    status: '200',
    text: 'OK'
  };

  return animeData;
}

// Função para buscar episódios
async function fetchEpisodes(animeSlug, startEpisode = 1, maxEpisodes = 200) {
  const results = [];

  for (let episode = startEpisode; episode <= maxEpisodes; episode++) {
    const url = `https://animefire.plus/video/${animeSlug}/${episode}`;
    try {
      const { data } = await axios.get(url);
      const json = data;

      if (json.response && json.response.status === "500") {
        break;
      }

      if (json.data && json.data.length > 0) {
        let hasGoogleVideo = json.data.some(item => item.src.includes('googlevideo.com'));

        let formattedData;
        if (hasGoogleVideo) {
          const episodePageUrl = `https://animefire.plus/animes/${animeSlug}/${episode}`;
          const bloggerUrl = await fetchBloggerIframeUrl(episodePageUrl);

          if (bloggerUrl) {
            formattedData = json.data.map(item => {
              if (item.src.includes('googlevideo.com')) {
                return {
                  url: bloggerUrl,
                  resolution: item.label,
                  status: 'ONLINE'
                };
              } else {
                return {
                  url: formatUrl(item.src),
                  resolution: item.label,
                  status: 'ONLINE'
                };
              }
            });
          } else {
            formattedData = json.data.map(item => {
              if (item.src.includes('googlevideo.com')) {
                return {
                  url: null,
                  resolution: item.label,
                  status: 'OFFLINE'
                };
              } else {
                return {
                  url: formatUrl(item.src),
                  resolution: item.label,
                  status: 'ONLINE'
                };
              }
            });
          }
        } else {
          formattedData = json.data.map(item => ({
            url: formatUrl(item.src),
            resolution: item.label,
            status: 'ONLINE'
          }));
        }

        results.push({ episode, data: formattedData });
      } else {
        results.push({ episode, data: [], status: 'OFFLINE' });
      }
    } catch (error) {
      console.error(`Erro ao buscar o episódio ${episode}:`, error);
      break;
    }
  }

  return results;
}

// Função para buscar a URL do iframe do Blogger
async function fetchBloggerIframeUrl(episodePageUrl) {
  try {
    const { data } = await axios.get(episodePageUrl);
    const $ = cheerio.load(data);
    const iframe = $("iframe[src*='blogger.com']").first();
    return iframe.attr('src') || null;
  } catch (error) {
    console.error(`Erro ao buscar iframe do Blogger em ${episodePageUrl}:`, error);
    return null;
  }
}

// Funções para buscar dados do anime
async function fetchAnimeSlug(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const links = $("div.div_video_list a");

    for (let i = 0; i < links.length; i++) {
      const href = $(links[i]).attr('href');
      const match = href.match(/\/animes\/([^/]+)\//);
      if (match) {
        return match[1];
      }
    }

    return null;
  } catch (error) {
    console.error(`Erro ao buscar anime_slug de ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeTitle(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const title = $("h1.quicksand400").first().text().trim();
    return title || null;
  } catch (error) {
    console.error(`Erro ao buscar título do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeTitle1(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const title1 = $("h6.text-gray").first().text().trim();
    return title1 || null;
  } catch (error) {
    console.error(`Erro ao buscar título alternativo do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeImage(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const img = $("div.sub_animepage_img img").first();
    return img.attr('data-src') || null;
  } catch (error) {
    console.error(`Erro ao buscar imagem do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeInfo(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const infoNodes = $("div.animeInfo a");
    let infoTexts = [];
    infoNodes.each((i, elem) => {
      infoTexts.push($(elem).text().trim());
    });
    return infoTexts.join(", ");
  } catch (error) {
    console.error(`Erro ao buscar informações do anime em ${animeLink}:`, error);
    return '';
  }
}

async function fetchAnimeSynopsis(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const synopsis = $("div.divSinopse span.spanAnimeInfo").first().text().trim();
    return synopsis || null;
  } catch (error) {
    console.error(`Erro ao buscar sinopse do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeScore(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const score = $("h4#anime_score").first().text().trim();
    return score || null;
  } catch (error) {
    console.error(`Erro ao buscar score do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchAnimeVotes(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const votes = $("h6#anime_votos").first().text().trim();
    return votes || null;
  } catch (error) {
    console.error(`Erro ao buscar votos do anime em ${animeLink}:`, error);
    return null;
  }
}

async function fetchYoutubeTrailer(animeLink) {
  try {
    const { data } = await axios.get(animeLink);
    const $ = cheerio.load(data);
    const iframe = $("#iframe-trailer iframe").first();
    return iframe.attr('src') || null;
  } catch (error) {
    console.error(`Erro ao buscar trailer do YouTube em ${animeLink}:`, error);
    return null;
  }
}

// Função para limpar texto
function cleanText(text) {
  if (!text) return '';
  const replacements = {
    'ç': 'c', 'Ç': 'C',
    'á': 'a', 'Á': 'A',
    'à': 'a', 'À': 'A',
    'ã': 'a', 'Ã': 'A',
    'â': 'a', 'Â': 'A',
    'é': 'e', 'É': 'E',
    'ê': 'e', 'Ê': 'E',
    'í': 'i', 'Í': 'I',
    'ó': 'o', 'Ó': 'O',
    'õ': 'o', 'Õ': 'O',
    'ô': 'o', 'Ô': 'O',
    'ú': 'u', 'Ú': 'U',
    'ü': 'u', 'Ü': 'U'
  };
  return text.split('').map(char => replacements[char] || char).join('');
}

// Função para formatar URLs
function formatUrl(url) {
  return url.replace(/\\\//g, '/').replace(/\\/g, '/');
}

module.exports = router;
