<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Fornecedores - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span><h1 class="mhs-page-title">MedTech Portugal</h1></div>
  <div class="mhs-page-actions">
    <a href="editar.php" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">NIF</span><span class="mhs-detail-summary-val">509123456</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Tipo</span><span class="mhs-detail-summary-val">Fabricante</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Telefone</span><span class="mhs-detail-summary-val">210 000 000</span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Email</span><span class="mhs-detail-summary-val">suporte@medtech.pt</span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="info"><i class="fa-solid fa-address-card"></i> Informação</button>
    <button class="mhs-detail-tab" data-tab="contacto"><i class="fa-solid fa-user"></i> Contacto</button>
    <button class="mhs-detail-tab" data-tab="equipamentos"><i class="fa-solid fa-stethoscope"></i> Equipamentos</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-info">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-address-card"></i> Dados gerais</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd>MedTech Portugal</dd>
          <dt>NIF</dt><dd>509123456</dd>
          <dt>Tipo</dt><dd>Fabricante</dd>
          <dt>Telefone</dt><dd>210 000 000</dd>
          <dt>Email</dt><dd>suporte@medtech.pt</dd>
          <dt>Website</dt><dd>www.medtech.pt</dd>
        </dl>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-contacto">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-5">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Pessoa de contacto</div>
        <dl class="mhs-info-dl">
          <dt>Nome</dt><dd>Ana Ribeiro</dd>
          <dt>Telemóvel</dt><dd>910 000 000</dd>
          <dt>Morada</dt><dd>Rua da Saúde, Lisboa</dd>
        </dl>
      </div>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs">Fornecedor principal de equipamentos e consumíveis.</p>
      </div>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-equipamentos">
    <div class="mhs-tab-body">
      <table class="table mhs-datatable mb-0"><thead><tr><th>Código</th><th>Designação</th><th>Marca</th><th>Tipo de relação</th></tr></thead>
      <tbody>
        <tr><td><span class="mhs-code">EQ-001</span></td><td class="mhs-td-primary">Monitor multiparamétrico</td><td>Philips</td><td>Fabricante</td></tr>
        <tr><td><span class="mhs-code">EQ-003</span></td><td class="mhs-td-primary">Ventilador pulmonar</td><td>Hamilton</td><td>Assistência técnica</td></tr>
      </tbody></table>
    </div>
  </div>
</div>
<script>document.querySelectorAll('.mhs-detail-tab').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.mhs-detail-tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.mhs-tab-pane').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById('tab-'+b.dataset.tab).classList.add('active');});});</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
