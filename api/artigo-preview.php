<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    http_response_code(400);
    echo json_encode(['error' => 'Slug não informado']);
    exit;
}

$artigo = $db->fetch(
    'SELECT a.titulo, a.conteudo, a.resumo, c.nome as cat_nome, c.cor as cat_cor, c.icone as cat_icone, a.tags
     FROM artigos a
     LEFT JOIN categorias c ON c.id = a.categoria_id
     WHERE a.slug = ? AND a.status = "publicado"',
    [$slug]
);

if (!$artigo) {
    http_response_code(404);
    echo json_encode(['error' => 'Artigo não encontrado']);
    exit;
}

// Get first ~1000 chars of rendered HTML content
$fullHtml = $artigo['conteudo'];
$previewHtml = '';

// Try to get headings and first paragraphs
if (preg_match_all('/<h[23][^>]*>.*?<\/h[23]>|<p[^>]*>.*?<\/p>|<blockquote[^>]*>.*?<\/blockquote>/s', $fullHtml, $matches)) {
    $charCount = 0;
    foreach ($matches[0] as $block) {
        $clean = strip_tags($block);
        if ($charCount + strlen($clean) > 1000) break;
        $previewHtml .= $block . "\n";
        $charCount += strlen($clean);
    }
}

if (empty($previewHtml)) {
    $previewHtml = '<p>' . esc(mb_substr(strip_tags($fullHtml), 0, 600)) . '</p>';
}

$hasMore = mb_strlen(strip_tags($fullHtml)) > 1000;

echo json_encode([
    'titulo' => $artigo['titulo'],
    'html' => $previewHtml,
    'has_more' => $hasMore,
    'categoria' => $artigo['cat_nome'],
    'cat_cor' => $artigo['cat_cor'],
    'cat_icone' => $artigo['cat_icone'],
    'tags' => $artigo['tags'],
]);
