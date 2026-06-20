<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$pdo = mhs_pdo();

// Listas para os selects
$categorias   = $pdo->query("SELECT id, nome FROM categorias WHERE ativo=1 AND eliminado_em IS NULL ORDER BY nome")->fetchAll();
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedores WHERE ativo=1 AND eliminado_em IS NULL ORDER BY nome")->fetchAll();
$servicos     = $pdo->query("SELECT DISTINCT servico FROM localizacoes WHERE eliminado_em IS NULL ORDER BY servico")->fetchAll();

// Recolha dos filtros (GET)
$f_codigo    = trim($_GET['codigo']    ?? '');
$f_designa   = trim($_GET['designa']  ?? '');
$f_marca     = trim($_GET['marca']    ?? '');
$f_modelo    = trim($_GET['modelo']   ?? '');
$f_serie     = trim($_GET['serie']    ?? '');
$f_servico   = trim($_GET['servico']  ?? '');
$f_estado    = trim($_GET['estado']   ?? '');
$f_fornec    = (int)($_GET['fornec']  ?? 0);
$f_cat       = (int)($_GET['cat']     ?? 0);
$f_critic    = trim($_GET['critic']   ?? '');

$pesquisou = !empty(array_filter([$f_codigo, $f_designa, $f_marca, $f_modelo, $f_serie, $f_servico, $f_estado, $f_fornec, $f_cat, $f_critic]));

$resultados = [];

if ($pesquisou) {
    $sql = "SELECT e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo,
                   e.numero_serie, e.estado, e.criticidade,
                   c.nome AS categoria,
                   l.servico, l.sala
            FROM equipamentos e
            LEFT JOIN categorias c ON e.id_categoria = c.id
            LEFT JOIN localizacoes l ON e.id_localizacao = l.id
            WHERE e.ativo = 1 AND e.eliminado_em IS NULL";

    $params = [];

    if ($f_codigo)  { $sql .= " AND e.codigo_inventario LIKE ?"; $params[] = "%$f_codigo%"; }
    if ($f_designa) { $sql .= " AND e.designacao LIKE ?";        $params[] = "%$f_designa%"; }
    if ($f_marca)   { $sql .= " AND e.marca LIKE ?";             $params[] = "%$f_marca%"; }
    if ($f_modelo)  { $sql .= " AND e.modelo LIKE ?";            $params[] = "%$f_modelo%"; }
    if ($f_serie)   { $sql .= " AND e.numero_serie LIKE ?";      $params[] = "%$f_serie%"; }
    if ($f_estado)  { $sql .= " AND e.estado = ?";               $params[] = $f_estado; }
    if ($f_critic)  { $sql .= " AND e.criticidade = ?";          $params[] = $f_critic; }
    if ($f_servico) { $sql .= " AND l.servico = ?";              $params[] = $f_servico; }
    if ($f_cat)     { $sql .= " AND e.id_categoria = ?";         $params[] = $f_cat; }
    if ($f_fornec)  {
        $sql .= " AND EXISTS (SELECT 1 FROM equipamentos_fornecedores ef WHERE ef.id_equipamento = e.id AND ef.id_fornecedor = ?)";
        $params[] = $f_fornec;
    }

    $sql .= " ORDER BY e.codigo_inventario ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();
}

$page_title = 'Pesquisa Avançada';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
    <div>
        <span class="mhs-page-kicker"><i class="fa-solid fa-magnifying-glass fa-fw"></i></span>
        <h1 class="mhs-page-title">Pesquisa Avançada</h1>
    </div>
</div>

<div class="card mhs-data-card mb-4">
    <div class="card-body">
        <form method="get" action="pesquisa.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Código de Inventário</label>
                    <input type="text" name="codigo" class="form-control" placeholder="Ex: EQ-001" value="<?= esc($f_codigo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Designação</label>
                    <input type="text" name="designa" class="form-control" placeholder="Nome do equipamento" value="<?= esc($f_designa) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Marca</label>
                    <input type="text" name="marca" class="form-control" placeholder="Ex: Philips" value="<?= esc($f_marca) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Modelo</label>
                    <input type="text" name="modelo" class="form-control" placeholder="Ex: IntelliVue MP5" value="<?= esc($f_modelo) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Número de Série</label>
                    <input type="text" name="serie" class="form-control" placeholder="Número de série" value="<?= esc($f_serie) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Serviço</label>
                    <select name="servico" class="form-select">
                        <option value="">Todos os serviços</option>
                        <?php foreach ($servicos as $s): ?>
                        <option value="<?= esc($s->servico) ?>" <?= $f_servico === $s->servico ? 'selected' : '' ?>><?= esc($s->servico) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach (['Ativo','Em manutenção','Inativo','Em calibração','Em quarentena','Abatido'] as $e): ?>
                        <option value="<?= $e ?>" <?= $f_estado === $e ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Criticidade</label>
                    <select name="critic" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach (['Alta','Média','Baixa','Suporte de vida'] as $c): ?>
                        <option value="<?= $c ?>" <?= $f_critic === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fornecedor</label>
                    <select name="fornec" class="form-select">
                        <option value="0">Todos os fornecedores</option>
                        <?php foreach ($fornecedores as $f): ?>
                        <option value="<?= $f->id ?>" <?= $f_fornec === (int)$f->id ? 'selected' : '' ?>><?= esc($f->nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Categoria</label>
                    <select name="cat" class="form-select">
                        <option value="0">Todas as categorias</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c->id ?>" <?= $f_cat === (int)$c->id ? 'selected' : '' ?>><?= esc($c->nome) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i> Pesquisar</button>
                <a href="pesquisa.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i> Limpar</a>
            </div>
        </form>
    </div>
</div>

<?php if ($pesquisou): ?>
<div class="card mhs-data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Resultados</strong>
        <span class="badge bg-primary"><?= count($resultados) ?> equipamento<?= count($resultados) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mhs-datatable mb-0">
                <thead class="mhs-thead">
                    <tr>
                        <th>Código</th>
                        <th>Designação</th>
                        <th>Marca / Modelo</th>
                        <th>Categoria</th>
                        <th>Serviço</th>
                        <th>Estado</th>
                        <th>Criticidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($resultados)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum equipamento encontrado com os filtros selecionados.</td></tr>
                <?php else: foreach ($resultados as $eq): ?>
                    <tr>
                        <td><code><?= esc($eq->codigo_inventario) ?></code></td>
                        <td><?= esc($eq->designacao) ?></td>
                        <td><?= esc($eq->marca) ?><?= $eq->modelo ? ' / ' . esc($eq->modelo) : '' ?></td>
                        <td><?= esc($eq->categoria ?? '—') ?></td>
                        <td><?= esc($eq->servico ?? '—') ?><?= $eq->sala ? ' · ' . esc($eq->sala) : '' ?></td>
                        <td><?= get_estado_badge($eq->estado) ?></td>
                        <td><?= get_criticidade_badge($eq->criticidade) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalhes"><i class="fa-solid fa-eye"></i></a>
                            <a href="<?= BASE_URL ?>/private/views/equipamentos/editar.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
