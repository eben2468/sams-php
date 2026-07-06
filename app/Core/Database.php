<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * PDO connection singleton + small query helpers.
 * Replaces Laravel's DB facade / Eloquent connection layer.
 */
class Database
{
    protected static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $db = $config['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db['host'],
            $db['port'],
            $db['database'],
            $db['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $db['username'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }

        return self::$pdo;
    }

    /**
     * Run a SELECT and return all rows as associative arrays.
     */
    public static function select(string $sql, array $bindings = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(self::normalizeBindings($bindings));
        return $stmt->fetchAll();
    }

    /**
     * Run a SELECT and return the first row (or null).
     */
    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(self::normalizeBindings($bindings));
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Run a single scalar query (e.g. COUNT) and return the first column.
     */
    public static function scalar(string $sql, array $bindings = [])
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(self::normalizeBindings($bindings));
        return $stmt->fetchColumn();
    }

    /**
     * Run an INSERT/UPDATE/DELETE statement, return affected row count.
     */
    public static function statement(string $sql, array $bindings = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(self::normalizeBindings($bindings));
        return $stmt->rowCount();
    }

    public static function insertGetId(string $sql, array $bindings = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute(self::normalizeBindings($bindings));
        return (int) self::connection()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::connection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connection()->commit();
    }

    public static function rollBack(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }

    /**
     * Normalize booleans/Carbon dates to scalar bind values.
     */
    protected static function normalizeBindings(array $bindings): array
    {
        $out = [];
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            } elseif ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $out[$key] = $value;
        }
        return $out;
    }
}
