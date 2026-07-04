<?php
/**
 * Página de Artigo
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header('Location: index.php');
    exit;
}

$artigo = $db->fetch(
    'SELECT a.*, c.nome as cat_nome, c.slug as cat_slug, c.icone as cat_icone,
            u.nome as autor_nome
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     LEFT JOIN usuarios u ON u.id = a.autor_id
     WHERE a.slug = ? AND a.status = "publicado"',
    [$slug]
);

if (!$artigo) {
    header('HTTP/1.0 404 Not Found');
    $titulo = 'Artigo não encontrado';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="text-align:center;padding:80px 0"><h2>Artigo não encontrado</h2><p style="opacity:0.6">O artigo que você procura não existe ou foi removido.</p><a href="index.php" class="btn-header btn-header-primary" style="display:inline-block;margin-top:20px">Voltar ao Início</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Incrementar view (uma por sessão)
if (!isset($_SESSION['views_' . $artigo['id']])) {
    $db->update('artigos', ['views' => $artigo['views'] + 1], 'id = ?', [$artigo['id']]);
    $_SESSION['views_' . $artigo['id']] = true;
}

// Comentários
$comentarios = $db->select(
    'SELECT c.*, u.nome as usuario_nome
     FROM comentarios c
     LEFT JOIN usuarios u ON u.id = c.usuario_id
     WHERE c.artigo_id = ? AND c.status = "aprovado" AND c.parent_id IS NULL
     ORDER BY c.criado_em DESC
     LIMIT 50',
    [$artigo['id']]
);

// Respostas
foreach ($comentarios as &$c) {
    $c['respostas'] = $db->select(
        'SELECT c.*, u.nome as usuario_nome
         FROM comentarios c
         LEFT JOIN usuarios u ON u.id = c.usuario_id
         WHERE c.parent_id = ? AND c.status = "aprovado"
         ORDER BY c.criado_em ASC',
        [$c['id']]
    );
}
unset($c);

// Comentário pendente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentar'])) {
    // Rate limiting check
    if (!Security::checkRateLimit('comentario', 3, 60)) {
        $erro_comentario = 'Muitas tentativas. Aguarde 1 minuto.';
    }
    
    // CAPTCHA validation
    if (!Captcha::validateMath($_POST['captcha_math'] ?? '')) {
        $erro_comentario = 'Resposta incorreta. Tente novamente.';
    }
    
    $conteudo = trim($_POST['conteudo'] ?? '');
    if (!$erro_comentario && $conteudo) {
        // ─── SEM FILTROS: Auto-aprovar comentários de usuários confiáveis ───
        $status_comentario = 'pendente';
        if (esta_logado()) {
            $rep = $db->fetch('SELECT nivel FROM usuario_reputacao WHERE usuario_id = ?', [$_SESSION['usuario_id']]);
            if ($rep && in_array($rep['nivel'], ['contribuidor','especialista','mestre','lendario'])) {
                $status_comentario = 'aprovado';
            }
            $db->insert('comentarios', [
                'artigo_id' => $artigo['id'],
                'usuario_id' => $_SESSION['usuario_id'],
                'conteudo' => $conteudo,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'status' => $status_comentario
            ]);
        } else {
            $nome = trim($_POST['autor_nome'] ?? 'Anônimo');
            $email = trim($_POST['autor_email'] ?? '');
            $db->insert('comentarios', [
                'artigo_id' => $artigo['id'],
                'autor_nome' => $nome,
                'autor_email' => $email,
                'conteudo' => $conteudo,
                'status' => 'pendente'
            ]);
        }
        // Gamificação
        require_once __DIR__ . '/includes/Gamification.php';
        $gam = new Gamification($db);
        $gam->registerAction($_SESSION['usuario_id'] ?? 0, 'comentario_feito');
        $msg = $status_comentario === 'aprovado' ? 'Comentário publicado!' : 'Comentário enviado! Aguarde aprovação.';
        echo '<script>alert("' . $msg . '"); window.location.href="' . APP_URL . '/artigo.php?slug=' . esc($slug) . '";</script>';
        exit;
    }
}

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
$paginas = $db->select('SELECT slug, titulo FROM paginas WHERE status = "publicado" AND no_menu = 1 ORDER BY ordem');

$titulo = $artigo['titulo'];
require_once __DIR__ . '/includes/header.php';
?>

<article>
    <header class="artigo-header">
        <div class="artigo-cat">
            <i class="<?= esc($artigo['cat_icone'] ?? 'bi bi-folder') ?>"></i>
            <a href="categoria.php?slug=<?= esc($artigo['cat_slug']) ?>"><?= esc($artigo['cat_nome'] ?? 'Sem categoria') ?></a>
        </div>
        <h1><?= esc($artigo['titulo']) ?></h1>
        <div class="artigo-meta">
            <span><i class="bi bi-person"></i> <?= esc($artigo['autor_nome'] ?? 'Admin') ?></span>
            <span><i class="bi bi-calendar"></i> <?= data_br($artigo['publicado_em']) ?></span>
            <span><i class="bi bi-eye"></i> <?= number_format($artigo['views'], 0, ',', '.') ?> views</span>
            <?php if ($artigo['fonte']): ?>
            <span><i class="bi bi-bookmark"></i> Fonte: <?= esc($artigo['fonte']) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <div class="artigo-conteudo">
        <?= $artigo['conteudo'] ?>
    </div>

    <?php if ($artigo['tags']): ?>
    <div class="artigo-tags">
        <?php foreach (explode(',', $artigo['tags']) as $tag): ?>
        <a href="busca.php?q=<?= urlencode(trim($tag)) ?>" class="tag">#<?= esc(trim($tag)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($artigo['fonte']): ?>
    <div class="artigo-fonte">
        <i class="bi bi-box"></i> Conteúdo original de: <strong><?= esc($artigo['fonte']) ?></strong>
    </div>
    <?php endif; ?>
</article>

<!-- COMENTÁRIOS -->
<section class="comentarios-section">
    <h3><i class="bi bi-chat-dots"></i> Comentários (<?= count($comentarios) ?>)</h3>

    <?php if (empty($comentarios)): ?>
        <p style="opacity:0.5;margin-bottom:20px;">Nenhum comentário ainda. Seja o primeiro!</p>
    <?php else: ?>
        <?php foreach ($comentarios as $com): ?>
        <div class="comentario">
            <div class="comentario-meta">
                <strong><?= esc($com['usuario_nome'] ?? $com['autor_nome'] ?? 'Anônimo') ?></strong>
                <span><?= tempo_relativo($com['criado_em']) ?></span>
            </div>
            <div class="comentario-conteudo"><?= nl2br(esc($com['conteudo'])) ?></div>
            <?php if (!empty($com['respostas'])): ?>
                <?php foreach ($com['respostas'] as $resp): ?>
                <div class="comentario-resposta">
                    <div class="comentario-meta">
                        <strong><?= esc($resp['usuario_nome'] ?? $resp['autor_nome'] ?? 'Anônimo') ?></strong>
                        <span><?= tempo_relativo($resp['criado_em']) ?></span>
                    </div>
                    <div><?= nl2br(esc($resp['conteudo'])) ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulário de Comentário -->
    <div class="comentario-form">
        <h4>Deixe seu comentário</h4>
        <?php if (isset($erro_comentario)): ?>
            <div class="erro" style="background:rgba(231,76,60,0.15);color:#e74c3c;padding:10px;border-radius:8px;margin-bottom:15px;"><?= esc($erro_comentario) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="parent_id" id="parent_id" value="">
            <textarea name="conteudo" placeholder="Compartilhe seus pensamentos..." required></textarea>
            <?php if (!esta_logado()): ?>
            <div class="form-row">
                <input type="text" name="autor_nome" placeholder="Seu nome" required>
                <input type="email" name="autor_email" placeholder="Seu email (opcional)">
            </div>
            <?php endif; ?>
            <div class="captcha-wrapper" style="margin:15px 0;">
                <?= Captcha::mathField() ?>
            </div>
            <button type="submit" name="comentar" class="btn-submit">Enviar Comentário</button>
        </form>
        <p style="font-size:0.8rem;opacity:0.4;margin-top:10px;">Comentários passam por moderação.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
