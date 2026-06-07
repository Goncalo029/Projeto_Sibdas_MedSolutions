<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Equipamentos - Lista';
$equipamentos = [];
$erro_bd = '';

try {
    $equipamentos = mhs_pdo()->query("
        SELECT e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
               e.estado, e.criticidade,
               c.nome AS categoria, l.servico,
               COUNT(d.id) AS total_documentos
        FROM equipamentos e
        LEFT JOIN categorias c ON c.id = e.id_categoria
        LEFT JOIN localizacoes l ON l.id = e.id_localizacao
        LEFT JOIN documentos d ON d.id_equipamento = e.id AND d.deleted_at IS NULL
        WHERE e.deleted_at IS NULL
        GROUP BY e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
                 e.estado, e.criticidade, c.nome, l.servico
        ORDER BY e.codigo_inventario
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar equipamentos.';
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

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-stethoscope mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Equipamentos</span>
      <span class="mhs-table-toolbar-count"><?= count($equipamentos) ?> registos</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <a href="exportar_csv.php" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-csv me-1"></i>Exportar CSV
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
      <table class="table mhs-datatable mb-0" id="equipamentosTable">
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

<script>
function mhsExportarPDF() {
    var w = window.open('', '_blank', 'width=1100,height=800');
    var rows = '';
    document.querySelectorAll('#equipamentosTable tbody tr').forEach(function(tr) {
        var tds = tr.querySelectorAll('td');
        if (tds.length < 8) return;
        rows += '<tr>';
        rows += '<td>' + (tds[0].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[1].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[2].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[3].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[4].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[5].textContent.trim()) + '</td>';
        rows += '<td>' + (tds[6].textContent.trim()) + '</td>';
        rows += '</tr>';
    });
    w.document.write('<html><head><title>Equipamentos</title>');
    w.document.write('<style>*{font-family:Arial,sans-serif;font-size:11px}body{padding:20px}h2{font-size:14px;margin-bottom:12px}table{width:100%;border-collapse:collapse}th{background:#0d6ea8;color:#fff;padding:7px 8px;text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.5px}td{padding:6px 8px;border-bottom:1px solid #e2e8f0}tr:nth-child(even) td{background:#f8fafc}.foot{margin-top:10px;font-size:10px;color:#94a3b8}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>Lista de Equipamentos &mdash; ' + new Date().toLocaleDateString('pt-PT') + '</h2>');
    w.document.write('<table><thead><tr><th>Código</th><th>Designação</th><th>Marca</th><th>Categoria</th><th>Serviço</th><th>Estado</th><th>Criticidade</th></tr></thead><tbody>' + rows + '</tbody></table>');
    w.document.write('<p class="foot">Total: <?= count($equipamentos) ?> equipamentos</p>');
    w.document.write('<script>window.onload=function(){window.print();window.close()}<\/script>');
    w.document.write('</body></html>');
    w.document.close();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
