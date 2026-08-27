<?php

header("Content-Type: application/json");

require "db.php";

$data =
json_decode(
file_get_contents("php://input"),
true
);

$ai_name =
$data["ai_name"];

$stmt =
$conn->prepare(
"UPDATE settings
SET setting_value=?
WHERE setting_name='ai_name'"
);

$stmt->bind_param(
"s",
$ai_name
);

$stmt->execute();

echo json_encode([
"success"=>true
]);

?>
