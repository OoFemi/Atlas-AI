<?php

session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: admin.html");
    exit;
}

require_once 'db.php';

/* ADD DEPARTMENT */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $department = trim($_POST["department_name"]);

    if (!empty($department)) {

        $groupId =
            "DEPT-" .
            strtoupper(
                preg_replace(
                    '/[^A-Za-z0-9]/',
                    '-',
                    $department
                )
            );

        $stmt = $conn->prepare(
            "INSERT INTO departments
            (department_name, group_id, folder_name)
            VALUES (?,?,?)"
        );

        $stmt->bind_param(
            "sss",
            $department,
            $groupId,
            $department
        );

        $stmt->execute();
    }

    header("Location: departments.php");
    exit;
}

$result =
$conn->query(
"SELECT *
 FROM departments
 ORDER BY department_name"
);

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Departments</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Segoe UI,sans-serif;
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

.card{
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;

    box-shadow:
    0 2px 8px rgba(0,0,0,.1);
}

input{
    width:300px;
    padding:10px;
}

.submit-btn{

    background:#4da3ff;
    color:white;

    border:none;

    padding:10px 15px;

    border-radius:6px;

    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th{
    background:#4da3ff;
    color:white;
    text-align:left;
    padding:12px;
}

td{
    padding:12px;
    border-bottom:1px solid #ddd;
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

    <h1>Departments</h1>

    <br>

    <div class="card">

        <h3>Add Department</h3>

        <br>

        <form method="POST">

            <input
                type="text"
                name="department_name"
                placeholder="Department Name"
                required>

            <br><br>

            <button
                type="submit"
                class="submit-btn">

                Add Department

            </button>

        </form>

    </div>

    <table>

        <thead>

            <tr>
                <th>ID</th>
                <th>Department</th>
                <th>Group ID</th>
                <th>Folder</th>
                <th>Status</th>
            </tr>

        </thead>

        <tbody>

        <?php while($row = $result->fetch_assoc()) { ?>

            <tr>

                <td><?= $row["id"] ?></td>

                <td><?= htmlspecialchars($row["department_name"]) ?></td>

                <td><?= htmlspecialchars($row["group_id"]) ?></td>

                <td><?= htmlspecialchars($row["folder_name"]) ?></td>

                <td><?= htmlspecialchars($row["status"] ?? '') ?></td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

</body>
</html>
