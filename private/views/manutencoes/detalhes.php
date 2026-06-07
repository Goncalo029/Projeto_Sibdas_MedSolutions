<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("
    SELECT m.*, e.codigo_inventario, e.designacao, e.id AS eq_id
    FROM manutencoes m
    JOIN equipamentos e ON e.id = m.id_equipamento
    WHERE m.id=? AND m.deleted_at IS NULL
");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$page_title = 'Manutenções - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker">
      <?php if ($row->tipo === 'Urgência'): ?>
        <i class="fa-solid fa-triangle-exclamation fa-fw text-danger"></i>
      <?php else: ?>
        <i class="fa-solid fa-wrench fa-fw"></i>
      <?php endif; ?>
    </span>
    <h1 class="mhs-page-title">
      Manutenção <?= $row->tipo === 'Urgência' ? '<span class="badge bg-danger ms-2">Urgência</span>' : '<span class="badge bg-primary ms-2">Preventiva</span>' ?>
    </h1>
  </div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<div class="card mhs-data-card">
  <div class="mhs-tab-body">
    <div class="row g-4">
      <div class="col-md-5">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-stethoscope"></i> Equipamento</div>
          <dl class="mhs-info-dl">
            <dt>Código</dt>
            <dd><a href="../equipamentos/detalhes.php?id=<?= (int)$row->eq_id ?>"><span class="mhs-code"><?= esc($row->codigo_inventario) ?></span></a></dd>
            <dt>Designação</dt><dd><?= esc($row->designacao) ?></dd>
          </dl>
        </div>

        <div class="mhs-info-group mt-3">
          <div class="mhs-info-group-title"><i class="fa-solid fa-wrench"></i> Dados da Manutenção</div>
          <dl class="mhs-info-dl">
            <dt>Tipo</dt>
            <dd>
              <?= $row->tipo === 'Urgência'
                ? '<span class="badge bg-danger">Urgência</span>'
                : '<span class="badge bg-primary">Preventiva</span>' ?>
            </dd>
            <dt>Estado</dt>
            <dd>
              <?php
              $est = $row->estado;
              $cls = match($est) {
                  'Concluída' => 'bg-success', 'Em curso' => 'bg-info text-dark',
                  'Planeada'  => 'bg-primary',  default    => 'bg-secondary',
              };
              echo "<span class='badge $cls'>$est</span>";
              ?>
            </dd>
            <dt>Data da manutenção</dt>
            <dd><?= $row->data_manutencao ? date('d/m/Y', strtotime($row->data_manutencao)) : '—' ?></dd>
            <?php if ($row->tipo === 'Preventiva'): ?>
              <dt>Próxima manutenção</dt>
              <dd>
                <?php if ($row->proxima_manutencao):
                  $vencida = $row->proxima_manutencao < date('Y-m-d') && $row->estado !== 'Concluída'; ?>
                  <span class="<?= $vencida ? 'text-danger fw-semibold' : '' ?>">
                    <?= date('d/m/Y', strtotime($row->proxima_manutencao)) ?>
                    <?= $vencida ? ' <i class="fa-solid fa-triangle-exclamation"></i>' : '' ?>
                  </span>
                <?php else: ?>—<?php endif; ?>
              </dd>
              <dt>Periodicidade</dt>
              <dd><?= $row->periodicidade ? esc($row->periodicidade) : '—' ?></dd>
            <?php endif; ?>
            <dt>Técnico responsável</dt>
            <dd><?= $row->tecnico_responsavel ? esc($row->tecnico_responsavel) : '—' ?></dd>
          </dl>
        </div>
      </div>

      <div class="col-md-7">
        <?php if ($row->tipo === 'Urgência' && $row->descricao): ?>
        <div class="mhs-info-group">
          <div class="mhs-info-group-title text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Descrição da Urgência</div>
          <div class="p-3 rounded" style="background:#fff5f5;border:1px solid #fecaca;white-space:pre-wrap"><?= esc($row->descricao) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($row->observacoes): ?>
        <div class="mhs-info-group <?= ($row->tipo === 'Urgência' && $row->descricao) ? 'mt-3' : '' ?>">
          <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
          <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;white-space:pre-wrap"><?= esc($row->observacoes) ?></div>
        </div>
        <?php endif; ?>

        <div class="mhs-info-group mt-3">
          <div class="mhs-info-group-title"><i class="fa-solid fa-clock"></i> Registo</div>
          <dl class="mhs-info-dl">
            <dt>Criado em</dt><dd><?= date('d/m/Y H:i', strtotime($row->created_at)) ?></dd>
            <?php if ($row->created_by): ?>
              <dt>Por</dt><dd><?= esc($row->created_by) ?></dd>
            <?php endif; ?>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
