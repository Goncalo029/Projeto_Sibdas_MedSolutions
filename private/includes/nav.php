<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$nome = $_SESSION['user_email'] ?? 'MedSolutions';
$iniciais = strtoupper(substr($nome, 0, 2));
$hoje = date('d/m/Y');
?>
<header class="mhs-topbar">
    <div class="mhs-topbar-inner">
        <div class="mhs-topbar-left">
            <button class="mhs-sidebar-toggle d-lg-none" onclick="document.body.classList.toggle('mhs-sidebar-open')">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="<?= BASE_URL ?>/private/home.php" class="mhs-topbar-brand">
                <span class="mhs-topbar-brand-mark">
                    <img alt="Logo" src="<?= BASE_URL ?>/public/assets/images/logo-medsoft.svg" height="32" />
                </span>
                <span class="mhs-topbar-brand-copy">
                    <strong>MedSolutions</strong>
                    <small></small>
                </span>
            </a>
        </div>
        <div class="mhs-topbar-right">
            <span class="mhs-topbar-meta">
                <i class="fa-regular fa-calendar"></i>
                <?php echo htmlspecialchars($hoje); ?>
            </span>
            
            <a class="mhs-avatar-btn" href="<?= BASE_URL ?>/public/logout.php" title="Logout">
                <span class="mhs-avatar"><?php echo $iniciais; ?></span>
                <span class="mhs-avatar-name d-none d-md-inline"><?php echo htmlspecialchars($nome); ?></span>
            </a>
        </div>
    </div>
</header>
