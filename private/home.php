<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

// Conexão com BD
try {
    $mysqli = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
    if ($mysqli->connect_error) {
        die('Erro na conexão: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
} catch (Exception $e) {
    die('Erro: ' . $e->getMessage());
}

// Queries para os dados dos gráficos
$equipamentos_estado = [];
$equipamentos_categoria = [];
$documentos_tipo = [];
$fornecedores_uso = [];
$localizacoes_uso = [];
$garantias_vencimento = [];

// Equipamentos por estado
$result = $mysqli->query("SELECT estado, COUNT(*) as total FROM equipamentos GROUP BY estado");
while ($row = $result->fetch_assoc()) {
    $equipamentos_estado[] = $row;
}

// Equipamentos por categoria
$result = $mysqli->query("
    SELECT c.nome, COUNT(e.id) as total 
    FROM categorias c 
    LEFT JOIN equipamentos e ON c.id = e.categoria_id 
    GROUP BY c.id, c.nome 
    ORDER BY total DESC
");
while ($row = $result->fetch_assoc()) {
    $equipamentos_categoria[] = $row;
}

// Documentos por tipo
$result = $mysqli->query("SELECT tipo, COUNT(*) as total FROM documentos GROUP BY tipo ORDER BY total DESC");
while ($row = $result->fetch_assoc()) {
    $documentos_tipo[] = $row;
}

// Fornecedores mais usados
$result = $mysqli->query("
    SELECT f.nome, COUNT(e.id) as total 
    FROM fornecedores f 
    LEFT JOIN equipamentos e ON f.id = e.fornecedor_id 
    GROUP BY f.id, f.nome 
    ORDER BY total DESC 
    LIMIT 8
");
while ($row = $result->fetch_assoc()) {
    $fornecedores_uso[] = $row;
}

// Localizações mais usadas
$result = $mysqli->query("
    SELECT l.nome, COUNT(e.id) as total 
    FROM localizacoes l 
    LEFT JOIN equipamentos e ON l.id = e.localizacao_id 
    GROUP BY l.id, l.nome 
    ORDER BY total DESC 
    LIMIT 6
");
while ($row = $result->fetch_assoc()) {
    $localizacoes_uso[] = $row;
}

// Garantias por mês de vencimento
$result = $mysqli->query("
    SELECT DATE_FORMAT(data_validade, '%Y-%m') as mes, COUNT(*) as total 
    FROM garantias_contrato 
    WHERE data_validade IS NOT NULL 
    GROUP BY DATE_FORMAT(data_validade, '%Y-%m') 
    ORDER BY mes ASC 
    LIMIT 12
");
while ($row = $result->fetch_assoc()) {
    $garantias_vencimento[] = $row;
}

// Estatísticas gerais
$stats_equipamentos = $mysqli->query("SELECT COUNT(*) as total FROM equipamentos")->fetch_assoc()['total'];
$stats_documentos = $mysqli->query("SELECT COUNT(*) as total FROM documentos")->fetch_assoc()['total'];
$stats_fornecedores = $mysqli->query("SELECT COUNT(*) as total FROM fornecedores")->fetch_assoc()['total'];
$stats_garantias = $mysqli->query("SELECT COUNT(*) as total FROM garantias_contrato WHERE data_validade > NOW()")->fetch_assoc()['total'];
$stats_criticos = $mysqli->query("SELECT COUNT(*) as total FROM equipamentos WHERE prioridade = 'Crítica'")->fetch_assoc()['total'];
$stats_inativos = $mysqli->query("SELECT COUNT(*) as total FROM equipamentos WHERE estado = 'Inativo'")->fetch_assoc()['total'];

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<style>
    .mhs-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .mhs-dashboard-grid.full {
        grid-template-columns: 1fr;
    }

    .mhs-chart-container {
        background: white;
        border-radius: 0.75rem;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        position: relative;
        min-height: 400px;
    }

    .mhs-chart-container h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mhs-chart-container canvas {
        max-height: 350px;
    }

    .mhs-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .mhs-stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 0.75rem;
        text-align: center;
    }

    .mhs-stat-card.blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .mhs-stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .mhs-stat-card.orange {
        background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
    }

    .mhs-stat-card.yellow {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .mhs-stat-card.purple {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .mhs-stat-card.red {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .mhs-stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .mhs-stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    .mhs-page-header--dashboard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        margin: -2rem -2rem 2rem -2rem;
        border-radius: 0 0 1rem 1rem;
    }

    .mhs-page-header--dashboard .mhs-page-title {
        color: white;
        font-size: 2.5rem;
        margin-top: 0.5rem;
    }

    .mhs-page-header--dashboard .mhs-page-copy {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1rem;
    }

    .mhs-chart-icon {
        font-size: 1.2rem;
    }
</style>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker">Centro de controlo</span>
        <h1 class="mhs-page-title">Dashboard Analítico</h1>
        <p class="mhs-page-copy">Visão geral de equipamentos, documentos e análises operacionais</p>
    </div>
</div>

<!-- Estatísticas Gerais -->
<div class="mhs-stats-grid">
    <div class="mhs-stat-card blue">
        <div class="mhs-stat-label">Equipamentos</div>
        <div class="mhs-stat-number"><?= $stats_equipamentos ?></div>
    </div>
    <div class="mhs-stat-card green">
        <div class="mhs-stat-label">Documentos</div>
        <div class="mhs-stat-number"><?= $stats_documentos ?></div>
    </div>
    <div class="mhs-stat-card orange">
        <div class="mhs-stat-label">Fornecedores</div>
        <div class="mhs-stat-number"><?= $stats_fornecedores ?></div>
    </div>
    <div class="mhs-stat-card yellow">
        <div class="mhs-stat-label">Garantias Ativas</div>
        <div class="mhs-stat-number"><?= $stats_garantias ?></div>
    </div>
    <div class="mhs-stat-card purple">
        <div class="mhs-stat-label">Críticos</div>
        <div class="mhs-stat-number"><?= $stats_criticos ?></div>
    </div>
    <div class="mhs-stat-card red">
        <div class="mhs-stat-label">Inativos</div>
        <div class="mhs-stat-number"><?= $stats_inativos ?></div>
    </div>
</div>

<!-- Gráficos -->
<div class="mhs-dashboard-grid">
    <!-- Estados dos Equipamentos -->
    <div class="mhs-chart-container">
        <h3><i class="fas fa-chart-pie mhs-chart-icon"></i> Estado dos Equipamentos</h3>
        <canvas id="chartEstados"></canvas>
    </div>

    <!-- Equipamentos por Categoria -->
    <div class="mhs-chart-container">
        <h3><i class="fas fa-chart-bar mhs-chart-icon"></i> Equipamentos por Categoria</h3>
        <canvas id="chartCategorias"></canvas>
    </div>

    <!-- Equipamentos por Localização -->
    <div class="mhs-chart-container">
        <h3><i class="fas fa-chart-doughnut mhs-chart-icon"></i> Equipamentos por Localização</h3>
        <canvas id="chartLocalizacoes"></canvas>
    </div>

    <!-- Documentos por Tipo -->
    <div class="mhs-chart-container">
        <h3><i class="fas fa-file-chart-line mhs-chart-icon"></i> Documentos por Tipo</h3>
        <canvas id="chartDocumentos"></canvas>
    </div>

    <!-- Fornecedores Mais Utilizados -->
    <div class="mhs-chart-container full">
        <h3><i class="fas fa-chart-bar mhs-chart-icon"></i> Fornecedores Mais Utilizados</h3>
        <canvas id="chartFornecedores"></canvas>
    </div>

    <!-- Garantias por Vencimento -->
    <div class="mhs-chart-container full">
        <h3><i class="fas fa-calendar-check mhs-chart-icon"></i> Garantias - Calendário de Vencimentos</h3>
        <canvas id="chartGarantias"></canvas>
    </div>
</div>

<script>
    // Cores
    const colors = {
        primary: '#667eea',
        success: '#11998e',
        warning: '#ff8e53',
        danger: '#ff6b6b',
        info: '#4facfe',
        accent: '#f093fb'
    };

    const chartColors = [
        '#667eea', '#764ba2', '#11998e', '#38ef7d', '#ff8e53', '#f093fb', '#4facfe', '#00f2fe'
    ];

    // 1. Estado dos Equipamentos - Pizza
    new Chart(document.getElementById('chartEstados'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($equipamentos_estado, 'estado')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($equipamentos_estado, 'total')) ?>,
                backgroundColor: chartColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, font: { size: 12, weight: '500' } }
                }
            }
        }
    });

    // 2. Equipamentos por Categoria - Barras
    new Chart(document.getElementById('chartCategorias'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($equipamentos_categoria, 'nome')) ?>,
            datasets: [{
                label: 'Quantidade',
                data: <?= json_encode(array_column($equipamentos_categoria, 'total')) ?>,
                backgroundColor: chartColors[0],
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // 3. Equipamentos por Localização - Doughnut
    new Chart(document.getElementById('chartLocalizacoes'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($localizacoes_uso, 'nome')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($localizacoes_uso, 'total')) ?>,
                backgroundColor: chartColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, font: { size: 12, weight: '500' } }
                }
            }
        }
    });

    // 4. Documentos por Tipo - Doughnut
    new Chart(document.getElementById('chartDocumentos'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($documentos_tipo, 'tipo')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($documentos_tipo, 'total')) ?>,
                backgroundColor: chartColors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, font: { size: 12, weight: '500' } }
                }
            }
        }
    });

    // 5. Fornecedores - Barras Horizontal
    new Chart(document.getElementById('chartFornecedores'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($fornecedores_uso, 'nome')) ?>,
            datasets: [{
                label: 'Equipamentos',
                data: <?= json_encode(array_column($fornecedores_uso, 'total')) ?>,
                backgroundColor: chartColors,
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });

    // 6. Garantias - Linha Temporal
    new Chart(document.getElementById('chartGarantias'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($garantias_vencimento, 'mes')) ?>,
            datasets: [{
                label: 'Garantias Vencimento',
                data: <?= json_encode(array_column($garantias_vencimento, 'total')) ?>,
                borderColor: colors.primary,
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { display: true }
            }
        }
    });
</script>

<?php $mysqli->close(); include __DIR__ . '/includes/footer.php'; ?>
