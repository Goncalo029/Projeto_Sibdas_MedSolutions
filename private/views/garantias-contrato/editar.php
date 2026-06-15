<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento       = (int)($_POST['id_equipamento'] ?? 0);
    $data_inicio          = trim($_POST['data_inicio'] ?? '') ?: null;
    $data_fim             = trim($_POST['data_fim'] ?? '') ?: null;
    $tem_contrato         = isset($_POST['tem_contrato']) ? 1 : 0;
    $tipo_contrato        = trim($_POST['tipo_contrato'] ?? '');
    $entidade_responsavel = trim($_POST['entidade_responsavel'] ?? '');
    $periodicidade        = trim($_POST['periodicidade'] ?? '');
    $observacoes          = trim($_POST['observacoes'] ?? '');
    if (!$id_equipamento) {
        $_SESSION['error_message'] = 'O campo Equipamento é obrigatório.';
        header("Location: editar.php?id=$id"); exit;
    }
    // Importar/substituir ficheiro PDF (opcional)
    $eq_cod_pre = mhs_pdo()->query("SELECT codigo_inventario FROM equipamentos WHERE id = " . $id_equipamento)->fetchColumn() ?: 'GAR';
    $erro_upload = null;
    $novo_ficheiro = mhs_guardar_pdf('ficheiro', 'GAR_' . $eq_cod_pre, $erro_upload, 'garantias');
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header("Location: editar.php?id=$id"); exit;
    }

    try {
        if ($novo_ficheiro) {
            $antigo = mhs_pdo()->query("SELECT nome_ficheiro FROM garantias_contratos WHERE id = " . (int)$id)->fetchColumn();
            if ($antigo) {
                $p = __DIR__ . '/../../uploads/garantias/' . basename($antigo);
                if (is_file($p)) { @unlink($p); }
            }
            mhs_pdo()->prepare("UPDATE garantias_contratos SET id_equipamento=?,data_inicio=?,data_fim=?,tem_contrato=?,tipo_contrato=?,entidade_responsavel=?,periodicidade=?,observacoes=?,nome_ficheiro=?,updated_at=NOW() WHERE id=?")
                ->execute([$id_equipamento, $data_inicio, $data_fim, $tem_contrato, $tipo_contrato ?: null, $entidade_responsavel ?: null, $periodicidade ?: null, $observacoes ?: null, $novo_ficheiro, $id]);
        } else {
            mhs_pdo()->prepare("UPDATE garantias_contratos SET id_equipamento=?,data_inicio=?,data_fim=?,tem_contrato=?,tipo_contrato=?,entidade_responsavel=?,periodicidade=?,observacoes=?,updated_at=NOW() WHERE id=?")
                ->execute([$id_equipamento, $data_inicio, $data_fim, $tem_contrato, $tipo_contrato ?: null, $entidade_responsavel ?: null, $periodicidade ?: null, $observacoes ?: null, $id]);
        }
        $eq_cod = mhs_pdo()->query("SELECT codigo_inventario FROM equipamentos WHERE id = " . $id_equipamento)->fetchColumn();
        mhs_historico('garantia-contrato', $id, 'Equipamento ' . ($eq_cod ?: $id_equipamento), 'editar');
        $_SESSION['success_message'] = 'Garantia/Contrato atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM garantias_contratos WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$equipamentos = $pdo->query("SELECT id, codigo_inventario, designacao FROM equipamentos ORDER BY codigo_inventario")->fetchAll();

$periodicidades = ['Mensal','Trimestral','Semestral','Anual','Bianual'];

$page_title = 'Garantias/Contratos - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
        <h1 class="mhs-page-title">Garantias/Contratos - Editar</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Informação da garantia/contrato</div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" style="max-width:720px">
            <input type="hidden" name="id" value="<?= $row->id ?>">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                    <select name="id_equipamento" class="form-select" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq->id ?>" <?= $row->id_equipamento == $eq->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Início</label>
                    <input type="text" name="data_inicio" class="form-control mhs-datepicker" value="<?= htmlspecialchars($row->data_inicio ?? '') ?>" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Fim</label>
                    <input type="text" name="data_fim" class="form-control mhs-datepicker" value="<?= htmlspecialchars($row->data_fim ?? '') ?>" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tem_contrato" id="tem_contrato" value="1" <?= $row->tem_contrato ? 'checked' : '' ?> />
                        <label class="form-check-label fw-semibold" for="tem_contrato">Tem contrato de manutenção</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo de Contrato</label>
                    <input type="text" name="tipo_contrato" class="form-control" value="<?= htmlspecialchars($row->tipo_contrato ?? '') ?>" maxlength="100" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Entidade Responsável</label>
                    <input type="text" name="entidade_responsavel" class="form-control" value="<?= htmlspecialchars($row->entidade_responsavel ?? '') ?>" maxlength="150" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Periodicidade</label>
                    <select name="periodicidade" class="form-select">
                        <option value="">-- Selecione --</option>
                        <?php foreach ($periodicidades as $p): ?>
                        <option <?= ($row->periodicidade ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Documento PDF</label>
                    <?php if (!empty($row->nome_ficheiro)): ?>
                      <div class="mb-2">
                        <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row->nome_ficheiro) ?></span>
                        <a href="lista.php?ficheiro=<?= (int)$row->id ?>" class="small ms-2">Descarregar atual</a>
                      </div>
                    <?php endif; ?>
                    <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
                    <div class="form-text">Deixe vazio para manter. Carregar um novo substitui o anterior (máx. 10 MB).</div>
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
