<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$mysqli = db();

$pairId = (int)($_GET['pair_id'] ?? 0);
if ($pairId <= 0) { http_response_code(400); echo 'Bad request'; exit; }

$pairStmt = $mysqli->prepare('SELECT p.id, p.user1_id, p.user2_id, ua.username AS a_name, ub.username AS b_name FROM support_pairs p JOIN users ua ON ua.id=p.user1_id JOIN users ub ON ub.id=p.user2_id WHERE p.id=?');
$pairStmt->bind_param('i', $pairId);
$pairStmt->execute();
$pair = $pairStmt->get_result()->fetch_assoc();
if (!$pair) { http_response_code(404); echo 'Pair not found'; exit; }

$msgStmt = $mysqli->prepare('SELECT m.id, m.sender_id, u.username, m.message, m.sent_at FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.pair_id=? ORDER BY m.id ASC');
$msgStmt->bind_param('i', $pairId);
$msgStmt->execute();
$messages = $msgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Админ: чат пары #<?php echo (int)$pairId; ?> — Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<main class="container">
		<h2>Переписка пары #<?php echo (int)$pair['id']; ?> (<?php echo e($pair['a_name']); ?> ↔ <?php echo e($pair['b_name']); ?>)</h2>
		<p class="warning">Этическое предупреждение: доступ к переписке только при необходимости.</p>
		<div class="chat-log">
			<?php foreach ($messages as $m): ?>
				<div class="msg"><strong><?php echo e($m['username']); ?>:</strong> <?php echo nl2br(e($m['message'])); ?> <span class="muted"><?php echo e($m['sent_at']); ?></span></div>
			<?php endforeach; ?>
		</div>
		<p><a href="/admin.php">Назад</a></p>
	</main>
</body>
</html>

