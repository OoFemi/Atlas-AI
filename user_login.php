<?php

session_start();

require "db.php";

$email = $_POST["email"] ?? "";
$password = $_POST["password"] ?? "";

$stmt = $conn->prepare(
    "SELECT * FROM users WHERE email=?"
);

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if(
    $user &&
    password_verify(
        $password,
        $user["password_hash"]
    )
){

    $_SESSION["logged_in"] = true;
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["name"] =
    trim(
        ($user["first_name"] ?? "") .
        " " .
        ($user["last_name"] ?? "")
    );
    $_SESSION["email"] =$user["email"];
    $_SESSION["group_id"] =$user["group_id"];

    header("Location: chat.html");
    exit;
}

echo "Login Failed";
