<?php
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$name = trim($_POST['username'] ?? '');
	$pass = $_POST['password'] ?? '';
	$pass2 = $_POST['password2'] ?? '';
 
	$errors = [];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Неверный email'; }
	if ($name === '') { $errors[] = 'Введите имя пользователя'; }
	if (strlen($pass) < 6) { $errors[] = 'Минимум 6 символов в пароле'; }
	if ($pass !== $pass2) { $errors[] = 'Пароли не совпадают'; }

	if (!$errors) {
		$mysqli = db();
		$exists = $mysqli->prepare('SELECT id FROM users WHERE email=?');
		if (!$exists) {
			$errors[] = 'Ошибка базы данных. Пожалуйста, сначала откройте /setup.php.';
		} else {
		$exists->bind_param('s', $email);
		$exists->execute();
		$exists->store_result();
		if ($exists->num_rows > 0) {
			$errors[] = 'Пользователь уже существует';
		} else {
			$hash = password_hash($pass, PASSWORD_DEFAULT);
			$ins = $mysqli->prepare('INSERT INTO users (username, email, password_hash) VALUES (?,?,?)');
			if (!$ins) {
				$errors[] = 'Ошибка базы данных при создании пользователя. Откройте /setup.php.';
			} else {
				$ins->bind_param('sss', $name, $email, $hash);
				$ins->execute();
				$userId = $ins->insert_id;
				
				// Получаем данные пользователя из БД, чтобы получить актуальную роль
				$userStmt = $mysqli->prepare('SELECT id, email, username, role FROM users WHERE id = ? LIMIT 1');
				$userStmt->bind_param('i', $userId);
				$userStmt->execute();
				$userResult = $userStmt->get_result();
				$userData = $userResult->fetch_assoc();
				
				if ($userData) {
					$_SESSION['user'] = [
						'id' => (int)$userData['id'],
						'email' => $userData['email'],
						'username' => $userData['username'],
						'role' => $userData['role'] ?? 'user'
					];
				}
				header('Location: /dashboard.php');
				exit;
			}
		}
		}
	}
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Регистрация — Therapy Buddy</title>
	<link rel="stylesheet" href="/assets/style.css?v=<?php echo ASSET_VERSION; ?>" />
</head>
<body>
	<header class="site-header">
		<div class="container">
			<div class="brand"><span class="logo">🫶</span> <span>Therapy Buddy</span></div>
			<nav>
				<a href="/">Главная</a>
			</nav>
		</div>
	</header>
	<main class="container auth">
		<h2>Регистрация</h2>
		<?php if (!empty($errors)): ?>
			<div class="alert">
				<?php foreach ($errors as $err): ?>
					<div><?php echo e($err); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<form method="post">
			<label>Email<input type="email" name="email" required /></label>
			<label>Имя пользователя<input type="text" name="username" required /></label>
			<label>Пароль<input type="password" name="password" required /></label>
			<label>Повторите пароль<input type="password" name="password2" required /></label>
			<button class="btn" type="submit">Создать аккаунт</button>
		</form>
		<p><a href="/login.php">У меня уже есть аккаунт</a></p>
	</main>
</body>
</html>

