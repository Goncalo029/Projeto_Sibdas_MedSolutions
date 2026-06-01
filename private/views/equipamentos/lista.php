<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Equipamentos - Lista';
$equipamentos = [];
$erro_bd = '';

try {
    $equipamentos = mhs_pdo()->query("
        SELECT e.id, e.codigo_inventario, e.designacao, e.marca, e.estado, e.criticidade,
               c.nome AS categoria, l.servico,
               COUNT(d.id) AS total_documentos
        FROM equipamentos e
        LEFT JOIN categorias c ON c.id = e.id_categoria
        LEFT JOIN localizacoes l ON l.id = e.id_localizacao
        LEFT JOIN documentos d ON d.id_equipamento = e.id AND d.deleted_at IS NULL
        WHERE e.deleted_at IS NULL
        GROUP BY e.id, e.codigo_inventario, e.designacao, e.marca, e.estado, e.criticidade, c.nome, l.servico
        ORDER BY e.codigo_inventario
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar equipamentos.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="novo.php" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Novo</a>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Pesquisar</label>
        <input type="text" class="form-control" id="equipamentosSearch" placeholder="Código ou designação..." />
      </div>
      <div class="col-md-6 text-end">
        <button class="btn btn-outline-secondary" onclick="document.getElementById('equipamentosSearch').value=''; jQuery('#equipamentosTable').DataTable().search('').draw();"><i class="fa-solid fa-times me-2"></i>Limpar</button>
      </div>
    </div>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="equipamentosTable">
        <thead class="mhs-thead">
          <tr><th>Codigo</th><th>Designacao</th><th>Marca</th><th>Categoria</th><th>Servico</th><th>Estado</th><th>Criticidade</th><th>Docs</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($equipamentos as $equipamento) : ?>
            <tr>
              <td><?= esc($equipamento->codigo_inventario) ?></td>
              <td><?= esc($equipamento->designacao) ?></td>
              <td><?= esc($equipamento->marca) ?></td>
              <td><?= esc($equipamento->categoria) ?></td>
              <td><?= esc($equipamento->servico) ?></td>
              <td><?= get_estado_badge($equipamento->estado) ?></td>
              <td><?= get_criticidade_badge($equipamento->criticidade) ?></td>
              <td><?= (int) $equipamento->total_documentos ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $equipamento->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $equipamento->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $equipamento->id ?>" data-delete-name="<?= esc($equipamento->codigo_inventario) ?>"><i class="fa-solid fa-trash"></i></button>
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
