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
$documentos_tipo        = [];
$fornecedores_uso       = [];
$localizacoes_uso       = [];
$garantias_vencimento   = [];

// ─── Dados para os gráficos ───────────────────────────────────────────────────

// Equipamentos por estado (Ativo, Em manutenção, Inativo, etc.)
$r = $mysqli->query("SELECT estado, COUNT(*) as total FROM equipamentos GROUP BY estado ORDER BY total DESC");
while ($row = $r->fetch_assoc()) $equipamentos_estado[] = $row;

// Equipamentos por categoria
$r = $mysqli->query("SELECT c.id, c.nome, COUNT(e.id) as total FROM categorias c LEFT JOIN equipamentos e ON c.id = e.id_categoria GROUP BY c.id, c.nome ORDER BY total DESC");
while ($row = $r->fetch_assoc()) $equipamentos_categoria[] = $row;

// Documentos por tipo (técnicos só veem documentos que não sejam contratos/garantias)
$_dash_profile = $_SESSION['profile'] ?? '';
$_doc_where = $_dash_profile === 'admin' ? '' : "WHERE tipo_documento NOT IN ('Contrato','Garantia')";
$r = $mysqli->query("SELECT tipo_documento as tipo, COUNT(*) as total FROM documentos $_doc_where GROUP BY tipo_documento ORDER BY total DESC");
while ($row = $r->fetch_assoc()) $documentos_tipo[] = $row;

// Top fornecedores com mais equipamentos associados
$r = $mysqli->query("SELECT f.nome, COUNT(ef.id_equipamento) as total FROM fornecedores f LEFT JOIN equipamentos_fornecedores ef ON f.id = ef.id_fornecedor GROUP BY f.id, f.nome ORDER BY total DESC LIMIT 8");
while ($row = $r->fetch_assoc()) $fornecedores_uso[] = $row;

// Localizações com mais equipamentos instalados
$r = $mysqli->query("SELECT l.id, CONCAT(l.servico, IF(l.sala IS NOT NULL AND l.sala != '', CONCAT(' · ', l.sala), '')) as nome, COUNT(e.id) as total FROM localizacoes l LEFT JOIN equipamentos e ON l.id = e.id_localizacao GROUP BY l.id ORDER BY total DESC LIMIT 6");
while ($row = $r->fetch_assoc()) $localizacoes_uso[] = $row;

// Garantias a vencer por mês (só para administradores)
if ($_dash_profile === 'admin') {
    $r = $mysqli->query("SELECT DATE_FORMAT(data_fim,'%Y-%m') as mes, COUNT(*) as total FROM garantias_contratos WHERE data_fim IS NOT NULL GROUP BY DATE_FORMAT(data_fim,'%Y-%m') ORDER BY mes ASC LIMIT 12");
    while ($row = $r->fetch_assoc()) $garantias_vencimento[] = $row;
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
// Equipamentos sem nenhum documento associado
$stats_sem_doc      = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos e WHERE e.ativo=1 AND e.eliminado_em IS NULL AND NOT EXISTS (SELECT 1 FROM documentos d WHERE d.id_equipamento=e.id AND d.ativo=1 AND d.eliminado_em IS NULL)")->fetch_assoc()['t'];
// Equipamentos com criticidade alta
$stats_criticos     = (int)$mysqli->query("SELECT COUNT(*) as t FROM equipamentos WHERE criticidade='Alta' AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];

// Garantias expiradas e ativas (só visíveis para administradores)
if ($_dash_profile === 'admin') {
    $stats_garantias_expiradas = (int)$mysqli->query("SELECT COUNT(*) as t FROM garantias_contratos WHERE data_fim < CURDATE() AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
    $stats_garantias           = (int)$mysqli->query("SELECT COUNT(*) as t FROM garantias_contratos WHERE data_fim > NOW() AND ativo=1 AND eliminado_em IS NULL")->fetch_assoc()['t'];
} else {
    $stats_garantias_expiradas = 0;
    $stats_garantias           = 0;
}

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<div class="dash-hero">
    <div class="dash-hero-top">
        <div>
            <h1 class="dash-hero-title">Dashboard</h1>
        </div>
        <a href="home.php" class="dash-refresh-btn" title="Recarregar dados">
            <i class="fas fa-rotate-right"></i> Atualizar
        </a>
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
        <a href="<?= $EQ ?>?filtro=sem_docs" class="dash-stat">
            <div class="dash-stat-icon"><i class="fas fa-file-circle-xmark"></i> &nbsp;Sem Docs</div>
            <div class="dash-stat-num" data-count="<?= $stats_sem_doc ?>">0</div>
            <div class="dash-stat-lbl">Sem documentação</div>
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
                <i class="fas fa-map-location-dot"></i> Distribuição por Localização
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
                <i class="fas fa-folder-tree"></i> Documentos por Tipo
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
            <i class="fas fa-building-columns"></i> Top Fornecedores por Equipamentos
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

<script>
(function () {
    /* ── Palette ─────────────────────────────────────────────── */
    const P = ['#3b82f6','#8b5cf6','#10b981','#f59e0b',
               '#ef4444','#06b6d4','#ec4899','#84cc16',
               '#f97316','#6366f1','#14b8a6','#a855f7'];

    /* ── Data from PHP ───────────────────────────────────────── */
    const chartData = {
        cEstados:      { labels: <?= json_encode(array_column($equipamentos_estado,   'estado')) ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_estado,   'total'))) ?> },
        cCategorias:   { labels: <?= json_encode(array_column($equipamentos_categoria,'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_categoria,'total'))) ?> },
        cLocalizacoes: { labels: <?= json_encode(array_column($localizacoes_uso,      'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($localizacoes_uso,      'total'))) ?> },
        cDocumentos:   { labels: <?= json_encode(array_column($documentos_tipo,       'tipo'))   ?>, values: <?= json_encode(array_map('intval', array_column($documentos_tipo,       'total'))) ?> },
        cFornecedores: { labels: <?= json_encode(array_column($fornecedores_uso,      'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($fornecedores_uso,      'total'))) ?> },
        cGarantias:    { labels: <?= json_encode(array_column($garantias_vencimento,  'mes'))    ?>, values: <?= json_encode(array_map('intval', array_column($garantias_vencimento,  'total'))) ?> }
    };

    /* ── IDs para navegação nos gráficos ────────────────────── */
    const chartIds = {
        cCategorias:   <?= json_encode(array_values(array_column($equipamentos_categoria, 'id'))) ?>,
        cLocalizacoes: <?= json_encode(array_values(array_column($localizacoes_uso,       'id'))) ?>,
    };

    /* ── URLs de destino ao clicar num segmento ─────────────── */
    const _EQ  = '<?= BASE_URL ?>/private/views/equipamentos/lista.php';
    const _DOC = '<?= BASE_URL ?>/private/views/documentos/lista.php';
    const _FOR = '<?= BASE_URL ?>/private/views/fornecedores/lista.php';
    const _GAR = '<?= BASE_URL ?>/private/views/garantias-contrato/lista.php';
    const chartLinkFns = {
        cEstados:      (idx, label) => _EQ + '?estado='         + encodeURIComponent(label),
        cCategorias:   (idx)        => _EQ + '?id_categoria='   + (chartIds.cCategorias[idx]   || ''),
        cLocalizacoes: (idx)        => _EQ + '?id_localizacao=' + (chartIds.cLocalizacoes[idx] || ''),
        cDocumentos:   ()           => _DOC,
        cFornecedores: ()           => _FOR,
        cGarantias:    ()           => _GAR,
    };

    /* ── Chart instance registry ─────────────────────────────── */
    const instances = {};

    /* ── Build / rebuild a chart ─────────────────────────────── */
    function buildChart(canvasId, type) {
        const el = document.getElementById(canvasId);
        if (!el) return;
        const d = chartData[canvasId];
        if (!d) return;

        if (instances[canvasId]) instances[canvasId].destroy();

        const isHBar    = type === 'bar-h';
        const chartType = isHBar ? 'bar' : type;
        const circular  = ['pie','doughnut','polarArea'].includes(chartType);
        const isLine    = chartType === 'line';

        /* Background colors */
        let bg, border;
        if (circular) {
            bg     = P.slice(0, d.values.length);
            border = '#fff';
        } else if (isLine) {
            const ctx = el.getContext('2d');
            const grad = ctx.createLinearGradient(0, 0, 0, 280);
            grad.addColorStop(0, P[0] + '38');
            grad.addColorStop(1, P[0] + '00');
            bg     = grad;
            border = P[0];
        } else {
            bg     = d.values.map((_, i) => P[i % P.length] + 'D0');
            border = 'transparent';
        }

        const dataset = {
            label: 'Total',
            data:  d.values,
            backgroundColor:   bg,
            borderColor:       border,
            borderWidth:       circular ? 2 : isLine ? 2.5 : 0,
            borderRadius:      circular || isLine ? 0 : 7,
            fill:              isLine,
            tension:           isLine ? 0.45 : 0,
            pointBackgroundColor: isLine ? P[0] : undefined,
            pointBorderColor:     isLine ? '#fff' : undefined,
            pointBorderWidth:     isLine ? 2.5 : undefined,
            pointRadius:          isLine ? 5   : undefined,
            pointHoverRadius:     isLine ? 7.5 : undefined,
        };

        const tooltipDefs = {
            backgroundColor: '#0f172a',
            titleColor:      '#fff',
            bodyColor:       'rgba(255,255,255,.72)',
            padding:         13,
            cornerRadius:    11,
            titleFont: { weight: '700', size: 12.5, family: 'Inter' },
            bodyFont:  { size: 12, family: 'Inter' },
            usePointStyle:  true,
            boxWidth:  9,
            boxHeight: 9,
        };

        const scaleBase = {
            grid:   { color: 'rgba(148,163,184,.1)', drawBorder: false },
            border: { display: false },
            ticks:  { font: { size: 11, family: 'Inter' }, color: '#94a3b8', padding: 6 },
            beginAtZero: true,
        };

        instances[canvasId] = new Chart(el, {
            type: chartType,
            data: { labels: d.labels, datasets: [dataset] },
            options: {
                indexAxis: isHBar ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 640, easing: 'easeOutQuart' },
                onClick: chartLinkFns[canvasId] ? function (evt, elements) {
                    if (!elements.length) return;
                    const fn = chartLinkFns[canvasId];
                    const idx = elements[0].index;
                    const label = d.labels[idx];
                    window.location.href = fn(idx, label);
                } : undefined,
                onHover: chartLinkFns[canvasId] ? function (evt, elements) {
                    evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                } : undefined,
                plugins: {
                    legend: {
                        display: circular,
                        position: 'bottom',
                        labels: {
                            padding: 18,
                            font: { size: 11.5, weight: '600', family: 'Inter' },
                            usePointStyle: true,
                            pointStyleWidth: 9,
                            color: '#334155',
                        }
                    },
                    tooltip: tooltipDefs,
                },
                scales: circular ? {} : {
                    x: { ...scaleBase, grid: { ...scaleBase.grid, display: isHBar } },
                    y: { ...scaleBase, grid: { ...scaleBase.grid, display: !isHBar }, ticks: { ...scaleBase.ticks, stepSize: 1 } },
                },
            }
        });

        /* Remove shimmer */
        const body = document.getElementById('body-' + canvasId);
        if (body) body.classList.remove('loading');
    }

    /* ── Animate stat counters ───────────────────────────────── */
    function animateCounter(el) {
        const target   = parseInt(el.dataset.count, 10) || 0;
        const duration = 1100;
        const fps      = 60;
        const steps    = Math.ceil(duration / (1000 / fps));
        let current    = 0;
        let frame      = 0;
        const timer = setInterval(() => {
            frame++;
            const progress = frame / steps;
            /* ease-out-quart */
            const ease = 1 - Math.pow(1 - progress, 4);
            current = Math.round(target * ease);
            el.textContent = current.toLocaleString('pt-PT');
            if (frame >= steps) { el.textContent = target.toLocaleString('pt-PT'); clearInterval(timer); }
        }, 1000 / fps);
    }
    document.querySelectorAll('.dash-stat-num').forEach(animateCounter);

    /* ── Toggle buttons ─────────────────────────────────────── */
    document.querySelectorAll('.dash-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const group = this.closest('.dash-toggle');
            group.querySelectorAll('.dash-toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            buildChart(this.dataset.canvas, this.dataset.type);
        });
    });

    /* ── Intersection observer → fade panels in ─────────────── */
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.06 });

    const panels = document.querySelectorAll('.dash-panel');
    panels.forEach(p => observer.observe(p));

    /* ── Initial render after first intersection ─────────────── */
    const panelChartMap = {
        'body-cEstados':      ['cEstados',      'pie'],
        'body-cCategorias':   ['cCategorias',   'bar-h'],
        'body-cLocalizacoes': ['cLocalizacoes', 'doughnut'],
        'body-cDocumentos':   ['cDocumentos',   'doughnut'],
        'body-cFornecedores': ['cFornecedores', 'bar-h'],
        'body-cGarantias':    ['cGarantias',    'line'],
    };

    const renderObserver = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting && e.target.classList.contains('loading')) {
                const key = e.target.id;
                if (panelChartMap[key]) buildChart(...panelChartMap[key]);
                renderObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.05 });

    Object.keys(panelChartMap).forEach(id => {
        const el = document.getElementById(id);
        if (el) renderObserver.observe(el);
    });

    /* Kick above-the-fold immediately */
    requestAnimationFrame(() => {
        document.querySelectorAll('.dash-panel').forEach(p => {
            if (p.getBoundingClientRect().top < window.innerHeight + 100) {
                p.classList.add('in');
            }
        });
        Object.keys(panelChartMap).forEach(id => {
            const el = document.getElementById(id);
            if (el && el.getBoundingClientRect().top < window.innerHeight + 100) {
                buildChart(...panelChartMap[id]);
            }
        });
    });
})();
</script>

<?php $mysqli->close(); include __DIR__ . '/includes/footer.php'; ?>
