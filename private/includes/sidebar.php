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
?>
<aside class="mhs-sidebar" id="mhsSidebar">
    <div class="mhs-sidebar-header">
        <a href="<?= BASE_URL ?>/private/home.php" class="mhs-sidebar-logo">
            <span class="mhs-sidebar-logo-mark"><img alt="Logo MedSolutions" src="<?= BASE_URL ?>/public/assets/images/logo-medsoft.svg" /></span>
            <span class="mhs-sidebar-logo-copy">
                <strong>MedSolutions</strong>
                <small>Inventário clínico centralizado</small>
            </span>
        </a>
        <p class="mhs-sidebar-copy">Operações, equipamentos, contratos e documentação num único painel.</p>
    </div>

    <nav class="mhs-sidebar-nav">
        <div class="mhs-nav-section">Principal</div>
        <a href="<?= BASE_URL ?>/private/home.php" class="mhs-nav-link<?= mhs_active('dashboard'); ?>"><i class="fa-solid fa-gauge-high fa-fw"></i><span>Dashboard</span></a>

        <div class="mhs-nav-section">Inventário</div>
        <a href="<?= BASE_URL ?>/private/views/equipamentos/lista.php" class="mhs-nav-link<?= mhs_active('equipamentos'); ?>"><i class="fa-solid fa-stethoscope fa-fw"></i><span>Equipamentos</span></a>
        <a href="<?= BASE_URL ?>/private/views/categorias/lista.php" class="mhs-nav-link<?= mhs_active('categorias'); ?>"><i class="fa-solid fa-tags fa-fw"></i><span>Categorias</span></a>
        <a href="<?= BASE_URL ?>/private/views/localizacoes/lista.php" class="mhs-nav-link<?= mhs_active('localizacoes'); ?>"><i class="fa-solid fa-location-dot fa-fw"></i><span>Localizações</span></a>

        <div class="mhs-nav-section">Gestão</div>
        <a href="<?= BASE_URL ?>/private/views/fornecedores/lista.php" class="mhs-nav-link<?= mhs_active('fornecedores'); ?>"><i class="fa-solid fa-truck fa-fw"></i><span>Fornecedores</span></a>
        <a href="<?= BASE_URL ?>/private/views/documentos/lista.php" class="mhs-nav-link<?= mhs_active('documentos'); ?>"><i class="fa-solid fa-file-lines fa-fw"></i><span>Documentos</span></a>
        <a href="<?= BASE_URL ?>/private/views/garantias-contrato/lista.php" class="mhs-nav-link<?= mhs_active('garantias'); ?>"><i class="fa-solid fa-shield-halved fa-fw"></i><span>Garantias-Contrato</span></a>

        <div class="mhs-nav-section">Ferramentas</div>
        <a href="<?= BASE_URL ?>/private/views/pesquisa/pesquisa.php" class="mhs-nav-link<?= mhs_active('pesquisa'); ?>"><i class="fa-solid fa-magnifying-glass fa-fw"></i><span>Pesquisa</span></a>
        <a href="<?= BASE_URL ?>/private/views/utilizadores/lista.php" class="mhs-nav-link<?= mhs_active('utilizadores'); ?>"><i class="fa-solid fa-users fa-fw"></i><span>Utilizadores</span></a>
    </nav>

    <div class="mhs-sidebar-footer">
        <span class="mhs-sidebar-footer-label">Versão v1.0</span>
        <small>&copy; 2026 MedSolutions</small>
    </div>
</aside>

<div class="mhs-sidebar-overlay" onclick="document.body.classList.remove('mhs-sidebar-open')"></div>
