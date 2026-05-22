<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Localizacoes - Lista';
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

<div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0">
        <thead class="mhs-thead">
          <tr>
            <th>Edificio</th>
            <th>Piso</th>
            <th>Servico</th>
            <th>Sala</th>
            <th>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Edificio A</td>
            <td>1</td>
            <td>Urgencia</td>
            <td>Sala 12</td>
            <td>
              <div class="d-flex gap-1 flex-nowrap">
                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                <a href="editar.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-button><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
