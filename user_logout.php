<?php

session_start();

session_destroy();

header("Location: chat.html");

exit;
