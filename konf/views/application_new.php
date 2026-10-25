<?php $err = static fn (string $k) => $errors[$k] ?? ''; ?>
<section class="panel panel--mid">
    <h1>Новая заявка</h1>
    <ol class="steps" aria-hidden="true"><li>Помещение</li><li>Мероприятие</li><li>Оплата</li></ol>
    <form method="post" action="/applications" class="form" novalidate data-validate>
        <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">

        <fieldset class="field<?= $err('room_id') ? ' has-error' : '' ?>">
            <legend>1. Помещение</legend>
            <div class="pick">
                <?php foreach ($rooms as $r): ?>
                    <label class="pick__item">
                        <input type="radio" name="room_id" value="<?= (int) $r['id'] ?>" data-rule="required" <?= ($old['room_id'] ?? '') == $r['id'] ? 'checked' : '' ?>>
                        <img src="/assets/img/<?= e($r['photo']) ?>.jpg" alt="" width="560" height="420" loading="lazy">
                        <span><b><?= e($r['name']) ?></b><small>до <?= (int) $r['capacity'] ?> мест</small></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <small class="error" data-error="room_id"><?= e($err('room_id')) ?></small>
        </fieldset>

        <fieldset>
            <legend>2. Мероприятие</legend>
            <div class="field<?= $err('title') ? ' has-error' : '' ?>">
                <label for="title">Название мероприятия</label>
                <input id="title" name="title" maxlength="150" required data-rule="required" value="<?= e($old['title'] ?? '') ?>" placeholder="Например, «Цифровая экономика — 2027»">
                <small class="error" data-error="title"><?= e($err('title')) ?></small>
            </div>
            <div class="field<?= $err('starts_at') ? ' has-error' : '' ?>">
                <label for="starts_at">Дата и время начала конференции</label>
                <input id="starts_at" name="starts_at" type="text" placeholder="ДД.ММ.ГГГГ ЧЧ:ММ" maxlength="16" autocomplete="off" required data-rule="future" value="<?= e($old['starts_at'] ?? '') ?>">
                <small class="hint">Формат: ДД.ММ.ГГГГ ЧЧ:ММ. Только будущие даты</small>
                <small class="error" data-error="starts_at"><?= e($err('starts_at')) ?></small>
            </div>
            <div class="field<?= $err('description') ? ' has-error' : '' ?>">
                <label for="description">Описание <span class="muted">(необязательно)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="500"><?= e($old['description'] ?? '') ?></textarea>
                <small class="error" data-error="description"><?= e($err('description')) ?></small>
            </div>
        </fieldset>

        <fieldset class="field<?= $err('payment_type_id') ? ' has-error' : '' ?>">
            <legend>3. Способ оплаты</legend>
            <label for="payment_type_id" class="sr">Способ оплаты</label>
            <select id="payment_type_id" name="payment_type_id" required data-rule="required">
                <option value="">— выберите —</option>
                <?php foreach ($payments as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= ($old['payment_type_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small class="error" data-error="payment_type_id"><?= e($err('payment_type_id')) ?></small>
        </fieldset>

        <button class="btn btn--wide" type="submit">Отправить</button>
    </form>
</section>
