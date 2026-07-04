-- =============================================
-- SISTEMA DE REFERÊNCIAS BIBLIOGRÁFICAS
-- =============================================

-- Tabela de referências bibliográficas
CREATE TABLE IF NOT EXISTS `referencias` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `tipo`         ENUM('livro','artigo','site','apocrifo','video','outro') DEFAULT 'livro',
  `titulo`       VARCHAR(255)  NOT NULL,
  `autor`        VARCHAR(255)  DEFAULT NULL,
  `ano`          INT           DEFAULT NULL,
  `editora`      VARCHAR(255)  DEFAULT NULL,
  `url`          VARCHAR(500)  DEFAULT NULL,
  `isbn`         VARCHAR(20)   DEFAULT NULL,
  `doi`          VARCHAR(100)  DEFAULT NULL,
  `formato`      ENUM('APA','Chicago','ABNT','MLA') DEFAULT 'APA',
  `citacao_apa`  TEXT          DEFAULT NULL,
  `citacao_chicago` TEXT       DEFAULT NULL,
  `citacao_abnt` TEXT          DEFAULT NULL,
  `descricao`    TEXT          DEFAULT NULL,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_ano` (`ano`),
  KEY `idx_autor` (`autor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de relação artigos-referências
CREATE TABLE IF NOT EXISTS `artigo_referencias` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`    INT           NOT NULL,
  `referencia_id` INT          NOT NULL,
  `contexto`     TEXT          DEFAULT NULL COMMENT 'Como a referência é usada no artigo',
  `ordem`        INT           DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_ar_artigo` (`artigo_id`),
  KEY `fk_ar_referencia` (`referencia_id`),
  CONSTRAINT `fk_ar_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ar_referencia` FOREIGN KEY (`referencia_id`)
    REFERENCES `referencias`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;