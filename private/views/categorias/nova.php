<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    if (!$nome) {
        $_SESSION['error_message'] = 'O campo Nome é obrigatório.';
        header('Location: nova.php'); exit;
    }
    try {
        mhs_pdo()->prepare("INSERT INTO categorias (nome, descricao, created_at) VALUES (?,?,NOW())")
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

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
        <h1 class="mhs-page-title">Categorias - Nova</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-tags me-1"></i>Informação da categoria</div>
    <div class="card-body">
        <form method="POST" action="" style="max-width:600px">
            <div class="mb-3">
                <label for="nome" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex.: Monitorização" required maxlength="100" />
            </div>
            <div class="mb-3">
                <label for="descricao" class="form-label fw-semibold">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição da categoria..." rows="4" maxlength="500"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>
                <a href="lista.php" class="btn btn-secondary"><i class="fa-solid fa-times me-2"></i>Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
