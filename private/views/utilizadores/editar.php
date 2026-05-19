<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Utilizadores - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Utilizadores - Editar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-user me-1"></i>Dados do utilizador</div>
  <div class="card-body">
    <form>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control" id="email" name="email" value="tecnico@hospital.pt" placeholder="utilizador@hospital.pt" required maxlength="190" />
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label fw-semibold">Nova password</label>
          <input type="text" class="form-control" id="password" name="password" value="" placeholder="Deixar vazio para manter a atual" minlength="6" maxlength="50" />
          <div class="form-text">Preencha apenas se quiser alterar a password.</div>
        </div>
        <div class="col-md-6">
          <label for="profile" class="form-label fw-semibold">Perfil <span class="text-danger">*</span></label>
          <select class="form-select" id="profile" name="profile" required>
            <option value="tecnico" selected>Tecnico</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-check me-2"></i>Guardar Alterações</button>
        <a href="lista.php" class="btn btn-secondary ms-2">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
