<?php
/**
 * Importador de Citações Gnósticas
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/Quotes.php';

$db = Database::getInstance();
$quotes = new Quotes($db);

echo "Importando citações gnósticas...\n";
$importadas = $quotes->importGnosticQuotes();
echo "Importadas: $importadas citações\n";
