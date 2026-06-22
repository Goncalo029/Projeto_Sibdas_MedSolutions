<?php
/**
 * Nova categoria
 * Formulário simples para criar uma nova categoria de equipamentos.
 */

require_once __DIR__ . '/../../includes/funcoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// ─── Processar o formulário quando é submetido ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    if (!$nome) {
        $_SESSION['error_message'] = 'O campo Nome é obrigatório.';
        header('Location: nova.php'); exit;
    }
    try {
        mhs_pdo()->prepare("INSERT INTO categorias (nome, descricao, criado_em) VALUES (?,?,NOW())")
            ->execute([$nome, $descricao ?: null]);
        mhs_historico('categoria', (int)mhs_pdo()->lastInsertId(), $nome, 'criar');
        $_SESSION['success_message'] = 'Categoria criada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: nova.php'); exit;
    }
}

$page_title = 'Categorias - Nova';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span>
    <h1 class="mhs-page-title">Nova Categoria</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-tags"></i> Informação da categoria</div>
        <div class="row g-3 mt-1">
          <div class="col-12">
            <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" class="form-control" placeholder="Ex.: Monitorização" required maxlength="100" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Descrição</label>
            <textarea name="descricao" class="form-control" placeholder="Descrição da categoria..." rows="4" maxlength="500"></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Categoria</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
