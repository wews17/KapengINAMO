<?php
session_start();
session_destroy();
header('Location: /KapengINAMO/auth/login.php');
exit();
?>
