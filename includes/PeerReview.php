<?php
/**
 * Sistema de Revisão por Pares
 */

class PeerReview {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Solicita revisão para um artigo
     */
    public function requestReview($artigoId, $revisorId, $solicitanteId) {
        // Verifica se já existe revisão pendente
        $existing = $this->db->fetch(
            'SELECT id FROM revisoes 
             WHERE artigo_id = ? AND revisor_id = ? AND status IN ("pendente", "em_progresso")',
            [$artigoId, $revisorId]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Já existe uma revisão pendente para este artigo'];
        }
        
        // Verifica qualificação do revisor
        $qualificacao = $this->getReviewerQualification($revisorId);
        if (!$qualificacao) {
            $this->createReviewerQualification($revisorId);
        }
        
        $revisaoId = $this->db->insert('revisoes', [
            'artigo_id' => $artigoId,
            'revisor_id' => $revisorId,
            'status' => 'solicitado',
            'versao_analisada' => $this->getCurrentArticleVersion($artigoId)
        ]);
        
        // Notifica o revisor (a ser implementado)
        $this->notifyReviewer($revisorId, $artigoId);
        
        return ['success' => true, 'revisao_id' => $revisaoId];
    }
    
    /**
     * Aceita solicitação de revisão
     */
    public function acceptReview($revisaoId, $revisorId) {
        $revisao = $this->db->fetch(
            'SELECT * FROM revisoes WHERE id = ? AND revisor_id = ?',
            [$revisaoId, $revisorId]
        );
        
        if (!$revisao) {
            return ['success' => false, 'message' => 'Revisão não encontrada'];
        }
        
        $this->db->update('revisoes', [
            'status' => 'em_progresso'
        ], 'id = ?', [$revisaoId]);
        
        return ['success' => true];
    }
    
    /**
     * Submete revisão completa
     */
    public function submitReview($revisaoId, $revisorId, $data) {
        $revisao = $this->db->fetch(
            'SELECT * FROM revisoes WHERE id = ? AND revisor_id = ?',
            [$revisaoId, $revisorId]
        );
        
        if (!$revisao) {
            return ['success' => false, 'message' => 'Revisão não encontrada'];
        }
        
        $updateData = [
            'status' => $data['status'] ?? 'pendente',
            'nota_qualidade' => $data['nota_qualidade'] ?? null,
            'nota_coerencia' => $data['nota_coerencia'] ?? null,
            'nota_relevancia' => $data['nota_relevancia'] ?? null,
            'nota_fontes' => $data['nota_fontes'] ?? null,
            'comentario_geral' => $data['comentario_geral'] ?? null,
            'pontos_fortes' => $data['pontos_fortes'] ?? null,
            'pontos_fracos' => $data['pontos_fracos'] ?? null,
            'sugestoes' => $data['sugestoes'] ?? null,
            'finalizado_em' => date('Y-m-d H:i:s')
        ];
        
        $this->db->update('revisoes', $updateData, 'id = ?', [$revisaoId]);
        
        // Atualiza qualificação do revisor
        $this->updateReviewerStats($revisorId);
        
        // Notifica o autor (a ser implementado)
        $this->notifyAuthor($revisao['artigo_id'], $revisaoId);
        
        return ['success' => true];
    }
    
    /**
     * Adiciona comentário à revisão
     */
    public function addReviewComment($revisaoId, $autorId, $conteudo, $tipo = 'geral', $trechoOriginal = null, $trechoSugerido = null) {
        return $this->db->insert('revisao_comentarios', [
            'revisao_id' => $revisaoId,
            'autor_id' => $autorId,
            'conteudo' => $conteudo,
            'tipo' => $tipo,
            'trecho_original' => $trechoOriginal,
            'trecho_sugerido' => $trechoSugerido
        ]);
    }
    
    /**
     * Obtém revisões de um artigo
     */
    public function getArticleReviews($artigoId) {
        return $this->db->select(
            'SELECT r.*, u.nome as revisor_nome, u.avatar as revisor_avatar,
                    (SELECT AVG((nota_qualidade + nota_coerencia + nota_relevancia + nota_fontes) / 4)
                     FROM revisoes WHERE artigo_id = ? AND status = "aprovado") as media_geral
             FROM revisoes r
             LEFT JOIN usuarios u ON u.id = r.revisor_id
             WHERE r.artigo_id = ?
             ORDER BY r.criado_em DESC',
            [$artigoId, $artigoId]
        );
    }
    
    /**
     * Obtém revisões de um revisor
     */
    public function getReviewerReviews($revisorId, $status = null) {
        $sql = 'SELECT r.*, a.titulo as artigo_titulo, a.slug as artigo_slug
                FROM revisoes r
                LEFT JOIN artigos a ON a.id = r.artigo_id
                WHERE r.revisor_id = ?';
        $params = [$revisorId];
        
        if ($status) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        
        $sql .= ' ORDER BY r.criado_em DESC';
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Obtém comentários de uma revisão
     */
    public function getReviewComments($revisaoId) {
        return $this->db->select(
            'SELECT rc.*, u.nome as autor_nome
             FROM revisao_comentarios rc
             LEFT JOIN usuarios u ON u.id = rc.autor_id
             WHERE rc.revisao_id = ?
             ORDER BY rc.criado_em ASC',
            [$revisaoId]
        );
    }
    
    /**
     * Obtém qualificação de revisor
     */
    public function getReviewerQualification($usuarioId) {
        return $this->db->fetch(
            'SELECT * FROM revisor_qualificacoes WHERE usuario_id = ?',
            [$usuarioId]
        );
    }
    
    /**
     * Cria qualificação de revisor
     */
    public function createReviewerQualification($usuarioId, $categoriaId = null) {
        return $this->db->insert('revisor_qualificacoes', [
            'usuario_id' => $usuarioId,
            'categoria_id' => $categoriaId,
            'nivel' => 'iniciante'
        ]);
    }
    
    /**
     * Atualiza estatísticas do revisor
     */
    private function updateReviewerStats($revisorId) {
        $stats = $this->db->fetch(
            'SELECT COUNT(*) as total, 
                    SUM(CASE WHEN status = "aprovado" THEN 1 ELSE 0 END) as aprovadas,
                    AVG((nota_qualidade + nota_coerencia + nota_relevancia + nota_fontes) / 4) as media
             FROM revisoes 
             WHERE revisor_id = ? AND status IN ("aprovado", "rejeitado")',
            [$revisorId]
        );
        
        $qualificacao = $this->getReviewerQualification($revisorId);
        
        // Determina nível baseado em estatísticas
        $nivel = 'iniciante';
        if ($stats['total'] >= 5 && $stats['media'] >= 3.5) {
            $nivel = 'intermediario';
        }
        if ($stats['total'] >= 15 && $stats['media'] >= 4.0) {
            $nivel = 'avancado';
        }
        if ($stats['total'] >= 30 && $stats['media'] >= 4.5) {
            $nivel = 'especialista';
        }
        
        $this->db->update('revisor_qualificacoes', [
            'total_revisoes' => $stats['total'],
            'revisoes_aprovadas' => $stats['aprovadas'],
            'media_notas' => $stats['media'] ?? 0,
            'nivel' => $nivel
        ], 'usuario_id = ?', [$revisorId]);
    }
    
    /**
     * Obtém versão atual do artigo
     */
    private function getCurrentArticleVersion($artigoId) {
        $stats = $this->db->fetch(
            'SELECT MAX(versao) as max_versao FROM artigo_versoes WHERE artigo_id = ?',
            [$artigoId]
        );
        return $stats['max_versao'] ?? 0;
    }
    
    /**
     * Notifica revisor (placeholder)
     */
    private function notifyReviewer($revisorId, $artigoId) {
        // Implementar sistema de notificações
        // Por enquanto, apenas log
        log_atividade($this->db, $revisorId, 'revisao_solicitada', "Artigo ID: $artigoId");
    }
    
    /**
     * Notifica autor (placeholder)
     */
    private function notifyAuthor($artigoId, $revisaoId) {
        // Implementar sistema de notificações
        $artigo = $this->db->fetch('SELECT autor_id FROM artigos WHERE id = ?', [$artigoId]);
        if ($artigo) {
            log_atividade($this->db, $artigo['autor_id'], 'revisao_concluida', "Revisão ID: $revisaoId");
        }
    }
    
    /**
     * Obtém artigos aguardando revisão
     */
    public function getPendingReviews($limit = 20) {
        return $this->db->select(
            'SELECT a.*, COUNT(r.id) as revisoes_pendentes
             FROM artigos a
             LEFT JOIN revisoes r ON r.artigo_id = a.id AND r.status IN ("pendente", "solicitado")
             WHERE a.status = "publicado"
             GROUP BY a.id
             HAVING revisoes_pendentes < 2
             ORDER BY a.publicado_em DESC
             LIMIT ?',
            [$limit]
        );
    }
    
    /**
     * Aprova artigo após revisões
     */
    public function approveArticleAfterReview($artigoId) {
        $revisoes = $this->getArticleReviews($artigoId);
        $aprovadas = array_filter($revisoes, fn($r) => $r['status'] === 'aprovado');
        
        if (count($aprovadas) >= 2) {
            // Calcula média das notas
            $media = array_reduce($aprovadas, function($carry, $r) {
                return $carry + (($r['nota_qualidade'] + $r['nota_coerencia'] + $r['nota_relevancia'] + $r['nota_fontes']) / 4);
            }, 0) / count($aprovadas);
            
            if ($media >= 3.5) {
                // Adiciona badge de artigo revisado
                $this->addReviewedBadge($artigoId);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Adiciona badge de artigo revisado (placeholder)
     */
    private function addReviewedBadge($artigoId) {
        // Implementar sistema de badges
        log_atividade($this->db, null, 'artigo_revisado', "Artigo ID: $artigoId");
    }
}