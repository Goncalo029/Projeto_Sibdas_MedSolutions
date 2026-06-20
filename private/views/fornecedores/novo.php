<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome'] ?? '');
    $nif             = trim($_POST['nif'] ?? '');
    $tipo_fornecedor = trim($_POST['tipo_fornecedor'] ?? '');
    $telefone        = trim($_POST['telefone'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $morada          = trim($_POST['morada'] ?? '');
    $website         = trim($_POST['website'] ?? '');
    $pessoa_contacto = trim($_POST['pessoa_contacto'] ?? '');
    $tel_contacto    = trim($_POST['tel_contacto'] ?? '');
    $observacoes     = trim($_POST['observacoes'] ?? '');
    if (!$nome) {
        $_SESSION['error_message'] = 'O campo Nome é obrigatório.';
        header('Location: novo.php'); exit;
    }
    try {
        mhs_pdo()->prepare("INSERT INTO fornecedores (nome,nif,tipo_fornecedor,telefone,email,morada,website,pessoa_contacto,tel_contacto,observacoes,criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,NOW())")
            ->execute([$nome, $nif ?: null, $tipo_fornecedor ?: null, $telefone ?: null, $email ?: null, $morada ?: null, $website ?: null, $pessoa_contacto ?: null, $tel_contacto ?: null, $observacoes ?: null]);
        mhs_historico('fornecedor', (int)mhs_pdo()->lastInsertId(), $nome, 'criar');
        $_SESSION['success_message'] = 'Fornecedor criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$page_title = 'Fornecedores - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span>
    <h1 class="mhs-page-title">Novo Fornecedor</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-address-card"></i> Dados gerais</div>
        <div class="row g-3 mt-1">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" class="form-control" placeholder="Nome do fornecedor" required maxlength="150" />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">NIF</label>
            <input type="text" name="nif" class="form-control" placeholder="9 dígitos" maxlength="9" pattern="[0-9]{9}" />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tipo de Fornecedor</label>
            <select name="tipo_fornecedor" class="form-select">
              <option value="">-- Selecione --</option>
              <option>Fabricante</option>
              <option>Distribuidor</option>
              <option>Assistência Técnica</option>
              <option>Outro</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Telefone</label>
            <input type="text" name="telefone" class="form-control" placeholder="Telefone geral" maxlength="20" />
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@fornecedor.pt" maxlength="100" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Morada</label>
            <input type="text" name="morada" class="form-control" placeholder="Morada completa" maxlength="250" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Website</label>
            <input type="url" name="website" class="form-control" placeholder="https://www.exemplo.pt" maxlength="150" />
          </div>
        </div>
      </div>

      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Pessoa de contacto</div>
        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nome do contacto</label>
            <input type="text" name="pessoa_contacto" class="form-control" placeholder="Nome do contacto" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tel. Contacto</label>
            <input type="text" name="tel_contacto" class="form-control" placeholder="Contacto direto" maxlength="20" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas internas"></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Fornecedor</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
