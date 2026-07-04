<?php
/**
 * Gerenciar Artigos
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
required_editor();
$db = Database::getInstance();

$acao = $_GET['acao'] ?? 'listar';
$id = (int)($_GET['id'] ?? 0);
$erro = '';
$sucesso = '';
$categorias = $db->select('SELECT * FROM categorias ORDER BY ordem');

// Salvar artigo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $slug = !empty($_POST['slug']) ? slugify($_POST['slug']) : slugify($titulo);
    $conteudo = $_POST['conteudo'] ?? '';
    $resumo = trim($_POST['resumo'] ?? '');
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $tags = trim($_POST['tags'] ?? '');
    $fonte = trim($_POST['fonte'] ?? '');
    $status = $_POST['status'] ?? 'rascunho';

    if (!$titulo) { $erro = 'O título é obrigatório.'; }
    else {
        // ─── SEM FILTROS: Auto-publish para usuários com reputação ───
        $nivel = usuario_nivel();
        $confiavel = false;
        if ($nivel === 'user') {
            $rep = $db->fetch('SELECT nivel FROM usuario_reputacao WHERE usuario_id = ?', [$_SESSION['usuario_id']]);
            $confiavel = $rep && in_array($rep['nivel'], ['aprendiz','contribuidor','especialista','mestre','lendario']);
        }
        if ($nivel === 'admin' || $nivel === 'editor' || $confiavel) {
            $status = 'publicado';
        }

        $data = [
            'titulo' => $titulo,
            'slug' => $slug,
            'conteudo' => $conteudo,
            'resumo' => $resumo,
            'categoria_id' => $categoria_id,
            'tags' => $tags,
            'fonte' => $fonte,
            'status' => $status,
            'autor_id' => $_SESSION['usuario_id']
        ];
        if ($status === 'publicado' && (!isset($_POST['ja_publicado']) || !$_POST['ja_publicado'])) {
            $data['publicado_em'] = date('Y-m-d H:i:s');
        }

        if ($id > 0) {
            $db->update('artigos', $data, 'id = ?', [$id]);
            $sucesso = 'Artigo atualizado!';
            log_atividade($db, $_SESSION['usuario_id'], 'artigo_atualizado', "Artigo: $titulo");
        } else {
            $id = $db->insert('artigos', $data);
            $sucesso = 'Artigo criado!';
            log_atividade($db, $_SESSION['usuario_id'], 'artigo_criado', "Artigo: $titulo");
            // Gamificação
            require_once __DIR__ . '/../../includes/Gamification.php';
            $gam = new Gamification($db);
            $gam->registerAction($_SESSION['usuario_id'], 'artigo_criado');
        }
    }
}

// Deletar
if ($acao === 'deletar' && $id > 0 && is_admin()) {
    $a = $db->fetch('SELECT titulo FROM artigos WHERE id = ?', [$id]);
    $db->delete('DELETE FROM artigos WHERE id = ?', [$id]);
    $sucesso = 'Artigo deletado!';
    log_atividade($db, $_SESSION['usuario_id'], 'artigo_deletado', "Artigo: {$a['titulo']}");
    $acao = 'listar';
}

// Carregar dados para edição
$artigo = null;
if ($acao === 'editar' && $id > 0) {
    $artigo = $db->fetch('SELECT * FROM artigos WHERE id = ?', [$id]);
    if (!$artigo) { header('Location: index.php'); exit; }
}

// Listar artigos
$pagina = (int)($_GET['pagina'] ?? 1);
$offset = ($pagina - 1) * 20;
$search = trim($_GET['search'] ?? '');
$where = '1=1';
$params = [];
if ($search) {
    $where = 'a.titulo LIKE ? OR a.conteudo LIKE ?';
    $params = ["%$search%", "%$search%"];
}
$total = $db->contar('artigos a', $where, $params);
$artigos = $db->paginar(
    "SELECT a.*, c.nome as cat_nome FROM artigos a LEFT JOIN categorias c ON c.id = a.categoria_id WHERE {$where} ORDER BY a.atualizado_em DESC",
    $params, $pagina, 20
);
$total_paginas = ceil($total / 20);

// Editor
if ($acao === 'editar' || $acao === 'novo'):
    $a = $artigo ?: ['id'=>0, 'titulo'=>'', 'slug'=>'', 'conteudo'=>'', 'resumo'=>'', 'categoria_id'=>'', 'tags'=>'', 'fonte'=>'', 'status'=>'rascunho', 'publicado_em'=>null];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $artigo ? 'Editar' : 'Novo' ?> Artigo - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#editor-conteudo',
        height: 600,
        menubar: true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | image link media | code fullscreen | help',
        toolbar_mode: 'sliding',
        images_upload_handler: function (blobInfo) {
            return new Promise(function (resolve, reject) {
                var formData = new FormData();
                formData.append('upload', blobInfo.blob(), blobInfo.filename());
                fetch('<?= APP_URL ?>/admin/midia/index.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function (response) { return response.json(); })
                .then(function (result) {
                    if (result.location) {
                        resolve(result.location);
                    } else {
                        reject(result.error.message || 'Upload failed');
                    }
                })
                .catch(function (error) {
                    reject('Erro no upload: ' + error.message);
                });
            });
        }
    });
    </script>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar">
                <h1><?= $artigo ? 'Editar' : 'Novo' ?> Artigo</h1>
                <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
            </div>

            <?php if ($erro): ?><div class="alert alert-error"><?= esc($erro) ?></div><?php endif; ?>
            <?php if ($sucesso): ?><div class="alert alert-success"><?= esc($sucesso) ?></div><?php endif; ?>

            <form method="POST" class="form-box">
                <input type="hidden" name="ja_publicado" value="<?= $a['publicado_em'] ? 1 : 0 ?>">
                <div class="form-group">
                    <label>Título do Artigo</label>
                    <input type="text" name="titulo" required value="<?= esc($a['titulo']) ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Slug (URL)</label>
                        <input type="text" name="slug" value="<?= esc($a['slug']) ?>">
                        <div class="form-help">Deixe em branco para gerar automaticamente</div>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria_id">
                            <option value="">Sem categoria</option>
                            <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $a['categoria_id'] ? 'selected' : '' ?>>
                                <?= esc($c['nome']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="rascunho" <?= $a['status'] === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                            <option value="publicado" <?= $a['status'] === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                            <option value="arquivado" <?= $a['status'] === 'arquivado' ? 'selected' : '' ?>>Arquivado</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Resumo (aparece nas listagens)</label>
                    <textarea name="resumo" maxlength="500" rows="3"><?= esc($a['resumo']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Conteúdo <small>(HTML permitido)</small></label>
                    <textarea name="conteudo" id="editor-conteudo" rows="20"><?= esc($a['conteudo']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tags (separadas por vírgula)</label>
                        <input type="text" name="tags" value="<?= esc($a['tags']) ?>" placeholder="gnose, hermetismo, meditação">
                    </div>
                    <div class="form-group">
                        <label>Fonte (origem do conteúdo)</label>
                        <input type="text" name="fonte" value="<?= esc($a['fonte']) ?>" placeholder="Ex: Modelos/pistisSofia">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="salvar" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> <?= $artigo ? 'Atualizar' : 'Publicar' ?>
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
<?php exit; endif; ?>

<!-- LISTAGEM -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="sidebar-logo">🕉️</span>
                <span class="sidebar-title">Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                <a href="index.php" class="active"><i class="bi bi-file-text"></i> Artigos</a>
                <a href="../categorias/index.php"><i class="bi bi-folder"></i> Categorias</a>
                <a href="../comentarios/index.php"><i class="bi bi-chat-dots"></i> Comentários</a>
                <a href="../usuarios/index.php"><i class="bi bi-people"></i> Usuários</a>
                <a href="../paginas/index.php"><i class="bi bi-file-earmark"></i> Páginas</a>
                <hr>
                <a href="../../index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
                <a href="../../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </nav>
            <div class="sidebar-footer"><?= esc($_SESSION['usuario_nome']) ?></div>
        </aside>

        <main class="admin-main">
            <div class="admin-topbar">
                <h1>Artigos (<?= $total ?>)</h1>
                <a href="index.php?acao=novo" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Artigo</a>
            </div>

            <?php if ($sucesso): ?><div class="alert alert-success"><?= esc($sucesso) ?></div><?php endif; ?>

            <div class="card-toolbar">
                <form method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Buscar artigos..." value="<?= esc($search) ?>">
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-search"></i></button>
                </form>
            </div>

            <div class="admin-card">
                <div class="card-body" style="padding:0">
                    <table class="table">
                        <thead>
                            <tr><th>Título</th><th>Categoria</th><th>Status</th><th>Views</th><th>Data</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($artigos)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:30px;opacity:0.5">Nenhum artigo encontrado.</td></tr>
                            <?php else: ?>
                            <?php foreach ($artigos as $a): ?>
                            <tr>
                                <td><a href="index.php?acao=editar&id=<?= $a['id'] ?>"><?= esc($a['titulo']) ?></a></td>
                                <td><?= esc($a['cat_nome'] ?? '-') ?></td>
                                <td><span class="status-badge status-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
                                <td><?= $a['views'] ?></td>
                                <td style="font-size:0.85rem;opacity:0.6"><?= data_br($a['publicado_em'] ?: $a['criado_em']) ?></td>
                                <td class="actions">
                                    <a href="index.php?acao=editar&id=<?= $a['id'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil"></i></a>
                                    <?php if (is_admin()): ?>
                                    <a href="index.php?acao=deletar&id=<?= $a['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Deletar este artigo?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="../../artigo.php?slug=<?= esc($a['slug']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_paginas > 1): ?>
            <div class="paginacao">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
