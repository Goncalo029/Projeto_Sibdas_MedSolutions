<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Equipamentos - Lista';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos</h1>
  </div>
  <div class="mhs-page-actions">
    <a href='novo.php' class='btn btn-primary'><i class='fa-solid fa-plus me-2'></i>Novo</a>
  </div>
</div><div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0">
        <thead class="mhs-thead">
          <tr><th>Código</th><th>Designação</th><th>Marca</th><th>Categoria</th><th>Serviço</th><th>Estado</th><th>Criticidade</th><th>Docs</th><th>Ações</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>EQ-001</td>
            <td>Monitor de Sinais Vitais</td>
            <td>Philips</td>
            <td>Monitorização</td>
            <td>Urgência</td>
            <td><span class="badge bg-success">Ativo</span></td>
            <td><span class="badge bg-warning text-dark">Alta</span></td>
            <td>2</td>
            <td>
              <div class="d-flex gap-1 flex-nowrap">
                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                <a href="editar.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-button><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <tr>
            <td>EQ-002</td>
            <td>Ventilador UCI</td>
            <td>Dräger</td>
            <td>Suporte de Vida</td>
            <td>UCI</td>
            <td><span class="badge bg-success">Ativo</span></td>
            <td><span class="badge bg-danger">Crítica</span></td>
            <td>1</td>
            <td>
              <div class="d-flex gap-1 flex-nowrap">
                <a href="detalhes.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                <a href="editar.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                <button type="button" class="btn btn-sm btn-outline-danger" data-delete-button><i class="fa-solid fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <tr>
            <td>EQ-003</td>
            <td>Desfibrilhador AED</td>
            <td>Zoll</td>
            <td>Emergência</td>
            <td>Bloco Operatório</td>
            <td><span class="badge bg-warning text-dark">Manutenção</span></td>
            <td><span class="badge bg-danger">Crítica</span></td>
            <td>0</td>
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
