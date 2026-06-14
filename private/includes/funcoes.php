<?php
require_once __DIR__ . '/../../config/config.php';

function mhs_pdo() {
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . MYSQL_HOST . ';port=' . MYSQL_PORT . ';dbname=' . MYSQL_DATABASE . ';charset=utf8mb4',
            MYSQL_USERNAME,
            MYSQL_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ]
        );
    }

    return $pdo;
}

/**
 * Registar uma alteração no histórico global (parte privada).
 * Nunca interrompe a operação principal: falha em silêncio.
 *
 * @param string   $entidade      ex: 'equipamento', 'categoria', 'fornecedor'
 * @param int|null $entidade_id   id do registo afetado
 * @param string   $entidade_nome rótulo legível (ex: 'EQ-001 — Monitor')
 * @param string   $acao          'criar' | 'editar' | 'apagar'
 * @param string   $detalhe       descrição opcional (campos alterados, etc.)
 */
function mhs_historico(string $entidade, ?int $entidade_id, string $entidade_nome, string $acao, string $detalhe = ''): void {
    try {
        mhs_pdo()->prepare(
            "INSERT INTO historico_alteracoes (entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        )->execute([
            $entidade,
            $entidade_id,
            $entidade_nome,
            $acao,
            $detalhe !== '' ? $detalhe : null,
            $_SESSION['user_email'] ?? 'sistema',
        ]);
    } catch (Exception) {
        // histórico não é crítico — falha silenciosa
    }
}

/**
 * Comparar dois registos (arrays) e devolver descrição dos campos alterados.
 * Usado para detalhar edições no histórico.
 */
function mhs_diff_campos(array $antes, array $depois, array $rotulos = []): string {
    $alteracoes = [];
    foreach ($depois as $campo => $novo) {
        $velho = $antes[$campo] ?? null;
        if ((string)$velho !== (string)$novo) {
            $rotulo = $rotulos[$campo] ?? $campo;
            $alteracoes[] = $rotulo . ': "' . (string)$velho . '" → "' . (string)$novo . '"';
        }
    }
    return implode(' · ', $alteracoes);
}

/**
 * Guardar um PDF carregado na pasta uploads/documentos.
 * Devolve o nome do ficheiro guardado, ou null se não houve upload válido.
 *
 * @param string $campo    nome do campo do formulário (ex: 'ficheiro')
 * @param string $prefixo  prefixo para o nome do ficheiro (ex: código do equipamento)
 * @param string $erro     (saída) mensagem de erro, se aplicável
 */
function mhs_guardar_pdf(string $campo, string $prefixo, ?string &$erro = null): ?string {
    if (empty($_FILES[$campo]['name']) || ($_FILES[$campo]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null; // sem ficheiro — não é erro
    }
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Falha no carregamento do ficheiro.';
        return null;
    }
    if ($_FILES[$campo]['size'] > 10 * 1024 * 1024) {
        $erro = 'O ficheiro excede o limite de 10 MB.';
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $erro = 'Apenas são aceites ficheiros PDF.';
        return null;
    }

    $dir = __DIR__ . '/../uploads/documentos';
    if (!is_dir($dir)) { mkdir($dir, 0775, true); }

    $base = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $prefixo)) ?: 'doc';
    $nome = $base . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(3)), 0, 6) . '.pdf';

    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $dir . '/' . $nome)) {
        $erro = 'Não foi possível guardar o ficheiro no servidor.';
        return null;
    }
    return $nome;
}

/**
 * Guardar vários PDFs carregados (campo com name="campo[]").
 * Devolve um array de nomes de ficheiro guardados (apenas os PDF válidos).
 */
function mhs_guardar_pdfs_multi(string $campo, string $prefixo): array {
    $guardados = [];
    if (empty($_FILES[$campo]) || !is_array($_FILES[$campo]['name'])) {
        return $guardados;
    }
    $dir = __DIR__ . '/../uploads/documentos';
    if (!is_dir($dir)) { mkdir($dir, 0775, true); }

    $base = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $prefixo)) ?: 'doc';
    $total = count($_FILES[$campo]['name']);
    for ($i = 0; $i < $total; $i++) {
        if (($_FILES[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { continue; }
        if (($_FILES[$campo]['size'][$i] ?? 0) > 10 * 1024 * 1024) { continue; }
        if (strtolower(pathinfo($_FILES[$campo]['name'][$i], PATHINFO_EXTENSION)) !== 'pdf') { continue; }

        $nome = $base . '_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(3)), 0, 6) . '.pdf';
        if (move_uploaded_file($_FILES[$campo]['tmp_name'][$i], $dir . '/' . $nome)) {
            $guardados[] = ['ficheiro' => $nome, 'original' => $_FILES[$campo]['name'][$i]];
        }
    }
    return $guardados;
}

/**
 * Verificar se utilizador está autenticado
 */
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_SESSION['logged_in'] ?? false);
}

/**
 * Redirecionar se não estiver autenticado
 */
function redirect_if_not_logged() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }
}

/**
 * Perfil do utilizador autenticado.
 */
function mhs_profile(): string {
    return $_SESSION['profile'] ?? '';
}

/**
 * É administrador?
 */
function is_admin(): bool {
    return mhs_profile() === 'admin';
}

/**
 * Bloquear acesso a quem não for administrador (ações sensíveis: apagar, mensagens, etc.).
 * Redireciona para o dashboard com mensagem de erro.
 */
function require_admin(): void {
    if (!is_admin()) {
        $_SESSION['error_message'] = 'Sem permissões. Apenas administradores podem executar esta ação.';
        header('Location: ' . BASE_URL . '/private/home.php');
        exit;
    }
}

/**
 * Redirecionar se estiver autenticado
 */
function redirect_if_logged() {
    if (is_logged_in()) {
        header('Location: ' . BASE_URL . '/private/home.php');
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

/**
 * Encriptar string com AES-256-CBC (para URLs)
 */
function aes_encrypt($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(OPENSSL_METHOD));
    $encrypted = openssl_encrypt($data, OPENSSL_METHOD, OPENSSL_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Desencriptar string com AES-256-CBC (de URLs)
 */
function aes_decrypt($data) {
    try {
        $data = base64_decode($data);
        $iv_len = openssl_cipher_iv_length(OPENSSL_METHOD);
        $iv = substr($data, 0, $iv_len);
        $encrypted = substr($data, $iv_len);
        return openssl_decrypt($encrypted, OPENSSL_METHOD, OPENSSL_KEY, 0, $iv);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Validações gerais (placeholder para extensão futura)
 */
function validate_equipment_fields($data) {
    $errors = [];
    // Implementar validações conforme necessário
    return $errors;
}
?>
