<?php

session_start();

header(
    "Content-Type: application/json"
);

echo json_encode([

    "logged_in" =>
    isset($_SESSION["logged_in"]),

    "name" =>
    $_SESSION["name"] ?? "",

    "email" =>
    $_SESSION["email"] ?? "",

    "group_id" =>
    $_SESSION["group_id"] ?? ""

]);
