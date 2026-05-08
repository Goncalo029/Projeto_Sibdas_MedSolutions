<?php
session_start();

// Destruir toda a sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
?>

