<?php
/**
 * Novo documento
 * Permite associar um documento (PDF) a um equipamento.
 * O ficheiro é guardado diretamente na base de dados (coluna BLOB).
 */

require_once __DIR__ . '/../../includes/funcoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// ─── Processar o formulário quando é submetido ────────────────────────────────
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
    if ($data_validade && $data_validade < date('Y-m-d')) {
        $_SESSION['error_message'] = 'A data de validade não pode ser anterior a hoje.';
        header('Location: novo.php'); exit;
    }
    // Não permitir um documento do mesmo tipo já existente para o equipamento (exceto "Outro")
    if ($tipo_documento !== 'Outro') {
        $dup = mhs_pdo()->prepare("SELECT COUNT(*) FROM documentos WHERE id_equipamento = ? AND tipo_documento = ? AND eliminado_em IS NULL AND ficheiro_conteudo IS NOT NULL");
        $dup->execute([$id_equipamento, $tipo_documento]);
        if ((int)$dup->fetchColumn() > 0) {
            $_SESSION['error_message'] = 'Já existe um documento do tipo "' . $tipo_documento . '" para este equipamento.';
            header('Location: novo.php'); exit;
        }
    }

    // Ficheiro PDF (guardado na base de dados, não em pasta)
    $erro_upload = null;
    $pdf = mhs_ler_pdf_upload('ficheiro', $erro_upload);
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header('Location: novo.php'); exit;
    }

    try {
        $stmt = mhs_pdo()->prepare("INSERT INTO documentos (id_equipamento,tipo_documento,nome_documento,data_documento,data_validade,nome_ficheiro,ficheiro_conteudo,ficheiro_mime,observacoes,criado_em) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->bindValue(1, $id_equipamento, PDO::PARAM_INT);
        $stmt->bindValue(2, $tipo_documento);
        $stmt->bindValue(3, $nome_documento ?: null);
        $stmt->bindValue(4, $data_documento);
        $stmt->bindValue(5, $data_validade);
        $stmt->bindValue(6, $pdf['nome'] ?? null);
        $stmt->bindValue(7, $pdf['conteudo'] ?? null, $pdf ? PDO::PARAM_LOB : PDO::PARAM_NULL);
        $stmt->bindValue(8, $pdf['mime'] ?? null);
        $stmt->bindValue(9, $observacoes ?: null);
        $stmt->execute();
        mhs_historico('documento', (int)mhs_pdo()->lastInsertId(), ($nome_documento ?: $tipo_documento), 'criar');
        $_SESSION['success_message'] = 'Documento criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$equipamentos = mhs_pdo()->query("SELECT id, codigo_inventario, designacao FROM equipamentos WHERE eliminado_em IS NULL ORDER BY codigo_inventario")->fetchAll();
$tipos = ['Manual de utilizador','Manual de serviço','Certificado de calibração','Contrato de manutenção','Ficha técnica','Fatura / Guia de aquisição','Declaração de conformidade','Relatório técnico','Outro'];
$first_eq_id = !empty($equipamentos) ? $equipamentos[0]->id : 0;

$page_title = 'Documentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>
<script>window.MHS_DOC_EQ = <?= (int)$first_eq_id ?>;</script>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
    <h1 class="mhs-page-title">Novo Documento</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
          <span><i class="fa-solid fa-file-lines"></i> Informação do documento</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillDocumento(window.MHS_DOC_EQ)"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-12">
            <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
            <select name="id_equipamento" class="form-select" required>
              <option value="">Selecione um equipamento...</option>
              <?php foreach ($equipamentos as $eq): ?>
              <option value="<?= $eq->id ?>"><?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
            <select name="tipo_documento" class="form-select" required>
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
            <input type="text" name="data_validade" class="form-control mhs-datepicker" data-mindate="today" placeholder="AAAA-MM-DD" />
            <div class="form-text">Para documentos com prazo (garantia, certificado de calibração…). Não pode ser anterior a hoje.</div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas sobre o documento"></textarea>
          </div>
        </div>
      </div>

      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-file-pdf"></i> Ficheiro</div>
        <div class="row g-3 mt-1">
          <div class="col-12">
            <label class="form-label fw-semibold">Ficheiro PDF</label>
            <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
            <div class="form-text">Carregue o documento em PDF (máx. 10 MB). Fica guardado na base de dados.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Documento</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
