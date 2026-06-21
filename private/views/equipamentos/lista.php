<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

// Ações rápidas a partir da lista (sem ficheiro separado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['accao'] ?? ''), ['concluir_manutencao', 'ativar'], true)) {
    $eq_id  = (int)($_POST['id_equipamento'] ?? 0);
    $accao  = $_POST['accao'];
    if ($accao === 'ativar') {
        if ($eq_id && mhs_ativar_equipamento($eq_id)) {
            $_SESSION['success_message'] = 'Equipamento ativado.';
        } else {
            $_SESSION['error_message'] = 'Equipamento já está ativo ou não foi encontrado.';
        }
    } else { // concluir_manutencao
        if ($eq_id && mhs_concluir_manutencao($eq_id)) {
            $_SESSION['success_message'] = 'Manutenção confirmada. Equipamento marcado como Ativo.';
        } else {
            $_SESSION['error_message'] = 'Não foi possível confirmar a manutenção.';
        }
    }
    $qs = http_build_query(array_filter(['estado' => $_POST['estado'] ?? '', 'filtro' => $_POST['filtro'] ?? '', 'ver' => $_POST['ver'] ?? '']));
    header('Location: lista.php' . ($qs ? '?' . $qs : ''));
    exit;
}

$page_title = 'Equipamentos - Lista';
$equipamentos = [];
$erro_bd = '';

// Filtros
$f_estado = trim($_GET['estado'] ?? '');
$f_critic = trim($_GET['criticidade'] ?? '');
$f_filtro = trim($_GET['filtro'] ?? '');
$f_ver    = trim($_GET['ver'] ?? '');    // 'inativos' | ''

// Inativos/abatidos via estado GET ou ver=inativos
$show_inativos = ($f_ver === 'inativos' || $f_estado === 'Inativo' || $f_estado === 'Abatido');
if ($show_inativos) { $f_ver = 'inativos'; }

$where  = "e.eliminado_em IS NULL";
$params = [];

if ($f_estado !== '') {
    $where .= " AND e.estado = ?";
    $params[] = $f_estado;
} elseif ($show_inativos) {
    $where .= " AND e.estado IN ('Inativo','Abatido')";
} else {
    // Vista padrão: excluir inativos/abatidos (NULL tratado como em serviço)
    $where .= " AND (e.estado IS NULL OR e.estado NOT IN ('Inativo','Abatido'))";
}
if ($f_critic !== '') {
    $where .= " AND e.criticidade = ?";
    $params[] = $f_critic;
}
if ($f_filtro === 'sem_docs') {
    $where .= " AND NOT EXISTS (SELECT 1 FROM documentos d2 WHERE d2.id_equipamento = e.id AND d2.eliminado_em IS NULL)";
}
if ($f_filtro === 'manutencao_atraso') {
    $where .= " AND EXISTS (SELECT 1 FROM manutencoes_preventivas mp
                WHERE mp.id_equipamento = e.id AND mp.proxima_manutencao < CURDATE()
                AND mp.estado NOT IN ('Concluída', 'Cancelada') AND mp.eliminado_em IS NULL)";
} elseif ($f_filtro === 'manutencao_7dias') {
    $where .= " AND EXISTS (SELECT 1 FROM manutencoes_preventivas mp
                WHERE mp.id_equipamento = e.id
                AND mp.proxima_manutencao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND mp.estado NOT IN ('Concluída', 'Cancelada') AND mp.eliminado_em IS NULL)";
} elseif ($f_filtro === 'emprestimo_30dias') {
    $where .= " AND EXISTS (SELECT 1 FROM emprestimos_equipamentos ee
                WHERE ee.id_equipamento = e.id AND ee.data_devolucao IS NULL
                AND ee.data_saida < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND ee.eliminado_em IS NULL)";
}

$filtro_label = match(true) {
    $f_estado !== ''                   => 'Estado: ' . $f_estado,
    $f_critic !== ''                   => 'Criticidade: ' . $f_critic,
    $f_filtro === 'sem_docs'           => 'Sem documentação',
    $f_filtro === 'manutencao_atraso'  => 'Manutenções em atraso',
    $f_filtro === 'manutencao_7dias'   => 'Manutenção prevista nos próximos 7 dias',
    $f_filtro === 'emprestimo_30dias'  => 'Empréstimos em curso há mais de 30 dias',
    default                            => '',
};

try {
    $pdo_main = mhs_pdo();
    $stmt = $pdo_main->prepare("
        SELECT e.id, e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
               e.estado, e.criticidade,
               c.nome AS categoria, l.servico
        FROM equipamentos e
        LEFT JOIN categorias c ON c.id = e.id_categoria
        LEFT JOIN localizacoes l ON l.id = e.id_localizacao
        WHERE $where
        ORDER BY e.codigo_inventario
    ");
    $stmt->execute($params);
    $equipamentos = $stmt->fetchAll();
    // Contagens para as tabs (sem filtros adicionais)
    $n_servico  = (int)$pdo_main->query("SELECT COUNT(*) FROM equipamentos WHERE eliminado_em IS NULL AND estado NOT IN ('Inativo','Abatido')")->fetchColumn();
    $n_inativos = (int)$pdo_main->query("SELECT COUNT(*) FROM equipamentos WHERE eliminado_em IS NULL AND estado IN ('Inativo','Abatido')")->fetchColumn();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar equipamentos.';
    $n_servico = 0; $n_inativos = 0;
}

// ── Exportação inline ──────────────────────────────────────────────────
$export = $_GET['export'] ?? '';
if ($export === 'csv' && !$erro_bd) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="equipamentos_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
    fputcsv($out, ['Código', 'Designação', 'Marca', 'Modelo', 'Nº Série', 'Categoria', 'Serviço', 'Estado', 'Criticidade']);
    foreach ($equipamentos as $eq) {
        fputcsv($out, [
            $eq->codigo_inventario, $eq->designacao, $eq->marca,
            $eq->modelo, $eq->numero_serie, $eq->categoria,
            $eq->servico, $eq->estado, $eq->criticidade,
        ]);
    }
    fclose($out);
    exit;
}
if ($export === 'pdf' && !$erro_bd) {
    // Geração de PDF descarregável (inline, sem dependências externas)
    $W = 595.28; $H = 841.89; $M = 36;
    $encf = function (string $s): string {
        $s = iconv('UTF-8', 'Windows-1252//TRANSLIT', $s);
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ''], $s ?: '');
    };
    $headers = ['Código', 'Designação', 'Marca', 'Serviço', 'Estado', 'Criticidade'];
    $colX    = [40, 108, 256, 334, 422, 494];

    $pageHeader = function () use ($W, $H, $M, $encf, $headers, $colX, $filtro_label, $equipamentos) {
        $c  = "0.114 0.161 0.302 rg " . $M . " " . ($H - $M - 44) . " " . ($W - 2 * $M) . " 44 re f 1 1 1 rg\n";
        $c .= "BT /F2 16 Tf " . ($M + 12) . " " . ($H - $M - 20) . " Td (" . $encf('Inventario de Equipamentos') . ") Tj ET\n";
        $sub = 'MedSolutions  -  ' . ($filtro_label !== '' ? $filtro_label : 'Lista completa')
             . '  -  ' . count($equipamentos) . ' equipamento(s)  -  ' . date('d/m/Y H:i');
        $c .= "BT /F1 9 Tf " . ($M + 12) . " " . ($H - $M - 36) . " Td (" . $encf($sub) . ") Tj ET\n0 0 0 rg\n";
        $hy = $H - $M - 64;
        $c .= "0.90 0.93 0.98 rg " . $M . " " . ($hy - 5) . " " . ($W - 2 * $M) . " 18 re f 0 0 0 rg\n";
        foreach ($headers as $i => $h) {
            $c .= "BT /F2 8 Tf " . $colX[$i] . " " . $hy . " Td (" . $encf($h) . ") Tj ET\n";
        }
        return [$c, $hy - 18];
    };

    $pages = [];
    [$content, $y] = $pageHeader();
    foreach ($equipamentos as $eq) {
        if ($y < $M + 24) { $pages[] = $content; [$content, $y] = $pageHeader(); }
        $vals = [
            mb_strimwidth((string)$eq->codigo_inventario, 0, 14, '', 'UTF-8'),
            mb_strimwidth((string)$eq->designacao, 0, 32, '...', 'UTF-8'),
            mb_strimwidth((string)($eq->marca ?? ''), 0, 16, '...', 'UTF-8'),
            mb_strimwidth((string)($eq->servico ?? ''), 0, 18, '...', 'UTF-8'),
            mb_strimwidth((string)$eq->estado, 0, 14, '...', 'UTF-8'),
            mb_strimwidth((string)$eq->criticidade, 0, 14, '...', 'UTF-8'),
        ];
        foreach ($vals as $i => $v) {
            $content .= "BT /F1 8 Tf " . $colX[$i] . " " . $y . " Td (" . $encf((string)$v) . ") Tj ET\n";
        }
        $content .= "0.86 0.86 0.86 RG 0.5 w " . $M . " " . ($y - 6) . " m " . ($W - $M) . " " . ($y - 6) . " l S\n";
        $y -= 18;
    }
    $pages[] = $content;

    // Montagem dos objetos PDF
    $n = count($pages);
    $objs = [];
    $objs[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $kids = [];
    for ($i = 0; $i < $n; $i++) { $kids[] = (3 + $i) . ' 0 R'; }
    $objs[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count $n >>";
    $cStart = 3 + $n; $fReg = $cStart + $n; $fBold = $fReg + 1;
    for ($i = 0; $i < $n; $i++) {
        $objs[3 + $i] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $W $H] /Resources << /Font << /F1 $fReg 0 R /F2 $fBold 0 R >> >> /Contents " . ($cStart + $i) . " 0 R >>";
        $objs[$cStart + $i] = "<< /Length " . strlen($pages[$i]) . " >>\nstream\n" . $pages[$i] . "endstream";
    }
    $objs[$fReg]  = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $objs[$fBold] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";
    ksort($objs);

    $pdf = "%PDF-1.4\n"; $off = [];
    foreach ($objs as $num => $body) { $off[$num] = strlen($pdf); $pdf .= "$num 0 obj\n$body\nendobj\n"; }
    $xref = strlen($pdf); $cnt = count($objs) + 1;
    $pdf .= "xref\n0 $cnt\n0000000000 65535 f \n";
    for ($i = 1; $i < $cnt; $i++) { $pdf .= sprintf("%010d 00000 n \n", $off[$i]); }
    $pdf .= "trailer\n<< /Size $cnt /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="equipamentos_' . date('Ymd_His') . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-stethoscope fa-fw"></i></span>
    <h1 class="mhs-page-title">Equipamentos</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<?php
$qs_sem_ver = http_build_query(array_filter(['estado' => $f_estado, 'criticidade' => $f_critic, 'filtro' => $f_filtro]));
?>
<!-- Tabs Em Serviço / Inativos -->
<div class="mhs-eq-tabs">
  <a href="lista.php<?= $qs_sem_ver ? '?' . esc($qs_sem_ver) : '' ?>" class="mhs-eq-tab<?= $f_ver !== 'inativos' ? ' active' : '' ?>">
    <i class="fa-solid fa-circle-check"></i> Em Serviço
    <span class="mhs-eq-tab-count"><?= $n_servico ?></span>
  </a>
  <a href="lista.php?ver=inativos<?= $qs_sem_ver ? '&' . esc($qs_sem_ver) : '' ?>" class="mhs-eq-tab<?= $f_ver === 'inativos' ? ' active' : '' ?>">
    <i class="fa-solid fa-power-off"></i> Inativos / Abatidos
    <span class="mhs-eq-tab-count"><?= $n_inativos ?></span>
  </a>
</div>

<?php if ($filtro_label !== '') : ?>
<div class="alert alert-info d-flex align-items-center justify-content-between mb-3">
  <span><i class="fa-solid fa-filter me-2"></i>Filtro ativo: <strong><?= esc($filtro_label) ?></strong> — <?= count($equipamentos) ?> resultado<?= count($equipamentos) !== 1 ? 's' : '' ?></span>
  <a href="lista.php<?= $f_ver === 'inativos' ? '?ver=inativos' : '' ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Limpar filtro</a>
</div>
<?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-stethoscope mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Equipamentos</span>
      <span class="mhs-table-toolbar-count"><?= count($equipamentos) ?> registos</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <?php $qs_filtros = http_build_query(array_filter(['estado' => $f_estado, 'criticidade' => $f_critic, 'filtro' => $f_filtro])); ?>
      <a href="?export=csv<?= $qs_filtros ? '&' . esc($qs_filtros) : '' ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-csv me-1"></i>Exportar CSV
      </a>
      <a href="?export=pdf<?= $qs_filtros ? '&' . esc($qs_filtros) : '' ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
      </a>
      <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn">
        <i class="fa-solid fa-plus"></i>
        Novo Equipamento
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mhs-datatable mb-0" id="equipamentosTable" data-total="<?= count($equipamentos) ?>">
        <thead>
          <tr>
            <th>Código</th>
            <th>Designação</th>
            <th>Marca</th>
            <th>Categoria</th>
            <th>Serviço</th>
            <th>Estado</th>
            <th>Criticidade</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($equipamentos as $eq) : ?>
            <tr>
              <td><span class="mhs-code"><?= esc($eq->codigo_inventario) ?></span></td>
              <td class="mhs-td-primary"><?= esc($eq->designacao) ?></td>
              <td><?= esc($eq->marca) ?></td>
              <td><?= esc($eq->categoria) ?></td>
              <td><?= esc($eq->servico) ?></td>
              <td><?= get_estado_badge($eq->estado) ?></td>
              <td><?= get_criticidade_badge($eq->criticidade) ?></td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <?php if (in_array($eq->estado, ['Em manutenção', 'Em calibração', 'Em quarentena'], true)): ?>
                  <form method="post" action="lista.php" onsubmit="return confirm('Confirmar que a manutenção de <?= esc($eq->codigo_inventario) ?> foi concluída? Volta a Ativo.');" class="m-0">
                    <input type="hidden" name="accao" value="concluir_manutencao">
                    <input type="hidden" name="id_equipamento" value="<?= (int)$eq->id ?>">
                    <input type="hidden" name="estado" value="<?= esc($f_estado) ?>">
                    <input type="hidden" name="filtro" value="<?= esc($f_filtro) ?>">
                    <input type="hidden" name="ver" value="<?= esc($f_ver) ?>">
                    <button type="submit" class="btn btn-sm btn-success" title="Confirmar manutenção concluída"><i class="fa-solid fa-circle-check"></i></button>
                  </form>
                  <?php elseif (in_array($eq->estado, ['Inativo', 'Abatido'], true)): ?>
                  <form method="post" action="lista.php" onsubmit="return confirm('Tornar <?= esc($eq->codigo_inventario) ?> Ativo?');" class="m-0">
                    <input type="hidden" name="accao" value="ativar">
                    <input type="hidden" name="id_equipamento" value="<?= (int)$eq->id ?>">
                    <input type="hidden" name="estado" value="<?= esc($f_estado) ?>">
                    <input type="hidden" name="filtro" value="<?= esc($f_filtro) ?>">
                    <input type="hidden" name="ver" value="<?= esc($f_ver) ?>">
                    <button type="submit" class="btn btn-sm btn-success" title="Tornar ativo"><i class="fa-solid fa-power-off"></i></button>
                  </form>
                  <?php endif; ?>
                  <a href="detalhes.php?id=<?= (int)$eq->id ?>" class="btn btn-sm btn-outline-secondary" title="Ver detalhes"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int)$eq->id ?>"  class="btn btn-sm btn-outline-primary"   title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int)$eq->id ?>" data-delete-name="<?= esc($eq->codigo_inventario) ?>" title="Apagar"><i class="fa-solid fa-trash"></i></button>
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
