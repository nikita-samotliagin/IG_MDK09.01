<?php
declare(strict_types=1);

namespace Konf\Repo;

use Konf\Db;

final class Dict
{
    public static function rooms(): array
    {
        return Db::all('SELECT * FROM room ORDER BY id');
    }

    public static function statuses(): array
    {
        return Db::all('SELECT * FROM status ORDER BY id');
    }

    public static function payments(): array
    {
        return Db::all('SELECT * FROM payment_type ORDER BY id');
    }

    public static function exists(string $table, int $id): bool
    {
        $allowed = ['room', 'status', 'payment_type'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        return Db::one("SELECT 1 FROM {$table} WHERE id = ?", [$id]) !== null;
    }
}
