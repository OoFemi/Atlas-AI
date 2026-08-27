<?php

require "db.php";

$username = "admin";

$passwordHash =
password_hash(
    "Fob123!",
    PASSWORD_DEFAULT
);

$role = "admin";

$stmt =
$conn->prepare(
"INSERT INTO users
(username,password_hash,role)
VALUES (?,?,?)"
);

$stmt->bind_param(
"sss",
$username,
$passwordHash,
$role
);

$stmt->execute();

echo "Admin Created";

?>
