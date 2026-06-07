<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if (!in_array($_SESSION['profile'] ?? '', ['admin', 'tecnico'])) {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = null;
$erro = '';

if ($id > 0) {
    try {
        $pdo = mhs_pdo();
        $stmt = $pdo->prepare("SELECT * FROM mensagens_contacto WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $msg = $stmt->fetch();

        if ($msg && !$msg->lida) {
            $pdo->prepare("UPDATE mensagens_contacto SET lida = 1, updated_at = NOW() WHERE id = ?")->execute([$id]);
        }
    } catch (PDOException $e) {
        $erro = 'Não foi possível carregar a mensagem.';
    }
}

if (!$msg && !$erro) {
    header('Location: lista.php');
    exit;
}

$page_title = 'Mensagem de Contacto';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-envelope-open fa-fw"></i></span>
    <h1 class="mhs-page-title">Mensagem de Contacto</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="mailto:<?= esc($msg->email) ?>?subject=Re: Contacto MedSolutions" class="btn btn-primary">
      <i class="fa-solid fa-reply me-2"></i>Responder
    </a>
    <button type="button" class="btn btn-outline-danger"
      data-delete-id="<?= (int)$msg->id ?>"
      data-delete-name="<?= esc($msg->nome) ?>">
      <i class="fa-solid fa-trash me-2"></i>Apagar
    </button>
    <a href="lista.php" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left me-2"></i>Voltar
    </a>
  </div>
</div>

<?php if ($erro): ?>
  <div class="alert alert-warning"><?= esc($erro) ?></div>
<?php endif; ?>

<?php if ($msg): ?>
<div class="card mhs-data-card mb-4">
  <div class="mhs-tab-body">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Remetente</div>
          <dl class="mhs-info-dl">
            <dt>Nome</dt><dd><?= esc($msg->nome) ?></dd>
            <dt>Email</dt>
            <dd><a href="mailto:<?= esc($msg->email) ?>"><?= esc($msg->email) ?></a></dd>
            <dt>Data</dt><dd><?= date('d/m/Y \à\s H:i', strtotime($msg->created_at)) ?></dd>
            <dt>Estado</dt>
            <dd>
              <?php if ($msg->lida): ?>
                <span class="badge bg-secondary"><i class="fa-regular fa-circle-check me-1"></i>Lida</span>
              <?php else: ?>
                <span class="badge bg-danger"><i class="fa-solid fa-circle me-1"></i>Não lida</span>
              <?php endif; ?>
            </dd>
          </dl>
        </div>
      </div>
      <div class="col-md-8">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-message"></i> Mensagem</div>
          <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;line-height:1.75;white-space:pre-wrap;word-break:break-word"><?= esc($msg->mensagem) ?></div>
        </div>
        <div class="mt-3">
          <a href="mailto:<?= esc($msg->email) ?>?subject=Re: Contacto MedSolutions&body=<?= rawurlencode("\n\n--- Mensagem original de " . $msg->nome . " ---\n" . $msg->mensagem) ?>"
             class="btn btn-primary">
            <i class="fa-solid fa-reply me-2"></i>Responder por email
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
