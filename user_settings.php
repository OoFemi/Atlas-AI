<?php
session_start();
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$user = [
    'username' => 'Guest User',
    'department' => '-',
    'role' => 'Guest',
    'theme' => 'system',
    'response_style' => 'balanced',
    'last_login' => '-'
];

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT 
            username, 
            department, 
            role, 
            theme, 
            response_style, 
            last_login 
        FROM users 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $user = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FOB-AI - User Settings</title>
<style>
:root {
    --primary-blue: #1a73e8;
    --accent-red: #d93025;
    --bg-light: #f5f6fa;
    --card-light: #ffffff;
    --text-light: #202124;
    --border-light: #e0e0e0;
    
    --bg-dark: #1e1f2b;
    --card-dark: #11131d;
    --text-dark: #ffffff;
    --border-dark: #2f3246;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--bg-light);
    color: var(--text-light);
    margin: 0;
    padding: 40px;
    transition: background 0.3s, color 0.3s;
}

body.dark-mode {
    background: var(--bg-dark);
    color: var(--text-dark);
}

.container {
    max-width: 800px;
    margin: auto;
    background: var(--card-light);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: background 0.3s, box-shadow 0.3s;
}

body.dark-mode .container {
    background: var(--card-dark);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-light);
}

body.dark-mode .section {
    border-bottom: 1px solid var(--border-dark);
}

h1, h2 {
    margin-top: 0;
    margin-bottom: 15px;
}

h1 {
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 12px 0;
    cursor: pointer;
}

button {
    background: var(--primary-blue);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: opacity 0.2s;
}

button:hover {
    opacity: 0.9;
}

.back-link {
    margin-left: 15px;
    text-decoration: none;
    color: var(--primary-blue);
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}

.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #323232;
    color: #fff;
    padding: 12px 24px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: none;
    z-index: 1000;
    font-size: 0.95rem;
}

.toast.success {
    background: #137333;
}
</style>
</head>
<body>

<div class="container">
    <h1>⚙ User Settings</h1>

    <div class="section">
        <h2>Account Information</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
        <?php if($isLoggedIn): ?>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <p><strong>Last Login:</strong> <?php echo htmlspecialchars($user['last_login']); ?></p>
        <?php else: ?>
            <p><strong>Role:</strong> Guest</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Appearance</h2>
        <label><input type="radio" name="theme" value="light"> Light</label>
        <label><input type="radio" name="theme" value="dark"> Dark</label>
        <label><input type="radio" name="theme" value="system" checked> System Default</label>
    </div>

    <div class="section">
        <h2>AI Response Style</h2>
        <label><input type="radio" name="response_style" value="concise"> Concise</label>
        <label><input type="radio" name="response_style" value="balanced" checked> Balanced</label>
        <label><input type="radio" name="response_style" value="detailed"> Detailed</label>
    </div>

    <div class="section">
        <h2>About FOB-AI</h2>
        <p>Version: 1.0.2 Enterprise</p>
        <p>Powered by Fiberone Limited</p>
    </div>

    <button type="button" onclick="saveSettings()">Save Settings</button>
    <a href="chat.php" class="back-link">← Back to Chat</a>
</div>

<div id="toast" class="toast">Settings saved successfully</div>

<script>
const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
const serverTheme = "<?php echo htmlspecialchars($user['theme'] ?? 'system'); ?>";
const serverStyle = "<?php echo htmlspecialchars($user['response_style'] ?? 'balanced'); ?>";

function applyTheme(theme) {
    document.body.classList.remove("dark-mode");
    if (theme === "dark") {
        document.body.classList.add("dark-mode");
    } else if (theme === "system") {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.classList.add("dark-mode");
        }
    }
}

const currentTheme = isLoggedIn && serverTheme !== '' ? serverTheme : (localStorage.getItem("theme") || "system");
const currentStyle = isLoggedIn && serverStyle !== '' ? serverStyle : (localStorage.getItem("response_style") || "balanced");

applyTheme(currentTheme);

const themeRadio = document.querySelector(`input[name="theme"][value="${currentTheme}"]`);
if (themeRadio) themeRadio.checked = true;

const styleRadio = document.querySelector(`input[name="response_style"][value="${currentStyle}"]`);
if (styleRadio) styleRadio.checked = true;

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.className = "toast success";
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}

function saveSettings() {
    const theme = document.querySelector('input[name="theme"]:checked').value;
    const responseStyle = document.querySelector('input[name="response_style"]:checked').value;

    localStorage.setItem("theme", theme);
    localStorage.setItem("response_style", responseStyle);
    applyTheme(theme);

    if (!isLoggedIn) {
        showToast("Settings saved locally");
        return;
    }

    fetch("save_user_settings.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "theme=" + encodeURIComponent(theme) + "&response_style=" + encodeURIComponent(responseStyle)
    })
    .then(response => {
        if (response.ok) {
            showToast("Settings saved successfully");
        } else {
            showToast("Failed to save settings to server");
        }
    })
    .catch(() => {
        showToast("Network error occurred");
    });
}
</script>

</body>
</html>
