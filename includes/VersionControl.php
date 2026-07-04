<?php
/**
 * Sistema de Controle de Versões de Artigos
 */

class VersionControl {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Cria nova versão de artigo
     */
    public function createVersion($artigoId, $autorId, $motivo = null) {
        $artigo = $this->db->fetch(
            'SELECT * FROM artigos WHERE id = ?',
            [$artigoId]
        );
        
        if (!$artigo) {
            return false;
        }
        
        // Verifica número da próxima versão
        $ultimaVersao = $this->db->fetch(
            'SELECT MAX(versao) as max_versao FROM artigo_versoes WHERE artigo_id = ?',
            [$artigoId]
        );
        
        $novaVersao = ($ultimaVersao['max_versao'] ?? 0) + 1;
        
        // Insere nova versão
        $versaoId = $this->db->insert('artigo_versoes', [
            'artigo_id' => $artigoId,
            'versao' => $novaVersao,
            'titulo' => $artigo['titulo'],
            'slug' => $artigo['slug'],
            'resumo' => $artigo['resumo'],
            'conteudo' => $artigo['conteudo'],
            'tags' => $artigo['tags'],
            'autor_id' => $autorId,
            'motivo_edicao' => $motivo
        ]);
        
        return $versaoId;
    }
    
    /**
     * Obtém todas as versões de um artigo
     */
    public function getVersions($artigoId) {
        return $this->db->select(
            'SELECT v.*, u.nome as autor_nome 
             FROM artigo_versoes v
             LEFT JOIN usuarios u ON u.id = v.autor_id
             WHERE v.artigo_id = ?
             ORDER BY v.versao DESC',
            [$artigoId]
        );
    }
    
    /**
     * Obtém uma versão específica
     */
    public function getVersion($versaoId) {
        return $this->db->fetch(
            'SELECT v.*, u.nome as autor_nome 
             FROM artigo_versoes v
             LEFT JOIN usuarios u ON u.id = v.autor_id
             WHERE v.id = ?',
            [$versaoId]
        );
    }
    
    /**
     * Restaura artigo para uma versão específica
     */
    public function restoreVersion($artigoId, $versaoId, $autorId) {
        $versao = $this->getVersion($versaoId);
        
        if (!$versao || $versao['artigo_id'] != $artigoId) {
            return false;
        }
        
        // Cria versão do estado atual antes de restaurar
        $this->createVersion($artigoId, $autorId, 'Restauração da versão ' . $versao['versao']);
        
        // Restaura conteúdo
        $this->db->update('artigos', [
            'titulo' => $versao['titulo'],
            'slug' => $versao['slug'],
            'resumo' => $versao['resumo'],
            'conteudo' => $versao['conteudo'],
            'tags' => $versao['tags'],
            'atualizado_em' => date('Y-m-d H:i:s')
        ], 'id = ?', [$artigoId]);
        
        return true;
    }
    
    /**
     * Compara duas versões
     */
    public function compareVersions($versaoId1, $versaoId2) {
        $v1 = $this->getVersion($versaoId1);
        $v2 = $this->getVersion($versaoId2);
        
        if (!$v1 || !$v2 || $v1['artigo_id'] != $v2['artigo_id']) {
            return false;
        }
        
        $diff = [
            'titulo' => $this->textDiff($v1['titulo'], $v2['titulo']),
            'resumo' => $this->textDiff($v1['resumo'] ?? '', $v2['resumo'] ?? ''),
            'conteudo' => $this->textDiff($v1['conteudo'] ?? '', $v2['conteudo'] ?? ''),
            'tags' => $this->textDiff($v1['tags'] ?? '', $v2['tags'] ?? '')
        ];
        
        return [
            'versao1' => $v1,
            'versao2' => $v2,
            'diff' => $diff
        ];
    }
    
    /**
     * Algoritmo simples de diff entre textos
     */
    private function textDiff($text1, $text2) {
        if ($text1 === $text2) {
            return ['changed' => false, 'text' => $text1];
        }
        
        $lines1 = explode("\n", $text1);
        $lines2 = explode("\n", $text2);
        
        $result = [];
        $i = 0;
        $j = 0;
        
        while ($i < count($lines1) || $j < count($lines2)) {
            if ($i < count($lines1) && $j < count($lines2) && $lines1[$i] === $lines2[$j]) {
                $result[] = ['type' => 'same', 'content' => $lines1[$i]];
                $i++;
                $j++;
            } else {
                if ($i < count($lines1)) {
                    $result[] = ['type' => 'removed', 'content' => $lines1[$i]];
                    $i++;
                }
                if ($j < count($lines2)) {
                    $result[] = ['type' => 'added', 'content' => $lines2[$j]];
                    $j++;
                }
            }
        }
        
        return ['changed' => true, 'diff' => $result];
    }
    
    /**
     * Obtém histórico de alterações
     */
    public function getHistory($artigoId, $limit = 20) {
        return $this->db->select(
            'SELECT v.*, u.nome as autor_nome 
             FROM artigo_versoes v
             LEFT JOIN usuarios u ON u.id = v.autor_id
             WHERE v.artigo_id = ?
             ORDER BY v.criado_em DESC
             LIMIT ?',
            [$artigoId, $limit]
        );
    }
    
    /**
     * Conta total de versões de um artigo
     */
    public function countVersions($artigoId) {
        $result = $this->db->fetch(
            'SELECT COUNT(*) as total FROM artigo_versoes WHERE artigo_id = ?',
            [$artigoId]
        );
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtém estatísticas de versões
     */
    public function getVersionStats($artigoId) {
        $total = $this->countVersions($artigoId);
        $autores = $this->db->select(
            'SELECT DISTINCT u.id, u.nome, COUNT(v.id) as total_versoes
             FROM artigo_versoes v
             LEFT JOIN usuarios u ON u.id = v.autor_id
             WHERE v.artigo_id = ?
             GROUP BY u.id, u.nome
             ORDER BY total_versoes DESC',
            [$artigoId]
        );
        
        $primeira = $this->db->fetch(
            'SELECT * FROM artigo_versoes WHERE artigo_id = ? ORDER BY versao ASC LIMIT 1',
            [$artigoId]
        );
        
        $ultima = $this->db->fetch(
            'SELECT * FROM artigo_versoes WHERE artigo_id = ? ORDER BY versao DESC LIMIT 1',
            [$artigoId]
        );
        
        return [
            'total_versoes' => $total,
            'autores' => $autores,
            'primeira_versao' => $primeira,
            'ultima_versao' => $ultima
        ];
    }
}