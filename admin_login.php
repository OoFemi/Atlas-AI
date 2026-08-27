<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

header("Content-Type: application/json");

require "db.php";

$data =
json_decode(
    file_get_contents("php://input"),
    true
);

$username =
$data["username"] ?? "";

$password =
$data["password"] ?? "";

$stmt =
$conn->prepare(
"SELECT *
FROM users
WHERE username=?"
);

$stmt->bind_param(
"s",
$username
);

$stmt->execute();

$result =
$stmt->get_result();

$user =
$result->fetch_assoc();

if (
    $user &&
    password_verify(
        $password,
        $user["password_hash"]
    ) &&
    $user["role"] === "admin"
)
{

    $_SESSION["admin"] = true;
    $_SESSION["username"] = $username;

    header("Location: dashboard.php");
    exit;

}

else
{

    header("Location: admin.html");
    exit;

}
