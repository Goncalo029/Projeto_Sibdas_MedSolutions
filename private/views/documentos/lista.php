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

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
    <h1 class="mhs-page-title">Documentos</h1>
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
        <input type="text" class="form-control" id="documentosSearch" placeholder="Equipamento, tipo ou nome..." />
      </div>
      <div class="col-md-6 text-end">
        <button class="btn btn-outline-secondary" onclick="document.getElementById('documentosSearch').value=''; jQuery('#documentosTable').DataTable().search('').draw();"><i class="fa-solid fa-times me-2"></i>Limpar</button>
      </div>
    </div>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="documentosTable">
        <thead class="mhs-thead">
          <tr><th>Equipamento</th><th>Tipo</th><th>Nome</th><th>Data</th><th>Validade</th><th>Ficheiro</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $documento) : ?>
            <tr>
              <td><?= esc($documento->codigo_inventario . ' - ' . $documento->designacao) ?></td>
              <td><?= esc($documento->tipo_documento) ?></td>
              <td><?= esc($documento->nome_documento) ?></td>
              <td><?= $documento->data_documento ? esc(date('d/m/Y', strtotime($documento->data_documento))) : '' ?></td>
              <td><?= $documento->data_validade ? esc(date('d/m/Y', strtotime($documento->data_validade))) : '' ?></td>
              <td><?= esc($documento->nome_ficheiro) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $documento->id ?>" data-delete-name="<?= esc($documento->nome_documento) ?>"><i class="fa-solid fa-trash"></i></button>
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
