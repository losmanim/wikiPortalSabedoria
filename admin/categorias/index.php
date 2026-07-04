<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
required_editor();
$db = Database::getInstance();
$erro = ''; $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($nome);
    $descricao = trim($_POST['descricao'] ?? '');
    $icone = trim($_POST['icone'] ?? 'bi bi-folder');
    $cor = trim($_POST['cor'] ?? '#f39c12');
    $id = (int)($_POST['id'] ?? 0);
    if (!$nome) { $erro = 'Nome obrigatório.'; }
    else {
        $data = compact('nome','slug','descricao','icone','cor');
        if ($id > 0) { $db->update('categorias', $data, 'id = ?', [$id]); $sucesso = 'Categoria atualizada!'; }
        else { $db->insert('categorias', $data); $sucesso = 'Categoria criada!'; }
    }
}
if (isset($_GET['deletar']) && is_admin()) {
    $db->delete('DELETE FROM categorias WHERE id = ?', [(int)$_GET['deletar']]);
    $sucesso = 'Categoria deletada!';
}

$categorias = $db->select('SELECT c.*, (SELECT COUNT(*) FROM artigos WHERE categoria_id = c.id) as total_artigos FROM categorias c ORDER BY c.ordem');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar"><h1>Categorias</h1></div>
            <?php if ($sucesso): ?><div class="alert alert-success"><?= esc($sucesso) ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="alert alert-error"><?= esc($erro) ?></div><?php endif; ?>

            <div class="admin-grid-2">
                <div class="admin-card">
                    <div class="card-header"><h3>Nova Categoria</h3></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" name="nome" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Slug</label>
                                    <input type="text" name="slug" placeholder="gerado automático">
                                </div>
                                <div class="form-group">
                                    <label>Ícone (Bootstrap Icons)</label>
                                    <input type="text" name="icone" value="bi bi-folder">
                                </div>
                                <div class="form-group">
                                    <label>Cor</label>
                                    <input type="color" name="cor" value="#f39c12" style="height:42px;padding:4px">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea name="descricao" rows="3"></textarea>
                            </div>
                            <button type="submit" name="salvar" class="btn btn-primary">Criar Categoria</button>
                        </form>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header"><h3>Todas as Categorias</h3></div>
                    <div class="card-body" style="padding:0">
                        <table class="table">
                            <thead><tr><th>Nome</th><th>Slug</th><th>Artigos</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($categorias as $c): ?>
                                <tr>
                                    <td><i class="<?= esc($c['icone']) ?>" style="color:<?= esc($c['cor']) ?>"></i> <?= esc($c['nome']) ?></td>
                                    <td style="font-size:0.85rem;opacity:0.6"><?= esc($c['slug']) ?></td>
                                    <td><?= $c['total_artigos'] ?></td>
                                    <td class="actions">
                                        <a href="?deletar=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deletar?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
