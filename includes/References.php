<?php
/**
 * Sistema de Referências Bibliográficas
 * Formatos: APA, Chicago, ABNT, MLA
 */

class References {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Cria nova referência
     */
    public function create($data) {
        $refData = [
            'tipo' => $data['tipo'] ?? 'livro',
            'titulo' => $data['titulo'],
            'autor' => $data['autor'] ?? null,
            'ano' => $data['ano'] ?? null,
            'editora' => $data['editora'] ?? null,
            'url' => $data['url'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'doi' => $data['doi'] ?? null,
            'formato' => $data['formato'] ?? 'APA',
            'descricao' => $data['descricao'] ?? null
        ];
        
        // Gera citações nos diferentes formatos
        $refData['citacao_apa'] = $this->generateAPA($refData);
        $refData['citacao_chicago'] = $this->generateChicago($refData);
        $refData['citacao_abnt'] = $this->generateABNT($refData);
        
        return $this->db->insert('referencias', $refData);
    }
    
    /**
     * Gera citação no formato APA
     */
    private function generateAPA($data) {
        $citacao = '';
        
        if (!empty($data['autor'])) {
            $citacao .= $data['autor'] . ' (' . ($data['ano'] ?? 's.d.') . '). ';
        }
        
        $citacao .= '<em>' . $data['titulo'] . '</em>.';
        
        if (!empty($data['editora'])) {
            $citacao .= ' ' . $data['editora'] . '.';
        }
        
        if (!empty($data['url'])) {
            $citacao .= ' Recuperado de ' . $data['url'];
        }
        
        return $citacao;
    }
    
    /**
     * Gera citação no formato Chicago
     */
    private function generateChicago($data) {
        $citacao = '';
        
        if (!empty($data['autor'])) {
            $citacao .= $data['autor'];
        }
        
        $citacao .= ' "' . $data['titulo'] . '"';
        
        if (!empty($data['editora'])) {
            $citacao .= '. ' . $data['editora'];
        }
        
        if (!empty($data['ano'])) {
            $citacao .= ', ' . $data['ano'];
        }
        
        $citacao .= '.';
        
        return $citacao;
    }
    
    /**
     * Gera citação no formato ABNT
     */
    private function generateABNT($data) {
        $citacao = '';
        
        if (!empty($data['autor'])) {
            $citacao .= strtoupper($data['autor']) . '. ';
        }
        
        $citacao .= '<strong>' . $data['titulo'] . '</strong>.';
        
        if (!empty($data['editora'])) {
            $citacao .= ' ' . $data['editora'];
        }
        
        if (!empty($data['ano'])) {
            $citacao .= ', ' . $data['ano'];
        }
        
        $citacao .= '.';
        
        return $citacao;
    }
    
    /**
     * Atualiza referência
     */
    public function update($id, $data) {
        $refData = [
            'tipo' => $data['tipo'] ?? 'livro',
            'titulo' => $data['titulo'],
            'autor' => $data['autor'] ?? null,
            'ano' => $data['ano'] ?? null,
            'editora' => $data['editora'] ?? null,
            'url' => $data['url'] ?? null,
            'isbn' => $data['isbn'] ?? null,
            'doi' => $data['doi'] ?? null,
            'formato' => $data['formato'] ?? 'APA',
            'descricao' => $data['descricao'] ?? null
        ];
        
        // Regera citações
        $refData['citacao_apa'] = $this->generateAPA($refData);
        $refData['citacao_chicago'] = $this->generateChicago($refData);
        $refData['citacao_abnt'] = $this->generateABNT($refData);
        
        return $this->db->update('referencias', $refData, 'id = ?', [$id]);
    }
    
    /**
     * Obtém referência por ID
     */
    public function get($id) {
        return $this->db->fetch('SELECT * FROM referencias WHERE id = ?', [$id]);
    }
    
    /**
     * Lista referências de um artigo
     */
    public function getByArticle($artigoId) {
        return $this->db->select(
            'SELECT r.*, ar.contexto, ar.ordem
             FROM referencias r
             JOIN artigo_referencias ar ON ar.referencia_id = r.id
             WHERE ar.artigo_id = ?
             ORDER BY ar.ordem',
            [$artigoId]
        );
    }
    
    /**
     * Adiciona referência a um artigo
     */
    public function addToArticle($artigoId, $referenciaId, $contexto = null, $ordem = 0) {
        // Verifica se já existe
        $existing = $this->db->fetch(
            'SELECT id FROM artigo_referencias 
             WHERE artigo_id = ? AND referencia_id = ?',
            [$artigoId, $referenciaId]
        );
        
        if ($existing) {
            return $existing['id'];
        }
        
        return $this->db->insert('artigo_referencias', [
            'artigo_id' => $artigoId,
            'referencia_id' => $referenciaId,
            'contexto' => $contexto,
            'ordem' => $ordem
        ]);
    }
    
    /**
     * Remove referência de um artigo
     */
    public function removeFromArticle($artigoId, $referenciaId) {
        return $this->db->delete(
            'DELETE FROM artigo_referencias 
             WHERE artigo_id = ? AND referencia_id = ?',
            [$artigoId, $referenciaId]
        );
    }
    
    /**
     * Busca referências
     */
    public function search($termo, $tipo = null, $limit = 20) {
        $sql = 'SELECT * FROM referencias WHERE titulo LIKE ?';
        $params = ['%' . $termo . '%'];
        
        if ($tipo) {
            $sql .= ' AND tipo = ?';
            $params[] = $tipo;
        }
        
        $sql .= ' ORDER BY titulo LIMIT ?';
        $params[] = $limit;
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Lista todas as referências
     */
    public function listAll($limit = 50, $offset = 0) {
        return $this->db->select(
            'SELECT * FROM referencias 
             ORDER BY atualizado_em DESC 
             LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }
    
    /**
     * Deleta referência
     */
    public function delete($id) {
        return $this->db->delete('DELETE FROM referencias WHERE id = ?', [$id]);
    }
    
    /**
     * Gera referências cruzadas (artigos que usam a mesma referência)
     */
    public function getCrossReferences($referenciaId) {
        return $this->db->select(
            'SELECT a.id, a.titulo, a.slug, ar.contexto
             FROM artigos a
             JOIN artigo_referencias ar ON ar.artigo_id = a.id
             WHERE ar.referencia_id = ? AND a.status = "publicado"
             ORDER BY a.titulo',
            [$referenciaId]
        );
    }
    
    /**
     * Importa referências de texto (formato BibTeX simplificado)
     */
    public function importFromBibtex($bibtex) {
        $referencias = [];
        $entries = preg_split('/@(\w+)\s*\{/', $bibtex, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($entries as $entry) {
            if (empty(trim($entry))) continue;
            
            $data = $this->parseBibtexEntry($entry);
            if ($data) {
                $id = $this->create($data);
                if ($id) {
                    $referencias[] = $id;
                }
            }
        }
        
        return $referencias;
    }
    
    /**
     * Parse de entrada BibTeX
     */
    private function parseBibtexEntry($entry) {
        $data = [
            'tipo' => 'livro',
            'titulo' => '',
            'autor' => '',
            'ano' => '',
            'editora' => ''
        ];
        
        // Extrai campos
        preg_match('/title\s*=\s*\{([^}]+)\}/i', $entry, $matches);
        if ($matches) $data['titulo'] = trim($matches[1]);
        
        preg_match('/author\s*=\s*\{([^}]+)\}/i', $entry, $matches);
        if ($matches) $data['autor'] = trim($matches[1]);
        
        preg_match('/year\s*=\s*\{([^}]+)\}/i', $entry, $matches);
        if ($matches) $data['ano'] = (int)trim($matches[1]);
        
        preg_match('/publisher\s*=\s*\{([^}]+)\}/i', $entry, $matches);
        if ($matches) $data['editora'] = trim($matches[1]);
        
        return !empty($data['titulo']) ? $data : null;
    }
}