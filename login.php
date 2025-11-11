<?php
require_once __DIR__ . '/config.php';
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? ($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');

    $mysqli = db();

    $loginEmail = filter_var($login, FILTER_VALIDATE_EMAIL) ? mb_strtolower($login) : '';
    $loginUser  = $login;

    $stmt = $mysqli->prepare(
        'SELECT id, email, username, password_hash, role 
         FROM users 
         WHERE email = ? OR username = ? 
         LIMIT 1'
    );
    $stmt->bind_param('ss', $loginEmail, $loginUser);
    $stmt->execute();
    $res  = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        $hash = trim((string)$user['password_hash']);

        $ok = password_verify($pass, $hash);

        if (!$ok && hash_equals($hash, $pass)) {
            $ok = true;
            $newHash = password_hash($pass, PASSWORD_DEFAULT);
            $upd = $mysqli->prepare('UPDATE users SET password_hash=? WHERE id=?');
            $upd->bind_param('si', $newHash, $user['id']);
            $upd->execute();
        }

        if ($ok) {
            $user['email'] = mb_strtolower($user['email']);

            $_SESSION['user'] = [
                'id'       => (int)$user['id'],
                'email'    => $user['email'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];
            header('Location: /dashboard.php');
            exit;
        }
    }

    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Вход — Therapy Buddy</title>
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
    <h2>Вход</h2>
    <?php if ($error): ?>
        <div class="alert"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label>Email или имя пользователя
            <input type="text" name="login" required />
        </label>
        <label>Пароль
            <input type="password" name="password" required />
        </label>
        <button class="btn" type="submit">Войти</button>
    </form>
    <p><a href="/register.php">Создать аккаунт</a></p>
</main>
</body>
</html>
