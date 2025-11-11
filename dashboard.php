<?php
require_once __DIR__ . '/config.php';
requireLogin();
$mysqli = db();
$user = currentUser();

$appStmt = $mysqli->prepare('SELECT id, status, description, created_at FROM support_requests WHERE user_id=? ORDER BY id DESC LIMIT 1');
$appStmt->bind_param('i', $user['id']);
$appStmt->execute();
$app = $appStmt->get_result()->fetch_assoc();

$pair = null;
$pairStmt = $mysqli->prepare('SELECT id, user1_id, user2_id, is_active FROM support_pairs WHERE is_active=1 AND (user1_id=? OR user2_id=?) LIMIT 1');
$pairStmt->bind_param('ii', $user['id'], $user['id']);
$pairStmt->execute();
$pair = $pairStmt->get_result()->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Личный кабинет — Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<header class="site-header">
		<div class="container">
			<div class="brand"><span class="logo">🫶</span> <span>Therapy Buddy</span></div>
			<nav>
				<a href="/">Главная</a>
				<a href="/logout.php">Выйти</a>
			</nav>
		</div>
	</header>
	<main class="container">
		<h2 style="margin-top: 30px; color: var(--primary);">Здравствуйте, <?php echo e($user['username'] ?? $user['email']); ?>! 👋</h2>
		<section class="card">
			<h3>Заявка на участие</h3>
			<?php if ($app): ?>
				<p>Статус: <span class="status status-<?php echo e($app['status']); ?>"><?php echo e($app['status']); ?></span></p>
				<p class="muted">Подана: <?php echo e($app['created_at']); ?></p>
				<?php if ($app['description']): ?><p>Комментарий: <?php echo nl2br(e($app['description'])); ?></p><?php endif; ?>
			<?php else: ?>
				<form method="post" action="/submit_application.php">
					<label>Почему вы хотите участвовать?
						<textarea name="motivation" rows="4" placeholder="Коротко о мотивации (необязательно)"></textarea>
					</label>
					<button class="btn" type="submit">Отправить заявку</button>
				</form>
			<?php endif; ?>
		</section>

		<section class="card">
			<h3>Пара и чат</h3>
			<?php if ($pair): ?>
				<p>Вам назначен партнёр по взаимной поддержке. Откройте чат ниже.</p>
				<div id="chat" data-pair-id="<?php echo (int)$pair['id']; ?>">
					<div id="chat-log" class="chat-log"></div>
					<form id="chat-form">
						<input type="text" id="chat-input" placeholder="Напишите сообщение" autocomplete="off" />
						<button class="btn" type="submit">Отправить</button>
					</form>
				</div>
				<script src="/assets/chat.js"></script>
			<?php else: ?>
				<p>Пара пока не назначена. Ожидайте решения администратора.</p>
			<?php endif; ?>
		</section>
	</main>
</body>
</html>

