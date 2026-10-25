<?php
$badge = [1 => 'new', 2 => 'assigned', 3 => 'done'];
$fmt = static fn (string $d) => date('d.m.Y H:i', strtotime($d));
$back = http_build_query(array_filter($filters, static fn ($v) => $v !== ''));
$token = Konf\Http::csrfToken();
?>
<div class="head">
    <h1>Панель администратора</h1>
    <p class="muted">Найдено заявок: <b><?= count($items) ?></b></p>
</div>

<div class="counters">
    <?php foreach ($statuses as $s): ?>
        <a class="counter counter--<?= $badge[$s['id']] ?>" href="/admin?status=<?= (int) $s['id'] ?>">
            <b><?= $counters[$s['id']] ?></b><span><?= e($s['name']) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<form class="filters panel" method="get" action="/admin" data-autosubmit>
    <label>Статус
        <select name="status">
            <option value="">Все</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= $filters['status'] == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Помещение
        <select name="room">
            <option value="">Все</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?= (int) $r['id'] ?>" <?= $filters['room'] == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Начало с <input type="text" name="from" placeholder="ДД.ММ.ГГГГ" value="<?= e($filters['from']) ?>"></label>
    <label>по <input type="text" name="to" placeholder="ДД.ММ.ГГГГ" value="<?= e($filters['to']) ?>"></label>
    <label class="filters__q">Поиск <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Логин, ФИО или название"></label>
    <div class="filters__btns">
        <button class="btn btn--small" type="submit">Применить</button>
        <a class="btn-link" href="/admin">Сбросить</a>
    </div>
</form>

<?php if (!$items): ?>
    <div class="panel empty"><p>По фильтру ничего не найдено.</p></div>
<?php else: ?>
<div class="table-wrap">
    <table class="table table--admin">
        <thead>
        <tr><th>№</th><th>Участник</th><th>Мероприятие</th><th>Помещение / начало</th><th>Оплата</th><th>Статус</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $a): $next = Konf\Repo\Applications::NEXT[(int) $a['status_id']]; ?>
            <tr id="a<?= (int) $a['id'] ?>">
                <td data-label="№"><?= (int) $a['id'] ?></td>
                <td data-label="Участник"><b><?= e($a['full_name']) ?></b><small class="muted"><?= e($a['login']) ?> · <?= e($a['phone']) ?><br><?= e($a['email']) ?></small></td>
                <td data-label="Мероприятие"><?= e($a['title']) ?>
                    <?php if ($a['description'] !== ''): ?><span class="desc"><?= e($a['description']) ?></span><?php endif; ?>
                    <?php if ($a['rating']): ?>
                        <div class="review review--compact"><span class="stars"><?= str_repeat('★', (int) $a['rating']) ?></span> <?= e($a['review']) ?></div>
                    <?php endif; ?>
                </td>
                <td data-label="Помещение"><?= e($a['room']) ?><small class="muted"><?= $fmt($a['starts_at']) ?></small></td>
                <td data-label="Оплата"><?= e($a['payment']) ?></td>
                <td data-label="Статус">
                    <span class="badge badge--<?= $badge[$a['status_id']] ?>"><?= e($a['status']) ?></span>
                    <?php if ($next): ?>
                        <form method="post" action="/admin/status" class="status-form">
                            <input type="hidden" name="_token" value="<?= e($token) ?>">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <input type="hidden" name="back" value="<?= e($back) ?>">
                            <select name="status_id" aria-label="Новый статус">
                                <?php foreach ($statuses as $s): if (in_array((int) $s['id'], $next, true)): ?>
                                    <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                                <?php endif; endforeach; ?>
                            </select>
                            <button class="btn btn--small" type="submit">Сменить</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
