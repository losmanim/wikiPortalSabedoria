<?php
/**
 * Página de Categoria
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

$categoria = $db->fetch('SELECT * FROM categorias WHERE slug = ?', [$slug]);
if (!$categoria) {
    header('Location: index.php');
    exit;
}

$pagina = (int)($_GET['pagina'] ?? 1);
$offset = ($pagina - 1) * ARTIGOS_POR_PAGINA;

$artigos = $db->select(
    'SELECT a.*, u.nome as autor_nome
     FROM artigos a
     LEFT JOIN usuarios u ON u.id = a.autor_id
     WHERE a.categoria_id = ? AND a.status = "publicado"
     ORDER BY a.publicado_em DESC
     LIMIT ? OFFSET ?',
    [$categoria['id'], ARTIGOS_POR_PAGINA, $offset]
);

$total = $db->contar('artigos', 'categoria_id = ? AND status = "publicado"', [$categoria['id']]);
$total_paginas = ceil($total / ARTIGOS_POR_PAGINA);

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = $categoria['nome'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="categoria-header">
    <h1><i class="<?= esc($categoria['icone']) ?>"></i> <?= esc($categoria['nome']) ?></h1>
    <?php if ($categoria['descricao']): ?>
    <p class="cat-desc"><?= esc($categoria['descricao']) ?></p>
    <?php endif; ?>
    <p style="margin-top:10px;opacity:0.5;font-size:0.9rem;"><?= $total ?> artigo<?= $total !== 1 ? 's' : '' ?></p>
</div>

<?php if (empty($artigos)): ?>
    <p style="text-align:center;padding:40px;opacity:0.5">Nenhum artigo nesta categoria ainda.</p>
<?php else: ?>
<div class="artigos-grid">
    <?php foreach ($artigos as $a): ?>
    <article class="artigo-card">
        <h3><a href="artigo.php?slug=<?= esc($a['slug']) ?>"><?= esc($a['titulo']) ?></a></h3>
        <p class="card-resumo"><?= resumir($a['resumo'] ?: $a['conteudo'], 200) ?></p>
        <?php if ($a['tags']): ?>
        <div class="card-tags">
            <?php foreach (array_slice(explode(',', $a['tags']), 0, 4) as $tag): ?>
            <span class="tag">#<?= esc(trim($tag)) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="card-meta">
            <span><i class="bi bi-eye"></i> <?= $a['views'] ?></span>
            <span><?= tempo_relativo($a['publicado_em']) ?></span>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php if ($total_paginas > 1): ?>
<div class="paginacao">
    <?php if ($pagina > 1): ?>
    <a href="categoria.php?slug=<?= esc($slug) ?>&pagina=<?= $pagina - 1 ?>">‹ Anterior</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
    <a href="categoria.php?slug=<?= esc($slug) ?>&pagina=<?= $i ?>" class="<?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($pagina < $total_paginas): ?>
    <a href="categoria.php?slug=<?= esc($slug) ?>&pagina=<?= $pagina + 1 ?>">Próxima ›</a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
