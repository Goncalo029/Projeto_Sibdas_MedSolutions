<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Categorias - Lista';
$categorias = [];
$erro_bd = '';

try {
    $categorias = mhs_pdo()->query("
        SELECT id, nome, descricao
        FROM categorias
        WHERE eliminado_em IS NULL
        ORDER BY nome
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar categorias.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-tags fa-fw"></i></span>
    <h1 class="mhs-page-title">Categorias</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-tags mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Categorias</span>
      <span class="mhs-table-toolbar-count"><?= count($categorias) ?> registos</span>
    </div>
    <a href="nova.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Nova Categoria</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="categoriasTable">
        <thead class="mhs-thead">
          <tr><th>Nome</th><th>Descricao</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($categorias as $categoria) : ?>
            <tr>
              <td><?= esc($categoria->nome) ?></td>
              <td><?= esc($categoria->descricao) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $categoria->id ?>" class="btn btn-sm btn-outline-secondary" title="Detalhes"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $categoria->id ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $categoria->id ?>" data-delete-name="<?= esc($categoria->nome) ?>" title="Apagar"><i class="fa-solid fa-trash"></i></button>
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
