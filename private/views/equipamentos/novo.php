<?php
/**
 * Novo equipamento
 * Formulário para registar um equipamento novo no sistema.
 * Inclui dados gerais, aquisição, localização, assistência técnica e documentos.
 * Os campos de marca, modelo e fabricante têm sugestões automáticas da base de dados.
 */

require_once __DIR__ . '/../../includes/funcoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

// ─── Processar o formulário quando é submetido ────────────────────────────────
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
    $estado            = trim($_POST['estado'] ?? '') ?: 'Ativo';
    $criticidade       = trim($_POST['criticidade'] ?? '') ?: null;
    $observacoes       = trim($_POST['observacoes'] ?? '') ?: null;

    if (!$codigo_inventario || !$designacao) {
        $_SESSION['error_message'] = 'Código de Inventário e Designação são obrigatórios.';
        header('Location: novo.php'); exit;
    }
    if (!preg_match('/^EQ-\d{3,}$/', $codigo_inventario)) {
        $_SESSION['error_message'] = 'O código de inventário deve seguir o formato EQ-001, EQ-002, etc.';
        header('Location: novo.php'); exit;
    }
    if ($numero_serie && !preg_match('/^[A-Z0-9]{2,}-\d{4}-\d{3,}$/', $numero_serie)) {
        $_SESSION['error_message'] = 'O número de série deve seguir o formato ex: SN-2024-001 (PREFIXO-ANO-SEQUÊNCIA).';
        header('Location: novo.php'); exit;
    }
    if ($data_aquisicao && $data_aquisicao > date('Y-m-d')) {
        $_SESSION['error_message'] = 'A data de aquisição não pode ser uma data futura.';
        header('Location: novo.php'); exit;
    }
    if ($ano_fabrico && $ano_fabrico > (int)date('Y')) {
        $_SESSION['error_message'] = 'O ano de fabrico não pode ser futuro (máx. ' . date('Y') . ').';
        header('Location: novo.php'); exit;
    }
    if ($custo_aquisicao && !preg_match('/^\d+([.,]\d{1,4})?$/', $custo_aquisicao)) {
        $_SESSION['error_message'] = 'O custo de aquisição deve ser um valor numérico (ex: 1500.00).';
        header('Location: novo.php'); exit;
    }
    try {
        $pdo = mhs_pdo();
        // Verificar código duplicado
        $chk = $pdo->prepare("SELECT id FROM equipamentos WHERE codigo_inventario = ? AND eliminado_em IS NULL");
        $chk->execute([$codigo_inventario]);
        if ($chk->fetch()) {
            $_SESSION['error_message'] = 'Já existe um equipamento com o código "' . htmlspecialchars($codigo_inventario) . '".';
            header('Location: novo.php'); exit;
        }
        $pdo->prepare("INSERT INTO equipamentos (codigo_inventario,designacao,id_categoria,marca,modelo,numero_serie,fabricante,data_aquisicao,ano_fabrico,custo_aquisicao,tipo_entrada,id_localizacao,estado,criticidade,observacoes,criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())")
            ->execute([$codigo_inventario,$designacao,$id_categoria,$marca,$modelo,$numero_serie,$fabricante,$data_aquisicao,$ano_fabrico,$custo_aquisicao,$tipo_entrada,$id_localizacao,$estado,$criticidade,$observacoes]);

        $new_id = (int)$pdo->lastInsertId();

        mhs_historico('equipamento', $new_id, $codigo_inventario . ' — ' . $designacao, 'criar');

        // Guardar AT se preenchido
        $at_empresa = trim($_POST['at_empresa']       ?? '') ?: null;
        $at_nome    = trim($_POST['at_nome_contacto'] ?? '') ?: null;
        $at_email   = trim($_POST['at_email']         ?? '') ?: null;
        $at_tel     = trim($_POST['at_telefone']      ?? '') ?: null;
        if ($new_id && ($at_empresa || $at_nome || $at_email || $at_tel)) {
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
                ")->execute([$new_id, $at_empresa, $at_nome, $at_email, $at_tel]);
            } catch (PDOException) {}
        }

        // Guardar documentos anexados (PDF na base de dados) e associá-los ao equipamento
        $docs = mhs_ler_pdfs_upload('documentos');
        if ($docs) {
            $doc_tipo = trim($_POST['doc_tipo'] ?? '') ?: 'Outro';
            $ins = $pdo->prepare("INSERT INTO documentos (id_equipamento,tipo_documento,nome_documento,data_documento,nome_ficheiro,ficheiro_conteudo,ficheiro_mime,criado_em) VALUES (?,?,?,?,?,?,?,NOW())");
            foreach ($docs as $d) {
                $nome_doc = pathinfo($d['nome'], PATHINFO_FILENAME) ?: $doc_tipo;
                $ins->bindValue(1, $new_id, PDO::PARAM_INT);
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

        $_SESSION['success_message'] = 'Equipamento criado com sucesso.'
            . ($docs ? ' (' . count($docs) . ' documento(s) anexado(s))' : '');
        header('Location: lista.php'); exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erro ao guardar: ' . $e->getMessage();
        header('Location: novo.php'); exit;
    }
}

$pdo = mhs_pdo();
$categorias   = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll();
$localizacoes = $pdo->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll();
// Próximo código disponível no formato EQ-XXX
$_nr = $pdo->query("SELECT MAX(CAST(REGEXP_REPLACE(codigo_inventario,'[^0-9]','') AS UNSIGNED)) AS mx FROM equipamentos WHERE eliminado_em IS NULL AND codigo_inventario REGEXP '^EQ-[0-9]'")->fetchColumn();
$next_code = 'EQ-' . str_pad((int)$_nr + 1, 3, '0', STR_PAD_LEFT);
// Índice para auto-preenchimento determinístico (0-based)
$_eq_idx     = (int)$_nr;
$_loc_ids    = array_values(array_column($localizacoes, 'id'));

$marcas      = $pdo->query("SELECT DISTINCT marca FROM equipamentos WHERE marca IS NOT NULL AND marca != '' AND eliminado_em IS NULL ORDER BY marca")->fetchAll(PDO::FETCH_COLUMN);
$modelos     = $pdo->query("SELECT DISTINCT modelo FROM equipamentos WHERE modelo IS NOT NULL AND modelo != '' AND eliminado_em IS NULL ORDER BY modelo")->fetchAll(PDO::FETCH_COLUMN);
$fabricantes = $pdo->query("SELECT nome FROM fornecedores WHERE eliminado_em IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_COLUMN);

$estados      = ['Ativo','Em manutenção','Inativo','Em calibração','Em quarentena','Abatido'];
$criticidades = ['Baixa','Média','Alta','Suporte de vida'];
$tipos_entrada = ['Compra','Doação','Aluguer','Empréstimo'];
$tipos_doc    = ['Manual','Certificado','Contrato','Ficha técnica','Outro'];

$page_title = 'Equipamentos - Novo';
include __DIR__ . '/../../includes/header.php';
?>

<?php if (!empty($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['error_message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-plus fa-fw"></i></span>
    <h1 class="mhs-page-title">Novo Equipamento</h1>
  </div>
  <div class="mhs-page-actions">
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
  <div class="card mhs-data-card">
    <div class="mhs-detail-tabs">
      <button type="button" class="mhs-detail-tab active" data-tab="ficha"><i class="fa-solid fa-barcode"></i> Ficha Técnica</button>
      <button type="button" class="mhs-detail-tab" data-tab="aquisicao"><i class="fa-solid fa-receipt"></i> Aquisição</button>
      <button type="button" class="mhs-detail-tab" data-tab="localizacao"><i class="fa-solid fa-location-dot"></i> Localização e Estado</button>
      <button type="button" class="mhs-detail-tab" data-tab="assistencia"><i class="fa-solid fa-headset"></i> Assistência Técnica</button>
      <button type="button" class="mhs-detail-tab" data-tab="documentos"><i class="fa-solid fa-file-lines"></i> Documentos</button>
    </div>

    <!-- Ficha Técnica -->
    <div class="mhs-tab-pane active" id="tab-ficha">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-barcode"></i> Identificação</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mhsAutoFillDemo('<?= htmlspecialchars($next_code, ENT_QUOTES) ?>',<?= $_eq_idx ?>,<?= json_encode($_loc_ids) ?>)" title="Preencher com dados de exemplo">
              <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Auto-preencher (demo)
            </button>
          </div>
          <div class="row g-3 mt-1">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Código Inventário <span class="text-danger">*</span></label>
              <input type="text" name="codigo_inventario" id="codigo_inventario" class="form-control" placeholder="EQ-001" required maxlength="50" value="<?= htmlspecialchars($_POST['codigo_inventario'] ?? '') ?>" pattern="EQ-\d{3,}" title="Formato: EQ-001, EQ-002, ..." />
              <p class="mhs-field-error" id="err_codigo_inventario">Campo obrigatório.</p>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Designação <span class="text-danger">*</span></label>
              <input type="text" name="designacao" id="designacao" class="form-control" placeholder="Nome do equipamento" required maxlength="200" value="<?= htmlspecialchars($_POST['designacao'] ?? '') ?>" />
              <p class="mhs-field-error" id="err_designacao">Campo obrigatório.</p>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Categoria</label>
              <select name="id_categoria" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nome) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Marca</label>
              <input type="text" name="marca" class="form-control" placeholder="Marca" maxlength="100" list="dl-marcas" autocomplete="off" />
              <datalist id="dl-marcas">
                <?php foreach ($marcas as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?>
              </datalist>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Modelo</label>
              <input type="text" name="modelo" class="form-control" placeholder="Modelo" maxlength="100" list="dl-modelos" autocomplete="off" />
              <datalist id="dl-modelos">
                <?php foreach ($modelos as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?>
              </datalist>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Número de Série</label>
              <input type="text" name="numero_serie" id="numero_serie" class="form-control" placeholder="Ex: SN-2024-001" maxlength="100" />
              <p class="mhs-field-error" id="err_numero_serie">Formato inválido. Use ex: SN-2024-001</p>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fabricante</label>
              <input type="text" name="fabricante" class="form-control" placeholder="Fabricante" maxlength="150" list="dl-fabricantes" autocomplete="off" />
              <datalist id="dl-fabricantes">
                <?php foreach ($fabricantes as $f): ?><option value="<?= htmlspecialchars($f) ?>"><?php endforeach; ?>
              </datalist>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Aquisição -->
    <div class="mhs-tab-pane" id="tab-aquisicao">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-receipt"></i> Dados de Aquisição</div>
          <div class="row g-3 mt-1">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Data Aquisição</label>
              <input type="text" name="data_aquisicao" id="data_aquisicao" class="form-control mhs-datepicker" data-maxdate="today" placeholder="AAAA-MM-DD" />
              <p class="mhs-field-error" id="err_data_aquisicao">A data não pode ser futura.</p>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Ano Fabrico</label>
              <input type="number" name="ano_fabrico" id="ano_fabrico" class="form-control" min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>" />
              <p class="mhs-field-error" id="err_ano_fabrico">O ano não pode ser futuro (máx. <?= date('Y') ?>).</p>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Custo Aquisição (€)</label>
              <input type="text" name="custo_aquisicao" id="custo_aquisicao" class="form-control" placeholder="0.00" />
              <p class="mhs-field-error" id="err_custo_aquisicao">Apenas números são aceites (ex: 1500.00).</p>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Tipo de Entrada</label>
              <select name="tipo_entrada" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach ($tipos_entrada as $t): ?>
                <option><?= $t ?></option>
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
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-location-dot"></i> Localização e Estado</div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Localização</label>
              <select name="id_localizacao" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach ($localizacoes as $loc): ?>
                <option value="<?= $loc->id ?>"><?= htmlspecialchars($loc->servico . ($loc->sala ? ' / ' . $loc->sala : '')) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Estado</label>
              <select name="estado" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach ($estados as $e): ?>
                <option><?= $e ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Criticidade</label>
              <select name="criticidade" class="form-select">
                <option value="">-- Selecione --</option>
                <?php foreach ($criticidades as $c): ?>
                <option><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Observações</label>
              <textarea name="observacoes" class="form-control" rows="3" placeholder="Notas sobre o equipamento"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assistência Técnica -->
    <div class="mhs-tab-pane" id="tab-assistencia">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-headset"></i> Contacto de assistência técnica <small class="fw-normal text-muted">(opcional)</small></div>
          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Empresa / Marca</label>
              <input type="text" name="at_empresa" class="form-control" placeholder="Ex: MedTech SA" maxlength="255" value="<?= htmlspecialchars($_POST['at_empresa'] ?? '') ?>" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nome do contacto</label>
              <input type="text" name="at_nome_contacto" class="form-control" placeholder="Ex: João Silva" maxlength="255" value="<?= htmlspecialchars($_POST['at_nome_contacto'] ?? '') ?>" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Telefone</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input type="text" name="at_telefone" class="form-control" placeholder="222 XXX XXX" maxlength="50" value="<?= htmlspecialchars($_POST['at_telefone'] ?? '') ?>" />
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="at_email" class="form-control" placeholder="assistencia@empresa.pt" maxlength="255" value="<?= htmlspecialchars($_POST['at_email'] ?? '') ?>" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Documentos -->
    <div class="mhs-tab-pane" id="tab-documentos">
      <div class="mhs-tab-body">
        <div class="mhs-info-group">
          <div class="mhs-info-group-title"><i class="fa-solid fa-file-lines"></i> Documentação <small class="fw-normal text-muted">(opcional)</small></div>
          <div class="row g-3 mt-1">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tipo de documento</label>
              <select name="doc_tipo" class="form-select">
                <?php foreach ($tipos_doc as $t): ?>
                <option><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Ficheiros PDF</label>
              <input type="file" name="documentos[]" class="form-control" accept="application/pdf,.pdf" multiple />
              <div class="form-text">Pode anexar um ou mais PDF (máx. 10 MB cada). Ficam associados a este equipamento.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 my-4">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar Equipamento</button>
    <a href="lista.php" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
