  GNU nano 7.2                                              logout.php
<?php

session_start();

session_unset();
session_destroy();

header("Location: /chat.html");
exit;

?>
