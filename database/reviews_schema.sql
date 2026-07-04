-- =============================================
-- SISTEMA DE REVISÃO POR PARES
-- =============================================

-- Tabela de revisões de artigos
CREATE TABLE IF NOT EXISTS `revisoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`    INT           NOT NULL,
  `revisor_id`   INT           NOT NULL,
  `status`       ENUM('pendente','em_progresso','aprovado','rejeitado','solicitado') DEFAULT 'pendente',
  `nota_qualidade` INT         DEFAULT NULL COMMENT '1-5',
  `nota_coerencia` INT         DEFAULT NULL COMMENT '1-5',
  `nota_relevancia` INT        DEFAULT NULL COMMENT '1-5',
  `nota_fontes` INT            DEFAULT NULL COMMENT '1-5',
  `comentario_geral` TEXT      DEFAULT NULL,
  `pontos_fortes` TEXT         DEFAULT NULL,
  `pontos_fracos` TEXT         DEFAULT NULL,
  'sugestoes' TEXT             DEFAULT NULL,
  `versao_analisada` INT       DEFAULT NULL,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `finalizado_em` TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_revisao_artigo` (`artigo_id`),
  KEY `fk_revisao_revisor` (`revisor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_revisao_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_revisao_revisor` FOREIGN KEY (`revisor_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de comentários de revisão
CREATE TABLE IF NOT EXISTS `revisao_comentarios` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `revisao_id`   INT           NOT NULL,
  `autor_id`     INT           NOT NULL,
  `conteudo`     TEXT          NOT NULL,
  `tipo`         ENUM('geral','correcao','sugestao','duvida') DEFAULT 'geral',
  `trecho_original` TEXT       DEFAULT NULL,
  `trecho_sugerido` TEXT       DEFAULT NULL,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rc_revisao` (`revisao_id`),
  KEY `fk_rc_autor` (`autor_id`),
  CONSTRAINT `fk_rc_revisao` FOREIGN KEY (`revisao_id`)
    REFERENCES `revisoes`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rc_autor` FOREIGN KEY (`autor_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de qualificação de revisores
CREATE TABLE IF NOT EXISTS `revisor_qualificacoes` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `categoria_id` INT           DEFAULT NULL,
  `nivel`        ENUM('iniciante','intermediario','avancado','especialista') DEFAULT 'iniciante',
  `total_revisoes` INT         DEFAULT 0,
  `revisoes_aprovadas` INT     DEFAULT 0,
  `media_notas` DECIMAL(3,2)   DEFAULT 0.00,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rq_usuario` (`usuario_id`),
  KEY `fk_rq_categoria` (`categoria_id`),
  CONSTRAINT `fk_rq_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rq_categoria` FOREIGN KEY (`categoria_id`)
    REFERENCES `categorias`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;