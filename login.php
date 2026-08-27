<?php

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

    echo json_encode([
        "success" => true,
        "role" => $user["role"]
    ]);

}
else
{

    echo json_encode([
        "success" => false
    ]);

}

?>
