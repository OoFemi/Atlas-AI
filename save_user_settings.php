<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit;
}

$userId = $_SESSION['user_id'];

$theme =
$_POST['theme'] ?? 'system';

$responseStyle =
$_POST['response_style'] ?? 'balanced';

$stmt = $conn->prepare("
UPDATE users
SET
    theme = ?,
    response_style = ?
WHERE id = ?
");

$stmt->bind_param(
    "ssi",
    $theme,
    $responseStyle,
    $userId
);

$stmt->execute();

echo "success";
