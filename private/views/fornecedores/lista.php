<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$page_title = 'Fornecedores - Lista';
$fornecedores = [];
$erro_bd = '';

try {
    $fornecedores = mhs_pdo()->query("
        SELECT id, nome, nif, tipo_fornecedor, telefone, email
        FROM fornecedores
        WHERE eliminado_em IS NULL
        ORDER BY nome
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar fornecedores.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-truck fa-fw"></i></span>
    <h1 class="mhs-page-title">Fornecedores</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-truck mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Fornecedores</span>
      <span class="mhs-table-toolbar-count"><?= count($fornecedores) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Novo Fornecedor</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="fornecedoresTable">
        <thead class="mhs-thead">
          <tr><th>Nome</th><th>NIF</th><th>Tipo</th><th>Telefone</th><th>Email</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($fornecedores as $fornecedor) : ?>
            <tr>
              <td><?= esc($fornecedor->nome) ?></td>
              <td><?= esc($fornecedor->nif) ?></td>
              <td><?= esc($fornecedor->tipo_fornecedor) ?></td>
              <td><?= esc($fornecedor->telefone) ?></td>
              <td><?= esc($fornecedor->email) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $fornecedor->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $fornecedor->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $fornecedor->id ?>" data-delete-name="<?= esc($fornecedor->nome) ?>"><i class="fa-solid fa-trash"></i></button>
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
