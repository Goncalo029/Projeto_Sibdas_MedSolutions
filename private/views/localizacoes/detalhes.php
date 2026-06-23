<?php
// pagina com os detalhes de uma localizacao e os equipamentos que la estao
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// vai buscar o id ao link, se nao tiver volta para a lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// procura a localizacao na base de dados
$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM localizacoes WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$id]);
$l = $stmt->fetch();
// se nao existir volta para a lista
if (!$l) { header('Location: lista.php'); exit; }

// vai buscar os equipamentos que estao nesta localizacao
$eqs = $pdo->prepare("
    SELECT id, codigo_inventario, designacao, estado, criticidade
    FROM equipamentos
    WHERE id_localizacao = ? AND eliminado_em IS NULL
    ORDER BY codigo_inventario
");
$eqs->execute([$id]);
$equipamentos = $eqs->fetchAll();

// titulo da pagina = servico / sala
$titulo = $l->servico . ($l->sala ? ' / ' . $l->sala : '');

$page_title = 'Localizações - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span><h1 class="mhs-page-title"><?= esc($titulo) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Edifício</span><span class="mhs-detail-summary-val"><?= esc($l->edificio ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Piso</span><span class="mhs-detail-summary-val"><?= esc($l->piso ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Serviço</span><span class="mhs-detail-summary-val"><?= esc($l->servico) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Sala</span><span class="mhs-detail-summary-val"><?= esc($l->sala ?: '—') ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-location-dot"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos (<?= count($equipamentos) ?>)</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-location-dot"></i> Dados da localização</div>
        <dl class="mhs-info-dl">
          <dt>Edifício</dt><dd><?= esc($l->edificio ?: '—') ?></dd>
          <dt>Piso</dt><dd><?= esc($l->piso ?: '—') ?></dd>
          <dt>Serviço</dt><dd><?= esc($l->servico) ?></dd>
          <dt>Sala</dt><dd><?= esc($l->sala ?: '—') ?></dd>
        </dl>
      </div>
      <?php if (!empty($l->observacoes)): ?>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs"><?= esc($l->observacoes) ?></p>
      </div>
      <?php endif; ?>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <?php if (count($equipamentos) > 0): ?>
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Estado</th><th>Criticidade</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($equipamentos as $e): ?>
        <tr>
          <td><span class="mhs-code"><?= esc($e->codigo_inventario) ?></span></td>
          <td class="mhs-td-primary"><?= esc($e->designacao) ?></td>
          <td><?= get_estado_badge($e->estado) ?></td>
          <td><?= get_criticidade_badge($e->criticidade) ?></td>
          <td class="text-end"><a href="../equipamentos/detalhes.php?id=<?= (int)$e->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php else: ?>
      <div class="mhs-empty-state"><i class="fa-solid fa-stethoscope"></i><p>Sem equipamentos nesta localização.</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
