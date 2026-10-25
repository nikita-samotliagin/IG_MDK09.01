<?php
$fields = [
    ['login', 'Логин', 'text', 'Латиница и цифры, не менее 6 символов', 'username', 'data-rule="login"'],
    ['password', 'Пароль', 'password', 'Не менее 8 символов', 'new-password', 'data-rule="password"'],
    ['full_name', 'ФИО', 'text', 'Кириллица и пробелы', 'name', 'data-rule="fio"'],
    ['phone', 'Телефон', 'tel', 'Формат 8(XXX)XXX-XX-XX', 'tel', 'data-rule="phone" data-mask="phone" placeholder="8(___)___-__-__"'],
    ['email', 'Электронная почта', 'email', 'Например, name@mail.ru', 'email', 'data-rule="email"'],
];
?>
<section class="panel panel--narrow">
    <h1>Регистрация</h1>
    <p class="muted">Все поля обязательны.</p>
    <form method="post" action="/register" class="form" novalidate data-validate>
        <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">
        <?php foreach ($fields as [$name, $label, $type, $hint, $ac, $attrs]): ?>
            <?php $err = $errors[$name] ?? null; ?>
            <div class="field<?= $err ? ' has-error' : '' ?>">
                <label for="<?= $name ?>"><?= $label ?></label>
                <div class="field__control">
                    <input id="<?= $name ?>" name="<?= $name ?>" type="<?= $type ?>" autocomplete="<?= $ac ?>" required
                           value="<?= e($old[$name] ?? '') ?>" aria-describedby="<?= $name ?>-hint <?= $name ?>-err" <?= $attrs ?>>
                    <?php if ($type === 'password'): ?>
                        <button type="button" class="eye" data-eye="<?= $name ?>" aria-label="Показать пароль">Показать</button>
                    <?php endif; ?>
                </div>
                <small class="hint" id="<?= $name ?>-hint"><?= $hint ?></small>
                <small class="error" id="<?= $name ?>-err" data-error="<?= $name ?>"><?= e($err ?? '') ?></small>
            </div>
        <?php endforeach; ?>
        <button class="btn btn--wide" type="submit">Зарегистрироваться</button>
        <p class="switch">Уже зарегистрированы? <a href="/login">Авторизация</a></p>
    </form>
</section>
