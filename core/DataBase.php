<?php
// core/Database.php
declare(strict_types=1);

namespace Core;

class Database
{
    private static ?self $instance = null;
    private \PDO $pdo;

    private function __construct()
    {
        $config = require CONFIG_PATH . '/database.php';

        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']};port=" . ($config['port'] ?? 3306);
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->pdo;
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar singleton");
    }
}