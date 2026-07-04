<?php
/**
 * Sistema CAPTCHA simples e eficaz
 */

class Captcha {
    
    /**
     * Gera novo CAPTCHA
     */
    public static function generate() {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sem caracteres confusos (I, l, 1, O, 0)
        $code = '';
        
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        $_SESSION['captcha_code'] = $code;
        $_SESSION['captcha_time'] = time();
        
        return $code;
    }
    
    /**
     * Valida CAPTCHA
     */
    public static function validate($userInput) {
        if (!isset($_SESSION['captcha_code']) || !isset($_SESSION['captcha_time'])) {
            return false;
        }
        
        // CAPTCHA expira após 5 minutos
        if (time() - $_SESSION['captcha_time'] > 300) {
            unset($_SESSION['captcha_code']);
            unset($_SESSION['captcha_time']);
            return false;
        }
        
        $valid = strtoupper($userInput) === $_SESSION['captcha_code'];
        
        // Limpa após validação
        unset($_SESSION['captcha_code']);
        unset($_SESSION['captcha_time']);
        
        return $valid;
    }
    
    /**
     * Gera imagem CAPTCHA
     */
    public static function generateImage() {
        $code = self::generate();
        
        $width = 150;
        $height = 50;
        
        $image = imagecreatetruecolor($width, $height);
        
        // Cores
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        $textColor = imagecolorallocate($image, 50, 50, 50);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        
        // Fundo
        imagefill($image, 0, 0, $bgColor);
        
        // Linhas de ruído
        for ($i = 0; $i < 5; $i++) {
            imageline($image, 
                rand(0, $width), rand(0, $height),
                rand(0, $width), rand(0, $height),
                $lineColor
            );
        }
        
        // Texto
        $fontSize = 20;
        $x = 20;
        
        for ($i = 0; $i < strlen($code); $i++) {
            $angle = rand(-15, 15);
            $y = rand(30, 40);
            imagettftext($image, $fontSize, $angle, $x, $y, $textColor, 
                __DIR__ . '/../assets/fonts/arial.ttf', $code[$i]);
            $x += 20;
        }
        
        // Pontos de ruído
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $textColor);
        }
        
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }
    
    /**
     * Gera CAPTCHA matemático (alternativa mais acessível)
     */
    public static function generateMath() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];
        
        if ($operator === '+') {
            $result = $num1 + $num2;
        } else {
            $result = $num1 - $num2;
        }
        
        $_SESSION['captcha_math'] = $result;
        $_SESSION['captcha_time'] = time();
        
        return "$num1 $operator $num2 = ?";
    }
    
    /**
     * Valida CAPTCHA matemático
     */
    public static function validateMath($userInput) {
        if (!isset($_SESSION['captcha_math']) || !isset($_SESSION['captcha_time'])) {
            return false;
        }
        
        // CAPTCHA expira após 5 minutos
        if (time() - $_SESSION['captcha_time'] > 300) {
            unset($_SESSION['captcha_math']);
            unset($_SESSION['captcha_time']);
            return false;
        }
        
        $valid = (int)$userInput === $_SESSION['captcha_math'];
        
        // Limpa após validação
        unset($_SESSION['captcha_math']);
        unset($_SESSION['captcha_time']);
        
        return $valid;
    }
    
    /**
     * Gera HTML para CAPTCHA matemático
     */
    public static function mathField() {
        $question = self::generateMath();
        return '
            <div class="captcha-container">
                <label class="captcha-label">' . $question . '</label>
                <input type="number" name="captcha_math" class="captcha-input" required autocomplete="off">
            </div>
        ';
    }
}