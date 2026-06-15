<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

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
        header('Location: novo.php'); exit;
    }
    // Importar ficheiro PDF do contrato (opcional)
    $eq_cod_pre = mhs_pdo()->query("SELECT codigo_inventario FROM equipamentos WHERE id = " . $id_equipamento)->fetchColumn() ?: 'GAR';
    $erro_upload = null;
    $nome_ficheiro = mhs_guardar_pdf('ficheiro', 'GAR_' . $eq_cod_pre, $erro_upload, 'garantias');
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header('Location: novo.php'); exit;
    }

    try {
        mhs_pdo()->prepare("INSERT INTO garantias_contratos (id_equipamento,data_inicio,data_fim,tem_contrato,tipo_contrato,entidade_responsavel,periodicidade,observacoes,nome_ficheiro,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())")
            ->execute([$id_equipamento, $data_inicio, $data_fim, $tem_contrato, $tipo_contrato ?: null, $entidade_responsavel ?: null, $periodicidade ?: null, $observacoes ?: null, $nome_ficheiro]);
        $g_id = (int)mhs_pdo()->lastInsertId();
        $eq_cod = mhs_pdo()->query("SELECT codigo_inventario FROM equipamentos WHERE id = " . $id_equipamento)->fetchColumn();
        mhs_historico('garantia-contrato', $g_id, 'Equipamento ' . ($eq_cod ?: $id_equipamento), 'criar');
        $_SESSION['success_message'] = 'Garantia/Contrato criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$pdo = mhs_pdo();
$equipamentos = $pdo->query("SELECT id, codigo_inventario, designacao FROM equipamentos ORDER BY codigo_inventario")->fetchAll();

$page_title = 'Garantias/Contratos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
        <h1 class="mhs-page-title">Garantias/Contratos - Novo</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-shield-halved me-1"></i>Informação da garantia/contrato</div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" style="max-width:720px">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
                    <select name="id_equipamento" class="form-select" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($equipamentos as $eq): ?>
                        <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Início</label>
                    <input type="text" name="data_inicio" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Fim</label>
                    <input type="text" name="data_fim" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" />
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tem_contrato" id="tem_contrato" value="1" />
                        <label class="form-check-label fw-semibold" for="tem_contrato">Tem contrato de manutenção</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipo de Contrato</label>
                    <input type="text" name="tipo_contrato" class="form-control" placeholder="Ex.: Preventiva" maxlength="100" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Entidade Responsável</label>
                    <input type="text" name="entidade_responsavel" class="form-control" placeholder="Fornecedor ou entidade" maxlength="150" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Periodicidade</label>
                    <select name="periodicidade" class="form-select">
                        <option value="">-- Selecione --</option>
                        <option>Mensal</option>
                        <option>Trimestral</option>
                        <option>Semestral</option>
                        <option>Anual</option>
                        <option>Bianual</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas da garantia ou contrato"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Importar documento PDF <small class="text-muted">(opcional)</small></label>
                    <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
                    <div class="form-text">Carregue o contrato/garantia em PDF (máx. 10 MB). Fica guardado no servidor.</div>
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
