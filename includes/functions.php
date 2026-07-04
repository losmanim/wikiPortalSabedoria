<?php
/**
 * Funções auxiliares do Portal
 */

function esc($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

// Função de CSRF
function csrf_field() {
    return Security::csrfField();
}

function csrf_token() {
    return Security::generateCsrfToken();
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error($msg, $status = 400) {
    json_response(['error' => $msg], $status);
}

function slugify($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $map = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
    ];
    $texto = strtr($texto, $map);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

function data_br($data) {
    if (!$data) return '-';
    return date('d/m/Y H:i', strtotime($data));
}

function tempo_relativo($data) {
    if (!$data) return '';
    $diferenca = time() - strtotime($data);
    if ($diferenca < 60) return 'agora';
    if ($diferenca < 3600) return floor($diferenca / 60) . ' min atrás';
    if ($diferenca < 86400) return floor($diferenca / 3600) . 'h atrás';
    if ($diferenca < 2592000) return floor($diferenca / 86400) . ' dias atrás';
    return date('d/m/Y', strtotime($data));
}

function resumir($texto, $limite = 200) {
    if (!$texto) return '';
    $texto = strip_tags($texto);
    if (mb_strlen($texto) <= $limite) return $texto;
    return mb_substr($texto, 0, $limite) . '...';
}

function esta_logado() {
    return isset($_SESSION['usuario_id']);
}

function usuario_nivel() {
    return $_SESSION['usuario_nivel'] ?? 'visitante';
}

function is_admin() {
    return usuario_nivel() === 'admin';
}

function is_editor() {
    return in_array(usuario_nivel(), ['admin', 'editor']);
}

function required_login() {
    if (!esta_logado()) {
        $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

function required_admin() {
    required_login();
    if (!is_admin()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function required_editor() {
    required_login();
    if (!is_editor()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function log_atividade($db, $usuario_id, $acao, $descricao = null) {
    $db->insert('logs', [
        'usuario_id' => $usuario_id,
        'acao' => $acao,
        'descricao' => $descricao,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

function valida_campos($fields, $data) {
    foreach ($fields as $f) {
        if (!isset($data[$f]) || (is_string($data[$f]) && trim($data[$f]) === '')) {
            return "O campo '{$f}' é obrigatório.";
        }
    }
    return null;
}

function enviar_email($para, $assunto, $mensagem) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . APP_NAME . " <" . APP_ADMIN_EMAIL . ">\r\n";
    return mail($para, $assunto, $mensagem, $headers);
}
