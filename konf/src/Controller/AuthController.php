<?php
declare(strict_types=1);

namespace Konf\Controller;

use Konf\Auth;
use Konf\Http;
use Konf\Repo\Users;

final class AuthController
{
    public function registerForm(): never
    {
        Http::render('register', ['title' => 'Регистрация', 'old' => [], 'errors' => []]);
    }

    public function register(): never
    {
        Http::checkCsrf();
        $f = [];
        foreach (['login', 'password', 'full_name', 'phone', 'email'] as $k) {
            $f[$k] = Http::post($k);
        }
        $errors = Users::register($f);
        if ($errors) {
            unset($f['password']);   
            Http::render('register', ['title' => 'Регистрация', 'old' => $f, 'errors' => $errors], 422);
        }
        Http::redirect('/login', 'Регистрация прошла успешно. Войдите в систему.');
    }

    public function loginForm(): never
    {
        if (Auth::user()) {
            Http::redirect(Auth::user()['role'] === 'admin' ? '/admin' : '/applications');
        }
        Http::render('login', ['title' => 'Авторизация', 'old' => [], 'errors' => [], 'alert' => null]);
    }

    public function login(): never
    {
        Http::checkCsrf();
        $login = Http::post('login');
        $password = (string) ($_POST['password'] ?? '');
        $errors = [];
        if ($login === '') {
            $errors['login'] = 'Введите логин';
        }
        if ($password === '') {
            $errors['password'] = 'Введите пароль';
        }
        $alert = null;
        if (!$errors) {
            $alert = Auth::attempt($login, $password);
            if ($alert === null) {
                Http::redirect(Auth::user()['role'] === 'admin' ? '/admin' : '/applications');
            }
        }
        $code = http_response_code() === 429 ? 429 : ($errors ? 422 : 401);
        Http::render('login', ['title' => 'Авторизация', 'old' => ['login' => $login], 'errors' => $errors, 'alert' => $alert], $code);
    }

    public function logout(): never
    {
        Http::checkCsrf();
        Auth::logout();
        Http::redirect('/', 'Вы вышли из системы');
    }
}
