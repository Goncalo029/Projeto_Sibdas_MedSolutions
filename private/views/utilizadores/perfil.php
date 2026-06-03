<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$pdo        = mhs_pdo();
$user_id    = $_SESSION['user_id'] ?? 0;
$user_email = $_SESSION['user_email'] ?? '';
$profile    = $_SESSION['profile'] ?? '';

// Buscar dados do utilizador
$stmt = $pdo->prepare("SELECT id, profile, last_login, created_at FROM agents WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$user_id]);
$agent = $stmt->fetch();

// Processamento de alteração de password
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pw_atual'], $_POST['pw_nova'], $_POST['pw_confirma'])) {
    $pw_atual    = $_POST['pw_atual'] ?? '';
    $pw_nova     = trim($_POST['pw_nova'] ?? '');
    $pw_confirma = trim($_POST['pw_confirma'] ?? '');

    $stmt_pw = $pdo->prepare("SELECT passwrd FROM agents WHERE id = ?");
    $stmt_pw->execute([$user_id]);
    $row = $stmt_pw->fetch();

    $valid = $row && (password_verify($pw_atual, $row->passwrd) || $pw_atual === $row->passwrd);

    if (!$valid) {
        $error = 'Password atual incorreta.';
    } elseif (strlen($pw_nova) < 6) {
        $error = 'A nova password deve ter pelo menos 6 caracteres.';
    } elseif ($pw_nova !== $pw_confirma) {
        $error = 'As passwords não coincidem.';
    } else {
        $hash = password_hash($pw_nova, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE agents SET passwrd = ?, updated_at = NOW() WHERE id = ?")->execute([$hash, $user_id]);
        $success = 'Password alterada com sucesso.';
    }
}

$profile_label = match($profile) {
    'admin'   => 'Administrador',
    'tecnico' => 'Técnico',
    default   => ucfirst($profile)
};

$page_title = 'Meu Perfil';
include __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <h2 class="fw-bold mb-0"><i class="fa-solid fa-user-circle me-2"></i>Meu Perfil</h2>
</div>
<hr>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-circle-check me-2"></i><?= esc($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fa-solid fa-circle-exclamation me-2"></i><?= esc($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-id-card me-2"></i>Informações da Conta</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Email</dt>
                    <dd class="col-7"><?= esc($user_email) ?></dd>
                    <dt class="col-5">Perfil</dt>
                    <dd class="col-7"><span class="badge <?= $profile === 'admin' ? 'bg-danger' : 'bg-primary' ?>"><?= esc($profile_label) ?></span></dd>
                    <dt class="col-5">Último acesso</dt>
                    <dd class="col-7"><?= $agent && $agent->last_login ? date('d/m/Y H:i', strtotime($agent->last_login)) : '—' ?></dd>
                    <dt class="col-5">Membro desde</dt>
                    <dd class="col-7"><?= $agent && $agent->created_at ? date('d/m/Y', strtotime($agent->created_at)) : '—' ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-lock me-2"></i>Alterar Password</div>
            <div class="card-body">
                <form method="post" action="perfil.php">
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
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
