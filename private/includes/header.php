<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | MedSolutions' : 'MedSolutions'; ?></title>
    <link rel="icon" type="image/svg+xml" href="../../public/assets/images/logo-medsoft.svg" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/fontawesome/all.min.css" />
    <link rel="stylesheet" href="../assets/css/medsolutions.css" />
</head>
<body class="mhs-app">

<?php
include __DIR__ . '/nav.php';
include __DIR__ . '/sidebar.php';
?>

<div class="mhs-main-content">
