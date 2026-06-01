<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

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

<div class="mhs-page-header mhs-page-header--dashboard">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-user-gear fa-fw"></i></span>
    <h1 class="mhs-page-title">Gestao de Utilizadores</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="novo.php" class="btn btn-primary"><i class="fa-solid fa-user-plus me-2"></i>Novo Utilizador</a>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card mb-4">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Pesquisar</label>
        <input type="text" class="form-control" id="utilizadoresSearch" placeholder="Email ou perfil..." />
      </div>
      <div class="col-md-6 text-end">
        <button class="btn btn-outline-secondary" onclick="document.getElementById('utilizadoresSearch').value=''; jQuery('#utilizadoresTable').DataTable().search('').draw();"><i class="fa-solid fa-times me-2"></i>Limpar</button>
      </div>
    </div>
  </div>
</div>

<div class="card mhs-data-card">
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
