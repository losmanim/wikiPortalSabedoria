<?php
/**
 * Sitemap XML para SEO
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Página inicial
$base = APP_URL;
echo "  <url><loc>{$base}/</loc><priority>1.0</priority><changefreq>daily</changefreq></url>\n";

// Artigos publicados
$artigos = $db->select('SELECT slug, atualizado_em FROM artigos WHERE status = "publicado" ORDER BY atualizado_em DESC');
foreach ($artigos as $a) {
    $data = date('Y-m-d', strtotime($a['atualizado_em'] ?: 'now'));
    echo "  <url><loc>{$base}/artigo/" . esc($a['slug']) . "</loc><lastmod>{$data}</lastmod><priority>0.9</priority></url>\n";
}

// Categorias
$cats = $db->select('SELECT slug FROM categorias ORDER BY nome');
foreach ($cats as $c) {
    echo "  <url><loc>{$base}/categoria/" . esc($c['slug']) . "</loc><priority>0.7</priority></url>\n";
}

// Páginas estáticas
$paginas = $db->select('SELECT slug FROM paginas WHERE status = "publicado"');
foreach ($paginas as $p) {
    echo "  <url><loc>{$base}/pagina/" . esc($p['slug']) . "</loc><priority>0.5</priority></url>\n";
}

// Biblioteca
echo "  <url><loc>{$base}/biblioteca.php</loc><priority>0.8</priority><changefreq>weekly</changefreq></url>\n";

// Busca
echo "  <url><loc>{$base}/busca.php</loc><priority>0.3</priority></url>\n";

echo '</urlset>';
