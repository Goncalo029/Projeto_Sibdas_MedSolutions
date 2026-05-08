<?php
/**
 * Verificar se utilizador está autenticado
 */
function is_logged_in() {
    return !empty($_SESSION['user_logged_in'] ?? false);
}

/**
 * Redirecionar se não estiver autenticado
 */
function redirect_if_not_logged() {
    if (!is_logged_in()) {
        header('Location: ../../public/login.php');
        exit;
    }
}

/**
 * Redirecionar se estiver autenticado
 */
function redirect_if_logged() {
    if (is_logged_in()) {
        header('Location: ../private/index.php');
        exit;
    }
}

/**
 * Obter badge de estado
 */
function get_estado_badge($estado) {
    $estado = strtolower(trim($estado ?? ''));
    
    if ($estado === 'ativo') {
        return '<span class="badge bg-success">Ativo</span>';
    } elseif (strpos($estado, 'manuten') !== false) {
        return '<span class="badge bg-warning">Em manutenção</span>';
    } elseif ($estado === 'inativo') {
        return '<span class="badge bg-secondary">Inativo</span>';
    }
    
    return '<span class="badge bg-light text-dark">' . htmlspecialchars($estado) . '</span>';
}

/**
 * Obter badge de criticidade
 */
function get_criticidade_badge($criticidade) {
    $criticidade = strtolower(trim($criticidade ?? ''));
    
    if (strpos($criticidade, 'alta') !== false) {
        return '<span class="badge bg-danger">Crítica</span>';
    } elseif (strpos($criticidade, 'médio') !== false || strpos($criticidade, 'media') !== false) {
        return '<span class="badge bg-warning">Média</span>';
    } elseif (strpos($criticidade, 'baixa') !== false) {
        return '<span class="badge bg-info">Baixa</span>';
    } elseif (strpos($criticidade, 'suporte') !== false) {
        return '<span class="badge bg-danger">Suporte de Vida</span>';
    }
    
    return '<span class="badge bg-light text-dark">' . htmlspecialchars($criticidade) . '</span>';
}

/**
 * Escape HTML
 */
function esc($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirecionar com mensagem
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Obter e limpar mensagem de sessão
 */
function get_message() {
    $message = $_SESSION['message'] ?? null;
    $type = $_SESSION['message_type'] ?? 'info';
    unset($_SESSION['message'], $_SESSION['message_type']);
    
    if ($message) {
        return sprintf(
            '<div class="alert alert-dismissible fade show alert-%s" role="alert">
                <i class="fa-solid fa-info-circle me-2"></i>%s
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>',
            $type,
            esc($message)
        );
    }
    
    return '';
}
?>
