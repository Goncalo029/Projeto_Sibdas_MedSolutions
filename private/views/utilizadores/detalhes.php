<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Utilizadores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-user-gear fa-fw"></i></span><h1 class="mhs-page-title">Técnico Hospitalar</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Email</span><span class="mhs-detail-summary-val">tecnico@hospital.pt</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Perfil</span><span class="mhs-detail-summary-val">Técnico</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Estado</span><span class="mhs-detail-summary-val mhs-detail-summary-val--ok">Ativo</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Último acesso</span><span class="mhs-detail-summary-val">12/04/2026 10:35</span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="conta"><i class="fa-solid fa-user"></i> Conta</button>
    <button class="mhs-detail-tab" data-tab="permissoes"><i class="fa-solid fa-shield-halved"></i> Permissões</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-conta">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Dados da conta</div>
        <dl class="mhs-info-dl">
          <dt>Email</dt><dd>tecnico@hospital.pt</dd>
          <dt>Perfil</dt><dd>Técnico</dd>
          <dt>Estado</dt><dd>Ativo</dd>
          <dt>Último acesso</dt><dd>12/04/2026 10:35</dd>
          <dt>Criado em</dt><dd>01/01/2026</dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-permissoes">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Permissões — Técnico</div>
        <ul class="mhs-permissions-list">
          <li><i class="fa-solid fa-check"></i> Consultar equipamentos</li>
          <li><i class="fa-solid fa-check"></i> Registar documentos</li>
          <li><i class="fa-solid fa-check"></i> Atualizar localizações e contratos</li>
          <li><i class="fa-solid fa-check"></i> Ver notificações</li>
          <li><i class="fa-solid fa-xmark mhs-perm-no"></i> Gerir utilizadores</li>
          <li><i class="fa-solid fa-xmark mhs-perm-no"></i> Editar website público</li>
        </ul>
      </div>
    </div></div></div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
