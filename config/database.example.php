<?php
// ============================================================
// config/database.example.php
// Contoh konfigurasi - COPY ke database.php dan isi password
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'andalan_beton');
define('DB_USER', 'postgres');
define('DB_PASS', 'YOUR_PASSWORD_HERE');  // Ganti dengan password asli  // Ganti dengan password asli
define('DB_SSLMODE', 'disable');

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
                    DB_HOST, DB_PORT, DB_NAME, DB_SSLMODE
                );
                
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                
                header('Content-Type: application/json');
                http_response_code(500);
                die(json_encode([
                    'error' => true,
                    'message' => 'Koneksi database gagal: ' . $e->getMessage()
                ]));
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): array|false {
        return self::query($sql, $params)->fetch();
    }

    public static function fetchColumn(string $sql, array $params = [], int $col = 0): mixed {
        return self::query($sql, $params)->fetchColumn($col);
    }

    public static function insert(string $table, array $data): int|string {
        $cols = implode(', ', array_keys($data));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$cols}) VALUES ({$places}) RETURNING id";
        $stmt = self::query($sql, array_values($data));
        return $stmt->fetchColumn();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $sets = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$sets}, updated_at = NOW() WHERE {$where}";
        $stmt = self::query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): void {
        self::getInstance()->beginTransaction();
    }

    public static function commit(): void {
        self::getInstance()->commit();
    }

    public static function rollback(): void {
        self::getInstance()->rollBack();
    }
}