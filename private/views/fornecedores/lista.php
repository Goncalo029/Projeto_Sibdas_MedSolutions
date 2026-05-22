<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Fornecedores - Lista';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span>
    <h1 class="mhs-page-title">Fornecedores</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="novo.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Novo</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0">
        <thead class="mhs-thead">
          <tr>
            <th>Nome</th>
            <th>NIF</th>
            <th>Tipo</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Clinicare Equipamentos</td>
            <td>509123456</td>
            <td>Assistencia tecnica</td>
            <td>210 000 000</td>
            <td>geral@clinicare.pt</td>
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
