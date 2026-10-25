<?php
declare(strict_types=1);

namespace Konf\Controller;

use Konf\Auth;
use Konf\Http;
use Konf\Repo\Applications;
use Konf\Repo\Dict;
use Konf\Repo\Reviews;
use Konf\Validator;

final class ApplicationController
{
    public function index(): never
    {
        $u = Auth::requireUser();
        Http::render('applications', [
            'title' => 'Мои заявки',
            'items' => Applications::forUser((int) $u['id']),
        ]);
    }

    public function createForm(): never
    {
        Auth::requireUser();
        $this->form([], []);
    }

    public function create(): never
    {
        $u = Auth::requireUser();
        Http::checkCsrf();
        $f = [];
        foreach (['room_id', 'title', 'starts_at', 'payment_type_id', 'description'] as $k) {
            $f[$k] = Http::post($k);
        }
        $inputDate = $f['starts_at'];
        $format = preg_match('/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}$/', $inputDate) ? 'd.m.Y H:i' : 'Y-m-d\\TH:i';
        $start = \DateTime::createFromFormat('!' . $format, $inputDate);
        if ($start !== false && $start->format($format) !== $inputDate) {
            $start = false;
        }
        $v = (new Validator($f))
            ->required('room_id', 'Выберите помещение')
            ->check('room_id', $f['room_id'] === '' || Dict::exists('room', (int) $f['room_id']), 'Такого помещения нет')
            ->required('title', 'Введите название мероприятия')
            ->check('title', mb_strlen($f['title']) <= 150, 'Не длиннее 150 символов')
            ->required('starts_at', 'Укажите дату и время начала')
            ->check('starts_at', $f['starts_at'] === '' || $start !== false, 'Некорректная дата')
            ->check('starts_at', $start === false || $start > new \DateTime(), 'Дата начала должна быть в будущем')
            ->required('payment_type_id', 'Выберите способ оплаты')
            ->check('payment_type_id', $f['payment_type_id'] === '' || Dict::exists('payment_type', (int) $f['payment_type_id']), 'Такого способа оплаты нет')
            ->check('description', mb_strlen($f['description']) <= 500, 'Не длиннее 500 символов');
        if (!$v->ok()) {
            $this->form($f, $v->errors(), 422);
        }
        $f['starts_at'] = $start->format('Y-m-d\\TH:i');
        $id = Applications::create((int) $u['id'], $f);
        Http::redirect('/applications', "Заявка № {$id} отправлена. Статус — «Новая».");
    }

    public function cancel(): never
    {
        $u = Auth::requireUser();
        Http::checkCsrf();
        $ok = Applications::cancel((int) ($_POST['id'] ?? 0), (int) $u['id']);
        Http::redirect('/applications', $ok ? 'Заявка отменена' : 'Отменить можно только новую заявку');
    }

    public function review(): never
    {
        $u = Auth::requireUser();
        Http::checkCsrf();
        $id = (int) ($_POST['application_id'] ?? 0);
        $err = Reviews::add((int) $u['id'], $id, (int) ($_POST['rating'] ?? 0), Http::post('body'));
        if ($err !== null) {
            Http::redirect('/applications#a' . $id, 'Ошибка: ' . $err);
        }
        Http::redirect('/applications#a' . $id, 'Спасибо! Отзыв опубликован.');
    }

    public function profile(): never
    {
        $u = Auth::requireUser();
        Http::render('profile', ['title' => 'Личный кабинет', 'stats' => Applications::stats((int) $u['id'])]);
    }

    private function form(array $old, array $errors, int $code = 200): never
    {
        Http::render('application_new', [
            'title' => 'Новая заявка',
            'rooms' => Dict::rooms(),
            'payments' => Dict::payments(),
            'old' => $old,
            'errors' => $errors,
        ], $code);
    }
}
