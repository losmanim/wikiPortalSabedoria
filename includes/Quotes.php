<?php
/**
 * Sistema de Citações Aleatórias
 */

class Quotes {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtém citação aleatória
     */
    public function getRandomQuote($categoria = null) {
        $sql = 'SELECT * FROM citacoes WHERE ativo = 1';
        $params = [];
        
        if ($categoria) {
            $sql .= ' AND categoria = ?';
            $params[] = $categoria;
        }
        
        $sql .= ' ORDER BY RAND() LIMIT 1';
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Obtém citação do dia (baseada na data)
     */
    public function getDailyQuote() {
        $dayOfYear = date('z'); // 0-365
        $sql = 'SELECT * FROM citacoes WHERE ativo = 1 ORDER BY id LIMIT 1 OFFSET ?';
        
        $total = $this->db->fetch('SELECT COUNT(*) as total FROM citacoes WHERE ativo = 1')['total'];
        if ($total == 0) return null;
        
        $offset = $dayOfYear % $total;
        
        return $this->db->fetch($sql, [$offset]);
    }
    
    /**
     * Adiciona citação
     */
    public function addQuote($data) {
        return $this->db->insert('citacoes', [
            'texto' => $data['texto'],
            'autor' => $data['autor'] ?? null,
            'fonte' => $data['fonte'] ?? null,
            'categoria' => $data['categoria'] ?? null,
            'tags' => $data['tags'] ?? null,
            'idioma' => $data['idioma'] ?? 'pt-BR',
            'ativo' => $data['ativo'] ?? 1
        ]);
    }
    
    /**
     * Lista citações
     */
    public function listQuotes($limit = 50, $offset = 0, $categoria = null) {
        $sql = 'SELECT * FROM citacoes WHERE ativo = 1';
        $params = [];
        
        if ($categoria) {
            $sql .= ' AND categoria = ?';
            $params[] = $categoria;
        }
        
        $sql .= ' ORDER BY criado_em DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Busca citações
     */
    public function searchQuotes($termo) {
        return $this->db->select(
            'SELECT * FROM citacoes 
             WHERE ativo = 1 AND (texto LIKE ? OR autor LIKE ? OR tags LIKE ?)
             ORDER BY criado_em DESC',
            ['%' . $termo . '%', '%' . $termo . '%', '%' . $termo . '%']
        );
    }
    
    /**
     * Obtém citações por categoria
     */
    public function getByCategory($categoria, $limit = 20) {
        return $this->db->select(
            'SELECT * FROM citacoes 
             WHERE ativo = 1 AND categoria = ? 
             ORDER BY RAND() 
             LIMIT ?',
            [$categoria, $limit]
        );
    }
    
    /**
     * Importa citações do conteúdo gnóstico
     */
    public function importGnosticQuotes() {
        $quotes = [
            [
                'texto' => 'É necessário compreendermos a necessidade de trabalharmos com os três Fatores da Revolução da Consciência se é que realmente queremos a Auto-Realização profunda.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Tratado de Psicologia Revolucionária',
                'categoria' => 'gnosis',
                'tags' => 'tres fatores, revolução da consciência, auto-realização'
            ],
            [
                'texto' => 'Nascer, Morrer e Sacrificar-se pela humanidade constituem-se nos três fatores básicos para a Revolução da Consciência.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Tratado de Psicologia Revolucionária',
                'categoria' => 'gnosis',
                'tags' => 'nascer, morrer, sacrificar, revolução da consciência'
            ],
            [
                'texto' => 'O sábio não é vítima das circunstâncias, mas senhor de si mesmo.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Mundo das Relações',
                'categoria' => 'sabedoria',
                'tags' => 'sabedoria, autocontrole, circunstâncias'
            ],
            [
                'texto' => 'Isolar-se não é a solução; é necessário conviver para trabalhar psicológicamente.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Mundo das Relações',
                'categoria' => 'psicologia',
                'tags' => 'relacionamentos, trabalho psicológico, isolamento'
            ],
            [
                'texto' => 'Eu sou o Caminho, a Verdade e a Vida. Ninguém vem ao Pai senão por mim.',
                'autor' => 'Jesus Cristo',
                'fonte' => 'João 14:6',
                'categoria' => 'cristianismo',
                'tags' => 'caminho, verdade, vida, jesus'
            ],
            [
                'texto' => 'Gnosis é conhecimento direto. Você não "acredita" no divino — você sabe por experiência, por revelação interior.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Evangelho de Tomé',
                'categoria' => 'gnosis',
                'tags' => 'gnosis, conhecimento direto, experiência interior'
            ],
            [
                'texto' => 'O Reino está dentro de vocês e fora de vocês. Quando conhecerem a si mesmos, serão conhecidos.',
                'autor' => 'Jesus Cristo',
                'fonte' => 'Evangelho de Tomé',
                'categoria' => 'gnosis',
                'tags' => 'reino, autoconhecimento, interior'
            ],
            [
                'texto' => 'A verdade liberta porque dissolve as ilusões da separação.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Ensinamentos Gnósticos',
                'categoria' => 'verdade',
                'tags' => 'verdade, liberdade, ilusão, separação'
            ],
            [
                'texto' => 'O verdadeiro Deus (o Inefável, a Fonte) não criou este caos cheio de sofrimento, ignorância e morte.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Nag Hammadi',
                'categoria' => 'gnosis',
                'tags' => 'deus, inefável, criação, sofrimento'
            ],
            [
                'texto' => 'Salvação não vem de sacrifício vicário ou fé cega, mas de gnosis — conhecer a si mesmo, conhecer o divino dentro.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Ensinamentos Gnósticos',
                'categoria' => 'gnosis',
                'tags' => 'salvação, gnosis, autoconhecimento, divino'
            ],
            [
                'texto' => 'Enquanto o ser humano não retornar à natureza, seus pensamentos permanecem superficiais e artificiais.',
                'autor' => 'Mestres do Raio Maia',
                'fonte' => 'Sabedoria Ancestral',
                'categoria' => 'natureza',
                'tags' => 'natureza, pensamentos, superficialidade, sabedoria'
            ],
            [
                'texto' => 'É fundamental abandonar os falsos templos e ídolos da vida urbana para voltar ao seio da Deusa Mãe do Mundo.',
                'autor' => 'Mestres do Raio Maia',
                'fonte' => 'Sabedoria Ancestral',
                'categoria' => 'natureza',
                'tags' => 'natureza, deusa mãe, templos, urbanização'
            ],
            [
                'texto' => 'A má relação consigo mesmo (falta de trabalho interior) gera conflitos inevitáveis com o mundo externo.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Mundo das Relações',
                'categoria' => 'psicologia',
                'tags' => 'auto-relacionamento, conflitos, mundo externo, trabalho interior'
            ],
            [
                'texto' => 'O objetivo final é a iluminação e a libertação das prisões do ego, permitindo que a alma se eleve.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Mundo das Relações',
                'categoria' => 'iluminação',
                'tags' => 'iluminação, libertação, ego, alma'
            ],
            [
                'texto' => 'O exterior é um reflexo do interior.',
                'autor' => 'Samael Aun Weor',
                'fonte' => 'Mundo das Relações',
                'categoria' => 'sabedoria',
                'tags' => 'reflexo, interior, exterior, sabedoria'
            ],
            [
                'texto' => 'Gnosis é crua. Exige trabalho interior — auto-observação implacável, dissolução do ego, enfrentamento das sombras.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Ensinamentos Gnósticos',
                'categoria' => 'gnosis',
                'tags' => 'gnosis, trabalho interior, auto-observação, ego, sombras'
            ],
            [
                'texto' => 'Quem sou eu realmente? Além do nome, corpo, história, papéis sociais.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Autoconhecimento',
                'categoria' => 'autoconhecimento',
                'tags' => 'quem sou eu, identidade, autoconhecimento'
            ],
            [
                'texto' => 'O Caminho é prático: une vida cotidiana (o horizontal) com o vertical (busca do Real).',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Caminho Prático',
                'categoria' => 'prática',
                'tags' => 'caminho, prática, horizontal, vertical, real'
            ],
            [
                'texto' => 'Mestres, livros, igrejas ajudam, mas a gnosis é direta. O Cristo interior é o guia verdadeiro.',
                'autor' => 'Tradição Gnóstica',
                'fonte' => 'Cristo Interior',
                'categoria' => 'gnosis',
                'tags' => 'mestres, cristo interior, gnosis direta'
            ],
            [
                'texto' => 'A Deusa Natureza acolhe o estudante, concedendo-lhe amor, sabedoria, alimento e abrigo.',
                'autor' => 'Mestres do Raio Maia',
                'fonte' => 'Sabedoria Ancestral',
                'categoria' => 'natureza',
                'tags' => 'deusa natureza, amor, sabedoria, abrigo'
            ]
        ];
        
        $importadas = 0;
        foreach ($quotes as $quote) {
            $existing = $this->db->fetch(
                'SELECT id FROM citacoes WHERE texto = ?',
                [$quote['texto']]
            );
            
            if (!$existing) {
                $this->addQuote($quote);
                $importadas++;
            }
        }
        
        return $importadas;
    }
}