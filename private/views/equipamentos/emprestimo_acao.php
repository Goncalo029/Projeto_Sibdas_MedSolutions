<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$id_emprestimo  = (int)($_POST['id_emprestimo'] ?? 0);
$id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
$accao          = $_POST['accao'] ?? '';
$voltar = 'detalhes.php?id=' . $id_equipamento . '#emprestimos';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id_emprestimo) {
    header('Location: ' . $voltar); exit;
}

try {
    $pdo = mhs_pdo();

    // Carregar empréstimo + código do equipamento (para o histórico)
    $stmt = $pdo->prepare("
        SELECT ee.*, e.codigo_inventario, l2.servico AS destino
        FROM emprestimos_equipamentos ee
        LEFT JOIN equipamentos e ON e.id = ee.id_equipamento
        LEFT JOIN localizacoes l2 ON l2.id = ee.id_localizacao_destino
        WHERE ee.id = ? AND ee.deleted_at IS NULL
    ");
    $stmt->execute([$id_emprestimo]);
    $emp = $stmt->fetch();

    if (!$emp) {
        $_SESSION['error_message'] = 'Empréstimo não encontrado.';
        header('Location: ' . $voltar); exit;
    }
    if ($emp->estado !== 'Ativo' || $emp->data_devolucao) {
        $_SESSION['error_message'] = 'Este empréstimo já não está ativo.';
        header('Location: ' . $voltar); exit;
    }

    $nome = 'Empréstimo ' . ($emp->codigo_inventario ?? '#' . $id_emprestimo) . ' → ' . ($emp->destino ?? '');

    if ($accao === 'terminar') {
        // Devolução antecipada: marca como devolvido hoje
        $pdo->prepare("UPDATE emprestimos_equipamentos
                       SET data_devolucao = CURDATE(), estado = 'Devolvido', updated_at = NOW(),
                           updated_by = ?
                       WHERE id = ?")
            ->execute([$_SESSION['user_email'] ?? null, $id_emprestimo]);

        mhs_historico('emprestimo', $id_emprestimo, $nome, 'editar', 'Terminado/devolvido antecipadamente em ' . date('d/m/Y'));
        $_SESSION['success_message'] = 'Empréstimo terminado (devolução registada para hoje).';

    } elseif ($accao === 'estender') {
        $nova = trim($_POST['nova_data'] ?? '');
        $ts = strtotime($nova);
        if (!$ts) {
            $_SESSION['error_message'] = 'Data de devolução inválida.';
            header('Location: ' . $voltar); exit;
        }
        if ($ts < strtotime($emp->data_saida)) {
            $_SESSION['error_message'] = 'A nova data não pode ser anterior à data de saída.';
            header('Location: ' . $voltar); exit;
        }
        $nova_fmt = date('Y-m-d', $ts);
        $antiga = $emp->data_prevista_devolucao ? date('d/m/Y', strtotime($emp->data_prevista_devolucao)) : '—';

        $pdo->prepare("UPDATE emprestimos_equipamentos
                       SET data_prevista_devolucao = ?, updated_at = NOW(), updated_by = ?
                       WHERE id = ?")
            ->execute([$nova_fmt, $_SESSION['user_email'] ?? null, $id_emprestimo]);

        mhs_historico('emprestimo', $id_emprestimo, $nome, 'editar',
            'Devolução prevista alterada: ' . $antiga . ' → ' . date('d/m/Y', $ts));
        $_SESSION['success_message'] = 'Prazo de devolução do empréstimo atualizado.';

    } else {
        $_SESSION['error_message'] = 'Ação inválida.';
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Erro ao processar a ação: ' . $e->getMessage();
}

header('Location: ' . $voltar);
exit;
