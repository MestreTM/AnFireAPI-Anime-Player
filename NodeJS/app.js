// app.js

const express = require('express');
const sequelize = require('./sequelize'); // Importa a configuração do Sequelize
const apiRouter = require('./routes/api'); // Importa as rotas da API
require('dotenv').config(); // Carrega as variáveis de ambiente

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware para parsear JSON (caso seja necessário no futuro)
app.use(express.json());

// Define a rota principal da API
app.use('/api', apiRouter);

// Sincroniza o banco de dados e inicia o servidor
sequelize.sync()
  .then(() => {
    console.log('Banco de dados sincronizado com sucesso.');
    app.listen(PORT, () => {
      console.log(`Servidor rodando na porta ${PORT}`);
    });
  })
  .catch(err => {
    console.error('Erro ao sincronizar o banco de dados:', err);
  });

