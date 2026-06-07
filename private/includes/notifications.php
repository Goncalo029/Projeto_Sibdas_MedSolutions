<?php
function get_notificacoes(): array {
    $profile = $_SESSION['profile'] ?? '';

    if (!in_array($profile, ['admin', 'tecnico'])) {
        return [];
    }

    $notifs = [];

    try {
        $pdo = mhs_pdo();

        // Mensagens de contacto não lidas (admin + tecnico)
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM mensagens_contacto WHERE lida = 0 AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-envelope',
                'color'    => 'danger',
                'label'    => $n . ' mensagem' . ($n > 1 ? 'ns' : '') . ' de contacto não ' . ($n > 1 ? 'lidas' : 'lida'),
                'link'     => BASE_URL . '/private/views/mensagens/lista.php',
                'count'    => $n,
                'priority' => 1,
            ];
        }

        // Manutenções em atraso (data vencida e não concluídas)
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM manutencoes_preventivas
             WHERE proxima_manutencao < CURDATE()
             AND estado NOT IN ('Concluída', 'Cancelada')
             AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-triangle-exclamation',
                'color'    => 'danger',
                'label'    => $n . ' manutenção' . ($n > 1 ? 'ões' : '') . ' em atraso',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php',
                'count'    => $n,
                'priority' => 2,
            ];
        }

        // Manutenções preventivas nos próximos 7 dias
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM manutencoes_preventivas
             WHERE proxima_manutencao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             AND estado NOT IN ('Concluída', 'Cancelada')
             AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-wrench',
                'color'    => 'warning',
                'label'    => $n . ' manutenção' . ($n > 1 ? 'ões' : '') . ' prevista' . ($n > 1 ? 's' : '') . ' nos próximos 7 dias',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php',
                'count'    => $n,
                'priority' => 3,
            ];
        }

        // Garantias/contratos a expirar nos próximos 30 dias
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM garantias_contratos
             WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-shield-halved',
                'color'    => 'warning',
                'label'    => $n . ' garantia' . ($n > 1 ? 's' : '') . '/contrato' . ($n > 1 ? 's' : '') . ' a expirar em 30 dias',
                'link'     => BASE_URL . '/private/views/garantias-contrato/lista.php',
                'count'    => $n,
                'priority' => 4,
            ];
        }

        // Documentos com data de validade nos próximos 30 dias
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM documentos
             WHERE data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-file-lines',
                'color'    => 'warning',
                'label'    => $n . ' documento' . ($n > 1 ? 's' : '') . ' com validade próxima',
                'link'     => BASE_URL . '/private/views/documentos/lista.php',
                'count'    => $n,
                'priority' => 5,
            ];
        }

        // Equipamentos em manutenção no momento
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM equipamentos
             WHERE estado = 'Em manutenção' AND ativo = 1 AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-screwdriver-wrench',
                'color'    => 'info',
                'label'    => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' em manutenção',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php',
                'count'    => $n,
                'priority' => 6,
            ];
        }

        // Equipamentos avariados
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM equipamentos
             WHERE estado = 'Avariado' AND ativo = 1 AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-circle-xmark',
                'color'    => 'danger',
                'label'    => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' avariado' . ($n > 1 ? 's' : ''),
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php',
                'count'    => $n,
                'priority' => 7,
            ];
        }

        // Empréstimos em curso sem data de devolução prevista (mais de 30 dias)
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM emprestimos_equipamentos
             WHERE data_devolucao IS NULL
             AND data_emprestimo < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             AND deleted_at IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-boxes-packing',
                'color'    => 'warning',
                'label'    => $n . ' empréstimo' . ($n > 1 ? 's' : '') . ' em curso há mais de 30 dias',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php',
                'count'    => $n,
                'priority' => 8,
            ];
        }

    } catch (Exception) {
        // notificações não são críticas — falha silenciosa
    }

    // Ordenar por prioridade
    usort($notifs, fn($a, $b) => $a['priority'] <=> $b['priority']);

    return $notifs;
}
