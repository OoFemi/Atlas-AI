<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = $data["id"];
$username = $data["username"];
$role = $data["role"];

$stmt = $conn->prepare(
"UPDATE users
SET username=?, role=?
WHERE id=?"
);

$stmt->bind_param(
"ssi",
$username,
$role,
$id
);

$stmt->execute();

echo json_encode([
    "success"=>true
]);

?>
