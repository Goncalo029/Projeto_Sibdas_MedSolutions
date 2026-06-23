<?php
// barra de navegacao do topo (sino de avisos e menu do utilizador)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/notifications.php';
// dados do utilizador com sessao, para mostrar o nome e as iniciais
$_nav_email    = $_SESSION['user_email'] ?? 'Utilizador';
$_nav_nome     = ucfirst(strstr($_nav_email, '@', true) ?: $_nav_email);
$_nav_iniciais = strtoupper(substr($_nav_email, 0, 2));
$_nav_hoje     = date('d/m/Y');
$_nav_uid         = (int)($_SESSION['user_id'] ?? 0);
$_nav_avatar_file = __DIR__ . '/../uploads/' . $_nav_uid . '.png';
$_nav_avatar      = ($_nav_uid && file_exists($_nav_avatar_file))
    ? BASE_URL . '/private/uploads/' . $_nav_uid . '.png?v=' . filemtime($_nav_avatar_file)
    : '';
$notificacoes  = get_notificacoes();
$total_notifs  = array_sum(array_column($notificacoes, 'count'));
?>
<button class="mhs-sidebar-toggle mhs-float-toggle d-lg-none"
        onclick="document.body.classList.toggle('mhs-sidebar-open')" type="button">
    <i class="fa-solid fa-bars"></i>
</button>

<div class="mhs-topbar-float">

    <div class="mhs-topbar-chip d-none d-md-flex">
        <i class="fa-regular fa-calendar-days"></i>
        <span><?= htmlspecialchars($_nav_hoje) ?></span>
    </div>

    <?php if (in_array($_SESSION['profile'] ?? '', ['admin', 'tecnico'])): ?>
    <div class="mhs-notif-dropdown" id="notifDropdown">
        <button class="mhs-notif-btn<?= $total_notifs > 0 ? ' mhs-notif-btn--active' : '' ?>"
                onclick="toggleNotifMenu(event)" type="button" title="Notificações">
            <i class="fa-regular fa-bell"></i>
            <?php if ($total_notifs > 0): ?>
            <span class="mhs-notif-badge"><?= $total_notifs > 99 ? '99+' : $total_notifs ?></span>
            <?php endif; ?>
        </button>
        <div class="mhs-notif-menu" id="notifMenu">
            <div class="mhs-notif-menu-header">
                <i class="fa-regular fa-bell"></i>
                Notificações
                <?php if ($total_notifs > 0): ?>
                <span class="mhs-notif-menu-count"><?= $total_notifs ?></span>
                <?php endif; ?>
            </div>
            <?php if (empty($notificacoes)): ?>
            <div class="mhs-notif-empty">
                <i class="fa-regular fa-bell-slash"></i>
                <span>Sem notificações de momento</span>
            </div>
            <?php else: ?>
            <?php foreach ($notificacoes as $n): ?>
            <a href="<?= esc($n['link']) ?>" class="mhs-notif-item mhs-notif-item--<?= $n['color'] ?>">
                <span class="mhs-notif-item-icon"><i class="fa-solid <?= $n['icon'] ?>"></i></span>
                <span class="mhs-notif-item-text"><?= esc($n['label']) ?></span>
                <span class="mhs-notif-item-arrow"><i class="fa-solid fa-chevron-right"></i></span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mhs-avatar-dropdown" id="avatarDropdown">
        <button class="mhs-avatar-btn" onclick="toggleAvatarMenu(event)" type="button">
            <span class="mhs-avatar"><?php if ($_nav_avatar): ?><img src="<?= htmlspecialchars($_nav_avatar) ?>" alt=""><?php else: ?><?= $_nav_iniciais ?><?php endif; ?></span>
            <div class="mhs-avatar-info d-none d-md-flex">
                <span class="mhs-avatar-name"><?= htmlspecialchars($_nav_nome) ?></span>
                <span class="mhs-avatar-role"><?= htmlspecialchars(ucfirst($_SESSION['profile'] ?? '')) ?></span>
            </div>
            <i class="fa-solid fa-chevron-down mhs-avatar-chevron"></i>
        </button>
        <div class="mhs-avatar-menu" id="avatarMenu">
            <a class="mhs-avatar-menu-item" href="<?= BASE_URL ?>/private/views/utilizadores/perfil.php">
                <i class="fa-regular fa-user"></i>
                Perfil
            </a>
            <div class="mhs-avatar-menu-divider"></div>
            <a class="mhs-avatar-menu-item mhs-avatar-menu-item--danger" href="<?= BASE_URL ?>/public/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </div>
    </div>

</div>
