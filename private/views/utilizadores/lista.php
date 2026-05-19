<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Gestão de Utilizadores';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-user-gear fa-fw"></i></span>
    <h1 class="mhs-page-title">Gestão de Utilizadores</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="novo.php" class="btn btn-primary"><i class="fa-solid fa-user-plus me-2"></i>Novo Utilizador</a>
  </div>
</div><div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0">
        <thead class="mhs-thead">
          <tr><th>Email</th><th>Perfil</th><th>Último login</th><th>Criado em</th><th>Ações</th></tr>
        </thead>
                        <tbody>
          <tr>
            <td>admin@hospital.pt</td>
            <td><span class="badge bg-success">Admin</span></td>
            <td>12/04/2026 10:35</td>
            <td>10/04/2026</td>
            <td>
              <div class="d-flex gap-1 flex-nowrap">
                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                <a href="editar.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-button><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <tr>
            <td>tecnico@hospital.pt</td>
            <td><span class="badge bg-info">Tecnico</span></td>
            <td>11/04/2026 16:20</td>
            <td>10/04/2026</td>
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
