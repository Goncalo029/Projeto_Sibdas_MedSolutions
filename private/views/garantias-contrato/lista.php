<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// ── Descarregar o ficheiro PDF importado (da base de dados) ──
if (isset($_GET['ficheiro'])) {
    $gid = (int)$_GET['ficheiro'];
    $stmt = mhs_pdo()->prepare("SELECT nome_ficheiro, ficheiro_conteudo, ficheiro_mime FROM garantias_contratos WHERE id = ? AND eliminado_em IS NULL");
    $stmt->execute([$gid]);
    $g = $stmt->fetch();
    if ($g && $g->ficheiro_conteudo !== null) {
        header('Content-Type: ' . ($g->ficheiro_mime ?: 'application/pdf'));
        header('Content-Disposition: attachment; filename="' . ($g->nome_ficheiro ?: 'contrato_' . $gid . '.pdf') . '"');
        header('Content-Length: ' . strlen($g->ficheiro_conteudo));
        echo $g->ficheiro_conteudo;
        exit;
    }
    $_SESSION['error_message'] = 'Ficheiro não encontrado.';
    header('Location: lista.php'); exit;
}

// ── Exportar PDF (relatório gerado da garantia/contrato) ──
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && isset($_GET['id'])) {
    $stmt = mhs_pdo()->prepare("
        SELECT g.*, e.codigo_inventario, e.designacao, e.marca, e.modelo
        FROM garantias_contratos g
        JOIN equipamentos e ON e.id = g.id_equipamento
        WHERE g.id = ? AND g.eliminado_em IS NULL
    ");
    $stmt->execute([(int)$_GET['id']]);
    $g = $stmt->fetch();
    if (!$g) { header('Location: lista.php'); exit; }

    $fmt  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
    $encf = function (string $s): string {
        $s = iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s ?: '');
    };
    $W = 595.28; $H = 841.89; $M = 48; $y = $H - $M;
    $c  = "0.114 0.161 0.302 rg $M " . ($y - 44) . " " . ($W - 2 * $M) . " 44 re f 1 1 1 rg\n";
    $c .= "BT /F2 17 Tf " . ($M + 14) . " " . ($y - 28) . " Td (" . $encf('Garantia / Contrato') . ") Tj ET\n";
    $c .= "BT /F1 10 Tf " . ($M + 14) . " " . ($y - 44 + 12) . " Td (" . $encf('MedSolutions - Gestao de Equipamentos Medicos') . ") Tj ET\n0 0 0 rg\n";
    $y -= 70;
    $linha = function (string $lbl, string $val) use (&$c, &$y, $M, $encf) {
        $c .= "BT /F2 9 Tf $M $y Td (" . $encf($lbl) . ") Tj ET\n";
        $c .= "BT /F1 11 Tf " . ($M + 160) . " $y Td (" . $encf($val !== '' ? $val : '—') . ") Tj ET\n";
        $y -= 22;
    };
    $linha('Equipamento', $g->codigo_inventario . ' — ' . $g->designacao);
    $linha('Marca / Modelo', trim(($g->marca ?? '') . ' ' . ($g->modelo ?? '')));
    $y -= 6;
    $linha('Data de inicio', $fmt($g->data_inicio));
    $linha('Data de fim', $fmt($g->data_fim));
    $linha('Tem contrato', $g->tem_contrato ? 'Sim' : 'Nao');
    $linha('Tipo de contrato', (string)($g->tipo_contrato ?? ''));
    $linha('Entidade responsavel', (string)($g->entidade_responsavel ?? ''));
    $linha('Periodicidade', (string)($g->periodicidade ?? ''));
    if (!empty($g->observacoes)) { $y -= 6; $linha('Observacoes', mb_substr($g->observacoes, 0, 80, 'UTF-8')); }
    $c .= "BT /F1 8 Tf $M " . ($M - 12) . " Td (" . $encf('Gerado em ' . date('d/m/Y H:i')) . ") Tj ET\n";

    $objs = [];
    $objs[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objs[2] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objs[3] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $W $H] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>";
    $objs[4] = "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "endstream";
    $objs[5] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $objs[6] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
    $pdf = "%PDF-1.4\n"; $off = [];
    foreach ($objs as $num => $body) { $off[$num] = strlen($pdf); $pdf .= "$num 0 obj\n$body\nendobj\n"; }
    $xref = strlen($pdf); $cnt = count($objs) + 1;
    $pdf .= "xref\n0 $cnt\n0000000000 65535 f \n";
    for ($i = 1; $i < $cnt; $i++) { $pdf .= sprintf("%010d 00000 n \n", $off[$i]); }
    $pdf .= "trailer\n<< /Size $cnt /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="garantia_' . $g->codigo_inventario . '_' . $g->id . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

$page_title = 'Garantias-Contrato - Lista';
$garantias = [];
$erro_bd = '';

$f_filtro = trim($_GET['filtro'] ?? '');
$gwhere = "g.eliminado_em IS NULL";
if ($f_filtro === 'expiradas')   { $gwhere .= " AND g.data_fim < CURDATE()"; }
elseif ($f_filtro === 'vigor')   { $gwhere .= " AND g.data_fim >= CURDATE()"; }

$filtro_label = match ($f_filtro) {
    'expiradas' => 'Garantias/contratos expirados',
    'vigor'     => 'Garantias/contratos em vigor',
    default     => '',
};

try {
    $garantias = mhs_pdo()->query("
        SELECT g.id, g.data_inicio, g.data_fim, g.tem_contrato, g.tipo_contrato, g.entidade_responsavel, g.nome_ficheiro,
               e.codigo_inventario, e.designacao
        FROM garantias_contratos g
        JOIN equipamentos e ON e.id = g.id_equipamento
        WHERE $gwhere
        ORDER BY g.data_fim ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar garantias e contratos.';
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span>
    <h1 class="mhs-page-title">Garantias-Contrato</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<?php if ($filtro_label !== '') : ?>
<div class="alert alert-info d-flex align-items-center justify-content-between mb-3">
  <span><i class="fa-solid fa-filter me-2"></i>Filtro ativo: <strong><?= esc($filtro_label) ?></strong> — <?= count($garantias) ?> resultado<?= count($garantias) !== 1 ? 's' : '' ?></span>
  <a href="lista.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Limpar filtro</a>
</div>
<?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-shield-halved mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Garantias e Contratos</span>
      <span class="mhs-table-toolbar-count"><?= count($garantias) ?> registos</span>
    </div>
    <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Nova Garantia</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="garantiasTable">
        <thead class="mhs-thead">
          <tr><th>Equipamento</th><th>Inicio</th><th>Fim</th><th>Contrato</th><th>Tipo</th><th>Entidade</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($garantias as $garantia) : ?>
            <tr>
              <td><?= esc($garantia->codigo_inventario . ' - ' . $garantia->designacao) ?></td>
              <td><?= $garantia->data_inicio ? esc(date('d/m/Y', strtotime($garantia->data_inicio))) : '' ?></td>
              <td><?= $garantia->data_fim ? esc(date('d/m/Y', strtotime($garantia->data_fim))) : '' ?></td>
              <td><?= $garantia->tem_contrato ? 'Sim' : 'Nao' ?></td>
              <td><?= esc($garantia->tipo_contrato) ?></td>
              <td><?= esc($garantia->entidade_responsavel) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <a href="?export=pdf&id=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-dark" title="Exportar PDF"><i class="fa-solid fa-file-arrow-down"></i></a>
                  <?php if (!empty($garantia->nome_ficheiro)): ?>
                  <a href="?ficheiro=<?= (int) $garantia->id ?>" class="btn btn-sm btn-outline-danger" title="Descarregar documento importado"><i class="fa-solid fa-file-pdf"></i></a>
                  <?php endif; ?>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $garantia->id ?>" data-delete-name="<?= esc($garantia->codigo_inventario) ?>"><i class="fa-solid fa-trash"></i></button>
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
