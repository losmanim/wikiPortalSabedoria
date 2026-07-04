<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = 'Biblioteca';
$descricao = 'Biblioteca completa dos Saberes Ancestrais — todo o conhecimento organizado por temas';
require_once __DIR__ . '/includes/header.php';

$totalArtigos = $db->contar('artigos', 'status = "publicado"');
$totalCategorias = count($categorias);

// Buscar todos os artigos
$todosArtigos = $db->select(
    'SELECT a.id, a.titulo, a.slug, a.resumo, a.tags, a.views, a.publicado_em,
            c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone, c.cor as cat_cor, c.id as cat_id
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     WHERE a.status = "publicado"
     ORDER BY c.ordem, a.publicado_em DESC'
);

$artigosPorCategoria = [];
$todasTags = [];
$totalViews = 0;
foreach ($todosArtigos as $a) {
    $catSlug = $a['cat_slug'] ?? 'sem-categoria';
    if (!isset($artigosPorCategoria[$catSlug])) {
        $artigosPorCategoria[$catSlug] = [
            'nome' => $a['cat_nome'] ?? 'Sem categoria',
            'slug' => $catSlug,
            'icone' => $a['cat_icone'] ?? 'bi bi-folder',
            'cor' => $a['cat_cor'] ?? '#666',
            'artigos' => []
        ];
    }
    $artigosPorCategoria[$catSlug]['artigos'][] = $a;
    $totalViews += (int)$a['views'];
}

// Coletar todas as tags únicas
foreach ($todosArtigos as $a) {
    if ($a['tags']) {
        foreach (explode(',', $a['tags']) as $tag) {
            $tag = trim($tag);
            if ($tag) $todasTags[$tag] = ($todasTags[$tag] ?? 0) + 1;
        }
    }
}
ksort($todasTags);
?>
<style>
.biblioteca-hero {
    text-align: center; padding: 60px 20px 30px; position: relative;
}
.biblioteca-hero h1 {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--accent), var(--accent2), #9b59b6);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    margin-bottom: 8px;
}
.biblioteca-hero p {
    max-width: 600px; margin: 0 auto 24px; opacity: .7; font-size: 1.05rem;
}

/* ── Stats ── */
.biblioteca-stats {
    display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px;
}
.stat-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 14px; padding: 18px 28px; text-align: center; min-width: 110px;
    backdrop-filter: blur(10px); transition: transform .2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .number {
    font-size: 1.8rem; font-weight: 700;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.stat-card .label { font-size: .8rem; opacity: .55; margin-top: 2px; }

/* ── Toolbar ── */
.biblioteca-toolbar {
    display: flex; flex-wrap: wrap; gap: 12px; align-items: center;
    margin-bottom: 28px; padding: 16px 20px;
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 14px;
}
.biblioteca-toolbar .search-wrap {
    flex: 1; min-width: 200px; position: relative;
}
.biblioteca-toolbar .search-wrap i {
    position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
    opacity: .4; font-size: 1rem;
}
.biblioteca-toolbar .search-wrap input {
    width: 100%; padding: 10px 14px 10px 40px;
    background: var(--bg3); border: 1px solid var(--border);
    border-radius: 8px; color: var(--text); font-size: .9rem;
    font-family: var(--font); transition: border-color .2s;
}
.biblioteca-toolbar .search-wrap input:focus {
    outline: none; border-color: var(--accent);
}
.biblioteca-toolbar .search-wrap .clear-btn {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--text2); cursor: pointer;
    display: none; font-size: 1.1rem;
}
.toolbar-group {
    display: flex; gap: 6px; align-items: center;
}
.toolbar-group label { font-size: .78rem; opacity: .5; margin-right: 4px; }
.toolbar-btn {
    padding: 8px 14px; border-radius: 8px;
    background: var(--bg3); border: 1px solid var(--border);
    color: var(--text2); font-size: .82rem; cursor: pointer;
    transition: all .2s; font-family: var(--font);
    display: inline-flex; align-items: center; gap: 4px;
}
.toolbar-btn:hover { border-color: var(--accent); color: var(--text); }
.toolbar-btn.active { background: var(--accent); color: #1a1a2e; border-color: var(--accent); }
.toolbar-btn-random {
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    border: none; color: #1a1a2e; font-weight: 600;
}
.toolbar-btn-random:hover { opacity: .9; }

/* ── Result count ── */
.result-count {
    text-align: center; font-size: .85rem; opacity: .5;
    margin-bottom: 20px; transition: all .3s;
}

/* ── Tag cloud ── */
.tag-cloud {
    display: flex; flex-wrap: wrap; gap: 6px;
    padding: 12px 16px; margin-bottom: 24px;
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 12px; align-items: center;
}
.tag-cloud .tag-label { font-size: .75rem; opacity: .4; margin-right: 6px; }
.tag-cloud .tag-pill {
    font-size: .75rem; padding: 3px 12px; border-radius: 20px;
    background: var(--bg3); border: 1px solid var(--border);
    color: var(--text2); cursor: pointer; transition: all .2s;
    font-family: var(--font);
}
.tag-cloud .tag-pill:hover { border-color: var(--accent); color: var(--text); }
.tag-cloud .tag-pill.active { background: var(--accent); color: #1a1a2e; border-color: var(--accent); }
.tag-cloud .tag-pill .count { opacity: .5; margin-left: 3px; font-size: .7rem; }
.tag-cloud .tag-clear {
    font-size: .7rem; opacity: .4; cursor: pointer; margin-left: auto;
    background: none; border: none; color: var(--text2); font-family: var(--font);
}
.tag-cloud .tag-clear:hover { opacity: 1; }

/* ── Axes ── */
.biblioteca-axes {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 14px; margin-bottom: 36px;
}
.axis-card {
    background: var(--bg2); border: 1px solid var(--border);
    border-radius: 14px; padding: 20px; cursor: pointer;
    transition: all .25s; user-select: none;
}
.axis-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.12); }
.axis-card.active { border-color: var(--accent); background: var(--bg3); }
.axis-card .axis-icon { font-size: 1.6rem; margin-bottom: 6px; }
.axis-card h3 { margin: 0 0 4px; font-size: 1rem; }
.axis-card p { font-size: .8rem; opacity: .6; margin: 0 0 8px; }
.axis-card .axis-count { font-size: .75rem; opacity: .45; }

/* ── Categorias ── */
.biblioteca-categoria {
    margin-bottom: 20px; background: var(--bg2);
    border: 1px solid var(--border); border-radius: 14px; overflow: hidden;
    transition: all .3s;
}
.biblioteca-categoria.collapsed .biblioteca-grid { display: none; }
.biblioteca-categoria .cat-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px; cursor: pointer; user-select: none;
    transition: background .2s;
}
.biblioteca-categoria .cat-header:hover { background: var(--bg3); }
.biblioteca-categoria .cat-header .cat-icon {
    font-size: 1.2rem; width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 10px; background: var(--bg3); border: 1px solid var(--border);
    flex-shrink: 0;
}
.biblioteca-categoria .cat-header h2 { margin: 0; font-size: 1.1rem; flex: 1; }
.biblioteca-categoria .cat-header .cat-desc { font-size: .78rem; opacity: .5; }
.biblioteca-categoria .cat-header .cat-toggle {
    font-size: .9rem; opacity: .4; transition: transform .2s; flex-shrink: 0;
}
.biblioteca-categoria.collapsed .cat-toggle { transform: rotate(-90deg); }
.biblioteca-categoria .cat-header .cat-filter-count {
    font-size: .72rem; background: var(--accent); color: #1a1a2e;
    padding: 1px 8px; border-radius: 10px; font-weight: 600;
}

/* ── Grid ── */
.biblioteca-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px; padding: 0 18px 16px; transition: all .3s;
}
.biblioteca-grid.list-view {
    grid-template-columns: 1fr;
}
.bib-item {
    background: var(--bg3); border: 1px solid var(--border);
    border-radius: 10px; padding: 14px 18px;
    transition: all .2s; display: block; text-decoration: none; color: inherit;
    animation: fadeIn .3s ease both;
}
.bib-item.hidden { display: none; }
.bib-item:hover { border-color: var(--accent); transform: translateY(-1px); }
.bib-item h4 { margin: 0 0 3px; font-size: .95rem; }
.bib-item .bib-resumo {
    font-size: .8rem; opacity: .6; margin: 0 0 6px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.bib-item .bib-tags { display: flex; flex-wrap: wrap; gap: 3px; }
.bib-item .bib-tags .tag {
    font-size: .65rem; padding: 1px 7px; border-radius: 12px;
    background: rgba(255,255,255,.04); border: 1px solid var(--border); cursor: pointer;
}
.bib-item .bib-tags .tag:hover { border-color: var(--accent); }
.bib-item .bib-meta {
    display: flex; gap: 10px; font-size: .72rem; opacity: .45; margin-top: 6px;
}
.bib-item .bib-meta i { margin-right: 2px; }

/* ── Animations ── */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
[data-delay] { animation-delay: attr(data-delay ms, 0); }

/* ── No results ── */
.no-results { text-align: center; padding: 60px 20px; opacity: .5; }
.no-results .icon { font-size: 3rem; margin-bottom: 12px; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .biblioteca-toolbar { flex-direction: column; }
    .toolbar-group { width: 100%; justify-content: center; flex-wrap: wrap; }
    .biblioteca-axes { grid-template-columns: 1fr 1fr; }
    .stat-card { min-width: 80px; padding: 14px 18px; }
    .stat-card .number { font-size: 1.4rem; }
    .biblioteca-hero h1 { font-size: 1.8rem; }
}
@media (max-width: 480px) {
    .biblioteca-axes { grid-template-columns: 1fr; }
    .biblioteca-grid { grid-template-columns: 1fr; }
}

/* ── Modal preview ── */
.modal-overlay {
    position: fixed; inset: 0; z-index: 999;
    background: rgba(0,0,0,.7); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    animation: fadeIn .2s ease;
}
.modal-content {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 16px; max-width: 700px; width: 90%; max-height: 80vh;
    padding: 28px 32px; overflow-y: auto;
    animation: modalSlide .3s ease;
}
@keyframes modalSlide {
    from { opacity: 0; transform: scale(.95) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-content .modal-close {
    float: right; background: none; border: none;
    color: var(--text2); font-size: 1.5rem; cursor: pointer;
}
.modal-content .modal-close:hover { color: var(--text); }
.modal-content h2 { margin-bottom: 12px; }
.modal-content .modal-body { line-height: 1.7; font-size: .95rem; }
.modal-content .modal-body p { margin-bottom: 10px; }
.modal-content .modal-footer {
    margin-top: 20px; display: flex; gap: 10px;
}
.modal-content .modal-footer .btn {
    padding: 10px 20px; border-radius: 8px; border: none;
    background: linear-gradient(90deg, var(--accent), var(--accent2));
    color: #1a1a2e; font-weight: 600; cursor: pointer;
    font-family: var(--font); text-decoration: none; display: inline-block;
}
.modal-content .modal-footer .btn-secondary {
    background: var(--bg3); border: 1px solid var(--border); color: var(--text);
}
</style>

<div class="biblioteca-hero">
    <h1>📚 Biblioteca de Saberes</h1>
    <p>Todo o conhecimento do Portal Saberes Ancestrais organizado, filtrável e interativo.</p>
    <div class="biblioteca-stats">
        <div class="stat-card"><div class="number" data-count="<?= $totalArtigos ?>">0</div><div class="label">Artigos</div></div>
        <div class="stat-card"><div class="number" data-count="<?= $totalCategorias ?>">0</div><div class="label">Categorias</div></div>
        <div class="stat-card"><div class="number" data-count="<?= $totalViews ?>">0</div><div class="label">Visualizações</div></div>
        <div class="stat-card"><div class="number" data-count="<?= count($todasTags) ?>">0</div><div class="label">Tags</div></div>
    </div>
</div>

<div class="container" style="max-width:1200px;margin:0 auto;padding:0 20px 60px;">

<?php
$eixos = [
    'espiritualidade' => ['🕉️', 'Espiritualidade & Gnose', 'Gnosticismo, hermetismo, teosofia — a natureza divina do ser', ['gnose','hermetismo','teosofia','kundalini','pistis','mestres']],
    'ciencia' => ['🔬', 'Ciência & Epigenética', 'Você não é vítima dos seus genes — epigenética, coerência cardíaca', ['epigenetica','coracao','icosmica']],
    'praticas' => ['🧘', 'Práticas & Meditação', 'Respiração, mantras, meditação — técnicas de transformação interior', ['praticas','meditacao']],
    'consciencia' => ['🧠', 'Consciência & Jornada', 'As 3 visões da consciência, os 3 fatores da revolução interior', ['consciencia','regra']],
    'filosofia' => ['🌿', 'Filosofia & Síntese', 'Camino Verdad, Tao/Dao — a unificação de todos os saberes', ['filosofia','ikigai']],
    'historico' => ['📜', 'História & Tradições', 'Jesus histórico, Pindorama — as raízes do conhecimento humano', ['jesus','pindorama']],
    'coracao' => ['💓', 'Sabedoria do Coração', 'HeartMath, coerência cardíaca e conexão com o campo da Terra', ['coracao']],
];
?>

<!-- Toolbar -->
<div class="biblioteca-toolbar">
    <div class="search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="bibSearch" placeholder="Buscar artigos... (pressione / para focar)" autocomplete="off">
        <button class="clear-btn" id="clearSearch" aria-label="Limpar busca">&times;</button>
    </div>
    <div class="toolbar-group">
        <label>Ordenar</label>
        <select id="sortSelect" class="toolbar-btn">
            <option value="recent">Recentes</option>
            <option value="title">A-Z</option>
            <option value="views">+Vistos</option>
        </select>
    </div>
    <div class="toolbar-group">
        <button class="toolbar-btn active" data-view="grid" id="viewGrid" title="Visualização em grade"><i class="bi bi-grid-3x3-gap"></i></button>
        <button class="toolbar-btn" data-view="list" id="viewList" title="Visualização em lista"><i class="bi bi-list-ul"></i></button>
    </div>
    <button class="toolbar-btn toolbar-btn-random" id="randomBtn">🎲 Aleatório</button>
</div>

<!-- Tag cloud -->
<div class="tag-cloud" id="tagCloud">
    <span class="tag-label">Tags:</span>
    <button class="tag-pill active" data-tag="" id="tagAll">Todas <span class="count">(<?= count($todosArtigos) ?>)</span></button>
    <?php foreach ($todasTags as $tag => $count): ?>
    <button class="tag-pill" data-tag="<?= esc($tag) ?>">#<?= esc($tag) ?> <span class="count">(<?= $count ?>)</span></button>
    <?php endforeach; ?>
    <button class="tag-clear" id="tagClear" style="display:none">✕ limpar</button>
</div>

<!-- Result count -->
<div class="result-count" id="resultCount">Mostrando <?= count($todosArtigos) ?> artigo<?= count($todosArtigos) !== 1 ? 's' : '' ?></div>

<!-- Eixos -->
<h2 style="margin-bottom:16px;font-size:1.3rem;">🔱 Eixos Temáticos</h2>
<div class="biblioteca-axes" id="axisContainer">
    <?php foreach ($eixos as $key => [$icon, $name, $desc, $cats]):
        $count = 0;
        foreach ($cats as $cs) $count += isset($artigosPorCategoria[$cs]) ? count($artigosPorCategoria[$cs]['artigos']) : 0;
    ?>
    <div class="axis-card" data-axis="<?= $key ?>" data-cats="<?= esc(implode(',', $cats)) ?>">
        <div class="axis-icon"><?= $icon ?></div>
        <h3><?= esc($name) ?></h3>
        <p><?= esc($desc) ?></p>
        <div class="axis-count"><?= $count ?> artigos</div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Catálogo -->
<h2 style="margin-bottom:16px;font-size:1.3rem;">📖 Catálogo Completo</h2>

<div id="catalogContainer">
<?php if (empty($todosArtigos)): ?>
    <div class="no-results"><div class="icon">📚</div><p>Nenhum artigo publicado ainda.</p></div>
<?php else:
    $delay = 0;
    foreach ($artigosPorCategoria as $catSlug => $catData):
        $catColor = esc($catData['cor']);
        $arts = $catData['artigos'];
    ?>
    <div class="biblioteca-categoria" data-category="<?= esc($catSlug) ?>">
        <div class="cat-header" onclick="this.closest('.biblioteca-categoria').classList.toggle('collapsed')">
            <div class="cat-icon" style="border-color:<?= $catColor ?>33">
                <i class="<?= esc($catData['icone']) ?>" style="color:<?= $catColor ?>"></i>
            </div>
            <h2><?= esc($catData['nome']) ?></h2>
            <div class="cat-desc"><?= count($arts) ?> artigo<?= count($arts) !== 1 ? 's' : '' ?></div>
            <span class="cat-toggle"><i class="bi bi-chevron-down"></i></span>
        </div>
        <div class="biblioteca-grid" id="grid-<?= esc($catSlug) ?>">
            <?php foreach ($arts as $a):
                $d = $delay * 30;
                $tagsArr = $a['tags'] ? explode(',', $a['tags']) : [];
            ?>
            <div class="bib-item" data-slug="<?= esc($a['slug']) ?>" data-title="<?= esc(strtolower($a['titulo'])) ?>"
                 data-views="<?= (int)$a['views'] ?>" data-date="<?= esc($a['publicado_em']) ?>"
                 data-category="<?= esc($catSlug) ?>" data-tags="<?= esc($a['tags'] ?? '') ?>"
                 data-resumo="<?= esc(strip_tags($a['resumo'] ?? '')) ?>"
                 onclick="openPreview(event, '<?= esc($a['slug']) ?>')"
                 style="animation-delay:<?= $d ?>ms">
                <h4><?= esc($a['titulo']) ?></h4>
                <p class="bib-resumo"><?= resumir($a['resumo'] ?: strip_tags($a['resumo'] ?? ''), 200) ?></p>
                <?php if ($tagsArr): ?>
                <div class="bib-tags">
                    <?php foreach (array_slice($tagsArr, 0, 5) as $tag): ?>
                    <span class="tag" data-tag="<?= esc(trim($tag)) ?>" onclick="event.stopPropagation();filterByTag('<?= esc(trim($tag)) ?>')">#<?= esc(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="bib-meta">
                    <span><i class="bi bi-eye"></i> <?= (int)$a['views'] ?></span>
                    <span><i class="bi bi-clock"></i> <?= tempo_relativo($a['publicado_em']) ?></span>
                </div>
            </div>
            <?php $delay++; endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

</div>

<!-- Modal -->
<div id="previewModal" class="modal-overlay" style="display:none" onclick="if(event.target===this)closePreview()">
    <div class="modal-content">
        <button class="modal-close" onclick="closePreview()">&times;</button>
        <div id="previewBody">
            <h2 id="previewTitle">Carregando...</h2>
            <div class="modal-body" id="previewContent"><p>Carregando conteúdo...</p></div>
            <div class="modal-footer">
                <a href="#" id="previewLink" class="btn">📖 Ler artigo completo</a>
                <button class="btn btn-secondary" onclick="closePreview()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================
// DADOS
// ============================================================
const ALL_ARTICLES = <?= json_encode(array_map(function($a) {
    return [
        'slug' => $a['slug'], 'titulo' => $a['titulo'],
        'cat_slug' => $a['cat_slug'], 'cat_nome' => $a['cat_nome'],
        'views' => (int)$a['views'], 'tags' => $a['tags'] ?? '',
        'resumo' => strip_tags($a['resumo'] ?? ''),
        'date' => $a['publicado_em'],
    ];
}, $todosArtigos)) ?>;

// ============================================================
// STATS COUNTER ANIMATION
// ============================================================
document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    const duration = 800;
    const start = performance.now();
    function update(now) {
        const pct = Math.min((now - start) / duration, 1);
        el.textContent = Math.floor(pct * target).toLocaleString('pt-BR');
        if (pct < 1) requestAnimationFrame(update);
        else el.textContent = target.toLocaleString('pt-BR');
    }
    requestAnimationFrame(update);
});

// ============================================================
// SEARCH (com debounce)
// ============================================================
const searchInput = document.getElementById('bibSearch');
const clearBtn = document.getElementById('clearSearch');
let searchTimeout;

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 150);
    clearBtn.style.display = searchInput.value ? 'block' : 'none';
});
clearBtn.addEventListener('click', () => {
    searchInput.value = '';
    clearBtn.style.display = 'none';
    applyFilters();
});

// Keyboard shortcut
document.addEventListener('keydown', e => {
    if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
        e.preventDefault();
        searchInput.focus();
    }
    if (e.key === 'Escape') { closePreview(); }
});

// ============================================================
// TAG FILTER
// ============================================================
let activeTag = '';

document.querySelectorAll('.tag-pill').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tag-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeTag = btn.dataset.tag;
        document.getElementById('tagClear').style.display = activeTag ? 'inline' : 'none';
        applyFilters();
    });
});
document.getElementById('tagClear').addEventListener('click', () => {
    document.querySelector('.tag-pill[data-tag=""]').click();
});

function filterByTag(tag) {
    const btn = document.querySelector(`.tag-pill[data-tag="${CSS.escape(tag)}"]`);
    if (btn) btn.click();
}

// ============================================================
// AXIS FILTER
// ============================================================
document.querySelectorAll('.axis-card').forEach(card => {
    card.addEventListener('click', () => {
        card.classList.toggle('active');
        applyFilters();
    });
});

// ============================================================
// SORT
// ============================================================
document.getElementById('sortSelect').addEventListener('change', applySort);

function applySort() {
    const sort = document.getElementById('sortSelect').value;
    document.querySelectorAll('.biblioteca-grid').forEach(grid => {
        const items = Array.from(grid.querySelectorAll('.bib-item:not(.hidden)'));
        items.sort((a, b) => {
            if (sort === 'title') return a.dataset.title.localeCompare(b.dataset.title);
            if (sort === 'views') return parseInt(b.dataset.views) - parseInt(a.dataset.views);
            return new Date(b.dataset.date) - new Date(a.dataset.date);
        });
        items.forEach(item => grid.appendChild(item));
    });
}

// ============================================================
// VIEW TOGGLE (Grid / List)
// ============================================================
document.getElementById('viewGrid').addEventListener('click', () => setView('grid'));
document.getElementById('viewList').addEventListener('click', () => setView('list'));

function setView(view) {
    document.querySelectorAll('.toolbar-btn[data-view]').forEach(b => b.classList.remove('active'));
    document.querySelector(`.toolbar-btn[data-view="${view}"]`).classList.add('active');
    document.querySelectorAll('.biblioteca-grid').forEach(g => g.classList.toggle('list-view', view === 'list'));
}

// ============================================================
// RANDOM ARTICLE
// ============================================================
document.getElementById('randomBtn').addEventListener('click', () => {
    const visible = ALL_ARTICLES.filter(a => {
        const el = document.querySelector(`.bib-item[data-slug="${a.slug}"]`);
        return el && !el.classList.contains('hidden');
    });
    if (visible.length === 0) return;
    const pick = visible[Math.floor(Math.random() * visible.length)];
    window.location.href = '<?= APP_URL ?>/artigo/' + pick.slug;
});

// ============================================================
// MAIN FILTER
// ============================================================
function applyFilters() {
    const q = searchInput.value.toLowerCase().trim();
    const activeAxes = new Set();
    document.querySelectorAll('.axis-card.active').forEach(c => activeAxes.add(c.dataset.cats));

    let visibleCount = 0;

    document.querySelectorAll('.bib-item').forEach(item => {
        const title = item.dataset.title || '';
        const tags = (item.dataset.tags || '').toLowerCase();
        const resumo = (item.dataset.resumo || '').toLowerCase();
        const cat = item.dataset.category || '';
        const itemTags = tags.split(',').map(t => t.trim()).filter(Boolean);

        // Search filter
        const matchSearch = !q || title.includes(q) || tags.includes(q) || resumo.includes(q);

        // Tag filter
        const matchTag = !activeTag || itemTags.includes(activeTag);

        // Axis filter
        let matchAxis = true;
        if (activeAxes.size > 0) {
            matchAxis = false;
            activeAxes.forEach(cats => {
                if (cats.split(',').includes(cat)) matchAxis = true;
            });
        }

        const visible = matchSearch && matchTag && matchAxis;
        item.classList.toggle('hidden', !visible);
        if (visible) visibleCount++;
    });

    // Category visibility
    document.querySelectorAll('.biblioteca-categoria').forEach(cat => {
        const visibleItems = cat.querySelectorAll('.bib-item:not(.hidden)').length;
        const countBadge = cat.querySelector('.cat-filter-count');
        if (countBadge) countBadge.remove();
        cat.style.display = visibleItems > 0 ? '' : 'none';
        if (visibleItems > 0 && visibleItems < cat.querySelectorAll('.bib-item').length) {
            const badge = document.createElement('span');
            badge.className = 'cat-filter-count';
            badge.textContent = visibleItems;
            cat.querySelector('.cat-header h2').after(badge);
        }
    });

    document.getElementById('resultCount').textContent =
        `Mostrando ${visibleCount} artigo${visibleCount !== 1 ? 's' : ''}` +
        (visibleCount !== ALL_ARTICLES.length ? ` de ${ALL_ARTICLES.length}` : '');

    applySort();
}

// ============================================================
// PREVIEW MODAL
// ============================================================
let previewCache = {};

function openPreview(event, slug) {
    // Don't open if clicking a tag
    if (event.target.closest('.tag')) return;
    const modal = document.getElementById('previewModal');
    const title = document.getElementById('previewTitle');
    const content = document.getElementById('previewContent');
    const link = document.getElementById('previewLink');

    document.getElementById('previewBody').style.display = 'block';
    title.textContent = 'Carregando...';
    content.innerHTML = '<p style="opacity:.5">Carregando conteúdo...</p>';
    link.href = '<?= APP_URL ?>/artigo/' + slug;
    modal.style.display = 'flex';

    if (previewCache[slug]) {
        title.textContent = previewCache[slug].titulo;
        content.innerHTML = previewCache[slug].html;
        return;
    }

    fetch('<?= APP_URL ?>/api/artigo-preview.php?slug=' + encodeURIComponent(slug))
        .then(r => r.json())
        .then(data => {
            if (data.error) { content.innerHTML = '<p style="opacity:.5">' + data.error + '</p>'; return; }
            title.textContent = data.titulo;
            content.innerHTML = data.html;
            previewCache[slug] = { titulo: data.titulo, html: data.html };
        })
        .catch(() => {
            content.innerHTML = '<p style="opacity:.5">Erro ao carregar.</p>';
        });
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

// ============================================================
// INIT: expand categories with active filters
// ============================================================
applySort();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
