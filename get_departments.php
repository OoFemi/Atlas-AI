<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "db.php";

$result = $conn->query("
    SELECT
        department_name,
        group_id
    FROM departments
    ORDER BY department_name
");

if (!$result) {
    die($conn->error);
}

$departments = [];

while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

header("Content-Type: application/json");

echo json_encode($departments);
