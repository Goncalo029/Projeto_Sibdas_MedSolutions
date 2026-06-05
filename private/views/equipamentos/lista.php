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
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn">
      <i class="fa-solid fa-plus"></i>
      Novo Equipamento
    </a>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>
