<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if (!in_array($_SESSION['profile'] ?? '', ['admin', 'tecnico'])) {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$mensagens = [];
$erro_bd = '';
$total_nao_lidas = 0;

try {
    $pdo = mhs_pdo();
    $mensagens = $pdo->query("
        SELECT id, nome, email,
               SUBSTRING(mensagem, 1, 100) AS resumo,
               mensagem, lida, created_at
        FROM mensagens_contacto
        WHERE deleted_at IS NULL
        ORDER BY lida ASC, created_at DESC
    ")->fetchAll();

    $row = $pdo->query("SELECT COUNT(*) AS total FROM mensagens_contacto WHERE lida = 0 AND deleted_at IS NULL")->fetch();
    $total_nao_lidas = (int)($row->total ?? 0);
} catch (PDOException $e) {
    $erro_bd = 'Não foi possível carregar as mensagens.';
}

$page_title = 'Mensagens de Contacto';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-envelope fa-fw"></i></span>
    <h1 class="mhs-page-title">Mensagens de Contacto</h1>
  </div>
  <?php if ($total_nao_lidas > 0): ?>
  <div class="mhs-page-actions">
    <span class="badge bg-danger fs-6 px-3 py-2">
      <i class="fa-solid fa-envelope me-1"></i>
      <?= $total_nao_lidas ?> não <?= $total_nao_lidas === 1 ? 'lida' : 'lidas' ?>
    </span>
  </div>
  <?php endif; ?>
</div>

<?php if ($erro_bd): ?>
  <div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div>
<?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-envelope mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Formulário de Contacto — Website Público</span>
      <span class="mhs-table-toolbar-count"><?= count($mensagens) ?> mensagens</span>
    </div>
  </div>
  <div class="card-body p-0">
    <?php if (empty($mensagens)): ?>
      <div class="mhs-empty-state py-5">
        <i class="fa-regular fa-envelope-open"></i>
        <p>Sem mensagens de contacto de momento.</p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="mensagensTable">
        <thead>
          <tr>
            <th></th>
            <th>Nome</th>
            <th>Email</th>
            <th>Mensagem</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mensagens as $m): ?>
            <tr class="<?= !$m->lida ? 'mhs-row-unread' : '' ?>">
              <td style="width:28px">
                <?php if (!$m->lida): ?>
                  <span class="badge bg-danger" title="Não lida"><i class="fa-solid fa-circle" style="font-size:.5rem"></i></span>
                <?php else: ?>
                  <span class="text-muted" title="Lida"><i class="fa-regular fa-circle-check" style="font-size:.85rem"></i></span>
                <?php endif; ?>
              </td>
              <td class="mhs-td-primary <?= !$m->lida ? 'fw-semibold' : '' ?>"><?= esc($m->nome) ?></td>
              <td><a href="mailto:<?= esc($m->email) ?>"><?= esc($m->email) ?></a></td>
              <td class="text-muted" style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= esc($m->resumo) ?><?= strlen($m->mensagem) > 100 ? '…' : '' ?>
              </td>
              <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($m->created_at)) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="ver.php?id=<?= (int)$m->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver mensagem">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                  <a href="mailto:<?= esc($m->email) ?>?subject=Re: Contacto MedSolutions" class="btn btn-sm btn-outline-primary" title="Responder">
                    <i class="fa-solid fa-reply"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger"
                    data-delete-id="<?= (int)$m->id ?>"
                    data-delete-name="<?= esc($m->nome) ?>"
                    title="Apagar">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.mhs-row-unread { background: #fffbeb; }
.mhs-row-unread:hover { background: #fef3c7 !important; }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
