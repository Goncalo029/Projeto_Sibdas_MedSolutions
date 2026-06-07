<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$registos = [];
$erro_bd = '';
$filtro_tipo = $_GET['tipo'] ?? '';

try {
    $pdo = mhs_pdo();
    $pdo->exec("CREATE TABLE IF NOT EXISTS manutencoes (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_equipamento INT UNSIGNED NOT NULL,
        tipo ENUM('Preventiva','Urgência') NOT NULL DEFAULT 'Preventiva',
        data_manutencao DATE DEFAULT NULL,
        proxima_manutencao DATE DEFAULT NULL,
        periodicidade VARCHAR(50) DEFAULT NULL,
        estado VARCHAR(30) NOT NULL DEFAULT 'Planeada',
        tecnico_responsavel VARCHAR(190) DEFAULT NULL,
        descricao TEXT DEFAULT NULL,
        observacoes TEXT DEFAULT NULL,
        created_by VARCHAR(190) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        deleted_at DATETIME DEFAULT NULL,
        FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $where = "m.deleted_at IS NULL";
    $params = [];
    if ($filtro_tipo) {
        $where .= " AND m.tipo = ?";
        $params[] = $filtro_tipo;
    }

    $stmt = $pdo->prepare("
        SELECT m.*, e.codigo_inventario, e.designacao
        FROM manutencoes m
        JOIN equipamentos e ON e.id = m.id_equipamento
        WHERE $where
        ORDER BY
            CASE m.estado
                WHEN 'Planeada' THEN 1
                WHEN 'Em curso' THEN 2
                WHEN 'Concluída' THEN 3
                WHEN 'Cancelada' THEN 4
                ELSE 5
            END,
            m.data_manutencao DESC
    ");
    $stmt->execute($params);
    $registos = $stmt->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Não foi possível carregar as manutenções.';
}

$page_title = 'Manutenções';
include __DIR__ . '/../../includes/header.php';

function badge_estado_man(string $estado): string {
    return match($estado) {
        'Concluída'  => '<span class="badge bg-success">' . $estado . '</span>',
        'Em curso'   => '<span class="badge bg-info text-dark">' . $estado . '</span>',
        'Planeada'   => '<span class="badge bg-primary">' . $estado . '</span>',
        'Cancelada'  => '<span class="badge bg-secondary">' . $estado . '</span>',
        default      => '<span class="badge bg-secondary">' . htmlspecialchars($estado) . '</span>',
    };
}
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-wrench fa-fw"></i></span>
    <h1 class="mhs-page-title">Manutenções</h1>
  </div>
</div>

<?php if ($erro_bd): ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<!-- Filtro por tipo -->
<div class="d-flex gap-2 mb-3">
  <a href="lista.php" class="btn btn-sm <?= !$filtro_tipo ? 'btn-primary' : 'btn-outline-secondary' ?>">
    Todas
  </a>
  <a href="lista.php?tipo=Preventiva" class="btn btn-sm <?= $filtro_tipo === 'Preventiva' ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <i class="fa-solid fa-calendar-check me-1"></i>Preventivas
  </a>
  <a href="lista.php?tipo=Urgência" class="btn btn-sm <?= $filtro_tipo === 'Urgência' ? 'btn-danger' : 'btn-outline-danger' ?>">
    <i class="fa-solid fa-triangle-exclamation me-1"></i>Urgências
  </a>
</div>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-wrench mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Registo de Manutenções</span>
      <span class="mhs-table-toolbar-count"><?= count($registos) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn">
      <i class="fa-solid fa-plus"></i> Nova Manutenção
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="manTable">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Equipamento</th>
            <th>Data</th>
            <th>Próxima</th>
            <th>Estado</th>
            <th>Responsável</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registos as $r): ?>
            <tr>
              <td>
                <?php if ($r->tipo === 'Urgência'): ?>
                  <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Urgência</span>
                <?php else: ?>
                  <span class="badge bg-primary"><i class="fa-solid fa-calendar-check me-1"></i>Preventiva</span>
                <?php endif; ?>
              </td>
              <td class="mhs-td-primary">
                <a href="../equipamentos/detalhes.php?id=<?= (int)$r->id_equipamento ?>" class="text-decoration-none">
                  <span class="mhs-code me-1"><?= esc($r->codigo_inventario) ?></span>
                  <?= esc($r->designacao) ?>
                </a>
              </td>
              <td><?= $r->data_manutencao ? date('d/m/Y', strtotime($r->data_manutencao)) : '—' ?></td>
              <td>
                <?php if ($r->proxima_manutencao): ?>
                  <?php $vencida = $r->proxima_manutencao < date('Y-m-d') && $r->estado !== 'Concluída'; ?>
                  <span class="<?= $vencida ? 'text-danger fw-semibold' : '' ?>">
                    <?= date('d/m/Y', strtotime($r->proxima_manutencao)) ?>
                    <?= $vencida ? ' <i class="fa-solid fa-circle-exclamation"></i>' : '' ?>
                  </span>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><?= badge_estado_man($r->estado) ?></td>
              <td><?= $r->tecnico_responsavel ? esc($r->tecnico_responsavel) : '—' ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int)$r->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int)$r->id ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <button type="button" class="btn btn-sm btn-outline-danger"
                    data-delete-id="<?= (int)$r->id ?>"
                    data-delete-name="<?= esc($r->codigo_inventario . ' - ' . $r->tipo) ?>"
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
