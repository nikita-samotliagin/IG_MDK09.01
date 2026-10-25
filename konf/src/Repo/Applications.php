<?php
declare(strict_types=1);

namespace Konf\Repo;

use Konf\Db;

final class Applications
{
    public const NEW = 1;
    public const ASSIGNED = 2;
    public const DONE = 3;

    public const NEXT = [
        self::NEW      => [self::ASSIGNED, self::DONE],
        self::ASSIGNED => [self::DONE],
        self::DONE     => [],
    ];

    private const SELECT = '
        SELECT a.id, a.status_id, s.name AS status, a.created_at,
               e.title, e.starts_at, e.description, r.name AS room, r.place,
               p.name AS payment, u.login, u.full_name, u.phone, u.email,
               v.rating, v.body AS review, v.created_at AS reviewed_at
          FROM application a
          JOIN event e          ON e.id = a.event_id
          JOIN room r           ON r.id = e.room_id
          JOIN status s         ON s.id = a.status_id
          JOIN payment_type p   ON p.id = a.payment_type_id
          JOIN users u          ON u.id = a.user_id
     LEFT JOIN review v         ON v.application_id = a.id';

    public static function create(int $userId, array $f): int
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $eventId = Db::insert(
                'INSERT INTO event (title, starts_at, room_id, description) VALUES (?, ?, ?, ?)',
                [$f['title'], str_replace('T', ' ', $f['starts_at']), (int) $f['room_id'], $f['description']]
            );
            $id = Db::insert(
                'INSERT INTO application (user_id, event_id, payment_type_id, status_id, created_at) VALUES (?, ?, ?, ?, ?)',
                [$userId, $eventId, (int) $f['payment_type_id'], self::NEW, date('Y-m-d H:i:s')]
            );
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function forUser(int $userId): array
    {
        return Db::all(self::SELECT . ' WHERE a.user_id = ? ORDER BY a.created_at DESC, a.id DESC', [$userId]);
    }

    public static function find(int $id): ?array
    {
        return Db::one(self::SELECT . ' WHERE a.id = ?', [$id]);
    }

    public static function search(array $f): array
    {
        $where = [];
        $params = [];
        if (!empty($f['status'])) {
            $where[] = 'a.status_id = ?';
            $params[] = (int) $f['status'];
        }
        if (!empty($f['room'])) {
            $where[] = 'r.id = ?';
            $params[] = (int) $f['room'];
        }
        if (!empty($f['q'])) {
            $where[] = '(u.login LIKE ? OR u.full_name LIKE ? OR e.title LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($f['from'])) {
            $where[] = 'e.starts_at >= ?';
            $from = \DateTime::createFromFormat('!d.m.Y', $f['from']);
            $params[] = ($from ? $from->format('Y-m-d') : $f['from']) . ' 00:00:00';
        }
        if (!empty($f['to'])) {
            $where[] = 'e.starts_at <= ?';
            $to = \DateTime::createFromFormat('!d.m.Y', $f['to']);
            $params[] = ($to ? $to->format('Y-m-d') : $f['to']) . ' 23:59:59';
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY a.created_at DESC, a.id DESC';
        return Db::all($sql, $params);
    }

    public static function counters(): array
    {
        $out = [self::NEW => 0, self::ASSIGNED => 0, self::DONE => 0];
        foreach (Db::all('SELECT status_id, COUNT(*) AS n FROM application GROUP BY status_id') as $r) {
            $out[(int) $r['status_id']] = (int) $r['n'];
        }
        return $out;
    }

    public static function changeStatus(int $id, int $to): ?string
    {
        $row = Db::one('SELECT status_id FROM application WHERE id = ?', [$id]);
        if ($row === null) {
            return 'Заявка не найдена';
        }
        if (!in_array($to, self::NEXT[(int) $row['status_id']], true)) {
            return 'Статус можно менять только вперёд';
        }
        Db::run('UPDATE application SET status_id = ? WHERE id = ?', [$to, $id]);
        return null;
    }

    public static function cancel(int $id, int $userId): bool
    {
        $row = Db::one('SELECT event_id FROM application WHERE id = ? AND user_id = ? AND status_id = ?', [$id, $userId, self::NEW]);
        if ($row === null) {
            return false;
        }
        Db::run('DELETE FROM event WHERE id = ?', [$row['event_id']]);   
        return true;
    }

    public static function stats(int $userId): array
    {
        $rows = Db::all('SELECT status_id, COUNT(*) AS n FROM application WHERE user_id = ? GROUP BY status_id', [$userId]);
        $s = ['total' => 0, self::NEW => 0, self::ASSIGNED => 0, self::DONE => 0];
        foreach ($rows as $r) {
            $s[(int) $r['status_id']] = (int) $r['n'];
            $s['total'] += (int) $r['n'];
        }
        $s['reviews'] = (int) Db::one(
            'SELECT COUNT(*) AS n FROM review v JOIN application a ON a.id = v.application_id WHERE a.user_id = ?',
            [$userId]
        )['n'];
        $s['next'] = Db::one(
            'SELECT e.title, e.starts_at, r.name AS room FROM application a JOIN event e ON e.id = a.event_id
               JOIN room r ON r.id = e.room_id
              WHERE a.user_id = ? AND a.status_id = ? AND e.starts_at >= ? ORDER BY e.starts_at LIMIT 1',
            [$userId, self::ASSIGNED, date('Y-m-d H:i:s')]
        );
        return $s;
    }
}
