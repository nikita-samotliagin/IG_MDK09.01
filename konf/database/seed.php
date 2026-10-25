<?php

declare(strict_types=1);

$_SERVER['REQUEST_URI'] = '/';
ob_start();
require __DIR__ . '/../src/bootstrap.php';
ob_end_clean();

use Konf\Db;
use Konf\Repo\Users;

if (Db::one('SELECT 1 FROM users WHERE login = ?', ['orlova01']) === null) {
    Users::register(['login' => 'orlova01', 'password' => 'Konf2027', 'full_name' => 'Орлова Мария Сергеевна', 'phone' => '8(916)555-12-34', 'email' => 'orlova@example.ru']);
    $uid = (int) Db::one('SELECT id FROM users WHERE login = ?', ['orlova01'])['id'];
    Users::register(['login' => 'kuznetsov7', 'password' => 'Konf2027', 'full_name' => 'Кузнецов Дмитрий Олегович', 'phone' => '8(903)222-44-10', 'email' => 'kuznetsov@example.ru']);
    $uid2 = (int) Db::one('SELECT id FROM users WHERE login = ?', ['kuznetsov7'])['id'];

    $demo = [
        
        [$uid,  'Цифровая экономика — 2027',         -12, 1, 2, 3, 30],
        [$uid,  'Секция «Молодые исследователи»',     9, 2, 1, 2, 6],
        [$uid,  'Показ научно-популярных фильмов',   21, 3, 2, 1, 1],
        [$uid2, 'Форум IT-образования',               14, 1, 1, 1, 2],
        [$uid2, 'Круглый стол по кибербезопасности', -5, 2, 2, 3, 20],
    ];
    foreach ($demo as [$u, $title, $startDays, $room, $pay, $status, $createdDays]) {
        $eventId = Db::insert('INSERT INTO event (title, starts_at, room_id, description) VALUES (?, ?, ?, ?)',
            [$title, date('Y-m-d 10:00:00', strtotime("$startDays days")), $room, '']);
        Db::insert('INSERT INTO application (user_id, event_id, payment_type_id, status_id, created_at) VALUES (?, ?, ?, ?, ?)',
            [$u, $eventId, $pay, $status, date('Y-m-d H:i:s', strtotime("-$createdDays days 14:20"))]);
    }
    
    $done = Db::one('SELECT a.id FROM application a WHERE a.user_id = ? AND a.status_id = 3', [$uid2]);
    Db::insert('INSERT INTO review (application_id, rating, body, created_at) VALUES (?, 4, ?, ?)',
        [$done['id'], 'Удобный коворкинг, хватило розеток на всех. Не хватило второго микрофона.', date('Y-m-d H:i:s', strtotime('-4 days'))]);
    echo "Демо-данные добавлены\n";
} else {
    echo "Демо-данные уже есть\n";
}
