<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);


$department =
$data["department"] ?? "";


/* Validation */

if(
    empty($data["username"]) ||
    empty($data["password"])
){
    echo json_encode([
        "success" => false,
        "message" => "Username and Password are required"
    ]);
    exit;
}

/* Check Username */

$check =
$conn->prepare(
"SELECT id FROM users WHERE username=?"
);

$check->bind_param(
"s",
$data["username"]
);

$check->execute();

$result =
$check->get_result();

if($result->num_rows > 0){

    echo json_encode([
        "success" => false,
        "message" => "Username already exists"
    ]);

    exit;
}

/* Generate User ID */

$result =
$conn->query(
"SELECT MAX(id) AS max_id FROM users"
);

$row =
$result->fetch_assoc();

$user_id =
"ATL-USR-" .
str_pad(
    ($row["max_id"] ?? 0) + 1,
    5,
    "0",
    STR_PAD_LEFT
);

/* Fields */

$first_name =
$data["first_name"] ?? "";

$last_name =
$data["last_name"] ?? "";

$email =
$data["email"] ?? "";

$username =
$data["username"];

$passwordHash =
password_hash(
    $data["password"],
    PASSWORD_DEFAULT
);

$role =
$data["role"] ?? "user";

$group_id =
$data["group_id"] ?? "ATL-GRP-SUPPORT";

$status = "Active";

/* Insert */

$stmt =
$conn->prepare(
"
INSERT INTO users
(
    user_id,
    first_name,
    last_name,
    email,
    username,
    password_hash,
    role,
    group_id,
    department,
    status
)
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)
"
);


$stmt->bind_param(
    "ssssssssss",
    $user_id,
    $first_name,
    $last_name,
    $email,
    $username,
    $passwordHash,
    $role,
    $group_id,
    $department,
    $status
);



if($stmt->execute()){

    echo json_encode([
        "success" => true,
        "user_id" => $user_id
    ]);

}else{

    echo json_encode([
        "success" => false,
        "message" =>
        $stmt->error
    ]);

}

?>
