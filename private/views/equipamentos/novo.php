<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

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
        header('Location: novo.php'); exit;
    }
    try {
        mhs_pdo()->prepare("INSERT INTO equipamentos (codigo_inventario,designacao,id_categoria,marca,modelo,numero_serie,fabricante,data_aquisicao,ano_fabrico,custo_aquisicao,tipo_entrada,id_localizacao,estado,criticidade,observacoes,created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")
            ->execute([$codigo_inventario,$designacao,$id_categoria,$marca,$modelo,$numero_serie,$fabricante,$data_aquisicao,$ano_fabrico,$custo_aquisicao,$tipo_entrada,$id_localizacao,$estado,$criticidade,$observacoes]);
        $_SESSION['success_message'] = 'Equipamento criado com sucesso.';
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$pdo = mhs_pdo();
$categorias   = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
$localizacoes = $pdo->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll();

$estados      = ['Ativo','Em manutenção','Inativo','Em calibração','Em quarentena','Abatido'];
$criticidades = ['Baixa','Média','Alta','Suporte de vida'];
$tipos_entrada = ['Compra','Doação','Aluguer','Empréstimo'];

$page_title = 'Equipamentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
        <h1 class="mhs-page-title">Equipamentos - Novo</h1>
    </div>
    <div class="mhs-page-actions">
        <a href="lista.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Voltar</a>
    </div>
</div>

<form method="POST" action="">
    <div class="card mhs-data-card mb-3">
        <div class="card-header fw-bold bg-primary text-white"><i class="fa-solid fa-barcode me-1"></i>Identificação</div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Código Inventário <span class="text-danger">*</span></label>
                <input type="text" name="codigo_inventario" class="form-control" placeholder="Ex.: EQ-001" required maxlength="50" />
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Designação <span class="text-danger">*</span></label>
                <input type="text" name="designacao" class="form-control" placeholder="Nome do equipamento" required maxlength="200" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Categoria</label>
                <select name="id_categoria" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nome) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Marca</label>
                <input type="text" name="marca" class="form-control" placeholder="Marca" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Modelo</label>
                <input type="text" name="modelo" class="form-control" placeholder="Modelo" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Número de Série</label>
                <input type="text" name="numero_serie" class="form-control" placeholder="Número de série" maxlength="100" />
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fabricante</label>
                <input type="text" name="fabricante" class="form-control" placeholder="Fabricante" maxlength="150" />
            </div>
        </div>
    </div>

    <div class="card mhs-data-card mb-3">
        <div class="card-header fw-bold bg-secondary text-white"><i class="fa-solid fa-receipt me-1"></i>Aquisição</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Data Aquisição</label>
                <input type="text" name="data_aquisicao" class="form-control mhs-datepicker" placeholder="AAAA-MM-DD" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Ano Fabrico</label>
                <input type="number" name="ano_fabrico" class="form-control" min="1900" max="2099" placeholder="<?= date('Y') ?>" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Custo Aquisição (€)</label>
                <input type="text" name="custo_aquisicao" class="form-control" placeholder="0.00" />
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tipo de Entrada</label>
                <select name="tipo_entrada" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($tipos_entrada as $t): ?>
                    <option><?= $t ?></option>
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
                    <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->servico . ($loc->sala ? ' / ' . $loc->sala : '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($estados as $e): ?>
                    <option><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Criticidade</label>
                <select name="criticidade" class="form-select">
                    <option value="">-- Selecione --</option>
                    <?php foreach ($criticidades as $c): ?>
                    <option><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Observações</label>
                <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas sobre o equipamento"></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
        <a href="lista.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
