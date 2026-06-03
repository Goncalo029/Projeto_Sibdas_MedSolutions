<?php
function get_notificacoes(): array {
    $profile = $_SESSION['profile'] ?? '';

    if (!in_array($profile, ['admin', 'tecnico'])) {
        return [];
    }

    $notifs = [];

    try {
        $pdo = mhs_pdo();

        // Garantias/contratos a expirar nos próximos 30 dias
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM garantias_contratos
             WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND deleted_at IS NULL"
        );
        $row = $stmt->fetch();
        if ($row->total > 0) {
            $n = (int) $row->total;
            $notifs[] = [
                'icon'  => 'fa-shield-halved',
                'color' => 'warning',
                'label' => $n . ' garantia' . ($n > 1 ? 's' : '') . '/contrato' . ($n > 1 ? 's' : '') . ' a expirar em 30 dias',
                'link'  => BASE_URL . '/private/views/garantias-contrato/lista.php',
                'count' => $n,
            ];
        }

        // Documentos com data de validade nos próximos 30 dias
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM documentos
             WHERE data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND deleted_at IS NULL"
        );
        $row = $stmt->fetch();
        if ($row->total > 0) {
            $n = (int) $row->total;
            $notifs[] = [
                'icon'  => 'fa-file-lines',
                'color' => 'danger',
                'label' => $n . ' documento' . ($n > 1 ? 's' : '') . ' com validade próxima',
                'link'  => BASE_URL . '/private/views/documentos/lista.php',
                'count' => $n,
            ];
        }

        // Equipamentos em manutenção
        $stmt = $pdo->query(
            "SELECT COUNT(*) as total FROM equipamentos
             WHERE estado = 'Em manutenção' AND ativo = 1 AND deleted_at IS NULL"
        );
        $row = $stmt->fetch();
        if ($row->total > 0) {
            $n = (int) $row->total;
            $notifs[] = [
                'icon'  => 'fa-wrench',
                'color' => 'info',
                'label' => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' em manutenção',
                'link'  => BASE_URL . '/private/views/equipamentos/lista.php',
                'count' => $n,
            ];
        }

    } catch (Exception $e) {
        // notificações não são críticas
    }

    return $notifs;
}
