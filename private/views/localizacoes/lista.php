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

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span>
    <h1 class="mhs-page-title">Localizacoes</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="nova.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Novo</a>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Pesquisar</label>
        <input type="text" class="form-control" id="localizacoesSearch" placeholder="Edifício, piso ou serviço..." />
      </div>
      <div class="col-md-6 text-end">
        <button class="btn btn-outline-secondary" onclick="document.getElementById('localizacoesSearch').value=''; jQuery('#localizacoesTable').DataTable().search('').draw();"><i class="fa-solid fa-times me-2"></i>Limpar</button>
      </div>
    </div>
  </div>
</div>

<div class="card mhs-data-card">
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
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $localizacao->id ?>" data-delete-name="<?= esc($localizacao->servico) ?>"><i class="fa-solid fa-trash"></i></button>
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
