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
$nome_display = ucfirst(strstr($user_email, '@', true) ?: $user_email);
$ultimo_acesso_fmt = $agent && $agent->ultimo_acesso ? date('d/m/Y H:i', strtotime($agent->ultimo_acesso)) : '—';
$membro_desde_fmt  = $agent && $agent->criado_em ? date('d/m/Y', strtotime($agent->criado_em)) : '—';

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

<!-- Hero do perfil -->
<div class="mhs-profile-hero">
  <div class="mhs-profile-hero-inner">
    <span class="mhs-profile-avatar"><?= esc($iniciais) ?></span>
    <div class="mhs-profile-id">
      <h2><?= esc($nome_display) ?></h2>
      <span class="email"><i class="fa-solid fa-envelope me-1"></i><?= esc($user_email) ?></span>
      <span class="role"><i class="fa-solid <?= $profile === 'admin' ? 'fa-user-shield' : 'fa-user-gear' ?>"></i> <?= esc($profile_label) ?></span>
    </div>
    <div class="mhs-profile-stats">
      <div class="mhs-profile-stat"><span class="k">Último acesso</span><span class="v"><?= esc($ultimo_acesso_fmt) ?></span></div>
      <div class="mhs-profile-stat"><span class="k">Membro desde</span><span class="v"><?= esc($membro_desde_fmt) ?></span></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Informações da conta -->
  <div class="col-lg-6">
    <div class="mhs-profile-card">
      <div class="mhs-profile-card-head"><span class="chip"><i class="fa-solid fa-id-card"></i></span> Informações da Conta</div>
      <div class="mhs-profile-card-body">
        <div class="mhs-profile-info-row">
          <span class="ico"><i class="fa-solid fa-envelope"></i></span>
          <span><span class="k">Email</span><span class="v"><?= esc($user_email) ?></span></span>
        </div>
        <div class="mhs-profile-info-row">
          <span class="ico"><i class="fa-solid <?= $profile === 'admin' ? 'fa-user-shield' : 'fa-user-gear' ?>"></i></span>
          <span><span class="k">Perfil</span><span class="v"><span class="badge <?= $profile === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= esc($profile_label) ?></span></span></span>
        </div>
        <div class="mhs-profile-info-row">
          <span class="ico"><i class="fa-solid fa-clock"></i></span>
          <span><span class="k">Último acesso</span><span class="v"><?= esc($ultimo_acesso_fmt) ?></span></span>
        </div>
        <div class="mhs-profile-info-row">
          <span class="ico"><i class="fa-solid fa-calendar-check"></i></span>
          <span><span class="k">Membro desde</span><span class="v"><?= esc($membro_desde_fmt) ?></span></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Alterar password -->
  <div class="col-lg-6">
    <div class="mhs-profile-card">
      <div class="mhs-profile-card-head"><span class="chip"><i class="fa-solid fa-lock"></i></span> Alterar Password</div>
      <div class="mhs-profile-card-body">
        <form method="post" action="perfil.php" id="formPassword">
          <div class="mb-3">
            <label class="form-label fw-semibold">Password Atual <span class="text-danger">*</span></label>
            <div class="mhs-pw-wrap">
              <input type="password" name="pw_atual" class="form-control" required>
              <button type="button" class="mhs-pw-toggle" tabindex="-1" aria-label="Mostrar/ocultar"><i class="fa-regular fa-eye"></i></button>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nova Password <span class="text-danger">*</span></label>
            <div class="mhs-pw-wrap">
              <input type="password" name="pw_nova" id="pwNova" class="form-control" required minlength="6">
              <button type="button" class="mhs-pw-toggle" tabindex="-1" aria-label="Mostrar/ocultar"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="mhs-pw-meter"><span id="pwMeter"></span></div>
            <div class="mhs-pw-hint" id="pwHint">Mínimo 6 caracteres. Use letras, números e símbolos.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Confirmar Nova Password <span class="text-danger">*</span></label>
            <div class="mhs-pw-wrap">
              <input type="password" name="pw_confirma" id="pwConfirma" class="form-control" required minlength="6">
              <button type="button" class="mhs-pw-toggle" tabindex="-1" aria-label="Mostrar/ocultar"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="mhs-pw-hint" id="pwMatch"></div>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
