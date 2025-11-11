<?php
require_once __DIR__ . '/config.php';
requireLogin();
$mysqli = db();
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$pairId = (int)($_POST['pair_id'] ?? 0);
$text = trim($_POST['message'] ?? '');
if ($pairId <= 0 || $text === '') { http_response_code(400); echo 'Bad request'; exit; }

// Ensure user belongs to the pair
$auth = $mysqli->prepare('SELECT id FROM support_pairs WHERE id=? AND is_active=1 AND (user1_id=? OR user2_id=?)');
$auth->bind_param('iii', $pairId, $user['id'], $user['id']);
$auth->execute();
if (!$auth->get_result()->fetch_assoc()) { http_response_code(403); echo 'Forbidden'; exit; }

$stmt = $mysqli->prepare('INSERT INTO messages (pair_id, sender_id, message) VALUES (?,?,?)');
$stmt->bind_param('iis', $pairId, $user['id'], $text);
$stmt->execute();

header('Content-Type: application/json');
echo json_encode(['ok'=>true, 'id'=>$stmt->insert_id]);
?>

