```php
<?php

session_start();

if (
    !isset($_SESSION["admin"]) ||
    $_SESSION["admin"] !== true
) {
    header("Location: admin.html");
    exit;
}

require "db.php";

$departments = [];

$result = $conn->query("
    SELECT department_name
    FROM departments
    WHERE status='Active'
    ORDER BY department_name
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row["department_name"];
    }
}

$baseFolder = "/home/femi/n8n-production/files";
$totalDocuments = 0;
$folders = is_dir($baseFolder) ? glob($baseFolder . "/*") : [];

if ($folders) {
    foreach ($folders as $folder) {
        if (!is_dir($folder)) {
            continue;
        }

        $files = glob($folder . "/*");
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalDocuments++;
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document Management</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#f4f6f9;
}

/* SIDEBAR */

.sidebar{
    position:fixed;
    left:0;
    top:0;

    width:220px;
    height:100vh;

    background:#001845;

    padding:20px;
}

.sidebar h2{
    color:white;
    margin-bottom:25px;
}

.sidebar button{

    width:100%;
    height:45px;

    margin-bottom:10px;

    border:none;
    border-radius:8px;

    background:#4da3ff;

    color:white;

    cursor:pointer;

    font-size:14px;
}

.logout{
    background:#e74c3c !important;
}

/* CONTENT */

.content{
    margin-left:220px;
    padding:30px;
}

.header{
    background:#001845;
    color:white;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-radius:10px;
    margin-bottom:25px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}

.stats{
    display:flex;
    gap:20px;
    margin-bottom:25px;
}

.stat-card{
    flex:1;
    background:white;
    padding:20px;
    border-radius:10px;
    text-align:center;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover{
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,.15);
}

.stat-card h3{
    color:#001845;
    margin-bottom:10px;
}

.stat-card p{
    font-size:28px;
    font-weight:bold;
    color:#4da3ff;
}

.card,
.table-card{
    background:white;
    border-radius:10px;
    padding:25px;
    margin-bottom:25px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover,
.table-card:hover{
    box-shadow: 0 6px 16px rgba(0,0,0,.12);
}

h2{
    margin-bottom:20px;
    color:#001845;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:bold;
    color:#333;
}

select,
input[type=file],
input[type=text]{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:6px;
    margin-bottom:15px;
    outline:none;
    transition: border-color 0.3s ease;
}

select:focus,
input[type=text]:focus{
    border-color:#4da3ff;
}

.upload-btn{
    background:#4da3ff;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
    transition: background 0.3s ease;
}

.upload-btn:hover{
    background:#3391ff;
}

.back-btn{
    color:white;
    text-decoration:none;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th{
    background:#4da3ff;
    color:white;
    padding:12px;
    text-align:left;
}

td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

.status{
    color:green;
    font-weight:bold;
}

.action-btn{
    text-decoration:none;
    padding:6px 10px;
    border-radius:5px;
    color:white;
    font-size:12px;
    margin-right:5px;
    display:inline-block;
    transition: opacity 0.3s ease;
}

.action-btn:hover{
    opacity: 0.85;
}

.view-btn{
    background:#28a745;
}

.delete-btn{
    background:#dc3545;
}

.success{
    background:#d4edda;
    color:#155724;
    padding:15px;
    border-radius:8px;
    margin-bottom:20px;
}

.error{
    background:#f8d7da;
    color:#721c24;
    padding:15px;
    border-radius:8px;
    margin-bottom:20px;
}
</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <h2>Fob AI</h2>

    <button onclick="location.href='dashboard.php'">
        📊 Dashboard
    </button>

    <button onclick="location.href='users.php'">
        👥 Users
    </button>

    <button onclick="location.href='departments.php'">
        🏢 Departments
    </button>

    <button onclick="location.href='documents.php'">
        📄 Documents
    </button>

    <button onclick="location.href='sharepoint.php'">
        📁 SharePoint
    </button>

    <button onclick="location.href='branding.php'">
        🎨 Branding
    </button>

    <button onclick="location.href='settings.php'">
        ⚙ Settings
    </button>

    <button class="logout" onclick="location.href='logout.php'">
        🚪 Logout
    </button>

</div>

<!-- PAGE CONTENT -->

<div class="content">

    <div class="header">
        <h1>Document Management</h1>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php
    if (isset($_GET["upload"])) {
        if ($_GET["upload"] === "success") {
            echo '<div class="success">✅ Document uploaded successfully.</div>';
        }
        if ($_GET["upload"] === "failed") {
            echo '<div class="error">❌ Document upload failed.</div>';
        }
    }
    ?>

    <div class="stats">
        <div class="stat-card">
            <h3>Documents</h3>
            <p><?= $totalDocuments ?></p>
        </div>
        <div class="stat-card">
            <h3>Departments</h3>
            <p><?= count($departments) ?></p>
        </div>
        <div class="stat-card">
            <h3>Status</h3>
            <p style="color:#28a745; font-size: 22px; margin-top: 5px;">Live</p>
        </div>
    </div>

    <div class="card">
        <h2>Upload Document</h2>

        <form action="upload_documents.php" method="POST" enctype="multipart/form-data">

            <label>Department</label>
            <select name="department" required>
                <?php foreach ($departments as $department): ?>
                <option value="<?= htmlspecialchars($department) ?>">
                    <?= htmlspecialchars($department) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Document</label>
            <input type="file" name="document" accept=".pdf,.docx,.txt,.xlsx,.csv" required>

            <button type="submit" class="upload-btn">
                Upload Document
            </button>

        </form>
    </div>

    <div class="table-card">
        <h2>Uploaded Documents</h2>

        <input type="text" id="docSearch" placeholder="Search documents...">

        <table id="documentsTable">
        <thead>
        <tr>
            <th>File Name</th>
            <th>Type</th>
            <th>Department</th>
            <th>Status</th>
            <th>Size</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>

        <?php
        if ($folders) {
            foreach ($folders as $folder) {
                if (!is_dir($folder)) {
                    continue;
                }

                $department = basename($folder);
                $files = glob($folder . "/*");

                if ($files) {
                    foreach ($files as $file) {
                        if (!is_file($file)) {
                            continue;
                        }

                        $fileName = basename($file);
                        $type = strtoupper(pathinfo($fileName, PATHINFO_EXTENSION));
                        $size = round(filesize($file) / 1024, 2);

                        ?>
                        <tr>
                            <td><?= htmlspecialchars($fileName) ?></td>
                            <td><?= $type ?></td>
                            <td><?= htmlspecialchars($department) ?></td>
                            <td class="status">🟢 Indexed</td>
                            <td><?= $size ?> KB</td>
                            <td>
                                <a href="view_document.php?department=<?= urlencode($department) ?>&file=<?= urlencode($fileName) ?>" 
                                   class="action-btn view-btn" target="_blank">View</a>
                                <a href="delete_document.php?department=<?= urlencode($department) ?>&file=<?= urlencode($fileName) ?>" 
                                   class="action-btn delete-btn" 
                                   onclick="return confirm('Delete this document?')">Delete</a>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
        }
        ?>

        </tbody>
        </table>
    </div>

</div>

<script>
document.getElementById("docSearch").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#documentsTable tbody tr");

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
    });
});
</script>

</body>
</html>

```
