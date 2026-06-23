<?php
// junta os avisos a mostrar no sino do topo (mensagens novas, manutencoes em atraso, etc.)
function get_notificacoes(): array {
    $profile = $_SESSION['profile'] ?? '';

    // so admin e tecnico recebem avisos
    if (!in_array($profile, ['admin', 'tecnico'])) {
        return [];
    }

    $notifs = [];

    try {
        $pdo = mhs_pdo();

        // Mensagens de contacto não lidas (apenas admin)
        if ($profile === 'admin') {
            $row = $pdo->query(
                "SELECT COUNT(*) AS total FROM mensagens_contacto WHERE lida = 0 AND eliminado_em IS NULL"
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
        }

        // Manutenções em atraso (data vencida e não concluídas)
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM manutencoes_preventivas
             WHERE proxima_manutencao < CURDATE()
             AND estado NOT IN ('Concluída', 'Cancelada')
             AND eliminado_em IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-triangle-exclamation',
                'color'    => 'danger',
                'label'    => $n . ' manutenção' . ($n > 1 ? 'ões' : '') . ' em atraso',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php?filtro=manutencao_atraso',
                'count'    => $n,
                'priority' => 2,
            ];
        }

        // Manutenções preventivas nos próximos 7 dias
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM manutencoes_preventivas
             WHERE proxima_manutencao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             AND estado NOT IN ('Concluída', 'Cancelada')
             AND eliminado_em IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-wrench',
                'color'    => 'warning',
                'label'    => $n . ' manutenção' . ($n > 1 ? 'ões' : '') . ' prevista' . ($n > 1 ? 's' : '') . ' nos próximos 7 dias',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php?filtro=manutencao_7dias',
                'count'    => $n,
                'priority' => 3,
            ];
        }

        // Garantias/contratos a expirar nos próximos 30 dias
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM garantias_contratos
             WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND eliminado_em IS NULL"
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
        $docs_val = $pdo->query(
            "SELECT tipo_documento FROM documentos
             WHERE data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             AND ativo = 1 AND eliminado_em IS NULL"
        )->fetchAll();
        $nv = count($docs_val);
        if ($nv > 0) {
            $tipos_distintos = array_values(array_unique(array_map(fn($r) => (string)$r->tipo_documento, $docs_val)));
            if ($nv === 1) {
                // Um só documento — nomear o tipo (Garantia, Certificado de calibração, …)
                $label = ($tipos_distintos[0] ?: 'Documento') . ' com validade próxima';
            } elseif (count($tipos_distintos) === 1) {
                $label = $nv . ' documentos de ' . $tipos_distintos[0] . ' com validade próxima';
            } else {
                $label = $nv . ' documentos com validade próxima';
            }
            $notifs[] = [
                'icon'     => 'fa-file-lines',
                'color'    => 'warning',
                'label'    => $label,
                'link'     => BASE_URL . '/private/views/documentos/lista.php?validade=proxima',
                'count'    => $nv,
                'priority' => 5,
            ];
        }

        // Equipamentos em manutenção no momento
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM equipamentos
             WHERE estado = 'Em manutenção' AND ativo = 1 AND eliminado_em IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-screwdriver-wrench',
                'color'    => 'info',
                'label'    => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' em manutenção',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php?estado=' . rawurlencode('Em manutenção'),
                'count'    => $n,
                'priority' => 6,
            ];
        }

        // Equipamentos avariados
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM equipamentos
             WHERE estado = 'Avariado' AND ativo = 1 AND eliminado_em IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-circle-xmark',
                'color'    => 'danger',
                'label'    => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' avariado' . ($n > 1 ? 's' : ''),
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php?estado=Avariado',
                'count'    => $n,
                'priority' => 7,
            ];
        }

        // Equipamentos com documentação incompleta (principal=7, componente=3)
        $tipos_7 = "'Manual de utilizador','Manual de serviço','Certificado de calibração','Contrato de manutenção','Fatura / Guia de aquisição','Declaração de conformidade','Relatório técnico'";
        $tipos_3 = "'Manual de utilizador','Declaração de conformidade','Relatório técnico'";
        $rows_inc = $pdo->query("
            SELECT e.id FROM equipamentos e
            WHERE e.eliminado_em IS NULL AND e.id_equipamento_pai IS NULL AND e.ativo=1
            AND (SELECT COUNT(DISTINCT d.tipo_documento) FROM documentos d
                 WHERE d.id_equipamento=e.id AND d.eliminado_em IS NULL AND d.ficheiro_conteudo IS NOT NULL
                 AND d.tipo_documento IN ($tipos_7)) < 7
            UNION ALL
            SELECT e.id FROM equipamentos e
            WHERE e.eliminado_em IS NULL AND e.id_equipamento_pai IS NOT NULL AND e.ativo=1
            AND (SELECT COUNT(DISTINCT d.tipo_documento) FROM documentos d
                 WHERE d.id_equipamento=e.id AND d.eliminado_em IS NULL AND d.ficheiro_conteudo IS NOT NULL
                 AND d.tipo_documento IN ($tipos_3)) < 3
        ")->fetchAll();
        $n = count($rows_inc);
        if ($n > 0) {
            // 1 equipamento → vai direto à ficha na aba Documentos; vários → lista filtrada
            $link = $n === 1
                ? BASE_URL . '/private/views/equipamentos/detalhes.php?id=' . (int)$rows_inc[0]->id . '&tab=documentos'
                : BASE_URL . '/private/views/equipamentos/lista.php?filtro=docs_incompletos';
            $notifs[] = [
                'icon'     => 'fa-file-circle-exclamation',
                'color'    => 'warning',
                'label'    => $n . ' equipamento' . ($n > 1 ? 's' : '') . ' com documentação incompleta',
                'link'     => $link,
                'count'    => $n,
                'priority' => 5,
            ];
        }

        // Empréstimos em curso sem devolução (mais de 30 dias)
        $row = $pdo->query(
            "SELECT COUNT(*) AS total FROM emprestimos_equipamentos
             WHERE data_devolucao IS NULL
             AND data_saida < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             AND eliminado_em IS NULL"
        )->fetch();
        if ((int)($row->total ?? 0) > 0) {
            $n = (int)$row->total;
            $notifs[] = [
                'icon'     => 'fa-boxes-packing',
                'color'    => 'warning',
                'label'    => $n . ' empréstimo' . ($n > 1 ? 's' : '') . ' em curso há mais de 30 dias',
                'link'     => BASE_URL . '/private/views/equipamentos/lista.php?filtro=emprestimo_30dias',
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
