<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$stmt = mhs_pdo()->prepare("
    SELECT g.*, e.codigo_inventario, e.designacao
    FROM garantias_contratos g
    JOIN equipamentos e ON e.id = g.id_equipamento
    WHERE g.id = ? AND g.eliminado_em IS NULL
");
$stmt->execute([$id]);
$g = $stmt->fetch();
if (!$g) { header('Location: lista.php'); exit; }

$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

// ── Export PDF ─────────────────────────────────────────────
if (($_GET['export'] ?? '') === 'pdf') {
    $W = 595.28; $H = 841.89; $M = 40;
    $encf = function (string $s): string {
        $s = iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $s ?: '');
    };
    $rows = [
        ['Equipamento',        $g->codigo_inventario . ' — ' . $g->designacao],
        ['Data de início',     $fmt($g->data_inicio)],
        ['Data de fim',        $fmt($g->data_fim)],
        ['Tem contrato',       $g->tem_contrato ? 'Sim' : 'Não'],
        ['Tipo',               $g->tipo_contrato ?: '—'],
        ['Entidade responsável', $g->entidade_responsavel ?: '—'],
        ['Periodicidade',      $g->periodicidade ?: '—'],
        ['Observações',        $g->observacoes ?: '—'],
    ];
    $c  = "0.051 0.102 0.196 rg $M " . ($H - $M - 44) . " " . ($W - 2 * $M) . " 44 re f 1 1 1 rg\n";
    $c .= "BT /F2 15 Tf " . ($M + 12) . " " . ($H - $M - 20) . " Td (" . $encf('Garantia/Contrato') . ") Tj ET\n";
    $c .= "BT /F1 9 Tf " . ($M + 12) . " " . ($H - $M - 35) . " Td (" . $encf('MedSolutions  —  ' . $g->codigo_inventario . '  —  ' . date('d/m/Y H:i')) . ") Tj ET\n0 0 0 rg\n";
    $y = $H - $M - 70;
    foreach ($rows as [$label, $value]) {
        $c .= "0.94 0.96 0.99 rg $M " . ($y - 4) . " " . ($W - 2 * $M) . " 18 re f 0 0 0 rg\n";
        $c .= "BT /F2 9 Tf " . ($M + 6) . " $y Td (" . $encf($label) . ") Tj ET\n";
        $c .= "BT /F1 9 Tf " . ($M + 160) . " $y Td (" . $encf(mb_strimwidth((string)$value, 0, 80, '...', 'UTF-8')) . ") Tj ET\n";
        $c .= "0.88 0.88 0.88 RG 0.3 w $M " . ($y - 4) . " m " . ($W - $M) . " " . ($y - 4) . " l S\n";
        $y -= 22;
    }
    $fReg = 4; $fBold = 5;
    $objs = [
        1 => "<< /Type /Catalog /Pages 2 0 R >>",
        2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $W $H] /Resources << /Font << /F1 $fReg 0 R /F2 $fBold 0 R >> >> /Contents 6 0 R >>",
        6 => "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "endstream",
        $fReg  => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>",
        $fBold => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>",
    ];
    ksort($objs);
    $pdf = "%PDF-1.4\n"; $off = [];
    foreach ($objs as $num => $body) { $off[$num] = strlen($pdf); $pdf .= "$num 0 obj\n$body\nendobj\n"; }
    $xref = strlen($pdf); $cnt = max(array_keys($objs)) + 1;
    $pdf .= "xref\n0 $cnt\n0000000000 65535 f \n";
    for ($i = 1; $i < $cnt; $i++) { $pdf .= isset($off[$i]) ? sprintf("%010d 00000 n \n", $off[$i]) : "0000000000 65535 f \n"; }
    $pdf .= "trailer\n<< /Size $cnt /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="garantia_' . $g->codigo_inventario . '_' . date('Ymd') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf; exit;
}

$page_title = 'Garantias-Contrato - Detalhes';
include __DIR__ . '/../../includes/header.php';
?>
<div class="mhs-page-header">
  <div><span class="mhs-page-kicker"><i class="fa-solid fa-shield-halved fa-fw"></i></span><h1 class="mhs-page-title">Garantia — <?= esc($g->codigo_inventario) ?></h1></div>
  <div class="mhs-page-actions">
    <a href="?export=pdf&id=<?= $id ?>" class="btn btn-outline-dark"><i class="fa-solid fa-file-arrow-down me-2"></i>Exportar PDF</a>
    <a href="editar.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-2"></i>Editar</a>
    <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
  </div>
</div>
<div class="mhs-detail-summary card mhs-data-card mb-4">
  <div class="mhs-detail-summary-inner">
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Equipamento</span><span class="mhs-detail-summary-val"><?= esc($g->codigo_inventario) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Início</span><span class="mhs-detail-summary-val"><?= $fmt($g->data_inicio) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Fim</span><span class="mhs-detail-summary-val"><?= $fmt($g->data_fim) ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Entidade</span><span class="mhs-detail-summary-val"><?= esc($g->entidade_responsavel ?: '—') ?></span></div>
    <div class="mhs-detail-summary-sep"></div>
    <div class="mhs-detail-summary-item"><span class="mhs-detail-summary-label">Contrato</span><span class="mhs-detail-summary-val <?= $g->tem_contrato ? 'mhs-detail-summary-val--ok' : '' ?>"><?= $g->tem_contrato ? 'Sim' : 'Não' ?></span></div>
  </div>
</div>
<div class="card mhs-data-card">
  <div class="mhs-detail-tabs">
    <button class="mhs-detail-tab active" data-tab="contrato"><i class="fa-solid fa-shield-halved"></i> Contrato</button>
    <button class="mhs-detail-tab" data-tab="ficheiro"><i class="fa-solid fa-file-pdf"></i> Documento</button>
  </div>
  <div class="mhs-tab-pane active" id="tab-contrato">
    <div class="mhs-tab-body"><div class="row g-4"><div class="col-md-6">
      <div class="mhs-info-group">
        <div class="mhs-info-group-title"><i class="fa-solid fa-shield-halved"></i> Dados do contrato</div>
        <dl class="mhs-info-dl">
          <dt>Equipamento</dt><dd><?= esc($g->codigo_inventario . ' — ' . $g->designacao) ?></dd>
          <dt>Data de início</dt><dd><?= $fmt($g->data_inicio) ?></dd>
          <dt>Data de fim</dt><dd><?= $fmt($g->data_fim) ?></dd>
          <dt>Tem contrato</dt><dd><?= $g->tem_contrato ? 'Sim' : 'Não' ?></dd>
          <dt>Tipo</dt><dd><?= esc($g->tipo_contrato ?: '—') ?></dd>
          <dt>Entidade responsável</dt><dd><?= esc($g->entidade_responsavel ?: '—') ?></dd>
          <dt>Periodicidade</dt><dd><?= esc($g->periodicidade ?: '—') ?></dd>
        </dl>
      </div>
      <?php if (!empty($g->observacoes)): ?>
      <div class="mhs-info-group mt-3">
        <div class="mhs-info-group-title"><i class="fa-solid fa-comment"></i> Observações</div>
        <p class="mhs-info-obs"><?= esc($g->observacoes) ?></p>
      </div>
      <?php endif; ?>
    </div></div></div>
  </div>
  <div class="mhs-tab-pane" id="tab-ficheiro">
    <div class="mhs-tab-body"><div class="mhs-info-group mhs-w-380">
      <div class="mhs-info-group-title"><i class="fa-solid fa-file-pdf"></i> Documento do contrato</div>
      <?php if (!empty($g->nome_ficheiro)): ?>
        <p class="mhs-info-obs mb-2"><i class="fa-solid fa-file-pdf text-danger me-1"></i><?= esc($g->nome_ficheiro) ?></p>
        <a href="lista.php?ficheiro=<?= $id ?>" class="btn btn-primary w-100"><i class="fa-solid fa-download me-2"></i>Descarregar PDF</a>
      <?php else: ?>
        <p class="mhs-info-obs">Sem documento importado. Pode anexar um na edição.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
