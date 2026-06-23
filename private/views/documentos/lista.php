<?php
/**
 * Lista de documentos
 * Mostra todos os documentos associados a equipamentos.
 * Permite ver, descarregar, editar e eliminar cada documento.
 */

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';

// Verificar se o utilizador está autenticado
redirect_if_not_logged();

$page_title = 'Documentos - Lista';
$documentos = [];
$erro_bd    = '';
$f_validade = ($_GET['validade'] ?? '') === 'proxima';

// ─── Carregar documentos com o equipamento associado ─────────────────────────
try {
    $where = "d.eliminado_em IS NULL";
    if ($f_validade) {
        $where .= " AND d.data_validade IS NOT NULL AND d.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    }
    $documentos = mhs_pdo()->query("
        SELECT d.id, d.tipo_documento, d.nome_documento, d.data_documento, d.data_validade, d.nome_ficheiro,
               (d.ficheiro_conteudo IS NOT NULL) AS tem_ficheiro,
               e.codigo_inventario, e.designacao
        FROM documentos d
        JOIN equipamentos e ON e.id = d.id_equipamento
        WHERE $where
        ORDER BY d.data_validade ASC, d.data_documento DESC, d.nome_documento
    ")->fetchAll();
} catch (PDOException $e) {
    $erro_bd = 'Nao foi possivel carregar documentos.';
}

// ─── Exportação CSV ──────────────────────────────────────────────────────────
if (($_GET['export'] ?? '') === 'csv' && !$erro_bd) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="documentos_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
    fputcsv($out, ['Equipamento', 'Tipo', 'Nome do documento', 'Data', 'Validade', 'Tem PDF']);
    foreach ($documentos as $d) {
        fputcsv($out, [
            $d->codigo_inventario . ' - ' . $d->designacao,
            $d->tipo_documento, $d->nome_documento,
            $d->data_documento, $d->data_validade,
            $d->tem_ficheiro ? 'Sim' : 'Não',
        ]);
    }
    fclose($out);
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="mhs-page-header">
  <div>
    <span class="mhs-page-kicker"><i class="fa-solid fa-file-lines fa-fw"></i></span>
    <h1 class="mhs-page-title">Documentos</h1>
  </div>
</div>

<?php if ($erro_bd) : ?><div class="alert alert-warning mb-3"><?= esc($erro_bd) ?></div><?php endif; ?>

<?php if ($f_validade) : ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
  <span><i class="fa-solid fa-filter me-2"></i>A mostrar apenas documentos com <strong>validade próxima</strong> (30 dias) — <?= count($documentos) ?> resultado<?= count($documentos) !== 1 ? 's' : '' ?></span>
  <a href="lista.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Limpar filtro</a>
</div>
<?php endif; ?>

<div class="card mhs-data-card">
  <div class="mhs-table-toolbar">
    <div class="mhs-table-toolbar-left">
      <i class="fa-solid fa-file-lines mhs-table-toolbar-icon"></i>
      <span class="mhs-table-toolbar-label">Lista de Documentos</span>
      <span class="mhs-table-toolbar-count"><?= count($documentos) ?> registos</span>
    </div>
    <div class="d-flex gap-2">
      <a href="?export=csv<?= $f_validade ? '&validade=proxima' : '' ?>" class="btn btn-outline-secondary mhs-table-toolbar-btn"><i class="fa-solid fa-file-csv"></i> Exportar CSV</a>
      <a href="novo.php" class="btn btn-primary mhs-table-toolbar-btn"><i class="fa-solid fa-plus"></i> Novo Documento</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mhs-datatable mb-0" id="documentosTable">
        <thead class="mhs-thead">
          <tr><th>Equipamento</th><th>Tipo</th><th>Nome</th><th>Data</th><th>Validade</th><th>PDF</th><th>Acoes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $documento) : ?>
            <tr>
              <td><?= esc($documento->codigo_inventario . ' - ' . $documento->designacao) ?></td>
              <td><?= esc($documento->tipo_documento) ?></td>
              <td><?= esc($documento->nome_documento) ?></td>
              <td><?= $documento->data_documento ? esc(date('d/m/Y', strtotime($documento->data_documento))) : '' ?></td>
              <td><?= $documento->data_validade ? esc(date('d/m/Y', strtotime($documento->data_validade))) : '' ?></td>
              <td>
                <?php if ($documento->tem_ficheiro): ?>
                <a href="download.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-danger" title="Descarregar PDF"><i class="fa-solid fa-file-pdf"></i></a>
                <?php else: ?>
                <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1 flex-nowrap">
                  <a href="detalhes.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Ver detalhes"><i class="fa-solid fa-eye"></i></a>
                  <a href="editar.php?id=<?= (int) $documento->id ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar"><i class="fa-solid fa-pen"></i></a>
                  <?php if (is_admin()): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-delete-id="<?= (int) $documento->id ?>" data-delete-name="<?= esc($documento->nome_documento) ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
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
