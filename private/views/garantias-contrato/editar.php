<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Garantias-Contrato - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Garantias-Contrato - Editar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Informação do contrato</div>
  <div class="card-body">
    <form style="max-width:720px">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
          <select name="id_equipamento" class="form-select" required>
            <option value="">-- Selecione --</option>
            <option selected>EQ-001 - Monitor multiparametrico</option>
            <option>EQ-002 - Bomba de infusao</option>
            <option>EQ-003 - Ventilador pulmonar</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Data Início</label>
          <input type="text" name="data_inicio" class="form-control mhs-datepicker" value="2026-01-10" placeholder="AAAA-MM-DD" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Data Fim</label>
          <input type="text" name="data_fim" class="form-control mhs-datepicker" value="2028-01-10" placeholder="AAAA-MM-DD" />
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" name="tem_contrato" id="tem_contrato" value="1" checked />
            <label class="form-check-label fw-semibold" for="tem_contrato">Tem contrato de manutenção</label>
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tipo de Contrato</label>
          <input type="text" name="tipo_contrato" class="form-control" value="Manutencao preventiva" maxlength="100" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Entidade Responsável</label>
          <input type="text" name="entidade_responsavel" class="form-control" value="MedTech Portugal" maxlength="150" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Periodicidade</label>
          <select name="periodicidade" class="form-select">
            <option value="">-- Selecione --</option>
            <option>Mensal</option>
            <option selected>Trimestral</option>
            <option>Semestral</option>
            <option>Anual</option>
            <option>Bianual</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="2">Contrato inclui visitas preventivas e suporte tecnico prioritario.</textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-primary mhs-btn-save"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
