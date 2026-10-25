<?php
declare(strict_types=1);

namespace Konf\Repo;

use Konf\Db;
use Konf\Validator;

final class Users
{
    
    public static function register(array $f): array
    {
        if (preg_match('/^8[0-9]{10}$/', $f['phone'] ?? '')) {
            $f['phone'] = preg_replace('/^8([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/', '8($1)$2-$3-$4', $f['phone']);
        }
        $v = (new Validator($f))
            ->required('login', 'Введите логин')
            ->regex('login', Validator::LOGIN, 'Только латиница и цифры, не менее 6 символов')
            ->required('password', 'Введите пароль')
            ->minLen('password', 8, 'Пароль — не менее 8 символов')
            ->required('full_name', 'Введите ФИО')
            ->regex('full_name', Validator::FIO, 'Только кириллица и пробелы')
            ->required('phone', 'Введите телефон')
            ->regex('phone', Validator::PHONE, 'Формат: 8(XXX)XXX-XX-XX')
            ->required('email', 'Введите e-mail')
            ->check('email', $f['email'] === '' || filter_var($f['email'], FILTER_VALIDATE_EMAIL) !== false, 'Некорректный e-mail');

        if (!isset($v->errors()['login']) && Db::one('SELECT 1 FROM users WHERE login = ?', [$f['login']]) !== null) {
            $v->check('login', false, 'Такой логин уже занят');
        }
        if (!$v->ok()) {
            return $v->errors();
        }
        Db::insert(
            'INSERT INTO users (login, password_hash, full_name, phone, email, role_id, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)',
            [$f['login'], password_hash($f['password'], PASSWORD_DEFAULT), preg_replace('/\s+/u', ' ', $f['full_name']), $f['phone'], $f['email'], date('Y-m-d H:i:s')]
        );
        return [];
    }
}
