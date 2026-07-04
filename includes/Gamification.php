<?php
/**
 * Sistema de Gamificação
 * Badges, Reputação, Leaderboard
 */

class Gamification {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Inicializa reputação do usuário
     */
    public function initializeUserReputation($usuarioId) {
        $existing = $this->db->fetch(
            'SELECT id FROM usuario_reputacao WHERE usuario_id = ?',
            [$usuarioId]
        );
        
        if (!$existing) {
            $this->db->insert('usuario_reputacao', [
                'usuario_id' => $usuarioId,
                'pontos' => 0,
                'nivel' => 'iniciante'
            ]);
        }
    }
    
    /**
     * Adiciona pontos ao usuário
     */
    public function addPoints($usuarioId, $pontos, $acao, $descricao = null) {
        $this->initializeUserReputation($usuarioId);
        
        // Adiciona pontos
        $this->db->update('usuario_reputacao', [
            'pontos' => $this->db->fetch('SELECT pontos FROM usuario_reputacao WHERE usuario_id = ?', [$usuarioId])['pontos'] + $pontos
        ], 'usuario_id = ?', [$usuarioId]);
        
        // Registra no histórico
        $this->db->insert('reputacao_historico', [
            'usuario_id' => $usuarioId,
            'pontos' => $pontos,
            'acao' => $acao,
            'descricao' => $descricao
        ]);
        
        // Atualiza nível
        $this->updateUserLevel($usuarioId);
        
        // Verifica conquistas
        $this->checkBadges($usuarioId);
        
        // Atualiza leaderboard
        $this->updateLeaderboard($usuarioId);
    }
    
    /**
     * Atualiza nível do usuário
     */
    private function updateUserLevel($usuarioId) {
        $reputacao = $this->db->fetch(
            'SELECT * FROM usuario_reputacao WHERE usuario_id = ?',
            [$usuarioId]
        );
        
        $pontos = $reputacao['pontos'];
        $novoNivel = 'iniciante';
        
        if ($pontos >= 50) $novoNivel = 'aprendiz';
        if ($pontos >= 150) $novoNivel = 'contribuidor';
        if ($pontos >= 300) $novoNivel = 'especialista';
        if ($pontos >= 500) $novoNivel = 'mestre';
        if ($pontos >= 1000) $novoNivel = 'lendario';
        
        if ($novoNivel !== $reputacao['nivel']) {
            $this->db->update('usuario_reputacao', [
                'nivel' => $novoNivel
            ], 'usuario_id = ?', [$usuarioId]);
            
            // Notifica usuário (a ser implementado)
            $this->notifyLevelUp($usuarioId, $novoNivel);
        }
    }
    
    /**
     * Verifica e atribui badges
     */
    private function checkBadges($usuarioId) {
        $badges = $this->db->select('SELECT * FROM badges WHERE ativo = 1');
        $reputacao = $this->db->fetch(
            'SELECT * FROM usuario_reputacao WHERE usuario_id = ?',
            [$usuarioId]
        );
        
        foreach ($badges as $badge) {
            // Verifica se já tem o badge
            $temBadge = $this->db->fetch(
                'SELECT id FROM usuario_badges WHERE usuario_id = ? AND badge_id = ?',
                [$usuarioId, $badge['id']]
            );
            
            if ($temBadge) continue;
            
            // Verifica requisito
            $conquistou = false;
            
            switch ($badge['requisito_tipo']) {
                case 'artigos_criados':
                    $conquistou = $reputacao['artigos_criados'] >= $badge['requisito_qtd'];
                    break;
                case 'artigos_revisados':
                    $conquistou = $reputacao['artigos_revisados'] >= $badge['requisito_qtd'];
                    break;
                case 'comentarios':
                    $conquistou = $reputacao['comentarios_feitos'] >= $badge['requisito_qtd'];
                    break;
                case 'pontos':
                    $conquistou = $reputacao['pontos'] >= $badge['requisito_qtd'];
                    break;
            }
            
            if ($conquistou) {
                $this->awardBadge($usuarioId, $badge['id']);
            }
        }
    }
    
    /**
     * Atribui badge ao usuário
     */
    public function awardBadge($usuarioId, $badgeId) {
        $badge = $this->db->fetch('SELECT * FROM badges WHERE id = ?', [$badgeId]);
        
        if (!$badge) return false;
        
        $this->db->insert('usuario_badges', [
            'usuario_id' => $usuarioId,
            'badge_id' => $badgeId
        ]);
        
        // Adiciona pontos do badge
        $this->addPoints($usuarioId, $badge['pontos'], 'badge_conquistado', "Badge: {$badge['nome']}");
        
        // Notifica usuário
        $this->notifyBadgeAwarded($usuarioId, $badge);
        
        return true;
    }
    
    /**
     * Obtém badges do usuário
     */
    public function getUserBadges($usuarioId) {
        return $this->db->select(
            'SELECT b.*, ub.conquistado_em
             FROM badges b
             JOIN usuario_badges ub ON ub.badge_id = b.id
             WHERE ub.usuario_id = ?
             ORDER BY b.pontos DESC',
            [$usuarioId]
        );
    }
    
    /**
     * Obtém reputação do usuário
     */
    public function getUserReputation($usuarioId) {
        $reputacao = $this->db->fetch(
            'SELECT * FROM usuario_reputacao WHERE usuario_id = ?',
            [$usuarioId]
        );
        
        if (!$reputacao) {
            $this->initializeUserReputation($usuarioId);
            $reputacao = $this->db->fetch(
                'SELECT * FROM usuario_reputacao WHERE usuario_id = ?',
                [$usuarioId]
            );
        }
        
        return $reputacao;
    }
    
    /**
     * Obtém histórico de pontos
     */
    public function getPointsHistory($usuarioId, $limit = 20) {
        return $this->db->select(
            'SELECT * FROM reputacao_historico 
             WHERE usuario_id = ? 
             ORDER BY criado_em DESC 
             LIMIT ?',
            [$usuarioId, $limit]
        );
    }
    
    /**
     * Atualiza leaderboard
     */
    private function updateLeaderboard($usuarioId) {
        $reputacao = $this->getUserReputation($usuarioId);
        
        // Atualiza leaderboard total
        $existing = $this->db->fetch(
            'SELECT id FROM leaderboard WHERE usuario_id = ? AND periodo = "total"',
            [$usuarioId]
        );
        
        if ($existing) {
            $this->db->update('leaderboard', [
                'pontos' => $reputacao['pontos']
            ], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('leaderboard', [
                'usuario_id' => $usuarioId,
                'pontos' => $reputacao['pontos'],
                'periodo' => 'total'
            ]);
        }
        
        // Recalcula posições
        $this->recalculateLeaderboardPositions('total');
    }
    
    /**
     * Recalcula posições do leaderboard
     */
    private function recalculateLeaderboardPositions($periodo) {
        $usuarios = $this->db->select(
            'SELECT usuario_id, pontos FROM leaderboard WHERE periodo = ? ORDER BY pontos DESC',
            [$periodo]
        );
        
        foreach ($usuarios as $index => $usuario) {
            $this->db->update('leaderboard', [
                'posicao' => $index + 1
            ], 'usuario_id = ? AND periodo = ?', [$usuario['usuario_id'], $periodo]);
        }
    }
    
    /**
     * Obtém leaderboard
     */
    public function getLeaderboard($periodo = 'total', $limit = 50) {
        return $this->db->select(
            'SELECT l.*, u.nome, u.avatar, ur.nivel
             FROM leaderboard l
             LEFT JOIN usuarios u ON u.id = l.usuario_id
             LEFT JOIN usuario_reputacao ur ON ur.usuario_id = l.usuario_id
             WHERE l.periodo = ?
             ORDER BY l.posicao ASC
             LIMIT ?',
            [$periodo, $limit]
        );
    }
    
    /**
     * Obtém posição do usuário no leaderboard
     */
    public function getUserLeaderboardPosition($usuarioId, $periodo = 'total') {
        $result = $this->db->fetch(
            'SELECT posicao FROM leaderboard WHERE usuario_id = ? AND periodo = ?',
            [$usuarioId, $periodo]
        );
        return $result['posicao'] ?? null;
    }
    
    /**
     * Registra ação do usuário
     */
    public function registerAction($usuarioId, $acao, $quantidade = 1) {
        $this->initializeUserReputation($usuarioId);
        
        $pontos = 0;
        $campoAtualizar = '';
        
        switch ($acao) {
            case 'artigo_criado':
                $pontos = 10;
                $campoAtualizar = 'artigos_criados';
                break;
            case 'artigo_revisado':
                $pontos = 5;
                $campoAtualizar = 'artigos_revisados';
                break;
            case 'comentario_feito':
                $pontos = 2;
                $campoAtualizar = 'comentarios_feitos';
                break;
            case 'login_diario':
                $pontos = 1;
                break;
            case 'revisao_recebida':
                $pontos = 3;
                $campoAtualizar = 'revisoes_recebidas';
                break;
        }
        
        if ($pontos > 0) {
            // Atualiza contador específico
            if ($campoAtualizar) {
                $this->db->update('usuario_reputacao', [
                    $campoAtualizar => $this->db->fetch("SELECT $campoAtualizar FROM usuario_reputacao WHERE usuario_id = ?", [$usuarioId])[$campoAtualizar] + $quantidade
                ], 'usuario_id = ?', [$usuarioId]);
            }
            
            $this->addPoints($usuarioId, $pontos * $quantidade, $acao);
        }
    }
    
    /**
     * Cria badges iniciais
     */
    public function createInitialBadges() {
        $badges = [
            [
                'nome' => 'Primeiro Artigo',
                'descricao' => 'Publicou seu primeiro artigo',
                'icone' => 'bi bi-pencil',
                'cor' => '#27ae60',
                'categoria' => 'contribuicao',
                'nivel' => 'bronze',
                'pontos' => 10,
                'requisito_tipo' => 'artigos_criados',
                'requisito_qtd' => 1
            ],
            [
                'nome' => 'Escritor Dedicado',
                'descricao' => 'Publicou 10 artigos',
                'icone' => 'bi bi-book',
                'cor' => '#3498db',
                'categoria' => 'contribuicao',
                'nivel' => 'prata',
                'pontos' => 25,
                'requisito_tipo' => 'artigos_criados',
                'requisito_qtd' => 10
            ],
            [
                'nome' => 'Mestre da Palavra',
                'descricao' => 'Publicou 50 artigos',
                'icone' => 'bi bi-journal',
                'cor' => '#9b59b6',
                'categoria' => 'contribuicao',
                'nivel' => 'ouro',
                'pontos' => 100,
                'requisito_tipo' => 'artigos_criados',
                'requisito_qtd' => 50
            ],
            [
                'nome' => 'Primeira Revisão',
                'descricao' => 'Realizou sua primeira revisão',
                'icone' => 'bi bi-eye',
                'cor' => '#e67e22',
                'categoria' => 'revisao',
                'nivel' => 'bronze',
                'pontos' => 5,
                'requisito_tipo' => 'artigos_revisados',
                'requisito_qtd' => 1
            ],
            [
                'nome' => 'Revisor Experiente',
                'descricao' => 'Realizou 25 revisões',
                'icone' => 'bi bi-check2-circle',
                'cor' => '#16a085',
                'categoria' => 'revisao',
                'nivel' => 'prata',
                'pontos' => 50,
                'requisito_tipo' => 'artigos_revisados',
                'requisito_qtd' => 25
            ],
            [
                'nome' => 'Comentarista Ativo',
                'descricao' => 'Fez 50 comentários',
                'icone' => 'bi bi-chat',
                'cor' => '#f39c12',
                'categoria' => 'social',
                'nivel' => 'bronze',
                'pontos' => 15,
                'requisito_tipo' => 'comentarios',
                'requisito_qtd' => 50
            ],
            [
                'nome' => 'Membro da Comunidade',
                'descricao' => 'Ganhou 100 pontos',
                'icone' => 'bi bi-people',
                'cor' => '#1abc9c',
                'categoria' => 'social',
                'nivel' => 'prata',
                'pontos' => 20,
                'requisito_tipo' => 'pontos',
                'requisito_qtd' => 100
            ]
        ];
        
        foreach ($badges as $badge) {
            $existing = $this->db->fetch(
                'SELECT id FROM badges WHERE nome = ?',
                [$badge['nome']]
            );
            
            if (!$existing) {
                $this->db->insert('badges', $badge);
            }
        }
    }
    
    /**
     * Notifica usuário de level up (placeholder)
     */
    private function notifyLevelUp($usuarioId, $novoNivel) {
        log_atividade($this->db, $usuarioId, 'level_up', "Novo nível: $novoNivel");
    }
    
    /**
     * Notifica usuário de badge conquistado (placeholder)
     */
    private function notifyBadgeAwarded($usuarioId, $badge) {
        log_atividade($this->db, $usuarioId, 'badge_conquistado', "Badge: {$badge['nome']}");
    }
}