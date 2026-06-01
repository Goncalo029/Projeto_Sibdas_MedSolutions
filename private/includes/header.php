<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');
$mhs_css_version = filemtime(__DIR__ . '/../assets/css/medsolutions.css');
$mhs_page_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$mhs_body_page_class = '';

if ($mhs_page_file === 'detalhes.php') {
    $mhs_body_page_class = ' mhs-detail-page';
} elseif ($mhs_page_file === 'editar.php') {
    $mhs_body_page_class = ' mhs-edit-page';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>" />
    <title><?php echo APP_NAME; ?></title>
    <link rel="shortcut icon" href="/MedSolutions/public/assets/images/logo-medsoft.svg" type="image/svg+xml">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />
    <!-- CSS globais -->
    <link rel="stylesheet" href="/MedSolutions/private/assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="/MedSolutions/private/assets/datatables/datatables.min.css">
    <link rel="stylesheet" href="/MedSolutions/private/assets/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="/MedSolutions/private/assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="/MedSolutions/private/assets/css/medsolutions.css?v=<?= $mhs_css_version ?>">
    <!-- Scripts -->
    <script src="/MedSolutions/private/assets/jQuery/jquery-3.6.0.min.js"></script>
    <script src="/MedSolutions/private/assets/datatables/datatables.min.js"></script>
    <script src="/MedSolutions/private/assets/flatpickr/flatpickr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="mhs-app<?= $mhs_body_page_class ?>">

<?php
include __DIR__ . '/nav.php';
include __DIR__ . '/sidebar.php';
?>

<div class="mhs-main-content">
