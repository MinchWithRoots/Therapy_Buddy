<?php
require_once __DIR__ . '/config.php';
if (isLoggedIn()) {
    refreshUserFromDB();
}
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<header class="site-header">
		<div class="container">
			<div class="brand"><span class="logo">🫶</span> <span>Therapy Buddy</span></div>
			<nav>
				<a href="#how">Как это работает</a>
				<a href="#ethics">Этика</a>
				<a href="#faq">FAQ</a>
				<?php if ($user): ?>
					<a href="/dashboard.php">Личный кабинет</a>
					<?php if (($user['role'] ?? 'user') === 'admin'): ?>
						<a href="/admin.php">Админ</a>
					<?php endif; ?>
					<a href="/logout.php">Выйти</a>
				<?php else: ?>
					<a href="/login.php">Войти</a>
					<a class="btn" href="/register.php">Регистрация</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<section class="hero">
		<div class="container hero-inner">
			<h1>Поддержка рядом, когда она нужна</h1>
			<p>Платформа взаимной поддержки с ручной модерацией: заявка → одобрение → назначение пары → приватный чат.</p>
			<?php if (!$user): ?>
				<p class="cta"><a class="btn btn-lg" href="/register.php">Присоединиться</a></p>
			<?php else: ?>
				<p class="cta"><a class="btn btn-lg" href="/dashboard.php">Перейти в кабинет</a></p>
			<?php endif; ?>
			<div class="badges">
				<span>👤 Ручное назначение пар</span>
				<span>🔒 Приватный чат</span>
				<span>🛡️ Модерация</span>
			</div>
		</div>
	</section>

	<section id="how" class="section container">
		<h2>Как это работает</h2>
		<div class="steps">
			<div class="step"><span class="num">1</span><h3>Регистрация</h3><p>Создайте аккаунт и расскажите о себе.</p></div>
			<div class="step"><span class="num">2</span><h3>Заявка</h3><p>Подайте заявку на участие. Админ её рассмотрит.</p></div>
			<div class="step"><span class="num">3</span><h3>Назначение пары</h3><p>Админ вручную формирует пары — без авто-матчинга.</p></div>
			<div class="step"><span class="num">4</span><h3>Чат</h3><p>Общайтесь в безопасном приватном чате 1:1.</p></div>
		</div>
	</section>

	<section id="ethics" class="section container">
		<h2>Этика и безопасность</h2>
		<ul class="list">
			<li><strong>Конфиденциальность:</strong> переписка видна только участникам. Админ имеет доступ лишь при необходимости.</li>
			<li><strong>Уважение границ:</strong> договоритесь о комфортных темпах и темах.</li>
			<li><strong>Это не терапия:</strong> платформа для peer support, не замена профессиональной помощи.</li>
		</ul>
	</section>

	<section id="faq" class="section container">
		<h2>Частые вопросы</h2>
		<div class="faq">
			<div class="q"><h3>Автоматического подбора нет?</h3><p>Нет. Пары назначает администратор вручную.</p></div>
			<div class="q"><h3>Сколько ждать одобрения?</h3><p>Обычно 1–3 дня. Статус — в личном кабинете.</p></div>
			<div class="q"><h3>Можно сменить партнёра?</h3><p>Напишите администратору — он поможет.</p></div>
		</div>
	</section>

	<footer class="footer">
		<div class="container">
			<p class="muted">© <?php echo date('Y'); ?> Therapy Buddy</p>
		</div>
	</footer>
</body>
</html>

