<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$mysqli = db();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['app_id'])) {
		$appId = (int)$_POST['app_id'];
		if ($_POST['action'] === 'approve') {
            $stmt = $mysqli->prepare("UPDATE support_requests SET status='approved' WHERE id=?");
			$stmt->bind_param('i', $appId);
			$stmt->execute();
		}
		if ($_POST['action'] === 'decline') {
            $stmt = $mysqli->prepare("UPDATE support_requests SET status='rejected' WHERE id=?");
			$stmt->bind_param('i', $appId);
			$stmt->execute();
		}
		header('Location: /admin.php');
		exit;
	}
	if (isset($_POST['pair_a'], $_POST['pair_b'])) {
		$a = (int)$_POST['pair_a'];
		$b = (int)$_POST['pair_b'];
        if ($a && $b && $a !== $b) {
			list($u1, $u2) = $a < $b ? [$a, $b] : [$b, $a];
            $ins = $mysqli->prepare('INSERT IGNORE INTO support_pairs (user1_id, user2_id, is_active) VALUES (?,?,1)');
			$ins->bind_param('ii', $u1, $u2);
			$ins->execute();
		}
		header('Location: /admin.php');
		exit;
	}
    if (isset($_POST['role_user_id'], $_POST['role'])) {
        $roleUserId = (int)$_POST['role_user_id'];
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

        $stmt = $mysqli->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->bind_param('si', $role, $roleUserId);
        $stmt->execute();

        if ($roleUserId === (int)$user['id']) {
            refreshUserFromDB();
        }

        header('Location: /admin.php');
        exit;
    }
}

$apps = $mysqli->query("SELECT a.id, a.user_id, a.status, a.description, u.username, u.email, a.created_at FROM support_requests a JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$eligible = $mysqli->query("SELECT u.id, u.username, u.email\nFROM users u\nJOIN support_requests a ON a.user_id=u.id AND a.status='approved'\nORDER BY u.username")->fetch_all(MYSQLI_ASSOC);

$pairs = $mysqli->query("SELECT p.id, p.user1_id, p.user2_id, p.is_active, ua.username AS a_name, ub.username AS b_name, p.created_at FROM support_pairs p JOIN users ua ON ua.id=p.user1_id JOIN users ub ON ub.id=p.user2_id ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$allUsers = $mysqli->query("SELECT id, username, email, role FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Админ — Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
    <header class="site-header">
		<div class="container">
			<div class="brand"><span class="logo">🫶</span> <span>Therapy Buddy</span></div>
			<nav>
				<a href="/">Главная</a>
				<a href="/admin_profile.php">Профиль админа</a>
				<a href="/logout.php">Выйти</a>
			</nav>
		</div>
    </header>
	<main class="container">
		<h2 style="margin-top: 30px; color: var(--primary);">Админ-панель 🛠️</h2>
		<section class="card">
			<h3>Заявки</h3>
			<div class="table">
                <div class="row head"><div>Пользователь</div><div>Email</div><div>Статус</div><div>Дата</div><div>Действия</div></div>
				<?php foreach ($apps as $a): ?>
					<div class="row">
                        <div><?php echo e($a['username']); ?></div>
						<div><?php echo e($a['email']); ?></div>
						<div><span class="status status-<?php echo e($a['status']); ?>"><?php echo e($a['status']); ?></span></div>
						<div class="muted"><?php echo e($a['created_at']); ?></div>
						<div>
							<form method="post" style="display:inline">
								<input type="hidden" name="app_id" value="<?php echo (int)$a['id']; ?>" />
								<button class="btn" name="action" value="approve" <?php echo $a['status']!=='pending'?'disabled':''; ?>>Подтвердить</button>
							</form>
							<form method="post" style="display:inline">
								<input type="hidden" name="app_id" value="<?php echo (int)$a['id']; ?>" />
								<button class="btn btn-secondary" name="action" value="decline" <?php echo $a['status']!=='pending'?'disabled':''; ?>>Отклонить</button>
							</form>
						</div>
					</div>
                    <?php if ($a['description']): ?>
                        <div class="row note"><div colspan="5">Комментарий: <?php echo nl2br(e($a['description'])); ?></div></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="card">
			<h3>Назначить пару (вручную)</h3>
			<form method="post" class="pair-form">
				<select name="pair_a" required>
					<option value="">Выберите участника A</option>
                    <?php foreach ($eligible as $u): ?>
                        <option value="<?php echo (int)$u['id']; ?>"><?php echo e($u['username']); ?> (<?php echo e($u['email']); ?>)</option>
					<?php endforeach; ?>
				</select>
				<span>↔</span>
				<select name="pair_b" required>
					<option value="">Выберите участника B</option>
                    <?php foreach ($eligible as $u): ?>
                        <option value="<?php echo (int)$u['id']; ?>"><?php echo e($u['username']); ?> (<?php echo e($u['email']); ?>)</option>
					<?php endforeach; ?>
				</select>
				<button class="btn" type="submit">Назначить</button>
			</form>
			<p class="muted">Назначение пар происходит вручную администратором. Автоматического подбора нет.</p>
		</section>

		<section class="card">
			<h3>Пары</h3>
			<div class="table">
                <div class="row head"><div>ID</div><div>Участник A</div><div>Участник B</div><div>Статус</div><div>Чат</div></div>
				<?php foreach ($pairs as $p): ?>
					<div class="row">
						<div><?php echo (int)$p['id']; ?></div>
						<div><?php echo e($p['a_name']); ?></div>
						<div><?php echo e($p['b_name']); ?></div>
                        <div><span class="status status-<?php echo $p['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $p['is_active'] ? 'активна' : 'закрыта'; ?></span></div>
						<div><a href="/admin_chat.php?pair_id=<?php echo (int)$p['id']; ?>" class="btn" style="padding: 8px 16px; font-size: 0.9rem;">Открыть</a></div>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="warning">Внимание: просмотр переписки допустим только при крайней необходимости и с соблюдением этических норм.</p>
		</section>

		<section class="card">
			<h3>Управление ролями</h3>
			<p class="muted">Назначайте или снимайте права администратора для пользователей платформы.</p>
			<div class="table">
				<div class="row head"><div>Пользователь</div><div>Email</div><div>Текущая роль</div><div>Изменить роль</div></div>
				<?php foreach ($allUsers as $account): ?>
					<div class="row">
						<div><?php echo e($account['username'] ?: '—'); ?></div>
						<div><?php echo e($account['email']); ?></div>
						<div><span class="status status-<?php echo $account['role'] === 'admin' ? 'approved' : 'inactive'; ?>"><?php echo e($account['role']); ?></span></div>
						<div>
							<form method="post" class="pair-form" style="gap: 10px;">
								<input type="hidden" name="role_user_id" value="<?php echo (int)$account['id']; ?>" />
								<select name="role" required>
									<option value="user" <?php echo $account['role'] === 'user' ? 'selected' : ''; ?>>Пользователь</option>
									<option value="admin" <?php echo $account['role'] === 'admin' ? 'selected' : ''; ?>>Администратор</option>
								</select>
								<button class="btn btn-secondary" type="submit">Сохранить</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</main>
</body>
</html>

