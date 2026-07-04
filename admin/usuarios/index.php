<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
required_admin();
$db = Database::getInstance();

if (isset($_GET['banir'])) { $db->update('usuarios', ['status' => 'banido'], 'id = ?', [(int)$_GET['banir']]); }
if (isset($_GET['ativar'])) { $db->update('usuarios', ['status' => 'ativo'], 'id = ?', [(int)$_GET['ativar']]); }
if (isset($_GET['deletar']) && (int)$_GET['deletar'] !== $_SESSION['usuario_id']) {
    $db->delete('DELETE FROM usuarios WHERE id = ?', [(int)$_GET['deletar']]);
}

$usuarios = $db->select('SELECT id, nome, email, nivel, status, criado_em FROM usuarios ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar"><h1>Usuários (<?= count($usuarios) ?>)</h1></div>
            <div class="admin-card">
                <div class="card-body" style="padding:0">
                    <table class="table">
                        <thead><tr><th>Nome</th><th>Email</th><th>Nível</th><th>Status</th><th>Cadastro</th><th>Ações</th></tr></thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= esc($u['nome']) ?></td>
                                <td><?= esc($u['email']) ?></td>
                                <td><span class="status-badge" style="background:rgba(243,156,18,0.15);color:#f39c12"><?= $u['nivel'] ?></span></td>
                                <td><span class="status-badge status-<?= $u['status'] ?>"><?= $u['status'] ?></span></td>
                                <td style="font-size:0.85rem;opacity:0.6"><?= data_br($u['criado_em']) ?></td>
                                <td class="actions">
                                    <?php if ($u['status'] === 'ativo'): ?>
                                    <a href="?banir=<?= $u['id'] ?>" class="btn btn-sm" style="color:#e74c3c" onclick="return confirm('Banir?')"><i class="bi bi-slash-circle"></i></a>
                                    <?php else: ?>
                                    <a href="?ativar=<?= $u['id'] ?>" class="btn btn-sm" style="color:#2ecc71"><i class="bi bi-check-circle"></i></a>
                                    <?php endif; ?>
                                    <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                    <a href="?deletar=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deletar permanentemente?')"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
