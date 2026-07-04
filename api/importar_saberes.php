<?php
/**
 * Importador de Saberes de Coração → Portal Saberes Ancestrais
 * Lê dados-unificados.json e insere no MySQL
 * Uso: php importar_saberes.php  ou  acessar via navegador
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();

// Caminho do arquivo JSON (Manjaro)
$jsonPath = '/media/lz-ntn/5109c857-645d-40f2-a6c5-36c96cb83473/@home/lzntn/Documentos/Cofre-Lz_ntn/4_PROJETOS/Saberes-de-Coracao/Saberes_de_Coracao-site-3.0/data/dados-unificados.json';

if (!file_exists($jsonPath)) {
    die("Arquivo JSON não encontrado em: $jsonPath\n");
}

$json = file_get_contents($jsonPath);
$data = json_decode($json, true);

if (!$data || !isset($data['saberes'])) {
    die("JSON inválido ou sem saberes.\n");
}

echo "=== Importador Saberes de Coração ===\n\n";
echo "Meta: " . ($data['meta']['versao'] ?? '?') . " - " . ($data['meta']['atualizado'] ?? '?') . "\n\n";

// ─── 1. Criar tabelas auxiliares se não existirem ───
$db->getPdo()->exec("
    CREATE TABLE IF NOT EXISTS `saberes_conexoes` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `saber_id` VARCHAR(100) NOT NULL,
        `conexao_id` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_saber` (`saber_id`),
        KEY `idx_conexao` (`conexao_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->getPdo()->exec("
    CREATE TABLE IF NOT EXISTS `praticas` (
        `id` VARCHAR(100) NOT NULL,
        `nome` VARCHAR(255) NOT NULL,
        `instrucoes` TEXT DEFAULT NULL,
        `duracao` VARCHAR(50) DEFAULT NULL,
        `frequencia` VARCHAR(100) DEFAULT NULL,
        `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->getPdo()->exec("
    CREATE TABLE IF NOT EXISTS `pratica_saberes` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `pratica_id` VARCHAR(100) NOT NULL,
        `saber_id` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_pratica` (`pratica_id`),
        KEY `idx_saber` (`saber_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->getPdo()->exec("
    CREATE TABLE IF NOT EXISTS `midia_saberes` (
        `id` VARCHAR(100) NOT NULL,
        `titulo` VARCHAR(255) NOT NULL,
        `tipo` ENUM('audio','video') DEFAULT 'audio',
        `arquivo` VARCHAR(500) DEFAULT NULL,
        `categoria` VARCHAR(100) DEFAULT NULL,
        `duracao` INT DEFAULT 0,
        `tags` VARCHAR(500) DEFAULT NULL,
        `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

echo "[OK] Tabelas auxiliares criadas\n";

// ─── 2. Mapa de categorias ───
$catMap = [];
foreach ($data['categorias'] as $cat) {
    $slug = slugify($cat['nome']);
    $existing = $db->fetch("SELECT id FROM categorias WHERE slug = ?", [$slug]);
    if ($existing) {
        $catMap[$cat['id']] = $existing['id'];
    } else {
        $db->insert('categorias', [
            'nome' => $cat['nome'],
            'slug' => $slug,
            'descricao' => $cat['descricao'],
            'icone' => 'bi bi-' . str_replace(['fa-','fa '], '', $cat['icone'] ?? 'stars'),
            'cor' => $cat['cor'] ?? '#f39c12',
            'ordem' => $cat['id']
        ]);
        $catMap[$cat['id']] = $db->getPdo()->lastInsertId();
    }
}
echo "[OK] " . count($catMap) . " categorias mapeadas\n";

// ─── 3. Importar práticas ───
$praticaCount = 0;
foreach ($data['praticas'] ?? [] as $pratica) {
    $existing = $db->fetch("SELECT id FROM praticas WHERE id = ?", [$pratica['id']]);
    if (!$existing) {
        $db->insert('praticas', [
            'id' => $pratica['id'],
            'nome' => $pratica['nome'],
            'instrucoes' => $pratica['instrucoes'] ?? null,
            'duracao' => $pratica['duracao'] ?? null,
            'frequencia' => $pratica['frequencia'] ?? null,
        ]);
        $praticaCount++;
    }
    // Relacionar saberes
    foreach ($pratica['saberes'] ?? [] as $saberId) {
        $rel = $db->fetch("SELECT id FROM pratica_saberes WHERE pratica_id = ? AND saber_id = ?",
            [$pratica['id'], $saberId]);
        if (!$rel) {
            $db->insert('pratica_saberes', [
                'pratica_id' => $pratica['id'],
                'saber_id' => $saberId,
            ]);
        }
    }
}
echo "[OK] $praticaCount práticas importadas\n";

// ─── 4. Importar mídia ───
$midiaCount = 0;
foreach (['audios' => 'audio', 'videos' => 'video'] as $tipo => $tipoLabel) {
    foreach ($data['midia'][$tipo] ?? [] as $item) {
        $existing = $db->fetch("SELECT id FROM midia_saberes WHERE id = ?", [$item['id']]);
        if (!$existing) {
            $db->insert('midia_saberes', [
                'id' => $item['id'],
                'titulo' => $item['titulo'],
                'tipo' => $tipoLabel,
                'arquivo' => $item['arquivo'] ?? null,
                'categoria' => $item['categoria'] ?? null,
                'duracao' => (int)($item['duracao'] ?? 0),
                'tags' => is_array($item['tags'] ?? null) ? implode(',', $item['tags']) : ($item['tags'] ?? null),
            ]);
            $midiaCount++;
        }
    }
}
echo "[OK] $midiaCount mídias importadas\n";

$GLOBALS['db'] = $db;

// ─── 5. Importar saberes como artigos ───
$artigoCount = 0;
foreach ($data['saberes'] as $saber) {
    $slug = $saber['slug'];
    $existing = $db->fetch("SELECT id FROM artigos WHERE slug = ?", [$slug]);
    if ($existing) continue;

    // Converter conteudo JSON → HTML
    $html = converterConteudoParaHtml($saber);
    
    // Processar tags
    $tags = is_array($saber['tags'] ?? null) ? implode(', ', $saber['tags']) : ($saber['tags'] ?? '');
    
    // Nível como categoria extra
    $nivel = $saber['nivel'] ?? 'iniciante';
    $nivelTags = $tags ? "$tags, nivel-$nivel" : "nivel-$nivel";
    if (!empty($saber['conexoes'])) {
        $nivelTags .= ', conexoes: ' . implode(', ', $saber['conexoes']);
    }
    
    $categoriaId = $catMap[$saber['categoria_id']] ?? 1;
    
    $db->insert('artigos', [
        'categoria_id' => $categoriaId,
        'autor_id' => 1,
        'titulo' => $saber['titulo'],
        'slug' => $slug,
        'resumo' => $saber['descricao'] ?? '',
        'conteudo' => $html,
        'tags' => $nivelTags,
        'fonte' => ($saber['fonte'] ?? '') . (!empty($saber['licenca']) ? ' | ' . $saber['licenca'] : ''),
        'status' => 'publicado',
        'views' => 0,
        'publicado_em' => date('Y-m-d H:i:s'),
        'atualizado_em' => date('Y-m-d H:i:s'),
        'criado_em' => date('Y-m-d H:i:s'),
    ]);
    
    $artigoId = $db->getPdo()->lastInsertId();
    $artigoCount++;
    
    // Relacionar conexões entre saberes
    foreach ($saber['conexoes'] ?? [] as $conexaoId) {
        $db->insert('saberes_conexoes', [
            'saber_id' => $saber['id'],
            'conexao_id' => $conexaoId,
        ]);
    }
}

echo "[OK] $artigoCount artigos importados\n";

// ─── 6. Importar citações dos saberes como quotes ───
$quoteCount = 0;
foreach ($data['saberes'] as $saber) {
    $citacoes = $saber['conteudo']['citacoes'] ?? [];
    if (is_string($citacoes)) $citacoes = [$citacoes];
    
    foreach ($citacoes as $cit) {
        if (is_string($cit)) {
            $texto = $cit;
            $autor = $saber['fonte'] ?? '';
        } elseif (is_array($cit)) {
            $texto = $cit['texto'] ?? $cit['citacao'] ?? json_encode($cit);
            $autor = $cit['autor'] ?? $saber['fonte'] ?? '';
        } else continue;
        
        if (mb_strlen($texto) < 20) continue;
        
        $existing = $db->fetch("SELECT id FROM citacoes WHERE texto = ?", [$texto]);
        if (!$existing) {
            $db->insert('citacoes', [
                'texto' => $texto,
                'autor' => $autor,
                'fonte' => $saber['titulo'],
                'categoria' => 'saberes-coracao',
                'tags' => implode(',', $saber['tags'] ?? []),
                'idioma' => 'pt-BR',
                'ativo' => 1,
            ]);
            $quoteCount++;
        }
    }
}
echo "[OK] $quoteCount citações importadas\n";

echo "\n=== Importação concluída! ===\n";
echo "Resumo:\n";
echo "  Categorias: " . count($catMap) . "\n";
echo "  Práticas: $praticaCount\n";
echo "  Mídias: $midiaCount\n";
echo "  Artigos: $artigoCount\n";
echo "  Citações: $quoteCount\n";

// ═══════════════════════════════════════════
// FUNÇÃO: Converte conteúdo JSON → HTML
// ═══════════════════════════════════════════
function esc_safe($val) {
    if (is_string($val)) return esc($val);
    if (is_array($val)) return esc(json_encode($val, JSON_UNESCAPED_UNICODE));
    return esc((string)$val);
}

function renderParagraph($key, $c) {
    if (empty($c[$key])) return '';
    $html = '<h2>' . esc(ucwords(str_replace('_', ' ', $key))) . '</h2>';
    if (is_array($c[$key])) {
        $isAssoc = array_keys($c[$key]) !== range(0, count($c[$key]) - 1);
        if ($isAssoc) {
            $html .= '<ul>';
            foreach ($c[$key] as $k => $v) {
                $html .= '<li><strong>' . esc(ucfirst($k)) . ':</strong> ' . esc(is_string($v) ? $v : '') . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<ul>';
            foreach ($c[$key] as $item) {
                $html .= '<li>' . esc(is_string($item) ? $item : (is_array($item) ? ($item['nome'] ?? $item['titulo'] ?? '') : '')) . '</li>';
            }
            $html .= '</ul>';
        }
    } else {
        $html .= '<p>' . nl2br(esc($c[$key])) . '</p>';
    }
    return $html;
}

function converterConteudoParaHtml($saber) {
    $c = $saber['conteudo'] ?? [];
    $html = '';
    
    // Definição
    if (!empty($c['definicao'])) {
        $html .= '<h2>Definição</h2>';
        $html .= '<p>' . nl2br(esc($c['definicao'])) . '</p>';
    }
    
    // Descrição direta (dado/desc format)
    if (!empty($c['desc'])) {
        $html .= '<p>' . nl2br(esc($c['desc'])) . '</p>';
    }
    if (!empty($c['dado'])) {
        $html .= '<blockquote>' . nl2br(esc($c['dado'])) . '</blockquote>';
    }
    
    // Conceitos
    if (!empty($c['conceitos']) && is_array($c['conceitos'])) {
        $html .= '<h2>Conceitos</h2>';
        foreach ($c['conceitos'] as $conceito) {
            if (is_string($conceito)) {
                $html .= '<p>' . esc($conceito) . '</p>';
            } elseif (is_array($conceito)) {
                $termo = $conceito['termo'] ?? $conceito['nome'] ?? '';
                $def = $conceito['def'] ?? $conceito['descricao'] ?? '';
                $html .= '<div class="destaque-box">';
                if ($termo) $html .= '<strong>' . esc($termo) . ':</strong> ';
                $html .= esc($def) . '</div>';
            }
        }
    }
    
    // Analogia
    if (!empty($c['analogia'])) {
        $html .= '<h2>Analogia</h2>';
        $html .= '<blockquote>' . nl2br(esc($c['analogia'])) . '</blockquote>';
    }
    
    // Insight principal
    if (!empty($c['insight'])) {
        $html .= '<h2>Insight</h2>';
        $html .= '<div class="destaque-box">' . nl2br(esc($c['insight'])) . '</div>';
    }
    
    // Princípios
    if (!empty($c['principios']) && is_array($c['principios'])) {
        $html .= '<h2>Princípios</h2><ul>';
        foreach ($c['principios'] as $p) {
            $html .= '<li>' . esc(is_string($p) ? $p : ($p['nome'] ?? $p['principio'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Benefícios
    if (!empty($c['beneficios']) && is_array($c['beneficios'])) {
        $html .= '<h2>Benefícios</h2><ul>';
        foreach ($c['beneficios'] as $b) {
            $html .= '<li>' . esc(is_string($b) ? $b : ($b['nome'] ?? $b['beneficio'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Técnica / Instruções Passo a Passo
    if (!empty($c['tecnica'])) {
        $html .= renderParagraph('tecnica', $c);
    }
    if (!empty($c['instrucoes_passos']) && is_array($c['instrucoes_passos'])) {
        $html .= '<h2>Instruções Passo a Passo</h2><ol>';
        foreach ($c['instrucoes_passos'] as $passo) {
            $html .= '<li>' . esc(is_string($passo) ? $passo : ($passo['passo'] ?? $passo['instrucao'] ?? '')) . '</li>';
        }
        $html .= '</ol>';
    }
    
    // Aplicações
    if (!empty($c['aplicacoes']) && is_array($c['aplicacoes'])) {
        $html .= '<h2>Aplicações</h2><ul>';
        foreach ($c['aplicacoes'] as $a) {
            $html .= '<li>' . esc(is_string($a) ? $a : ($a['aplicacao'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Características / Desafios
    if (!empty($c['caracteristicas']) && is_array($c['caracteristicas'])) {
        $html .= '<h2>Características</h2><ul>';
        foreach ($c['caracteristicas'] as $item) {
            $html .= '<li>' . esc(is_string($item) ? $item : ($item['nome'] ?? $item['caracteristica'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['desafios']) && is_array($c['desafios'])) {
        $html .= '<h2>Desafios</h2><ul>';
        foreach ($c['desafios'] as $item) {
            $html .= '<li>' . esc(is_string($item) ? $item : ($item['desafio'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Chakras
    if (!empty($c['chakras']) && is_array($c['chakras'])) {
        $html .= '<h2>Chakras</h2>';
        foreach ($c['chakras'] as $ch) {
            if (is_string($ch)) {
                $html .= '<p>' . esc($ch) . '</p>';
            } elseif (is_array($ch)) {
                $html .= '<div class="destaque-box"><strong>' . esc($ch['nome'] ?? '') . '</strong>';
                if (!empty($ch['funcao'])) $html .= '<br>' . esc($ch['funcao']);
                if (!empty($ch['local'])) $html .= '<br><em>Local: ' . esc($ch['local']) . '</em>';
                $html .= '</div>';
            }
        }
    }
    
    // Mantras
    if (!empty($c['mantras'])) {
        $html .= '<h2>Mantras</h2>';
        $mantras = is_array($c['mantras']) ? $c['mantras'] : [$c['mantras']];
        foreach ($mantras as $m) {
            $html .= '<blockquote>' . esc(is_string($m) ? $m : ($m['mantra'] ?? $m['texto'] ?? '')) . '</blockquote>';
        }
    }
    
    // Três Fatores (gnose específico)
    if (!empty($c['tres_fatores']) && is_array($c['tres_fatores'])) {
        $html .= '<h2>Os Três Fatores da Revolução da Consciência</h2><ul>';
        foreach ($c['tres_fatores'] as $f) {
            $html .= '<li><strong>' . esc($f['nome'] ?? '') . ':</strong> ' . esc($f['def'] ?? $f['descricao'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Personagens
    if (!empty($c['personagens']) && is_array($c['personagens'])) {
        $html .= '<h2>Personagens</h2>';
        foreach ($c['personagens'] as $p) {
            $html .= '<div class="destaque-box"><strong>' . esc($p['nome'] ?? '') . '</strong>';
            if (!empty($p['papeis'])) $html .= '<br><em>Papéis: ' . esc(implode(', ', is_array($p['papeis']) ? $p['papeis'] : [$p['papeis']])) . '</em>';
            if (!empty($p['descricao'])) $html .= '<br>' . esc($p['descricao']);
            $html .= '</div>';
        }
    }
    
    // Mundos / Dimensões
    if (!empty($c['mundos']) && is_array($c['mundos'])) {
        $html .= '<h2>Mundos</h2><ul>';
        foreach ($c['mundos'] as $m) {
            $html .= '<li><strong>' . esc($m['nome'] ?? '') . ':</strong> ' . esc($m['descricao'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['dimensoes']) && is_array($c['dimensoes'])) {
        $html .= '<h2>Dimensões</h2><ul>';
        foreach ($c['dimensoes'] as $d) {
            $html .= '<li><strong>' . esc($d['nome'] ?? '') . ':</strong> ' . esc($d['descricao'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Ciência moderna / Mecanismos
    if (!empty($c['ciencia_moderna'])) {
        $html .= renderParagraph('ciencia_moderna', $c);
    }
    if (!empty($c['mecanismos']) && is_array($c['mecanismos'])) {
        $html .= '<h2>Mecanismos</h2><ul>';
        foreach ($c['mecanismos'] as $m) {
            $html .= '<li>' . esc(is_string($m) ? $m : ($m['nome'] ?? $m['mecanismo'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Alquimia interior
    if (!empty($c['alquimia_interior'])) {
        $html .= renderParagraph('alquimia_interior', $c);
    }
    
    // Visões / Textos integrais
    if (!empty($c['visoes']) && is_array($c['visoes'])) {
        $html .= '<h2>Visões</h2><ul>';
        foreach ($c['visoes'] as $v) {
            $html .= '<li>' . esc(is_string($v) ? $v : ($v['visao'] ?? $v['nome'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['textos']) && is_array($c['textos'])) {
        $html .= '<h2>Textos</h2>';
        foreach ($c['textos'] as $t) {
            $html .= '<blockquote>' . esc(is_string($t) ? $t : ($t['texto'] ?? $t['trecho'] ?? '')) . '</blockquote>';
        }
    }
    if (!empty($c['texto_integral'])) {
        $html .= '<h2>Texto Integral</h2>';
        $html .= '<div class="destaque-box">' . nl2br(esc($c['texto_integral'])) . '</div>';
    }
    
    // Ensinamentos chave / Parábolas
    if (!empty($c['ensinamentos_chave']) && is_array($c['ensinamentos_chave'])) {
        $html .= '<h2>Ensinamentos Chave</h2><ul>';
        foreach ($c['ensinamentos_chave'] as $e) {
            $html .= '<li>' . esc(is_string($e) ? $e : ($e['nome'] ?? $e['ensino'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['parabolas']) && is_array($c['parabolas'])) {
        $html .= '<h2>Parábolas</h2>';
        foreach ($c['parabolas'] as $p) {
            $html .= '<blockquote>' . esc(is_string($p) ? $p : ($p['texto'] ?? $p['parabola'] ?? '')) . '</blockquote>';
        }
    }
    
    // Correntes / Controvérsias
    if (!empty($c['correntes']) && is_array($c['correntes'])) {
        $html .= '<h2>Correntes</h2><ul>';
        foreach ($c['correntes'] as $cor) {
            $html .= '<li><strong>' . esc($cor['nome'] ?? '') . ':</strong> ' . esc($cor['descricao'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['controversias'])) {
        $html .= '<h2>Controvérsias</h2>';
        if (is_array($c['controversias'])) {
            $html .= '<ul>';
            foreach ($c['controversias'] as $cont) {
                if (is_string($cont)) {
                    $html .= '<li>' . esc($cont) . '</li>';
                } elseif (is_array($cont)) {
                    $html .= '<li><strong>' . esc($cont['tema'] ?? $cont['nome'] ?? '') . ':</strong> ' . esc($cont['desc'] ?? $cont['descricao'] ?? '') . '</li>';
                }
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>' . nl2br(esc($c['controversias'])) . '</p>';
        }
    }
    
    // Práticas relacionadas
    if (!empty($c['praticas_diarias']) && is_array($c['praticas_diarias'])) {
        $html .= '<h2>Práticas Diárias</h2><ul>';
        foreach ($c['praticas_diarias'] as $p) {
            $html .= '<li>' . esc(is_string($p) ? $p : ($p['pratica'] ?? $p['nome'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['praticas_acesso'])) {
        $html .= renderParagraph('praticas_acesso', $c);
    }
    
    // Conexões / Pontes
    if (!empty($c['conexao_heartmath'])) {
        $html .= renderParagraph('conexao_heartmath', $c);
    }
    if (!empty($c['ponte_ciencia_teosofia'])) {
        $html .= renderParagraph('ponte_ciencia_teosofia', $c);
    }
    
    // Estrutura cósmica / Mistérios
    if (!empty($c['estrutura_cosmica'])) {
        $html .= '<h2>Estrutura Cósmica</h2>';
        if (is_array($c['estrutura_cosmica'])) {
            $html .= '<ul>';
            foreach ($c['estrutura_cosmica'] as $k => $v) {
                $html .= '<li><strong>' . esc(ucfirst($k)) . ':</strong> ' . esc(is_string($v) ? $v : '') . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>' . nl2br(esc($c['estrutura_cosmica'])) . '</p>';
        }
    }
    if (!empty($c['misterios']) && is_array($c['misterios'])) {
        $html .= '<h2>Mistérios</h2><ul>';
        foreach ($c['misterios'] as $m) {
            $html .= '<li>' . esc(is_string($m) ? $m : ($m['misterio'] ?? $m['nome'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Filtros da percepção / Mapas
    if (!empty($c['filtros_da_percepcao']) && is_array($c['filtros_da_percepcao'])) {
        $html .= '<h2>Filtros da Percepção</h2><ul>';
        foreach ($c['filtros_da_percepcao'] as $f) {
            $html .= '<li>' . esc(is_string($f) ? $f : ($f['nome'] ?? $f['filtro'] ?? '')) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['quatro_mapas']) && is_array($c['quatro_mapas'])) {
        $html .= '<h2>Quatro Mapas</h2><ul>';
        foreach ($c['quatro_mapas'] as $m) {
            $html .= '<li><strong>' . esc($m['nome'] ?? '') . ':</strong> ' . esc($m['descricao'] ?? '') . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($c['mapa_exoterico_esoterico'])) {
        $html .= renderParagraph('mapa_exoterico_esoterico', $c);
    }
    
    // Miscelânea
    foreach (['dicotomia', 'felicidade_como_verbo', 'if_no_meaning', 'ruido_moderno', 'ascensao', 'sintese', 'tool_musica', 'nag_hammadi', 'aviso', 'perspectivas', 'integracao_pratica', 'tres_filtros', 'ferramentas_praticas', 'mitos_desmistificados', 'obras'] as $key) {
        if (!empty($c[$key])) {
            $label = ucfirst(str_replace('_', ' ', $key));
            $html .= '<h2>' . esc(ucwords($label)) . '</h2>';
            if (is_array($c[$key])) {
                $html .= '<ul>';
                foreach ($c[$key] as $item) {
                    $html .= '<li>' . esc(is_string($item) ? $item : ($item['nome'] ?? $item['titulo'] ?? $item['obra'] ?? json_encode($item))) . '</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>' . nl2br(esc($c[$key])) . '</p>';
            }
        }
    }
    
    // Fonte
    if (!empty($saber['fonte'])) {
        $html .= '<div class="artigo-fonte">Fonte: ' . esc($saber['fonte']);
        if (!empty($saber['licenca'])) $html .= ' | ' . esc($saber['licenca']);
        $html .= '</div>';
    }
    
    // Práticas relacionadas (do BD)
    $praticasRel = $GLOBALS['db']->select(
        "SELECT p.* FROM praticas p JOIN pratica_saberes ps ON ps.pratica_id = p.id WHERE ps.saber_id = ?",
        [$saber['id']]
    );
    if ($praticasRel) {
        $html .= '<h2>Práticas Relacionadas</h2><ul>';
        foreach ($praticasRel as $p) {
            $html .= '<li><strong>' . esc($p['nome']) . '</strong>';
            if ($p['duracao']) $html .= ' — ' . esc($p['duracao']);
            if ($p['frequencia']) $html .= ' (' . esc($p['frequencia']) . ')';
            if ($p['instrucoes']) $html .= '<br>' . nl2br(esc($p['instrucoes']));
            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    
    // Mídia relacionada
    $midiaRel = $GLOBALS['db']->select(
        "SELECT * FROM midia_saberes WHERE tags LIKE ?",
        ['%' . $saber['id'] . '%']
    );
    if (empty($midiaRel) && !empty($saber['tags'])) {
        $tagLike = '%' . (is_array($saber['tags']) ? $saber['tags'][0] : $saber['tags']) . '%';
        $midiaRel = $GLOBALS['db']->select(
            "SELECT * FROM midia_saberes WHERE tags LIKE ? LIMIT 3",
            [$tagLike]
        );
    }
    if ($midiaRel) {
        $html .= '<h2>Mídia Relacionada</h2><ul>';
        foreach ($midiaRel as $m) {
            $icone = $m['tipo'] === 'audio' ? '🎵' : '🎬';
            $link = $m['arquivo'] ? (' <a href="' . esc($m['arquivo']) . '" target="_blank">Ouvir</a>') : '';
            $html .= '<li>' . $icone . ' ' . esc($m['titulo']) . $link . '</li>';
        }
        $html .= '</ul>';
    }
    
    // Conexões com outros saberes
    $conexoes = $GLOBALS['db']->select(
        "SELECT sc.conexao_id, a.titulo, a.slug 
         FROM saberes_conexoes sc 
         LEFT JOIN artigos a ON a.slug = sc.conexao_id
         WHERE sc.saber_id = ?",
        [$saber['id']]
    );
    if ($conexoes) {
        $html .= '<h2>Saberes Relacionados</h2><ul>';
        foreach ($conexoes as $cx) {
            if ($cx['titulo']) {
                $html .= '<li><a href="' . APP_URL . '/artigo.php?slug=' . esc($cx['slug']) . '">' . esc($cx['titulo']) . '</a></li>';
            } else {
                $html .= '<li>' . esc($cx['conexao_id']) . '</li>';
            }
        }
        $html .= '</ul>';
    }
    
    // Tags/nível
    $html .= '<div class="artigo-tags">';
    $tags = is_array($saber['tags'] ?? null) ? $saber['tags'] : [];
    foreach ($tags as $tag) {
        $html .= '<span class="tag">#' . esc(trim($tag)) . '</span>';
    }
    $html .= '<span class="tag" style="background:var(--accent);color:#1a1a2e;">' . esc(ucfirst($saber['nivel'] ?? 'iniciante')) . '</span>';
    $html .= '</div>';
    
    return $html;
}
