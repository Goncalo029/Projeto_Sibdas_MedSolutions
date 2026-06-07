<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo                = $_POST['tipo'] ?? 'Preventiva';
    $id_equipamento      = (int)($_POST['id_equipamento'] ?? 0);
    $data_manutencao     = trim($_POST['data_manutencao'] ?? '');
    $proxima_manutencao  = trim($_POST['proxima_manutencao'] ?? '');
    $periodicidade       = trim($_POST['periodicidade'] ?? '');
    $estado              = trim($_POST['estado'] ?? 'Planeada');
    $tecnico_responsavel = trim($_POST['tecnico_responsavel'] ?? '');
    $descricao           = trim($_POST['descricao'] ?? '');
    $observacoes         = trim($_POST['observacoes'] ?? '');

    if (!$id_equipamento) {
        $_SESSION['error_message'] = 'Selecione um equipamento.';
        header("Location: editar.php?id=$id"); exit;
    }
    if (!in_array($tipo, ['Preventiva', 'Urgência'])) $tipo = 'Preventiva';

    try {
        mhs_pdo()->prepare("
            UPDATE manutencoes SET
                id_equipamento=?, tipo=?, data_manutencao=?, proxima_manutencao=?,
                periodicidade=?, estado=?, tecnico_responsavel=?, descricao=?, observacoes=?,
                updated_at=NOW()
            WHERE id=?
        ")->execute([
            $id_equipamento,
            $tipo,
            $data_manutencao ?: null,
            ($tipo === 'Preventiva' && $proxima_manutencao) ? $proxima_manutencao : null,
            ($tipo === 'Preventiva' && $periodicidade) ? $periodicidade : null,
            $estado,
            $tecnico_responsavel ?: null,
            $descricao ?: null,
            $observacoes ?: null,
            $id,
        ]);
        $_SESSION['success_message'] = 'Manutenção atualizada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$stmt = mhs_pdo()->prepare("SELECT * FROM manutencoes WHERE id=? AND deleted_at IS NULL");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$equipamentos = mhs_pdo()->query("
    SELECT id, codigo_inventario, designacao FROM equipamentos
    WHERE deleted_at IS NULL ORDER BY codigo_inventario
")->fetchAll();

$page_title = 'Manutenções - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Manutenção</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-header fw-bold bg-primary text-white">
    <i class="fa-solid fa-wrench me-2"></i>Editar Registo de Manutenção
  </div>
  <div class="card-body">
    <form method="POST" action="" style="max-width:760px">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label fw-semibold">Tipo de Manutenção <span class="text-danger">*</span></label>
          <div class="d-flex gap-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo" id="tipoPrev" value="Preventiva"
                <?= $row->tipo === 'Preventiva' ? 'checked' : '' ?> onchange="mhsTipoToggle()">
              <label class="form-check-label fw-semibold text-primary" for="tipoPrev">
                <i class="fa-solid fa-calendar-check me-1"></i>Preventiva
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo" id="tipoUrg" value="Urgência"
                <?= $row->tipo === 'Urgência' ? 'checked' : '' ?> onchange="mhsTipoToggle()">
              <label class="form-check-label fw-semibold text-danger" for="tipoUrg">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>Urgência
              </label>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <label class="form-label fw-semibold">Equipamento <span class="text-danger">*</span></label>
          <select name="id_equipamento" class="form-select" required>
            <option value="">— Selecione um equipamento —</option>
            <?php foreach ($equipamentos as $eq): ?>
              <option value="<?= $eq->id ?>" <?= $eq->id == $row->id_equipamento ? 'selected' : '' ?>>
                <?= esc($eq->codigo_inventario) ?> — <?= esc($eq->designacao) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Estado</label>
          <select name="estado" class="form-select">
            <?php foreach (['Planeada','Em curso','Concluída','Cancelada'] as $opt): ?>
              <option <?= $row->estado === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Data da Manutenção</label>
          <input type="date" name="data_manutencao" class="form-control" value="<?= esc($row->data_manutencao ?? '') ?>" />
        </div>

        <div class="col-md-4" id="campoProxima">
          <label class="form-label fw-semibold">Próxima Manutenção</label>
          <input type="date" name="proxima_manutencao" class="form-control" value="<?= esc($row->proxima_manutencao ?? '') ?>" />
        </div>

        <div class="col-md-4" id="campoPeriodicidade">
          <label class="form-label fw-semibold">Periodicidade</label>
          <select name="periodicidade" class="form-select">
            <option value="">— Selecione —</option>
            <?php foreach (['Mensal','Trimestral','Semestral','Anual'] as $opt): ?>
              <option <?= ($row->periodicidade ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Técnico Responsável</label>
          <input type="text" name="tecnico_responsavel" class="form-control" value="<?= esc($row->tecnico_responsavel ?? '') ?>" maxlength="190" />
        </div>

        <div class="col-12" id="campoDescricao">
          <label class="form-label fw-semibold text-danger">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Descrição da Avaria / Urgência
          </label>
          <textarea name="descricao" class="form-control border-danger" rows="3"><?= esc($row->descricao ?? '') ?></textarea>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="2"><?= esc($row->observacoes ?? '') ?></textarea>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
function mhsTipoToggle() {
    var urgente = document.getElementById('tipoUrg').checked;
    document.getElementById('campoProxima').style.display       = urgente ? 'none' : '';
    document.getElementById('campoPeriodicidade').style.display  = urgente ? 'none' : '';
    document.getElementById('campoDescricao').style.display     = urgente ? '' : 'none';
}
mhsTipoToggle();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
