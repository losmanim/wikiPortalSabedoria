<?php
/**
 * Conexão PDO com MySQL (Singleton aprimorado)
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'portal_saberes');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instancia = null;
    private $pdo;

    private function __construct() {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die('Erro DB: ' . $e->getMessage());
            }
            die('Erro ao conectar ao banco.');
        }
    }

    public static function getInstance() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function select($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function insert($table, $data) {
        $cols = implode(', ', array_keys($data));
        $vals = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $params = []) {
        $set = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $params));
        return $stmt->rowCount();
    }

    public function delete($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function contar($tabela, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$tabela} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    public function paginar($sql, $params = [], $pagina = 1, $porPagina = 12) {
        $pagina = max(1, (int)$pagina);
        $offset = ($pagina - 1) * $porPagina;
        $sql .= " LIMIT {$porPagina} OFFSET {$offset}";
        return $this->select($sql, $params);
    }

    public function getPdo() {
        return $this->pdo;
    }
}
