-- =============================================
-- SISTEMA DE CITAÇÕES ALEATÓRIAS
-- =============================================

-- Tabela de citações
CREATE TABLE IF NOT EXISTS `citacoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `texto`        TEXT          NOT NULL,
  `autor`        VARCHAR(255)  DEFAULT NULL,
  `fonte`        VARCHAR(255)  DEFAULT NULL,
  `categoria`    VARCHAR(100)  DEFAULT NULL,
  `tags`         VARCHAR(500)  DEFAULT NULL,
  `idioma`       VARCHAR(10)   DEFAULT 'pt-BR',
  `ativo`        TINYINT(1)    DEFAULT 1,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_autor` (`autor`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;