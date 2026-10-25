<?php
declare(strict_types=1);

namespace Konf\Repo;

use Konf\Db;

final class Reviews
{
    
    public static function add(int $userId, int $applicationId, int $rating, string $body): ?string
    {
        $a = Db::one('SELECT status_id FROM application WHERE id = ? AND user_id = ?', [$applicationId, $userId]);
        if ($a === null) {
            return 'Заявка не найдена';
        }
        if ((int) $a['status_id'] !== Applications::DONE) {
            return 'Отзыв можно оставить только после завершения мероприятия';
        }
        if (Db::one('SELECT 1 FROM review WHERE application_id = ?', [$applicationId]) !== null) {
            return 'Отзыв по этой заявке уже оставлен';
        }
        if ($rating < 1 || $rating > 5) {
            return 'Поставьте оценку от 1 до 5';
        }
        if (mb_strlen($body) < 10) {
            return 'Напишите отзыв — не короче 10 символов';
        }
        if (mb_strlen($body) > 1000) {
            return 'Отзыв — не длиннее 1000 символов';
        }
        Db::insert(
            'INSERT INTO review (application_id, rating, body, created_at) VALUES (?, ?, ?, ?)',
            [$applicationId, $rating, $body, date('Y-m-d H:i:s')]
        );
        return null;
    }
}
