<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

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
    try {
        mhs_pdo()->prepare("INSERT INTO localizacoes (edificio, piso, servico, sala, observacoes, created_at) VALUES (?,?,?,?,?,NOW())")
            ->execute([$edificio ?: null, $piso ?: null, $servico, $sala ?: null, $observacoes ?: null]);
        mhs_historico('localizacao', (int)mhs_pdo()->lastInsertId(), $servico . ($sala ? ' · ' . $sala : ''), 'criar');
        $_SESSION['success_message'] = 'Localização criada com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: nova.php'); exit;
    }
}

$page_title = 'Localizações - Nova';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
        <h1 class="mhs-page-title">Localizações - Nova</h1>
    </div>
</div>

<div class="card mhs-data-card">
    <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-location-dot me-1"></i>Informação da localização</div>
    <div class="card-body">
        <form method="POST" action="" style="max-width:640px">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Edifício</label>
                    <input type="text" name="edificio" class="form-control" placeholder="Ex.: Bloco Central" maxlength="100" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Piso</label>
                    <input type="text" name="piso" class="form-control" placeholder="Ex.: Piso 2" maxlength="50" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Serviço <span class="text-danger">*</span></label>
                    <input type="text" name="servico" class="form-control" placeholder="Ex.: Urgência" required maxlength="100" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sala</label>
                    <input type="text" name="sala" class="form-control" placeholder="Ex.: Sala 204" maxlength="100" />
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observações</label>
                    <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas sobre a localização"></textarea>
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
