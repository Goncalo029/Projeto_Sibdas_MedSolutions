<?php
// topo comum a todas as paginas da area privada (abre o <html>, mete o CSS e a barra de cima)
require_once __DIR__ . '/../../config/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');
// versao do CSS pela data do ficheiro, para o browser recarregar quando muda
$mhs_css_version = filemtime(__DIR__ . '/../assets/css/1220673.css');
$mhs_page_file = basename($_SERVER['SCRIPT_NAME'] ?? '');
$mhs_body_page_class = '';

if (in_array($mhs_page_file, ['detalhes.php', 'perfil.php'])) {
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
    <title><?php echo APP_NAME; ?></title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>/public/assets/images/logo-medsoft.svg" type="image/svg+xml">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet" />
    <!-- CSS globais -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/private/assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/private/assets/datatables/datatables.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/private/assets/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/private/assets/fontawesome/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/private/assets/css/1220673.css?v=<?= $mhs_css_version ?>">
    <!-- Scripts -->
    <script src="<?= BASE_URL ?>/private/assets/jQuery/jquery-3.6.0.min.js"></script>
    <script src="<?= BASE_URL ?>/private/assets/datatables/datatables.min.js"></script>
    <script src="<?= BASE_URL ?>/private/assets/flatpickr/flatpickr.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="mhs-app<?= $mhs_body_page_class ?>">

<?php
include __DIR__ . '/sidebar.php';
include __DIR__ . '/nav.php';
?>

<div class="mhs-main-content">
<?php if (!empty($_SESSION['success_message'])): ?>
<div class="position-fixed top-0 start-50 translate-middle-x" style="z-index:9999;margin-top:90px;min-width:320px">
    <div class="toast show align-items-center text-bg-success border-0 shadow" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold"><i class="fas fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (!empty($_SESSION['error_message'])): ?>
<div class="position-fixed top-0 start-50 translate-middle-x" style="z-index:9999;margin-top:90px;min-width:320px">
    <div class="toast show align-items-center text-bg-danger border-0 shadow" role="alert">
        <div class="d-flex">
            <div class="toast-body fw-semibold"><i class="fas fa-circle-xmark me-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php unset($_SESSION['error_message']); endif; ?>
