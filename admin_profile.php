<?php
require_once __DIR__ . '/config.php';
requireAdmin();
$mysqli = db();
$admin = currentUser();

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$pass = $_POST['password'] ?? '';

	if ($username === '') { $errors[] = 'Введите имя пользователя'; }
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Неверный email'; }

	if (!$errors) {
		$chk = $mysqli->prepare('SELECT id FROM users WHERE email=? AND id<>?');
		$chk->bind_param('si', $email, $admin['id']);
		$chk->execute();
		$chk->store_result();
		if ($chk->num_rows > 0) {
			$errors[] = 'Такой email уже используется';
		} else {
			if ($pass !== '') {
				$hash = password_hash($pass, PASSWORD_DEFAULT);
				$upd = $mysqli->prepare('UPDATE users SET username=?, email=?, password_hash=? WHERE id=?');
				$upd->bind_param('sssi', $username, $email, $hash, $admin['id']);
			} else {
				$upd = $mysqli->prepare('UPDATE users SET username=?, email=? WHERE id=?');
				$upd->bind_param('ssi', $username, $email, $admin['id']);
			}
			$upd->execute();
			refreshUserFromDB();
			$success = 'Данные администратора обновлены';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Профиль администратора — Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<header class="site-header">
		<div class="container">
			<div class="brand"><span class="logo">🫶</span> <span>Therapy Buddy</span></div>
			<nav>
				<a href="/admin.php">Админ-панель</a>
				<a href="/">Главная</a>
				<a href="/logout.php">Выйти</a>
			</nav>
		</div>
	</header>
	<main class="container">
		<h2 style="margin-top: 30px; color: var(--primary);">Профиль администратора 👤</h2>
		<?php if ($success): ?><div class="card" style="border-left:4px solid #22c55e;"><?php echo e($success); ?></div><?php endif; ?>
		<?php if (!empty($errors)): ?>
			<div class="alert">
				<?php foreach ($errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
			</div>
		<?php endif; ?>
		<section class="card">
			<form method="post" class="auth" autocomplete="off">
				<label>Имя пользователя
					<input type="text" name="username" value="<?php echo e($admin['username']); ?>" required />
				</label>
				<label>Email
					<input type="email" name="email" value="<?php echo e($admin['email']); ?>" required />
				</label>
				<label>Новый пароль (необязательно)
					<input type="password" name="password" placeholder="Оставьте пустым, чтобы не менять" />
				</label>
				<button class="btn" type="submit">Сохранить</button>
			</form>
		</section>
	</main>
</body>
</html>

