<?php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$nav = [['/', 'Помещения', 'home']];
if ($user && $user['role'] === 'admin') {
    $nav[] = ['/admin', 'Все заявки', 'list'];
} elseif ($user) {
    $nav[] = ['/profile', 'Личный кабинет', 'user'];
    $nav[] = ['/applications', 'Мои заявки', 'list'];
    $nav[] = ['/applications/new', 'Новая заявка', 'plus'];
} else {
    $nav[] = ['/login', 'Вход', 'enter'];
    $nav[] = ['/register', 'Регистрация', 'user'];
}
$icons = [
    'home'  => 'M3 11 12 4l9 7v9h-6v-6H9v6H3z',
    'list'  => 'M4 6h16M4 12h16M4 18h10',
    'plus'  => 'M12 5v14M5 12h14',
    'user'  => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-7 8a7 7 0 0 1 14 0',
    'enter' => 'M10 17l5-5-5-5M15 12H3M14 4h5v16h-5',
];
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ? e($title) . ' · ' : '' ?>Конференции.РФ</title>
    <meta name="description" content="Бронирование помещений для проведения Всероссийских конференций">
    <link rel="icon" href="/assets/img/logo-32.png">
    <link rel="preload" href="/assets/fonts/PTSans-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="shell">
    <aside class="side" id="side">
        <a class="brand" href="/">
            <img src="/assets/img/logo-64.png" width="40" height="40" alt="">
            <span>Конференции<b>.РФ</b></span>
        </a>
        <nav class="menu" aria-label="Основное меню">
            <?php foreach ($nav as [$href, $label, $icon]): ?>
                <a href="<?= $href ?>" class="<?= $path === $href ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $icons[$icon] ?>"/></svg><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php if ($user): ?>
            <div class="side__user">
                <span class="avatar"><?= e(mb_substr($user['full_name'], 0, 1)) ?></span>
                <div><b><?= e($user['login']) ?></b><small><?= $user['role'] === 'admin' ? 'администратор' : 'участник' ?></small></div>
                <form method="post" action="/logout">
                    <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">
                    <button class="btn-link" type="submit">Выйти</button>
                </form>
            </div>
        <?php endif; ?>
    </aside>

    <div class="main">
        <header class="topbar">
            <a class="brand brand--small" href="/"><img src="/assets/img/logo-32.png" width="28" height="28" alt=""><span>Конференции<b>.РФ</b></span></a>
            <button class="menu-btn" type="button" aria-controls="side" aria-expanded="false" data-menu-btn>
                <span></span><span class="sr">Меню</span>
            </button>
        </header>

        <main class="content">
            <?php if ($flash): ?>
                <div class="notice <?= str_starts_with($flash, 'Ошибка') ? 'notice--error' : '' ?>" role="status"><?= e($flash) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>

        <footer class="foot">
            <span>© 2027 Конференции.РФ — помещения для Всероссийских конференций</span>
            <span class="foot__links"><a href="#" aria-label="ВКонтакте">VK</a><a href="#" aria-label="Одноклассники">OK</a></span>
        </footer>
    </div>
</div>
<div class="veil" data-veil hidden></div>
<script src="/assets/js/app.js" defer></script>
</body>
</html>
