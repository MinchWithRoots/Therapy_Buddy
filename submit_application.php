<?php
require_once __DIR__ . '/config.php';
requireLogin();
$mysqli = db();
$user = currentUser();

// Prevent duplicate active applications
$check = $mysqli->prepare('SELECT id FROM support_requests WHERE user_id=?');
$check->bind_param('i', $user['id']);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
	header('Location: /dashboard.php');
	exit;
}

$motivation = trim($_POST['motivation'] ?? '');
$stmt = $mysqli->prepare('INSERT INTO support_requests (user_id, description) VALUES (?, ?)');
$stmt->bind_param('is', $user['id'], $motivation);
$stmt->execute();

header('Location: /dashboard.php');
exit;
?>

