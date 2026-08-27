<?php

header("Content-Type: application/json");

require "db.php";

$result =
$conn->query(
"SELECT * FROM settings"
);

$data=[];

while(
$row=$result->fetch_assoc()
){
    $data[$row["setting_name"]]
    =
    $row["setting_value"];
}

echo json_encode($data);

?>
