<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$registos = [];
$erro_bd = '';

try {
    $pdo = mhs_pdo();
    $pdo->exec("CREATE TABLE IF NOT EXISTS assistencia_tecnica (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        empresa VARCHAR(190) NOT NULL,
        nome_contacto VARCHAR(190) NOT NULL,
        email VARCHAR(190) DEFAULT NULL,
        telefone VARCHAR(30) DEFAULT NULL,
        telefone_urgencia VARCHAR(30) DEFAULT NULL,
        observacoes TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $registos = $pdo->query("
        SELECT id, empresa, nome_contacto, email, telefone, telefone_urgencia
        FROM assistencia_tecnica
        WHERE deleted_at IS NULL
        ORDER BY empresa
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Não foi possível carregar os contactos de assistência técnica.';
}

$page_title = 'Assistência Técnica';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-headset fa-fw"></i></span>
    <h1 class="mhs-page-title">Assistência Técnica</h1>
  </div>
</div>

<?php if ($erro_bd): ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-headset mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Contactos de Assistência Técnica</span>
      <span class="mhs-table-toolbar-count"><?= count($registos) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn">
      <i class="fa-solid fa-plus"></i> Novo Contacto
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="atTable">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Nome do Contacto</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Urgência</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registos as $r): ?>
            <tr>
              <td class="mhs-td-primary"><?= esc($r->empresa) ?></td>
              <td><?= esc($r->nome_contacto) ?></td>
              <td><?= $r->email ? '<a href="mailto:' . esc($r->email) . '">' . esc($r->email) . '</a>' : '—' ?></td>
              <td><?= $r->telefone ? '<a href="tel:' . esc($r->telefone) . '">' . esc($r->telefone) . '</a>' : '—' ?></td>
              <td>
                <?php if ($r->telefone_urgencia): ?>
                  <a href="tel:<?= esc($r->telefone_urgencia) ?>" class="badge bg-danger text-white text-decoration-none">
                    <i class="fa-solid fa-phone me-1"></i><?= esc($r->telefone_urgencia) ?>
                  </a>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int)$r->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver detalhes"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int)$r->id ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger"
                    data-delete-id="<?= (int)$r->id ?>"
                    data-delete-name="<?= esc($r->empresa) ?>"
                    title="Apagar"><i class="fa-solid fa-trash"></i></button>
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
