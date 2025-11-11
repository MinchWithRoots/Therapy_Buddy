<?php
require_once __DIR__ . '/config.php';
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}
$mysqli = db();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($username === '') {
        $errors[] = 'Введите имя пользователя';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный email';
    }
    if (strlen($pass) < 6) {
        $errors[] = 'Пароль должен быть не меньше 6 символов';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'Такой email уже используется';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare('INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, "user")');
            $stmt->bind_param('sss', $username, $email, $hash);

            if ($stmt->execute()) {
                $success = 'Аккаунт успешно создан. Теперь вы можете <a href="/login.php">войти</a>.';
            } else {
                $errors[] = 'Ошибка при создании аккаунта: ' . $stmt->error;
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
    <main class="container auth">
        <h2>Регистрация</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php foreach ($errors as $err): ?><div><?php echo e($err); ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="card" style="border-left:4px solid #22c55e;"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Имя пользователя
                <input type="text" name="username" required value="<?php echo e($_POST['username'] ?? ''); ?>" />
            </label>
            <label>Email
                <input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>" />
            </label>
            <label>Пароль
                <input type="password" name="password" required />
            </label>
            <button class="btn" type="submit">Зарегистрироваться</button>
        </form>
        <p><a href="/login.php">Уже есть аккаунт? Войти</a></p>
    </main>
</body>
</html>
