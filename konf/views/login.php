<section class="panel panel--narrow">
    <h1>Авторизация</h1>
    <?php if ($alert): ?>
        <div class="notice notice--error" role="alert"><?= e($alert) ?></div>
    <?php endif; ?>
    <form method="post" action="/login" class="form" novalidate data-validate>
        <input type="hidden" name="_token" value="<?= e(Konf\Http::csrfToken()) ?>">
        <div class="field<?= isset($errors['login']) ? ' has-error' : '' ?>">
            <label for="login">Логин</label>
            <input id="login" name="login" autocomplete="username" required value="<?= e($old['login'] ?? '') ?>" data-rule="required" aria-describedby="login-err">
            <small class="error" id="login-err" data-error="login"><?= e($errors['login'] ?? '') ?></small>
        </div>
        <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
            <label for="password">Пароль</label>
            <div class="field__control">
                <input id="password" name="password" type="password" autocomplete="current-password" required data-rule="required" aria-describedby="password-err">
                <button type="button" class="eye" data-eye="password" aria-label="Показать пароль">Показать</button>
            </div>
            <small class="error" id="password-err" data-error="password"><?= e($errors['password'] ?? '') ?></small>
        </div>
        <button class="btn btn--wide" type="submit">Войти</button>
        <p class="switch">Еще не зарегистрированы? <a href="/register">Регистрация</a></p>
    </form>
</section>
