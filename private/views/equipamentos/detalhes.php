<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$eq = null;
$fornecedores = [];
$documentos = [];
$garantia = null;
$proxima_manutencao = null;
$manutencao_info = null;
$contacto_at = null;
$fornecedores_at = [];

if ($id > 0) {
    try {
        $pdo = mhs_pdo();

        $eq = $pdo->prepare("
            SELECT e.*, c.nome AS categoria, l.servico, l.piso, l.sala
            FROM equipamentos e
            LEFT JOIN categorias c ON c.id = e.id_categoria
            LEFT JOIN localizacoes l ON l.id = e.id_localizacao
            WHERE e.id = ? AND e.deleted_at IS NULL
        ");
        $eq->execute([$id]);
        $eq = $eq->fetch();

        if ($eq) {
            $fornecedores = $pdo->prepare("
                SELECT f.nome, ef.tipo_relacao, f.telefone, f.email
                FROM equipamentos_fornecedores ef
                JOIN fornecedores f ON f.id = ef.id_fornecedor
                WHERE ef.id_equipamento = ?
            ");
            $fornecedores->execute([$id]);
            $fornecedores = $fornecedores->fetchAll();

            foreach ($fornecedores as $f) {
                if (stripos($f->tipo_relacao ?? '', 'assist') !== false || stripos($f->tipo_relacao ?? '', 'tecni') !== false) {
                    $fornecedores_at[] = $f;
                    if (!$contacto_at) $contacto_at = $f;
                }
            }

            $documentos = $pdo->prepare("
                SELECT * FROM documentos
                WHERE id_equipamento = ? AND deleted_at IS NULL
                ORDER BY data_documento DESC
            ");
            $documentos->execute([$id]);
            $documentos = $documentos->fetchAll();

            $garantia = $pdo->prepare("
                SELECT * FROM garantias_contratos
                WHERE id_equipamento = ? AND deleted_at IS NULL
                ORDER BY id DESC LIMIT 1
            ");
            $garantia->execute([$id]);
            $garantia = $garantia->fetch();

            $man = $pdo->prepare("
                SELECT * FROM manutencoes_preventivas
                WHERE id_equipamento = ? AND deleted_at IS NULL
                ORDER BY id DESC LIMIT 1
            ");
            $man->execute([$id]);
            $manutencao_info = $man->fetch();
            $proxima_manutencao = $manutencao_info ? $manutencao_info->proxima_manutencao : null;
        }
    } catch (PDOException $e) {
        // falha silenciosa — dados ficam null
    }
}

$page_title = 'Equipamentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title"><?= $eq ? esc($eq->designacao) : 'Equipamento' ?></h1>
  </div>
  <div class="mhs-page-actions">
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<!-- Resumo rápido -->
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Código</span>
      <span class="mhs-code"><?= $eq ? esc($eq->codigo_inventario) : '—' ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Categoria</span>
      <span class="mhs-detail-summary-val"><?= $eq ? esc($eq->categoria) : '—' ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Estado</span>
      <span class="mhs-detail-summary-val"><?= $eq ? get_estado_badge($eq->estado) : '—' ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Criticidade</span>
      <span class="mhs-detail-summary-val"><?= $eq ? get_criticidade_badge($eq->criticidade) : '—' ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Localização</span>
      <span class="mhs-detail-summary-val">
        <?php if ($eq && $eq->servico): ?>
          <?= esc($eq->servico) ?><?= $eq->sala ? ' / ' . esc($eq->sala) : '' ?>
        <?php else: ?>—<?php endif; ?>
      </span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Próxima Manutenção</span>
      <span class="mhs-detail-summary-val <?= ($proxima_manutencao && $proxima_manutencao < date('Y-m-d')) ? 'text-danger fw-semibold' : '' ?>">
        <?= $proxima_manutencao ? date('d/m/Y', strtotime($proxima_manutencao)) : '—' ?>
      </span>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="ficha">
      <i class="fa-solid fa-barcode"></i> Ficha Técnica
    </button>
    <button class="mhs-detail-tab" data-tab="aquisicao">
      <i class="fa-solid fa-receipt"></i> Aquisição
    </button>
    <button class="mhs-detail-tab" data-tab="garantia">
      <i class="fa-solid fa-shield-halved"></i> Garantia / Contrato
    </button>
    <button class="mhs-detail-tab" data-tab="fornecedores">
      <i class="fa-solid fa-truck"></i> Fornecedores
    </button>
    <button class="mhs-detail-tab" data-tab="assistencia">
      <i class="fa-solid fa-headset"></i> Assistência Técnica
    </button>
    <button class="mhs-detail-tab" data-tab="documentos">
      <i class="fa-solid fa-file-lines"></i> Documentos
    </button>
    <button class="mhs-detail-tab" data-tab="emprestimos">
      <i class="fa-solid fa-boxes-packing"></i> Empréstimos
    </button>
    <button class="mhs-detail-tab" data-tab="movimentacoes">
      <i class="fa-solid fa-clock-rotate-left"></i> Movimentações
    </button>
  </div>

  <!-- Ficha Técnica -->
  <div class="mhs-tab-pane active" id="tab-ficha">
    <div class="mhs-tab-body">
      <div class="row g-4">
        <div class="col-md-6">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-barcode"></i> Identificação</div>
            <dl class="mhs-info-dl">
              <dt>Código de inventário</dt><dd><?= $eq ? esc($eq->codigo_inventario) : '—' ?></dd>
              <dt>Designação</dt><dd><?= $eq ? esc($eq->designacao) : '—' ?></dd>
              <dt>Marca / Modelo</dt><dd><?= $eq ? esc($eq->marca) . ($eq->modelo ? ' / ' . esc($eq->modelo) : '') : '—' ?></dd>
              <dt>Número de série</dt><dd><?= $eq ? esc($eq->numero_serie) : '—' ?></dd>
              <dt>Fabricante</dt><dd><?= $eq ? esc($eq->fabricante) : '—' ?></dd>
            </dl>
          </div>

        </div>
        <div class="col-md-6">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-heart-pulse"></i> Estado operacional</div>
            <dl class="mhs-info-dl">
              <dt>Estado</dt><dd><?= $eq ? get_estado_badge($eq->estado) : '—' ?></dd>
              <dt>Criticidade</dt><dd><?= $eq ? get_criticidade_badge($eq->criticidade) : '—' ?></dd>
              <dt>Localização</dt><dd><?= $eq ? esc($eq->servico) . ($eq->sala ? ' / ' . esc($eq->sala) : '') : '—' ?></dd>
              <dt>Tipo de entrada</dt><dd><?= $eq ? esc($eq->tipo_entrada) : '—' ?></dd>
            </dl>
          </div>
          <?php if ($eq && $eq->observacoes): ?>
          <div class="mhs-info-group mt-3">
            <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
            <p class="mhs-info-obs"><?= esc($eq->observacoes) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Aquisição -->
  <div class="mhs-tab-pane" id="tab-aquisicao">
    <div class="mhs-tab-body">
      <div class="row g-4">
        <div class="col-md-5">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title"><i class="fa-solid fa-receipt"></i> Dados de aquisição</div>
            <dl class="mhs-info-dl">
              <dt>Data de aquisição</dt>
              <dd><?= ($eq && $eq->data_aquisicao) ? date('d/m/Y', strtotime($eq->data_aquisicao)) : '—' ?></dd>
              <dt>Ano de fabrico</dt><dd><?= $eq ? esc($eq->ano_fabrico) : '—' ?></dd>
              <dt>Custo</dt>
              <dd><?= ($eq && $eq->custo_aquisicao) ? number_format($eq->custo_aquisicao, 2, ',', ' ') . ' €' : '—' ?></dd>
              <dt>Tipo de entrada</dt><dd><?= $eq ? esc($eq->tipo_entrada) : '—' ?></dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Garantia / Contrato -->
  <div class="mhs-tab-pane" id="tab-garantia">
    <div class="mhs-tab-body">
      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-sm btn-outline-secondary" onclick="mhsPrintSection('garantia-print-area')">
          <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
        </button>
      </div>
      <div id="garantia-print-area">
        <div class="row g-4">
          <div class="col-md-5">
            <div class="mhs-info-group">
              <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Garantia e contrato</div>
              <?php if ($garantia): ?>
                <dl class="mhs-info-dl">
                  <dt>Data de início</dt>
                  <dd><?= $garantia->data_inicio ? date('d/m/Y', strtotime($garantia->data_inicio)) : '—' ?></dd>
                  <dt>Data de fim</dt>
                  <dd><?= $garantia->data_fim ? date('d/m/Y', strtotime($garantia->data_fim)) : '—' ?></dd>
                  <dt>Tem contrato</dt>
                  <dd><?= $garantia->tem_contrato ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?></dd>
                  <dt>Entidade responsável</dt><dd><?= esc($garantia->entidade_responsavel) ?></dd>
                  <?php if ($garantia->tipo_contrato): ?>
                    <dt>Tipo de contrato</dt><dd><?= esc($garantia->tipo_contrato) ?></dd>
                  <?php endif; ?>
                  <?php if ($garantia->periodicidade): ?>
                    <dt>Periodicidade</dt><dd><?= esc($garantia->periodicidade) ?></dd>
                  <?php endif; ?>
                </dl>
              <?php else: ?>
                <p class="text-muted small">Sem garantia/contrato registado para este equipamento.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Fornecedores -->
  <div class="mhs-tab-pane" id="tab-fornecedores">
    <div class="mhs-tab-body">
      <?php if (count($fornecedores) > 0): ?>
        <table class="table mhs-datatable mb-0">
          <thead>
            <tr><th>Fornecedor</th><th>Tipo de relação</th><th>Telefone</th><th>Email</th></tr>
          </thead>
          <tbody>
            <?php foreach ($fornecedores as $f): ?>
              <tr>
                <td class="mhs-td-primary"><?= esc($f->nome) ?></td>
                <td><?= esc($f->tipo_relacao) ?></td>
                <td><?= $f->telefone ? '<a href="tel:' . esc($f->telefone) . '">' . esc($f->telefone) . '</a>' : '—' ?></td>
                <td><?= $f->email ? '<a href="mailto:' . esc($f->email) . '">' . esc($f->email) . '</a>' : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="mhs-empty-state">
          <i class="fa-solid fa-truck-ramp-box"></i>
          <p>Sem fornecedores associados a este equipamento.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Assistência Técnica -->
  <div class="mhs-tab-pane" id="tab-assistencia">
    <div class="mhs-tab-body">

      <?php if ($eq && $eq->estado === 'Avariado'): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
        <div><strong>Equipamento avariado.</strong> Contacte a assistência técnica com urgência.</div>
      </div>
      <?php elseif ($eq && $eq->estado === 'Em manutenção'): ?>
      <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
        <i class="fa-solid fa-screwdriver-wrench fa-lg"></i>
        <div><strong>Equipamento em manutenção.</strong></div>
      </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Atalhos -->
        <div class="col-12">
          <div class="d-flex gap-2 flex-wrap">
            <a href="../assistencia-tecnica/lista.php" class="btn btn-outline-primary">
              <i class="fa-solid fa-headset me-1"></i>Ver todos os contactos de AT
            </a>
            <a href="../manutencoes/novo.php?id_equipamento=<?= $id ?>&tipo=Urgência" class="btn btn-danger">
              <i class="fa-solid fa-triangle-exclamation me-1"></i>Pedir assistência de urgência
            </a>
            <a href="../manutencoes/novo.php?id_equipamento=<?= $id ?>&tipo=Preventiva" class="btn btn-outline-secondary">
              <i class="fa-solid fa-calendar-plus me-1"></i>Agendar manutenção preventiva
            </a>
          </div>
        </div>

        <!-- Histórico de manutenções deste equipamento -->
        <div class="col-12">
          <div class="mhs-info-group">
            <div class="mhs-info-group-title d-flex justify-content-between align-items-center">
              <span><i class="fa-solid fa-wrench"></i> Histórico de Manutenções</span>
              <a href="../manutencoes/lista.php" class="btn btn-sm btn-outline-secondary">Ver todas</a>
            </div>
            <?php
            $mans_eq = [];
            if ($id > 0) {
                try {
                    $stmt = mhs_pdo()->prepare("
                        SELECT * FROM manutencoes
                        WHERE id_equipamento = ? AND deleted_at IS NULL
                        ORDER BY data_manutencao DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$id]);
                    $mans_eq = $stmt->fetchAll();
                } catch (PDOException) {}
            }
            ?>
            <?php if (count($mans_eq) > 0): ?>
              <table class="table mhs-datatable mb-0 mt-2">
                <thead>
                  <tr><th>Tipo</th><th>Data</th><th>Próxima</th><th>Estado</th><th>Responsável</th><th></th></tr>
                </thead>
                <tbody>
                  <?php foreach ($mans_eq as $m): ?>
                    <tr>
                      <td>
                        <?= $m->tipo === 'Urgência'
                          ? '<span class="badge bg-danger">Urgência</span>'
                          : '<span class="badge bg-primary">Preventiva</span>' ?>
                      </td>
                      <td><?= $m->data_manutencao ? date('d/m/Y', strtotime($m->data_manutencao)) : '—' ?></td>
                      <td>
                        <?php if ($m->proxima_manutencao):
                          $v = $m->proxima_manutencao < date('Y-m-d') && $m->estado !== 'Concluída'; ?>
                          <span class="<?= $v ? 'text-danger fw-semibold' : '' ?>"><?= date('d/m/Y', strtotime($m->proxima_manutencao)) ?></span>
                        <?php else: ?>—<?php endif; ?>
                      </td>
                      <td>
                        <?php
                        $cls = match($m->estado) {
                            'Concluída' => 'bg-success', 'Em curso' => 'bg-info text-dark',
                            'Planeada'  => 'bg-primary',  default    => 'bg-secondary',
                        };
                        echo "<span class='badge $cls'>{$m->estado}</span>";
                        ?>
                      </td>
                      <td><?= $m->tecnico_responsavel ? esc($m->tecnico_responsavel) : '—' ?></td>
                      <td>
                        <a href="../manutencoes/detalhes.php?id=<?= (int)$m->id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye"></i></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <div class="mhs-empty-state py-4">
                <i class="fa-solid fa-wrench"></i>
                <p>Sem manutenções registadas para este equipamento.</p>
                <a href="../manutencoes/novo.php?id_equipamento=<?= $id ?>" class="btn btn-primary btn-sm">
                  <i class="fa-solid fa-plus me-1"></i>Registar manutenção
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Documentos -->
  <div class="mhs-tab-pane" id="tab-documentos">
    <div class="mhs-tab-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="../documentos/novo.php?id_equipamento=<?= $id ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-plus me-1"></i>Adicionar documento
        </a>
        <?php if (count($documentos) > 0): ?>
          <button class="btn btn-sm btn-outline-secondary" onclick="mhsPrintSection('documentos-print-area')">
            <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
          </button>
        <?php endif; ?>
      </div>

      <div id="documentos-print-area">
        <?php if (count($documentos) > 0): ?>
          <table class="table mhs-datatable mb-0">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Data</th>
                <th>Validade</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentos as $doc): ?>
                <tr>
                  <td class="mhs-td-primary"><?= esc($doc->nome_documento) ?></td>
                  <td><?= esc($doc->tipo_documento) ?></td>
                  <td><?= $doc->data_documento ? date('d/m/Y', strtotime($doc->data_documento)) : '—' ?></td>
                  <td>
                    <?php if ($doc->data_validade): ?>
                      <span class="<?= $doc->data_validade < date('Y-m-d') ? 'text-danger fw-semibold' : '' ?>">
                        <?= date('d/m/Y', strtotime($doc->data_validade)) ?>
                      </span>
                    <?php else: ?>—<?php endif; ?>
                  </td>
                  <td>
                    <?php if ($doc->nome_ficheiro): ?>
                      <a href="../documentos/download.php?id=<?= (int)$doc->id ?>" class="btn btn-sm btn-outline-secondary" title="Descarregar">
                        <i class="fa-solid fa-download"></i>
                      </a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="mhs-empty-state">
            <i class="fa-solid fa-file-circle-xmark"></i>
            <p>Sem documentos associados a este equipamento.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Empréstimos -->
  <div class="mhs-tab-pane" id="tab-emprestimos">
    <div class="mhs-tab-body">
      <?php
      $emprestimos = [];
      if ($id > 0) {
          try {
              $stmt = mhs_pdo()->prepare("
                  SELECT ee.*, l1.servico AS origem, l2.servico AS destino
                  FROM emprestimos_equipamentos ee
                  LEFT JOIN localizacoes l1 ON l1.id = ee.id_localizacao_origem
                  LEFT JOIN localizacoes l2 ON l2.id = ee.id_localizacao_destino
                  WHERE ee.id_equipamento = ? AND ee.deleted_at IS NULL
                  ORDER BY ee.data_emprestimo DESC
              ");
              $stmt->execute([$id]);
              $emprestimos = $stmt->fetchAll();
          } catch (PDOException $e) {}
      }
      ?>
      <?php if (count($emprestimos) > 0): ?>
        <div class="mhs-history-list">
          <?php foreach ($emprestimos as $emp): ?>
            <article class="mhs-history-item">
              <div class="mhs-history-dot"></div>
              <div class="mhs-history-copy">
                <div class="mhs-history-head">
                  <strong>Empréstimo — <?= esc($emp->destino ?? '—') ?></strong>
                  <small><?= $emp->data_emprestimo ? date('d/m/Y', strtotime($emp->data_emprestimo)) : '—' ?></small>
                </div>
                <p>
                  <?= esc($emp->origem ?? '—') ?>
                  <i class="fa-solid fa-arrow-right-long mx-2"></i>
                  <?= esc($emp->destino ?? '—') ?>
                  <?= $emp->data_devolucao ? ' — Devolvido em ' . date('d/m/Y', strtotime($emp->data_devolucao)) : ' — Em curso' ?>
                </p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="mhs-empty-state">
          <i class="fa-solid fa-boxes-packing"></i>
          <p>Sem empréstimos registados para este equipamento.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Movimentações -->
  <div class="mhs-tab-pane" id="tab-movimentacoes">
    <div class="mhs-tab-body">
      <?php
      $movimentacoes = [];
      if ($id > 0) {
          try {
              $stmt = mhs_pdo()->prepare("
                  SELECT em.*, u.email AS utilizador
                  FROM equipamentos_movimentacoes em
                  LEFT JOIN utilizadores u ON u.id = em.id_utilizador
                  WHERE em.id_equipamento = ?
                  ORDER BY em.created_at DESC
              ");
              $stmt->execute([$id]);
              $movimentacoes = $stmt->fetchAll();
          } catch (PDOException $e) {}
      }
      ?>
      <?php if (count($movimentacoes) > 0): ?>
        <div class="mhs-history-list">
          <?php foreach ($movimentacoes as $mov): ?>
            <article class="mhs-history-item">
              <div class="mhs-history-dot"></div>
              <div class="mhs-history-copy">
                <div class="mhs-history-head">
                  <strong><?= esc($mov->tipo_movimentacao ?? $mov->tipo ?? 'Movimentação') ?></strong>
                  <small><?= $mov->created_at ? date('d/m/Y H:i', strtotime($mov->created_at)) : '—' ?></small>
                </div>
                <?php if ($mov->descricao ?? $mov->observacoes ?? null): ?>
                  <p><?= esc($mov->descricao ?? $mov->observacoes) ?></p>
                <?php endif; ?>
                <?php if ($mov->utilizador): ?>
                  <small>Por <?= esc($mov->utilizador) ?></small>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="mhs-empty-state">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <p>Sem movimentações registadas para este equipamento.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- card -->

<!-- Modal de impressão de secção -->
<div id="mhsPrintModal" style="display:none;position:fixed;inset:0;z-index:9999;background:#fff;padding:2rem;overflow:auto"></div>

<script>
document.querySelectorAll('.mhs-detail-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.mhs-detail-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.mhs-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

function mhsPrintSection(sectionId) {
    var content = document.getElementById(sectionId);
    if (!content) return;
    var title = document.querySelector('.mhs-page-title') ? document.querySelector('.mhs-page-title').textContent : 'Equipamento';
    var w = window.open('', '_blank', 'width=900,height=700');
    w.document.write('<html><head><title>' + title + '</title>');
    w.document.write('<link rel="stylesheet" href="/MedSolutions/private/assets/bootstrap/bootstrap.min.css">');
    w.document.write('<style>body{font-family:Inter,sans-serif;padding:2rem;font-size:13px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #e2e8f0;padding:8px 10px;text-align:left}th{background:#f8fafc;font-weight:600}dl{display:grid;grid-template-columns:160px 1fr;gap:4px 8px;margin:0}dt{font-weight:600;color:#64748b}dd{margin:0;color:#1e293b}.mhs-info-group{margin-bottom:1.5rem}.mhs-info-group-title{font-weight:700;margin-bottom:.5rem;padding-bottom:.25rem;border-bottom:2px solid #e2e8f0}h2{font-size:1.1rem;margin-bottom:1rem;color:#1e293b}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>' + title + ' — ' + new Date().toLocaleDateString('pt-PT') + '</h2>');
    w.document.write(content.innerHTML);
    w.document.write('<script>window.onload=function(){window.print();window.close()}<\/script>');
    w.document.write('</body></html>');
    w.document.close();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
