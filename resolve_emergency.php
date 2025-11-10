<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['resident_role']) || $_SESSION['resident_role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

// Mark all active alerts as resolved
$stmt = $pdo->prepare("UPDATE emergency_alerts SET status = 'resolved' WHERE status = 'active'");
$stmt->execute();

echo "resolved";
?>
