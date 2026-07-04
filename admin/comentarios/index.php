<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
required_editor();
$db = Database::getInstance();

if (isset($_GET['aprovar'])) { $db->update('comentarios', ['status' => 'aprovado'], 'id = ?', [(int)$_GET['aprovar']]); }
if (isset($_GET['rejeitar'])) { $db->update('comentarios', ['status' => 'rejeitado'], 'id = ?', [(int)$_GET['rejeitar']]); }
if (isset($_GET['deletar'])) { $db->delete('DELETE FROM comentarios WHERE id = ?', [(int)$_GET['deletar']]); }

$filtro = $_GET['filtro'] ?? 'todos';
$where = $filtro === 'pendentes' ? 'c.status = "pendente"' : '1=1';
$comentarios = $db->select(
    "SELECT c.*, a.titulo as artigo_titulo, a.slug as artigo_slug, u.nome as usuario_nome
     FROM comentarios c
     JOIN artigos a ON a.id = c.artigo_id
     LEFT JOIN usuarios u ON u.id = c.usuario_id
     WHERE {$where}
     ORDER BY c.criado_em DESC LIMIT 50"
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentários - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar">
                <h1>Comentários</h1>
                <div>
                    <a href="?filtro=todos" class="btn btn-sm <?= $filtro === 'todos' ? 'btn-primary' : 'btn-secondary' ?>">Todos</a>
                    <a href="?filtro=pendentes" class="btn btn-sm <?= $filtro === 'pendentes' ? 'btn-primary' : 'btn-secondary' ?>">Pendentes</a>
                </div>
            </div>
            <div class="admin-card">
                <div class="card-body" style="padding:0">
                    <table class="table">
                        <thead><tr><th>Autor</th><th>Comentário</th><th>Artigo</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
                        <tbody>
                            <?php if (empty($comentarios)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:30px;opacity:0.5">Nenhum comentário.</td></tr>
                            <?php else: foreach ($comentarios as $c): ?>
                            <tr>
                                <td><?= esc($c['usuario_nome'] ?? $c['autor_nome'] ?? 'Anônimo') ?></td>
                                <td><?= resumir($c['conteudo'], 80) ?></td>
                                <td><a href="../../artigo.php?slug=<?= esc($c['artigo_slug']) ?>" target="_blank"><?= resumir($c['artigo_titulo'], 40) ?></a></td>
                                <td><span class="status-badge status-<?= $c['status'] ?>"><?= $c['status'] ?></span></td>
                                <td style="font-size:0.85rem;opacity:0.6"><?= tempo_relativo($c['criado_em']) ?></td>
                                <td class="actions">
                                    <?php if ($c['status'] !== 'aprovado'): ?>
                                    <a href="?aprovar=<?= $c['id'] ?>" class="btn btn-sm" style="color:#2ecc71"><i class="bi bi-check-circle"></i></a>
                                    <?php endif; ?>
                                    <?php if ($c['status'] !== 'rejeitado'): ?>
                                    <a href="?rejeitar=<?= $c['id'] ?>" class="btn btn-sm" style="color:#e74c3c"><i class="bi bi-x-circle"></i></a>
                                    <?php endif; ?>
                                    <a href="?deletar=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deletar?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
