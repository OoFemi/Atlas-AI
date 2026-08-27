<?php
session_start();
require_once 'db.php'; // your database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT
        username,
        department,
        role,
        last_login,
        theme,
        response_style
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Fob AI - User Settings</title>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f4f6f9;
    margin:0;
    padding:40px;
}

.container{
    max-width:700px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

h2{
    margin-bottom:25px;
}

.section{
    margin-bottom:30px;
    padding-bottom:20px;
    border-bottom:1px solid #ddd;
}

.section h3{
    margin-bottom:15px;
}

.radio-group label{
    display:block;
    margin:10px 0;
}

.btn-save{
    background:#4a90e2;
    color:#fff;
    border:none;
    padding:12px 25px;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
}

.btn-save:hover{
    background:#357abd;
}

.info-row{
    margin-bottom:8px;
}

.success{
    color:green;
    margin-bottom:20px;
    font-weight:bold;
}

</style>
</head>

<body>

<div class="container">

    <h2>⚙ User Settings</h2>

    <?php if(isset($_GET['updated'])): ?>
        <div class="success">
            Settings saved successfully.
        </div>
    <?php endif; ?>

    save_settings.php

        <!-- Account Information -->

        <div class="section">
            <h3>Account Information</h3>

            <div class="info-row">
                <strong>Username:</strong>
                <?= htmlspecialchars($user['username']); ?>
            </div>

            <div class="info-row">
                <strong>Department:</strong>
                <?= htmlspecialchars($user['department']); ?>
            </div>

            <div class="info-row">
                <strong>Role:</strong>
                <?= htmlspecialchars($user['role']); ?>
            </div>

            <div class="info-row">
                <strong>Last Login:</strong>
                <?= htmlspecialchars($user['last_login']); ?>
            </div>

        </div>

        <!-- Theme -->

        <div class="section">

            <h3>Theme</h3>

            <div class="radio-group">

                <label>
                    <input
                        type="radio"
                        name="theme"
                        value="light"
                        <?= ($user['theme'] == 'light') ? 'checked' : ''; ?>
                    >
                    Light
                </label>

                <label>
                    <input
                        type="radio"
                        name="theme"
                        value="dark"
                        <?= ($user['theme'] == 'dark') ? 'checked' : ''; ?>
                    >
                    Dark
                </label>

                <label>
                    <input
                        type="radio"
                        name="theme"
                        value="system"
                        <?= ($user['theme'] == 'system') ? 'checked' : ''; ?>
                    >
                    System Default
                </label>

            </div>

        </div>

        <!-- AI Response Style -->

        <div class="section">

            <h3>AI Response Style</h3>

            <div class="radio-group">

                <label>
                    <input
                        type="radio"
                        name="response_style"
                        value="concise"
                        <?= ($user['response_style'] == 'concise') ? 'checked' : ''; ?>
                    >
                    Concise
                </label>

                <label>
                    <input
                        type="radio"
                        name="response_style"
                        value="balanced"
                        <?= ($user['response_style'] == 'balanced') ? 'checked' : ''; ?>
                    >
                    Balanced
                </label>

                <label>
                    <input
                        type="radio"
                        name="response_style"
                        value="detailed"
                        <?= ($user['response_style'] == 'detailed') ? 'checked' : ''; ?>
                    >
                    Detailed
                </label>

            </div>

        </div>

        <!-- About -->

        <div class="section">

            <h3>About Fob AI</h3>

            <p><strong>Version:</strong> 1.0.0</p>
            <p><strong>Build:</strong> 2026.07.26</p>

        </div>

        <button type="submit" class="btn-save">
            Save Settings
        </button>

    </form>

</div>

</body>
</html>
