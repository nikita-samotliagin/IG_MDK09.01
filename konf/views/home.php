<section class="intro">
    <picture>
        <source srcset="/assets/img/hero.webp" type="image/webp">
        <img src="/assets/img/hero.jpg" alt="" width="1200" height="500">
    </picture>
    <div class="intro__text">
        <p class="eyebrow">Всероссийские конференции</p>
        <h1>Помещения для вашей конференции</h1>
        <p>Подайте заявку онлайн: выберите зал, дату и способ оплаты. Статус заявки видно в личном кабинете.</p>
        <?php if (!$user): ?>
            <div class="row-gap">
                <a class="btn" href="/register">Зарегистрироваться</a>
                <a class="btn btn--ghost-light" href="/login">Войти</a>
            </div>
        <?php elseif ($user['role'] === 'user'): ?>
            <a class="btn" href="/applications/new">Подать заявку</a>
        <?php else: ?>
            <a class="btn" href="/admin">К заявкам</a>
        <?php endif; ?>
    </div>
</section>

<h2 class="section-title">Помещения</h2>
<div class="rooms">
    <?php foreach ($rooms as $r): ?>
        <article class="room">
            <picture>
                <source srcset="/assets/img/<?= e($r['photo']) ?>.webp" type="image/webp">
                <img src="/assets/img/<?= e($r['photo']) ?>.jpg" alt="<?= e($r['name']) ?>" width="560" height="420" loading="lazy">
            </picture>
            <div class="room__body">
                <h3><?= e($r['name']) ?></h3>
                <p class="muted"><?= e($r['place']) ?> · до <?= (int) $r['capacity'] ?> мест</p>
                <p><?= e($r['description']) ?></p>
            </div>
        </article>
    <?php endforeach; ?>
</div>
