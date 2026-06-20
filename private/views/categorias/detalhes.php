<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { header('Location: lista.php'); exit; }

$eqs = $pdo->prepare("
    SELECT id, codigo_inventario, designacao, estado
    FROM equipamentos
    WHERE id_categoria = ? AND eliminado_em IS NULL
    ORDER BY codigo_inventario
");
$eqs->execute([$id]);
$equipamentos = $eqs->fetchAll();

$page_title = 'Categorias - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span><h1 class="mhs-page-title"><?= esc($c->nome) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-tags"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos (<?= count($equipamentos) ?>)</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-tags"></i> Dados da categoria</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd><?= esc($c->nome) ?></dd>
          <dt>Descrição</dt><dd><?= esc($c->descricao ?: '—') ?></dd>
          <dt>Estado</dt><dd><?= $c->ativo ? 'Ativa' : 'Inativa' ?></dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <?php if (count($equipamentos) > 0): ?>
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Estado</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($equipamentos as $e): ?>
        <tr>
          <td><span class="mhs-code"><?= esc($e->codigo_inventario) ?></span></td>
          <td class="mhs-td-primary"><?= esc($e->designacao) ?></td>
          <td><?= get_estado_badge($e->estado) ?></td>
          <td class="text-end"><a href="../equipamentos/detalhes.php?id=<?= (int)$e->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
      </tbody></table>
      <?php else: ?>
      <div class="mhs-empty-state"><i class="fa-solid fa-stethoscope"></i><p>Sem equipamentos nesta categoria.</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
