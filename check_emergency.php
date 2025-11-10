<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT * FROM emergency_alerts WHERE created_at >= (NOW() - INTERVAL 10 SECOND) ORDER BY id DESC LIMIT 1");
$alert = $stmt->fetch(PDO::FETCH_ASSOC);

if ($alert) {
    echo json_encode([
        "status" => "alert",
        "id" => $alert['id'],
        "message" => $alert['message']
    ]);
} else {
    echo json_encode(["status" => "safe"]);
}
?>
