<?php
session_start();
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'system';
    $response_style = $_POST['response_style'] ?? 'balanced';

    $stmt = $conn->prepare("UPDATE users SET theme = ?, response_style = ? WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $theme, $response_style, $username);
        if ($stmt->execute()) {
            $success_message = "Settings updated successfully.";
        } else {
            $error_message = "Failed to update settings: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Database error: " . $conn->error;
    }
}

// Fetch user details
$stmt = $conn->prepare("SELECT username, department, role, last_login, theme, response_style FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fallbacks if user record is missing fields
if (!$user) {
    $user = [
        'username' => $username,
        'department' => 'General',
        'role' => 'user',
        'last_login' => null,
        'theme' => 'system',
        'response_style' => 'balanced'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Settings - FOB-AI</title>
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 30px;
        }
        h1 {
            font-size: 24px;
            margin-top: 0;
            color: #f0f6fc;
            border-bottom: 1px solid #30363d;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 18px;
            color: #f0f6fc;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .info-block {
            font-size: 14px;
            color: #8b949e;
            margin-bottom: 10px;
        }
        .info-block span {
            color: #c9d1d9;
            font-weight: 500;
        }
        .radio-group {
            margin: 10px 0;
        }
        .radio-group label {
            display: block;
            color: #c9d1d9;
            font-size: 14px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .radio-group input {
            margin-right: 8px;
        }
        .btn-save {
            background-color: #1f6feb;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-save:hover {
            background-color: #388bfd;
        }
        .back-link {
            color: #58a6ff;
            text-decoration: none;
            margin-left: 15px;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-success {
            background-color: rgba(46, 160, 67, 0.15);
            border: 1px solid rgba(46, 160, 67, 0.4);
            color: #3fb950;
        }
        .alert-error {
            background-color: rgba(248, 81, 73, 0.15);
            border: 1px solid rgba(248, 81, 73, 0.4);
            color: #f85145;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ User Settings</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <h2>Account Information</h2>
            <div class="info-block">Username: <span><?php echo htmlspecialchars($user['username'] ?? ''); ?></span></div>
            <div class="info-block">Department: <span><?php echo htmlspecialchars($user['department'] ?? 'General'); ?></span></div>
            <div class="info-block">Role: <span><?php echo htmlspecialchars($user['role'] ?? 'user'); ?></span></div>
            <div class="info-block">Last Login: <span><?php echo htmlspecialchars($user['last_login'] ?? 'Never'); ?></span></div>

            <h2>Appearance</h2>
            <div class="radio-group">
                <label><input type="radio" name="theme" value="light" <?php echo (($user['theme'] ?? 'system') === 'light') ? 'checked' : ''; ?>> Light</label>
                <label><input type="radio" name="theme" value="dark" <?php echo (($user['theme'] ?? 'system') === 'dark') ? 'checked' : ''; ?>> Dark</label>
                <label><input type="radio" name="theme" value="system" <?php echo (($user['theme'] ?? 'system') === 'system') ? 'checked' : ''; ?>> System Default</label>
            </div>

            <h2>AI Response Style</h2>
            <div class="radio-group">
                <label><input type="radio" name="response_style" value="concise" <?php echo (($user['response_style'] ?? 'balanced') === 'concise') ? 'checked' : ''; ?>> Concise</label>
                <label><input type="radio" name="response_style" value="balanced" <?php echo (($user['response_style'] ?? 'balanced') === 'balanced') ? 'checked' : ''; ?>> Balanced</label>
                <label><input type="radio" name="response_style" value="detailed" <?php echo (($user['response_style'] ?? 'balanced') === 'detailed') ? 'checked' : ''; ?>> Detailed</label>
            </div>

            <h2>About FOB-AI</h2>
            <div class="info-block">Version: <span>1.0.0 Enterprise</span></div>
            <div class="info-block">Powered by <span>Fiberone Limited</span></div>

            <button type="submit" class="btn-save">Save Settings</button>
            <a href="index.php" class="back-link">← Back to Chat</a>
        </form>
    </div>
</body>
</html>
