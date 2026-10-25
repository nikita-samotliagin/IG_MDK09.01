<?php
declare(strict_types=1);

namespace Konf;

use PDO;

final class Db
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function config(string $key): mixed
    {
        return self::$config[$key] ?? null;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            $isSqlite = str_starts_with($c['dsn'], 'sqlite:');
            $fresh = $isSqlite && !is_file(substr($c['dsn'], 7));
            self::$pdo = new PDO($c['dsn'], $c['db_user'], $c['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            if ($isSqlite) {
                self::$pdo->exec('PRAGMA foreign_keys = ON');
                if ($fresh) {
                    self::$pdo->exec((string) file_get_contents(__DIR__ . '/../database/schema.sql'));
                }
            }
            self::ensureAdmin();
        }
        return self::$pdo;
    }

    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);
        return (int) self::pdo()->lastInsertId();
    }

    private static function ensureAdmin(): void
    {
        $a = self::$config['admin'];
        $st = self::$pdo->prepare('SELECT id FROM users WHERE login = ?');
        $st->execute([$a['login']]);
        if ($st->fetch() === false) {
            self::$pdo->prepare(
                'INSERT INTO users (login, password_hash, full_name, phone, email, role_id) VALUES (?, ?, ?, ?, ?, 2)'
            )->execute([$a['login'], password_hash($a['password'], PASSWORD_DEFAULT), 'Администратор портала', '8(000)000-00-00', 'admin@konf.local']);
        }
    }
}
