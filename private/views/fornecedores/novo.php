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
    if (mb_strlen($nome) < 2 || !preg_match('/[a-zA-ZÀ-úÀ-ÖØ-öø-ÿ]/u', $nome)) {
        $_SESSION['error_message'] = 'O nome do fornecedor deve ter pelo menos 2 letras.';
        header('Location: novo.php'); exit;
    }
    if ($nif && !preg_match('/^\d{9}$/', $nif)) {
        $_SESSION['error_message'] = 'O NIF deve ter exatamente 9 dígitos.';
        header('Location: novo.php'); exit;
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Endereço de email inválido.';
        header('Location: novo.php'); exit;
    }
    if ($telefone && strlen(preg_replace('/\D/', '', $telefone)) < 9) {
        $_SESSION['error_message'] = 'Número de telefone inválido (mínimo 9 dígitos).';
        header('Location: novo.php'); exit;
    }
    if (!$pessoa_contacto || !$tel_contacto) {
        $_SESSION['error_message'] = 'O contacto de assistência técnica (nome e telefone) é obrigatório.';
        header('Location: novo.php'); exit;
    }
    if (strlen(preg_replace('/\D/', '', $tel_contacto)) < 9) {
        $_SESSION['error_message'] = 'Telefone de assistência técnica inválido (mínimo 9 dígitos).';
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

$_forn_existentes = mhs_pdo()->query("SELECT nome FROM fornecedores WHERE eliminado_em IS NULL")->fetchAll(PDO::FETCH_COLUMN);
$page_title = 'Fornecedores - Novo';
include __DIR__ . '/../../includes/header.php';
?>
<script>window.MHS_FORN_EX = <?= json_encode(array_values($_forn_existentes)) ?>;</script>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span>
    <h1 class="mhs-page-title">Novo Fornecedor</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" class="mhs-validate-form" novalidate>
  <div class="card mhs-data-card">
    <div class="mhs-detail-tabs">
      <button type="button" class="mhs-detail-tab active" data-tab="dados-gerais">
        <i class="fa-solid fa-address-card"></i> Dados Gerais
      </button>
      <button type="button" class="mhs-detail-tab" data-tab="contacto">
        <i class="fa-solid fa-user"></i> Pessoa de Contacto
      </button>
    </div>

    <!-- Dados Gerais -->
    <div class="mhs-tab-pane active" id="tab-dados-gerais">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-address-card"></i> Dados gerais</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillFornecedor(window.MHS_FORN_EX)"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
          </div>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
              <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do fornecedor" required maxlength="150"
                     value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_nome">Campo obrigatório.</p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">NIF</label>
              <input type="text" name="nif" id="nif" class="form-control" placeholder="9 dígitos" maxlength="9"
                     value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_nif">O NIF deve ter exatamente 9 dígitos.</p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tipo de Fornecedor</label>
              <select name="tipo_fornecedor" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach (['Fabricante','Distribuidor','Assistência Técnica','Outro'] as $t): ?>
                <option <?= ($_POST['tipo_fornecedor'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Telefone</label>
              <input type="text" name="telefone" id="telefone" class="form-control" placeholder="222 333 444" maxlength="20"
                     value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_telefone">Número inválido (mín. 9 dígitos).</p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" name="email" id="email" class="form-control" placeholder="email@fornecedor.pt" maxlength="100"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_email">Endereço de email inválido.</p>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Morada</label>
              <input type="text" name="morada" class="form-control" placeholder="Rua, número, código postal, cidade" maxlength="250"
                     value="<?= htmlspecialchars($_POST['morada'] ?? '') ?>" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Website</label>
              <input type="url" name="website" class="form-control" placeholder="https://www.exemplo.pt" maxlength="150"
                     value="<?= htmlspecialchars($_POST['website'] ?? '') ?>" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pessoa de Contacto -->
    <div class="mhs-tab-pane" id="tab-contacto">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-user"></i> Pessoa de contacto <span class="text-danger">*</span></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nome do contacto <span class="text-danger">*</span></label>
              <input type="text" name="pessoa_contacto" id="pessoa_contacto" class="form-control"
                     placeholder="Nome do técnico/responsável" maxlength="100" required
                     value="<?= htmlspecialchars($_POST['pessoa_contacto'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_pessoa_contacto">Campo obrigatório.</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tel. Assistência <span class="text-danger">*</span></label>
              <input type="text" name="tel_contacto" id="tel_contacto" class="form-control"
                     placeholder="912 345 678" maxlength="20" required
                     value="<?= htmlspecialchars($_POST['tel_contacto'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_tel_contacto">Número inválido (mín. 9 dígitos).</p>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Observações</label>
              <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas internas"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
            </div>
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
