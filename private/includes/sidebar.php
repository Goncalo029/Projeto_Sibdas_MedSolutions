<?php
$current_uri = $_SERVER['REQUEST_URI'] ?? '';

function mhs_active(string $segment): string
{
    global $current_uri;

    if ($segment === 'dashboard' && (str_contains($current_uri, '/home.php') || str_contains($current_uri, '/index.php') || str_ends_with($current_uri, '/private/'))) {
        return ' active';
    }

    if ($segment !== 'dashboard' && str_contains($current_uri, '/' . $segment . '/')) {
        return ' active';
    }

    return '';
}

function mhs_mensagens_nao_lidas(): int
{
    if (($_SESSION['profile'] ?? '') !== 'admin') {
        return 0;
    }
    try {
        $row = mhs_pdo()->query("SELECT COUNT(*) AS total FROM mensagens_contacto WHERE lida = 0 AND eliminado_em IS NULL")->fetch();
        return (int)($row->total ?? 0);
    } catch (Exception) {
        return 0;
    }
}
$_sidebar_msgs_nao_lidas = mhs_mensagens_nao_lidas();
?>
<aside class="mhs-sidebar" id="mhsSidebar">
    <div class="mhs-sidebar-header">
        <a href="<?= BASE_URL ?>/private/home.php" class="mhs-sidebar-logo">
            <span class="mhs-sidebar-logo-mark"><img alt="Logo MedSolutions" src="<?= BASE_URL ?>/public/assets/images/logo-medsoft.svg" /></span>
            <span class="mhs-sidebar-logo-copy">
                <strong>MedSolutions</strong>
            </span>
        </a>
    </div>

    <nav class="mhs-sidebar-nav">
        <div class="mhs-nav-section">Principal</div>
        <a href="<?= BASE_URL ?>/private/home.php" class="mhs-nav-link<?= mhs_active('dashboard'); ?>"><i class="fa-solid fa-gauge-high fa-fw"></i><span>Dashboard</span></a>

        <div class="mhs-nav-section">Inventário</div>
        <a href="<?= BASE_URL ?>/private/views/equipamentos/lista.php" class="mhs-nav-link<?= mhs_active('equipamentos'); ?>"><i class="fa-solid fa-stethoscope fa-fw"></i><span>Equipamentos</span></a>
        <a href="<?= BASE_URL ?>/private/views/localizacoes/lista.php" class="mhs-nav-link<?= mhs_active('localizacoes'); ?>"><i class="fa-solid fa-location-dot fa-fw"></i><span>Localizações</span></a>

        <div class="mhs-nav-section">Gestão</div>
        <a href="<?= BASE_URL ?>/private/views/fornecedores/lista.php" class="mhs-nav-link<?= mhs_active('fornecedores'); ?>"><i class="fa-solid fa-truck fa-fw"></i><span>Fornecedores</span></a>
        <a href="<?= BASE_URL ?>/private/views/documentos/lista.php" class="mhs-nav-link<?= mhs_active('documentos'); ?>"><i class="fa-solid fa-file-lines fa-fw"></i><span>Documentos</span></a>
        <a href="<?= BASE_URL ?>/private/views/garantias-contrato/lista.php" class="mhs-nav-link<?= mhs_active('garantias'); ?>"><i class="fa-solid fa-shield-halved fa-fw"></i><span>Garantias-Contrato</span></a>

        <div class="mhs-nav-section">Ferramentas</div>
        <a href="<?= BASE_URL ?>/private/views/pesquisa/pesquisa.php" class="mhs-nav-link<?= mhs_active('pesquisa'); ?>"><i class="fa-solid fa-magnifying-glass fa-fw"></i><span>Pesquisa</span></a>

        <?php if (is_admin()): ?>
        <div class="mhs-nav-section">Comunicação</div>
        <a href="<?= BASE_URL ?>/private/views/mensagens/lista.php" class="mhs-nav-link<?= mhs_active('mensagens'); ?>" style="position:relative">
            <i class="fa-solid fa-envelope fa-fw"></i>
            <span>Mensagens</span>
            <?php if ($_sidebar_msgs_nao_lidas > 0): ?>
            <span class="badge bg-danger ms-auto" style="font-size:.65rem;padding:2px 6px;border-radius:10px"><?= $_sidebar_msgs_nao_lidas ?></span>
            <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['profile']) && $_SESSION['profile'] === 'admin'): ?>
        <div class="mhs-nav-section">Administração</div>
        <a href="<?= BASE_URL ?>/private/views/utilizadores/lista.php" class="mhs-nav-link<?= mhs_active('utilizadores'); ?>"><i class="fa-solid fa-users fa-fw"></i><span>Utilizadores</span></a>
        <a href="<?= BASE_URL ?>/private/views/historico/lista.php" class="mhs-nav-link<?= mhs_active('historico'); ?>"><i class="fa-solid fa-clock-rotate-left fa-fw"></i><span>Histórico de Alterações</span></a>
        <a href="<?= BASE_URL ?>/private/views/website/editar.php" class="mhs-nav-link<?= mhs_active('website'); ?>"><i class="fa-solid fa-globe fa-fw"></i><span>Website Público</span></a>
        <?php endif; ?>
    </nav>
</aside>

<div class="mhs-sidebar-overlay" onclick="document.body.classList.remove('mhs-sidebar-open')"></div>
