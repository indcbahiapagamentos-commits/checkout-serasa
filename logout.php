<?php
session_start();
session_destroy(); // Destroi a sessão
header('Location: login.php');
exit;
?>