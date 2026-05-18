<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Localizações - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Localizações - Editar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-location-dot me-1"></i>Informação da localização</div>
  <div class="card-body">
    <form style="max-width:640px">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Edifício</label>
          <input type="text" name="edificio" class="form-control" value="Bloco Central" maxlength="100" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Piso</label>
          <input type="text" name="piso" class="form-control" value="Piso 2" maxlength="50" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Serviço <span class="text-danger">*</span></label>
          <input type="text" name="servico" class="form-control" value="Urgencia" required maxlength="100" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Sala</label>
          <input type="text" name="sala" class="form-control" value="Sala 204" maxlength="100" />
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="2">Zona com prioridade para equipamentos criticos.</textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-primary mhs-btn-save"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
