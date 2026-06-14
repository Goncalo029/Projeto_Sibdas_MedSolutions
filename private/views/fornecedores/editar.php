<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

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
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        mhs_pdo()->prepare("UPDATE fornecedores SET nome=?,nif=?,tipo_fornecedor=?,telefone=?,email=?,morada=?,website=?,pessoa_contacto=?,tel_contacto=?,observacoes=?,updated_at=NOW() WHERE id=?")
            ->execute([$nome, $nif ?: null, $tipo_fornecedor ?: null, $telefone ?: null, $email ?: null, $morada ?: null, $website ?: null, $pessoa_contacto ?: null, $tel_contacto ?: null, $observacoes ?: null, $id]);
        mhs_historico('fornecedor', $id, $nome, 'editar');
        $_SESSION['success_message'] = 'Fornecedor atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$stmt = mhs_pdo()->prepare("SELECT * FROM fornecedores WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Fornecedores - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
        <h1 class="mhs-page-title">Fornecedores - Editar</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-address-card me-1"></i>Informação do fornecedor</div>
    <div class="card-body">
        <form method="POST" action="" style="max-width:720px">
            <input type="hidden" name="id" value="<?= $row->id ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($row->nome) ?>" required maxlength="150" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">NIF</label>
                    <input type="text" name="nif" class="form-control" value="<?= htmlspecialchars($row->nif ?? '') ?>" maxlength="9" pattern="[0-9]{9}" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo de Fornecedor</label>
                    <select name="tipo_fornecedor" class="form-select">
                        <option value="">-- Selecione --</option>
                        <?php foreach (['Fabricante','Distribuidor','Assistência Técnica','Outro'] as $opt): ?>
                        <option <?= ($row->tipo_fornecedor ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($row->telefone ?? '') ?>" maxlength="20" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row->email ?? '') ?>" maxlength="100" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Morada</label>
                    <input type="text" name="morada" class="form-control" value="<?= htmlspecialchars($row->morada ?? '') ?>" maxlength="250" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Website</label>
                    <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($row->website ?? '') ?>" maxlength="150" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Pessoa de Contacto</label>
                    <input type="text" name="pessoa_contacto" class="form-control" value="<?= htmlspecialchars($row->pessoa_contacto ?? '') ?>" maxlength="100" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tel. Contacto</label>
                    <input type="text" name="tel_contacto" class="form-control" value="<?= htmlspecialchars($row->tel_contacto ?? '') ?>" maxlength="20" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="lista.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
