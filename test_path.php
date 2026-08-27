<?php

echo "<pre>";

echo "Contents of /home:\n";
print_r(scandir('/home'));

echo "\nContents of /home/femi:\n";

$result = @scandir('/home/femi');

var_dump($result);

echo "\nLast error:\n";
print_r(error_get_last());

echo "</pre>";
