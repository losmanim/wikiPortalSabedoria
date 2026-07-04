<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
required_editor();
$db = Database::getInstance();
$erro = ''; $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($titulo);
    $conteudo = $_POST['conteudo'] ?? '';
    $no_menu = isset($_POST['no_menu']) ? 1 : 0;
    $status = $_POST['status'] ?? 'publicado';
    $id = (int)($_POST['id'] ?? 0);
    if (!$titulo) { $erro = 'Título obrigatório.'; }
    else {
        $data = compact('titulo','slug','conteudo','no_menu','status');
        if ($id > 0) { $db->update('paginas', $data, 'id = ?', [$id]); $sucesso = 'Página atualizada!'; }
        else { $id = $db->insert('paginas', $data); $sucesso = 'Página criada!'; }
    }
}
if (isset($_GET['deletar'])) { $db->delete('DELETE FROM paginas WHERE id = ?', [(int)$_GET['deletar']]); }

$paginas = $db->select('SELECT * FROM paginas ORDER BY ordem');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Páginas - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar"><h1>Páginas</h1></div>
            <?php if ($sucesso): ?><div class="alert alert-success"><?= esc($sucesso) ?></div><?php endif; ?>
            <?php if ($erro): ?><div class="alert alert-error"><?= esc($erro) ?></div><?php endif; ?>

            <div class="admin-grid-2">
                <div class="admin-card">
                    <div class="card-header"><h3>Nova Página</h3></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group"><label>Título</label><input type="text" name="titulo" required></div>
                            <div class="form-group"><label>Slug</label><input type="text" name="slug" placeholder="deixe vazio para automático"></div>
                            <div class="form-group"><label>Conteúdo (HTML)</label><textarea name="conteudo" rows="8"></textarea></div>
                            <div class="form-row">
                                <div class="form-group"><label>Status</label><select name="status"><option value="publicado">Publicado</option><option value="rascunho">Rascunho</option></select></div>
                                <div class="form-group"><label>&nbsp;</label><label><input type="checkbox" name="no_menu" checked> Mostrar no menu</label></div>
                            </div>
                            <button type="submit" name="salvar" class="btn btn-primary">Criar Página</button>
                        </form>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header"><h3>Todas as Páginas</h3></div>
                    <div class="card-body" style="padding:0">
                        <table class="table">
                            <thead><tr><th>Título</th><th>Slug</th><th>Status</th><th>Menu</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($paginas as $p): ?>
                                <tr>
                                    <td><?= esc($p['titulo']) ?></td>
                                    <td style="font-size:0.85rem;opacity:0.6"><?= esc($p['slug']) ?></td>
                                    <td><span class="status-badge status-<?= $p['status'] ?>"><?= $p['status'] ?></span></td>
                                    <td><?= $p['no_menu'] ? '✅' : '—' ?></td>
                                    <td class="actions">
                                        <a href="../../pagina/<?= esc($p['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a>
                                        <a href="?deletar=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deletar?')"><i class="bi bi-trash"></i></a>
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
