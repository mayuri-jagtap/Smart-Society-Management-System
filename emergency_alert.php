<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['resident_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$triggered_by = $_SESSION['resident_id'];
$message = "🚨 Emergency triggered by Resident ID: " . $triggered_by;

// Always create a new alert (no blocking)
$stmt = $pdo->prepare("INSERT INTO emergency_alerts (triggered_by, message, status) VALUES (?, ?, 'active')");
$stmt->execute([$triggered_by, $message]);

// Auto mark as resolved after 10 seconds (using DB event or delay logic)
$alert_id = $pdo->lastInsertId();

// optional background mark (just for record)
echo "alert_sent";
?>
