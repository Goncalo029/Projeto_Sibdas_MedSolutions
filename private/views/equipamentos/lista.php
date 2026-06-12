<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Equipamentos - Lista';
$equipamentos = [];
$erro_bd = '';

// Filtros vindos das notificações (ou de links diretos)
$f_estado = trim($_GET['estado'] ?? '');
$f_filtro = trim($_GET['filtro'] ?? '');

$where  = "e.deleted_at IS NULL";
$params = [];

if ($f_estado !== '') {
    $where .= " AND e.estado = ?";
    $params[] = $f_estado;
}
if ($f_filtro === 'manutencao_atraso') {
    $where .= " AND EXISTS (SELECT 1 FROM manutencoes_preventivas mp
                WHERE mp.id_equipamento = e.id AND mp.proxima_manutencao < CURDATE()
                AND mp.estado NOT IN ('Concluída', 'Cancelada') AND mp.deleted_at IS NULL)";
} elseif ($f_filtro === 'manutencao_7dias') {
    $where .= " AND EXISTS (SELECT 1 FROM manutencoes_preventivas mp
                WHERE mp.id_equipamento = e.id
                AND mp.proxima_manutencao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND mp.estado NOT IN ('Concluída', 'Cancelada') AND mp.deleted_at IS NULL)";
} elseif ($f_filtro === 'emprestimo_30dias') {
    $where .= " AND EXISTS (SELECT 1 FROM emprestimos_equipamentos ee
                WHERE ee.id_equipamento = e.id AND ee.data_devolucao IS NULL
                AND ee.data_saida < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ee.deleted_at IS NULL)";
}

$filtro_label = match(true) {
    $f_estado !== ''                   => 'Estado: ' . $f_estado,
    $f_filtro === 'manutencao_atraso'  => 'Manutenções em atraso',
    $f_filtro === 'manutencao_7dias'   => 'Manutenção prevista nos próximos 7 dias',
    $f_filtro === 'emprestimo_30dias'  => 'Empréstimos em curso há mais de 30 dias',
    default                            => '',
};

try {
    $stmt = mhs_pdo()->prepare("
        SELECT e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
               e.estado, e.criticidade,
               c.nome AS categoria, l.servico,
               COUNT(d.id) AS total_documentos
        FROM equipamentos e
        LEFT JOIN categorias c ON c.id = e.id_categoria
        LEFT JOIN localizacoes l ON l.id = e.id_localizacao
        LEFT JOIN documentos d ON d.id_equipamento = e.id AND d.deleted_at IS NULL
        WHERE $where
        GROUP BY e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
                 e.estado, e.criticidade, c.nome, l.servico
        ORDER BY e.codigo_inventario
    ");
    $stmt->execute($params);
    $equipamentos = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar equipamentos.';
}

// ── Exportação inline ──────────────────────────────────────────────────
$export = $_GET['export'] ?? '';
if ($export === 'csv' && !$erro_bd) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="equipamentos_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
    fputcsv($out, ['Código', 'Designação', 'Marca', 'Modelo', 'Nº Série', 'Categoria', 'Serviço', 'Estado', 'Criticidade', 'Documentos']);
    foreach ($equipamentos as $eq) {
        fputcsv($out, [
            $eq->codigo_inventario, $eq->designacao, $eq->marca,
            $eq->modelo, $eq->numero_serie, $eq->categoria,
            $eq->servico, $eq->estado, $eq->criticidade, (int)$eq->total_documentos,
        ]);
    }
    fclose($out);
    exit;
}
if ($export === 'json' && !$erro_bd) {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="equipamentos_' . date('Ymd_His') . '.json"');
    echo json_encode(array_map(fn($eq) => [
        'codigo_inventario' => $eq->codigo_inventario,
        'designacao'        => $eq->designacao,
        'marca'             => $eq->marca,
        'modelo'            => $eq->modelo,
        'numero_serie'      => $eq->numero_serie,
        'categoria'         => $eq->categoria,
        'servico'           => $eq->servico,
        'estado'            => $eq->estado,
        'criticidade'       => $eq->criticidade,
        'total_documentos'  => (int)$eq->total_documentos,
    ], $equipamentos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<?php if ($filtro_label !== '') : ?>
<div class="alert alert-info d-flex align-items-center justify-content-between mb-3">
  <span><i class="fa-solid fa-filter me-2"></i>Filtro ativo: <strong><?= esc($filtro_label) ?></strong> — <?= count($equipamentos) ?> resultado<?= count($equipamentos) !== 1 ? 's' : '' ?></span>
  <a href="lista.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Limpar filtro</a>
</div>
<?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-stethoscope mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Equipamentos</span>
      <span class="mhs-table-toolbar-count"><?= count($equipamentos) ?> registos</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <?php $qs_filtros = http_build_query(array_filter(['estado' => $f_estado, 'filtro' => $f_filtro])); ?>
      <a href="?export=csv<?= $qs_filtros ? '&' . esc($qs_filtros) : '' ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-csv me-1"></i>Exportar CSV
      </a>
      <a href="?export=json<?= $qs_filtros ? '&' . esc($qs_filtros) : '' ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-brackets-curly me-1"></i>Exportar JSON
      </a>
      <button onclick="mhsExportarPDF()" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
      </button>
      <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn">
        <i class="fa-solid fa-plus"></i>
        Novo Equipamento
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="equipamentosTable" data-total="<?= count($equipamentos) ?>">
        <thead>
          <tr>
            <th>Código</th>
            <th>Designação</th>
            <th>Marca</th>
            <th>Categoria</th>
            <th>Serviço</th>
            <th>Estado</th>
            <th>Criticidade</th>
            <th>Docs</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($equipamentos as $eq) : ?>
            <tr>
              <td><span class="mhs-code"><?= esc($eq->codigo_inventario) ?></span></td>
              <td class="mhs-td-primary"><?= esc($eq->designacao) ?></td>
              <td><?= esc($eq->marca) ?></td>
              <td><?= esc($eq->categoria) ?></td>
              <td><?= esc($eq->servico) ?></td>
              <td><?= get_estado_badge($eq->estado) ?></td>
              <td><?= get_criticidade_badge($eq->criticidade) ?></td>
              <td><span class="mhs-docs-count"><?= (int)$eq->total_documentos ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int)$eq->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver detalhes"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int)$eq->id ?>"  class="btn btn-sm btn-outline-primary"   title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int)$eq->id ?>" data-delete-name="<?= esc($eq->codigo_inventario) ?>" title="Apagar"><i class="fa-solid fa-trash"></i></button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
