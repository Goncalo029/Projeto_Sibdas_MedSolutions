<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Garantias-Contrato - Lista';
$garantias = [];
$erro_bd = '';

try {
    $garantias = mhs_pdo()->query("
        SELECT g.id, g.data_inicio, g.data_fim, g.tem_contrato, g.tipo_contrato, g.entidade_responsavel,
               e.codigo_inventario, e.designacao
        FROM garantias_contratos g
        JOIN equipamentos e ON e.id = g.id_equipamento
        WHERE g.deleted_at IS NULL
        ORDER BY g.data_fim ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar garantias e contratos.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span>
    <h1 class="mhs-page-title">Garantias-Contrato</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-shield-halved mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Garantias e Contratos</span>
      <span class="mhs-table-toolbar-count"><?= count($garantias) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Nova Garantia</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="garantiasTable">
        <thead class="mhs-thead">
          <tr><th>Equipamento</th><th>Inicio</th><th>Fim</th><th>Contrato</th><th>Tipo</th><th>Entidade</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($garantias as $garantia) : ?>
            <tr>
              <td><?= esc($garantia->codigo_inventario . ' - ' . $garantia->designacao) ?></td>
              <td><?= $garantia->data_inicio ? esc(date('d/m/Y', strtotime($garantia->data_inicio))) : '' ?></td>
              <td><?= $garantia->data_fim ? esc(date('d/m/Y', strtotime($garantia->data_fim))) : '' ?></td>
              <td><?= $garantia->tem_contrato ? 'Sim' : 'Nao' ?></td>
              <td><?= esc($garantia->tipo_contrato) ?></td>
              <td><?= esc($garantia->entidade_responsavel) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $garantia->id ?>" data-delete-name="<?= esc($garantia->codigo_inventario) ?>"><i class="fa-solid fa-trash"></i></button>
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
