<?php

header("Content-Type: application/json");

require "db.php";

$data =
json_decode(
    file_get_contents("php://input"),
    true
);

$id =
$data["id"] ?? 0;

$password =
$data["password"] ?? "";

if(!$id || !$password){

    echo json_encode([
        "success"=>false
    ]);

    exit;

}

$passwordHash =
password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt =
$conn->prepare(
    "UPDATE users
     SET password_hash=?
     WHERE id=?"
);

$stmt->bind_param(
    "si",
    $passwordHash,
    $id
);

$stmt->execute();

echo json_encode([
    "success"=>true
]);

?>
