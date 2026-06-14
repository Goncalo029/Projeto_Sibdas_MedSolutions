<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();
require_admin(); // gestão de utilizadores apenas para administradores

$page_title = 'Gestao de Utilizadores';
$utilizadores = [];
$erro_bd = '';

try {
    $stmt = mhs_pdo()->prepare("
        SELECT id, AES_DECRYPT(name, :chave) AS email, profile, last_login, created_at
        FROM agents
        WHERE deleted_at IS NULL
        ORDER BY profile, email
    ");
    $stmt->execute([':chave' => MYSQL_AES_KEY]);
    $utilizadores = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar utilizadores.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-user-gear fa-fw"></i></span>
    <h1 class="mhs-page-title">Utilizadores</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-users mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Gestão de Utilizadores</span>
      <span class="mhs-table-toolbar-count"><?= count($utilizadores) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-user-plus"></i> Novo Utilizador</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="utilizadoresTable">
        <thead class="mhs-thead">
          <tr><th>Email</th><th>Perfil</th><th>Ultimo login</th><th>Criado em</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($utilizadores as $utilizador) : ?>
            <tr>
              <td><?= esc($utilizador->email) ?></td>
              <td><span class="badge bg-info"><?= esc($utilizador->profile) ?></span></td>
              <td><?= $utilizador->last_login ? esc(date('d/m/Y H:i', strtotime($utilizador->last_login))) : '' ?></td>
              <td><?= $utilizador->created_at ? esc(date('d/m/Y', strtotime($utilizador->created_at))) : '' ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $utilizador->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $utilizador->id ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $utilizador->id ?>" data-delete-name="<?= esc($utilizador->email) ?>"><i class="fa-solid fa-trash"></i></button>
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
