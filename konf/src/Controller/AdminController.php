<?php
declare(strict_types=1);

namespace Konf\Controller;

use Konf\Auth;
use Konf\Http;
use Konf\Repo\Applications;
use Konf\Repo\Dict;

final class AdminController
{
    public function index(): never
    {
        Auth::requireAdmin();
        $filters = [];
        foreach (['status', 'room', 'q', 'from', 'to'] as $k) {
            $filters[$k] = trim((string) ($_GET[$k] ?? ''));
        }
        Http::render('admin', [
            'title' => 'Панель администратора',
            'items' => Applications::search($filters),
            'filters' => $filters,
            'counters' => Applications::counters(),
            'statuses' => Dict::statuses(),
            'rooms' => Dict::rooms(),
        ]);
    }

    public function status(): never
    {
        Auth::requireAdmin();
        Http::checkCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $err = Applications::changeStatus($id, (int) ($_POST['status_id'] ?? 0));
        $back = '/admin' . (empty($_POST['back']) ? '' : '?' . preg_replace('/[^\w=&%.-]/u', '', (string) $_POST['back']));
        if ($err !== null) {
            http_response_code(400);
            Http::redirect($back, 'Ошибка: ' . $err);
        }
        Http::redirect($back . '#a' . $id, "Статус заявки № {$id} изменён");
    }
}
