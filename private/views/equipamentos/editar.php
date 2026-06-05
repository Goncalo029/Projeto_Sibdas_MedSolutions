<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_inventario = trim($_POST['codigo_inventario'] ?? '');
    $designacao        = trim($_POST['designacao'] ?? '');
    $id_categoria      = (int)($_POST['id_categoria'] ?? 0) ?: null;
    $marca             = trim($_POST['marca'] ?? '') ?: null;
    $modelo            = trim($_POST['modelo'] ?? '') ?: null;
    $numero_serie      = trim($_POST['numero_serie'] ?? '') ?: null;
    $fabricante        = trim($_POST['fabricante'] ?? '') ?: null;
    $data_aquisicao    = trim($_POST['data_aquisicao'] ?? '') ?: null;
    $ano_fabrico       = (int)($_POST['ano_fabrico'] ?? 0) ?: null;
    $custo_aquisicao   = trim($_POST['custo_aquisicao'] ?? '') ?: null;
    $tipo_entrada      = trim($_POST['tipo_entrada'] ?? '') ?: null;
    $id_localizacao    = (int)($_POST['id_localizacao'] ?? 0) ?: null;
    $estado            = trim($_POST['estado'] ?? '') ?: null;
    $criticidade       = trim($_POST['criticidade'] ?? '') ?: null;
    $observacoes       = trim($_POST['observacoes'] ?? '') ?: null;
    if (!$codigo_inventario || !$designacao) {
        $_SESSION['error_message'] = 'Código de Inventário e Designação são obrigatórios.';
        header("Location: editar.php?id=$id"); exit;
    }
    try {
        mhs_pdo()->prepare("UPDATE equipamentos SET codigo_inventario=?,designacao=?,id_categoria=?,marca=?,modelo=?,numero_serie=?,fabricante=?,data_aquisicao=?,ano_fabrico=?,custo_aquisicao=?,tipo_entrada=?,id_localizacao=?,estado=?,criticidade=?,observacoes=?,updated_at=NOW() WHERE id=?")
            ->execute([$codigo_inventario,$designacao,$id_categoria,$marca,$modelo,$numero_serie,$fabricante,$data_aquisicao,$ano_fabrico,$custo_aquisicao,$tipo_entrada,$id_localizacao,$estado,$criticidade,$observacoes,$id]);
        $_SESSION['success_message'] = 'Equipamento atualizado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM equipamentos WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$categorias   = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
$localizacoes = $pdo->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll();
$estados      = ['Ativo','Em manutenção','Inativo','Em calibração','Em quarentena','Abatido'];
$criticidades = ['Baixa','Média','Alta','Suporte de vida'];
$tipos_entrada = ['Compra','Doação','Aluguer','Empréstimo'];

$page_title = 'Equipamentos - Editar';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Equipamento</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="detalhes.php?id=<?= $id ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<!-- Resumo -->
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Código</span>
      <span class="mhs-code"><?= esc($row->codigo_inventario) ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Designação</span>
      <span class="mhs-detail-summary-val"><?= esc($row->designacao) ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Estado actual</span>
      <span class="mhs-detail-summary-val mhs-detail-summary-val--ok"><?= esc($row->estado ?? '—') ?></span>
    </div>
  </div>
</div>

<form method="POST" action="">
<input type="hidden" name="id" value="<?= $row->id ?>">

<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button type="button" class="mhs-detail-tab active" data-tab="identificacao">
      <i class="fa-solid fa-barcode"></i> Identificação
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="aquisicao">
      <i class="fa-solid fa-receipt"></i> Aquisição
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="localizacao">
      <i class="fa-solid fa-location-dot"></i> Localização e Estado
    </button>
  </div>

  <!-- Identificação -->
  <div class="mhs-tab-pane active" id="tab-identificacao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-barcode"></i> Dados de identificação</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Código de inventário <span class="text-danger">*</span></label>
            <input type="text" name="codigo_inventario" class="form-control" value="<?= esc($row->codigo_inventario) ?>" required maxlength="50" />
          </div>
          <div class="col-md-8">
            <label class="form-label">Designação <span class="text-danger">*</span></label>
            <input type="text" name="designacao" class="form-control" value="<?= esc($row->designacao) ?>" required maxlength="200" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoria</label>
            <select name="id_categoria" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat->id ?>" <?= $row->id_categoria == $cat->id ? 'selected' : '' ?>><?= esc($cat->nome) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Marca</label>
            <input type="text" name="marca" class="form-control" value="<?= esc($row->marca ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Modelo</label>
            <input type="text" name="modelo" class="form-control" value="<?= esc($row->modelo ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Número de série</label>
            <input type="text" name="numero_serie" class="form-control" value="<?= esc($row->numero_serie ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Fabricante</label>
            <input type="text" name="fabricante" class="form-control" value="<?= esc($row->fabricante ?? '') ?>" maxlength="150" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Aquisição -->
  <div class="mhs-tab-pane" id="tab-aquisicao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-receipt"></i> Dados de aquisição</div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Data de aquisição</label>
            <input type="text" name="data_aquisicao" class="form-control mhs-datepicker" value="<?= esc($row->data_aquisicao ?? '') ?>" placeholder="AAAA-MM-DD" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Ano de fabrico</label>
            <input type="number" name="ano_fabrico" class="form-control" value="<?= $row->ano_fabrico ?? '' ?>" min="1900" max="2099" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Custo de aquisição (€)</label>
            <input type="text" name="custo_aquisicao" class="form-control" value="<?= esc($row->custo_aquisicao ?? '') ?>" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo de entrada</label>
            <select name="tipo_entrada" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($tipos_entrada as $t): ?>
              <option <?= ($row->tipo_entrada ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Localização e Estado -->
  <div class="mhs-tab-pane" id="tab-localizacao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-location-dot"></i> Localização e estado operacional</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Localização</label>
            <select name="id_localizacao" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($localizacoes as $loc): ?>
              <option value="<?= $loc->id ?>" <?= $row->id_localizacao == $loc->id ? 'selected' : '' ?>>
                <?= esc($loc->servico . ($loc->sala ? ' / '.$loc->sala : '')) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($estados as $e): ?>
              <option <?= ($row->estado ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Criticidade</label>
            <select name="criticidade" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($criticidades as $c): ?>
              <option <?= ($row->criticidade ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= esc($row->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- card -->

<div class="mhs-form-actions">
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar alterações</button>
  <a href="lista.php" class="btn btn-outline-secondary">Cancelar</a>
</div>

</form>

<script>
document.querySelectorAll('.mhs-detail-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.mhs-detail-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.mhs-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
