<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
require_admin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("
    SELECT id, AES_DECRYPT(nome, ?) AS email, perfil AS profile, ultimo_acesso, criado_em
    FROM utilizadores WHERE id = ? AND eliminado_em IS NULL
");
$stmt->execute([MYSQL_AES_KEY, $id]);
$u = $stmt->fetch();
if (!$u) { header('Location: lista.php'); exit; }

$perfil_label = match ($u->profile) { 'admin' => 'Administrador', 'tecnico' => 'Técnico', default => ucfirst((string)$u->profile) };
$is_admin_user = $u->profile === 'admin';

$page_title = 'Utilizadores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-user-gear fa-fw"></i></span><h1 class="mhs-page-title"><?= esc($u->email) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Email</span><span class="mhs-detail-summary-val"><?= esc($u->email) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Perfil</span><span class="mhs-detail-summary-val"><span class="badge <?= $is_admin_user ? 'bg-danger' : 'bg-primary' ?>"><?= esc($perfil_label) ?></span></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Último acesso</span><span class="mhs-detail-summary-val"><?= $u->ultimo_acesso ? date('d/m/Y H:i', strtotime($u->ultimo_acesso)) : '—' ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Membro desde</span><span class="mhs-detail-summary-val"><?= $u->criado_em ? date('d/m/Y', strtotime($u->criado_em)) : '—' ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="conta"><i class="fa-solid fa-user"></i> Conta</button>
    <button class="mhs-detail-tab" data-tab="permissoes"><i class="fa-solid fa-shield-halved"></i> Permissões</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-conta">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Dados da conta</div>
        <dl class="mhs-info-dl">
          <dt>Email</dt><dd><?= esc($u->email) ?></dd>
          <dt>Perfil</dt><dd><?= esc($perfil_label) ?></dd>
          <dt>Último acesso</dt><dd><?= $u->ultimo_acesso ? date('d/m/Y H:i', strtotime($u->ultimo_acesso)) : '—' ?></dd>
          <dt>Criado em</dt><dd><?= $u->criado_em ? date('d/m/Y', strtotime($u->criado_em)) : '—' ?></dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-permissoes">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Permissões — <?= esc($perfil_label) ?></div>
        <ul class="mhs-permissions-list">
          <li><i class="fa-solid fa-check"></i> Consultar e pesquisar todo o inventário</li>
          <li><i class="fa-solid fa-check"></i> Criar e editar registos (equipamentos, documentos, etc.)</li>
          <li><i class="fa-solid fa-check"></i> Registar manutenções, empréstimos e movimentações</li>
          <li><i class="fa-solid <?= $is_admin_user ? 'fa-check' : 'fa-xmark mhs-perm-no' ?>"></i> Apagar registos</li>
          <li><i class="fa-solid <?= $is_admin_user ? 'fa-check' : 'fa-xmark mhs-perm-no' ?>"></i> Ver mensagens do formulário de contacto</li>
          <li><i class="fa-solid <?= $is_admin_user ? 'fa-check' : 'fa-xmark mhs-perm-no' ?>"></i> Gerir utilizadores</li>
          <li><i class="fa-solid <?= $is_admin_user ? 'fa-check' : 'fa-xmark mhs-perm-no' ?>"></i> Editar website público</li>
        </ul>
      </div>
    </div></div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
