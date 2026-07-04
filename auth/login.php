<?php
/**
 * Autenticação - Login
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    if (!Security::checkRateLimit('login', 5, 60)) {
        $erro = 'Muitas tentativas de login. Aguarde 1 minuto.';
        Security::logSecurityEvent('login_rate_limit', ['email' => $_POST['email'] ?? '']);
    }
    
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $usuario = $db->fetch('SELECT * FROM usuarios WHERE email = ? AND status = "ativo"', [$email]);

    if (!$erro && $usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'];
        $_SESSION['usuario_avatar'] = $usuario['avatar'];

        log_atividade($db, $usuario['id'], 'login', 'Usuário fez login');

        $redirect = $_SESSION['redirect_after'] ?? APP_URL . '/admin/index.php';
        unset($_SESSION['redirect_after']);
        header("Location: $redirect");
        exit;
    } else {
        $erro = 'Email ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a0a1a 0%, #16213e 50%, #1a1a2e 100%);
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .auth-container {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .auth-logo { text-align: center; font-size: 3rem; margin-bottom: 10px; }
        .auth-title { text-align: center; font-size: 1.4rem; margin-bottom: 5px; background: linear-gradient(90deg, #f39c12, #e74c3c); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .auth-subtitle { text-align: center; font-size: 0.85rem; opacity: 0.5; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8; }
        .form-group input, .form-group select {
            width: 100%; padding: 12px 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; color: #fff; font-size: 1rem;
            transition: border 0.3s; font-family: inherit;
        }
        .form-group input:focus { outline: none; border-color: #f39c12; }
        .btn {
            width: 100%; padding: 14px; border: none; border-radius: 8px;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: transform 0.2s;
            background: linear-gradient(90deg, #f39c12, #e67e22);
            color: #1a1a2e;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .erro {
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.3);
            color: #e74c3c;
            padding: 12px; border-radius: 8px; margin-bottom: 20px;
            text-align: center; font-size: 0.9rem;
        }
        .auth-links { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .auth-links a { color: #f39c12; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        .divider {
            display: flex; align-items: center; gap: 15px;
            margin: 20px 0; opacity: 0.3;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: currentColor; }
        .alert-success {
            background: rgba(46,204,113,0.15);
            border: 1px solid rgba(46,204,113,0.3);
            color: #2ecc71;
            padding: 12px; border-radius: 8px; margin-bottom: 20px;
            text-align: center; font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">🕉️</div>
        <h1 class="auth-title"><?= APP_NAME ?></h1>
        <p class="auth-subtitle">Faça login para acessar o portal</p>

        <?php if ($erro): ?>
            <div class="erro"><?= esc($erro) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registrado'])): ?>
            <div class="alert-success">✅ Conta criada! Faça login.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autocomplete="email"
                       value="<?= esc($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>

        <div class="auth-links">
            <a href="registro.php">Criar conta</a> &middot;
            <a href="../index.php">Voltar ao site</a>
        </div>
    </div>
</body>
</html>
