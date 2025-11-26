<?php
session_start();
session_destroy();
header("Location: /BatCave_Admin/src/pages/login.php");
exit;
