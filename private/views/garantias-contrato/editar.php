<?php
/**
 * Editar garantia / contrato
 * Permite alterar os dados de uma garantia ou contrato existente.
 * Também é possível substituir o ficheiro PDF anexado.
 */

require_once __DIR__ . '/../../includes/funcoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// Obter o ID da garantia — se não existir, voltar à lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// ─── Processar o formulário quando é submetido ────────────────────────────────
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
    // Importar/substituir ficheiro PDF na base de dados (opcional)
    $erro_upload = null;
    $pdf = mhs_ler_pdf_upload('ficheiro', $erro_upload);
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header("Location: editar.php?id=$id"); exit;
    }

    try {
        if ($pdf) {
            $stmt = mhs_pdo()->prepare("UPDATE garantias_contratos SET id_equipamento=?,data_inicio=?,data_fim=?,tem_contrato=?,tipo_contrato=?,entidade_responsavel=?,periodicidade=?,observacoes=?,nome_ficheiro=?,ficheiro_conteudo=?,ficheiro_mime=?,atualizado_em=NOW() WHERE id=?");
            $stmt->bindValue(1, $id_equipamento, PDO::PARAM_INT);
            $stmt->bindValue(2, $data_inicio);
            $stmt->bindValue(3, $data_fim);
            $stmt->bindValue(4, $tem_contrato, PDO::PARAM_INT);
            $stmt->bindValue(5, $tipo_contrato ?: null);
            $stmt->bindValue(6, $entidade_responsavel ?: null);
            $stmt->bindValue(7, $periodicidade ?: null);
            $stmt->bindValue(8, $observacoes ?: null);
            $stmt->bindValue(9, $pdf['nome']);
            $stmt->bindValue(10, $pdf['conteudo'], PDO::PARAM_LOB);
            $stmt->bindValue(11, $pdf['mime']);
            $stmt->bindValue(12, $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            mhs_pdo()->prepare("UPDATE garantias_contratos SET id_equipamento=?,data_inicio=?,data_fim=?,tem_contrato=?,tipo_contrato=?,entidade_responsavel=?,periodicidade=?,observacoes=?,atualizado_em=NOW() WHERE id=?")
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

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Garantia / Contrato</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $row->id ?>">
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-shield-halved"></i> Informação da garantia / contrato</div>
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
            <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <div class="mhs-form-section mt-3">
        <div class="mhs-form-section-title"><i class="fa-solid fa-file-pdf"></i> Documento</div>
        <div class="row g-3">
          <div class="col-12">
            <?php if (!empty($row->nome_ficheiro)): ?>
            <div class="mb-2">
              <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row->nome_ficheiro) ?></span>
              <a href="lista.php?ficheiro=<?= (int)$row->id ?>" class="small ms-2">Descarregar atual</a>
            </div>
            <?php endif; ?>
            <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
            <div class="form-text">Deixe vazio para manter. Carregar um novo substitui o anterior (máx. 10 MB). Fica na base de dados.</div>
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
