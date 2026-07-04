<?php
/**
 * Admin Dashboard - Portal Saberes Ancestrais
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

required_editor();
$db = Database::getInstance();
$nivel = usuario_nivel();

// Stats
$stats = [
    'artigos' => $db->contar('artigos'),
    'publicados' => $db->contar('artigos', 'status = "publicado"'),
    'rascunhos' => $db->contar('artigos', 'status = "rascunho"'),
    'categorias' => $db->contar('categorias'),
    'comentarios' => $db->contar('comentarios'),
    'pendentes' => $db->contar('comentarios', 'status = "pendente"'),
    'usuarios' => $db->contar('usuarios'),
    'paginas' => $db->contar('paginas'),
    'views' => $db->fetch('SELECT SUM(views) as total FROM artigos')['total'] ?? 0,
];

$recentes = $db->select(
    'SELECT a.id, a.titulo, a.slug, a.status, a.publicado_em, c.nome as cat_nome
     FROM artigos a LEFT JOIN categorias c ON c.id = a.categoria_id
     ORDER BY a.atualizado_em DESC LIMIT 8'
);

$ultimos_comentarios = $db->select(
    'SELECT c.*, a.titulo as artigo_titulo, a.slug as artigo_slug
     FROM comentarios c JOIN artigos a ON a.id = c.artigo_id
     ORDER BY c.criado_em DESC LIMIT 10'
);

$logs = $db->select(
    'SELECT l.*, u.nome as usuario_nome
     FROM logs l LEFT JOIN usuarios u ON u.id = l.usuario_id
     ORDER BY l.criado_em DESC LIMIT 10'
);

$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="sidebar-logo">🕉️</span>
                <span class="sidebar-title">Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="artigos/index.php"><i class="bi bi-file-text"></i> Artigos</a>
                <a href="categorias/index.php"><i class="bi bi-folder"></i> Categorias</a>
                <a href="comentarios/index.php"><i class="bi bi-chat-dots"></i> Comentários
                    <?php if ($stats['pendentes'] > 0): ?>
                        <span class="badge"><?= $stats['pendentes'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="usuarios/index.php"><i class="bi bi-people"></i> Usuários</a>
                <a href="paginas/index.php"><i class="bi bi-file-earmark"></i> Páginas</a>
                <hr>
                <a href="../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
                <a href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </nav>
            <div class="sidebar-footer">
                <?= esc($_SESSION['usuario_nome']) ?> (<?= $nivel ?>)
            </div>
        </aside>

        <!-- Main -->
        <main class="admin-main">
            <div class="admin-topbar">
                <h1>Dashboard</h1>
                <div class="topbar-actions">
                    <a href="artigos/editar.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Novo Artigo
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(243,156,18,0.15);color:#f39c12">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= $stats['artigos'] ?></span>
                        <span class="stat-label">Total Artigos</span>
                    </div>
                    <div class="stat-sub">
                        <?= $stats['publicados'] ?> publi. / <?= $stats['rascunhos'] ?> rasc.
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(46,204,113,0.15);color:#2ecc71">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= number_format($stats['views'], 0, ',', '.') ?></span>
                        <span class="stat-label">Visualizações</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(52,152,219,0.15);color:#3498db">
                        <i class="bi bi-folder"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= $stats['categorias'] ?></span>
                        <span class="stat-label">Categorias</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(155,89,182,0.15);color:#9b59b6">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= $stats['comentarios'] ?></span>
                        <span class="stat-label">Comentários</span>
                    </div>
                    <div class="stat-sub">
                        <?= $stats['pendentes'] ?> pendentes
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(46,204,113,0.15);color:#2ecc71">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= $stats['usuarios'] ?></span>
                        <span class="stat-label">Usuários</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(230,126,34,0.15);color:#e67e22">
                        <i class="bi bi-file-earmark"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?= $stats['paginas'] ?></span>
                        <span class="stat-label">Páginas</span>
                    </div>
                </div>
            </div>

            <div class="admin-grid-2">
                <!-- Artigos Recentes -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="bi bi-clock"></i> Artigos Recentes</h3>
                        <a href="artigos/index.php" class="card-link">Ver todos</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentes)): ?>
                            <p style="opacity:0.5;text-align:center;padding:20px;">Nenhum artigo encontrado.</p>
                        <?php else: ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($recentes as $a): ?>
                                    <tr>
                                        <td>
                                            <a href="artigos/editar.php?id=<?= $a['id'] ?>"><?= esc($a['titulo']) ?></a>
                                        </td>
                                        <td><?= esc($a['cat_nome'] ?? '-') ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $a['status'] ?>">
                                                <?= $a['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comentários Recentes -->
                <div class="admin-card">
                    <div class="card-header">
                        <h3><i class="bi bi-chat"></i> Últimos Comentários</h3>
                        <a href="comentarios/index.php" class="card-link">Gerenciar</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($ultimos_comentarios)): ?>
                            <p style="opacity:0.5;text-align:center;padding:20px;">Nenhum comentário.</p>
                        <?php else: ?>
                            <?php foreach ($ultimos_comentarios as $c): ?>
                            <div class="comentario-item">
                                <div class="comentario-top">
                                    <strong><?= esc($c['usuario_nome'] ?? $c['autor_nome'] ?? 'Anônimo') ?></strong>
                                    <span class="status-badge status-<?= $c['status'] ?>"><?= $c['status'] ?></span>
                                </div>
                                <p><?= resumir($c['conteudo'], 100) ?></p>
                                <div class="comentario-footer">
                                    <a href="../artigo.php?slug=<?= esc($c['artigo_slug']) ?>#comentarios">
                                        <?= esc($c['artigo_titulo']) ?>
                                    </a>
                                    <span><?= tempo_relativo($c['criado_em']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Logs de Atividade -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="bi bi-activity"></i> Atividades Recentes</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <p style="opacity:0.5;text-align:center;padding:20px;">Nenhuma atividade registrada.</p>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr><th>Usuário</th><th>Ação</th><th>Descrição</th><th>Data</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td><?= esc($l['usuario_nome'] ?? 'Sistema') ?></td>
                                    <td><?= esc($l['acao']) ?></td>
                                    <td><?= esc($l['descricao'] ?? '-') ?></td>
                                    <td style="font-size:0.85rem;opacity:0.6"><?= tempo_relativo($l['criado_em']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
