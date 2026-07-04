<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

http_response_code(404);
$titulo = 'Página não encontrada';
$descricao = 'O conteúdo que você procura não existe.';
require_once __DIR__ . '/includes/header.php';
?>
<div class="error-page">
    <div class="error-code">404</div>
    <h1>Página não encontrada</h1>
    <p>O conteúdo que você procura foi removido, renomeado ou está temporariamente indisponível.</p>
    <div class="error-actions">
        <a href="<?= APP_URL ?>/index.php" class="btn-header btn-header-primary">
            <i class="bi bi-house"></i> Ir para o Início
        </a>
        <a href="<?= APP_URL ?>/busca.php" class="btn-header">
            <i class="bi bi-search"></i> Buscar no Portal
        </a>
    </div>
    <div class="error-suggestions">
        <h3>Talvez você esteja procurando:</h3>
        <div class="categorias-grid" style="margin-top:15px">
            <?php foreach ($categorias as $cat): ?>
            <a href="categoria.php?slug=<?= esc($cat['slug']) ?>" class="cat-card" style="--cat-color: <?= esc($cat['cor']) ?>">
                <span class="cat-icon"><i class="<?= esc($cat['icone']) ?>"></i></span>
                <span class="cat-name"><?= esc($cat['nome']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<style>
.error-page { text-align: center; padding: 60px 20px; max-width: 700px; margin: 0 auto; }
.error-code { font-size: 8rem; font-weight: 900; background: linear-gradient(135deg, #f39c12, #e74c3c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; margin-bottom: 10px; opacity: 0.5; }
.error-page h1 { font-size: 1.8rem; margin-bottom: 15px; }
.error-page p { opacity: 0.6; margin-bottom: 30px; font-size: 1.1rem; line-height: 1.6; }
.error-actions { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 50px; }
.error-suggestions h3 { font-size: 1.1rem; opacity: 0.5; margin-bottom: 20px; }
.error-suggestions .categorias-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
.error-suggestions .cat-card { padding: 15px 20px; min-width: 140px; flex: 0 1 auto; }
</style>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
