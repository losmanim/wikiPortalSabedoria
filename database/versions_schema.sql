-- =============================================
-- SISTEMA DE CONTROLE DE VERSÕES
-- =============================================

-- Tabela de versões de artigos
CREATE TABLE IF NOT EXISTS `artigo_versoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`    INT           NOT NULL,
  `versao`       INT           NOT NULL,
  `titulo`       VARCHAR(255)  NOT NULL,
  `slug`         VARCHAR(255)  NOT NULL,
  `resumo`       VARCHAR(500)  DEFAULT NULL,
  `conteudo`     LONGTEXT      DEFAULT NULL,
  `tags`         VARCHAR(500)  DEFAULT NULL,
  `autor_id`     INT           DEFAULT NULL,
  `motivo_edicao` TEXT         DEFAULT NULL COMMENT 'Razão da edição',
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_versao_artigo` (`artigo_id`),
  KEY `fk_versao_autor` (`autor_id`),
  KEY `idx_versao` (`versao`),
  CONSTRAINT `fk_versao_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_versao_autor` FOREIGN KEY (`autor_id`)
    REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de comparações entre versões
CREATE TABLE IF NOT EXISTS `artigo_comparacoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`    INT           NOT NULL,
  `versao_de`    INT           NOT NULL,
  `versao_para`  INT           NOT NULL,
  `diff_html`    LONGTEXT      DEFAULT NULL COMMENT 'HTML do diff',
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comp_artigo` (`artigo_id`),
  CONSTRAINT `fk_comp_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;