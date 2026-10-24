<?php
require_once 'includes/session.php';

if (isLoggedIn()) {
    header('Location: /KapengINAMO/dashboard.php');
} else {
    header('Location: /KapengINAMO/auth/login.php');
}
exit();
?>
