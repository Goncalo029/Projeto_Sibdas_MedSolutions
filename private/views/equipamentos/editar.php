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

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
        <h1 class="mhs-page-title">Equipamentos - Editar</h1>
    </div>
    <div class="mhs-page-actions">
        <a href="lista.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="id" value="<?= $row->id ?>">

    <div class="card mhs-data-card mb-3">
        <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-barcode me-1"></i>Identificação</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Código Inventário <span class="text-danger">*</span></label>
                <input type="text" name="codigo_inventario" class="form-control" value="<?= htmlspecialchars($row->codigo_inventario) ?>" required maxlength="50" />
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Designação <span class="text-danger">*</span></label>
                <input type="text" name="designacao" class="form-control" value="<?= htmlspecialchars($row->designacao) ?>" required maxlength="200" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Categoria</label>
                <select name="id_categoria" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= $row->id_categoria == $cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Marca</label>
                <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($row->marca ?? '') ?>" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Modelo</label>
                <input type="text" name="modelo" class="form-control" value="<?= htmlspecialchars($row->modelo ?? '') ?>" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Número de Série</label>
                <input type="text" name="numero_serie" class="form-control" value="<?= htmlspecialchars($row->numero_serie ?? '') ?>" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fabricante</label>
                <input type="text" name="fabricante" class="form-control" value="<?= htmlspecialchars($row->fabricante ?? '') ?>" maxlength="150" />
            </div>
        </div>
    </div>

    <div class="card mhs-data-card mb-3">
        <div class="card-header fw-bold bg-secondary text-white"><i class="fa-solid fa-receipt me-1"></i>Aquisição</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Data Aquisição</label>
                <input type="text" name="data_aquisicao" class="form-control mhs-datepicker" value="<?= htmlspecialchars($row->data_aquisicao ?? '') ?>" placeholder="AAAA-MM-DD" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Ano Fabrico</label>
                <input type="number" name="ano_fabrico" class="form-control" value="<?= $row->ano_fabrico ?? '' ?>" min="1900" max="2099" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Custo Aquisição (€)</label>
                <input type="text" name="custo_aquisicao" class="form-control" value="<?= htmlspecialchars($row->custo_aquisicao ?? '') ?>" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tipo de Entrada</label>
                <select name="tipo_entrada" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($tipos_entrada as $t): ?>
                    <option <?= ($row->tipo_entrada ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="card mhs-data-card mb-3">
        <div class="card-header fw-bold bg-dark text-white"><i class="fa-solid fa-location-dot me-1"></i>Localização e Estado</div>
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Localização</label>
                <select name="id_localizacao" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($localizacoes as $loc): ?>
                    <option value="<?= $loc->id ?>" <?= $row->id_localizacao == $loc->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc->servico . ($loc->sala ? ' / ' . $loc->sala : '')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($estados as $e): ?>
                    <option <?= ($row->estado ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Criticidade</label>
                <select name="criticidade" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($criticidades as $c): ?>
                    <option <?= ($row->criticidade ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="2"><?= htmlspecialchars($row->observacoes ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
