<?php
// pagina do perfil do proprio utilizador: trocar a foto e a password
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

// dados do utilizador que esta com sessao aberta
$pdo        = mhs_pdo();
$user_id    = $_SESSION['user_id'] ?? 0;
$user_email = $_SESSION['user_email'] ?? '';
$profile    = $_SESSION['profile'] ?? '';

// vai buscar mais alguns dados a base de dados
$stmt = $pdo->prepare("SELECT id, perfil AS profile, ultimo_acesso, criado_em FROM utilizadores WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$user_id]);
$agent = $stmt->fetch();

$success = '';
$error   = '';

// caminho onde fica guardada a foto de perfil (private/uploads)
$avatar_dir  = __DIR__ . '/../../uploads';
$avatar_file = $avatar_dir . '/' . (int)$user_id . '.png';

// se enviaram uma foto nova, trata do upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['foto']['name'])) {
    if (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $info = @getimagesize($_FILES['foto']['tmp_name']);
        $tipo = $info[2] ?? null;
        if ($tipo && in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)
            && $_FILES['foto']['size'] <= 5 * 1024 * 1024) {
            if (!is_dir($avatar_dir)) { @mkdir($avatar_dir, 0775, true); }
            $src = match ($tipo) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($_FILES['foto']['tmp_name']),
                IMAGETYPE_PNG  => @imagecreatefrompng($_FILES['foto']['tmp_name']),
                IMAGETYPE_WEBP => @imagecreatefromwebp($_FILES['foto']['tmp_name']),
                default        => null,
            };
            if ($src) {
                $w = imagesx($src); $h = imagesy($src);
                $lado = min($w, $h);
                $ox = (int)(($w - $lado) / 2); $oy = (int)(($h - $lado) / 2);
                $size = 256;
                $dst = imagecreatetruecolor($size, $size);
                imagecopyresampled($dst, $src, 0, 0, $ox, $oy, $size, $size, $lado, $lado);
                @imagepng($dst, $avatar_file);
                $_SESSION['success_message'] = 'Foto de perfil atualizada.';
            } else {
                $_SESSION['error_message'] = 'Não foi possível processar a imagem.';
            }
        } else {
            $_SESSION['error_message'] = 'Imagem inválida (use PNG ou JPG até 5 MB).';
        }
    } else {
        $_SESSION['error_message'] = 'Falha ao carregar a imagem.';
    }
    header('Location: perfil.php');
    exit;
}

// se enviaram o formulario de mudar password, trata disso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw_atual    = $_POST['pw_atual']    ?? '';
    $pw_nova     = trim($_POST['pw_nova']    ?? '');
    $pw_confirma = trim($_POST['pw_confirma'] ?? '');

    // vai buscar a password atual guardada para confirmar
    $stmt_pw = $pdo->prepare("SELECT senha FROM utilizadores WHERE id = ?");
    $stmt_pw->execute([$user_id]);
    $row = $stmt_pw->fetch();

    // confirma se a password atual esta certa (com hash ou em texto simples)
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
$avatar_url = file_exists($avatar_file) ? (BASE_URL . '/private/uploads/' . (int)$user_id . '.png?v=' . filemtime($avatar_file)) : '';

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
    <form method="post" enctype="multipart/form-data" id="formFoto" class="mhs-profile-avatar-wrap" title="Mudar foto de perfil">
      <span class="mhs-profile-avatar">
        <?php if ($avatar_url): ?><img src="<?= esc($avatar_url) ?>" alt="Foto de perfil"><?php else: ?><?= esc($iniciais) ?><?php endif; ?>
      </span>
      <label class="mhs-avatar-edit" aria-label="Mudar foto">
        <i class="fa-solid fa-camera"></i>
        <input type="file" name="foto" id="fotoInput" accept="image/png,image/jpeg,image/webp" hidden>
      </label>
    </form>
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
