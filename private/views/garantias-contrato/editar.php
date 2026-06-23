<?php
// pagina para editar uma garantia/contrato que ja existe (e trocar os PDF se quiser)
require_once __DIR__ . '/../../includes/funcoes.php';

// so entra com sessao iniciada
redirect_if_not_logged();

// vai buscar o id ao link, se nao tiver volta para a lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// quando o formulario e enviado, valida e grava
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
    // Importar/substituir os ficheiros PDF na base de dados (opcionais)
    $erro_upload = null;
    $pdf_contrato = mhs_ler_pdf_upload('ficheiro', $erro_upload);          // documento do contrato
    if ($erro_upload) { $_SESSION['error_message'] = $erro_upload; header("Location: editar.php?id=$id"); exit; }
    $pdf_garantia = mhs_ler_pdf_upload('ficheiro_garantia', $erro_upload); // documento da garantia
    if ($erro_upload) { $_SESSION['error_message'] = $erro_upload; header("Location: editar.php?id=$id"); exit; }

    try {
        $pdo = mhs_pdo();
        mhs_ensure_garantia_doc_cols($pdo);

        // Campos de texto (sempre)
        $pdo->prepare("UPDATE garantias_contratos SET id_equipamento=?,data_inicio=?,data_fim=?,tem_contrato=?,tipo_contrato=?,entidade_responsavel=?,periodicidade=?,observacoes=?,atualizado_em=NOW() WHERE id=?")
            ->execute([$id_equipamento, $data_inicio, $data_fim, $tem_contrato, $tipo_contrato ?: null, $entidade_responsavel ?: null, $periodicidade ?: null, $observacoes ?: null, $id]);

        // Documento do contrato (só se enviado)
        if ($pdf_contrato) {
            $s = $pdo->prepare("UPDATE garantias_contratos SET nome_ficheiro=?,ficheiro_conteudo=?,ficheiro_mime=?,atualizado_em=NOW() WHERE id=?");
            $s->bindValue(1, $pdf_contrato['nome']);
            $s->bindValue(2, $pdf_contrato['conteudo'], PDO::PARAM_LOB);
            $s->bindValue(3, $pdf_contrato['mime']);
            $s->bindValue(4, $id, PDO::PARAM_INT);
            $s->execute();
        }
        // Documento da garantia (só se enviado)
        if ($pdf_garantia) {
            $s = $pdo->prepare("UPDATE garantias_contratos SET garantia_nome_ficheiro=?,garantia_ficheiro_conteudo=?,garantia_ficheiro_mime=?,atualizado_em=NOW() WHERE id=?");
            $s->bindValue(1, $pdf_garantia['nome']);
            $s->bindValue(2, $pdf_garantia['conteudo'], PDO::PARAM_LOB);
            $s->bindValue(3, $pdf_garantia['mime']);
            $s->bindValue(4, $id, PDO::PARAM_INT);
            $s->execute();
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
mhs_ensure_garantia_doc_cols($pdo);
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
        <div class="mhs-form-section-title"><i class="fa-solid fa-file-pdf"></i> Documentos</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="fa-solid fa-shield-halved text-muted me-1"></i>Documento da garantia</label>
            <?php if (($row->garantia_ficheiro_conteudo ?? null) !== null): ?>
            <div class="mb-2">
              <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row->garantia_nome_ficheiro ?: 'garantia.pdf') ?></span>
              <a href="lista.php?ficheiro=<?= (int)$row->id ?>&doc=garantia" class="small ms-2">Descarregar atual</a>
            </div>
            <?php endif; ?>
            <input type="file" name="ficheiro_garantia" class="form-control" accept="application/pdf,.pdf" />
            <div class="form-text">Deixe vazio para manter (máx. 10 MB).</div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="fa-solid fa-file-contract text-muted me-1"></i>Documento do contrato</label>
            <?php if ($row->ficheiro_conteudo !== null): ?>
            <div class="mb-2">
              <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row->nome_ficheiro ?: 'contrato.pdf') ?></span>
              <a href="lista.php?ficheiro=<?= (int)$row->id ?>&doc=contrato" class="small ms-2">Descarregar atual</a>
            </div>
            <?php endif; ?>
            <input type="file" name="ficheiro" class="form-control" accept="application/pdf,.pdf" />
            <div class="form-text">Deixe vazio para manter (máx. 10 MB).</div>
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
