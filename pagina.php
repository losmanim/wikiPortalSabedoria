<?php
/**
 * Página Estática
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

$pagina = $db->fetch('SELECT * FROM paginas WHERE slug = ? AND status = "publicado"', [$slug]);
if (!$pagina) {
    header('Location: index.php');
    exit;
}

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = $pagina['titulo'];
require_once __DIR__ . '/includes/header.php';
?>

<article style="max-width:800px;margin:40px auto;padding:0 20px;">
    <h1 style="margin-bottom:20px"><?= esc($pagina['titulo']) ?></h1>
    <div class="artigo-conteudo">
        <?= $pagina['conteudo'] ?>
    </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
