<?php
require_once __DIR__ . '/../../config/config.php';

// apanha erros nao tratados e mostra um aviso bonito em vez de uma pagina em branco
if (!ob_get_level()) { ob_start(); }
set_exception_handler(static function (\Throwable $e): void {
    while (ob_get_level() > 0) { ob_end_clean(); }
    $is_db = $e instanceof PDOException
          || stripos($e->getMessage(), 'SQLSTATE') !== false
          || stripos($e->getMessage(), 'connect') !== false
          || stripos($e->getMessage(), 'Access denied') !== false;
    $msg = $is_db
        ? 'Sem ligação à base de dados. O servidor pode estar temporariamente indisponível.'
        : 'Ocorreu um erro inesperado. Por favor, tente novamente.';
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Erro — MedSolutions</title>
<style>*{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,sans-serif}body{background:#f0f4f8;min-height:100vh;display:grid;place-items:center;padding:1rem}.card{background:#fff;border-radius:16px;overflow:hidden;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.12)}.hd{background:#fee2e2;padding:18px 22px;display:flex;align-items:center;gap:10px;border-bottom:1px solid #fca5a5}.hd h2{color:#dc2626;font-size:1rem;font-weight:700;margin:0}.bd{padding:24px}.bd p{color:#1e293b;font-weight:600;margin:0 0 8px;font-size:.95rem}.bd small{color:#64748b;font-size:.85rem;line-height:1.5;display:block}.ft{padding:16px 22px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end}button{padding:9px 18px;border-radius:8px;font-size:.88rem;font-weight:600;cursor:pointer;border:1px solid #e2e8f0;background:#fff;color:#334155}.bp{background:linear-gradient(135deg,#0d6ea8,#0bb37e);color:#fff;border:0}</style>
</head><body><div class="card">
<div class="hd"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg><h2>Erro de ligação</h2></div>
<div class="bd"><p>' . htmlspecialchars($msg) . '</p><small>Se o problema persistir, verifique a sua ligação à rede ou contacte o administrador do sistema.</small></div>
<div class="ft"><button onclick="history.back()">Voltar</button><button class="bp" onclick="location.reload()">Tentar novamente</button></div>
</div></body></html>';
    exit;
});

// faz a ligacao a base de dados so uma vez e devolve-a sempre que for preciso
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
                // reaproveita a ligacao entre paginas (a BD e remota, abrir ligacao nova e lento)
                PDO::ATTR_PERSISTENT => true,
            ]
        );
    }

    return $pdo;
}

// guarda uma alteracao no historico (criar/editar/apagar); se falhar nao chateia
function mhs_historico(string $entidade, ?int $entidade_id, string $entidade_nome, string $acao, string $detalhe = ''): void {
    try {
        mhs_pdo()->prepare(
            "INSERT INTO historico_alteracoes (entidade, entidade_id, entidade_nome, acao, detalhe, utilizador, criado_em)
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
    // houve uma alteracao de dados: limpa o cache das notificacoes para o sino atualizar logo
    unset($_SESSION['_notif_cache'], $_SESSION['_notif_cache_ts']);
}

// compara o antes e o depois e diz que campos mudaram (para o historico)
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

// le um PDF carregado e devolve o conteudo para guardar na base de dados (ou null se nao houver)
function mhs_ler_pdf_upload(string $campo, ?string &$erro = null): ?array {
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
    if (strtolower(pathinfo($_FILES[$campo]['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        $erro = 'Apenas são aceites ficheiros PDF.';
        return null;
    }
    $bin = @file_get_contents($_FILES[$campo]['tmp_name']);
    if ($bin === false) {
        $erro = 'Não foi possível ler o ficheiro.';
        return null;
    }
    return ['conteudo' => $bin, 'mime' => 'application/pdf', 'nome' => basename($_FILES[$campo]['name'])];
}

// le varios PDF carregados de uma vez (name="campo[]") e devolve os que sao validos
function mhs_ler_pdfs_upload(string $campo): array {
    $out = [];
    if (empty($_FILES[$campo]) || !is_array($_FILES[$campo]['name'])) {
        return $out;
    }
    $total = count($_FILES[$campo]['name']);
    for ($i = 0; $i < $total; $i++) {
        if (($_FILES[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) { continue; }
        if (($_FILES[$campo]['size'][$i] ?? 0) > 10 * 1024 * 1024) { continue; }
        if (strtolower(pathinfo($_FILES[$campo]['name'][$i], PATHINFO_EXTENSION)) !== 'pdf') { continue; }
        $bin = @file_get_contents($_FILES[$campo]['tmp_name'][$i]);
        if ($bin === false) { continue; }
        $out[] = ['conteudo' => $bin, 'mime' => 'application/pdf', 'nome' => basename($_FILES[$campo]['name'][$i])];
    }
    return $out;
}

// adiciona uma coluna a uma tabela so se ela ainda nao existir
function mhs_ensure_col(PDO $pdo, string $table, string $col, string $definition): void {
    try {
        $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $st->execute([$table, $col]);
        if ((int)$st->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        }
    } catch (PDOException) {}
}

// garante que existem as colunas dos ficheiros na tabela garantias_contratos
// (ficheiro_* guarda o contrato; garantia_* guarda a garantia)
function mhs_ensure_garantia_doc_cols(PDO $pdo): void {
    mhs_ensure_col($pdo, 'garantias_contratos', 'garantia_nome_ficheiro',     '`garantia_nome_ficheiro` VARCHAR(255) NULL');
    mhs_ensure_col($pdo, 'garantias_contratos', 'garantia_ficheiro_conteudo', '`garantia_ficheiro_conteudo` LONGBLOB NULL');
    mhs_ensure_col($pdo, 'garantias_contratos', 'garantia_ficheiro_mime',     '`garantia_ficheiro_mime` VARCHAR(100) NULL');
}

// partilha o PDF do contrato do equipamento com a area das garantias/contratos
// (o mesmo ficheiro fica nos dois lados); atualiza o registo mais recente ou cria um novo
function mhs_sincronizar_contrato_pdf(PDO $pdo, int $id_equipamento, array $pdf): void {
    if (empty($pdf['conteudo'])) { return; }
    try {
        // ve se ja existe uma garantia/contrato para este equipamento
        $st = $pdo->prepare("SELECT id FROM garantias_contratos WHERE id_equipamento = ? AND eliminado_em IS NULL ORDER BY id DESC LIMIT 1");
        $st->execute([$id_equipamento]);
        $gid = $st->fetchColumn();

        if ($gid) {
            $up = $pdo->prepare("UPDATE garantias_contratos SET nome_ficheiro = ?, ficheiro_conteudo = ?, ficheiro_mime = ?, atualizado_em = NOW() WHERE id = ?");
            $up->bindValue(1, $pdf['nome']);
            $up->bindValue(2, $pdf['conteudo'], PDO::PARAM_LOB);
            $up->bindValue(3, $pdf['mime'] ?: 'application/pdf');
            $up->bindValue(4, (int)$gid, PDO::PARAM_INT);
            $up->execute();
        } else {
            // Criar um registo mínimo para que o contrato fique visível na pasta
            $marca = $pdo->query("SELECT marca FROM equipamentos WHERE id = " . (int)$id_equipamento)->fetchColumn();
            $in = $pdo->prepare("INSERT INTO garantias_contratos (id_equipamento,tem_contrato,tipo_contrato,entidade_responsavel,nome_ficheiro,ficheiro_conteudo,ficheiro_mime,criado_em) VALUES (?,?,?,?,?,?,?,NOW())");
            $in->bindValue(1, $id_equipamento, PDO::PARAM_INT);
            $in->bindValue(2, 1, PDO::PARAM_INT);
            $in->bindValue(3, 'Contrato de manutenção');
            $in->bindValue(4, $marca ?: null);
            $in->bindValue(5, $pdf['nome']);
            $in->bindValue(6, $pdf['conteudo'], PDO::PARAM_LOB);
            $in->bindValue(7, $pdf['mime'] ?: 'application/pdf');
            $in->execute();
        }
    } catch (PDOException) {
        // sincronização não é crítica — falha silenciosa
    }
}

// igual ao de cima mas para o PDF da garantia (colunas garantia_*)
function mhs_sincronizar_garantia_pdf(PDO $pdo, int $id_equipamento, array $pdf): void {
    if (empty($pdf['conteudo'])) { return; }
    mhs_ensure_garantia_doc_cols($pdo);
    try {
        $st = $pdo->prepare("SELECT id FROM garantias_contratos WHERE id_equipamento = ? AND eliminado_em IS NULL ORDER BY id DESC LIMIT 1");
        $st->execute([$id_equipamento]);
        $gid = $st->fetchColumn();

        if ($gid) {
            $up = $pdo->prepare("UPDATE garantias_contratos SET garantia_nome_ficheiro = ?, garantia_ficheiro_conteudo = ?, garantia_ficheiro_mime = ?, atualizado_em = NOW() WHERE id = ?");
            $up->bindValue(1, $pdf['nome']);
            $up->bindValue(2, $pdf['conteudo'], PDO::PARAM_LOB);
            $up->bindValue(3, $pdf['mime'] ?: 'application/pdf');
            $up->bindValue(4, (int)$gid, PDO::PARAM_INT);
            $up->execute();
        } else {
            $marca = $pdo->query("SELECT marca FROM equipamentos WHERE id = " . (int)$id_equipamento)->fetchColumn();
            $in = $pdo->prepare("INSERT INTO garantias_contratos (id_equipamento,tem_contrato,entidade_responsavel,garantia_nome_ficheiro,garantia_ficheiro_conteudo,garantia_ficheiro_mime,criado_em) VALUES (?,?,?,?,?,?,NOW())");
            $in->bindValue(1, $id_equipamento, PDO::PARAM_INT);
            $in->bindValue(2, 0, PDO::PARAM_INT);
            $in->bindValue(3, $marca ?: null);
            $in->bindValue(4, $pdf['nome']);
            $in->bindValue(5, $pdf['conteudo'], PDO::PARAM_LOB);
            $in->bindValue(6, $pdf['mime'] ?: 'application/pdf');
            $in->execute();
        }
    } catch (PDOException) {
        // sincronização não é crítica — falha silenciosa
    }
}

// torna um equipamento ativo outra vez (estava inativo/abatido) e regista a mudanca
function mhs_ativar_equipamento(int $id): bool {
    try {
        $pdo = mhs_pdo();
        $stmt = $pdo->prepare("SELECT codigo_inventario, designacao, estado FROM equipamentos WHERE id = ? AND eliminado_em IS NULL");
        $stmt->execute([$id]);
        $eq = $stmt->fetch();
        if (!$eq || $eq->estado === 'Ativo') { return false; }
        $antigo = $eq->estado;
        $pdo->prepare("UPDATE equipamentos SET estado = 'Ativo', atualizado_em = NOW() WHERE id = ?")->execute([$id]);
        $pdo->prepare("
            INSERT INTO equipamentos_movimentacoes (id_equipamento, campo, valor_anterior, valor_novo, alterado_por, criado_em)
            VALUES (?, 'estado', ?, 'Ativo', ?, NOW())
        ")->execute([$id, $antigo, $_SESSION['user_email'] ?? null]);
        mhs_historico('equipamento', $id, $eq->codigo_inventario . ' — ' . $eq->designacao, 'editar', 'Estado: ' . $antigo . ' → Ativo');
        return true;
    } catch (PDOException) {
        return false;
    }
}

// conclui a manutencao de um equipamento: fecha intervencoes abertas e poe-no outra vez ativo
function mhs_concluir_manutencao(int $id): bool {
    try {
        $pdo = mhs_pdo();
        $stmt = $pdo->prepare("SELECT codigo_inventario, designacao, estado FROM equipamentos WHERE id = ? AND eliminado_em IS NULL");
        $stmt->execute([$id]);
        $eq = $stmt->fetch();
        if (!$eq) { return false; }

        $estado_antigo = $eq->estado;

        $pdo->prepare("
            UPDATE manutencoes SET estado='Concluída', data_manutencao=COALESCE(data_manutencao, CURDATE()), atualizado_em=NOW()
            WHERE id_equipamento=? AND estado IN ('Em curso','Planeada') AND eliminado_em IS NULL
        ")->execute([$id]);

        $pdo->prepare("
            UPDATE manutencoes_preventivas
            SET ultima_manutencao=CURDATE(),
                proxima_manutencao = CASE
                    WHEN periodicidade='Mensal'     THEN DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                    WHEN periodicidade='Trimestral' THEN DATE_ADD(CURDATE(), INTERVAL 3 MONTH)
                    WHEN periodicidade='Semestral'  THEN DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                    ELSE DATE_ADD(CURDATE(), INTERVAL 1 YEAR) END,
                estado='Planeada', atualizado_em=NOW()
            WHERE id_equipamento=? AND eliminado_em IS NULL
        ")->execute([$id]);

        $pdo->prepare("UPDATE equipamentos SET estado='Ativo', atualizado_em=NOW() WHERE id=?")->execute([$id]);

        if ($estado_antigo !== 'Ativo') {
            $pdo->prepare("
                INSERT INTO equipamentos_movimentacoes (id_equipamento, campo, valor_anterior, valor_novo, alterado_por, criado_em)
                VALUES (?, 'estado', ?, 'Ativo', ?, NOW())
            ")->execute([$id, $estado_antigo, $_SESSION['user_email'] ?? null]);
        }

        mhs_historico('equipamento', $id, $eq->codigo_inventario . ' — ' . $eq->designacao, 'editar',
            'Manutenção confirmada/concluída — estado: ' . $estado_antigo . ' → Ativo');
        return true;
    } catch (PDOException) {
        return false;
    }
}

// diz se o utilizador tem sessao iniciada
function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_SESSION['logged_in'] ?? false);
}

// se nao tiver sessao iniciada, manda para a pagina de login
function redirect_if_not_logged() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }
}

// devolve o perfil do utilizador com sessao (admin ou tecnico)
function mhs_profile(): string {
    return $_SESSION['profile'] ?? '';
}

// diz se o utilizador e administrador
function is_admin(): bool {
    return mhs_profile() === 'admin';
}

// bloqueia o acesso a quem nao for admin (acoes sensiveis como apagar) e manda para o dashboard
function require_admin(): void {
    if (!is_admin()) {
        $_SESSION['error_message'] = 'Sem permissões. Apenas administradores podem executar esta ação.';
        header('Location: ' . BASE_URL . '/private/home.php');
        exit;
    }
}

// se ja tiver sessao iniciada, manda logo para o dashboard
function redirect_if_logged() {
    if (is_logged_in()) {
        header('Location: ' . BASE_URL . '/private/home.php');
        exit;
    }
}

// devolve um badge colorido conforme o estado do equipamento
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

// devolve um badge colorido conforme a criticidade do equipamento
function get_criticidade_badge($criticidade) {
    $criticidade = strtolower(trim($criticidade ?? ''));
    
    if (strpos($criticidade, 'alta') !== false) {
        return '<span class="badge bg-danger">Crítica</span>';
    } elseif (strpos($criticidade, 'méd') !== false || strpos($criticidade, 'med') !== false) {
        return '<span class="badge bg-warning">Média</span>';
    } elseif (strpos($criticidade, 'baixa') !== false) {
        return '<span class="badge bg-info">Baixa</span>';
    } elseif (strpos($criticidade, 'suporte') !== false) {
        return '<span class="badge bg-danger">Suporte de Vida</span>';
    }
    
    return '<span class="badge bg-light text-dark">' . htmlspecialchars($criticidade) . '</span>';
}

// escapa o texto para mostrar em HTML em seguranca (evita injecoes)
function esc($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// redireciona para uma pagina deixando uma mensagem guardada na sessao
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header('Location: ' . $url);
    exit;
}

// vai buscar a mensagem guardada na sessao, apaga-a e devolve o alerta em HTML
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

// encripta texto com AES-256-CBC (para esconder valores nos links)
function aes_encrypt($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(OPENSSL_METHOD));
    $encrypted = openssl_encrypt($data, OPENSSL_METHOD, OPENSSL_KEY, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// desencripta o texto AES que veio nos links
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

// validacoes gerais dos equipamentos (fica aqui para usar no futuro)
function validate_equipment_fields($data) {
    $errors = [];
    // depois pode-se acrescentar aqui as validacoes que faltarem
    return $errors;
}
?>
