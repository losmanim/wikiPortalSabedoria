-- =============================================
-- Portal Saberes Ancestrais - Wiki CMS
-- =============================================

CREATE DATABASE IF NOT EXISTS `portal_saberes`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `portal_saberes`;

-- =============================================
-- USUÁRIOS
-- =============================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `nome`       VARCHAR(100)  NOT NULL,
  `email`      VARCHAR(255)  NOT NULL,
  `senha`      VARCHAR(255)  NOT NULL,
  `avatar`     VARCHAR(255)  DEFAULT NULL,
  `bio`        TEXT          DEFAULT NULL,
  `nivel`      ENUM('admin','editor','user') DEFAULT 'user',
  `status`     ENUM('ativo','banido') DEFAULT 'ativo',
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_nivel` (`nivel`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- CATEGORIAS (hierárquicas)
-- =============================================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `nome`       VARCHAR(100)  NOT NULL,
  `slug`       VARCHAR(100)  NOT NULL,
  `descricao`  TEXT          DEFAULT NULL,
  `icone`      VARCHAR(50)   DEFAULT 'bi bi-folder',
  `cor`        VARCHAR(7)    DEFAULT '#f39c12',
  `parent_id`  INT           DEFAULT NULL,
  `ordem`      INT           DEFAULT 0,
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cat_slug` (`slug`),
  KEY `fk_cat_parent` (`parent_id`),
  CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `categorias`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- ARTIGOS
-- =============================================
CREATE TABLE IF NOT EXISTS `artigos` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `categoria_id` INT           DEFAULT NULL,
  `autor_id`     INT           DEFAULT NULL,
  `titulo`       VARCHAR(255)  NOT NULL,
  `slug`         VARCHAR(255)  NOT NULL,
  `resumo`       VARCHAR(500)  DEFAULT NULL,
  `conteudo`     LONGTEXT      DEFAULT NULL,
  `tags`         VARCHAR(500)  DEFAULT NULL,
  `imagem`       VARCHAR(255)  DEFAULT NULL,
  `fonte`        VARCHAR(500)  DEFAULT NULL COMMENT 'Origem do conteúdo (ex: Modelos/pistisSofia)',
  `status`       ENUM('rascunho','publicado','arquivado') DEFAULT 'rascunho',
  `views`        INT           DEFAULT 0,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `publicado_em` TIMESTAMP     NULL DEFAULT NULL,
  `atualizado_em` TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_artigo_slug` (`slug`),
  KEY `fk_artigo_categoria` (`categoria_id`),
  KEY `fk_artigo_autor` (`autor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_publicado` (`publicado_em`),
  FULLTEXT KEY `ft_busca` (`titulo`, `resumo`, `conteudo`, `tags`),
  CONSTRAINT `fk_artigo_categoria` FOREIGN KEY (`categoria_id`)
    REFERENCES `categorias`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_artigo_autor` FOREIGN KEY (`autor_id`)
    REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- COMENTÁRIOS
-- =============================================
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`  INT           NOT NULL,
  `usuario_id` INT           DEFAULT NULL,
  `autor_nome` VARCHAR(100)  DEFAULT NULL COMMENT 'Para visitantes não logados',
  `autor_email` VARCHAR(255) DEFAULT NULL,
  `conteudo`   TEXT          NOT NULL,
  `status`     ENUM('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `parent_id`  INT           DEFAULT NULL COMMENT 'Resposta a outro comentário',
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_comentario_artigo` (`artigo_id`),
  KEY `fk_comentario_usuario` (`usuario_id`),
  KEY `fk_comentario_parent` (`parent_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_comentario_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_comentario_parent` FOREIGN KEY (`parent_id`)
    REFERENCES `comentarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- PÁGINAS ESTÁTICAS (Sobre, Contato, etc)
-- =============================================
CREATE TABLE IF NOT EXISTS `paginas` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `titulo`     VARCHAR(255)  NOT NULL,
  `slug`       VARCHAR(255)  NOT NULL,
  `conteudo`   LONGTEXT      DEFAULT NULL,
  `ordem`      INT           DEFAULT 0,
  `no_menu`    TINYINT(1)    DEFAULT 1,
  `status`     ENUM('rascunho','publicado') DEFAULT 'publicado',
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pagina_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- CONFIGURAÇÕES DO SITE
-- =============================================
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `chave`      VARCHAR(100)  NOT NULL,
  `valor`      TEXT          DEFAULT NULL,
  `atualizado_em` TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- LOGS DE ATIVIDADE
-- =============================================
CREATE TABLE IF NOT EXISTS `logs` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `usuario_id` INT           DEFAULT NULL,
  `acao`       VARCHAR(100)  NOT NULL,
  `descricao`  TEXT          DEFAULT NULL,
  `ip`         VARCHAR(45)   DEFAULT NULL,
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_log_usuario` (`usuario_id`),
  KEY `idx_acao` (`acao`),
  CONSTRAINT `fk_log_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- VIEWS: artigos_views (contagem separada)
-- =============================================
CREATE TABLE IF NOT EXISTS `artigos_views` (
  `id`         INT           NOT NULL AUTO_INCREMENT,
  `artigo_id`  INT           NOT NULL,
  `ip`         VARCHAR(45)   DEFAULT NULL,
  `user_agent` VARCHAR(500)  DEFAULT NULL,
  `criado_em`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_view_artigo` (`artigo_id`),
  CONSTRAINT `fk_view_artigo` FOREIGN KEY (`artigo_id`)
    REFERENCES `artigos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DADOS INICIAIS
-- =============================================

-- Admin padrão (senha: admin123)
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel`) VALUES
('Administrador', 'admin@saberes.com',
 '$2y$12$k0WE9sMyYa3UMTVQef1cHO7FebpGoJexy6Z/t4NgvX5zaPqdNgSkW',
 'admin')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Configurações iniciais
INSERT INTO `configuracoes` (`chave`, `valor`) VALUES
('site_nome', 'Portal Saberes Ancestrais'),
('site_descricao', 'Wiki colaborativa sobre saberes que unem ciência, espiritualidade e filosofia'),
('site_logo', '🕉️'),
('site_email', 'contato@saberes.com'),
('comentarios_aprovacao', 'manual'),
('artigos_por_pagina', '12'),
('tema', 'escuro')
ON DUPLICATE KEY UPDATE `chave` = `chave`;

-- Páginas iniciais
INSERT INTO `paginas` (`titulo`, `slug`, `conteudo`, `ordem`, `no_menu`) VALUES
('Sobre', 'sobre', '<h2>Sobre o Portal</h2><p>Este portal é um espaço dedicado ao estudo e difusão dos saberes ancestrais que unem ciência, espiritualidade e filosofia. Aqui você encontrará artigos sobre Gnose, Epigenética, Hermetismo, Teosofia, Meditação e muito mais.</p><p>Todo o conteúdo é baseado em pesquisas e estudos independentes, buscando oferecer uma visão ampla e sem filtros sobre o conhecimento humano.</p>', 1, 1),
('Contato', 'contato', '<h2>Entre em Contato</h2><p>Quer contribuir com o portal? Tem dúvidas ou sugestões? Envie um email para contato@saberes.com</p>', 2, 1)
ON DUPLICATE KEY UPDATE `slug` = `slug`;
