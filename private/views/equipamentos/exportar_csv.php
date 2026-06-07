<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/validacoes.php';
redirect_if_not_logged();

try {
    $equipamentos = mhs_pdo()->query("
        SELECT e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie,
               e.estado, e.criticidade, e.tipo_entrada, e.ano_fabrico,
               e.custo_aquisicao, e.data_aquisicao,
               c.nome AS categoria, l.servico, l.piso, l.sala
        FROM equipamentos e
        LEFT JOIN categorias c ON c.id = e.id_categoria
        LEFT JOIN localizacoes l ON l.id = e.id_localizacao
        WHERE e.deleted_at IS NULL
        ORDER BY e.codigo_inventario
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro ao exportar dados.');
}

$filename = 'equipamentos_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');

// BOM para Excel reconhecer UTF-8
fwrite($out, "\xEF\xBB\xBF");

// Cabeçalhos
fputcsv($out, [
    'Código', 'Designação', 'Marca', 'Modelo', 'Nº Série',
    'Estado', 'Criticidade', 'Tipo de Entrada', 'Ano de Fabrico',
    'Custo (€)', 'Data de Aquisição',
    'Categoria', 'Serviço', 'Piso', 'Sala'
], ';');

foreach ($equipamentos as $eq) {
    fputcsv($out, [
        $eq['codigo_inventario'],
        $eq['designacao'],
        $eq['marca'],
        $eq['modelo'],
        $eq['numero_serie'],
        $eq['estado'],
        $eq['criticidade'],
        $eq['tipo_entrada'],
        $eq['ano_fabrico'],
        $eq['custo_aquisicao'] !== null ? number_format((float)$eq['custo_aquisicao'], 2, ',', ' ') : '',
        $eq['data_aquisicao'] ? date('d/m/Y', strtotime($eq['data_aquisicao'])) : '',
        $eq['categoria'],
        $eq['servico'],
        $eq['piso'],
        $eq['sala'],
    ], ';');
}

fclose($out);
exit;
