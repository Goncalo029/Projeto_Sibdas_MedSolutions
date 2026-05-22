<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$page_title = 'Dashboard';

include __DIR__ . '/includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker">Centro de controlo</span>
        <h1 class="mhs-page-title">Dashboard</h1>
        <p class="mhs-page-copy">Dashboard</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
