<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// Apenas administradores
if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$registos = [];
$erro_bd  = '';

$f_entidade = trim($_GET['entidade'] ?? '');
$f_acao     = trim($_GET['acao'] ?? '');

$where  = '1=1';
$params = [];
if ($f_entidade !== '') { $where .= ' AND entidade = ?'; $params[] = $f_entidade; }
if ($f_acao !== '')     { $where .= ' AND acao = ?';     $params[] = $f_acao; }

try {
    $stmt = mhs_pdo()->prepare("
        SELECT id, entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, created_at
        FROM historico_alteracoes
        WHERE $where
        ORDER BY created_at DESC, id DESC
        LIMIT 500
    ");
    $stmt->execute($params);
    $registos = $stmt->fetchAll();

    $entidades = mhs_pdo()->query("SELECT DISTINCT entidade FROM historico_alteracoes ORDER BY entidade")->fetchAll();
} catch (PDOException $e) {
    $erro_bd   = 'Não foi possível carregar o histórico.';
    $entidades = [];
}

function mhs_hist_badge(string $acao): string {
    return match ($acao) {
        'criar'  => '<span class="badge bg-success"><i class="fa-solid fa-plus me-1"></i>Criação</span>',
        'editar' => '<span class="badge bg-primary"><i class="fa-solid fa-pen me-1"></i>Edição</span>',
        'apagar' => '<span class="badge bg-danger"><i class="fa-solid fa-trash me-1"></i>Remoção</span>',
        default  => '<span class="badge bg-secondary">' . esc($acao) . '</span>',
    };
}

function mhs_hist_entidade_label(string $e): string {
    return match ($e) {
        'equipamento'       => 'Equipamento',
        'categoria'         => 'Categoria',
        'localizacao'       => 'Localização',
        'fornecedor'        => 'Fornecedor',
        'documento'         => 'Documento',
        'garantia-contrato' => 'Garantia/Contrato',
        'emprestimo'        => 'Empréstimo',
        'utilizador'        => 'Utilizador',
        'website'           => 'Website Público',
        'mensagem'          => 'Mensagem',
        default             => ucfirst($e),
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

<!-- Filtros -->
<form method="get" class="card mhs-data-card mb-3">
  <div class="card-body d-flex flex-wrap gap-2 align-items-end">
    <div>
      <label class="form-label small text-muted mb-1">Entidade</label>
      <select name="entidade" class="form-select form-select-sm" style="min-width:180px">
        <option value="">Todas</option>
        <?php foreach ($entidades as $e): ?>
          <option value="<?= esc($e->entidade) ?>" <?= $f_entidade === $e->entidade ? 'selected' : '' ?>><?= esc(mhs_hist_entidade_label($e->entidade)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label small text-muted mb-1">Ação</label>
      <select name="acao" class="form-select form-select-sm" style="min-width:150px">
        <option value="">Todas</option>
        <option value="criar"  <?= $f_acao === 'criar'  ? 'selected' : '' ?>>Criação</option>
        <option value="editar" <?= $f_acao === 'editar' ? 'selected' : '' ?>>Edição</option>
        <option value="apagar" <?= $f_acao === 'apagar' ? 'selected' : '' ?>>Remoção</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
    <?php if ($f_entidade !== '' || $f_acao !== ''): ?>
      <a href="lista.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-xmark me-1"></i>Limpar</a>
    <?php endif; ?>
  </div>
</form>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-clock-rotate-left mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Registo de atividade da plataforma</span>
      <span class="mhs-table-toolbar-count"><?= count($registos) ?> registos</span>
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
            <th>Entidade</th>
            <th>Registo</th>
            <th>Detalhe</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registos as $r): ?>
            <tr>
              <td class="text-nowrap"><?= $r->created_at ? date('d/m/Y H:i', strtotime($r->created_at)) : '—' ?></td>
              <td><?= esc($r->utilizador ?? '—') ?></td>
              <td><?= mhs_hist_badge($r->acao) ?></td>
              <td><?= esc(mhs_hist_entidade_label($r->entidade)) ?></td>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>
