<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $nome_documento = trim($_POST['nome_documento'] ?? '');
    $data_documento = trim($_POST['data_documento'] ?? '') ?: null;
    $data_validade  = trim($_POST['data_validade'] ?? '') ?: null;
    $observacoes    = trim($_POST['observacoes'] ?? '');
    if (!$id_equipamento || !$tipo_documento) {
        $_SESSION['error_message'] = 'Equipamento e Tipo de documento são obrigatórios.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        mhs_pdo()->prepare("UPDATE documentos SET id_equipamento=?,tipo_documento=?,nome_documento=?,data_documento=?,data_validade=?,observacoes=?,updated_at=NOW() WHERE id=?")
            ->execute([$id_equipamento, $tipo_documento, $nome_documento ?: null, $data_documento, $data_validade, $observacoes ?: null, $id]);
        $_SESSION['success_message'] = 'Documento atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM documentos WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$equipamentos = $pdo->query("SELECT id, codigo_inventario, designacao FROM equipamentos ORDER BY codigo_inventario")->fetchAll();
$tipos = ['Manual','Certificado','Contrato','Relatório','Ficha técnica','Outro'];

$page_title = 'Documentos - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
        <h1 class="mhs-page-title">Documentos - Editar</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-file-lines me-1"></i>Informação do documento</div>
    <div class="card-body">
        <form method="POST" action="" style="max-width:640px">
            <input type="hidden" name="id" value="<?= $row->id ?>">
            <div class="row g-3">
                <div class="col-12">
                    <label for="id_equipamento" class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                    <select id="id_equipamento" name="id_equipamento" class="form-select" required>
                        <option value="">Selecione um equipamento...</option>
                        <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq->id ?>" <?= $row->id_equipamento == $eq->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tipo_documento" class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                    <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                        <option value="">Selecione um tipo...</option>
                        <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t ?>" <?= ($row->tipo_documento ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome do Documento</label>
                    <input type="text" name="nome_documento" class="form-control" value="<?= htmlspecialchars($row->nome_documento ?? '') ?>" maxlength="200" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data do Documento</label>
                    <input type="text" name="data_documento" class="form-control mhs-datepicker" value="<?= htmlspecialchars($row->data_documento ?? '') ?>" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data de Validade</label>
                    <input type="text" name="data_validade" class="form-control mhs-datepicker" value="<?= htmlspecialchars($row->data_validade ?? '') ?>" placeholder="AAAA-MM-DD" />
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
