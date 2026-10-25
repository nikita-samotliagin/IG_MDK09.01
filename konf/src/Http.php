<?php
declare(strict_types=1);

namespace Konf;

final class Http
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function checkCsrf(): void
    {
        $sent = (string) ($_POST['_token'] ?? '');
        if (!hash_equals(self::csrfToken(), $sent)) {
            self::abort(419, 'Страница устарела. Обновите её и повторите действие.');
        }
    }

    public static function redirect(string $to, ?string $flash = null): never
    {
        if ($flash !== null) {
            $_SESSION['flash'] = $flash;
        }
        header('Location: ' . $to, true, 302);
        exit;
    }

    public static function takeFlash(): ?string
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    public static function render(string $view, array $data = [], int $code = 200): never
    {
        http_response_code($code);
        $data['user'] ??= Auth::user();
        $data['flash'] ??= self::takeFlash();
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
        exit;
    }

    public static function abort(int $code, string $text): never
    {
        self::render('error', ['title' => 'Ошибка ' . $code, 'code' => $code, 'text' => $text], $code);
    }

    public static function post(string $key): string
    {
        return trim((string) ($_POST[$key] ?? ''));
    }
}

