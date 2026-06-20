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
    // Importar ficheiro PDF do contrato (guardado na base de dados, opcional)
    $erro_upload = null;
    $pdf = mhs_ler_pdf_upload('ficheiro', $erro_upload);
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header('Location: novo.php'); exit;
    }

    try {
        $stmt = mhs_pdo()->prepare("INSERT INTO garantias_contratos (id_equipamento,data_inicio,data_fim,tem_contrato,tipo_contrato,entidade_responsavel,periodicidade,observacoes,nome_ficheiro,ficheiro_conteudo,ficheiro_mime,criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->bindValue(1, $id_equipamento, PDO::PARAM_INT);
        $stmt->bindValue(2, $data_inicio);
        $stmt->bindValue(3, $data_fim);
        $stmt->bindValue(4, $tem_contrato, PDO::PARAM_INT);
        $stmt->bindValue(5, $tipo_contrato ?: null);
        $stmt->bindValue(6, $entidade_responsavel ?: null);
        $stmt->bindValue(7, $periodicidade ?: null);
        $stmt->bindValue(8, $observacoes ?: null);
        $stmt->bindValue(9, $pdf['nome'] ?? null);
        $stmt->bindValue(10, $pdf['conteudo'] ?? null, $pdf ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(11, $pdf['mime'] ?? null);
        $stmt->execute();
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

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span>
    <h1 class="mhs-page-title">Nova Garantia / Contrato</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <?php $first_eq_id = !empty($equipamentos) ? (int)$equipamentos[0]->id : 0; ?>
        <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
          <span><i class="fa-solid fa-shield-halved"></i> Informação da garantia / contrato</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillGarantia(<?= $first_eq_id ?>)"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
        </div>
        <div class="row g-3 mt-1">
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
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas da garantia ou contrato"></textarea>
          </div>
        </div>
      </div>

      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-file-pdf"></i> Documento <small class="fw-normal text-muted">(opcional)</small></div>
        <div class="row g-3 mt-1">
          <div class="col-12">
            <label class="form-label fw-semibold">Importar PDF</label>
            <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
            <div class="form-text">Carregue o contrato/garantia em PDF (máx. 10 MB). Fica guardado na base de dados.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Garantia / Contrato</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
