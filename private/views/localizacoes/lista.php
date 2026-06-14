<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Localizacoes - Lista';
$localizacoes = [];
$erro_bd = '';

try {
    $localizacoes = mhs_pdo()->query("
        SELECT id, edificio, piso, servico, sala
        FROM localizacoes
        WHERE deleted_at IS NULL
        ORDER BY edificio, piso, servico, sala
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar localizacoes.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span>
    <h1 class="mhs-page-title">Localizações</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-location-dot mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Localizações</span>
      <span class="mhs-table-toolbar-count"><?= count($localizacoes) ?> registos</span>
    </div>
    <a href="nova.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Nova Localização</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="localizacoesTable">
        <thead class="mhs-thead">
          <tr><th>Edificio</th><th>Piso</th><th>Servico</th><th>Sala</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($localizacoes as $localizacao) : ?>
            <tr>
              <td><?= esc($localizacao->edificio) ?></td>
              <td><?= esc($localizacao->piso) ?></td>
              <td><?= esc($localizacao->servico) ?></td>
              <td><?= esc($localizacao->sala) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $localizacao->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $localizacao->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $localizacao->id ?>" data-delete-name="<?= esc($localizacao->servico) ?>"><i class="fa-solid fa-trash"></i></button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
