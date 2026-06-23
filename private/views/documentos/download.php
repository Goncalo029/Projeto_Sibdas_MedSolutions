<?php
// carrega as funcoes e verifica se o utilizador tem sessao iniciada
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

// vai buscar o id do documento ao link, se nao tiver volta para a lista
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

// procura o ficheiro do documento na base de dados
$stmt = mhs_pdo()->prepare("SELECT nome_ficheiro, ficheiro_conteudo, ficheiro_mime FROM documentos WHERE id = ? AND eliminado_em IS NULL");
$stmt->execute([$id]);
$doc = $stmt->fetch();

// se o documento nao existir ou nao tiver ficheiro, mostra erro e volta
if (!$doc || $doc->ficheiro_conteudo === null) {
    $_SESSION['error_message'] = 'Este documento não tem ficheiro associado.';
    header('Location: lista.php'); exit;
}

// se nao tiver nome usa um nome por defeito
$nome = $doc->nome_ficheiro ?: ('documento_' . $id . '.pdf');

// envia os cabecalhos para o browser fazer o download do ficheiro
header('Content-Type: ' . ($doc->ficheiro_mime ?: 'application/pdf'));
header('Content-Disposition: attachment; filename="' . $nome . '"');
header('Content-Length: ' . strlen($doc->ficheiro_conteudo));

// envia o conteudo do ficheiro
echo $doc->ficheiro_conteudo;
exit;
