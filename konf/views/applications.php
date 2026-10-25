<?php
$badge = [1 => 'new', 2 => 'assigned', 3 => 'done'];
$fmt = static fn (string $d) => date('d.m.Y H:i', strtotime($d));
?>
<div class="head">
    <div>
        <h1>Мои заявки</h1>
        <p class="muted"><?= e($user['full_name']) ?></p>
    </div>
    <a class="btn" href="/applications/new">Новая заявка</a>
</div>

<?php if (!$items): ?>
    <div class="panel empty">
        <p>Заявок пока нет.</p>
        <a class="btn" href="/applications/new">Подать первую заявку</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr><th>№</th><th>Мероприятие</th><th>Помещение</th><th>Начало</th><th>Оплата</th><th>Статус</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($items as $a): ?>
                <tr id="a<?= (int) $a['id'] ?>">
                    <td data-label="№"><?= (int) $a['id'] ?></td>
                    <td data-label="Мероприятие"><b><?= e($a['title']) ?></b><?php if ($a['description'] !== ''): ?><span class="desc"><?= e($a['description']) ?></span><?php endif; ?><small class="muted">подана <?= $fmt($a['created_at']) ?></small></td>
                    <td data-label="Помещение"><?= e($a['room']) ?></td>
                    <td data-label="Начало"><?= $fmt($a['starts_at']) ?></td>
                    <td data-label="Оплата"><?= e($a['payment']) ?></td>
                    <td data-label="Статус"><span class="badge badge--<?= $badge[$a['status_id']] ?>"><?= e($a['status']) ?></span></td>
                    <td class="cell-actions">
                        <?php if ((int) $a['status_id'] === 1): ?>
                            <form method="post" action="/applications/cancel" data-confirm>
                                <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <button class="btn-link btn-link--danger" type="submit">Отменить</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ((int) $a['status_id'] === 3): ?>
                    <tr class="review-row">
                        <td colspan="7">
                            <?php if ($a['rating']): ?>
                                <div class="review">
                                    <span class="stars" aria-label="Оценка <?= (int) $a['rating'] ?> из 5"><?= str_repeat('★', (int) $a['rating']) . str_repeat('☆', 5 - (int) $a['rating']) ?></span>
                                    <p><?= e($a['review']) ?></p>
                                </div>
                            <?php else: ?>
                                <form method="post" action="/reviews" class="review-form" data-validate novalidate>
                                    <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">
                                    <input type="hidden" name="application_id" value="<?= (int) $a['id'] ?>">
                                    <span class="review-form__label">Мероприятие завершено — оцените его:</span>
                                    <div class="rate" role="radiogroup" aria-label="Оценка">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="r<?= $a['id'] . '-' . $i ?>" name="rating" value="<?= $i ?>" data-rule="required">
                                            <label for="r<?= $a['id'] . '-' . $i ?>" title="<?= $i ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <textarea name="body" rows="2" placeholder="Что понравилось, что улучшить (от 10 до 1000 символов)" maxlength="1000" data-rule="min10" aria-label="Текст отзыва"></textarea>
                                    <button class="btn btn--small" type="submit">Отправить отзыв</button>
                                    <small class="error" data-error="rating"></small>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
