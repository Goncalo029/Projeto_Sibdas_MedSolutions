<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$SERVICOS = ['Urgência','UCI','UCIP','Bloco Operatório','Cardiologia','Pediatria','Ortopedia',
             'Oncologia','Radiologia','Neurologia','Psiquiatria','Ginecologia','Obstetrícia',
             'Neonatologia','Oftalmologia','Otorrinolaringologia','Gastrenterologia','Pneumologia',
             'Nefrologia','Endocrinologia','Dermatologia','Fisioterapia','Reabilitação',
             'Farmácia','Laboratório','Imagiologia','Medicina Interna','Cirurgia Geral',
             'Cirurgia Vascular','Hematologia','Reumatologia','Nutrição','Consultas Externas',
             'Hospital de Dia','Anestesiologia','Cuidados Paliativos','Esterilização',
             'Bloco de Partos','Serviços Administrativos'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edificio    = trim($_POST['edificio'] ?? '');
    $piso        = trim($_POST['piso'] ?? '');
    $servico     = trim($_POST['servico'] ?? '');
    $sala        = trim($_POST['sala'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if (!$servico) {
        $_SESSION['error_message'] = 'O campo Serviço é obrigatório.';
        header('Location: nova.php'); exit;
    }
    if (mb_strlen($servico) < 3 || !preg_match('/[a-zA-ZÀ-úÀ-ÖØ-öø-ÿ]/u', $servico)) {
        $_SESSION['error_message'] = 'O serviço deve ter pelo menos 3 letras (ex: Urgência, Cardiologia).';
        header('Location: nova.php'); exit;
    }
    if ($sala && mb_strlen($sala) < 2) {
        $_SESSION['error_message'] = 'O nome da sala deve ter pelo menos 2 caracteres.';
        header('Location: nova.php'); exit;
    }

    try {
        mhs_pdo()->prepare("INSERT INTO localizacoes (edificio, piso, servico, sala, observacoes, criado_em) VALUES (?,?,?,?,?,NOW())")
            ->execute([$edificio ?: null, $piso ?: null, $servico, $sala ?: null, $observacoes ?: null]);
        mhs_historico('localizacao', (int)mhs_pdo()->lastInsertId(), $servico . ($sala ? ' · ' . $sala : ''), 'criar');
        $_SESSION['success_message'] = 'Localização criada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: nova.php'); exit;
    }
}

$_loc_existentes = mhs_pdo()->query("SELECT CONCAT(servico,'|',COALESCE(sala,'')) FROM localizacoes")->fetchAll(PDO::FETCH_COLUMN);
$page_title = 'Localizações - Nova';
include __DIR__ . '/../../includes/header.php';
?>
<script>window.MHS_LOC_EX = <?= json_encode(array_values($_loc_existentes)) ?>;</script>

<!-- datalist de serviços hospitalares -->
<datalist id="mhs-servicos">
  <?php foreach ($SERVICOS as $s): ?><option value="<?= htmlspecialchars($s) ?>"><?php endforeach; ?>
</datalist>
<datalist id="mhs-edificios">
  <option value="Bloco A"><option value="Bloco B"><option value="Bloco C">
  <option value="Bloco Central"><option value="Edifício Principal">
  <option value="Consultas Externas"><option value="Hospital de Dia"><option value="Urgência">
</datalist>
<datalist id="mhs-pisos">
  <option value="RCH (Rés-do-Chão)"><option value="Piso 1"><option value="Piso 2">
  <option value="Piso 3"><option value="Piso 4"><option value="Cave">
</datalist>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-location-dot fa-fw"></i></span>
    <h1 class="mhs-page-title">Nova Localização</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" class="mhs-validate-form" novalidate>
  <div class="card mhs-data-card">
    <div class="mhs-tab-body">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
          <span><i class="fa-solid fa-location-dot"></i> Informação da localização</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillLocalizacao(window.MHS_LOC_EX)"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)</button>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Edifício</label>
            <input type="text" name="edificio" id="edificio" class="form-control" list="mhs-edificios"
                   placeholder="Ex.: Bloco Central" maxlength="100"
                   value="<?= htmlspecialchars($_POST['edificio'] ?? '') ?>" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Piso</label>
            <input type="text" name="piso" id="piso" class="form-control" list="mhs-pisos"
                   placeholder="Ex.: Piso 2" maxlength="50"
                   value="<?= htmlspecialchars($_POST['piso'] ?? '') ?>" />
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Serviço <span class="text-danger">*</span></label>
            <input type="text" name="servico" id="servico" class="form-control" list="mhs-servicos"
                   placeholder="Selecione ou escreva um serviço" required maxlength="100"
                   value="<?= htmlspecialchars($_POST['servico'] ?? '') ?>" />
            <p class="mhs-field-error" id="err_servico">Indique um serviço válido (ex: Urgência, Cardiologia).</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Sala</label>
            <input type="text" name="sala" id="sala" class="form-control"
                   placeholder="Ex.: Sala 204" maxlength="100"
                   value="<?= htmlspecialchars($_POST['sala'] ?? '') ?>" />
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas sobre a localização"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Localização</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
