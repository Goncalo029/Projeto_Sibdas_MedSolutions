<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$id]);
$f = $stmt->fetch();
if (!$f) { header('Location: lista.php'); exit; }

// Equipamentos associados a este fornecedor (apenas os não eliminados)
$eqs = $pdo->prepare("
    SELECT e.id, e.codigo_inventario, e.designacao, e.marca, ef.tipo_relacao
    FROM equipamentos_fornecedores ef
    JOIN equipamentos e ON e.id = ef.id_equipamento
    WHERE ef.id_fornecedor = ? AND e.eliminado_em IS NULL
    ORDER BY e.codigo_inventario
");
$eqs->execute([$id]);
$equipamentos = $eqs->fetchAll();

$page_title = 'Fornecedores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span><h1 class="mhs-page-title"><?= esc($f->nome) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">NIF</span><span class="mhs-detail-summary-val"><?= esc($f->nif ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Tipo</span><span class="mhs-detail-summary-val"><?= esc($f->tipo_fornecedor ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Telefone</span><span class="mhs-detail-summary-val"><?= esc($f->telefone ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Email</span><span class="mhs-detail-summary-val"><?= esc($f->email ?: '—') ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-address-card"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="contacto"><i class="fa-solid fa-user"></i> Contacto</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos (<?= count($equipamentos) ?>)</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-address-card"></i> Dados gerais</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd><?= esc($f->nome) ?></dd>
          <dt>NIF</dt><dd><?= esc($f->nif ?: '—') ?></dd>
          <dt>Tipo</dt><dd><?= esc($f->tipo_fornecedor ?: '—') ?></dd>
          <dt>Telefone</dt><dd><?= esc($f->telefone ?: '—') ?></dd>
          <dt>Email</dt><dd><?= esc($f->email ?: '—') ?></dd>
          <dt>Website</dt><dd><?= esc($f->website ?: '—') ?></dd>
        </dl>
      </div>
      <?php if (!empty($f->observacoes)): ?>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs"><?= esc($f->observacoes) ?></p>
      </div>
      <?php endif; ?>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-contacto">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Pessoa de contacto</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd><?= esc($f->pessoa_contacto ?: '—') ?></dd>
          <dt>Telemóvel</dt><dd><?= esc($f->tel_contacto ?: '—') ?></dd>
          <dt>Morada</dt><dd><?= esc($f->morada ?: '—') ?></dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <?php if (count($equipamentos) > 0): ?>
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Marca</th><th>Tipo de relação</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($equipamentos as $e): ?>
        <tr>
          <td><span class="mhs-code"><?= esc($e->codigo_inventario) ?></span></td>
          <td class="mhs-td-primary"><?= esc($e->designacao) ?></td>
          <td><?= esc($e->marca ?: '—') ?></td>
          <td><?= esc($e->tipo_relacao ?: '—') ?></td>
          <td class="text-end"><a href="../equipamentos/detalhes.php?id=<?= (int)$e->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php else: ?>
      <div class="mhs-empty-state"><i class="fa-solid fa-stethoscope"></i><p>Sem equipamentos associados a este fornecedor.</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
