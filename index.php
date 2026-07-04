<?php
/**
 * Página Inicial do Portal
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();

// Dados para o header
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');
$artigos_recentes = $db->select(
    'SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone,
            u.nome as autor_nome
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     LEFT JOIN usuarios u ON u.id = a.autor_id
     WHERE a.status = "publicado"
     ORDER BY a.publicado_em DESC
     LIMIT 9'
);

$titulo = 'Início';
$descricao = APP_DESC;
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-orbs" aria-hidden="true">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>
    <h1 class="reveal">Portal Saberes Ancestrais</h1>
    <p class="reveal reveal-delay-1">Uma jornada através dos saberes que unem ciência, espiritualidade e filosofia. Explore, aprenda e compartilhe conhecimento.</p>
    <form class="hero-search reveal reveal-delay-2" action="busca.php" method="GET">
        <input type="text" name="q" placeholder="O que você busca?" required>
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
</section>

<section class="reveal reveal-delay-3">
    <h2 class="section-title"><i class="bi bi-grid"></i> Categorias</h2>
    <div class="categorias-grid">
        <?php $catIndex = 0; foreach ($categorias as $cat):
            $total = $db->contar('artigos', 'categoria_id = ? AND status = "publicado"', [$cat['id']]);
        ?>
        <a href="categoria.php?slug=<?= esc($cat['slug']) ?>" class="cat-card reveal reveal-delay-<?= min($catIndex + 1, 5) ?>" style="--cat-color: <?= esc($cat['cor']) ?>">
            <span class="cat-icon"><i class="<?= esc($cat['icone']) ?>"></i></span>
            <span class="cat-name"><?= esc($cat['nome']) ?></span>
            <span class="cat-count"><?= $total ?> artigos</span>
        </a>
        <?php $catIndex++; endforeach; ?>
    </div>
</section>

<section class="reveal reveal-delay-4">
    <h2 class="section-title"><i class="bi bi-clock-history"></i> Artigos Recentes</h2>
    <?php if (empty($artigos_recentes)): ?>
        <p style="text-align:center;opacity:0.5;padding:40px;">Nenhum artigo publicado ainda.</p>
    <?php else: ?>
    <div class="artigos-grid">
        <?php $artIndex = 0; foreach ($artigos_recentes as $artigo): ?>
        <article class="artigo-card reveal reveal-delay-<?= min($artIndex % 5 + 1, 5) ?>">
            <div class="card-cat">
                <i class="<?= esc($artigo['cat_icone'] ?? 'bi bi-folder') ?>"></i>
                <?= esc($artigo['cat_nome'] ?? 'Sem categoria') ?>
            </div>
            <h3><a href="artigo.php?slug=<?= esc($artigo['slug']) ?>"><?= esc($artigo['titulo']) ?></a></h3>
            <p class="card-resumo"><?= resumir($artigo['resumo'] ?: $artigo['conteudo'], 200) ?></p>
            <?php if ($artigo['tags']): ?>
            <div class="card-tags">
                <?php foreach (array_slice(explode(',', $artigo['tags']), 0, 4) as $tag): ?>
                <span class="tag">#<?= esc(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <div class="card-meta">
                <span><i class="bi bi-eye"></i> <?= $artigo['views'] ?> views</span>
                <span><i class="bi bi-clock"></i> <?= tempo_relativo($artigo['publicado_em']) ?></span>
            </div>
        </article>
        <?php $artIndex++; endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
