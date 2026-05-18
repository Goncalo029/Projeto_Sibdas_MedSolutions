<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
$page_title = 'Fornecedores - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Fornecedores - Editar</h1>
  </div>
  <div class="mhs-page-actions">
    
  </div>
</div><div class="card border-0 shadow-sm">
  <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-address-card me-1"></i>Informação do fornecedor</div>
  <div class="card-body">
    <form style="max-width:720px">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
          <input type="text" name="nome" class="form-control" value="MedTech Portugal" required maxlength="150" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">NIF</label>
          <input type="text" name="nif" class="form-control" value="509123456" maxlength="9" pattern="[0-9]{9}" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tipo de Fornecedor</label>
          <select name="tipo_fornecedor" class="form-select">
            <option value="">-- Selecione --</option>
            <option selected>Fabricante</option>
            <option>Distribuidor</option>
            <option>Assistência Técnica</option>
            <option>Outro</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Telefone</label>
          <input type="text" name="telefone" class="form-control" value="210 000 000" maxlength="20" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" value="suporte@medtech.pt" maxlength="100" />
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Morada</label>
          <input type="text" name="morada" class="form-control" value="Rua da Saude, Lisboa" maxlength="250" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Website</label>
          <input type="url" name="website" class="form-control" value="https://www.medtech.pt" maxlength="150" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Pessoa de Contacto</label>
          <input type="text" name="pessoa_contacto" class="form-control" value="Ana Ribeiro" maxlength="100" />
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tel. Contacto</label>
          <input type="text" name="tel_contacto" class="form-control" value="910 000 000" maxlength="20" />
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="2">Fornecedor principal de equipamentos e consumiveis.</textarea>
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
