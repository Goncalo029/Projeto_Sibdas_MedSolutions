<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── Processar ações dos formulários da própria ficha (sem ficheiros separados) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $accao = $_POST['accao'] ?? '';
    $tab   = $_POST['tab'] ?? 'ficha';
    try {
        $pdo = mhs_pdo();
        $eqRow = $pdo->prepare("SELECT codigo_inventario, designacao, estado, id_localizacao FROM equipamentos WHERE id = ? AND deleted_at IS NULL");
        $eqRow->execute([$id]);
        $eqRow = $eqRow->fetch();

        if ($eqRow) {
            $user = $_SESSION['user_email'] ?? null;
            $nome_eq = $eqRow->codigo_inventario . ' — ' . $eqRow->designacao;

            // ── Manutenções ──
            if ($accao === 'concluir_manutencao') {
                if (mhs_concluir_manutencao($id)) {
                    $_SESSION['success_message'] = 'Manutenção confirmada. Equipamento marcado como Ativo.';
                } else {
                    $_SESSION['error_message'] = 'Não foi possível confirmar a manutenção.';
                }
            } elseif ($accao === 'registar_manutencao') {
                $tipo = ($_POST['tipo'] ?? '') === 'Urgência' ? 'Urgência' : 'Preventiva';
                $data = trim($_POST['data_manutencao'] ?? '') ?: date('Y-m-d');
                $desc = trim($_POST['descricao'] ?? '') ?: null;
                $em_curso = isset($_POST['em_curso']);
                $pdo->prepare("INSERT INTO manutencoes (id_equipamento, tipo, data_manutencao, estado, tecnico_responsavel, descricao, created_by, created_at) VALUES (?,?,?,?,?,?,?,NOW())")
                    ->execute([$id, $tipo, $data, $em_curso ? 'Em curso' : 'Concluída', $user, $desc, $user]);
                mhs_historico('equipamento', $id, $nome_eq, 'editar', 'Registo de manutenção (' . $tipo . ', ' . ($em_curso ? 'Em curso' : 'Concluída') . ')');
                if ($em_curso && $eqRow->estado !== 'Em manutenção') {
                    $pdo->prepare("UPDATE equipamentos SET estado='Em manutenção', updated_at=NOW() WHERE id=?")->execute([$id]);
                    $pdo->prepare("INSERT INTO equipamentos_movimentacoes (id_equipamento, campo, valor_anterior, valor_novo, alterado_por, created_at) VALUES (?, 'estado', ?, 'Em manutenção', ?, NOW())")->execute([$id, $eqRow->estado, $user]);
                }
                $_SESSION['success_message'] = 'Manutenção registada com sucesso.' . ($em_curso ? ' Equipamento colocado em manutenção.' : '');

            // ── Empréstimos ──
            } elseif ($accao === 'registar_emprestimo') {
                $destino = (int)($_POST['id_localizacao_destino'] ?? 0);
                $saida   = trim($_POST['data_saida'] ?? '') ?: date('Y-m-d');
                $prev    = trim($_POST['data_prevista_devolucao'] ?? '') ?: null;
                $obs     = trim($_POST['observacoes'] ?? '') ?: null;
                if (!$destino) {
                    $_SESSION['error_message'] = 'Indique a localização de destino do empréstimo.';
                } elseif ($prev && strtotime($prev) < strtotime($saida)) {
                    $_SESSION['error_message'] = 'A devolução prevista não pode ser anterior à data de saída.';
                } else {
                    $pdo->prepare("INSERT INTO emprestimos_equipamentos (id_equipamento, id_localizacao_origem, id_localizacao_destino, data_saida, data_prevista_devolucao, estado, observacoes, created_by, created_at, updated_at) VALUES (?,?,?,?,?,'Ativo',?,?,NOW(),NOW())")
                        ->execute([$id, $eqRow->id_localizacao ?: null, $destino, $saida, $prev, $obs, $user]);
                    $dn = $pdo->query("SELECT CONCAT(servico, IF(sala IS NOT NULL AND sala<>'', CONCAT(' / ', sala), '')) FROM localizacoes WHERE id = " . $destino)->fetchColumn();
                    mhs_historico('emprestimo', (int)$pdo->lastInsertId(), 'Empréstimo ' . $eqRow->codigo_inventario . ' → ' . ($dn ?: ('#' . $destino)), 'criar');
                    $_SESSION['success_message'] = 'Empréstimo registado com sucesso.';
                }
            } elseif ($accao === 'emprestimo_terminar' || $accao === 'emprestimo_estender') {
                $eid = (int)($_POST['id_emprestimo'] ?? 0);
                $emp = $pdo->prepare("SELECT * FROM emprestimos_equipamentos WHERE id=? AND id_equipamento=? AND deleted_at IS NULL");
                $emp->execute([$eid, $id]);
                $emp = $emp->fetch();
                if ($emp && $emp->estado === 'Ativo' && !$emp->data_devolucao) {
                    if ($accao === 'emprestimo_terminar') {
                        $pdo->prepare("UPDATE emprestimos_equipamentos SET data_devolucao=CURDATE(), estado='Devolvido', updated_at=NOW(), updated_by=? WHERE id=?")->execute([$user, $eid]);
                        mhs_historico('emprestimo', $eid, 'Empréstimo ' . $eqRow->codigo_inventario, 'editar', 'Terminado/devolvido antecipadamente em ' . date('d/m/Y'));
                        $_SESSION['success_message'] = 'Empréstimo terminado (devolução registada para hoje).';
                    } else {
                        $nova = trim($_POST['nova_data'] ?? '');
                        if (($ts = strtotime($nova)) && $ts >= strtotime($emp->data_saida)) {
                            $pdo->prepare("UPDATE emprestimos_equipamentos SET data_prevista_devolucao=?, updated_at=NOW(), updated_by=? WHERE id=?")->execute([date('Y-m-d', $ts), $user, $eid]);
                            mhs_historico('emprestimo', $eid, 'Empréstimo ' . $eqRow->codigo_inventario, 'editar', 'Devolução prevista alterada para ' . date('d/m/Y', $ts));
                            $_SESSION['success_message'] = 'Prazo de devolução atualizado.';
                        } else {
                            $_SESSION['error_message'] = 'Data de devolução inválida.';
                        }
                    }
                } else {
                    $_SESSION['error_message'] = 'Este empréstimo já não está ativo.';
                }

            // ── Movimentações (deslocação prolongada — NÃO altera a localização oficial) ──
            } elseif ($accao === 'registar_movimentacao') {
                $destino = (int)($_POST['id_localizacao'] ?? 0);
                $obs     = trim($_POST['observacoes'] ?? '');
                $dn = $destino ? $pdo->query("SELECT CONCAT(servico, IF(sala IS NOT NULL AND sala<>'', CONCAT(' / ', sala), '')) FROM localizacoes WHERE id = " . $destino)->fetchColumn() : '';
                $atual = $pdo->query("SELECT CONCAT(l.servico, IF(l.sala IS NOT NULL AND l.sala<>'', CONCAT(' / ', l.sala), '')) FROM localizacoes l WHERE l.id = " . (int)$eqRow->id_localizacao)->fetchColumn();
                if (!$destino || !$dn) {
                    $_SESSION['error_message'] = 'Indique a localização de destino.';
                } else {
                    $valor_novo = $dn . ($obs !== '' ? ' (' . $obs . ')' : '');
                    $pdo->prepare("INSERT INTO equipamentos_movimentacoes (id_equipamento, campo, valor_anterior, valor_novo, alterado_por, created_at) VALUES (?, 'localizacao', ?, ?, ?, NOW())")
                        ->execute([$id, $atual ?: '—', $valor_novo, $user]);
                    mhs_historico('equipamento', $id, $nome_eq, 'editar', 'Movimentação (temporária) para ' . $dn);
                    $_SESSION['success_message'] = 'Movimentação registada.';
                }
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao processar a ação: ' . $e->getMessage();
    }
    header('Location: detalhes.php?id=' . $id . '&tab=' . $tab);
    exit;
}

$eq = null;
$fornecedores = [];
$documentos = [];
$garantia = null;
$proxima_manutencao = null;
$manutencao_info = null;
$contacto_at = null;
$fornecedores_at = [];
$at_contact = null;
$mans_eq = [];

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
                SELECT f.nome, ef.tipo_relacao, f.telefone, f.email,
                       f.pessoa_contacto, f.tel_contacto, f.tipo_fornecedor
                FROM equipamentos_fornecedores ef
                JOIN fornecedores f ON f.id = ef.id_fornecedor
                WHERE ef.id_equipamento = ?
            ");
            $fornecedores->execute([$id]);
            $fornecedores = $fornecedores->fetchAll();

            foreach ($fornecedores as $f) {
                $rel = ($f->tipo_relacao ?? '') . ' ' . ($f->tipo_fornecedor ?? '');
                if (stripos($rel, 'assist') !== false || stripos($rel, 'tecni') !== false) {
                    $fornecedores_at[] = $f;
                    if (!$contacto_at) $contacto_at = $f;
                }
            }

            // Sem fornecedor de AT associado: usar o fornecedor global de assistência técnica
            if (!$contacto_at) {
                $s = $pdo->query("
                    SELECT nome, telefone, email, pessoa_contacto, tel_contacto, tipo_fornecedor
                    FROM fornecedores
                    WHERE tipo_fornecedor LIKE '%assist%' AND deleted_at IS NULL
                    ORDER BY id LIMIT 1
                ");
                $contacto_at = $s ? $s->fetch() : null;
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

            // AT por equipamento
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS equipamento_at (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_equipamento INT NOT NULL,
                    empresa VARCHAR(255) DEFAULT NULL,
                    nome_contacto VARCHAR(255) DEFAULT NULL,
                    email VARCHAR(255) DEFAULT NULL,
                    telefone VARCHAR(50) DEFAULT NULL,
                    telefone_urgencia VARCHAR(50) DEFAULT NULL,
                    observacoes TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT NOW(),
                    updated_at DATETIME DEFAULT NOW(),
                    UNIQUE KEY uq_eq_at (id_equipamento)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $s = $pdo->prepare("SELECT * FROM equipamento_at WHERE id_equipamento = ? LIMIT 1");
                $s->execute([$id]);
                $at_contact = $s->fetch();
            } catch (PDOException) {}

            // Manutenções deste equipamento
            try {
                $s2 = $pdo->prepare("
                    SELECT * FROM manutencoes
                    WHERE id_equipamento = ? AND deleted_at IS NULL
                    ORDER BY data_manutencao DESC
                ");
                $s2->execute([$id]);
                $mans_eq = $s2->fetchAll();
            } catch (PDOException) {}
        }
    } catch (PDOException) {}
}

// Localizações para os formulários de empréstimo / movimentação
$localizacoes_all = [];
try {
    $localizacoes_all = mhs_pdo()->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll();
} catch (PDOException) {}

$page_title = 'Equipamentos - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['success_message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>
<?php if (!empty($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

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
    <button class="mhs-detail-tab" data-tab="manutencoes">
      <i class="fa-solid fa-wrench"></i> Manutenções
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
      <?php endif; ?>

      <?php if ($at_contact && ($at_contact->empresa || $at_contact->nome_contacto || $at_contact->email || $at_contact->telefone)): ?>
      <div class="mhs-at-card">
        <div class="mhs-at-card-header">
          <div>
            <p class="mhs-at-card-label">Assistência Técnica</p>
            <h5 class="mhs-at-card-empresa"><?= $at_contact->empresa ? esc($at_contact->empresa) : '—' ?></h5>
            <?php if ($at_contact->nome_contacto): ?>
            <p class="mhs-at-card-nome"><?= esc($at_contact->nome_contacto) ?></p>
            <?php endif; ?>
          </div>
          <a href="editar.php?id=<?= $id ?>&tab=assistencia" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-pen me-1"></i>Editar
          </a>
        </div>
        <div class="mhs-at-card-contacts">
          <?php if ($at_contact->telefone): ?>
          <a href="tel:<?= esc($at_contact->telefone) ?>" class="mhs-at-contact-item mhs-at-contact-item--tel">
            <span class="mhs-at-contact-icon"><i class="fa-solid fa-phone"></i></span>
            <div>
              <small>Telefone</small>
              <strong><?= esc($at_contact->telefone) ?></strong>
            </div>
          </a>
          <?php endif; ?>
          <?php if ($at_contact->email): ?>
          <a href="mailto:<?= esc($at_contact->email) ?>" class="mhs-at-contact-item">
            <span class="mhs-at-contact-icon"><i class="fa-solid fa-envelope"></i></span>
            <div>
              <small>Email</small>
              <strong><?= esc($at_contact->email) ?></strong>
            </div>
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php elseif ($contacto_at): ?>
      <div class="mhs-at-card">
        <div class="mhs-at-card-header">
          <div>
            <p class="mhs-at-card-label">Assistência Técnica</p>
            <h5 class="mhs-at-card-empresa"><?= esc($contacto_at->nome) ?></h5>
            <?php if (!empty($contacto_at->pessoa_contacto)): ?>
            <p class="mhs-at-card-nome"><?= esc($contacto_at->pessoa_contacto) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="mhs-at-card-contacts">
          <?php if (!empty($contacto_at->telefone)): ?>
          <a href="tel:<?= esc($contacto_at->telefone) ?>" class="mhs-at-contact-item mhs-at-contact-item--tel">
            <span class="mhs-at-contact-icon"><i class="fa-solid fa-phone"></i></span>
            <div>
              <small>Telefone</small>
              <strong><?= esc($contacto_at->telefone) ?></strong>
            </div>
          </a>
          <?php endif; ?>
          <?php if (!empty($contacto_at->tel_contacto)): ?>
          <a href="tel:<?= esc($contacto_at->tel_contacto) ?>" class="mhs-at-contact-item mhs-at-contact-item--tel">
            <span class="mhs-at-contact-icon"><i class="fa-solid fa-phone-volume"></i></span>
            <div>
              <small>Linha direta</small>
              <strong><?= esc($contacto_at->tel_contacto) ?></strong>
            </div>
          </a>
          <?php endif; ?>
          <?php if (!empty($contacto_at->email)): ?>
          <a href="mailto:<?= esc($contacto_at->email) ?>" class="mhs-at-contact-item">
            <span class="mhs-at-contact-icon"><i class="fa-solid fa-envelope"></i></span>
            <div>
              <small>Email</small>
              <strong><?= esc($contacto_at->email) ?></strong>
            </div>
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="mhs-empty-state">
        <i class="fa-solid fa-headset"></i>
        <p>Sem contacto de assistência técnica registado.</p>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Manutenções -->
  <div class="mhs-tab-pane" id="tab-manutencoes">
    <div class="mhs-tab-body">

      <!-- Banner: equipamento em manutenção -> confirmar conclusão -->
      <?php if ($eq && in_array($eq->estado, ['Em manutenção', 'Em calibração', 'Em quarentena'], true)): ?>
      <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <span>
          <i class="fa-solid fa-screwdriver-wrench me-2"></i>
          Este equipamento está <strong><?= esc($eq->estado) ?></strong>. Confirme quando a intervenção estiver concluída.
        </span>
        <form method="post" action="detalhes.php?id=<?= $id ?>" onsubmit="return confirm('Confirmar que a manutenção foi concluída? O equipamento volta a Ativo.');" class="m-0">
          <input type="hidden" name="accao" value="concluir_manutencao">
          <input type="hidden" name="tab" value="manutencoes">
          <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-circle-check me-1"></i>Confirmar manutenção concluída</button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Registar nova manutenção -->
      <details class="mhs-info-group mb-4">
        <summary class="mhs-info-group-title" style="cursor:pointer;list-style:none">
          <i class="fa-solid fa-plus"></i> Registar manutenção
        </summary>
        <form method="post" action="detalhes.php?id=<?= $id ?>" class="row g-3 mt-1">
          <input type="hidden" name="accao" value="registar_manutencao">
          <input type="hidden" name="tab" value="manutencoes">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tipo</label>
            <select name="tipo" class="form-select">
              <option value="Preventiva">Preventiva</option>
              <option value="Urgência">Urgência</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Data</label>
            <input type="date" name="data_manutencao" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Descrição</label>
            <input type="text" name="descricao" class="form-control" placeholder="Breve descrição da intervenção" maxlength="255">
          </div>
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="em_curso" id="em_curso" value="1">
              <label class="form-check-label" for="em_curso">
                Intervenção a decorrer — colocar o equipamento <strong>Em manutenção</strong> (caso contrário fica registada como concluída)
              </label>
            </div>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Registar</button>
          </div>
        </form>
      </details>

      <!-- Próxima manutenção em destaque -->
      <?php
        $proxima_prev = null;
        foreach ($mans_eq as $_m) {
            if ($_m->tipo === 'Preventiva' && $_m->proxima_manutencao) {
                if (!$proxima_prev || $_m->proxima_manutencao > $proxima_prev) {
                    $proxima_prev = $_m->proxima_manutencao;
                }
            }
        }
        $prev_atrasada = $proxima_prev && $proxima_prev < date('Y-m-d');
      ?>
      <div class="mhs-next-maint-banner <?= $prev_atrasada ? 'mhs-next-maint-banner--late' : ($proxima_prev ? 'mhs-next-maint-banner--ok' : 'mhs-next-maint-banner--none') ?> mb-4">
        <span class="mhs-next-maint-icon">
          <i class="fa-regular fa-calendar-check"></i>
        </span>
        <div>
          <p class="mhs-next-maint-label">Próxima manutenção preventiva</p>
          <p class="mhs-next-maint-date">
            <?php if ($proxima_prev): ?>
              <?= date('d \d\e F \d\e Y', strtotime($proxima_prev)) ?>
              <?php if ($prev_atrasada): ?><span class="mhs-next-maint-tag">Em atraso</span><?php endif; ?>
            <?php else: ?>
              Sem data definida
            <?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Calendário Preventivo (semestral) -->
      <div class="mhs-info-group mb-4">
        <div class="mhs-info-group-title d-flex align-items-center justify-content-between gap-2">
          <span><i class="fa-regular fa-calendar"></i> Calendário — periodicidade semestral</span>
          <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="calPrevYear">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span class="fw-bold" id="calYearLabel" style="min-width:46px;text-align:center;font-size:.95rem"></span>
            <button type="button" class="btn btn-sm btn-outline-secondary px-2" id="calNextYear">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <div class="mhs-cal-grid mt-3" id="mhsCalGrid" data-maints="<?= htmlspecialchars(json_encode(array_values(array_map(fn($m) => ['date' => $m->data_manutencao, 'estado' => $m->estado, 'proxima' => $m->proxima_manutencao], array_filter($mans_eq, fn($m) => $m->tipo === 'Preventiva')))), ENT_QUOTES, 'UTF-8') ?>"></div>

        <div class="d-flex gap-4 mt-3 flex-wrap" style="font-size:.78rem;color:#64748b">
          <span class="d-flex align-items-center gap-1"><span class="mhs-cal-legend mhs-cal-legend--done"></span> Concluída</span>
          <span class="d-flex align-items-center gap-1"><span class="mhs-cal-legend mhs-cal-legend--planned"></span> Planeada</span>
          <span class="d-flex align-items-center gap-1"><span class="mhs-cal-legend mhs-cal-legend--overdue"></span> Em atraso</span>
        </div>
      </div>

      <!-- Tabela de registos -->
      <?php
      $preventivas = array_filter($mans_eq, fn($m) => $m->tipo === 'Preventiva');
      $urgencias   = array_filter($mans_eq, fn($m) => $m->tipo !== 'Preventiva');
      ?>

      <?php if (count($mans_eq) > 0): ?>
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-list-check"></i> Histórico de registos</div>
        <table class="table table-sm mhs-datatable mb-0 mt-2">
          <thead>
            <tr><th>Tipo</th><th>Data realizada</th><th>Próxima prevista</th><th>Estado</th><th>Responsável</th></tr>
          </thead>
          <tbody>
            <?php foreach ($mans_eq as $m): ?>
              <tr>
                <td><?= $m->tipo === 'Urgência' ? '<span class="badge bg-danger">Urgência</span>' : '<span class="badge bg-primary">Preventiva</span>' ?></td>
                <td><?= $m->data_manutencao ? date('d/m/Y', strtotime($m->data_manutencao)) : '—' ?></td>
                <td>
                  <?php if ($m->proxima_manutencao):
                    $v = $m->proxima_manutencao < date('Y-m-d') && $m->estado !== 'Concluída'; ?>
                    <span class="<?= $v ? 'text-danger fw-semibold' : '' ?>"><?= date('d/m/Y', strtotime($m->proxima_manutencao)) ?></span>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td><?php
                  $cls = match($m->estado) {
                    'Concluída' => 'bg-success', 'Em curso' => 'bg-info text-dark',
                    'Planeada'  => 'bg-primary',  default    => 'bg-secondary',
                  };
                  echo "<span class='badge $cls'>{$m->estado}</span>"; ?>
                </td>
                <td><?= $m->tecnico_responsavel ? esc($m->tecnico_responsavel) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <div class="mhs-empty-state py-3">
          <i class="fa-solid fa-wrench"></i>
          <p>Sem manutenções registadas. Use os botões acima para adicionar.</p>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Documentos -->
  <div class="mhs-tab-pane" id="tab-documentos">
    <div class="mhs-tab-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="../documentos/novo.php?id_equipamento=<?= $id ?>" class="btn btn-primary btn-sm">
          <i class="fa-solid fa-plus me-1"></i>Adicionar documento
        </a>
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
                  <td class="text-nowrap">
                    <a href="../documentos/download.php?id=<?= (int)$doc->id ?>" class="btn btn-sm btn-outline-danger" title="Descarregar PDF">
                      <i class="fa-solid fa-file-pdf"></i>
                    </a>
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
                  ORDER BY ee.data_saida DESC
              ");
              $stmt->execute([$id]);
              $emprestimos = $stmt->fetchAll();
          } catch (PDOException $e) {}
      }
      ?>

      <!-- Registar empréstimo -->
      <details class="mhs-info-group mb-4">
        <summary class="mhs-info-group-title" style="cursor:pointer;list-style:none">
          <i class="fa-solid fa-plus"></i> Registar empréstimo
        </summary>
        <form method="post" action="detalhes.php?id=<?= $id ?>" class="row g-3 mt-1">
          <input type="hidden" name="accao" value="registar_emprestimo">
          <input type="hidden" name="tab" value="emprestimos">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Destino <span class="text-danger">*</span></label>
            <select name="id_localizacao_destino" class="form-select" required>
              <option value="">-- Selecione o serviço/sala --</option>
              <?php foreach ($localizacoes_all as $loc): ?>
              <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->servico . ($loc->sala ? ' / ' . $loc->sala : '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Data de saída</label>
            <input type="date" name="data_saida" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Devolução prevista</label>
            <input type="date" name="data_prevista_devolucao" class="form-control" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Observações</label>
            <input type="text" name="observacoes" class="form-control" placeholder="Motivo / notas do empréstimo" maxlength="255">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Registar empréstimo</button>
          </div>
        </form>
      </details>

      <?php if (count($emprestimos) > 0): ?>
        <div class="mhs-history-list">
          <?php foreach ($emprestimos as $emp): ?>
            <article class="mhs-history-item">
              <div class="mhs-history-dot"></div>
              <div class="mhs-history-copy">
                <div class="mhs-history-head">
                  <strong>
                    Empréstimo — <?= esc($emp->destino ?? '—') ?>
                    <span class="badge <?= $emp->estado === 'Ativo' ? 'bg-info text-dark' : 'bg-success' ?> ms-1"><?= esc($emp->estado) ?></span>
                  </strong>
                  <small><?= $emp->data_saida ? date('d/m/Y', strtotime($emp->data_saida)) : '—' ?></small>
                </div>
                <p>
                  <?= esc($emp->origem ?? '—') ?>
                  <i class="fa-solid fa-arrow-right-long mx-2"></i>
                  <?= esc($emp->destino ?? '—') ?>
                  <?php if ($emp->data_devolucao): ?>
                    — Devolvido em <?= date('d/m/Y', strtotime($emp->data_devolucao)) ?>
                  <?php elseif ($emp->data_prevista_devolucao): ?>
                    — Devolução prevista a <?= date('d/m/Y', strtotime($emp->data_prevista_devolucao)) ?>
                  <?php else: ?>
                    — Em curso
                  <?php endif; ?>
                </p>
                <?php if ($emp->observacoes): ?>
                  <small><?= esc($emp->observacoes) ?></small>
                <?php endif; ?>

                <?php if ($emp->estado === 'Ativo' && !$emp->data_devolucao): ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  <form method="post" action="detalhes.php?id=<?= $id ?>" onsubmit="return confirm('Terminar este empréstimo agora (devolução antecipada)?');" class="d-inline">
                    <input type="hidden" name="accao" value="emprestimo_terminar">
                    <input type="hidden" name="tab" value="emprestimos">
                    <input type="hidden" name="id_emprestimo" value="<?= (int)$emp->id ?>">
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-rotate-left me-1"></i>Terminar agora</button>
                  </form>
                  <form method="post" action="detalhes.php?id=<?= $id ?>" class="d-inline d-flex align-items-center gap-1">
                    <input type="hidden" name="accao" value="emprestimo_estender">
                    <input type="hidden" name="tab" value="emprestimos">
                    <input type="hidden" name="id_emprestimo" value="<?= (int)$emp->id ?>">
                    <input type="date" name="nova_data" class="form-control form-control-sm" style="width:auto" value="<?= $emp->data_prevista_devolucao ?: date('Y-m-d') ?>" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-calendar-plus me-1"></i>Estender</button>
                  </form>
                </div>
                <?php endif; ?>
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
                  SELECT em.*
                  FROM equipamentos_movimentacoes em
                  WHERE em.id_equipamento = ? AND em.deleted_at IS NULL
                  ORDER BY em.created_at DESC
              ");
              $stmt->execute([$id]);
              $movimentacoes = $stmt->fetchAll();
          } catch (PDOException $e) {}
      }
      ?>

      <!-- Registar movimentação -->
      <details class="mhs-info-group mb-4">
        <summary class="mhs-info-group-title" style="cursor:pointer;list-style:none">
          <i class="fa-solid fa-plus"></i> Registar movimentação
        </summary>
        <form method="post" action="detalhes.php?id=<?= $id ?>" class="row g-3 mt-1">
          <input type="hidden" name="accao" value="registar_movimentacao">
          <input type="hidden" name="tab" value="movimentacoes">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Local de destino <span class="text-danger">*</span></label>
            <select name="id_localizacao" class="form-select" required>
              <option value="">-- Selecione o serviço/sala --</option>
              <?php foreach ($localizacoes_all as $loc): ?>
              <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->servico . ($loc->sala ? ' / ' . $loc->sala : '')) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Motivo / observações</label>
            <input type="text" name="observacoes" class="form-control" placeholder="Ex: cedência prolongada a outro serviço" maxlength="255">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Registar movimentação</button>
          </div>
        </form>
      </details>

      <?php if (count($movimentacoes) > 0): ?>
        <div class="mhs-history-list">
          <?php foreach ($movimentacoes as $mov): ?>
            <article class="mhs-history-item">
              <div class="mhs-history-dot"></div>
              <div class="mhs-history-copy">
                <div class="mhs-history-head">
                  <strong><?php
                    echo match($mov->campo) {
                        'localizacao' => 'Mudança de localização',
                        'estado'      => 'Alteração de estado',
                        default       => 'Alteração de ' . esc($mov->campo),
                    };
                  ?></strong>
                  <small><?= $mov->created_at ? date('d/m/Y H:i', strtotime($mov->created_at)) : '—' ?></small>
                </div>
                <p>
                  <?= esc($mov->valor_anterior ?? '—') ?>
                  <i class="fa-solid fa-arrow-right-long mx-2"></i>
                  <?= esc($mov->valor_novo ?? '—') ?>
                </p>
                <?php if ($mov->alterado_por): ?>
                  <small>Por <?= esc($mov->alterado_por) ?></small>
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


<?php include __DIR__ . '/../../includes/footer.php'; ?>
