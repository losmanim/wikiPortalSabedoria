<?php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/Cloudinary.php';
required_editor();
$db = Database::getInstance();
$cloud = Cloudinary::getInstance();

$useCloudinary = APP_ENV === 'production' || getenv('CLOUDINARY_CLOUD_NAME');
$uploadDir = UPLOAD_PATH;
$uploadUrl = APP_URL . '/uploads/';
$erro = '';
$sucesso = '';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function uploadToCloudinary($file, $publicId = null) {
    global $cloud;
    $result = $cloud->upload($file, $publicId);
    if (isset($result['secure_url'])) {
        return $result['secure_url'];
    }
    throw new Exception($result['error']['message'] ?? 'Falha no upload para Cloudinary');
}

function getAllowedExtensions() {
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $arquivo = $_FILES['upload'];
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, getAllowedExtensions())) {
            throw new Exception('Formato não permitido: ' . $ext);
        }
        if ($arquivo['size'] > MAX_UPLOAD_SIZE) {
            throw new Exception('Arquivo muito grande (máx ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB)');
        }

        if ($useCloudinary) {
            $url = uploadToCloudinary($arquivo['tmp_name'], uniqid('upload_'));
        } else {
            $nome = uniqid() . '.' . $ext;
            $destino = $uploadDir . $nome;
            if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
                throw new Exception('Falha ao salvar o arquivo');
            }
            $url = $uploadUrl . $nome;
        }

        echo json_encode(['location' => $url], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['error' => ['message' => $e->getMessage()]], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['arquivo'])) {
    $arquivo = $_FILES['arquivo'];
    $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, getAllowedExtensions())) {
        $erro = 'Formato não permitido: ' . $ext;
    } elseif ($arquivo['size'] > MAX_UPLOAD_SIZE) {
        $erro = 'Arquivo muito grande (máx ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB)';
    } elseif ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Erro no upload (código ' . $arquivo['error'] . ')';
    } else {
        try {
            if ($useCloudinary) {
                $url = uploadToCloudinary($arquivo['tmp_name'], uniqid('upload_'));
                $sucesso = 'Arquivo enviado para Cloudinary';
            } else {
                $nome = uniqid() . '.' . $ext;
                if (move_uploaded_file($arquivo['tmp_name'], $uploadDir . $nome)) {
                    $sucesso = 'Arquivo enviado: ' . $nome;
                } else {
                    throw new Exception('Falha ao salvar o arquivo');
                }
            }
            log_atividade($db, $_SESSION['usuario_id'], 'upload', "Upload: {$ext}");
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

if (isset($_GET['deletar'])) {
    $arquivo = basename($_GET['deletar']);
    $caminho = $uploadDir . $arquivo;
    if (file_exists($caminho) && unlink($caminho)) {
        $sucesso = 'Arquivo deletado: ' . $arquivo;
        log_atividade($db, $_SESSION['usuario_id'], 'upload_deletado', "Deletou: {$arquivo}");
    } else {
        $erro = 'Erro ao deletar arquivo.';
    }
}

if ($useCloudinary) {
    $arquivos = $cloud->listResources();
    $total = count($arquivos);
} else {
    $arquivos = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp,svg,ico}', GLOB_BRACE);
    usort($arquivos, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $total = count($arquivos);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mídia - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar"><?php include __DIR__ . '/../sidebar.php'; ?></aside>
        <main class="admin-main">
            <div class="admin-topbar">
                <h1>Mídia (<?= $total ?>)</h1>
            </div>

            <?php if ($erro): ?><div class="alert alert-error"><?= esc($erro) ?></div><?php endif; ?>
            <?php if ($sucesso): ?><div class="alert alert-success"><?= esc($sucesso) ?></div><?php endif; ?>

            <div class="admin-card" style="margin-bottom:25px">
                <div class="card-header"><h3><i class="bi bi-upload"></i> Enviar Imagem</h3></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="form-box">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="file" name="arquivo" accept="image/*" required
                                       style="padding:10px;background:var(--bg2);border:1px solid var(--border);border-radius:6px;width:100%">
                                <div class="form-help">Formatos: JPG, PNG, GIF, WebP, SVG (máx <?= MAX_UPLOAD_SIZE / 1024 / 1024 ?>MB)</div>
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="midia-grid">
                <?php if (empty($arquivos)): ?>
                    <p style="text-align:center;padding:40px;opacity:0.5">Nenhuma imagem ainda. Faça upload!</p>
                <?php else: ?>
                    <?php if ($useCloudinary): ?>
                        <?php foreach ($arquivos as $arq):
                            $nome = $arq['public_id'];
                            $url = $arq['secure_url'];
                            $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION) ?: pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                            $tamanho = round(($arq['bytes'] ?? 0) / 1024, 1);
                            $data = date('d/m/Y H:i', strtotime($arq['created_at'] ?? 'now'));
                        ?>
                        <div class="midia-item">
                            <div class="midia-preview">
                                <?php if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])): ?>
                                    <img src="<?= esc($url) ?>" alt="<?= esc($nome) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="midia-icon"><i class="bi bi-file-earmark-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="midia-info">
                                <div class="midia-nome" title="<?= esc($nome) ?>"><?= esc(basename($nome)) ?></div>
                                <div class="midia-meta"><?= $tamanho ?>KB &middot; <?= $data ?></div>
                                <div class="midia-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="copiarURL('<?= esc($url) ?>')" title="Copiar URL">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($arquivos as $arq): 
                            $nome = basename($arq);
                            $tamanho = round(filesize($arq) / 1024, 1);
                            $url = $uploadUrl . $nome;
                            $data = date('d/m/Y H:i', filemtime($arq));
                            $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
                        ?>
                        <div class="midia-item">
                            <div class="midia-preview">
                                <?php if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])): ?>
                                    <img src="<?= esc($url) ?>" alt="<?= esc($nome) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="midia-icon"><i class="bi bi-file-earmark-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="midia-info">
                                <div class="midia-nome" title="<?= esc($nome) ?>"><?= esc($nome) ?></div>
                                <div class="midia-meta"><?= $tamanho ?>KB &middot; <?= $data ?></div>
                                <div class="midia-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="copiarURL('<?= esc($url) ?>')" title="Copiar URL">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                    <a href="?deletar=<?= urlencode($nome) ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Deletar <?= esc($nome) ?>?')" title="Deletar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
    function copiarURL(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('URL copiada: ' + url);
        });
    }
    </script>
    <style>
    .midia-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    .midia-item {
        background: var(--bg2);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        transition: transform 0.2s;
    }
    .midia-item:hover { transform: translateY(-2px); }
    .midia-preview {
        width: 100%; height: 160px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.2);
        overflow: hidden;
    }
    .midia-preview img {
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .midia-icon { font-size: 3rem; opacity: 0.3; }
    .midia-info { padding: 10px 12px; }
    .midia-nome {
        font-size: 0.8rem;
        white-space: nowrap; overflow: hidden;
        text-overflow: ellipsis; margin-bottom: 5px;
    }
    .midia-meta { font-size: 0.75rem; opacity: 0.4; margin-bottom: 8px; }
    .midia-actions { display: flex; gap: 5px; }
    </style>
</body>
</html>
