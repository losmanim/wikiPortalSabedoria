<?php
/**
 * Sistema de Cache Simples
 * Suporta cache em arquivo com TTL
 */

class Cache {
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hora
    
    /**
     * Inicializa o diretório de cache
     */
    public static function init($cacheDir = null) {
        self::$cacheDir = $cacheDir ?: __DIR__ . '/../cache/';
        
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Gera chave de cache segura
     */
    private static function key($key) {
        return md5($key) . '.cache';
    }
    
    /**
     * Obtém valor do cache
     */
    public static function get($key, $default = null) {
        self::init();
        
        $file = self::$cacheDir . self::key($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // Verifica se expirou
        if ($data['expires'] < time()) {
            @unlink($file);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Define valor no cache
     */
    public static function set($key, $value, $ttl = null) {
        self::init();
        
        $ttl = $ttl ?? self::$defaultTTL;
        $file = self::$cacheDir . self::key($key);
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Remove item do cache
     */
    public static function delete($key) {
        self::init();
        
        $file = self::$cacheDir . self::key($key);
        
        if (file_exists($file)) {
            return @unlink($file);
        }
        
        return true;
    }
    
    /**
     * Limpa todo o cache
     */
    public static function clear() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        
        foreach ($files as $file) {
            @unlink($file);
        }
        
        return true;
    }
    
    /**
     * Limpa cache expirado
     */
    public static function clearExpired() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            $data = @unserialize(file_get_contents($file));
            
            if ($data && $data['expires'] < time()) {
                @unlink($file);
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Cache com callback (lazy loading)
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Obtém estatísticas do cache
     */
    public static function stats() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $expired = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            
            $data = @unserialize(file_get_contents($file));
            if ($data && $data['expires'] < time()) {
                $expired++;
            }
        }
        
        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'expired_files' => $expired,
            'cache_dir' => self::$cacheDir
        ];
    }
}
