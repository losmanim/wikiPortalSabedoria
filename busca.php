<?php
/**
 * Busca Avançada no Portal
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$q = trim($_GET['q'] ?? '');
$categoria = $_GET['categoria'] ?? '';
$ordenar = $_GET['ordenar'] ?? 'relevancia';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$resultados = [];
$total = 0;

if (strlen($q) >= 2) {
    $termo = '%' . $q . '%';
    $params = [$termo, $termo, $termo, $termo];
    $sql = 'SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone
            FROM artigos a
            LEFT JOIN categorias c ON c.id = a.categoria_id
            WHERE a.status = "publicado"
              AND (a.titulo LIKE ? OR a.resumo LIKE ? OR a.conteudo LIKE ? OR a.tags LIKE ?)';
    
    // Filtro por categoria
    if ($categoria) {
        $sql .= ' AND c.slug = ?';
        $params[] = $categoria;
    }
    
    // Filtro por data
    if ($data_inicio) {
        $sql .= ' AND a.publicado_em >= ?';
        $params[] = $data_inicio . ' 00:00:00';
    }
    
    if ($data_fim) {
        $sql .= ' AND a.publicado_em <= ?';
        $params[] = $data_fim . ' 23:59:59';
    }
    
    // Ordenação
    switch ($ordenar) {
        case 'recentes':
            $sql .= ' ORDER BY a.publicado_em DESC';
            break;
        case 'antigos':
            $sql .= ' ORDER BY a.publicado_em ASC';
            break;
        case 'views':
            $sql .= ' ORDER BY a.views DESC';
            break;
        case 'titulo':
            $sql .= ' ORDER BY a.titulo ASC';
            break;
        default:
            $sql .= ' ORDER BY a.publicado_em DESC';
    }
    
    $sql .= ' LIMIT 50';
    $resultados = $db->select($sql, $params);
    $total = count($resultados);
}

$titulo = $q ? "Busca: $q" : 'Buscar';
require_once __DIR__ . '/includes/header.php';
?>

<div class="busca-page">
    <h1><i class="bi bi-search"></i> Busca Avançada</h1>
    
    <!-- Formulário de Busca -->
    <div class="busca-advanced">
        <form class="busca-form" method="GET">
            <input type="text" name="q" placeholder="Digite palavras-chave..." value="<?= esc($q) ?>" required minlength="2">
            <button type="submit"><i class="bi bi-search"></i> Buscar</button>
        </form>
        
        <!-- Filtros -->
        <div class="busca-filters">
            <div class="filter-group">
                <label><i class="bi bi-grid-3x3"></i> Categoria</label>
                <select name="categoria">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= esc($cat['slug']) ?>" <?= $categoria === $cat['slug'] ? 'selected' : '' ?>>
                        <?= esc($cat['nome']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="bi bi-sort"></i> Ordenar por</label>
                <select name="ordenar">
                    <option value="relevancia" <?= $ordenar === 'relevancia' ? 'selected' : '' ?>>Relevância</option>
                    <option value="recentes" <?= $ordenar === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                    <option value="antigos" <?= $ordenar === 'antigos' ? 'selected' : '' ?>>Mais antigos</option>
                    <option value="views" <?= $ordenar === 'views' ? 'selected' : '' ?>>Mais visualizados</option>
                    <option value="titulo" <?= $ordenar === 'titulo' ? 'selected' : '' ?>>Título A-Z</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="bi bi-calendar"></i> Data Início</label>
                <input type="date" name="data_inicio" value="<?= esc($data_inicio) ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="bi bi-calendar"></i> Data Fim</label>
                <input type="date" name="data_fim" value="<?= esc($data_fim) ?>">
            </div>
            
            <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Aplicar Filtros</button>
        </div>
    </div>

    <?php if ($q): ?>
        <div class="busca-results-info">
            <?php if ($total > 0): ?>
                <p><?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?> para "<?= esc($q) ?>"</p>
                <?php if ($categoria || $data_inicio || $data_fim): ?>
                    <p class="filters-active">Filtros ativos - <a href="?q=<?= urlencode($q) ?>" class="clear-filters">Limpar filtros</a></p>
                <?php endif; ?>
            <?php else: ?>
                <p>Nenhum resultado para "<?= esc($q) ?>"</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($resultados)): ?>
            <div class="resultados-grid">
                <?php foreach ($resultados as $r): ?>
                <div class="resultado-item">
                    <div class="resultado-cat">
                        <i class="<?= esc($r['cat_icone'] ?? 'bi bi-folder') ?>"></i> 
                        <?= esc($r['cat_nome'] ?? 'Sem categoria') ?>
                    </div>
                    <h3><a href="artigo.php?slug=<?= esc($r['slug']) ?>"><?= esc($r['titulo']) ?></a></h3>
                    <p class="resultado-resumo"><?= resumir($r['resumo'] ?: $r['conteudo'], 200) ?></p>
                    <div class="resultado-meta">
                        <span><i class="bi bi-eye"></i> <?= number_format($r['views']) ?> views</span>
                        <span><i class="bi bi-calendar"></i> <?= tempo_relativo($r['publicado_em']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p style="font-size:3rem;margin-bottom:15px">🔍</p>
                <p>Nenhum resultado encontrado. Tente:</p>
                <ul>
                    <li>Termos diferentes: gnose, epigenética, meditação, chakras, Jesus...</li>
                    <li>Verificar os filtros aplicados</li>
                    <li>Remover restrições de data ou categoria</li>
                </ul>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="busca-suggestions">
            <h3><i class="bi bi-lightbulb"></i> Sugestões de Busca</h3>
            <div class="suggestions-tags">
                <a href="?q=gnose" class="suggestion-tag">gnose</a>
                <a href="?q=consciencia" class="suggestion-tag">consciência</a>
                <a href="?q=jesus" class="suggestion-tag">Jesus</a>
                <a href="?q=meditacao" class="suggestion-tag">meditação</a>
                <a href="?q=epigenetica" class="suggestion-tag">epigenética</a>
                <a href="?q=hermetismo" class="suggestion-tag">hermetismo</a>
                <a href="?q=teosofia" class="suggestion-tag">teosofia</a>
                <a href="?q=arcontes" class="suggestion-tag">arcontes</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
