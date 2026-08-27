<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$id = intval($data["id"]);

/*
 * Prevent deletion of admin accounts
 */

$stmt = $conn->prepare(
    "SELECT role, username
     FROM users
     WHERE id=?"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if(!$user){

    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);

    exit;
}

if($user["role"] === "admin"){

    echo json_encode([
        "success" => false,
        "message" => "Admin accounts cannot be deleted"
    ]);

    exit;
}

/*
 * Delete user
 */

$stmt = $conn->prepare(
    "DELETE FROM users WHERE id=?"
);

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

echo json_encode([
    "success" => true
]);
