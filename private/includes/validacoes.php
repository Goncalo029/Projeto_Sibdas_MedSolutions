<?php
/**
 * Funções de validação — MedSolutions / MedInventar
 */

/**
 * Validar nome (genérico)
 */
function validar_nome(string $nome): array
{
    $erros = [];
    if (empty(trim($nome))) {
        $erros[] = 'O campo Nome é obrigatório.';
    } elseif (strlen($nome) < 3) {
        $erros[] = 'O campo Nome deve ter no mínimo 3 caracteres.';
    } elseif (strlen($nome) > 150) {
        $erros[] = 'O campo Nome não pode exceder 150 caracteres.';
    }
    return $erros;
}

/**
 * Validar email
 */
function validar_email(string $email): array
{
    $erros = [];
    if (empty(trim($email))) {
        $erros[] = 'O campo Email é obrigatório.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O campo Email deve conter um endereço válido.';
    }
    return $erros;
}

/**
 * Validar telefone (9 dígitos)
 */
function validar_telefone(string $telefone): array
{
    $erros = [];
    if (!empty(trim($telefone)) && !preg_match('/^\d{9}$/', $telefone)) {
        $erros[] = 'O campo Telefone deve conter exatamente 9 dígitos.';
    }
    return $erros;
}

/**
 * Validar morada
 */
function validar_morada(string $morada): array
{
    $erros = [];
    if (!empty(trim($morada)) && (strlen($morada) < 5 || strlen($morada) > 200)) {
        $erros[] = 'A morada deve ter entre 5 e 200 caracteres.';
    }
    return $erros;
}

/**
 * Validar código de inventário
 */
function validar_codigo_inventario(string $codigo): array
{
    $erros = [];
    if (empty(trim($codigo))) {
        $erros[] = 'O código de inventário é obrigatório.';
    } elseif (!preg_match('/^[A-Za-z0-9.\-\/]+$/', $codigo)) {
        $erros[] = 'O código de inventário só pode conter letras, números, pontos, hífens e barras.';
    } elseif (strlen($codigo) > 50) {
        $erros[] = 'O código de inventário não pode exceder 50 caracteres.';
    }
    return $erros;
}

/**
 * Validar NIF (9 dígitos)
 */
function validar_nif(string $nif): array
{
    $erros = [];
    if (!empty(trim($nif)) && !preg_match('/^\d{9}$/', $nif)) {
        $erros[] = 'O NIF deve conter exatamente 9 dígitos.';
    }
    return $erros;
}

/**
 * Validar estado do equipamento
 */
function validar_estado_equipamento(string $estado): array
{
    $validos = ['Ativo', 'Em manutenção', 'Em calibração', 'Em quarentena', 'Inativo', 'Abatido'];
    $erros   = [];
    if (empty($estado)) {
        $erros[] = 'O estado do equipamento é obrigatório.';
    } elseif (!in_array($estado, $validos, true)) {
        $erros[] = 'Estado do equipamento inválido.';
    }
    return $erros;
}

/**
 * Validar criticidade
 */
function validar_criticidade(string $criticidade): array
{
    $validos = ['Baixa', 'Média', 'Alta', 'Suporte de vida'];
    $erros   = [];
    if (empty($criticidade)) {
        $erros[] = 'A criticidade é obrigatória.';
    } elseif (!in_array($criticidade, $validos, true)) {
        $erros[] = 'Nível de criticidade inválido.';
    }
    return $erros;
}

/**
 * Validar data (formato dd/mm/yyyy)
 */
function validar_data(string $data): array
{
    $erros = [];
    if (empty(trim($data))) {
        $erros[] = 'A data é obrigatória.';
    } elseif (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
        $erros[] = 'O formato da data deve ser DD/MM/YYYY.';
    } else {
        $partes = explode('/', $data);
        if (!checkdate((int)$partes[1], (int)$partes[0], (int)$partes[2])) {
            $erros[] = 'A data não é válida.';
        }
    }
    return $erros;
}

/**
 * Validar URL
 */
function validar_url(string $url): array
{
    $erros = [];
    if (!empty(trim($url)) && !filter_var($url, FILTER_VALIDATE_URL)) {
        $erros[] = 'A URL não é válida.';
    }
    return $erros;
}
?>
