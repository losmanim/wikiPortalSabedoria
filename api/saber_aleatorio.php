<?php
/**
 * API: Retorna slug de um artigo aleatório
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();
$artigo = $db->fetch(
    "SELECT slug FROM artigos WHERE status = 'publicado' ORDER BY RAND() LIMIT 1"
);

if ($artigo) {
    json_response(['slug' => $artigo['slug']]);
} else {
    json_response(['slug' => null], 404);
}
