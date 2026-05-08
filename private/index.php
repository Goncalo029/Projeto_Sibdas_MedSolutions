<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 p-4">
                <div class="mhs-page-header mhs-page-header--dashboard">
                    <div>
                        <span class="mhs-page-kicker">Centro de controlo</span>
                        <h1 class="mhs-page-title">Dashboard</h1>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="card mhs-kpi-card h-100"><div class="card-body"><div class="mhs-kpi-label">Categorias</div><p class="mhs-kpi-meta mb-0">Gestão de categorias de equipamentos.</p><a href="views/categorias/lista.php" class="btn btn-outline-primary mt-3">Abrir</a></div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card mhs-kpi-card h-100"><div class="card-body"><div class="mhs-kpi-label">Equipamentos</div><p class="mhs-kpi-meta mb-0">Inventário de equipamentos.</p><a href="views/equipamentos/lista.php" class="btn btn-outline-primary mt-3">Abrir</a></div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card mhs-kpi-card h-100"><div class="card-body"><div class="mhs-kpi-label">Fornecedores</div><p class="mhs-kpi-meta mb-0">Lista de fornecedores e contactos.</p><a href="views/fornecedores/lista.php" class="btn btn-outline-primary mt-3">Abrir</a></div></div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card mhs-kpi-card h-100"><div class="card-body"><div class="mhs-kpi-label">Pesquisa</div><p class="mhs-kpi-meta mb-0">Pesquisa avançada de registos.</p><a href="views/pesquisa/pesquisa.php" class="btn btn-outline-primary mt-3">Abrir</a></div></div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
