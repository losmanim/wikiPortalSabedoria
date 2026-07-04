<?php
/**
 * Cadastro de Usuários
 */
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = Database::getInstance();
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting check
    if (!Security::checkRateLimit('registro', 3, 300)) {
        $erro = 'Muitas tentativas de registro. Aguarde 5 minutos.';
    }
    
    // CAPTCHA validation
    if (!Captcha::validateMath($_POST['captcha_math'] ?? '')) {
        $erro = 'Resposta incorreta. Tente novamente.';
    }
    
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if (!$erro && (!$nome || !$email || !$senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (!$erro && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (!$erro && strlen($senha) < 6) {
        $erro = 'Senha deve ter no mínimo 6 caracteres.';
    } elseif (!$erro && $senha !== $confirmar) {
        $erro = 'Senhas não conferem.';
    } else {
        $existe = $db->fetch('SELECT id FROM usuarios WHERE email = ?', [$email]);
        if ($existe) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            $id = $db->insert('usuarios', [
                'nome' => $nome,
                'email' => $email,
                'senha' => $hash,
                'nivel' => 'user',
                'atualizado_em' => date('Y-m-d H:i:s')
            ]);
            log_atividade($db, $id, 'registro', "Novo usuário: $nome");
            header('Location: login.php?registrado=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?= APP_NAME ?></title>
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
        .form-group input {
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
        .erro {
            background: rgba(231,76,60,0.15);
            color: #e74c3c;
            padding: 12px; border-radius: 8px; margin-bottom: 20px;
            text-align: center; font-size: 0.9rem;
        }
        .auth-links { text-align: center; margin-top: 20px; font-size: 0.9rem; }
        .auth-links a { color: #f39c12; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        .form-row { display: flex; gap: 15px; }
        @media (max-width: 480px) { .form-row { flex-direction: column; gap: 0; } }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">🕉️</div>
        <h1 class="auth-title">Criar Conta</h1>
        <p class="auth-subtitle">Junte-se à comunidade de saberes</p>

        <?php if ($erro): ?>
            <div class="erro"><?= esc($erro) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" required value="<?= esc($_POST['nome'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= esc($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label>Senha</label>
                    <input type="password" name="senha" required minlength="6">
                </div>
                <div class="form-group" style="flex:1">
                    <label>Confirmar</label>
                    <input type="password" name="confirmar_senha" required minlength="6">
                </div>
            </div>
            <div class="form-group">
                <label>Verificação de Segurança</label>
                <?= Captcha::mathField() ?>
            </div>
            <button type="submit" class="btn">Criar Conta</button>
        </form>

        <div class="auth-links">
            <a href="login.php">Já tem conta? Faça login</a> &middot;
            <a href="../index.php">Voltar</a>
        </div>
    </div>
</body>
</html>
