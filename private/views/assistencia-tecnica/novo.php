<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa           = trim($_POST['empresa'] ?? '');
    $nome_contacto     = trim($_POST['nome_contacto'] ?? '');
    $email             = trim($_POST['email'] ?? '');
    $telefone          = trim($_POST['telefone'] ?? '');
    $telefone_urgencia = trim($_POST['telefone_urgencia'] ?? '');
    $observacoes       = trim($_POST['observacoes'] ?? '');

    if (!$empresa || !$nome_contacto) {
        $_SESSION['error_message'] = 'Os campos Empresa e Nome do Contacto são obrigatórios.';
        header('Location: novo.php'); exit;
    }

    try {
        mhs_pdo()->prepare("
            INSERT INTO assistencia_tecnica
                (empresa, nome_contacto, email, telefone, telefone_urgencia, observacoes)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
            $empresa,
            $nome_contacto,
            $email ?: null,
            $telefone ?: null,
            $telefone_urgencia ?: null,
            $observacoes ?: null,
        ]);
        $_SESSION['success_message'] = 'Contacto de assistência técnica criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$page_title = 'Assistência Técnica - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
    <h1 class="mhs-page-title">Novo Contacto de Assistência Técnica</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-header fw-bold bg-primary text-white">
    <i class="fa-solid fa-headset me-2"></i>Informação do Contacto
  </div>
  <div class="card-body">
    <form method="POST" action="" style="max-width:720px">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Empresa <span class="text-danger">*</span></label>
          <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa" required maxlength="190" />
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nome do Contacto <span class="text-danger">*</span></label>
          <input type="text" name="nome_contacto" class="form-control" placeholder="Nome da pessoa de contacto" required maxlength="190" />
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" placeholder="contacto@empresa.pt" maxlength="190" />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Telefone</label>
          <input type="text" name="telefone" class="form-control" placeholder="Telefone geral" maxlength="30" />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Telefone de Urgência</label>
          <div class="input-group">
            <span class="input-group-text bg-danger text-white border-danger"><i class="fa-solid fa-phone"></i></span>
            <input type="text" name="telefone_urgencia" class="form-control border-danger" placeholder="Linha de urgência" maxlength="30" />
          </div>
          <div class="form-text text-danger">Contacto para situações urgentes</div>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="3" placeholder="Horários de atendimento, contrato de assistência, etc."></textarea>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
