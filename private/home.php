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
    JOIN categorias c ON c.id = e.id_categoria
    LEFT JOIN localizacoes l ON l.id = e.id_localizacao
    WHERE c.nome LIKE 'Suporte%' AND e.eliminado_em IS NULL
    GROUP BY l.servico ORDER BY total DESC");
if ($r) while ($row = $r->fetch_assoc()) $suporte_vida_servico[] = $row;
$sv_total = array_sum(array_map(fn($x) => (int)$x['total'], $suporte_vida_servico));

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<style>
/* ── Dashboard moderna (paleta do site: azul #0d6ea8 + teal #0bb37e) ── */
.dash-hero{background:linear-gradient(135deg,#0a5580 0%,#0d6ea8 48%,#0bb37e 100%);
    box-shadow:0 22px 60px -30px rgba(13,110,168,.6)}
.dash-hero::after{background:radial-gradient(circle,rgba(11,179,126,.30),transparent 70%);filter:blur(8px)}

.dash-panel{background:linear-gradient(180deg,#ffffff 0%,#f4faff 100%);
    border:1px solid var(--mhs-border-strong);border-radius:20px;
    box-shadow:0 14px 36px -22px rgba(13,110,168,.28)}
.dash-panel::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:linear-gradient(90deg,#0d6ea8,#0bb37e,#6366f1,#0d6ea8);
    background-size:300% 100%;animation:mhsHue 9s linear infinite;opacity:.9}
@keyframes mhsHue{to{background-position:300% 0}}
.dash-panel.in:hover{transform:translateY(-4px);
    box-shadow:0 26px 50px -24px rgba(13,110,168,.34);transition:transform .25s ease,box-shadow .25s ease}
.dash-panel-head{background:linear-gradient(180deg,rgba(223,240,248,.55),transparent);
    border-bottom:1px solid rgba(148,163,184,.12)}
.dash-panel-title{font-size:.95rem;letter-spacing:-.01em}
.dash-panel-title i{width:30px;height:30px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;
    color:#fff;background:linear-gradient(135deg,#0d6ea8,#0bb37e);box-shadow:0 6px 14px -6px rgba(13,110,168,.7);font-size:.85rem}
.dash-toggle{background:rgba(13,110,168,.08);border-radius:12px;padding:4px}
.dash-toggle-btn{border-radius:9px}
.dash-toggle-btn.active{background:#fff;color:var(--mhs-primary);box-shadow:0 3px 10px -2px rgba(13,110,168,.3)}

/* KPI cards com brilho subtil no hover */
a.dash-stat::after{content:'';position:absolute;inset:0;opacity:0;transition:opacity .25s;pointer-events:none;
    background:radial-gradient(120% 80% at 50% 0%,rgba(255,255,255,.16),transparent 70%)}
a.dash-stat:hover::after{opacity:1}
a.dash-stat:hover{transform:translateY(-3px)}
.dash-stat-num{text-shadow:0 2px 18px rgba(255,255,255,.18)}
</style>

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
<style>
.mhs-vitals{position:relative;border:1px solid var(--mhs-border-strong);border-radius:20px;overflow:hidden;margin-bottom:1.5rem;
    background:linear-gradient(135deg,#ffffff 0%,#f1faf6 55%,#eaf3fb 100%);
    box-shadow:0 16px 40px -22px rgba(13,110,168,.35)}
.mhs-vitals::before{content:'';position:absolute;inset:0;pointer-events:none;opacity:.55;
    background-image:linear-gradient(rgba(13,110,168,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(13,110,168,.05) 1px,transparent 1px);
    background-size:26px 26px}
.mhs-vitals-head{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;position:relative;z-index:2}
.mhs-vitals-title{font-family:'Sora',sans-serif;font-weight:800;font-size:1rem;letter-spacing:.02em;color:var(--mhs-text);text-transform:uppercase}
.mhs-vitals-title i{color:#0bb37e;margin-right:9px}
.mhs-vitals-body{display:flex;flex-wrap:wrap;align-items:stretch;gap:8px;padding:0 22px 22px;position:relative;z-index:2}
.mhs-vitals-ecg{flex:1 1 340px;min-height:170px;position:relative;display:flex;align-items:center}
.mhs-vitals-ecg svg{position:absolute;inset:0;width:100%;height:100%}
.mhs-ecg-line{fill:none;stroke:#0bb37e;stroke-width:2.6;filter:drop-shadow(0 0 4px rgba(11,179,126,.45));
    animation:mhsEcgScroll 3.6s linear infinite}
@keyframes mhsEcgScroll{from{transform:translateX(0)}to{transform:translateX(-600px)}}
.mhs-vitals-count{position:relative;z-index:3;margin-left:auto;text-align:right;padding-right:4px;align-self:center}
.mhs-vitals-count .num{display:block;font-family:'Sora',sans-serif;font-weight:800;font-size:4.6rem;line-height:.9;
    color:#0d6ea8;animation:mhsBeat 1.2s ease-in-out infinite}
@keyframes mhsBeat{0%,100%{transform:scale(1)}14%{transform:scale(1.06)}28%{transform:scale(1)}}
.mhs-vitals-count .lbl{display:block;margin-top:6px;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--mhs-muted)}
.mhs-vitals-count .bpm{display:inline-flex;align-items:center;gap:5px;margin-top:9px;font-size:.72rem;font-weight:600;color:#0bb37e}
.mhs-vitals-channels{flex:1 1 100%;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;margin-top:6px}
.mhs-vch{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:12px;text-decoration:none;
    background:#fff;border:1px solid var(--mhs-border-strong);transition:background .15s,transform .15s,border-color .15s,box-shadow .15s}
.mhs-vch:hover{background:var(--mhs-primary-soft);border-color:var(--mhs-primary);transform:translateY(-2px);box-shadow:0 10px 20px -12px rgba(13,110,168,.5)}
.mhs-vch-dot{width:10px;height:10px;border-radius:50%;background:#0bb37e;box-shadow:0 0 0 4px rgba(11,179,126,.14);flex:0 0 auto}
.mhs-vch-name{flex:1;color:var(--mhs-text);font-size:.86rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mhs-vch-val{font-family:'Sora',sans-serif;font-weight:800;font-size:1.15rem;color:#0d6ea8}
.mhs-vitals-empty{padding:30px 22px;color:var(--mhs-muted);position:relative;z-index:2}
</style>
<div class="dash-panel mhs-vitals dash-gap" style="transition-delay:.35s">
    <div class="mhs-vitals-head">
        <span class="mhs-vitals-title"><i class="fas fa-heart-pulse"></i> Monitor de Suporte de Vida</span>
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
        cDocumentos:   { labels: <?= json_encode(array_column($equipamentos_servico,   'nome'))   ?>, values: <?= json_encode(array_map('intval', array_column($equipamentos_servico,   'total'))) ?> },
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
        cLocalizacoes: (idx, label) => _EQ + '?edificio=' + encodeURIComponent(label),
        cDocumentos:   (idx, label) => _EQ + '?servico=' + encodeURIComponent(label),
        cFornecedores: (idx, label) => _EQ + '?fabricante=' + encodeURIComponent(label),
        cGarantias:    (idx, label) => _GAR + '?vence=' + encodeURIComponent(label),
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

        const ctx = el.getContext('2d');
        /* Background colors */
        let bg, border;
        if (circular) {
            bg     = P.slice(0, d.values.length);
            border = '#fff';
        } else if (isLine) {
            const grad = ctx.createLinearGradient(0, 0, 0, 280);
            grad.addColorStop(0, P[0] + '45');
            grad.addColorStop(1, P[0] + '00');
            bg     = grad;
            border = P[0];
        } else {
            /* barras com gradiente (cor sólida → translúcida) */
            bg = d.values.map((_, i) => {
                const c = P[i % P.length];
                const g = isHBar ? ctx.createLinearGradient(0, 0, 480, 0)
                                 : ctx.createLinearGradient(0, 0, 0, 280);
                g.addColorStop(0, c + 'FF');
                g.addColorStop(1, c + '82');
                return g;
            });
            border = 'transparent';
        }

        const dataset = {
            label: 'Total',
            data:  d.values,
            backgroundColor:   bg,
            borderColor:       border,
            borderWidth:       circular ? 3 : isLine ? 3 : 0,
            borderRadius:      circular ? 12 : isLine ? 0 : 9,
            borderSkipped:     false,
            spacing:           circular ? 2 : 0,
            hoverOffset:       circular ? 12 : 0,
            barPercentage:     0.6,
            categoryPercentage: 0.7,
            fill:              isLine,
            tension:           isLine ? 0.45 : 0,
            pointBackgroundColor: isLine ? '#fff' : undefined,
            pointBorderColor:     isLine ? P[0]  : undefined,
            pointBorderWidth:     isLine ? 3 : undefined,
            pointRadius:          isLine ? 5 : undefined,
            pointHoverRadius:     isLine ? 8.5 : undefined,
        };

        /* Plugin: total no centro do donut */
        const centerTextPlugin = {
            id: 'centerText',
            afterDraw(chart) {
                if (chart.config.type !== 'doughnut') return;
                const arr = chart.data.datasets[0].data;
                const total = arr.reduce((a, b) => a + (Number(b) || 0), 0);
                const meta = chart.getDatasetMeta(0);
                const arc = meta.data[0];
                if (!arc) return;
                const c = chart.ctx;
                c.save();
                c.textAlign = 'center'; c.textBaseline = 'middle';
                c.fillStyle = '#0d6ea8';
                c.font = '800 32px Sora, sans-serif';
                c.fillText(total, arc.x, arc.y - 6);
                c.fillStyle = '#94a3b8';
                c.font = '700 10px Inter, sans-serif';
                c.fillText('TOTAL', arc.x, arc.y + 17);
                c.restore();
            }
        };
        /* Plugin: valor no topo/fim de cada barra */
        const barValuePlugin = {
            id: 'barValues',
            afterDatasetsDraw(chart) {
                if (chart.config.type !== 'bar') return;
                const horiz = chart.options.indexAxis === 'y';
                const c = chart.ctx;
                const meta = chart.getDatasetMeta(0);
                const data = chart.data.datasets[0].data;
                c.save();
                c.fillStyle = '#475569';
                c.font = '800 11.5px Inter, sans-serif';
                meta.data.forEach((bar, i) => {
                    const v = data[i];
                    if (!v) return;
                    if (horiz) { c.textAlign = 'left'; c.textBaseline = 'middle'; c.fillText(v, bar.x + 8, bar.y); }
                    else       { c.textAlign = 'center'; c.textBaseline = 'bottom'; c.fillText(v, bar.x, bar.y - 6); }
                });
                c.restore();
            }
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
            plugins: [centerTextPlugin, barValuePlugin],
            options: {
                indexAxis: isHBar ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: true,
                cutout: chartType === 'doughnut' ? '70%' : undefined,
                layout: { padding: { right: isHBar ? 32 : 0, top: (!circular && !isHBar) ? 18 : 0 } },
                animation: { duration: 700, easing: 'easeOutQuart' },
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
                    x: { ...scaleBase, grid: { ...scaleBase.grid, display: isHBar }, ticks: { ...scaleBase.ticks, ...(isHBar ? { stepSize: 1, precision: 0 } : {}) } },
                    y: { ...scaleBase, grid: { ...scaleBase.grid, display: !isHBar }, ticks: { ...scaleBase.ticks, stepSize: 1, precision: 0 } },
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
