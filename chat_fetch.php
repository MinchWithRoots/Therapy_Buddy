<?php
require_once __DIR__ . '/config.php';
requireLogin();
$mysqli = db();
$user = currentUser();

$pairId = (int)($_GET['pair_id'] ?? 0);
$afterId = (int)($_GET['after_id'] ?? 0);
if ($pairId <= 0) { http_response_code(400); echo json_encode(['error'=>'bad_request']); exit; }

// Ensure user belongs to the pair
$auth = $mysqli->prepare('SELECT id FROM support_pairs WHERE id=? AND is_active=1 AND (user1_id=? OR user2_id=?)');
$auth->bind_param('iii', $pairId, $user['id'], $user['id']);
$auth->execute();
if (!$auth->get_result()->fetch_assoc()) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }

header('Content-Type: application/json; charset=utf-8');
$stmt = $mysqli->prepare('SELECT m.id, m.sender_id, u.username, m.message, m.sent_at FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.pair_id=? AND m.id>? ORDER BY m.id ASC');
$stmt->bind_param('ii', $pairId, $afterId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['messages'=>$rows]);
?>

