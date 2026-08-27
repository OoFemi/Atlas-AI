<?php

header("Content-Type: application/json");

require "db.php";

$result =
$conn->query(
"
SELECT
    id,
    user_id,
    first_name,
    last_name,
    email,
    username,
    role,
    group_id,
    status,
    created_at,
    last_login
FROM users
ORDER BY username
"
);

$users = [];

while(
    $row =
    $result->fetch_assoc()
){

    $users[] = $row;

}

echo json_encode(
    $users
);

?>
