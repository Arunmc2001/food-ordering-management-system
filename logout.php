<?php
session_start();
session_destroy();
header("Location: delivery_login.php");
exit();
?>