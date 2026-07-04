<?php
/**
 * Sistema de Segurança Aprimorado
 * CSRF Protection, Rate Limiting, Input Validation
 */

class Security {
    
    /**
     * Gera e armazena token CSRF
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida token CSRF
     */
    public static function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token expira após 1 hora
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Gera campo HTML para CSRF
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Rate Limiting
     */
    private static function getRateLimitKey($action) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return 'rate_limit_' . $action . '_' . $ip;
    }
    
    public static function checkRateLimit($action, $maxAttempts = 5, $window = 60) {
        $key = self::getRateLimitKey($action);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'attempts' => 0,
                'first_attempt' => time()
            ];
        }
        
        $data = $_SESSION[$key];
        
        // Reset se passou o tempo da janela
        if (time() - $data['first_attempt'] > $window) {
            $_SESSION[$key] = [
                'attempts' => 1,
                'first_attempt' => time()
            ];
            return true;
        }
        
        // Verifica se excedeu o limite
        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key]['attempts']++;
        return true;
    }
    
    public static function getRateLimitRemaining($action, $maxAttempts = 5, $window = 60) {
        $key = self::getRateLimitKey($action);
        
        if (!isset($_SESSION[$key])) {
            return $maxAttempts;
        }
        
        $data = $_SESSION[$key];
        
        // Reset se passou o tempo da janela
        if (time() - $data['first_attempt'] > $window) {
            return $maxAttempts;
        }
        
        return max(0, $maxAttempts - $data['attempts']);
    }
    
    /**
     * Sanitização de Input
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validação de Email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validação de URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validação de Inteiro
     */
    public static function validateInt($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }
        
        $int = (int)$value;
        
        if ($min !== null && $int < $min) {
            return false;
        }
        
        if ($max !== null && $int > $max) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validação de String
     */
    public static function validateString($value, $minLength = null, $maxLength = null) {
        if (!is_string($value)) {
            return false;
        }
        
        $length = mb_strlen($value, 'UTF-8');
        
        if ($minLength !== null && $length < $minLength) {
            return false;
        }
        
        if ($maxLength !== null && $length > $maxLength) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Headers de Segurança HTTP
     */
    public static function setSecurityHeaders() {
        // Prevenção de Clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevenção de MIME sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilita XSS protection (navegadores antigos)
        header('X-XSS-Protection: 1; mode=block');
        
        // Política de Referer
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (básico)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;");
        
        // HSTS (apenas em produção com HTTPS)
        if (APP_ENV === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Log de tentativas de segurança
     */
    public static function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'details' => $details,
            'user_id' => $_SESSION['usuario_id'] ?? null
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Validação de senha forte
     */
    public static function validatePasswordStrength($password) {
        if (mb_strlen($password) < 8) {
            return ['valid' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres'];
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter pelo menos uma letra maiúscula'];
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter pelo menos uma letra minúscula'];
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'A senha deve conter pelo menos um número'];
        }
        
        return ['valid' => true, 'message' => ''];
    }
    
    /**
     * Gera senha segura
     */
    public static function generateSecurePassword($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 1, $length);
    }
}