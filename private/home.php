<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$page_title = 'Dashboard';

$is_admin = ($_SESSION['profile'] ?? '') === 'admin';

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

$total = $ativos = $em_manutencao = $inativos = 0;
$garantias_expiradas = $garantias_proximas = $sem_docs = $criticos = 0;
$por_servico = $por_categoria = [];
$erro_bd = '';
$taxa_operacional = 0;
$max_servico_total = 0;
$max_categoria_total = 0;

try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar se tabelas existem
    $tableCheckStmt = $pdo->query("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '" . MYSQL_DATABASE . "' 
        AND TABLE_NAME IN ('equipamentos', 'categorias', 'localizacoes', 'garantias_contratos', 'documentos')
    ");
    $table_count = $tableCheckStmt->fetchColumn();

    if ($table_count === 5) {
        // Queries apenas se todas as tabelas existem
        $total = $pdo->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
        $ativos = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'Ativo'")->fetchColumn();
        $em_manutencao = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado LIKE 'Em manuten%'")->fetchColumn();
        $inativos = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado IN ('Inativo','Abatido')")->fetchColumn();
        $criticos = $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE criticidade IN ('Alta','Suporte de vida')")->fetchColumn();

        $garantias_expiradas = $pdo->query("
            SELECT COUNT(*) FROM garantias_contratos
            WHERE data_fim < CURDATE()
        ")->fetchColumn();

        $garantias_proximas = $pdo->query("
            SELECT COUNT(*) FROM garantias_contratos
            WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ")->fetchColumn();

        $sem_docs = $pdo->query("
            SELECT COUNT(*) FROM equipamentos e
            LEFT JOIN documentos d ON e.id = d.id_equipamento
            WHERE d.id IS NULL
        ")->fetchColumn();

        $por_servico = $pdo->query("
            SELECT l.servico, COUNT(e.id) AS total
            FROM equipamentos e
            JOIN localizacoes l ON e.id_localizacao = l.id
            GROUP BY l.servico
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_OBJ);

        $por_categoria = $pdo->query("
            SELECT c.nome AS categoria, COUNT(e.id) AS total
            FROM equipamentos e
            JOIN categorias c ON e.id_categoria = c.id
            GROUP BY c.nome
            ORDER BY total DESC
        ")->fetchAll(PDO::FETCH_OBJ);
    }
} catch (PDOException $e) {
    $erro_bd = 'Não foi possível carregar os indicadores da base de dados.';
}

if ((int) $total > 0) {
    $taxa_operacional = (int) round(($ativos / $total) * 100);
}

foreach ($por_servico as $row) {
    $max_servico_total = max($max_servico_total, (int) $row->total);
}

foreach ($por_categoria as $row) {
    $max_categoria_total = max($max_categoria_total, (int) $row->total);
}

include __DIR__ . '/includes/header.php';
?>

<?php if (!empty($success_message)) : ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?= htmlspecialchars($success_message) ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker">Centro de controlo</span>
        <h1 class="mhs-page-title">Dashboard</h1>
        <p class="mhs-page-copy">Acompanhe disponibilidade, alertas operacionais e distribuição do parque tecnológico hospitalar.</p>
    </div>
    <div class="mhs-page-actions">
        <div class="mhs-inline-stat">
            <span>Disponibilidade atual</span>
            <strong><?= $taxa_operacional ?>%</strong>
            <small><?= $ativos ?> de <?= $total ?> equipamentos ativos</small>
        </div>
        <a href="views/equipamentos/novo.php" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Novo Equipamento
        </a>
    </div>
</div>

<?php if ($erro_bd) : ?>
    <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($erro_bd) ?></div>
<?php endif; ?>

<?php if ($error_message) : ?>
    <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card mhs-kpi-card h-100">
            <div class="card-body">
                <div class="mhs-kpi-head">
                    <span class="mhs-kpi-icon mhs-kpi-icon--primary">
                        <i class="fa-solid fa-stethoscope"></i>
                    </span>
                    <span class="mhs-kpi-chip">Inventário</span>
                </div>
                <div class="mhs-kpi-value"><?= $total ?></div>
                <div class="mhs-kpi-label">Total de equipamentos</div>
                <p class="mhs-kpi-meta mb-0">Parque tecnológico registado na base ativa.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card mhs-kpi-card h-100">
            <div class="card-body">
                <div class="mhs-kpi-head">
                    <span class="mhs-kpi-icon mhs-kpi-icon--success">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                    <span class="mhs-kpi-chip">Estado</span>
                </div>
                <div class="mhs-kpi-value"><?= $ativos ?></div>
                <div class="mhs-kpi-label">Equipamentos ativos</div>
                <p class="mhs-kpi-meta mb-0"><?= $taxa_operacional ?>% de disponibilidade sobre o total.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card mhs-kpi-card h-100">
            <div class="card-body">
                <div class="mhs-kpi-head">
                    <span class="mhs-kpi-icon mhs-kpi-icon--warning">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </span>
                    <span class="mhs-kpi-chip">Manutenção</span>
                </div>
                <div class="mhs-kpi-value"><?= $em_manutencao ?></div>
                <div class="mhs-kpi-label">Intervenções em curso</div>
                <p class="mhs-kpi-meta mb-0">Equipamentos a requerer seguimento técnico imediato.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card mhs-kpi-card h-100">
            <div class="card-body">
                <div class="mhs-kpi-head">
                    <span class="mhs-kpi-icon mhs-kpi-icon--danger">
                        <i class="fa-solid fa-ban"></i>
                    </span>
                    <span class="mhs-kpi-chip">Risco</span>
                </div>
                <div class="mhs-kpi-value"><?= $inativos ?></div>
                <div class="mhs-kpi-label">Inativos ou abatidos</div>
                <p class="mhs-kpi-meta mb-0"><?= $criticos ?> equipamentos com criticidade alta ou suporte de vida.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Alertas prioritários</span>
                <span class="mhs-card-meta">Monitorização contínua</span>
            </div>
            <div class="card-body">
                <div class="mhs-alert-stack">
                    <?php if ($garantias_expiradas > 0) : ?>
                        <a href="views/garantias-contrato/lista.php" class="mhs-alert-item">
                            <span class="mhs-alert-icon mhs-alert-icon--danger"><i class="fa-solid fa-circle-exclamation"></i></span>
                            <div>
                                <strong><?= $garantias_expiradas ?> garantia(s) expirada(s)</strong>
                                <p>Renovação ou revisão contratual em atraso.</p>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($garantias_proximas > 0) : ?>
                        <a href="views/garantias-contrato/lista.php" class="mhs-alert-item">
                            <span class="mhs-alert-icon mhs-alert-icon--warning"><i class="fa-regular fa-clock"></i></span>
                            <div>
                                <strong><?= $garantias_proximas ?> garantia(s) a expirar</strong>
                                <p>Janela de 30 dias para atuar preventivamente.</p>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($sem_docs > 0) : ?>
                        <a href="views/documentos/lista.php" class="mhs-alert-item">
                            <span class="mhs-alert-icon mhs-alert-icon--primary"><i class="fa-solid fa-file-circle-xmark"></i></span>
                            <div>
                                <strong><?= $sem_docs ?> equipamento(s) sem documentação</strong>
                                <p>Falta de manuais, certificados ou anexos técnicos.</p>
                            </div>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>

                    <?php if ($criticos > 0) : ?>
                        <div class="mhs-alert-item mhs-alert-item--static">
                            <span class="mhs-alert-icon mhs-alert-icon--dark"><i class="fa-solid fa-heart-pulse"></i></span>
                            <div>
                                <strong><?= $criticos ?> equipamento(s) críticos</strong>
                                <p>Alta criticidade ou suporte de vida sob vigilância reforçada.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($garantias_expiradas == 0 && $garantias_proximas == 0 && $sem_docs == 0 && $criticos == 0) : ?>
                        <div class="mhs-empty-inline">
                            <i class="fa-solid fa-circle-check"></i>
                            <div>
                                <strong>Sem alertas ativos</strong>
                                <p>O sistema não detectou pendências críticas neste momento.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Resumo operacional</span>
                <span class="mhs-card-meta">Leitura rápida</span>
            </div>
            <div class="card-body">
                <div class="mhs-metric-strip">
                    <div class="mhs-metric-strip-item">
                        <span>Garantias expiradas</span>
                        <strong><?= $garantias_expiradas ?></strong>
                    </div>
                    <div class="mhs-metric-strip-item">
                        <span>Garantias a 30 dias</span>
                        <strong><?= $garantias_proximas ?></strong>
                    </div>
                    <div class="mhs-metric-strip-item">
                        <span>Sem documentação</span>
                        <strong><?= $sem_docs ?></strong>
                    </div>
                    <div class="mhs-metric-strip-item">
                        <span>Críticos</span>
                        <strong><?= $criticos ?></strong>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="views/equipamentos/lista.php" class="mhs-quick-link">
                            <span class="mhs-quick-link-icon"><i class="fa-solid fa-stethoscope"></i></span>
                            <div>
                                <strong>Inventário</strong>
                                <p>Consultar registos, estados e criticidade.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="views/pesquisa/pesquisa.php" class="mhs-quick-link">
                            <span class="mhs-quick-link-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <div>
                                <strong>Pesquisa avançada</strong>
                                <p>Filtrar por código, estado, serviço ou fornecedor.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="views/documentos/lista.php" class="mhs-quick-link">
                            <span class="mhs-quick-link-icon"><i class="fa-solid fa-file-lines"></i></span>
                            <div>
                                <strong>Documentação</strong>
                                <p>Centralizar anexos técnicos e certificados.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="views/garantias-contrato/lista.php" class="mhs-quick-link">
                            <span class="mhs-quick-link-icon"><i class="fa-solid fa-shield-halved"></i></span>
                            <div>
                                <strong>Contratos</strong>
                                <p>Rever cobertura, renovações e periodicidade.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
