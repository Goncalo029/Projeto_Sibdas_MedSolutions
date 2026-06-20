<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$pdo        = mhs_pdo();
$user_id    = $_SESSION['user_id'] ?? 0;
$user_email = $_SESSION['user_email'] ?? '';
$profile    = $_SESSION['profile'] ?? '';

$stmt = $pdo->prepare("SELECT id, perfil AS profile, ultimo_acesso, criado_em FROM utilizadores WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$user_id]);
$agent = $stmt->fetch();

// Processamento de alteração de password
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw_atual    = $_POST['pw_atual']    ?? '';
    $pw_nova     = trim($_POST['pw_nova']    ?? '');
    $pw_confirma = trim($_POST['pw_confirma'] ?? '');

    $stmt_pw = $pdo->prepare("SELECT senha FROM utilizadores WHERE id = ?");
    $stmt_pw->execute([$user_id]);
    $row = $stmt_pw->fetch();

    $valid = $row && (password_verify($pw_atual, $row->senha) || $pw_atual === $row->senha);

    if (!$valid) {
        $error = 'Password atual incorreta.';
    } elseif (strlen($pw_nova) < 6) {
        $error = 'A nova password deve ter pelo menos 6 caracteres.';
    } elseif ($pw_nova !== $pw_confirma) {
        $error = 'As passwords não coincidem.';
    } else {
        $hash = password_hash($pw_nova, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE utilizadores SET senha = ?, atualizado_em = NOW() WHERE id = ?")->execute([$hash, $user_id]);
        $_SESSION['success_message'] = 'Password alterada com sucesso.';
        header('Location: perfil.php');
        exit;
    }
}

$profile_label = match($profile) {
    'admin'   => 'Administrador',
    'tecnico' => 'Técnico',
    default   => ucfirst($profile)
};
$iniciais = strtoupper(mb_substr($user_email, 0, 2, 'UTF-8'));

$page_title = 'Meu Perfil';
include __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i><?= esc($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-circle-user fa-fw"></i></span>
    <h1 class="mhs-page-title">Meu Perfil</h1>
  </div>
</div>

<!-- Resumo do perfil -->
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner" style="align-items:center">
    <div class="mhs-detail-summary-item" style="flex-direction:row;align-items:center;gap:14px">
      <span style="width:54px;height:54px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#1d4ed8,#0284c7);color:#fff;font-weight:800;font-size:1.1rem;letter-spacing:.02em;flex-shrink:0"><?= esc($iniciais) ?></span>
      <span>
        <span class="mhs-detail-summary-label">Sessão iniciada como</span>
        <span class="mhs-detail-summary-val" style="font-size:1rem"><?= esc($user_email) ?></span>
      </span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Perfil</span>
      <span class="mhs-detail-summary-val">
        <span class="badge <?= $profile === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= esc($profile_label) ?></span>
      </span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Último acesso</span>
      <span class="mhs-detail-summary-val"><?= $agent && $agent->ultimo_acesso ? date('d/m/Y H:i', strtotime($agent->ultimo_acesso)) : '—' ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Membro desde</span>
      <span class="mhs-detail-summary-val"><?= $agent && $agent->criado_em ? date('d/m/Y', strtotime($agent->criado_em)) : '—' ?></span>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Informações da conta -->
  <div class="col-lg-6">
    <div class="card mhs-data-card h-100">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-id-card"></i> Informações da Conta</div>
          <dl class="mhs-info-dl">
            <dt>Email</dt><dd><?= esc($user_email) ?></dd>
            <dt>Perfil</dt><dd><span class="badge <?= $profile === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= esc($profile_label) ?></span></dd>
            <dt>Último acesso</dt><dd><?= $agent && $agent->ultimo_acesso ? date('d/m/Y H:i', strtotime($agent->ultimo_acesso)) : '—' ?></dd>
            <dt>Membro desde</dt><dd><?= $agent && $agent->criado_em ? date('d/m/Y', strtotime($agent->criado_em)) : '—' ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>

  <!-- Alterar password -->
  <div class="col-lg-6">
    <div class="card mhs-data-card h-100">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-lock"></i> Alterar Password</div>
          <form method="post" action="perfil.php" class="mt-2">
            <div class="mb-3">
              <label class="form-label fw-semibold">Password Atual <span class="text-danger">*</span></label>
              <input type="password" name="pw_atual" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Nova Password <span class="text-danger">*</span></label>
              <input type="password" name="pw_nova" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Confirmar Nova Password <span class="text-danger">*</span></label>
              <input type="password" name="pw_confirma" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
