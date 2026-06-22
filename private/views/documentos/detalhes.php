<?php
/**
 * Detalhes do documento
 * Mostra a informação completa de um documento associado a um equipamento.
 * Permite descarregar o ficheiro PDF e aceder à edição do documento.
 */

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// Obter o ID do documento — se não existir, voltar à lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("
    SELECT d.*, e.codigo_inventario, e.designacao, f.nome AS fornecedor
    FROM documentos d
    JOIN equipamentos e ON e.id = d.id_equipamento
    LEFT JOIN fornecedores f ON f.id = d.id_fornecedor
    WHERE d.id = ? AND d.eliminado_em IS NULL
");
$stmt->execute([$id]);
$d = $stmt->fetch();
if (!$d) { header('Location: lista.php'); exit; }

$fmt = fn($x) => $x ? date('d/m/Y', strtotime($x)) : '—';
$temFicheiro = $d->ficheiro_conteudo !== null;

$page_title = 'Documentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span><h1 class="mhs-page-title"><?= esc($d->nome_documento ?: $d->tipo_documento) ?></h1></div>
  <div class="mhs-page-actions">
    <?php if ($temFicheiro): ?><a href="download.php?id=<?= $id ?>" class="btn btn-outline-danger"><i class="fa-solid fa-file-pdf me-2"></i>Descarregar PDF</a><?php endif; ?>
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Equipamento</span><span class="mhs-detail-summary-val"><?= esc($d->codigo_inventario) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Tipo</span><span class="mhs-detail-summary-val"><?= esc($d->tipo_documento ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Data</span><span class="mhs-detail-summary-val"><?= $fmt($d->data_documento) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Validade</span><span class="mhs-detail-summary-val"><?= $d->data_validade ? $fmt($d->data_validade) : 'Sem validade' ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-file-lines"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="ficheiro"><i class="fa-solid fa-download"></i> Ficheiro</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-file-lines"></i> Dados do documento</div>
        <dl class="mhs-info-dl">
          <dt>Equipamento</dt><dd><?= esc($d->codigo_inventario . ' — ' . $d->designacao) ?></dd>
          <dt>Tipo</dt><dd><?= esc($d->tipo_documento ?: '—') ?></dd>
          <dt>Nome</dt><dd><?= esc($d->nome_documento ?: '—') ?></dd>
          <dt>Fornecedor</dt><dd><?= esc($d->fornecedor ?: '—') ?></dd>
          <dt>Data</dt><dd><?= $fmt($d->data_documento) ?></dd>
          <dt>Validade</dt><dd><?= $d->data_validade ? $fmt($d->data_validade) : '—' ?></dd>
        </dl>
      </div>
      <?php if (!empty($d->observacoes)): ?>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs"><?= esc($d->observacoes) ?></p>
      </div>
      <?php endif; ?>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-ficheiro">
    <div class="mhs-tab-body"><div class="mhs-info-group mhs-w-380">
      <div class="mhs-info-group-title"><i class="fa-solid fa-download"></i> Download</div>
      <?php if ($temFicheiro): ?>
        <p class="mhs-info-obs mb-2"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= esc($d->nome_ficheiro ?: 'documento.pdf') ?></p>
        <a href="download.php?id=<?= $id ?>" class="btn btn-primary w-100"><i class="fa-solid fa-file-pdf me-2"></i>Descarregar PDF</a>
      <?php else: ?>
        <p class="mhs-info-obs">Sem ficheiro associado. Pode anexar um na edição.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
