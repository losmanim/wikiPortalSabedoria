<?php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/Cloudinary.php';

$db = Database::getInstance();
$cloud = Cloudinary::getInstance();

$folderCategoryMap = [
    '01_gnose-esoterismo' => 'gnose-esoterismo',
    '02_cristianismo-esoterico' => 'cristianismo-esoterico',
    '02_filosofia-consciencia' => 'filosofia-consciencia',
    '03_hermetismo-teosofia' => 'hermetismo-teosofia',
    '04_consciencia-meditacao' => 'consciencia-meditacao',
    '05_animes' => 'animes-animacoes',
    '05_corpo-regeneracao' => 'corpo-regeneracao',
    '06_musica-sons' => 'musica-sons',
];

function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 100);
    return $slug;
}

function cleanTitle($filename) {
    $publicId = pathinfo($filename, PATHINFO_FILENAME);
    $publicId = preg_replace('/_[a-z0-9]{6,}$/', '', $publicId);
    $title = str_replace(['_', '-'], ' ', $publicId);
    $title = preg_replace('/^\d+\s*/', '', $title);
    return $title;
}

$imported = 0;
$skipped = 0;

foreach ($folderCategoryMap as $folder => $catSlug) {
    $resources = $cloud->listResources($folder);

    if (empty($resources)) {
        echo "Pasta vazia ou não encontrada: $folder\n";
        continue;
    }

    $categoria = $db->fetch('SELECT id FROM categorias WHERE slug = ?', [$catSlug]);
    if (!$categoria) {
        echo "Categoria não encontrada: $catSlug\n";
        continue;
    }

    $categoriaId = $categoria['id'];

    foreach ($resources as $resource) {
        $publicId = $cloud->getPublicId($resource);
        $title = cleanTitle($publicId);
        $slug = generateSlug($title);
        $mediaUrl = $cloud->getUrl($resource);
        $resourceType = $cloud->getResourceType($resource);
        $mediaType = $resourceType === 'video' ? 'video' : 'audio';

        $existing = $db->fetch('SELECT id FROM artigos WHERE slug = ?', [$slug]);
        if ($existing) {
            $skipped++;
            continue;
        }

        $playerHtml = $mediaType === 'video'
            ? '<video controls style="width:100%;max-width:720px;border-radius:8px"><source src="' . esc($mediaUrl) . '" type="video/mp4"></video>'
            : '<audio controls style="width:100%"><source src="' . esc($mediaUrl) . '" type="audio/mpeg"></audio>';

        $conteudo = "<p>Conteúdo multimídia de <strong>$mediaType</strong> disponível na biblioteca.</p>";
        $conteudo .= "<p>$playerHtml</p>";
        $conteudo .= '<p><a href="' . esc($mediaUrl) . '" target="_blank" rel="noopener">📥 Baixar / Abrir</a></p>';

        $tags = implode(', ', [$slug, $mediaType, 'multimidia', $catSlug]);

        $db->insert('artigos', [
            'categoria_id' => $categoriaId,
            'autor_id' => 1,
            'titulo' => $title,
            'slug' => $slug,
            'resumo' => "Conteúdo de $mediaType: " . substr($title, 0, 150),
            'conteudo' => $conteudo,
            'tags' => $tags,
            'fonte' => $mediaUrl,
            'status' => 'publicado',
            'publicado_em' => date('Y-m-d H:i:s'),
            'atualizado_em' => date('Y-m-d H:i:s')
        ]);

        $imported++;
        echo "Importado: $title ($mediaType)\n";
    }
}

echo "\n=== Resumo ===\n";
echo "Importados: $imported\n";
echo "Pulados (já existiam): $skipped\n";
echo "Total processado: " . ($imported + $skipped) . "\n";
