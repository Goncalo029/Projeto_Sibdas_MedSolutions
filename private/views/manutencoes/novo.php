<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// Pré-selecionar equipamento se vir do detalhe do equipamento
$pre_equipamento = (int)($_GET['id_equipamento'] ?? 0);
$pre_tipo = $_GET['tipo'] ?? 'Preventiva';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento      = (int)($_POST['id_equipamento'] ?? 0);
    $tipo                = $_POST['tipo'] ?? 'Preventiva';
    $data_manutencao     = trim($_POST['data_manutencao'] ?? '');
    $proxima_manutencao  = trim($_POST['proxima_manutencao'] ?? '');
    $periodicidade       = trim($_POST['periodicidade'] ?? '');
    $estado              = trim($_POST['estado'] ?? 'Planeada');
    $tecnico_responsavel = trim($_POST['tecnico_responsavel'] ?? '');
    $descricao           = trim($_POST['descricao'] ?? '');
    $observacoes         = trim($_POST['observacoes'] ?? '');

    if (!$id_equipamento) {
        $_SESSION['error_message'] = 'Selecione um equipamento.';
        header('Location: novo.php'); exit;
    }
    if (!in_array($tipo, ['Preventiva', 'Urgência'])) {
        $tipo = 'Preventiva';
    }

    try {
        mhs_pdo()->prepare("
            INSERT INTO manutencoes
                (id_equipamento, tipo, data_manutencao, proxima_manutencao, periodicidade,
                 estado, tecnico_responsavel, descricao, observacoes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $_SESSION['user_email'] ?? null,
        ]);
        $_SESSION['success_message'] = 'Manutenção registada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

// Carregar equipamentos para o select
$equipamentos = mhs_pdo()->query("
    SELECT id, codigo_inventario, designacao FROM equipamentos
    WHERE deleted_at IS NULL ORDER BY codigo_inventario
")->fetchAll();

$page_title = 'Manutenções - Nova';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
    <h1 class="mhs-page-title">Nova Manutenção</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-header fw-bold bg-primary text-white">
    <i class="fa-solid fa-wrench me-2"></i>Registo de Manutenção
  </div>
  <div class="card-body">
    <form method="POST" action="" style="max-width:760px" id="manForm">
      <div class="row g-3">

        <!-- Tipo — destaque visual -->
        <div class="col-12">
          <label class="form-label fw-semibold">Tipo de Manutenção <span class="text-danger">*</span></label>
          <div class="d-flex gap-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo" id="tipoPrev" value="Preventiva"
                <?= $pre_tipo !== 'Urgência' ? 'checked' : '' ?> onchange="mhsTipoToggle()">
              <label class="form-check-label fw-semibold text-primary" for="tipoPrev">
                <i class="fa-solid fa-calendar-check me-1"></i>Preventiva
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="tipo" id="tipoUrg" value="Urgência"
                <?= $pre_tipo === 'Urgência' ? 'checked' : '' ?> onchange="mhsTipoToggle()">
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
              <option value="<?= $eq->id ?>" <?= $eq->id === $pre_equipamento ? 'selected' : '' ?>>
                <?= esc($eq->codigo_inventario) ?> — <?= esc($eq->designacao) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Estado</label>
          <select name="estado" class="form-select">
            <?php foreach (['Planeada','Em curso','Concluída','Cancelada'] as $opt): ?>
              <option><?= $opt ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Data da Manutenção</label>
          <input type="date" name="data_manutencao" class="form-control" value="<?= date('Y-m-d') ?>" />
        </div>

        <!-- Campos só para Preventiva -->
        <div class="col-md-4" id="campoProxima">
          <label class="form-label fw-semibold">Próxima Manutenção</label>
          <input type="date" name="proxima_manutencao" class="form-control" />
        </div>

        <div class="col-md-4" id="campoPeriodicidade">
          <label class="form-label fw-semibold">Periodicidade</label>
          <select name="periodicidade" class="form-select">
            <option value="">— Selecione —</option>
            <option>Mensal</option>
            <option>Trimestral</option>
            <option>Semestral</option>
            <option>Anual</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Técnico Responsável</label>
          <input type="text" name="tecnico_responsavel" class="form-control" placeholder="Nome do técnico" maxlength="190" />
        </div>

        <!-- Campo só para Urgência -->
        <div class="col-12" id="campoDescricao" style="display:none">
          <label class="form-label fw-semibold text-danger">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>Descrição da Avaria / Urgência
          </label>
          <textarea name="descricao" class="form-control border-danger" rows="3" placeholder="Descreva o problema ou motivo da chamada de urgência..."></textarea>
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Observações</label>
          <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas adicionais..."></textarea>
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
    document.getElementById('campoProxima').style.display      = urgente ? 'none' : '';
    document.getElementById('campoPeriodicidade').style.display = urgente ? 'none' : '';
    document.getElementById('campoDescricao').style.display    = urgente ? '' : 'none';
}
mhsTipoToggle();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
