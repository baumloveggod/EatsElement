<?php
session_start();
session_unset();
session_destroy();
setcookie('auth', '', time() - 3600, "/"); // Löscht das Auth-Cookie
header("Location: /login.html");
exit;
?>
