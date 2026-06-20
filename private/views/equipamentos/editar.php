<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_inventario = trim($_POST['codigo_inventario'] ?? '');
    $designacao        = trim($_POST['designacao'] ?? '');
    $id_categoria      = (int)($_POST['id_categoria'] ?? 0) ?: null;
    $marca             = trim($_POST['marca'] ?? '') ?: null;
    $modelo            = trim($_POST['modelo'] ?? '') ?: null;
    $numero_serie      = trim($_POST['numero_serie'] ?? '') ?: null;
    $fabricante        = trim($_POST['fabricante'] ?? '') ?: null;
    $data_aquisicao    = trim($_POST['data_aquisicao'] ?? '') ?: null;
    $ano_fabrico       = (int)($_POST['ano_fabrico'] ?? 0) ?: null;
    $custo_aquisicao   = trim($_POST['custo_aquisicao'] ?? '') ?: null;
    $tipo_entrada      = trim($_POST['tipo_entrada'] ?? '') ?: null;
    $id_localizacao    = (int)($_POST['id_localizacao'] ?? 0) ?: null;
    $estado            = trim($_POST['estado'] ?? '') ?: null;
    $criticidade       = trim($_POST['criticidade'] ?? '') ?: null;
    $observacoes       = trim($_POST['observacoes'] ?? '') ?: null;

    if (!$codigo_inventario || !$designacao) {
        $_SESSION['error_message'] = 'Código de Inventário e Designação são obrigatórios.';
        header("Location: editar.php?id=$id"); exit;
    }

    try {
        $pdo = mhs_pdo();

        // Estado anterior (para registar no histórico o que mudou)
        $antes_stmt = $pdo->prepare("SELECT codigo_inventario,designacao,marca,modelo,numero_serie,fabricante,data_aquisicao,ano_fabrico,custo_aquisicao,tipo_entrada,estado,criticidade,observacoes FROM equipamentos WHERE id=?");
        $antes_stmt->execute([$id]);
        $antes = $antes_stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $pdo->prepare("UPDATE equipamentos SET codigo_inventario=?,designacao=?,id_categoria=?,marca=?,modelo=?,numero_serie=?,fabricante=?,data_aquisicao=?,ano_fabrico=?,custo_aquisicao=?,tipo_entrada=?,id_localizacao=?,estado=?,criticidade=?,observacoes=?,atualizado_em=NOW() WHERE id=?")
            ->execute([$codigo_inventario,$designacao,$id_categoria,$marca,$modelo,$numero_serie,$fabricante,$data_aquisicao,$ano_fabrico,$custo_aquisicao,$tipo_entrada,$id_localizacao,$estado,$criticidade,$observacoes,$id]);

        // Histórico de alterações
        $depois = [
            'codigo_inventario' => $codigo_inventario, 'designacao' => $designacao, 'marca' => $marca,
            'modelo' => $modelo, 'numero_serie' => $numero_serie, 'fabricante' => $fabricante,
            'data_aquisicao' => $data_aquisicao, 'ano_fabrico' => $ano_fabrico, 'custo_aquisicao' => $custo_aquisicao,
            'tipo_entrada' => $tipo_entrada, 'estado' => $estado, 'criticidade' => $criticidade, 'observacoes' => $observacoes,
        ];
        $rotulos = [
            'codigo_inventario' => 'Código', 'designacao' => 'Designação', 'marca' => 'Marca', 'modelo' => 'Modelo',
            'numero_serie' => 'Nº Série', 'fabricante' => 'Fabricante', 'data_aquisicao' => 'Data aquisição',
            'ano_fabrico' => 'Ano fabrico', 'custo_aquisicao' => 'Custo', 'tipo_entrada' => 'Tipo entrada',
            'estado' => 'Estado', 'criticidade' => 'Criticidade', 'observacoes' => 'Observações',
        ];
        $detalhe = mhs_diff_campos($antes, $depois, $rotulos);
        mhs_historico('equipamento', $id, $codigo_inventario . ' — ' . $designacao, 'editar', $detalhe);

        // Guardar AT
        $at_empresa = trim($_POST['at_empresa']       ?? '') ?: null;
        $at_nome    = trim($_POST['at_nome_contacto'] ?? '') ?: null;
        $at_email   = trim($_POST['at_email']         ?? '') ?: null;
        $at_tel     = trim($_POST['at_telefone']      ?? '') ?: null;
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
                criado_em DATETIME DEFAULT NOW(),
                atualizado_em DATETIME DEFAULT NOW(),
                UNIQUE KEY uq_eq_at (id_equipamento)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->prepare("
                INSERT INTO equipamento_at (id_equipamento, empresa, nome_contacto, email, telefone, criado_em, atualizado_em)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    empresa       = VALUES(empresa),
                    nome_contacto = VALUES(nome_contacto),
                    email         = VALUES(email),
                    telefone      = VALUES(telefone),
                    atualizado_em    = NOW()
            ")->execute([$id, $at_empresa, $at_nome, $at_email, $at_tel]);
        } catch (PDOException) {}

        // Apagar documentos marcados
        $apagar = $_POST['doc_apagar'] ?? [];
        if (is_array($apagar) && $apagar) {
            $del = $pdo->prepare("DELETE FROM documentos WHERE id = ? AND id_equipamento = ?");
            foreach ($apagar as $docId) {
                $del->execute([(int)$docId, $id]);
                mhs_historico('documento', (int)$docId, 'Documento do equipamento ' . $codigo_inventario, 'apagar');
            }
        }

        // Anexar novos documentos (PDF guardado na base de dados)
        $novos = mhs_ler_pdfs_upload('documentos');
        if ($novos) {
            $doc_tipo = trim($_POST['doc_tipo'] ?? '') ?: 'Outro';
            $ins = $pdo->prepare("INSERT INTO documentos (id_equipamento,tipo_documento,nome_documento,data_documento,nome_ficheiro,ficheiro_conteudo,ficheiro_mime,criado_em) VALUES (?,?,?,?,?,?,?,NOW())");
            foreach ($novos as $d) {
                $nome_doc = pathinfo($d['nome'], PATHINFO_FILENAME) ?: $doc_tipo;
                $ins->bindValue(1, $id, PDO::PARAM_INT);
                $ins->bindValue(2, $doc_tipo);
                $ins->bindValue(3, $nome_doc);
                $ins->bindValue(4, date('Y-m-d'));
                $ins->bindValue(5, $d['nome']);
                $ins->bindValue(6, $d['conteudo'], PDO::PARAM_LOB);
                $ins->bindValue(7, $d['mime']);
                $ins->execute();
                mhs_historico('documento', (int)$pdo->lastInsertId(), $nome_doc, 'criar');
            }
        }

        $_SESSION['success_message'] = 'Equipamento atualizado com sucesso.';
        header('Location: detalhes.php?id=' . $id); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header("Location: editar.php?id=$id"); exit;
    }
}

$pdo = mhs_pdo();
$stmt = $pdo->prepare("SELECT * FROM equipamentos WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { header('Location: lista.php'); exit; }

$categorias   = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
$localizacoes = $pdo->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll();
$estados      = ['Ativo','Em manutenção','Inativo','Em calibração','Em quarentena','Abatido'];
$criticidades = ['Baixa','Média','Alta','Suporte de vida'];
$tipos_doc    = ['Manual','Certificado','Contrato','Relatório','Ficha técnica','Outro'];
$documentos_eq = $pdo->query("SELECT id, tipo_documento, nome_documento, nome_ficheiro, (ficheiro_conteudo IS NOT NULL) AS tem_ficheiro FROM documentos WHERE id_equipamento = " . (int)$id . " AND eliminado_em IS NULL ORDER BY id")->fetchAll();
$tipos_entrada = ['Compra','Doação','Aluguer','Empréstimo'];

// Carregar AT
$at = null;
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
        criado_em DATETIME DEFAULT NOW(),
        atualizado_em DATETIME DEFAULT NOW(),
        UNIQUE KEY uq_eq_at (id_equipamento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $s = $pdo->prepare("SELECT * FROM equipamento_at WHERE id_equipamento = ? LIMIT 1");
    $s->execute([$id]);
    $at = $s->fetch();
} catch (PDOException) {}

// Valores a mostrar nos campos de AT: usa o contacto próprio do equipamento;
// se não existir, mostra o do fornecedor de assistência técnica (igual aos detalhes).
$at_vals = ['empresa' => '', 'nome_contacto' => '', 'telefone' => '', 'email' => ''];
if ($at) {
    $at_vals = ['empresa' => $at->empresa, 'nome_contacto' => $at->nome_contacto, 'telefone' => $at->telefone, 'email' => $at->email];
} else {
    try {
        $sf = $pdo->prepare("
            SELECT f.nome, f.pessoa_contacto, f.telefone, f.tel_contacto, f.email
            FROM equipamentos_fornecedores ef
            JOIN fornecedores f ON f.id = ef.id_fornecedor
            WHERE ef.id_equipamento = ? AND f.eliminado_em IS NULL
              AND (ef.tipo_relacao LIKE '%assist%' OR ef.tipo_relacao LIKE '%tecni%'
                   OR f.tipo_fornecedor LIKE '%assist%' OR f.tipo_fornecedor LIKE '%tecni%')
            LIMIT 1
        ");
        $sf->execute([$id]);
        $fb = $sf->fetch();
        if (!$fb) {
            $fb = $pdo->query("SELECT nome, pessoa_contacto, telefone, tel_contacto, email FROM fornecedores WHERE tipo_fornecedor LIKE '%assist%' AND eliminado_em IS NULL ORDER BY id LIMIT 1")->fetch();
        }
        if ($fb) {
            $at_vals = ['empresa' => $fb->nome, 'nome_contacto' => $fb->pessoa_contacto, 'telefone' => $fb->telefone ?: $fb->tel_contacto, 'email' => $fb->email];
        }
    } catch (PDOException) {}
}

$page_title = 'Equipamentos - Editar';
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
    <span class="mhs-page-kicker"><i class="fa-solid fa-pen fa-fw"></i></span>
    <h1 class="mhs-page-title">Editar Equipamento</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="detalhes.php?id=<?= $id ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<!-- Resumo -->
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Código</span>
      <span class="mhs-code"><?= esc($row->codigo_inventario) ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Designação</span>
      <span class="mhs-detail-summary-val"><?= esc($row->designacao) ?></span>
    </div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item">
      <span class="mhs-detail-summary-label">Estado actual</span>
      <span class="mhs-detail-summary-val mhs-detail-summary-val--ok"><?= esc($row->estado ?? '—') ?></span>
    </div>
  </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $row->id ?>">

<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button type="button" class="mhs-detail-tab active" data-tab="identificacao">
      <i class="fa-solid fa-barcode"></i> Identificação
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="aquisicao">
      <i class="fa-solid fa-receipt"></i> Aquisição
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="localizacao">
      <i class="fa-solid fa-location-dot"></i> Localização e Estado
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="assistencia">
      <i class="fa-solid fa-headset"></i> Assistência Técnica
    </button>
    <button type="button" class="mhs-detail-tab" data-tab="documentos">
      <i class="fa-solid fa-file-lines"></i> Documentos
    </button>
  </div>

  <!-- Identificação -->
  <div class="mhs-tab-pane active" id="tab-identificacao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-barcode"></i> Dados de identificação</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Código de inventário <span class="text-danger">*</span></label>
            <input type="text" name="codigo_inventario" class="form-control" value="<?= esc($row->codigo_inventario) ?>" required maxlength="50" />
          </div>
          <div class="col-md-8">
            <label class="form-label">Designação <span class="text-danger">*</span></label>
            <input type="text" name="designacao" class="form-control" value="<?= esc($row->designacao) ?>" required maxlength="200" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Categoria</label>
            <select name="id_categoria" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat->id ?>" <?= $row->id_categoria == $cat->id ? 'selected' : '' ?>><?= esc($cat->nome) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Marca</label>
            <input type="text" name="marca" class="form-control" value="<?= esc($row->marca ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-4">
            <label class="form-label">Modelo</label>
            <input type="text" name="modelo" class="form-control" value="<?= esc($row->modelo ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Número de série</label>
            <input type="text" name="numero_serie" class="form-control" value="<?= esc($row->numero_serie ?? '') ?>" maxlength="100" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Fabricante</label>
            <input type="text" name="fabricante" class="form-control" value="<?= esc($row->fabricante ?? '') ?>" maxlength="150" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Aquisição -->
  <div class="mhs-tab-pane" id="tab-aquisicao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-receipt"></i> Dados de aquisição</div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Data de aquisição</label>
            <input type="text" name="data_aquisicao" class="form-control mhs-datepicker" value="<?= esc($row->data_aquisicao ?? '') ?>" placeholder="AAAA-MM-DD" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Ano de fabrico</label>
            <input type="number" name="ano_fabrico" class="form-control" value="<?= $row->ano_fabrico ?? '' ?>" min="1900" max="2099" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Custo de aquisição (€)</label>
            <input type="text" name="custo_aquisicao" class="form-control" value="<?= esc($row->custo_aquisicao ?? '') ?>" />
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo de entrada</label>
            <select name="tipo_entrada" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($tipos_entrada as $t): ?>
              <option <?= ($row->tipo_entrada ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Localização e Estado -->
  <div class="mhs-tab-pane" id="tab-localizacao">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-location-dot"></i> Localização e estado operacional</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Localização</label>
            <select name="id_localizacao" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($localizacoes as $loc): ?>
              <option value="<?= $loc->id ?>" <?= $row->id_localizacao == $loc->id ? 'selected' : '' ?>>
                <?= esc($loc->servico . ($loc->sala ? ' / '.$loc->sala : '')) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($estados as $e): ?>
              <option <?= ($row->estado ?? '') === $e ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Criticidade</label>
            <select name="criticidade" class="form-select">
              <option value="">— Selecione —</option>
              <?php foreach ($criticidades as $c): ?>
              <option <?= ($row->criticidade ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Observações</label>
            <textarea name="observacoes" class="form-control" rows="3"><?= esc($row->observacoes ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Assistência Técnica -->
  <div class="mhs-tab-pane" id="tab-assistencia">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-headset"></i> Contacto de assistência técnica</div>
        <div class="row g-3">
          <?php if (!$at): ?>
          <div class="col-12">
            <div class="alert alert-info py-2 mb-0"><i class="fa-solid fa-circle-info me-1"></i>Contacto pré-preenchido a partir do fornecedor de assistência técnica. Pode editá-lo para definir um contacto próprio deste equipamento.</div>
          </div>
          <?php endif; ?>
          <div class="col-md-6">
            <label class="form-label">Empresa / Marca</label>
            <input type="text" name="at_empresa" class="form-control"
              value="<?= esc($at_vals['empresa']) ?>"
              placeholder="Ex: MedTech SA" maxlength="255" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Nome do contacto</label>
            <input type="text" name="at_nome_contacto" class="form-control"
              value="<?= esc($at_vals['nome_contacto']) ?>"
              placeholder="Ex: João Silva" maxlength="255" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
              <input type="text" name="at_telefone" class="form-control"
                value="<?= esc($at_vals['telefone']) ?>"
                placeholder="222 XXX XXX" maxlength="50" />
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
              <input type="email" name="at_email" class="form-control"
                value="<?= esc($at_vals['email']) ?>"
                placeholder="assistencia@empresa.pt" maxlength="255" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Documentos -->
  <div class="mhs-tab-pane" id="tab-documentos">
    <div class="mhs-tab-body">
      <div class="mhs-form-section">
        <div class="mhs-form-section-title"><i class="fa-solid fa-file-lines"></i> Documentos associados</div>
        <?php if ($documentos_eq): ?>
        <table class="table table-sm align-middle mb-3">
          <thead><tr><th>Nome</th><th>Tipo</th><th>Ficheiro</th><th class="text-end">Apagar</th></tr></thead>
          <tbody>
            <?php foreach ($documentos_eq as $doc): ?>
            <tr>
              <td><?= esc($doc->nome_documento ?: '—') ?></td>
              <td><?= esc($doc->tipo_documento ?: '—') ?></td>
              <td>
                <?php if ($doc->tem_ficheiro): ?>
                  <a href="../documentos/download.php?id=<?= (int)$doc->id ?>" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
                <?php else: ?><span class="text-muted small">sem ficheiro</span><?php endif; ?>
              </td>
              <td class="text-end">
                <input type="checkbox" name="doc_apagar[]" value="<?= (int)$doc->id ?>" class="form-check-input" title="Marcar para apagar">
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="text-muted small">Marca a coluna "Apagar" e grava para remover documentos.</p>
        <?php else: ?>
        <p class="mhs-info-obs mb-3">Este equipamento ainda não tem documentos.</p>
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Tipo do novo documento</label>
            <select name="doc_tipo" class="form-select">
              <?php foreach ($tipos_doc as $t): ?><option><?= $t ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Anexar PDF(s)</label>
            <input type="file" name="documentos[]" class="form-control" accept="application/pdf,.pdf" multiple />
            <div class="form-text">Os ficheiros ficam guardados na base de dados.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div><!-- card -->

<div class="mhs-form-actions">
  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar alterações</button>
  <a href="detalhes.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancelar</a>
</div>

</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
