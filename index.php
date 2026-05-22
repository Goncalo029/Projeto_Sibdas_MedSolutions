<?php
// Redirecionar para login se não estiver autenticado
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Já autenticado - ir para o dashboard
    header('Location: /MedSolutions/private/home.php');
    exit;
} else {
    // Não autenticado - ir para login
    header('Location: /MedSolutions/public/login.php');
    exit;
}
?>
