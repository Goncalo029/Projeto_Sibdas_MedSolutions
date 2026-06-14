<?php
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/pdf.php';
redirect_if_not_logged();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// Carregar documento + equipamento + fornecedor
$stmt = mhs_pdo()->prepare("
    SELECT d.*,
           e.codigo_inventario, e.designacao, e.marca, e.modelo, e.numero_serie, e.fabricante,
           c.nome AS categoria, l.servico, l.sala,
           f.nome AS fornecedor, f.telefone AS forn_tel, f.email AS forn_email
    FROM documentos d
    LEFT JOIN equipamentos e ON e.id = d.id_equipamento
    LEFT JOIN categorias c   ON c.id = e.id_categoria
    LEFT JOIN localizacoes l ON l.id = e.id_localizacao
    LEFT JOIN fornecedores f ON f.id = d.id_fornecedor
    WHERE d.id = ? AND d.deleted_at IS NULL
");
$stmt->execute([$id]);
$doc = $stmt->fetch();

if (!$doc) { header('Location: lista.php'); exit; }

$fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$tipo = $doc->tipo_documento ?: 'Documento';

$pdf = new MhsPdf();
$pdf->title_band(
    $doc->nome_documento ?: $tipo,
    'MedSolutions — Gestão de Equipamentos Médicos'
);
$pdf->space(8);

// Identificação do documento
$pdf->line_text('IDENTIFICAÇÃO DO DOCUMENTO', 10, true);
$pdf->hr();
$pdf->field('Tipo de documento', $tipo);
$pdf->field('Designação', $doc->nome_documento);
$pdf->field('Data do documento', $fmt($doc->data_documento));
$pdf->field('Validade', $doc->data_validade ? $fmt($doc->data_validade) : 'Sem validade');
$pdf->space(10);

// Equipamento associado
$pdf->line_text('EQUIPAMENTO ASSOCIADO', 10, true);
$pdf->hr();
$pdf->field('Código de inventário', $doc->codigo_inventario);
$pdf->field('Designação', $doc->designacao);
$pdf->field('Marca / Modelo', trim(($doc->marca ?? '') . ' ' . ($doc->modelo ?? '')) ?: null);
$pdf->field('Nº de série', $doc->numero_serie);
$pdf->field('Fabricante', $doc->fabricante);
$pdf->field('Categoria', $doc->categoria);
$pdf->field('Localização', trim(($doc->servico ?? '') . ($doc->sala ? ' · ' . $doc->sala : '')) ?: null);
$pdf->space(10);

// Conteúdo específico conforme o tipo de documento
$pdf->line_text('CONTEÚDO', 10, true);
$pdf->hr();

$tipo_l = strtolower($tipo);
if (str_contains($tipo_l, 'manual')) {
    foreach ([
        'Este manual técnico descreve a operação, manutenção e cuidados de segurança',
        'do equipamento acima identificado. Deve ser consultado antes de qualquer',
        'intervenção e mantido junto ao equipamento.',
        '',
        '1. Instruções de operação e arranque.',
        '2. Procedimentos de limpeza e desinfeção.',
        '3. Verificações periódicas e calibração.',
        '4. Resolução de problemas e contactos de assistência técnica.',
    ] as $ln) { $pdf->line_text($ln, 11); }
} elseif (str_contains($tipo_l, 'certificad') || str_contains($tipo_l, 'calibra')) {
    foreach ([
        'Certifica-se que o equipamento identificado foi sujeito a verificação e',
        'calibração de acordo com os procedimentos aplicáveis, encontrando-se em',
        'conformidade com os parâmetros de funcionamento especificados.',
        '',
        'Resultado da verificação: CONFORME.',
        'Próxima calibração recomendada: ' . ($doc->data_validade ? $fmt($doc->data_validade) : 'a definir') . '.',
    ] as $ln) { $pdf->line_text($ln, 11); }
} elseif (str_contains($tipo_l, 'contrato')) {
    foreach ([
        'Documento de contrato de manutenção/assistência relativo ao equipamento',
        'identificado. Define o âmbito da prestação de serviços, periodicidade das',
        'intervenções preventivas e condições de assistência técnica.',
        '',
        'Entidade responsável: ' . ($doc->fornecedor ?: '—') . '.',
        'Validade do contrato: ' . ($doc->data_validade ? $fmt($doc->data_validade) : '—') . '.',
    ] as $ln) { $pdf->line_text($ln, 11); }
} else {
    foreach ([
        'Documento técnico relativo ao equipamento identificado neste relatório.',
        'Inclui a informação registada na plataforma MedSolutions à data de emissão.',
    ] as $ln) { $pdf->line_text($ln, 11); }
}

if (!empty($doc->observacoes)) {
    $pdf->space(8);
    $pdf->line_text('Observações:', 10, true);
    foreach (str_split($doc->observacoes, 95) as $ln) {
        $pdf->line_text($ln, 11);
    }
}

// Fornecedor / contacto
if ($doc->fornecedor) {
    $pdf->space(10);
    $pdf->line_text('FORNECEDOR / ENTIDADE', 10, true);
    $pdf->hr();
    $pdf->field('Nome', $doc->fornecedor);
    $pdf->field('Telefone', $doc->forn_tel);
    $pdf->field('Email', $doc->forn_email);
}

$pdf->footer_note('Documento gerado automaticamente pela plataforma MedSolutions em ' . date('d/m/Y H:i') . '.');

$nome = $doc->nome_ficheiro ?: ('documento_' . $doc->codigo_inventario . '_' . $id . '.pdf');
if (!str_ends_with(strtolower($nome), '.pdf')) { $nome .= '.pdf'; }

$pdf->download($nome);
