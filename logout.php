<?php
require_once 'config/config.php';

if (isset($_SESSION['user_id'])) {
    // Log activity
    $logStmt = $pdo->prepare("INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
    $logStmt->execute([$_SESSION['user_id'], 'User logged out']);
}

session_destroy();
header('Location: index.php');
exit;
?>
