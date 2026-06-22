<?php
/**
 * Dashboard (painel principal)
 * Página inicial após o login. Mostra um resumo do estado do sistema:
 * - Contadores rápidos (equipamentos, garantias expiradas, sem documentação, etc.)
 * - Gráficos com distribuição por estado, categoria, localização, documentos e fornecedores
 * - Os gráficos são clicáveis e redirecionam para as listas filtradas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/includes/funcoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// ─── Ligação à base de dados (usando MySQLi para as queries do dashboard) ────
try {
    $mysqli = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE, MYSQL_PORT);
    if ($mysqli->connect_error) throw new \RuntimeException('Erro na conexão: ' . $mysqli->connect_error);
    $mysqli->set_charset('utf8mb4');
} catch (Exception $e) {
    throw new \RuntimeException($e->getMessage(), 0, $e);
}

// Arrays para guardar os dados de cada gráfico
$equipamentos_estado    = [];
$equipamentos_categoria = [];
$equipamentos_servico   = [];
$fornecedores_uso       = [];
$localizacoes_uso       = [];
$garantias_vencimento   = [];

// ─── Dados para os gráficos ───────────────────────────────────────────────────

// Equipamentos por estado (Ativo, Em manutenção, Inativo, etc.)
$r = $mysqli->query("SELECT estado, COUNT(*) as total FROM equipamentos WHERE eliminado_em IS NULL GROUP BY estado ORDER BY total DESC");
while ($row = $r->fetch_assoc()) $equipamentos_estado[] = $row;

// Equipamentos por categoria
$r = $mysqli->query("SELECT c.id, c.nome, COUNT(e.id) as total FROM categorias c LEFT JOIN equipamentos e ON c.id = e.id_categoria AND e.eliminado_em IS NULL GROUP BY c.id, c.nome ORDER BY total DESC");
while ($row = $r->fetch_assoc()) $equipamentos_categoria[] = $row;

// Equipamentos por serviço (agrupados pelo serviço da localização)
$_dash_profile = $_SESSION['profile'] ?? '';
$r = $mysqli->query("
    SELECT l.servico AS nome, COUNT(e.id) AS total
    FROM localizacoes l
    JOIN equipamentos e ON e.id_localizacao = l.id AND e.eliminado_em IS NULL
    WHERE l.servico IS NOT NULL AND l.servico != ''
    GROUP BY l.servico
    ORDER BY total DESC
");
while ($row = $r->fetch_assoc()) $equipamentos_servico[] = $row;

// Equipamentos por fornecedor (campo fabricante — atualiza ao adicionar equipamento)
$r = $mysqli->query("
    SELECT fabricante AS nome, COUNT(*) as total
    FROM equipamentos
    WHERE eliminado_em IS NULL AND fabricante IS NOT NULL AND fabricante != ''
    GROUP BY fabricante
    ORDER BY total DESC
    LIMIT 8
");
while ($row = $r->fetch_assoc()) $fornecedores_uso[] = $row;

// Equipamentos por edifício
$r = $mysqli->query("
    SELECT l.edificio AS nome, COUNT(e.id) as total
    FROM localizacoes l
    JOIN equipamentos e ON e.id_localizacao = l.id AND e.eliminado_em IS NULL
    WHERE l.edificio IS NOT NULL AND l.edificio != ''
    GROUP BY l.edificio
    HAVING total > 0
    ORDER BY total DESC
    LIMIT 8
");
while ($row = $r->fetch_assoc()) $localizacoes_uso[] = $row;

// Garantias a vencer nos próximos 12 meses (timeline contínua, só para administradores)
if ($_dash_profile === 'admin') {
    $venc_map = [];
    $r = $mysqli->query("
        SELECT DATE_FORMAT(data_fim,'%Y-%m') as mes, COUNT(*) as total
        FROM garantias_contratos
        WHERE data_fim IS NOT NULL AND eliminado_em IS NULL
          AND data_fim >= DATE_FORMAT(CURDATE(),'%Y-%m-01')
          AND data_fim < DATE_ADD(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_fim,'%Y-%m')
    ");
    if ($r) while ($row = $r->fetch_assoc()) $venc_map[$row['mes']] = (int)$row['total'];
    // Construir os 12 meses contínuos a partir do mês atual (meses sem vencimentos ficam a 0)
    for ($i = 0; $i < 12; $i++) {
        $m = date('Y-m', strtotime("first day of this month +$i month"));
        $garantias_vencimento[] = ['mes' => $m, 'total' => $venc_map[$m] ?? 0];
    }
}

// ─── Contadores para os cartões de resumo (KPIs) ─────────────────────────────

// Total de equipamentos ativos no sistema
$stats_equipamentos = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
// Equipamentos com estado "Ativo"
$stats_ativos       = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE estado='Ativo' AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
// Equipamentos em manutenção
$stats_manutencao   = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE estado='Em manutenção' AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
// Equipamentos inativos
$stats_inativos     = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE estado='Inativo' AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
// Equipamentos com documentação incompleta (principal=7 tipos, componente=3 tipos)
$_t7 = "'Manual de utilizador','Manual de serviço','Certificado de calibração','Contrato de manutenção','Fatura / Guia de aquisição','Declaração de conformidade','Relatório técnico'";
$_t3 = "'Manual de utilizador','Declaração de conformidade','Relatório técnico'";
$stats_sem_doc      = (int)$mysqli->query("
    SELECT COUNT(*) AS t FROM (
        SELECT e.id FROM equipamentos e
        WHERE e.eliminado_em IS NULL AND e.id_equipamento_pai IS NULL AND e.ativo=1
        AND (SELECT COUNT(DISTINCT d.tipo_documento) FROM documentos d
             WHERE d.id_equipamento=e.id AND d.eliminado_em IS NULL AND d.ficheiro_conteudo IS NOT NULL
             AND d.tipo_documento IN ($_t7)) < 7
        UNION ALL
        SELECT e.id FROM equipamentos e
        WHERE e.eliminado_em IS NULL AND e.id_equipamento_pai IS NOT NULL AND e.ativo=1
        AND (SELECT COUNT(DISTINCT d.tipo_documento) FROM documentos d
             WHERE d.id_equipamento=e.id AND d.eliminado_em IS NULL AND d.ficheiro_conteudo IS NOT NULL
             AND d.tipo_documento IN ($_t3)) < 3
    ) sub
")->fetch_assoc()['t'];
// Equipamentos com criticidade alta
$stats_criticos     = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE criticidade='Alta' AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];

// Garantias expiradas e ativas (só visíveis para administradores)
if ($_dash_profile === 'admin') {
    $stats_garantias_expiradas = (int)$mysqli->query("SELECT COUNT(*) as t FROM garantias_contratos WHERE data_fim < CURDATE() AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
    $stats_garantias           = (int)$mysqli->query("SELECT COUNT(*) as t FROM garantias_contratos WHERE data_fim > NOW() AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
    $stats_garantias_30dias    = (int)$mysqli->query("SELECT COUNT(*) as t FROM garantias_contratos WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
} else {
    $stats_garantias_expiradas = 0;
    $stats_garantias           = 0;
    $stats_garantias_30dias    = 0;
}

// Equipamentos de suporte de vida por serviço
$suporte_vida_servico = [];
$r = $mysqli->query("SELECT COALESCE(l.servico,'Sem localização') AS servico, COUNT(e.id) AS total
    FROM equipamentos e
    LEFT JOIN categorias c ON c.id = e.id_categoria
    LEFT JOIN localizacoes l ON l.id = e.id_localizacao
    WHERE (e.criticidade LIKE 'Suporte%' OR c.nome LIKE 'Suporte%') AND e.eliminado_em IS NULL
    GROUP BY l.servico ORDER BY total DESC");
if ($r) while ($row = $r->fetch_assoc()) $suporte_vida_servico[] = $row;
$sv_total = array_sum(array_map(fn($x) => (int)$x['total'], $suporte_vida_servico));

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<!-- estilos da dashboard movidos para private/assets/css/1220673.css -->

<!-- ════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<div class="dash-hero">
    <div class="dash-hero-top">
        <div>
            <h1 class="dash-hero-title">Dashboard</h1>
        </div>
    </div>

    <!-- Stats strip: big numbers clicáveis -> listas filtradas -->
    <?php $EQ = BASE_URL . '/private/views/equipamentos/lista.php'; $GAR = BASE_URL . '/private/views/garantias-contrato/lista.php'; ?>
    <div class="dash-stats">
        <a href="<?= $EQ ?>" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-stethoscope"></i> &nbsp;Equipamentos</div>
            <div class="dash-stat-num" data-count="<?= $stats_equipamentos ?>">0</div>
            <div class="dash-stat-lbl">Total registado</div>
        </a>
        <a href="<?= $EQ ?>?estado=Ativo" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-circle-check"></i> &nbsp;Ativos</div>
            <div class="dash-stat-num" data-count="<?= $stats_ativos ?>">0</div>
            <div class="dash-stat-lbl">Em operação</div>
        </a>
        <a href="<?= $EQ ?>?estado=<?= rawurlencode('Em manutenção') ?>" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-wrench"></i> &nbsp;Manutenção</div>
            <div class="dash-stat-num" data-count="<?= $stats_manutencao ?>">0</div>
            <div class="dash-stat-lbl">Em intervenção</div>
        </a>
        <a href="<?= $EQ ?>?estado=Inativo" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-circle-xmark"></i> &nbsp;Inativos</div>
            <div class="dash-stat-num" data-count="<?= $stats_inativos ?>">0</div>
            <div class="dash-stat-lbl">Fora de serviço</div>
        </a>
        <?php if ($_dash_profile === 'admin'): ?>
        <a href="<?= $GAR ?>?filtro=vigor" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-shield-halved"></i> &nbsp;Garantias</div>
            <div class="dash-stat-num" data-count="<?= $stats_garantias ?>">0</div>
            <div class="dash-stat-lbl">Ainda em vigor</div>
        </a>
        <a href="<?= $GAR ?>?filtro=expiradas" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-calendar-xmark"></i> &nbsp;Expiradas</div>
            <div class="dash-stat-num" data-count="<?= $stats_garantias_expiradas ?>">0</div>
            <div class="dash-stat-lbl">Garantias expiradas</div>
        </a>
        <?php endif; ?>
        <a href="<?= $EQ ?>?filtro=docs_incompletos" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-file-circle-exclamation"></i> &nbsp;Docs em Falta</div>
            <div class="dash-stat-num" data-count="<?= $stats_sem_doc ?>">0</div>
            <div class="dash-stat-lbl">Documentação incompleta</div>
        </a>
        <a href="<?= $EQ ?>?criticidade=Alta" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-triangle-exclamation"></i> &nbsp;Críticos</div>
            <div class="dash-stat-num" data-count="<?= $stats_criticos ?>">0</div>
            <div class="dash-stat-lbl">Criticidade alta</div>
        </a>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     ROW 1 — Estados + Categorias
════════════════════════════════════════════════════════════ -->
<div class="dash-grid-2">

    <div class="dash-panel" style="transition-delay:.05s">
        <div class="dash-panel-head">
            <h3 class="dash-panel-title">
                <i class="fas fa-circle-half-stroke"></i> Estado dos Equipamentos
            </h3>
            <div class="dash-toggle">
                <button class="dash-toggle-btn active" data-canvas="cEstados" data-type="pie">Setores</button>
                <button class="dash-toggle-btn" data-canvas="cEstados" data-type="doughnut">Anel</button>
            </div>
        </div>
        <div class="dash-panel-body loading" id="body-cEstados">
            <canvas id="cEstados"></canvas>
        </div>
    </div>

    <div class="dash-panel" style="transition-delay:.1s">
        <div class="dash-panel-head">
            <h3 class="dash-panel-title">
                <i class="fas fa-layer-group"></i> Equipamentos por Categoria
            </h3>
            <div class="dash-toggle">
                <button class="dash-toggle-btn active" data-canvas="cCategorias" data-type="bar-h">Barras</button>
                <button class="dash-toggle-btn" data-canvas="cCategorias" data-type="bar">Colunas</button>
            </div>
        </div>
        <div class="dash-panel-body loading" id="body-cCategorias">
            <canvas id="cCategorias"></canvas>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     ROW 2 — Localizações + Documentos
════════════════════════════════════════════════════════════ -->
<div class="dash-grid-32 dash-gap">

    <div class="dash-panel" style="transition-delay:.15s">
        <div class="dash-panel-head">
            <h3 class="dash-panel-title">
                <i class="fas fa-map-location-dot"></i> Distribuição por Edifício
            </h3>
            <div class="dash-toggle">
                <button class="dash-toggle-btn active" data-canvas="cLocalizacoes" data-type="doughnut">Anel</button>
                <button class="dash-toggle-btn" data-canvas="cLocalizacoes" data-type="pie">Setores</button>
                <button class="dash-toggle-btn" data-canvas="cLocalizacoes" data-type="bar-h">Barras</button>
            </div>
        </div>
        <div class="dash-panel-body loading" id="body-cLocalizacoes">
            <canvas id="cLocalizacoes"></canvas>
        </div>
    </div>

    <div class="dash-panel" style="transition-delay:.2s">
        <div class="dash-panel-head">
            <h3 class="dash-panel-title">
                <i class="fas fa-hospital"></i> Equipamentos por Serviço
            </h3>
            <div class="dash-toggle">
                <button class="dash-toggle-btn active" data-canvas="cDocumentos" data-type="doughnut">Anel</button>
                <button class="dash-toggle-btn" data-canvas="cDocumentos" data-type="bar">Colunas</button>
            </div>
        </div>
        <div class="dash-panel-body loading" id="body-cDocumentos">
            <canvas id="cDocumentos"></canvas>
        </div>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     ROW 3 — Fornecedores (full width)
════════════════════════════════════════════════════════════ -->
<div class="dash-panel dash-gap" style="transition-delay:.25s">
    <div class="dash-panel-head">
        <h3 class="dash-panel-title">
            <i class="fas fa-building-columns"></i> Equipamentos por Fornecedor
        </h3>
        <div class="dash-toggle">
            <button class="dash-toggle-btn active" data-canvas="cFornecedores" data-type="bar-h">Barras</button>
            <button class="dash-toggle-btn" data-canvas="cFornecedores" data-type="bar">Colunas</button>
        </div>
    </div>
    <div class="dash-panel-body loading" id="body-cFornecedores">
        <canvas id="cFornecedores" style="max-height:280px"></canvas>
    </div>
</div>

<?php if ($_dash_profile === 'admin'): ?>
<!-- ════════════════════════════════════════════════════════════
     ROW 4 — Garantias timeline (full width)
════════════════════════════════════════════════════════════ -->
<div class="dash-panel dash-gap" style="transition-delay:.3s;margin-bottom:1.5rem">
    <div class="dash-panel-head">
        <h3 class="dash-panel-title">
            <i class="fas fa-calendar-days"></i> Vencimentos de Garantias por Mês
        </h3>
        <div class="dash-toggle">
            <button class="dash-toggle-btn active" data-canvas="cGarantias" data-type="line">Série Temporal</button>
            <button class="dash-toggle-btn" data-canvas="cGarantias" data-type="bar">Colunas</button>
        </div>
    </div>
    <div class="dash-panel-body loading" id="body-cGarantias">
        <canvas id="cGarantias" style="max-height:280px"></canvas>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════
     ROW 5 — Suporte de vida por serviço
════════════════════════════════════════════════════════════ -->
<!-- estilos do monitor de suporte de vida movidos para private/assets/css/1220673.css -->
<div class="dash-panel mhs-vitals dash-gap" style="transition-delay:.35s">
    <div class="mhs-vitals-head">
        <span class="mhs-vitals-title"><i class="fas fa-heart-pulse"></i> Equipamentos de Suporte de Vida</span>
    </div>
    <?php if (empty($suporte_vida_servico)): ?>
    <div class="mhs-vitals-empty"><i class="fa-solid fa-heart-pulse me-2"></i>Sem equipamentos de suporte de vida registados.</div>
    <?php else: ?>
    <div class="mhs-vitals-body">
        <div class="mhs-vitals-ecg">
            <svg viewBox="0 0 600 170" preserveAspectRatio="none">
                <path class="mhs-ecg-line" d="M0,85 L70,85 L82,72 L94,85 L150,85 L162,85 L172,103 L182,30 L192,130 L202,85 L260,85 L274,74 L288,85 L300,85
                    L370,85 L382,72 L394,85 L450,85 L462,85 L472,103 L482,30 L492,130 L502,85 L560,85 L574,74 L588,85 L600,85"/>
            </svg>
            <div class="mhs-vitals-count">
                <span class="num"><?= (int)$sv_total ?></span>
                <span class="lbl">equipamentos de<br>suporte de vida</span>
                <span class="bpm"><i class="fa-solid fa-heart-pulse"></i> em <?= count($suporte_vida_servico) ?> serviço<?= count($suporte_vida_servico) !== 1 ? 's' : '' ?></span>
            </div>
        </div>
        <div class="mhs-vitals-channels">
            <?php foreach ($suporte_vida_servico as $sv): ?>
            <a class="mhs-vch" href="<?= BASE_URL ?>/private/views/equipamentos/lista.php?servico=<?= urlencode($sv['servico']) ?>">
                <span class="mhs-vch-dot"></span>
                <span class="mhs-vch-name"><?= htmlspecialchars($sv['servico']) ?></span>
                <span class="mhs-vch-val"><?= (int)$sv['total'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
/* Dados da dashboard para o motor de gráficos (private/assets/js/1220673.js) */
window.MHS_DASH = {
    chartData: {
        cEstados:      { labels: <?= json_encode(array_column($equipamentos_estado,   'estado')) ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_estado,   'total'))) ?> },
        cCategorias:   { labels: <?= json_encode(array_column($equipamentos_categoria,'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_categoria,'total'))) ?> },
        cLocalizacoes: { labels: <?= json_encode(array_column($localizacoes_uso,      'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($localizacoes_uso,      'total'))) ?> },
        cDocumentos:   { labels: <?= json_encode(array_column($equipamentos_servico,   'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_servico,   'total'))) ?> },
        cFornecedores: { labels: <?= json_encode(array_column($fornecedores_uso,      'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($fornecedores_uso,      'total'))) ?> },
        cGarantias:    { labels: <?= json_encode(array_column($garantias_vencimento,  'mes'))    ?>, values: <?= json_encode(array_map('intval', array_column($garantias_vencimento,  'total'))) ?> }
    },
    chartIds: {
        cCategorias:   <?= json_encode(array_values(array_column($equipamentos_categoria, 'id'))) ?>,
        cLocalizacoes: <?= json_encode(array_values(array_column($localizacoes_uso,       'id'))) ?>
    },
    base: '<?= BASE_URL ?>'
};
</script>

<?php $mysqli->close(); include __DIR__ . '/includes/footer.php'; ?>
