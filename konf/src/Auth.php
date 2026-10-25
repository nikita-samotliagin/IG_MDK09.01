<?php
declare(strict_types=1);

namespace Konf;

final class Auth
{
    private static ?array $cached = null;

    public static function user(): ?array
    {
        if (self::$cached === null && isset($_SESSION['uid'])) {
            self::$cached = Db::one(
                'SELECT u.id, u.login, u.full_name, u.phone, u.email, u.created_at, r.code AS role
                   FROM users u JOIN role r ON r.id = u.role_id WHERE u.id = ?',
                [$_SESSION['uid']]
            );
        }
        return self::$cached;
    }

    public static function attempt(string $login, string $password): ?string
    {
        $since = time() - (int) Db::config('lock_sec');
        $fails = Db::one('SELECT COUNT(*) AS n FROM login_fail WHERE login = ? AND at > ?', [$login, $since])['n'];
        if ($fails >= Db::config('max_fail')) {
            http_response_code(429);
            return 'Слишком много попыток входа. Попробуйте через 5 минут.';
        }
        $row = Db::one('SELECT id, password_hash FROM users WHERE login = ?', [$login]);
        if ($row === null || !password_verify($password, $row['password_hash'])) {
            Db::run('INSERT INTO login_fail (login, at) VALUES (?, ?)', [$login, time()]);
            return 'Неверный логин или пароль';
        }
        Db::run('DELETE FROM login_fail WHERE login = ?', [$login]);
        session_regenerate_id(true);      
        $_SESSION['uid'] = (int) $row['id'];
        self::$cached = null;
        return null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function requireUser(): array
    {
        $u = self::user();
        if ($u === null) {
            Http::redirect('/login', 'Войдите, чтобы продолжить');
        }
        if ($u['role'] !== 'user') {
            Http::abort(403, 'Раздел доступен только участникам');
        }
        return $u;
    }

    public static function requireAdmin(): array
    {
        $u = self::user();
        if ($u === null) {
            Http::redirect('/login', 'Войдите, чтобы продолжить');
        }
        if ($u['role'] !== 'admin') {
            Http::abort(403, 'Раздел доступен только администратору');
        }
        return $u;
    }
}
