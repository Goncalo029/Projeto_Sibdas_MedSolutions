<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("
    SELECT g.*, e.codigo_inventario, e.designacao
    FROM garantias_contratos g
    JOIN equipamentos e ON e.id = g.id_equipamento
    WHERE g.id = ? AND g.eliminado_em IS NULL
");
$stmt->execute([$id]);
$g = $stmt->fetch();
if (!$g) { header('Location: lista.php'); exit; }

$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

$page_title = 'Garantias-Contrato - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span><h1 class="mhs-page-title">Garantia — <?= esc($g->codigo_inventario) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="?export=pdf&id=<?= $id ?>" class="btn btn-outline-dark"><i class="fa-solid fa-file-arrow-down me-2"></i>Exportar PDF</a>
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Equipamento</span><span class="mhs-detail-summary-val"><?= esc($g->codigo_inventario) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Início</span><span class="mhs-detail-summary-val"><?= $fmt($g->data_inicio) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Fim</span><span class="mhs-detail-summary-val"><?= $fmt($g->data_fim) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Entidade</span><span class="mhs-detail-summary-val"><?= esc($g->entidade_responsavel ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Contrato</span><span class="mhs-detail-summary-val <?= $g->tem_contrato ? 'mhs-detail-summary-val--ok' : '' ?>"><?= $g->tem_contrato ? 'Sim' : 'Não' ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="contrato"><i class="fa-solid fa-shield-halved"></i> Contrato</button>
    <button class="mhs-detail-tab" data-tab="ficheiro"><i class="fa-solid fa-file-pdf"></i> Documento</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-contrato">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Dados do contrato</div>
        <dl class="mhs-info-dl">
          <dt>Equipamento</dt><dd><?= esc($g->codigo_inventario . ' — ' . $g->designacao) ?></dd>
          <dt>Data de início</dt><dd><?= $fmt($g->data_inicio) ?></dd>
          <dt>Data de fim</dt><dd><?= $fmt($g->data_fim) ?></dd>
          <dt>Tem contrato</dt><dd><?= $g->tem_contrato ? 'Sim' : 'Não' ?></dd>
          <dt>Tipo</dt><dd><?= esc($g->tipo_contrato ?: '—') ?></dd>
          <dt>Entidade responsável</dt><dd><?= esc($g->entidade_responsavel ?: '—') ?></dd>
          <dt>Periodicidade</dt><dd><?= esc($g->periodicidade ?: '—') ?></dd>
        </dl>
      </div>
      <?php if (!empty($g->observacoes)): ?>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs"><?= esc($g->observacoes) ?></p>
      </div>
      <?php endif; ?>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-ficheiro">
    <div class="mhs-tab-body"><div class="mhs-info-group mhs-w-380">
      <div class="mhs-info-group-title"><i class="fa-solid fa-file-pdf"></i> Documento do contrato</div>
      <?php if (!empty($g->nome_ficheiro)): ?>
        <p class="mhs-info-obs mb-2"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= esc($g->nome_ficheiro) ?></p>
        <a href="lista.php?ficheiro=<?= $id ?>" class="btn btn-primary w-100"><i class="fa-solid fa-download me-2"></i>Descarregar PDF</a>
      <?php else: ?>
        <p class="mhs-info-obs">Sem documento importado. Pode anexar um na edição.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
