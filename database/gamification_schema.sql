-- =============================================
-- SISTEMA DE GAMIFICAÇÃO
-- =============================================

-- Tabela de badges
CREATE TABLE IF NOT EXISTS `badges` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `nome`         VARCHAR(100)  NOT NULL,
  `descricao`    TEXT          NOT NULL,
  `icone`        VARCHAR(50)   DEFAULT 'bi bi-award',
  `cor`          VARCHAR(7)    DEFAULT '#f39c12',
  `categoria`    ENUM('contribuicao','revisao','social','especial') DEFAULT 'contribuicao',
  `nivel`        ENUM('bronze','prata','ouro','platina','diamante') DEFAULT 'bronze',
  `pontos`       INT           DEFAULT 10,
  `requisito_tipo` ENUM('artigos_criados','artigos_revisados','comentarios','logins','dias_consecutivos','especial') DEFAULT 'artigos_criados',
  `requisito_qtd` INT           DEFAULT 1,
  `ativo`        TINYINT(1)    DEFAULT 1,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_nivel` (`nivel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de badges dos usuários
CREATE TABLE IF NOT EXISTS `usuario_badges` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `badge_id`     INT           NOT NULL,
  `conquistado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ub_usuario` (`usuario_id`),
  KEY `fk_ub_badge` (`badge_id`),
  CONSTRAINT `fk_ub_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ub_badge` FOREIGN KEY (`badge_id`)
    REFERENCES `badges`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de pontos de reputação
CREATE TABLE IF NOT EXISTS `usuario_reputacao` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `pontos`       INT           DEFAULT 0,
  `nivel`        ENUM('iniciante','aprendiz','contribuidor','especialista','mestre','lendario') DEFAULT 'iniciante',
  `artigos_criados` INT        DEFAULT 0,
  `artigos_revisados` INT      DEFAULT 0,
  `comentarios_feitos` INT     DEFAULT 0,
  `revisoes_recebidas` INT     DEFAULT 0,
  `likes_recebidos` INT        DEFAULT 0,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ur_usuario` (`usuario_id`),
  CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de histórico de pontos
CREATE TABLE IF NOT EXISTS `reputacao_historico` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `pontos`       INT           NOT NULL,
  `acao`         VARCHAR(100)  NOT NULL,
  `descricao`    TEXT          DEFAULT NULL,
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_rh_usuario` (`usuario_id`),
  CONSTRAINT `fk_rh_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de leaderboard
CREATE TABLE IF NOT EXISTS `leaderboard` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `usuario_id`   INT           NOT NULL,
  `pontos`       INT           DEFAULT 0,
  `posicao`      INT           DEFAULT 0,
  `periodo`      ENUM('semanal','mensal','anual','total') DEFAULT 'total',
  `criado_em`    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_lb_usuario` (`usuario_id`),
  KEY `idx_periodo` (`periodo`),
  CONSTRAINT `fk_lb_usuario` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;