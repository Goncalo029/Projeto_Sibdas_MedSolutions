<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $nome_documento = trim($_POST['nome_documento'] ?? '');
    $data_documento = trim($_POST['data_documento'] ?? '') ?: null;
    $data_validade  = trim($_POST['data_validade'] ?? '') ?: null;
    $observacoes    = trim($_POST['observacoes'] ?? '');
    if (!$id_equipamento || !$tipo_documento) {
        $_SESSION['error_message'] = 'Equipamento e Tipo de documento são obrigatórios.';
        header('Location: novo.php'); exit;
    }

    // Código do equipamento para nomear o ficheiro
    $cod = mhs_pdo()->query("SELECT codigo_inventario FROM equipamentos WHERE id = " . $id_equipamento)->fetchColumn() ?: 'doc';
    $erro_upload = null;
    $nome_ficheiro = mhs_guardar_pdf('ficheiro', $cod, $erro_upload);
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header('Location: novo.php'); exit;
    }

    try {
        mhs_pdo()->prepare("INSERT INTO documentos (id_equipamento,tipo_documento,nome_documento,data_documento,data_validade,nome_ficheiro,observacoes,created_at) VALUES (?,?,?,?,?,?,?,NOW())")
            ->execute([$id_equipamento, $tipo_documento, $nome_documento ?: null, $data_documento, $data_validade, $nome_ficheiro, $observacoes ?: null]);
        mhs_historico('documento', (int)mhs_pdo()->lastInsertId(), ($nome_documento ?: $tipo_documento), 'criar');
        $_SESSION['success_message'] = 'Documento criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$equipamentos = mhs_pdo()->query("SELECT id, codigo_inventario, designacao FROM equipamentos ORDER BY codigo_inventario")->fetchAll();
$tipos = ['Manual','Certificado','Contrato','Relatório','Ficha técnica','Outro'];

$page_title = 'Documentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
        <h1 class="mhs-page-title">Documentos - Novo</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-file-lines me-1"></i>Informação do documento</div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" style="max-width:640px">
            <div class="row g-3">
                <div class="col-12">
                    <label for="id_equipamento" class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                    <select id="id_equipamento" name="id_equipamento" class="form-select" required>
                        <option value="">Selecione um equipamento...</option>
                        <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tipo_documento" class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
                    <select id="tipo_documento" name="tipo_documento" class="form-select" required>
                        <option value="">Selecione um tipo...</option>
                        <?php foreach ($tipos as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome do Documento</label>
                    <input type="text" name="nome_documento" class="form-control" placeholder="Ex.: Manual do utilizador" maxlength="200" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data do Documento</label>
                    <input type="text" name="data_documento" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Data de Validade</label>
                    <input type="text" name="data_validade" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Ficheiro PDF</label>
                    <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
                    <div class="form-text">Carregue o documento em PDF (máx. 10 MB). O ficheiro fica guardado no servidor.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas sobre o documento"></textarea>
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
