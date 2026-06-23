<?php
// pagina para editar um documento que ja existe (e trocar o PDF se quiser)
require_once __DIR__ . '/../../includes/funcoes.php';

// so entra com sessao iniciada
redirect_if_not_logged();

// vai buscar o id do documento ao link, se nao tiver volta para a lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// quando o formulario e enviado, trata dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // le os campos do formulario
    $id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $nome_documento = trim($_POST['nome_documento'] ?? '');
    $data_documento = trim($_POST['data_documento'] ?? '') ?: null;
    $data_validade  = trim($_POST['data_validade'] ?? '') ?: null;
    $observacoes    = trim($_POST['observacoes'] ?? '');
    // equipamento e tipo sao obrigatorios
    if (!$id_equipamento || !$tipo_documento) {
        $_SESSION['error_message'] = 'Equipamento e Tipo de documento são obrigatórios.';
        header("Location: editar.php?id=$id"); exit;
    }
    // validade nao pode ser no passado, mas deixa manter a que ja estava (documento expirado)
    if ($data_validade && $data_validade < date('Y-m-d')) {
        $old = mhs_pdo()->prepare("SELECT data_validade FROM documentos WHERE id=?");
        $old->execute([$id]);
        $old_val = $old->fetchColumn() ?: null;
        if ($data_validade !== $old_val) {
            $_SESSION['error_message'] = 'A data de validade não pode ser anterior a hoje.';
            header("Location: editar.php?id=$id"); exit;
        }
    }
    // ficheiro novo e opcional: se vier, substitui o antigo na base de dados
    $erro_upload = null;
    $pdf = mhs_ler_pdf_upload('ficheiro', $erro_upload);
    if ($erro_upload) {
        $_SESSION['error_message'] = $erro_upload;
        header("Location: editar.php?id=$id"); exit;
    }

    try {
        // se ha PDF novo, atualiza tambem o ficheiro
        if ($pdf) {
            $stmt = mhs_pdo()->prepare("UPDATE documentos SET id_equipamento=?,tipo_documento=?,nome_documento=?,data_documento=?,data_validade=?,nome_ficheiro=?,ficheiro_conteudo=?,ficheiro_mime=?,observacoes=?,atualizado_em=NOW() WHERE id=?");
            $stmt->bindValue(1, $id_equipamento, PDO::PARAM_INT);
            $stmt->bindValue(2, $tipo_documento);
            $stmt->bindValue(3, $nome_documento ?: null);
            $stmt->bindValue(4, $data_documento);
            $stmt->bindValue(5, $data_validade);
            $stmt->bindValue(6, $pdf['nome']);
            $stmt->bindValue(7, $pdf['conteudo'], PDO::PARAM_LOB);
            $stmt->bindValue(8, $pdf['mime']);
            $stmt->bindValue(9, $observacoes ?: null);
            $stmt->bindValue(10, $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // sem ficheiro novo: so atualiza os dados e mantem o PDF antigo
            mhs_pdo()->prepare("UPDATE documentos SET id_equipamento=?,tipo_documento=?,nome_documento=?,data_documento=?,data_validade=?,observacoes=?,atualizado_em=NOW() WHERE id=?")
                ->execute([$id_equipamento, $tipo_documento, $nome_documento ?: null, $data_documento, $data_validade, $observacoes ?: null, $id]);
        }
        // guarda no historico que o documento foi editado
        mhs_historico('documento', $id, ($nome_documento ?: $tipo_documento), 'editar');
        $_SESSION['success_message'] = 'Documento atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        // se a gravacao falhar mostra o erro
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

// vai buscar o documento atual para preencher o formulario
$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM documentos WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
// se nao existir volta para a lista
if (!$row) { header('Location: lista.php'); exit; }

// equipamentos e tipos para os menus do formulario
$equipamentos = $pdo->query("SELECT id, codigo_inventario, designacao FROM equipamentos ORDER BY codigo_inventario")->fetchAll();
$tipos = ['Manual de utilizador','Manual de serviço','Certificado de calibração','Contrato de manutenção','Ficha técnica','Fatura / Guia de aquisição','Declaração de conformidade','Relatório técnico','Outro'];

$page_title = 'Documentos - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Documento</h1>
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
        <div class="mhs-form-section-title"><i class="fa-solid fa-file-lines"></i> Informação do documento</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
            <select name="id_equipamento" class="form-select" required>
              <option value="">Selecione um equipamento...</option>
              <?php foreach ($equipamentos as $eq): ?>
              <option value="<?= $eq->id ?>" <?= $row->id_equipamento == $eq->id ? 'selected' : '' ?>>
                <?= htmlspecialchars($eq->codigo_inventario . ' - ' . $eq->designacao) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Tipo de Documento <span class="text-danger">*</span></label>
            <select name="tipo_documento" class="form-select" required>
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
            <input type="text" name="data_validade" class="form-control mhs-datepicker" data-mindate="today" value="<?= htmlspecialchars($row->data_validade ?? '') ?>" placeholder="AAAA-MM-DD" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <div class="mhs-form-section mt-3">
        <div class="mhs-form-section-title"><i class="fa-solid fa-file-pdf"></i> Ficheiro</div>
        <div class="row g-3">
          <div class="col-12">
            <?php if (!empty($row->nome_ficheiro)): ?>
            <div class="mb-2">
              <span class="badge bg-light text-dark border"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row->nome_ficheiro) ?></span>
              <a href="download.php?id=<?= (int)$row->id ?>" class="small ms-2">Descarregar atual</a>
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
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Documento</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
