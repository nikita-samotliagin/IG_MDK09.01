<?php $fmt = static fn (string $d) => date('d.m.Y H:i', strtotime($d)); ?>
<h1>Личный кабинет</h1>
<div class="profile">
    <section class="panel profile__card">
        <span class="avatar avatar--big"><?= e(mb_substr($user['full_name'], 0, 1)) ?></span>
        <h2><?= e($user['full_name']) ?></h2>
        <dl class="dl">
            <dt>Логин</dt><dd><?= e($user['login']) ?></dd>
            <dt>Телефон</dt><dd><?= e($user['phone']) ?></dd>
            <dt>E-mail</dt><dd><?= e($user['email']) ?></dd>
            <dt>С нами с</dt><dd><?= date('d.m.Y', strtotime($user['created_at'])) ?></dd>
        </dl>
    </section>

    <section class="profile__side">
        <div class="counters">
            <div class="counter"><b><?= $stats['total'] ?></b><span>Всего заявок</span></div>
            <div class="counter counter--new"><b><?= $stats[1] ?></b><span>Новые</span></div>
            <div class="counter counter--assigned"><b><?= $stats[2] ?></b><span>Назначены</span></div>
            <div class="counter counter--done"><b><?= $stats[3] ?></b><span>Завершены</span></div>
        </div>
        <div class="panel">
            <h2>Ближайшее мероприятие</h2>
            <?php if ($stats['next']): ?>
                <p class="next"><b><?= e($stats['next']['title']) ?></b><br><?= e($stats['next']['room']) ?>, <?= $fmt($stats['next']['starts_at']) ?></p>
            <?php else: ?>
                <p class="muted">Назначенных мероприятий пока нет.</p>
            <?php endif; ?>
            <p class="muted">Оставлено отзывов: <?= $stats['reviews'] ?></p>
            <div class="row-gap">
                <a class="btn" href="/applications/new">Новая заявка</a>
                <a class="btn btn--ghost" href="/applications">Мои заявки</a>
            </div>
        </div>
    </section>
</div>
