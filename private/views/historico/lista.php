<?php
/**
 * Histórico de alterações (log de eventos)
 * Página exclusiva para administradores.
 * Regista todas as ações feitas no sistema: criações, edições, eliminações e logins.
 * Separado por secções (Equipamentos, Documentos, Garantias, etc.)
 */

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';

redirect_if_not_logged();
if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$registos = [];
$erro_bd  = '';

try {
    $registos = mhs_pdo()->query("
        SELECT id, entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, criado_em
        FROM historico_alteracoes
        ORDER BY criado_em DESC, id DESC
        LIMIT 1000
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Não foi possível carregar o histórico.';
}

// Separar por secção
$secoes = [
    'equipamento'       => ['label' => 'Equipamentos',       'icon' => 'fa-stethoscope',          'registos' => []],
    'documento'         => ['label' => 'Documentos',         'icon' => 'fa-file-lines',            'registos' => []],
    'garantia-contrato' => ['label' => 'Garantias/Contratos','icon' => 'fa-shield-halved',         'registos' => []],
    'fornecedor'        => ['label' => 'Fornecedores',       'icon' => 'fa-building',              'registos' => []],
    'localizacao'       => ['label' => 'Localizações',       'icon' => 'fa-location-dot',          'registos' => []],
    'categoria'         => ['label' => 'Categorias',         'icon' => 'fa-layer-group',           'registos' => []],
    'utilizador'        => ['label' => 'Utilizadores',       'icon' => 'fa-user',                  'registos' => []],
    '__outros'          => ['label' => 'Outros',             'icon' => 'fa-ellipsis',              'registos' => []],
];
foreach ($registos as $r) {
    if (isset($secoes[$r->entidade])) {
        $secoes[$r->entidade]['registos'][] = $r;
    } else {
        $secoes['__outros']['registos'][] = $r;
    }
}

function mhs_hist_badge(string $acao): string {
    return match ($acao) {
        'criar'  => '<span class="badge bg-success"><i class="fa-solid fa-plus me-1"></i>Criação</span>',
        'editar' => '<span class="badge bg-primary"><i class="fa-solid fa-pen me-1"></i>Edição</span>',
        'apagar' => '<span class="badge bg-danger"><i class="fa-solid fa-trash me-1"></i>Remoção</span>',
        default  => '<span class="badge bg-secondary">' . esc($acao) . '</span>',
    };
}

$page_title = 'Histórico de Alterações';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-clock-rotate-left fa-fw"></i></span>
    <h1 class="mhs-page-title">Histórico de Alterações</h1>
  </div>
</div>

<?php if ($erro_bd): ?>
  <div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div>
<?php endif; ?>

<!-- Tabs por secção -->
<div class="mhs-detail-tabs mb-3" style="flex-wrap:wrap">
  <button class="mhs-detail-tab active" data-hist-sec="__todos">
    <i class="fa-solid fa-list fa-fw"></i> Todos
    <span class="badge bg-secondary ms-1"><?= count($registos) ?></span>
  </button>
  <?php foreach ($secoes as $key => $sec): if (empty($sec['registos'])) continue; ?>
  <button class="mhs-detail-tab" data-hist-sec="<?= esc($key) ?>">
    <i class="fa-solid <?= esc($sec['icon']) ?> fa-fw"></i> <?= esc($sec['label']) ?>
    <span class="badge bg-secondary ms-1"><?= count($sec['registos']) ?></span>
  </button>
  <?php endforeach; ?>
</div>

<!-- Tabela principal (todos os registos, filtrada por JS via DataTables) -->
<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-clock-rotate-left mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Registo de atividade da plataforma</span>
      <span class="mhs-table-toolbar-count" id="hist-count"><?= count($registos) ?> registos</span>
    </div>
  </div>
  <div class="card-body p-0">
    <?php if (empty($registos)): ?>
      <div class="mhs-empty-state py-5">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <p>Sem alterações registadas.</p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="historicoTable">
        <thead>
          <tr>
            <th>Data / Hora</th>
            <th>Utilizador</th>
            <th>Ação</th>
            <th>Secção</th>
            <th>Registo</th>
            <th>Detalhe</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registos as $r): ?>
            <tr>
              <td class="text-nowrap"><?= $r->criado_em ? date('d/m/Y H:i', strtotime($r->criado_em)) : '—' ?></td>
              <td><?= esc($r->utilizador ?? '—') ?></td>
              <td><?= mhs_hist_badge($r->acao) ?></td>
              <td data-entidade="<?= esc($r->entidade) ?>">
                <?php $sec = $secoes[$r->entidade] ?? $secoes['__outros']; ?>
                <span class="text-nowrap"><i class="fa-solid <?= esc($sec['icon']) ?> me-1 text-muted"></i><?= esc($sec['label']) ?></span>
              </td>
              <td class="mhs-td-primary"><?= esc($r->entidade_nome ?? '—') ?></td>
              <td><small class="text-muted"><?= $r->detalhe ? esc($r->detalhe) : '—' ?></small></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- JS (filtro por secção na DataTable) movido para private/assets/js/1220673.js -->

<?php include __DIR__ . '/../../includes/footer.php'; ?>
