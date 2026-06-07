<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("SELECT * FROM assistencia_tecnica WHERE id=? AND deleted_at IS NULL");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Assistência Técnica - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-headset fa-fw"></i></span>
    <h1 class="mhs-page-title"><?= esc($row->empresa) ?></h1>
  </div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="mhs-tab-body">
    <div class="row g-4">
      <div class="col-md-5">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-building"></i> Empresa</div>
          <dl class="mhs-info-dl">
            <dt>Empresa</dt><dd><?= esc($row->empresa) ?></dd>
            <dt>Nome do Contacto</dt><dd><?= esc($row->nome_contacto) ?></dd>
          </dl>
        </div>

        <div class="mhs-info-group mt-3">
          <div class="mhs-info-group-title"><i class="fa-solid fa-address-book"></i> Contactos</div>
          <div class="d-flex flex-column gap-2 mt-2">
            <?php if ($row->email): ?>
              <a href="mailto:<?= esc($row->email) ?>" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="fa-solid fa-envelope"></i><span><?= esc($row->email) ?></span>
              </a>
            <?php endif; ?>
            <?php if ($row->telefone): ?>
              <a href="tel:<?= esc($row->telefone) ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fa-solid fa-phone"></i><span><?= esc($row->telefone) ?></span>
              </a>
            <?php endif; ?>
            <?php if ($row->telefone_urgencia): ?>
              <a href="tel:<?= esc($row->telefone_urgencia) ?>" class="btn btn-danger d-flex align-items-center gap-2">
                <i class="fa-solid fa-phone-volume"></i>
                <span><strong>URGÊNCIA:</strong> <?= esc($row->telefone_urgencia) ?></span>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($row->observacoes): ?>
      <div class="col-md-7">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações / Notas</div>
          <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;white-space:pre-wrap"><?= esc($row->observacoes) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
