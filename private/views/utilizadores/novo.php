<?php
/**
 * Novo utilizador
 * Formulário para criar uma nova conta de utilizador no sistema.
 * O email é encriptado com AES antes de ser guardado na base de dados.
 * A password é guardada com hash bcrypt (password_hash).
 */

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// Contar utilizadores existentes (usado para sugestões no auto-preencher)
$_n_users = (int)mhs_pdo()->query("SELECT COUNT(*) FROM utilizadores WHERE eliminado_em IS NULL")->fetchColumn();
$page_title = 'Utilizadores - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
    <h1 class="mhs-page-title">Utilizadores - Novo</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card mhs-data-card">
  <div class="card-body">
    <form style="max-width:760px">
      <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillUtilizador(<?= $_n_users ?>)"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
      </div>
      <div class="row g-3">
        <div class="col-md-6"><label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label><input type="email" class="form-control" id="email" name="email" placeholder="utilizador@hospital.pt" required maxlength="190" /></div>
        <div class="col-md-6"><label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label><input type="text" class="form-control" id="password" name="password" placeholder="Minimo 6 caracteres" required minlength="6" maxlength="50" /></div>
        <div class="col-md-6"><label for="profile" class="form-label fw-semibold">Perfil <span class="text-danger">*</span></label><select class="form-select" id="profile" name="profile" required><option value="">-- Selecione --</option><option value="tecnico">Tecnico</option><option value="admin">Admin</option></select></div>
      </div>
      <div class="mt-4">
        <button type="button" class="btn btn-primary"><i class="fa-solid fa-check me-2"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary ms-2">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
