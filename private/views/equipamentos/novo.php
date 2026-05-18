<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Equipamentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos - Novo</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><form>
  <div class="card mhs-data-card mb-3">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-barcode me-1"></i>Identificação</div>
    <div class="card-body row g-3">
      <div class="col-md-4"><label class="form-label fw-semibold">Código Inventário <span class="text-danger">*</span></label><input type="text" name="codigo_inventario" class="form-control" placeholder="Ex.: EQ-001" required maxlength="50" /></div>
      <div class="col-md-8"><label class="form-label fw-semibold">Designação <span class="text-danger">*</span></label><input type="text" name="designacao" class="form-control" placeholder="Nome do equipamento" required maxlength="200" /></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Categoria</label><select name="id_categoria" class="form-select"><option value="">-- Selecione --</option><option>Monitorizacao</option><option>Diagnostico</option><option>Terapia</option></select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Marca</label><input type="text" name="marca" class="form-control" placeholder="Marca" maxlength="100" /></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Modelo</label><input type="text" name="modelo" class="form-control" placeholder="Modelo" maxlength="100" /></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Número de Série</label><input type="text" name="numero_serie" class="form-control" placeholder="Número de série" maxlength="100" /></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Fabricante</label><input type="text" name="fabricante" class="form-control" placeholder="Fabricante" maxlength="150" /></div>
    </div>
  </div>

  <div class="card mhs-data-card mb-3">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-receipt me-1"></i>Aquisição</div>
    <div class="card-body row g-3">
      <div class="col-md-3"><label class="form-label fw-semibold">Data Aquisição</label><input type="text" name="data_aquisicao" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" /></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Ano Fabrico</label><input type="number" name="ano_fabrico" class="form-control" min="1900" max="2099" placeholder="2026" /></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Custo Aquisição (€)</label><input type="text" name="custo_aquisicao" class="form-control" placeholder="0.00" /></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Tipo de Entrada</label><select name="tipo_entrada" class="form-select"><option value="">-- Selecione --</option><option>Compra</option><option>Doação</option><option>Aluguer</option><option>Empréstimo</option></select></div>
    </div>
  </div>

  <div class="card mhs-data-card mb-3">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-location-dot me-1"></i>Localização e Estado</div>
    <div class="card-body row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">Localização <span class="text-danger">*</span></label><select name="id_localizacao" class="form-select" required><option value="">-- Selecione --</option><option>Bloco Central - Urgencia / Sala 204</option><option>Bloco B - Cardiologia / Sala 12</option><option>Bloco C - UCI / Sala 3</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Estado</label><select name="estado" class="form-select"><option value="">-- Selecione --</option><option>Ativo</option><option>Em manutenção</option><option>Inativo</option><option>Em calibração</option><option>Em quarentena</option><option>Abatido</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Criticidade</label><select name="criticidade" class="form-select"><option value="">-- Selecione --</option><option>Baixa</option><option>Média</option><option>Alta</option><option>Suporte de vida</option></select></div>
      <div class="col-12"><label class="form-label fw-semibold">Observações</label><textarea name="observacoes" class="form-control" rows="2" placeholder="Notas sobre o equipamento"></textarea></div>
    </div>
  </div>

  <div class="card mhs-data-card mb-3">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-truck me-1"></i>Fornecedores Associados</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead class="table-light"><tr><th style="width:30px"></th><th>Fornecedor</th><th style="width:220px">Tipo de Relação</th></tr></thead>
          <tbody>
            <tr><td><input type="checkbox" class="form-check-input" name="fornecedores[]" /></td><td>MedTech Portugal</td><td><select class="form-select form-select-sm"><option value="">-- Tipo --</option><option>Fabricante</option><option>Distribuidor</option><option>Assistência técnica</option><option>Consumíveis</option></select></td></tr>
            <tr><td><input type="checkbox" class="form-check-input" name="fornecedores[]" /></td><td>Clinicare Equipamentos</td><td><select class="form-select form-select-sm"><option value="">-- Tipo --</option><option>Fabricante</option><option>Distribuidor</option><option>Assistência técnica</option><option>Consumíveis</option></select></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mb-4">
    <button type="button" class="btn btn-primary mhs-btn-save"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
