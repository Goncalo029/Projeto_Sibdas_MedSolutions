<?php
/**
 * Editar utilizador
 * Página exclusiva para administradores.
 * Permite alterar o perfil e a password de um utilizador existente.
 * A nova password, se fornecida, é guardada com hash bcrypt.
 */

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';

// Verificar autenticação e que é administrador
redirect_if_not_logged();
require_admin();

// Obter o ID do utilizador — se não existir, voltar à lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['email']   ?? '');
    $perfil  = trim($_POST['profile'] ?? '');
    $senha   = trim($_POST['password'] ?? '');

    if (!$email || !$perfil) {
        $_SESSION['error_message'] = 'Email e perfil são obrigatórios.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        $pdo = mhs_pdo();
        if ($senha !== '') {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE utilizadores SET nome=AES_ENCRYPT(?,?), perfil=?, senha=?, atualizado_em=NOW() WHERE id=?")
                ->execute([$email, MYSQL_AES_KEY, $perfil, $hash, $id]);
        } else {
            $pdo->prepare("UPDATE utilizadores SET nome=AES_ENCRYPT(?,?), perfil=?, atualizado_em=NOW() WHERE id=?")
                ->execute([$email, MYSQL_AES_KEY, $perfil, $id]);
        }
        mhs_historico('utilizador', $id, $email, 'editar');
        $_SESSION['success_message'] = 'Utilizador atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$stmt = mhs_pdo()->prepare("SELECT id, AES_DECRYPT(nome, :chave) AS email, perfil AS profile, criado_em FROM utilizadores WHERE id=? AND eliminado_em IS NULL");
$stmt->execute([':chave' => MYSQL_AES_KEY, $id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Utilizadores - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-3">
  <i class="fa-solid fa-circle-check me-2"></i><?= esc($_SESSION['success_message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (!empty($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show mb-3">
  <i class="fa-solid fa-circle-exclamation me-2"></i><?= esc($_SESSION['error_message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Utilizador</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <input type="hidden" name="id" value="<?= $row->id ?>">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-user"></i> Dados do utilizador</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" value="<?= esc($row->email) ?>" required maxlength="190" placeholder="utilizador@hospital.pt" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Perfil <span class="text-danger">*</span></label>
            <select name="profile" class="form-select" required>
              <option value="">-- Selecione --</option>
              <option value="tecnico" <?= $row->profile === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
              <option value="admin"   <?= $row->profile === 'admin'   ? 'selected' : '' ?>>Administrador</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nova password</label>
            <input type="password" name="password" class="form-control" minlength="6" maxlength="50" placeholder="Deixar vazio para manter a atual" />
            <div class="form-text">Preencha apenas se quiser alterar a password.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Utilizador</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
