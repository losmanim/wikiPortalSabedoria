<?php
/**
 * Importador de Conteúdo Multimídia Gnóstico
 * Importa arquivos de áudio e vídeo como artigos no portal
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/functions.php';

$db = Database::getInstance();

// Mapeamento de categorias por diretório
$categoryMap = [
    'audios-lz/01_gnose-esoterismo' => 'gnose-esoterismo',
    'audios-lz/02_cristianismo-esoterico' => 'cristianismo-esoterico',
    'audios-lz/03_hermetismo-teosofia' => 'hermetismo-teosofia',
    'audios-lz/04_consciencia-meditacao' => 'consciencia-meditacao',
    'audios-lz/05_corpo-regeneracao' => 'corpo-regeneracao',
    'audios-lz/06_musica-sons' => 'musica-sons',
    'videos-lz/01_frequencias-curacao' => 'frequencias-cura',
    'videos-lz/02_filosofia-consciencia' => 'filosofia-consciencia',
    'videos-lz/03_historia-cultura' => 'historia-cultura',
    'videos-lz/04_momentos-pessoais' => 'momentos-pessoais',
    'videos-lz/05_animes' => 'animes-animacoes',
];

// Função para gerar slug
function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 100);
    return $slug;
}

// Função para limpar título
function cleanTitle($filename) {
    $title = pathinfo($filename, PATHINFO_FILENAME);
    $title = preg_replace('/^\d+[_\s-]?/', '', $title); // Remove número inicial
    $title = str_replace(['_', '-'], ' ', $title);
    $title = preg_replace('/\.(mp3|mp4|mkv)$/i', '', $title);
    return $title;
}

// Importar arquivos
$imported = 0;
$skipped = 0;

foreach ($categoryMap as $dir => $slug) {
    $fullPath = __DIR__ . '/multimidia/' . $dir;
    
    if (!is_dir($fullPath)) {
        continue;
    }
    
    // Obter categoria ID
    $categoria = $db->fetch('SELECT id FROM categorias WHERE slug = ?', [$slug]);
    if (!$categoria) {
        echo "Categoria não encontrada: $slug\n";
        continue;
    }
    
    $categoriaId = $categoria['id'];
    
    // Listar arquivos
    $files = glob($fullPath . '/*.{mp3,mp4,mkv}', GLOB_BRACE);
    
    foreach ($files as $file) {
        $filename = basename($file);
        $title = cleanTitle($filename);
        $slug = generateSlug($title);
        
        // Verificar se já existe
        $existing = $db->fetch('SELECT id FROM artigos WHERE slug = ?', [$slug]);
        if ($existing) {
            $skipped++;
            continue;
        }
        
        // Determinar tipo de mídia
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $mediaType = in_array($extension, ['mp3']) ? 'audio' : 'video';
        
        // Criar artigo
        $conteudo = "<p>Conteúdo multimídia de <strong>$mediaType</strong> disponível na biblioteca.</p>";
        $conteudo .= "<p><em>Arquivo: " . esc($filename) . "</em></p>";
        
        $tags = implode(', ', [$slug, $mediaType, 'multimidia']);
        
        $db->insert('artigos', [
            'categoria_id' => $categoriaId,
            'autor_id' => 1, // Admin
            'titulo' => $title,
            'slug' => $slug,
            'resumo' => "Conteúdo de $mediaType: " . substr($title, 0, 150),
            'conteudo' => $conteudo,
            'tags' => $tags,
            'fonte' => 'multimidia/' . $dir,
            'status' => 'publicado',
            'publicado_em' => date('Y-m-d H:i:s'),
            'atualizado_em' => date('Y-m-d H:i:s')
        ]);
        
        $imported++;
        echo "Importado: $title\n";
    }
}

echo "\n=== Resumo ===\n";
echo "Importados: $imported\n";
echo "Pulados (já existiam): $skipped\n";
echo "Total processado: " . ($imported + $skipped) . "\n";
