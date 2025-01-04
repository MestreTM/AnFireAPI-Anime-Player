const { DataTypes } = require('sequelize');
const sequelize = require('../sequelize');

const ApiCache = sequelize.define('api_cache', {
  id: {
    type: DataTypes.INTEGER.UNSIGNED,
    autoIncrement: true,
    primaryKey: true
  },
  anime_slug: {
    type: DataTypes.STRING(255),
    allowNull: true
  },
  anime_link: {
    type: DataTypes.STRING(512),
    allowNull: true
  },
  response: {
    type: DataTypes.TEXT('long'),
    allowNull: false
  },
  created_at: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  },
  updated_at: {
    type: DataTypes.DATE,
    defaultValue: DataTypes.NOW
  }
}, {
  timestamps: false,
  indexes: [
    { fields: ['anime_slug'] },
    { fields: ['anime_link'] }
  ]
});

module.exports = ApiCache;
