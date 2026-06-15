<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Documentos - Lista';
$documentos = [];
$erro_bd = '';

try {
    $documentos = mhs_pdo()->query("
        SELECT d.id, d.tipo_documento, d.nome_documento, d.data_documento, d.data_validade, d.nome_ficheiro,
               e.codigo_inventario, e.designacao
        FROM documentos d
        JOIN equipamentos e ON e.id = d.id_equipamento
        WHERE d.deleted_at IS NULL
        ORDER BY d.data_documento DESC, d.nome_documento
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar documentos.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
    <h1 class="mhs-page-title">Documentos</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-file-lines mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Documentos</span>
      <span class="mhs-table-toolbar-count"><?= count($documentos) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Novo Documento</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="documentosTable">
        <thead class="mhs-thead">
          <tr><th>Equipamento</th><th>Tipo</th><th>Nome</th><th>Data</th><th>Validade</th><th>PDF</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $documento) : ?>
            <tr>
              <td><?= esc($documento->codigo_inventario . ' - ' . $documento->designacao) ?></td>
              <td><?= esc($documento->tipo_documento) ?></td>
              <td><?= esc($documento->nome_documento) ?></td>
              <td><?= $documento->data_documento ? esc(date('d/m/Y', strtotime($documento->data_documento))) : '' ?></td>
              <td><?= $documento->data_validade ? esc(date('d/m/Y', strtotime($documento->data_validade))) : '' ?></td>
              <td>
                <a href="download.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-danger" title="Descarregar PDF"><i class="fa-solid fa-file-pdf"></i></a>
              </td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $documento->id ?>" data-delete-name="<?= esc($documento->nome_documento) ?>"><i class="fa-solid fa-trash"></i></button>
                  <?php endif; ?>
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
